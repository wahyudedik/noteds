<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = $user->notifications();

        // Filter by single type OR category (multiple types)
        if ($request->has('type') && $request->type && $request->type !== 'all') {
            $query->whereJsonContains('data->type', $request->type);
        } elseif ($request->has('category') && $request->category && $request->category !== 'all') {
            $categories = [
                'support' => ['new_support_ticket', 'support_ticket_response', 'support_ticket_status_update'],
                'posts' => ['post_moderated', 'post_restored'],
                'reports' => ['content_reported'],
            ];
            $types = $categories[$request->category] ?? [];
            if (!empty($types)) {
                $query->where(function ($q) use ($types) {
                    foreach ($types as $t) {
                        $q->orWhereJsonContains('data->type', $t);
                    }
                });
            }
        }

        // Filter by read/unread
        if ($request->has('read')) {
            if ($request->read === 'true') {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->latest()->paginate(20);

        // Get unread count
        $unreadCount = $user->unreadNotifications()->count();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'filters' => $request->only(['type', 'category', 'read']),
        ]);
    }

    /**
     * Mark a notification as read.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);

        // Get redirect URL before marking as read
        $redirectUrl = $this->getRedirectUrl($notification, $user);

        // Mark notification as read
        $notification->markAsRead();

        // Redirect to the appropriate URL
        return redirect($redirectUrl);
    }

    /**
     * Signed GET fallback to mark a notification as read.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function readSigned(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        $redirectUrl = $this->getRedirectUrl($notification, $user);
        $notification->markAsRead();
        return redirect($redirectUrl);
    }

    /**
     * Mark all notifications as read.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Get unread notifications count (for API/header).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Get redirect URL for a notification based on its type and data.
     *
     * @param DatabaseNotification $notification
     * @param \App\Models\User $user
     * @return string
     */
    private function getRedirectUrl(DatabaseNotification $notification, $user): string
    {
        $data = $notification->data;
        $type = $data['type'] ?? null;

        if (!$type) {
            return route('notifications.index');
        }

        try {
            return match ($type) {
                // Support Tickets
                'new_support_ticket' => $user->isAdmin()
                    ? route('admin.support-tickets.show', $data['ticket_id'] ?? '')
                    : route('notifications.index'),

                'support_ticket_response' => ($data['is_admin_response'] ?? false)
                    ? route('support.tickets.show', $data['ticket_id'] ?? '')
                    : route('admin.support-tickets.show', $data['ticket_id'] ?? ''),

                'support_ticket_status_update' => $user->isAdmin()
                    ? route('admin.support-tickets.show', $data['ticket_id'] ?? '')
                    : route('support.tickets.show', $data['ticket_id'] ?? ''),

                // Posts
                'post_moderated' => route('posts.show', $data['post_id'] ?? ''),
                'post_restored' => route('posts.show', $data['post_id'] ?? ''),

                // Reports
                'content_reported' => route('admin.reports.show', $data['report_id'] ?? ''),

                // Default fallback
                default => route('notifications.index'),
            };
        } catch (\Exception $e) {
            // If route generation fails (e.g., invalid ID), fallback to notifications index
            return route('notifications.index');
        }
    }
}
