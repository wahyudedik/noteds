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

        $messages = $conversation->messages()
            ->with(['user', 'media', 'replyTo.user', 'readReceipts.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

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
