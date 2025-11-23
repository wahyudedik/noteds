<?php

namespace App\Http\Controllers;

use App\Models\NoteConversation;
use App\Models\NoteMessage;
use App\Models\ChatQuickReply;
use App\Services\NotificationService;
use App\Services\TranslationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NoteConversationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $conversations = NoteConversation::with(['note', 'buyer', 'seller', 'latestMessage.sender'])
            ->where(function ($query) use ($user) {
                $query->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->orderByDesc(DB::raw('COALESCE(last_message_at, updated_at)'))
            ->paginate(15);

        return view('note-conversations.index', compact('conversations', 'user'));
    }

    public function show(Request $request, NoteConversation $conversation): View
    {
        $user = $request->user();
        $this->authorizeConversation($conversation, $user->id);

        $conversation->load([
            'note',
            'buyer',
            'seller',
            'messages.sender',
            'messages.translations',
            'ratings',
        ]);

        // Mark unread messages as read
        NoteMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Get quick replies for user
        $quickReplies = ChatQuickReply::getActiveForUser($user->id);

        // Check if user has rated this conversation
        $hasRated = $conversation->hasRatingFrom($user->id);
        $userRating = $hasRated ? $conversation->getRatingFrom($user->id) : null;

        return view('note-conversations.show', compact('conversation', 'user', 'quickReplies', 'hasRated', 'userRating'));
    }

    public function store(Request $request, NoteConversation $conversation, NotificationService $notificationService, TranslationService $translationService): RedirectResponse|JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $this->authorizeConversation($conversation, $user->id);

        // Detect original language
        $originalLanguage = $translationService->detectLanguage($request->input('message'));

        $message = NoteMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->input('message'),
            'original_language' => $originalLanguage,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        // Notify recipient
        $recipient = $conversation->buyer_id === $user->id
            ? $conversation->seller
            : $conversation->buyer;

        $previewText = $user->name . ': ' . Str::limit($message->message, 120);

        $notificationService->notifyNoteChatMessage(
            $recipient,
            $conversation,
            $previewText,
            $user
        );

        // Send email notification if enabled
        $notificationService->sendChatEmailNotification($recipient, $conversation, $message, $user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message->load('sender'),
            ]);
        }

        return redirect()
            ->route('note-conversations.show', $conversation)
            ->with('success', 'Message sent.');
    }

    /**
     * Translate a message.
     */
    public function translate(Request $request, NoteMessage $message, TranslationService $translationService): JsonResponse
    {
        $request->validate([
            'target_language' => ['required', 'string', 'in:en,id,ar'],
        ]);

        $user = $request->user();
        
        // Check if user is part of conversation
        $conversation = $message->conversation;
        if ($conversation->buyer_id !== $user->id && $conversation->seller_id !== $user->id) {
            abort(403);
        }

        $targetLanguage = $request->input('target_language');
        
        // Get or create translation
        $translation = $translationService->translateAndStore($message, $targetLanguage);

        return response()->json([
            'success' => true,
            'translated_message' => $translation->translated_message,
            'target_language' => $targetLanguage,
        ]);
    }

    private function authorizeConversation(NoteConversation $conversation, string $userId): void
    {
        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            abort(403);
        }
    }
}


