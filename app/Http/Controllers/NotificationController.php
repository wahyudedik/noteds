<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);
        
        return view('40-shared/notifications/index', compact('notifications'));
    }

    public function markAsRead(AppNotification $notification): RedirectResponse
    {
        // Ensure user owns this notification
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->notifications()->unread()->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }
}

