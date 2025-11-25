<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationPreferenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show notification preferences page.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Get all notification types
        $notificationTypes = [
            'purchase' => 'Purchase Notifications',
            'sale' => 'Sale Notifications',
            'review' => 'Review Notifications',
            'ticket_response' => 'Support Ticket Responses',
            'withdrawal' => 'Withdrawal Notifications',
            'subscription' => 'Subscription Notifications',
            'referral' => 'Referral Notifications',
            'badge_earned' => 'Badge Notifications',
            'level_up' => 'Level Up Notifications',
            'note_chat_message' => 'Chat Messages',
            'forum_post_liked' => 'Forum Post Likes',
            'forum_post_commented' => 'Forum Post Comments',
            'forum_comment_replied' => 'Forum Comment Replies',
            'forum_new_follower' => 'New Followers',
            'note_new_from_following' => 'New Notes from Following',
            'workspace_digest' => 'Workspace Digests',
            'marketplace_daily_digest' => 'Marketplace Daily Digest',
        ];

        // Get user preferences
        $preferences = $user->notificationPreferences()->get()->keyBy('notification_type');
        
        // Get quiet hours settings
        $quietHours = [
            'enabled' => $user->quiet_hours_enabled ?? false,
            'start' => $user->quiet_hours_start,
            'end' => $user->quiet_hours_end,
            'timezone' => $user->timezone ?? config('app.timezone', 'UTC'),
        ];

        // Get email digest settings
        $emailDigest = [
            'frequency' => $user->email_digest_frequency ?? 'none',
            'time' => $user->email_digest_time,
            'timezone' => $user->email_digest_timezone ?? $user->timezone ?? config('app.timezone', 'UTC'),
        ];

        return view('notifications.preferences', compact('notificationTypes', 'preferences', 'quietHours', 'emailDigest'));
    }

    /**
     * Update notification preference for a specific type.
     */
    public function updatePreference(Request $request, string $type): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'in_app' => ['boolean'],
            'email' => ['boolean'],
            'push' => ['boolean'],
        ]);

        $preference = NotificationPreference::getOrCreate(auth()->id(), $type);
        $preference->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'preference' => $preference,
                'message' => 'Notification preference updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Notification preference updated successfully.');
    }

    /**
     * Update quiet hours settings.
     */
    public function updateQuietHours(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['boolean'],
            'start' => ['required_with:enabled', 'date_format:H:i'],
            'end' => ['required_with:enabled', 'date_format:H:i'],
            'timezone' => ['nullable', 'string', 'max:255'],
        ]);

        auth()->user()->update([
            'quiet_hours_enabled' => $validated['enabled'] ?? false,
            'quiet_hours_start' => $validated['enabled'] ? $validated['start'] : null,
            'quiet_hours_end' => $validated['enabled'] ? $validated['end'] : null,
            'timezone' => $validated['timezone'] ?? auth()->user()->timezone,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Quiet hours updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Quiet hours updated successfully.');
    }

    /**
     * Update email digest preferences.
     */
    public function updateEmailDigest(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'frequency' => ['required', 'in:none,daily,weekly'],
            'time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['nullable', 'string', 'max:255'],
        ]);

        auth()->user()->update([
            'email_digest_frequency' => $validated['frequency'],
            'email_digest_time' => $validated['frequency'] !== 'none' ? ($validated['time'] ?? '09:00') : null,
            'email_digest_timezone' => $validated['timezone'] ?? auth()->user()->timezone ?? config('app.timezone', 'UTC'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email digest preferences updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Email digest preferences updated successfully.');
    }

    /**
     * Bulk update notification preferences.
     */
    public function bulkUpdate(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*.type' => ['required', 'string'],
            'preferences.*.in_app' => ['boolean'],
            'preferences.*.email' => ['boolean'],
            'preferences.*.push' => ['boolean'],
        ]);

        foreach ($validated['preferences'] as $pref) {
            $preference = NotificationPreference::getOrCreate(auth()->id(), $pref['type']);
            $preference->update([
                'in_app' => $pref['in_app'] ?? true,
                'email' => $pref['email'] ?? true,
                'push' => $pref['push'] ?? false,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification preferences updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Notification preferences updated successfully.');
    }
}
