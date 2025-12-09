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
        $user = Auth::user();
        
        // Get unique conversations (latest message from each sender)
        $conversations = $user->receivedMessages()
            ->selectRaw('DISTINCT sender_id, MAX(created_at) as latest_message_time')
            ->with('sender')
            ->latest('latest_message_time')
            ->groupBy('sender_id')
            ->paginate(15);

        $unreadCount = $user->getUnreadMessageCount();

        return view('messages.inbox', compact('conversations', 'unreadCount'));
    }

    /**
     * Sent messages - list messages sent by current user
     * GET /messages/sent
     */
    public function sent()
    {
        $user = Auth::user();
        
        $conversations = $user->sentMessages()
            ->selectRaw('DISTINCT recipient_id, MAX(created_at) as latest_message_time')
            ->with('recipient')
            ->latest('latest_message_time')
            ->groupBy('recipient_id')
            ->paginate(15);

        return view('messages.sent', compact('conversations'));
    }

    /**
     * View conversation thread with another user
     * GET /messages/{user}
     */
    public function show(User $user)
    {
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

        $currentUser->decrement('unread_messages_count', 
            $currentUser->receivedMessages()->where('sender_id', $user->id)->count()
        );

        return view('messages.thread', compact('user', 'messages'));
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

        return view('messages.compose', compact('recipient'));
    }
}
