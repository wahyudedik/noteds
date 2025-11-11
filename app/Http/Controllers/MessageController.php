<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of conversations.
     */
    public function index(): View
    {
        $conversations = Message::where('sender_id', auth()->id())
            ->orWhere('recipient_id', auth()->id())
            ->with(['sender', 'recipient', 'note'])
            ->latest()
            ->get()
            ->groupBy(function ($message) {
                $otherUserId = $message->sender_id === auth()->id() 
                    ? $message->recipient_id 
                    : $message->sender_id;
                return $otherUserId;
            })
            ->map(function ($messages) {
                return $messages->first();
            })
            ->sortByDesc('created_at')
            ->values();

        return view('messages.index', compact('conversations'));
    }

    /**
     * Display conversation with a specific user.
     */
    public function conversation(User $user): View
    {
        if ($user->id === auth()->id()) {
            abort(403, 'Cannot message yourself.');
        }

        $messages = Message::where(function ($query) use ($user) {
            $query->where('sender_id', auth()->id())
                  ->where('recipient_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->where('recipient_id', auth()->id());
        })
        ->with(['sender', 'recipient', 'note'])
        ->latest()
        ->paginate(50);

        // Mark messages as read
        Message::where('sender_id', $user->id)
            ->where('recipient_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('messages.conversation', compact('user', 'messages'));
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Cannot message yourself.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:2000'],
            'note_id' => ['nullable', 'exists:notes,id'],
        ]);

        $messageModel = Message::create([
            'sender_id' => auth()->id(),
            'recipient_id' => $user->id,
            'note_id' => $validated['note_id'] ?? null,
            'message' => $validated['message'],
        ]);

        // Notify recipient
        $this->notificationService->create(
            $user,
            'message_received',
            '💬 New Message',
            auth()->user()->name . ' sent you a message',
            route('messages.conversation', auth()->user()),
            ['message_id' => $messageModel->id, 'sender_id' => auth()->id()]
        );

        return redirect()->route('messages.conversation', $user)
            ->with('success', 'Message sent successfully.');
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Message $message): RedirectResponse
    {
        if ($message->recipient_id !== auth()->id()) {
            abort(403);
        }

        $message->markAsRead();

        return redirect()->back();
    }
}
