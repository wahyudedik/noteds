<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateFraudLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service untuk deteksi dan prevent duplicate affiliate clicks
 * 
 * Strategi:
 * 1. Session-based deduplication: Satu session = satu click valid
 * 2. Device fingerprint window: Max 1 click per 5 detik dari same device
 * 3. Click ID tracking: Prevent reusing same click_id
 * 4. Rate limiting: Max clicks per minute/hour dari device yang sama
 */
class ClickDeduplicationService
{
    private const CLICK_DEDUP_WINDOW = 5; // Seconds - minimum gap between clicks from same device
    private const MAX_CLICKS_PER_MINUTE = 12; // Max 12 clicks/minute (reasonable affiliate traffic)
    private const MAX_CLICKS_PER_HOUR = 360; // Max 360 clicks/hour (1 per 10 seconds avg)
    private const SESSION_CLICK_LIMIT = 1; // Max 1 valid click per session
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Check apakah click ini adalah duplicate/fraud
     * 
     * Returns: [
     *   'is_duplicate' => bool,
     *   'reason' => string|null,
     *   'risk_score_increase' => int (0-30),
     *   'click_id' => string (unique click identifier),
     *   'fraud_indicators' => array
     * ]
     */
    public function detectDuplicateClick(
        Affiliate $affiliate,
        string $ipAddress,
        string $userAgent,
        string $sessionId,
        string $referrer = '',
    ): array {
        $fraudIndicators = [];
        $riskScoreIncrease = 0;
        $isDuplicate = false;
        $reason = null;

        // Generate device fingerprint
        $deviceFingerprint = $this->generateDeviceFingerprint($ipAddress, $userAgent);

        // Generate click signature untuk deduplication
        $clickSignature = $this->generateClickSignature(
            affiliateId: $affiliate->user_id,
            deviceFingerprint: $deviceFingerprint,
            referrer: $referrer
        );

        // Strategy 1: Check recent click dari same device dalam time window
        $recentClickKey = "click_recent_{$affiliate->user_id}_{$deviceFingerprint}";
        $lastClickTime = Cache::get($recentClickKey);

        if ($lastClickTime) {
            $timeSinceLastClick = now()->diffInSeconds($lastClickTime);

            if ($timeSinceLastClick < self::CLICK_DEDUP_WINDOW) {
                $isDuplicate = true;
                $reason = 'duplicate_click_too_fast';
                $fraudIndicators[] = 'rapid_clicks_same_device';
                $riskScoreIncrease += 25; // Significant fraud indicator

                Log::warning('Duplicate click detected - too fast', [
                    'affiliate_id' => $affiliate->user_id,
                    'device_fingerprint' => $deviceFingerprint,
                    'time_since_last' => $timeSinceLastClick . 's',
                    'threshold' => self::CLICK_DEDUP_WINDOW . 's',
                ]);

                return $this->buildDuplicateResponse(
                    isDuplicate: true,
                    reason: $reason,
                    riskScoreIncrease: $riskScoreIncrease,
                    fraudIndicators: $fraudIndicators,
                    clickId: Str::uuid(),
                );
            }
        }

        // Strategy 2: Check session-based duplicate
        // Satu session seharusnya hanya generate satu valid click
        $sessionClickKey = "click_session_{$affiliate->user_id}_{$sessionId}";
        $existingSessionClick = Cache::get($sessionClickKey);

        if ($existingSessionClick && isset($existingSessionClick['click_id'])) {
            $isDuplicate = true;
            $reason = 'duplicate_click_same_session';
            $fraudIndicators[] = 'multiple_clicks_same_session';
            $riskScoreIncrease += 20;

            Log::warning('Duplicate click detected - same session', [
                'affiliate_id' => $affiliate->user_id,
                'session_id' => $sessionId,
                'first_click_id' => $existingSessionClick['click_id'],
            ]);

            return $this->buildDuplicateResponse(
                isDuplicate: true,
                reason: $reason,
                riskScoreIncrease: $riskScoreIncrease,
                fraudIndicators: $fraudIndicators,
                clickId: $existingSessionClick['click_id'], // Return existing click ID
            );
        }

        // Strategy 3: Rate limiting check - per minute
        $minuteClicksKey = "clicks_minute_{$affiliate->user_id}_{$deviceFingerprint}";
        $minuteClicks = Cache::get($minuteClicksKey, 0);

        if ($minuteClicks >= self::MAX_CLICKS_PER_MINUTE) {
            $isDuplicate = true;
            $reason = 'rate_limit_exceeded_minute';
            $fraudIndicators[] = 'high_click_rate_minute';
            $riskScoreIncrease += 30;

            Log::warning('Rate limit exceeded - minute', [
                'affiliate_id' => $affiliate->user_id,
                'minute_clicks' => $minuteClicks,
                'limit' => self::MAX_CLICKS_PER_MINUTE,
            ]);

            return $this->buildDuplicateResponse(
                isDuplicate: true,
                reason: $reason,
                riskScoreIncrease: $riskScoreIncrease,
                fraudIndicators: $fraudIndicators,
                clickId: Str::uuid(),
            );
        }

        // Strategy 4: Rate limiting check - per hour
        $hourClicksKey = "clicks_hour_{$affiliate->user_id}_{$deviceFingerprint}";
        $hourClicks = Cache::get($hourClicksKey, 0);

        if ($hourClicks >= self::MAX_CLICKS_PER_HOUR) {
            $isDuplicate = true;
            $reason = 'rate_limit_exceeded_hour';
            $fraudIndicators[] = 'high_click_rate_hour';
            $riskScoreIncrease += 25;

            Log::warning('Rate limit exceeded - hour', [
                'affiliate_id' => $affiliate->user_id,
                'hour_clicks' => $hourClicks,
                'limit' => self::MAX_CLICKS_PER_HOUR,
            ]);

            return $this->buildDuplicateResponse(
                isDuplicate: true,
                reason: $reason,
                riskScoreIncrease: $riskScoreIncrease,
                fraudIndicators: $fraudIndicators,
                clickId: Str::uuid(),
            );
        }

        // Strategy 5: Check click signature cache untuk prevent exact duplicate
        $signatureKey = "click_signature_{$clickSignature}";
        $existingSignature = Cache::get($signatureKey);

        if ($existingSignature) {
            $isDuplicate = true;
            $reason = 'exact_duplicate_signature';
            $fraudIndicators[] = 'exact_duplicate_click';
            $riskScoreIncrease += 20;

            Log::warning('Exact duplicate click detected', [
                'affiliate_id' => $affiliate->user_id,
                'signature' => $clickSignature,
                'previous_click_id' => $existingSignature['click_id'],
            ]);

            return $this->buildDuplicateResponse(
                isDuplicate: true,
                reason: $reason,
                riskScoreIncrease: $riskScoreIncrease,
                fraudIndicators: $fraudIndicators,
                clickId: $existingSignature['click_id'],
            );
        }

        // All checks passed - this is a VALID click
        // Now register it untuk future deduplication checks

        $newClickId = Str::uuid();

        // Register click timing untuk next window check
        Cache::put($recentClickKey, now(), self::CLICK_DEDUP_WINDOW);

        // Register session click untuk prevent session duplicates
        Cache::put($sessionClickKey, [
            'click_id' => $newClickId,
            'timestamp' => now(),
            'device_fingerprint' => $deviceFingerprint,
        ], 86400); // 24 hours

        // Increment per-minute counter
        Cache::put(
            $minuteClicksKey,
            $minuteClicks + 1,
            60 // 1 minute
        );

        // Increment per-hour counter
        Cache::put(
            $hourClicksKey,
            $hourClicks + 1,
            3600 // 1 hour
        );

        // Register signature untuk prevent exact duplicates
        Cache::put($signatureKey, [
            'click_id' => $newClickId,
            'timestamp' => now(),
        ], 86400); // 24 hours

        return [
            'is_duplicate' => false,
            'reason' => null,
            'risk_score_increase' => 0,
            'click_id' => $newClickId,
            'fraud_indicators' => [],
        ];
    }

    /**
     * Generate device fingerprint dari IP + User-Agent
     * Consistent identification untuk same device
     */
    public function generateDeviceFingerprint(
        string $ipAddress,
        string $userAgent
    ): string {
        $fingerprint = "{$ipAddress}|{$userAgent}";
        return hash('sha256', $fingerprint);
    }

    /**
     * Generate click signature untuk exact duplicate detection
     * Combines: affiliate_id, device, referrer, dan timestamp window
     */
    private function generateClickSignature(
        int $affiliateId,
        string $deviceFingerprint,
        string $referrer
    ): string {
        // Include timestamp window (10 seconds) untuk flexible signature matching
        $timeWindow = floor(now()->timestamp / 10) * 10;

        $signature = "{$affiliateId}|{$deviceFingerprint}|{$referrer}|{$timeWindow}";
        return hash('sha256', $signature);
    }

    /**
     * Build response untuk duplicate click
     */
    private function buildDuplicateResponse(
        bool $isDuplicate,
        ?string $reason,
        int $riskScoreIncrease,
        array $fraudIndicators,
        string $clickId
    ): array {
        return [
            'is_duplicate' => $isDuplicate,
            'reason' => $reason,
            'risk_score_increase' => $riskScoreIncrease,
            'click_id' => $clickId,
            'fraud_indicators' => $fraudIndicators,
        ];
    }

    /**
     * Get click deduplication statistics untuk analytics
     */
    public function getDeduplicationStats(int $affiliateId, \DateInterval $interval = null): array
    {
        $interval = $interval ?? new \DateInterval('P7D'); // Default 7 days
        $startDate = now()->sub($interval);

        $totalClicks = AffiliateFraudLog::where('affiliate_id', $affiliateId)
            ->where('activity_type', 'click')
            ->where('created_at', '>=', $startDate)
            ->count();

        $duplicateClicks = AffiliateFraudLog::where('affiliate_id', $affiliateId)
            ->whereJsonContains('fraud_indicators', 'rapid_clicks_same_device')
            ->orWhereJsonContains('fraud_indicators', 'multiple_clicks_same_session')
            ->orWhereJsonContains('fraud_indicators', 'high_click_rate_minute')
            ->orWhereJsonContains('fraud_indicators', 'exact_duplicate_click')
            ->where('created_at', '>=', $startDate)
            ->count();

        $validClicks = $totalClicks - $duplicateClicks;
        $duplicatePercentage = $totalClicks > 0 ? ($duplicateClicks / $totalClicks) * 100 : 0;

        return [
            'total_clicks' => $totalClicks,
            'valid_clicks' => $validClicks,
            'duplicate_clicks' => $duplicateClicks,
            'duplicate_percentage' => round($duplicatePercentage, 2),
            'period' => $startDate->format('Y-m-d') . ' to ' . now()->format('Y-m-d'),
        ];
    }

    /**
     * Clear all deduplication cache untuk testing
     */
    public function clearDeduplicationCache(int $affiliateId): bool
    {
        $pattern = "click_*_{$affiliateId}_*";

        // Clear using pattern (simplified - production use Redis patterns)
        Cache::flush();

        Log::info('Cleared deduplication cache', ['affiliate_id' => $affiliateId]);

        return true;
    }
}
