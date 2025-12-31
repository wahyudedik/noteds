<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ClipperLoggingService
{
    /**
     * Log view tracking event.
     */
    public function logViewTracking(string $clipId, string $platform, int $views, ?\Exception $error = null): void
    {
        $context = [
            'clip_id' => $clipId,
            'platform' => $platform,
            'views' => $views,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($error) {
            Log::channel('clipper')->error('View tracking failed', array_merge($context, [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]));
        } else {
            Log::channel('clipper')->info('View tracking successful', $context);
        }
    }

    /**
     * Log transfer event.
     */
    public function logTransfer(
        string $fromWalletType,
        ?string $fromWalletId,
        string $toWalletType,
        ?string $toWalletId,
        float $amount,
        string $reason,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?\Exception $error = null
    ): void {
        $context = [
            'from_wallet_type' => $fromWalletType,
            'from_wallet_id' => $fromWalletId,
            'to_wallet_type' => $toWalletType,
            'to_wallet_id' => $toWalletId,
            'amount' => $amount,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($error) {
            Log::channel('financial')->error('Transfer failed', array_merge($context, [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]));
        } else {
            Log::channel('financial')->info('Transfer completed', $context);
        }
    }

    /**
     * Log view validation event.
     */
    public function logViewValidation(
        string $clipId,
        int $validViews,
        float $stabilityScore,
        bool $isValid,
        ?string $fraudReason = null
    ): void {
        $context = [
            'clip_id' => $clipId,
            'valid_views' => $validViews,
            'stability_score' => $stabilityScore,
            'is_valid' => $isValid,
            'fraud_reason' => $fraudReason,
            'timestamp' => now()->toIso8601String(),
        ];

        if (!$isValid || $fraudReason) {
            Log::channel('clipper')->warning('View validation failed', $context);
        } else {
            Log::channel('clipper')->info('View validation successful', $context);
        }
    }

    /**
     * Log reward calculation.
     */
    public function logRewardCalculation(
        string $clipId,
        int $views,
        float $cpm,
        float $calculatedReward,
        ?float $maxRewardLimit = null,
        ?float $finalReward = null
    ): void {
        Log::channel('financial')->info('Reward calculated', [
            'clip_id' => $clipId,
            'views' => $views,
            'cpm' => $cpm,
            'calculated_reward' => $calculatedReward,
            'max_reward_limit' => $maxRewardLimit,
            'final_reward' => $finalReward ?? $calculatedReward,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log campaign event.
     */
    public function logCampaignEvent(
        string $campaignId,
        string $event,
        array $data = []
    ): void {
        Log::channel('clipper')->info("Campaign {$event}", array_merge([
            'campaign_id' => $campaignId,
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
        ], $data));
    }
}

