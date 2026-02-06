<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use App\Services\UserActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private UserActivityLogService $activityLogService
    ) {}
    /**
     * Display the settings page.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $settings = $user->settings ?? UserSetting::create([
            'user_id' => $user->id,
            'notification_preferences' => [],
            'privacy_settings' => [],
            'email_preferences' => [],
            'profile_visibility' => true,
            'search_visibility' => true,
        ]);

        return Inertia::render('Settings/Index', [
            'user' => $user,
            'settings' => $settings,
        ]);
    }

    /**
     * Update account settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAccount(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_field' => ['nullable', 'string', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Update password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'Account settings updated successfully.');
    }

    /**
     * Update privacy settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePrivacy(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'profile_visibility' => ['required', 'boolean'],
            'search_visibility' => ['required', 'boolean'],
            'privacy_settings' => ['nullable', 'array'],
        ]);

        $settings = $user->settings ?? UserSetting::create([
            'user_id' => $user->id,
            'notification_preferences' => [],
            'privacy_settings' => [],
            'email_preferences' => [],
            'profile_visibility' => true,
            'search_visibility' => true,
        ]);

        $settings->update([
            'profile_visibility' => $validated['profile_visibility'],
            'search_visibility' => $validated['search_visibility'],
            'privacy_settings' => $validated['privacy_settings'] ?? [],
        ]);

        return back()->with('success', 'Privacy settings updated successfully.');
    }

    /**
     * Update notification preferences.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateNotifications(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'notification_preferences' => ['nullable', 'array'],
        ]);

        $settings = $user->settings ?? UserSetting::create([
            'user_id' => $user->id,
            'notification_preferences' => [],
            'privacy_settings' => [],
            'email_preferences' => [],
            'profile_visibility' => true,
            'search_visibility' => true,
        ]);

        $settings->update([
            'notification_preferences' => $validated['notification_preferences'] ?? [],
        ]);

        return back()->with('success', 'Notification preferences updated successfully.');
    }

    /**
     * Update playback auto-play preference.
     */
    public function updatePlayback(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'auto_play_enabled' => ['required', 'boolean'],
        ]);
        $settings = $user->settings ?? UserSetting::create([
            'user_id' => $user->id,
            'notification_preferences' => [],
            'privacy_settings' => [],
            'email_preferences' => [],
            'profile_visibility' => true,
            'search_visibility' => true,
            'auto_play_enabled' => false,
        ]);
        $settings->update([
            'auto_play_enabled' => (bool) $validated['auto_play_enabled'],
        ]);
        return back()->with('success', 'Playback preference updated.');
    }

    /**
     * Update security settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSecurity(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Display user activity log.
     *
     * @param Request $request
     * @return Response
     */
    public function activityLog(Request $request): Response
    {
        $user = $request->user();

        $filters = [
            'activity_type' => $request->get('activity_type'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'search' => $request->get('search'),
        ];

        $query = $this->activityLogService->getUserActivities($user, $filters);
        $activities = $query->paginate(20)->withQueryString();

        // Activity types for filter
        $activityTypes = [
            'login' => 'Login History',
            'profile_change' => 'Profile Changes',
            'security_change' => 'Security Changes',
        ];

        return Inertia::render('Settings/ActivityLog', [
            'activities' => $activities,
            'activityTypes' => $activityTypes,
            'filters' => $filters,
        ]);
    }

    /**
     * Export user activity log to CSV.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportActivityLog(Request $request)
    {
        $user = $request->user();

        $filters = [
            'activity_type' => $request->get('activity_type'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'search' => $request->get('search'),
        ];

        $filename = $this->activityLogService->exportToCsv($user, $filters);

        return response()->download(
            $filename,
            'activity_log_' . now()->format('Y-m-d_H-i-s') . '.csv',
            [
                'Content-Type' => 'text/csv',
            ]
        )->deleteFileAfterSend(true);
    }
}

