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
                'payments' => ['payment_failed', 'order_status_update'],
                'orders' => ['new_order', 'order_created', 'order_cancelled'],
                'withdrawals' => ['withdrawal_status', 'withdrawal_request'],
                'products' => ['product_approved', 'product_rejected', 'product_created'],
                'support' => ['new_support_ticket', 'support_ticket_response', 'support_ticket_status_update'],
                'campaigns' => ['new_campaign', 'campaign_created', 'campaign_ended', 'campaign_suspended'],
                'clips' => ['clip_submitted', 'clip_approved', 'clip_rejected', 'view_validated', 'fraud_detected'],
                'brand' => ['brand_approved', 'brand_rejected'],
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

                // Clips
                'clip_approved' => route('clipper.clips.show', $data['clip_id'] ?? ''),
                'clip_rejected' => route('clipper.clips.show', $data['clip_id'] ?? ''),
                'clip_submitted' => route('admin.clips.show', $data['clip_id'] ?? ''),
                'view_validated' => route('clipper.clips.show', $data['clip_id'] ?? ''),
                'fraud_detected' => route('admin.clips.show', $data['clip_id'] ?? ''),

                // Brand
                'brand_approved' => route('clipper.brand-registration.show'),
                'brand_rejected' => route('clipper.brand-registration.show'),

                // Campaigns
                'new_campaign' => route('clipper.campaigns.available'),
                'campaign_created' => route('admin.campaigns.show', $data['campaign_id'] ?? ''),
                'campaign_ended' => route('clipper.campaigns.show', $data['campaign_id'] ?? ''),
                'campaign_suspended' => $user->isAdmin()
                    ? route('admin.campaigns.show', $data['campaign_id'] ?? '')
                    : route('clipper.campaigns.show', $data['campaign_id'] ?? ''),

                // Orders
                'new_order' => route('marketplace.seller.orders.show', $data['order_id'] ?? ''), // Always sent to seller

                'order_created' => $user->isAdmin()
                    ? route('notifications.index') // No admin.orders.show route exists
                    : route('marketplace.orders.show', $data['order_id'] ?? ''),

                'order_cancelled' => ($data['role'] ?? '') === 'seller'
                    ? route('marketplace.seller.orders.show', $data['order_id'] ?? '')
                    : route('marketplace.orders.show', $data['order_id'] ?? ''),

                'order_status_update' => route('marketplace.orders.show', $data['order_id'] ?? ''),
                'payment_failed' => route('marketplace.orders.show', $data['order_id'] ?? ''),

                // Withdrawals
                'withdrawal_status' => $this->getWithdrawalRoute($data, $user),
                'withdrawal_request' => route('admin.withdrawals.show', $data['withdrawal_id'] ?? ''),

                // Products
                'product_approved' => route('marketplace.products.show', $data['product_id'] ?? ''),
                'product_rejected' => route('marketplace.products.show', $data['product_id'] ?? ''),
                'product_created' => route('admin.products.index'), // No show route, use index

                // Posts
                'post_moderated' => route('posts.show', $data['post_id'] ?? ''),
                'post_restored' => route('posts.show', $data['post_id'] ?? ''),

                // Reports
                'content_reported' => route('admin.reports.show', $data['report_id'] ?? ''),

                // Rewards & Wallet
                'reward_received' => route('clipper.wallet.clipper'),

                // Top Up
                'topup_success' => route('clipper.top-ups.show', $data['top_up_id'] ?? ''),

                // Default fallback
                default => route('notifications.index'),
            };
        } catch (\Exception $e) {
            // If route generation fails (e.g., invalid ID), fallback to notifications index
            return route('notifications.index');
        }
    }

    /**
     * Get the appropriate withdrawal route based on withdrawal type.
     *
     * @param array $data
     * @param \App\Models\User $user
     * @return string
     */
    private function getWithdrawalRoute(array $data, $user): string
    {
        $withdrawalId = $data['withdrawal_id'] ?? null;

        if (!$withdrawalId) {
            return route('notifications.index');
        }

        // Try to load the withdrawal to determine type
        try {
            $withdrawal = \App\Models\Withdrawal::find($withdrawalId);

            if (!$withdrawal) {
                return route('notifications.index');
            }

            $userType = $withdrawal->user_type ?? null;

            return match ($userType) {
                'clipper' => route('clipper.withdrawals.show', $withdrawalId),
                'creator' => route('clipper.withdrawals.creator.show', $withdrawalId),
                default => route('marketplace.withdrawals.show', $withdrawalId), // marketplace seller
            };
        } catch (\Exception $e) {
            // Fallback: try to determine from user's role/context
            // If user has marketplace products, likely marketplace withdrawal
            if ($user->products()->exists()) {
                return route('marketplace.withdrawals.show', $withdrawalId);
            }

            // Otherwise, try clipper withdrawal
            return route('clipper.withdrawals.show', $withdrawalId);
        }
    }
}
