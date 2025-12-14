<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMessage;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMessageController extends Controller
{
    /**
     * Inbox - list all received messages with conversation summaries
     * GET /messages
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // Get distinct conversations from received messages
        $senderIds = $user->receivedMessages()
            ->distinct()
            ->pluck('sender_id')
            ->toArray();

        // Get latest message from each sender for conversation list
        $conversations = [];
        foreach ($senderIds as $senderId) {
            $sender = User::find($senderId);
            $latestMessage = $user->receivedMessages()
                ->where('sender_id', $senderId)
                ->latest()
                ->first();

            if ($latestMessage && $sender) {
                $conversations[] = (object)[
                    'sender_id' => $senderId,
                    'sender' => $sender,
                    'latest_message_time' => $latestMessage->created_at,
                ];
            }
        }

        // Sort by latest message time
        usort($conversations, function ($a, $b) {
            return $b->latest_message_time <=> $a->latest_message_time;
        });

        // Paginate manually
        $page = request()->get('page', 1);
        $perPage = 15;
        $total = count($conversations);
        $conversations = array_slice($conversations, ($page - 1) * $perPage, $perPage);

        $unreadCount = $user->getUnreadMessageCount();

        return view('40-shared/messages/inbox', compact('conversations', 'unreadCount'));
    }

    /**
     * Sent messages - list messages sent by current user
     * GET /messages/sent
     */
    public function sent()
    {
        /** @var User $user */
        $user = Auth::user();

        // Get distinct conversations from sent messages
        $recipientIds = $user->sentMessages()
            ->distinct()
            ->pluck('recipient_id')
            ->toArray();

        // Get latest message to each recipient for conversation list
        $conversations = [];
        foreach ($recipientIds as $recipientId) {
            $recipient = User::find($recipientId);
            $latestMessage = $user->sentMessages()
                ->where('recipient_id', $recipientId)
                ->latest()
                ->first();

            if ($latestMessage && $recipient) {
                $conversations[] = (object)[
                    'recipient_id' => $recipientId,
                    'recipient' => $recipient,
                    'latest_message_time' => $latestMessage->created_at,
                ];
            }
        }

        // Sort by latest message time
        usort($conversations, function ($a, $b) {
            return $b->latest_message_time <=> $a->latest_message_time;
        });

        // Paginate manually
        $page = request()->get('page', 1);
        $perPage = 15;
        $total = count($conversations);
        $conversations = array_slice($conversations, ($page - 1) * $perPage, $perPage);

        return view('40-shared/messages/sent', compact('conversations'));
    }

    /**
     * View conversation thread with another user
     * GET /messages/{user}
     */
    public function show(User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Authorization
        if ($currentUser->id === $user->id) {
            abort(403, 'Cannot message yourself');
        }

        // Get conversation between both users
        $messages = UserMessage::conversationBetween($currentUser->id, $user->id)
            ->paginate(50, ['*'], 'page', 1); // Reverse pagination for chat

        // Mark all messages from this user as read
        $currentUser->receivedMessages()
            ->where('sender_id', $user->id)
            ->unread($currentUser->id)
            ->update(['read_at' => now()]);

        $currentUser->decrement(
            'unread_messages_count',
            $currentUser->receivedMessages()->where('sender_id', $user->id)->count()
        );

        return view('40-shared/messages/thread', compact('user', 'messages'));
    }

    /**
     * Send a message
     * POST /messages
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|uuid|exists:users,id',
            'message' => 'required|string|max:2000',
        ]);

        /** @var User $currentUser */
        $currentUser = Auth::user();
        $recipient = User::findOrFail($validated['recipient_id']);

        // Validation
        if ($currentUser->id === $recipient->id) {
            return back()->withErrors('You cannot message yourself');
        }

        // Create message
        $message = $currentUser->sendMessage(
            $validated['message'],
            $recipient
        );

        // Send notification
        $recipient->notify(new NewMessageNotification($message, $currentUser));

        return back()->with('success', 'Message sent');
    }

    /**
     * Mark message as read
     * POST /messages/{message}/read
     */
    public function markAsRead(UserMessage $message)
    {
        // Authorization
        if ($message->recipient_id !== Auth::id()) {
            abort(403);
        }

        $message->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Delete a message (soft delete, only for sender/recipient)
     * DELETE /messages/{message}
     */
    public function destroy(UserMessage $message)
    {
        $user = Auth::user();

        // Authorization - only sender or recipient can delete
        if ($message->sender_id !== $user->id && $message->recipient_id !== $user->id) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Message deleted');
    }

    /**
     * Compose new message (show form)
     * GET /messages/compose
     */
    public function compose(Request $request)
    {
        $recipientId = $request->query('to');
        $recipient = null;

        if ($recipientId) {
            $recipient = User::findOrFail($recipientId);
        }

        return view('40-shared/messages/compose', compact('recipient'));
    }
}
