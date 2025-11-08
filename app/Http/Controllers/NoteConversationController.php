<?php

namespace App\Http\Controllers;

use App\Models\NoteConversation;
use App\Models\NoteMessage;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
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
        ]);

        // Mark unread messages as read
        NoteMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('note-conversations.show', compact('conversation', 'user'));
    }

    public function store(Request $request, NoteConversation $conversation, NotificationService $notificationService): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $this->authorizeConversation($conversation, $user->id);

        $message = NoteMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->input('message'),
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

        return redirect()
            ->route('note-conversations.show', $conversation)
            ->with('success', 'Message sent.');
    }

    private function authorizeConversation(NoteConversation $conversation, string $userId): void
    {
        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            abort(403);
        }
    }
}


