<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Log;

class ClipperCommissionService
{
    /**
     * Calculate platform fee for clipper reward.
     *
     * @param float $rewardAmount
     * @return array
     */
    public function calculatePlatformFee(float $rewardAmount): array
    {
        $feePercent = $this->getPlatformFeePercent();

        $platformFee = round($rewardAmount * ($feePercent / 100), 2);
        $clipperAmount = $rewardAmount - $platformFee;

        // Ensure clipper receives at least 0
        $clipperAmount = max(0, $clipperAmount);

        return [
            'reward_amount' => $rewardAmount,
            'platform_fee_percent' => $feePercent,
            'platform_fee' => $platformFee,
            'clipper_amount' => $clipperAmount,
        ];
    }

    /**
     * Get current platform fee percentage.
     */
    public function getPlatformFeePercent(): float
    {
        return (float) PlatformSetting::get(
            'clipper_platform_fee_percent',
            config('clipper.platform_fee_percent', 5)
        );
    }

    /**
     * Update platform fee percentage.
     */
    public function updatePlatformFeePercent(float $percent): bool
    {
        if ($percent < 0 || $percent > 100) {
            throw new \InvalidArgumentException('Platform fee percentage must be between 0 and 100');
        }

        try {
            PlatformSetting::set('clipper_platform_fee_percent', $percent, 'number');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update clipper platform fee: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get platform fee settings.
     */
    public function getSettings(): array
    {
        return [
            'platform_fee_percent' => $this->getPlatformFeePercent(),
        ];
    }
}

