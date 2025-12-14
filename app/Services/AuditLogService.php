<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Audit Logging Service
 * Track all sensitive operations untuk security audit trail
 */
class AuditLogService
{
    /**
     * Log authentication event
     */
    public static function logLogin(int|string|User $user, string $status = 'success', ?string $reason = null): void
    {
        self::log('login', self::resolveUserId($user), [
            'status' => $status,
            'reason' => $reason,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log logout event
     */
    public static function logLogout(int|string|User $user): void
    {
        self::log('logout', self::resolveUserId($user), [
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log password change
     */
    public static function logPasswordChange(int $userId, bool $success = true): void
    {
        self::log('password_change', $userId, [
            'success' => $success,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log profile update
     */
    public static function logProfileUpdate(int $userId, array $changes): void
    {
        // Filter sensitive fields
        $sensitiveFields = ['password', 'email', 'phone', 'ssn', 'bank_account'];

        $logChanges = [];
        foreach ($changes as $field => $value) {
            if (in_array($field, $sensitiveFields)) {
                $logChanges[$field] = '***CHANGED***';
            } else {
                $logChanges[$field] = $value;
            }
        }

        self::log('profile_update', $userId, [
            'changes' => $logChanges,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log note creation
     */
    public static function logNoteCreation(int $userId, int $noteId, string $title): void
    {
        self::log('note_created', $userId, [
            'note_id' => $noteId,
            'title' => $title,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log note deletion
     */
    public static function logNoteDeletion(int $userId, int $noteId, string $title): void
    {
        self::log('note_deleted', $userId, [
            'note_id' => $noteId,
            'title' => $title,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log transaction
     */
    public static function logTransaction(int $userId, array $data): void
    {
        self::log('transaction', $userId, [
            'transaction_id' => $data['id'] ?? null,
            'type' => $data['type'] ?? 'unknown',
            'amount' => $data['amount'] ?? 0,
            'status' => $data['status'] ?? 'unknown',
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log file upload
     */
    public static function logFileUpload(int $userId, string $fileName, string $type, int $size): void
    {
        self::log('file_upload', $userId, [
            'file_name' => $fileName,
            'file_type' => $type,
            'file_size' => $size,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log data export
     */
    public static function logDataExport(int $userId, string $dataType, int $recordCount): void
    {
        self::log('data_export', $userId, [
            'data_type' => $dataType,
            'record_count' => $recordCount,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log permission change
     */
    public static function logPermissionChange(int $userId, int $targetUserId, array $changes): void
    {
        self::log('permission_change', $userId, [
            'target_user_id' => $targetUserId,
            'changes' => $changes,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log suspension/ban
     */
    public static function logUserSuspension(int $userId, int $suspendedUserId, string $reason): void
    {
        self::log('user_suspension', $userId, [
            'suspended_user_id' => $suspendedUserId,
            'reason' => $reason,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log dispute creation
     */
    public static function logDisputeCreated(int $userId, int $disputeId, int $transactionId): void
    {
        self::log('dispute_created', $userId, [
            'dispute_id' => $disputeId,
            'transaction_id' => $transactionId,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log refund
     */
    public static function logRefund(int $userId, int $transactionId, float $amount): void
    {
        self::log('refund', $userId, [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log admin action
     */
    public static function logAdminAction(int $adminId, string $action, array $details = []): void
    {
        self::log('admin_action', $adminId, array_merge([
            'action' => $action,
            'ip_address' => Request::ip(),
        ], $details));
    }

    /**
     * Log API access
     */
    public static function logApiAccess(int $userId, string $endpoint, string $method, int $statusCode): void
    {
        self::log('api_access', $userId, [
            'endpoint' => $endpoint,
            'method' => $method,
            'status_code' => $statusCode,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Log failed login attempt
     */
    public static function logFailedLogin(string $email, string $reason = 'invalid_credentials'): void
    {
        AuditLog::create([
            'user_id' => null,
            'action' => 'failed_login',
            'description' => "Failed login attempt for email: {$email}",
            'data' => [
                'email' => $email,
                'reason' => $reason,
                'ip_address' => Request::ip(),
            ],
        ]);
    }

    /**
     * Log suspicious activity
     */
    public static function logSuspiciousActivity(int $userId, string $type, array $details): void
    {
        self::log('suspicious_activity', $userId, array_merge([
            'type' => $type,
            'ip_address' => Request::ip(),
        ], $details));
    }

    /**
     * Base log method
     */
    private static function log(string $action, int|string|null $userId, array $data): void
    {
        try {
            AuditLog::create([
                'user_id' => $userId ?? Auth::id(),
                'action' => $action,
                'description' => self::getActionDescription($action),
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Audit log failed: ' . $e->getMessage());
        }
    }

    /**
     * Get human-readable action description
     */
    private static function getActionDescription(string $action): string
    {
        $descriptions = [
            'login' => 'User logged in',
            'logout' => 'User logged out',
            'password_change' => 'Password changed',
            'profile_update' => 'Profile updated',
            'note_created' => 'Note created',
            'note_deleted' => 'Note deleted',
            'transaction' => 'Transaction processed',
            'file_upload' => 'File uploaded',
            'data_export' => 'Data exported',
            'permission_change' => 'Permission changed',
            'user_suspension' => 'User suspended',
            'dispute_created' => 'Dispute created',
            'refund' => 'Refund processed',
            'admin_action' => 'Admin action performed',
            'api_access' => 'API accessed',
            'failed_login' => 'Failed login attempt',
            'suspicious_activity' => 'Suspicious activity detected',
        ];

        return $descriptions[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }

    /**
     * Normalize user identifier
     */
    private static function resolveUserId(int|string|User|null $user): int|string|null
    {
        if ($user instanceof User) {
            return $user->getKey();
        }

        return $user;
    }

    /**
     * Get audit logs for user
     */
    public static function getUserLogs(int $userId, int $limit = 100)
    {
        return AuditLog::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for action
     */
    public static function getActionLogs(string $action, int $limit = 100)
    {
        return AuditLog::where('action', $action)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent suspicious activities
     */
    public static function getRecentSuspiciousActivities(int $minutes = 60, int $limit = 50)
    {
        return AuditLog::where('action', 'suspicious_activity')
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->latest()
            ->limit($limit)
            ->get();
    }
}
