<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class UserActivityLogService
{
    /**
     * Log a user activity.
     */
    public function log(
        string $userId,
        string $activityType,
        string $action,
        ?string $description = null,
        ?array $metadata = null,
        ?Request $request = null
    ): UserActivityLog {
        $request = $request ?? request();

        return UserActivityLog::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'action' => $action,
            'description' => $description ?? $this->generateDescription($activityType, $action, $metadata),
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log a login activity.
     */
    public function logLogin(User $user, ?Request $request = null): UserActivityLog
    {
        return $this->log(
            $user->id,
            'login',
            'logged_in',
            "User logged in from {$this->getDeviceInfo($request)}",
            [
                'email' => $user->email,
            ],
            $request
        );
    }

    /**
     * Log a logout activity.
     */
    public function logLogout(User $user, ?Request $request = null): UserActivityLog
    {
        return $this->log(
            $user->id,
            'login',
            'logged_out',
            'User logged out',
            null,
            $request
        );
    }

    /**
     * Log a profile change.
     */
    public function logProfileChange(
        User $user,
        string $action,
        array $changes,
        ?Request $request = null
    ): UserActivityLog {
        return $this->log(
            $user->id,
            'profile_change',
            $action,
            "Profile updated: " . implode(', ', array_keys($changes)),
            [
                'changes' => $changes,
            ],
            $request
        );
    }

    /**
     * Log a security change (password, email, etc.).
     */
    public function logSecurityChange(
        User $user,
        string $action,
        ?array $metadata = null,
        ?Request $request = null
    ): UserActivityLog {
        return $this->log(
            $user->id,
            'security_change',
            $action,
            "Security change: {$action}",
            $metadata,
            $request
        );
    }

    /**
     * Get user activity logs with filters.
     */
    public function getUserActivities(
        User $user,
        array $filters = []
    ) {
        $query = UserActivityLog::where('user_id', $user->id);

        // Filter by activity type
        if (!empty($filters['activity_type'])) {
            $query->where('activity_type', $filters['activity_type']);
        }

        // Filter by date range
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Search
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->latest('created_at');
    }

    /**
     * Export user activities to CSV.
     */
    public function exportToCsv(User $user, array $filters = []): string
    {
        $activities = $this->getUserActivities($user, $filters)->get();

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename = $tempDir . '/activity_log_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $file = fopen($filename, 'w');
        
        // Write CSV header
        fputcsv($file, ['Date', 'Activity Type', 'Action', 'Description', 'IP Address', 'User Agent', 'Location']);

        // Write data
        foreach ($activities as $activity) {
            fputcsv($file, [
                $activity->created_at->format('Y-m-d H:i:s'),
                $activity->activity_type,
                $activity->action,
                $activity->description,
                $activity->ip_address ?? '',
                $activity->user_agent ?? '',
                $activity->location ?? '',
            ]);
        }

        fclose($file);

        return $filename;
    }

    /**
     * Generate description from activity type and action.
     */
    private function generateDescription(string $activityType, string $action, ?array $metadata): string
    {
        $descriptions = [
            'login' => [
                'logged_in' => 'User logged in',
                'logged_out' => 'User logged out',
            ],
            'profile_change' => [
                'profile_updated' => 'Profile information updated',
            ],
            'security_change' => [
                'password_changed' => 'Password changed',
                'email_changed' => 'Email address changed',
            ],
        ];

        return $descriptions[$activityType][$action] ?? ucfirst(str_replace('_', ' ', $action));
    }

    /**
     * Get device info from user agent.
     */
    private function getDeviceInfo(?Request $request = null): string
    {
        $request = $request ?? request();
        $userAgent = $request->userAgent() ?? 'Unknown';

        // Simple device detection
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'Mobile Device';
        }

        return 'Desktop';
    }
}

