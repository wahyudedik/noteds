<?php

namespace App\Services;

use App\Models\Clip;
use App\Models\Campaign;

class RewardCalculationService
{
    /**
     * Calculate reward based on views and CPM.
     */
    public function calculateReward(Clip $clip, int $validViews): float
    {
        $cpm = (float) $clip->campaign->cpm;
        $reward = ($validViews / 1000) * $cpm;

        // Apply max reward per clipper if set
        $reward = $this->applyMaxRewardLimit($reward, $clip->campaign);

        return round($reward, 2);
    }

    /**
     * Estimate reward based on current views.
     */
    public function estimateReward(Clip $clip, int $views): float
    {
        $cpm = (float) $clip->campaign->cpm;
        $reward = ($views / 1000) * $cpm;

        // Apply max reward per clipper if set
        $reward = $this->applyMaxRewardLimit($reward, $clip->campaign);

        return round($reward, 2);
    }

    /**
     * Apply max reward per clipper limit.
     */
    public function applyMaxRewardLimit(float $reward, Campaign $campaign): float
    {
        if ($campaign->max_reward_per_clipper) {
            return min($reward, (float) $campaign->max_reward_per_clipper);
        }

        return $reward;
    }
}

