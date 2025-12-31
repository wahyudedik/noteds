<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClipperNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = auth()->user()->notifications()
            ->latest()
            ->paginate(20);

        return Inertia::render('Clipper/Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount()
    {
        $count = auth()->user()->unreadNotifications->count();

        return response()->json(['count' => $count]);
    }
}

