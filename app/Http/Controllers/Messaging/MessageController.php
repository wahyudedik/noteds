<?php

namespace App\Http\Controllers\Messaging;

use App\Events\TypingStopped;
use App\Http\Controllers\Controller;
use App\Http\Requests\Messaging\StoreMessageRequest;
use App\Http\Requests\Messaging\UpdateMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\MessageService;
use App\Services\TypingIndicatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Jobs\TranscribeVoiceMessage;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService,
        private TypingIndicatorService $typingIndicatorService
    ) {}

    /**
     * Get messages for a conversation.
     */
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $t0 = microtime(true);
        $limit = max(1, min(50, (int) $request->get('limit', 20)));
        $before = $request->get('before');
        $query = $conversation->messages()
            ->with(['user', 'media', 'replyTo.user', 'readReceipts.user'])
            ->orderBy('created_at', 'desc');
        if ($before) {
            try {
                $dt = \Illuminate\Support\Carbon::parse($before);
                $query->where('created_at', '<', $dt);
            } catch (\Throwable $e) {}
        }
        $messages = $query->paginate($limit);
        $elapsed = (microtime(true) - $t0) * 1000;
        try {
            \Illuminate\Support\Facades\Redis::incrbyfloat('metrics:messaging:messages.index:sum_ms', $elapsed);
            \Illuminate\Support\Facades\Redis::incr('metrics:messaging:messages.index:count');
            \Illuminate\Support\Facades\Redis::zadd('metrics:latency:messaging.messages.index:samples', [ (int) floor(microtime(true)) => $elapsed ]);
            \Illuminate\Support\Facades\Redis::zremrangebyscore('metrics:latency:messaging.messages.index:samples', 0, (int) floor(microtime(true)) - 3600);
        } catch (\Throwable $e) {
            $sum = (float) \Illuminate\Support\Facades\Cache::get('metrics:messaging:messages.index:sum_ms', 0);
            $cnt = (int) \Illuminate\Support\Facades\Cache::get('metrics:messaging:messages.index:count', 0);
            \Illuminate\Support\Facades\Cache::put('metrics:messaging:messages.index:sum_ms', $sum + $elapsed, 600);
            \Illuminate\Support\Facades\Cache::put('metrics:messaging:messages.index:count', $cnt + 1, 600);
            $samples = \Illuminate\Support\Facades\Cache::get('metrics:latency:messaging.messages.index:samples', []);
            $samples[] = ['ts' => time(), 'ms' => $elapsed];
            $samples = array_filter($samples, fn($x) => ($x['ts'] ?? 0) >= time() - 3600);
            \Illuminate\Support\Facades\Cache::put('metrics:latency:messaging.messages.index:samples', $samples, 600);
        }

        return response()->json($messages);
    }

    /**
     * Store a newly created message.
     */
    public function store(StoreMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        try {
            $t0 = microtime(true);
            // Stop typing indicator
            $this->typingIndicatorService->removeTyping($conversation, $user);
            broadcast(new TypingStopped($conversation, $user))->toOthers();

            // Handle file uploads - Laravel handles array inputs with []
            $attachments = [];
            if ($request->hasFile('attachments')) {
                $files = $request->file('attachments');
                // Laravel returns array for multiple files
                if (is_array($files)) {
                    $attachments = $files;
                } else {
                    $attachments = [$files];
                }
            }
            
            $message = $this->messageService->sendMessage(
                $conversation,
                $user,
                $validated['content'] ?? null,
                $attachments,
                $validated['reply_to_id'] ?? null
            );
            $elapsed = (microtime(true) - $t0) * 1000;
            try {
                \Illuminate\Support\Facades\Redis::incrbyfloat('metrics:messaging:messages.store:sum_ms', $elapsed);
                \Illuminate\Support\Facades\Redis::incr('metrics:messaging:messages.store:count');
                \Illuminate\Support\Facades\Redis::zadd('metrics:latency:messaging.messages.store:samples', [ (int) floor(microtime(true)) => $elapsed ]);
                \Illuminate\Support\Facades\Redis::zremrangebyscore('metrics:latency:messaging.messages.store:samples', 0, (int) floor(microtime(true)) - 3600);
            } catch (\Throwable $e) {
                $sum = (float) \Illuminate\Support\Facades\Cache::get('metrics:messaging:messages.store:sum_ms', 0);
                $cnt = (int) \Illuminate\Support\Facades\Cache::get('metrics:messaging:messages.store:count', 0);
                \Illuminate\Support\Facades\Cache::put('metrics:messaging:messages.store:sum_ms', $sum + $elapsed, 600);
                \Illuminate\Support\Facades\Cache::put('metrics:messaging:messages.store:count', $cnt + 1, 600);
                $samples = \Illuminate\Support\Facades\Cache::get('metrics:latency:messaging.messages.store:samples', []);
                $samples[] = ['ts' => time(), 'ms' => $elapsed];
                $samples = array_filter($samples, fn($x) => ($x['ts'] ?? 0) >= time() - 3600);
                \Illuminate\Support\Facades\Cache::put('metrics:latency:messaging.messages.store:samples', $samples, 600);
            }

            return response()->json([
                'message' => $message,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Upload voice message with duration and metadata.
     */
    public function storeVoice(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $request->validate([
            'voice' => 'required|file|mimetypes:audio/webm,audio/ogg,audio/mpeg,audio/wav|max:20480',
            'duration' => 'required|integer|min:1|max:120',
            'reply_to_id' => 'nullable|string',
            'audio_codec' => 'nullable|string',
            'sample_rate' => 'nullable|integer',
            'bitrate' => 'nullable|integer',
            'channels' => 'nullable|integer|min:1|max:8',
            'waveform' => 'nullable|array',
            'encrypted' => 'nullable|boolean',
        ]);
        try {
            /** @var UploadedFile $audio */
            $audio = $request->file('voice');
            $duration = (int) $request->input('duration');
            $waveform = $request->input('waveform');
            if (is_string($waveform)) {
                try { $waveform = json_decode($waveform, true); } catch (\Throwable $e) { $waveform = null; }
            }
            $meta = [
                'audio_codec' => $request->input('audio_codec'),
                'sample_rate' => $request->input('sample_rate'),
                'bitrate' => $request->input('bitrate'),
                'channels' => $request->input('channels'),
                'waveform' => $waveform,
                'is_encrypted' => (bool) $request->boolean('encrypted'),
            ];
            $message = $this->messageService->sendVoice(
                $conversation,
                $user,
                $audio,
                $duration,
                $request->input('reply_to_id'),
                $meta
            );
            if (config('transcription.provider') !== 'none' && $message->media && count($message->media) > 0) {
                dispatch(new TranscribeVoiceMessage($message->media[0]->id));
            }
            return response()->json(['message' => $message], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update the specified message.
     */
    public function update(UpdateMessageRequest $request, Message $message): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        try {
            $message = $this->messageService->editMessage($message, $user, $validated['content']);

            return response()->json([
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified message.
     */
    public function destroy(Request $request, Message $message): JsonResponse
    {
        $user = $request->user();

        try {
            $this->messageService->deleteMessage($message, $user);

            return response()->json([
                'message' => 'Message deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Request $request, Message $message): JsonResponse
    {
        $user = $request->user();

        $this->messageService->markAsRead($message, $user);

        return response()->json([
            'message' => 'Message marked as read.',
        ]);
    }

    /**
     * Mark all messages in conversation as read.
     */
    public function markConversationAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        $this->messageService->markConversationAsRead($conversation, $user);

        return response()->json([
            'message' => 'All messages marked as read.',
        ]);
    }

    /**
     * Search messages in conversation.
     */
    public function search(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = $request->get('q', '');
        $senderId = $request->get('sender_id');
        $type = $request->get('type');

        $messages = $conversation->messages()
            ->when($query, function ($q) use ($query) {
                $q->where('content', 'like', "%{$query}%");
            })
            ->when($senderId, function ($q) use ($senderId) {
                $q->where('user_id', $senderId);
            })
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->with(['user', 'media'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($messages);
    }
}
