<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Base Security Policy
 * Provides common security checks untuk authorization
 */
abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(?User $user): bool
    {
        return $user !== null && $user->email_verified_at !== null;
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin(?User $user): bool
    {
        return $user && $user->hasRole('admin');
    }

    /**
     * Check if user is the resource owner
     */
    protected function isOwner(?User $user, ?int $ownerId): bool
    {
        return $user && $user->id === $ownerId;
    }

    /**
     * Check if user is seller
     */
    protected function isSeller(?User $user): bool
    {
        return $user && $user->hasRole('seller');
    }

    /**
     * Check if user is buyer
     */
    protected function isBuyer(?User $user): bool
    {
        return $user && $user->hasRole('buyer');
    }

    /**
     * Check if user has KYC verification
     */
    protected function hasKyc(?User $user): bool
    {
        return $user && $user->ktp_path && $user->selfie_path;
    }

    /**
     * Check if user is active
     */
    protected function isActive(?User $user): bool
    {
        return $user && $user->is_active;
    }

    /**
     * Check if user is suspended
     */
    protected function isSuspended(?User $user): bool
    {
        return $user && $user->suspended_at !== null;
    }

    /**
     * Check if user has premium subscription
     */
    protected function hasPremium(?User $user): bool
    {
        return $user && $user->isPremium();
    }

    /**
     * Check if user can access resource
     */
    protected function canAccess(?User $user, string $permission): bool
    {
        return $user && $user->hasPermissionTo($permission);
    }

    /**
     * Check if user has sufficient balance
     */
    protected function hasSufficientBalance(?User $user, float $amount): bool
    {
        if (!$user) {
            return false;
        }

        $wallet = $user->wallet;
        return $wallet && $wallet->balance >= $amount;
    }

    /**
     * Deny access with proper message
     */
    protected function denyUnauthorized(string $message = 'Unauthorized action.'): void
    {
        \Log::warning('Unauthorized access attempt', [
            'user_id' => auth()->id(),
            'message' => $message,
            'ip' => request()->ip(),
        ]);

        $this->deny($message);
    }

    /**
     * Check suspicious activity before allowing action
     */
    protected function checkSuspiciousActivity(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Check if user has too many failed attempts recently
        $failedAttempts = \Cache::get("failed_attempts:{$user->id}", 0);
        if ($failedAttempts > 10) {
            AuditLogService::logSuspiciousActivity(
                $user->id,
                'multiple_failed_attempts',
                ['count' => $failedAttempts]
            );
            return false;
        }

        return true;
    }

    /**
     * Log policy decision
     */
    protected function logAccess(?User $user, string $action, bool $allowed): void
    {
        if (!$allowed) {
            \Log::warning("Policy denied: {$action}", [
                'user_id' => $user?->id,
                'action' => $action,
                'ip' => request()->ip(),
            ]);
        }
    }
}
