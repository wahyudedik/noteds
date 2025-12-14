<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

/**
 * Rate Limiting Service
 * Prevent abuse dengan intelligent rate limiting
 */
class RateLimitService
{
    /**
     * Check if action is rate limited
     */
    public static function isLimited(string $key, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * Increment rate limit counter
     */
    public static function hit(string $key, int $decayMinutes = 1): int
    {
        return RateLimiter::hit($key, $decayMinutes * 60);
    }

    /**
     * Get remaining attempts
     */
    public static function remaining(string $key, int $maxAttempts = 60): int
    {
        $attempts = RateLimiter::attempts($key);
        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Reset rate limit for key
     */
    public static function reset(string $key): void
    {
        RateLimiter::resetAttempts($key);
    }

    /**
     * Get retry after (in seconds)
     */
    public static function retryAfter(string $key): int
    {
        return RateLimiter::availableIn($key);
    }

    /**
     * Rate limit login attempts
     */
    public static function rateLimitLogin(string $email): bool
    {
        $key = "login.attempts:{$email}";

        if (self::isLimited($key, 5, 15)) { // 5 attempts per 15 minutes
            return false;
        }

        self::hit($key, 15);
        return true;
    }

    /**
     * Rate limit password reset
     */
    public static function rateLimitPasswordReset(string $email): bool
    {
        $key = "password.reset:{$email}";

        if (self::isLimited($key, 3, 60)) { // 3 attempts per hour
            return false;
        }

        self::hit($key, 60);
        return true;
    }

    /**
     * Rate limit API calls
     */
    public static function rateLimitApi(int $userId): bool
    {
        $key = "api.calls:{$userId}";

        if (self::isLimited($key, 1000, 60)) { // 1000 per minute
            return false;
        }

        self::hit($key, 60);
        return true;
    }

    /**
     * Rate limit file uploads
     */
    public static function rateLimitFileUpload(int $userId): bool
    {
        $key = "file.upload:{$userId}";

        if (self::isLimited($key, 50, 60)) { // 50 files per hour
            return false;
        }

        self::hit($key, 60);
        return true;
    }

    /**
     * Rate limit note creation
     */
    public static function rateLimitNoteCreation(int $userId): bool
    {
        $key = "note.create:{$userId}";

        if (self::isLimited($key, 20, 60)) { // 20 notes per hour
            return false;
        }

        self::hit($key, 60);
        return true;
    }

    /**
     * Rate limit messaging
     */
    public static function rateLimitMessaging(int $userId): bool
    {
        $key = "message.send:{$userId}";

        if (self::isLimited($key, 100, 60)) { // 100 messages per hour
            return false;
        }

        self::hit($key, 60);
        return true;
    }

    /**
     * Rate limit transactions
     */
    public static function rateLimitTransaction(int $userId): bool
    {
        $key = "transaction:{$userId}";

        if (self::isLimited($key, 50, 60)) { // 50 transactions per hour
            return false;
        }

        self::hit($key, 60);
        return true;
    }

    /**
     * Rate limit withdrawal requests
     */
    public static function rateLimitWithdrawal(int $userId): bool
    {
        $key = "withdrawal:{$userId}";

        if (self::isLimited($key, 5, 1440)) { // 5 withdrawals per day
            return false;
        }

        self::hit($key, 1440);
        return true;
    }

    /**
     * Rate limit refund requests
     */
    public static function rateLimitRefundRequest(int $userId): bool
    {
        $key = "refund.request:{$userId}";

        if (self::isLimited($key, 10, 1440)) { // 10 per day
            return false;
        }

        self::hit($key, 1440);
        return true;
    }

    /**
     * Check for suspicious activity (many failed attempts)
     */
    public static function isSuspiciousActivity(string $key, int $threshold = 10): bool
    {
        return RateLimiter::attempts($key) >= $threshold;
    }

    /**
     * Log rate limit exceeded
     */
    public static function logRateLimitExceeded(string $identifier, string $type): void
    {
        Cache::increment("rate_limit_exceeded:{$identifier}:{$type}", 1, 60);

        $count = Cache::get("rate_limit_exceeded:{$identifier}:{$type}", 0);

        if ($count > 5) {
            AuditLogService::logSuspiciousActivity(
                auth()->id() ?? 0,
                'rate_limit_exceeded',
                [
                    'type' => $type,
                    'identifier' => $identifier,
                    'count' => $count,
                ]
            );
        }
    }

    /**
     * Get current rate limit status
     */
    public static function getStatus(string $key, int $maxAttempts = 60, int $decayMinutes = 1): array
    {
        $attempts = RateLimiter::attempts($key);
        $remaining = max(0, $maxAttempts - $attempts);

        return [
            'attempts' => $attempts,
            'remaining' => $remaining,
            'max_attempts' => $maxAttempts,
            'limited' => self::isLimited($key, $maxAttempts),
            'retry_after' => self::isLimited($key, $maxAttempts) ? self::retryAfter($key) : 0,
        ];
    }
}
