<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Clip;
use App\Models\ClipViewTracking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CampaignPayoutDistributionService
{
    public function __construct(
        protected WalletService $walletService,
        protected AutoTransferService $autoTransferService
    ) {}

    /**
     * Distribute campaign remaining budget equally to clippers
     * who reached per-account view target.
     */
    public function distributeEqualSplit(Campaign $campaign, ?int $overridePerAccountTarget = null, array $filters = [], array $weights = []): int
    {
        return DB::transaction(function () use ($campaign, $overridePerAccountTarget, $filters, $weights) {
            if (!$campaign || !$campaign->exists) {
                throw new Exception('Campaign not found.');
            }

            $target = (int) ($overridePerAccountTarget ?? $campaign->per_account_view_target ?? 0);
            if ($target <= 0) {
                throw new Exception('Per-account view target is required for equal split strategy.');
            }

            $campaignWallet = $this->walletService->getCampaignWallet($campaign);
            $remaining = (float) $campaignWallet->remaining_budget;
            if ($remaining <= 0) {
                return 0;
            }

            $eligible = collect($this->getEligibleClippers($campaign, $filters, $weights));
            $winners = $eligible->filter(function ($row) use ($target) {
                return (int) ($row['total_valid_views'] ?? 0) >= $target;
            })->values();

            $count = $winners->count();
            if ($count === 0) {
                return 0;
            }

            $grossPerWinner = round($remaining / $count, 2);
            if ($grossPerWinner <= 0) {
                return 0;
            }

            $distributed = 0;
            foreach ($winners as $winner) {
                /** @var User $clipper */
                $clipper = User::find($winner['clipper_id']);
                if (!$clipper) {
                    continue;
                }
                try {
                    $this->transferDistribution($campaign, $clipper, $grossPerWinner, 'equal_split');
                    $distributed += $grossPerWinner;
                } catch (Exception $e) {
                    Log::error('Failed to distribute equal split payout', [
                        'campaign_id' => $campaign->id,
                        'clipper_id' => $clipper->id,
                        'amount' => $grossPerWinner,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update campaign stats
            if ($distributed > 0) {
                $campaign->increment('total_spent', $distributed);
                // Remaining budget is adjusted inside wallet deduction
            }

            return $count;
        });
    }

    /**
     * Distribute full remaining budget to a single winner.
     * Winner is the clipper with highest total approved valid views,
     * optionally requiring global target views if set.
     */
    public function distributeSingleWinner(
        Campaign $campaign,
        ?int $overrideGlobalTargetViews = null,
        ?string $manualWinnerUserId = null,
        array $filters = [],
        array $weights = [],
        bool $forceManualWinner = false
    ): ?User {
        return DB::transaction(function () use ($campaign, $overrideGlobalTargetViews, $manualWinnerUserId, $filters, $weights, $forceManualWinner) {
            if (!$campaign || !$campaign->exists) {
                throw new Exception('Campaign not found.');
            }

            $campaignWallet = $this->walletService->getCampaignWallet($campaign);
            $remaining = (float) $campaignWallet->remaining_budget;
            if ($remaining <= 0) {
                return null;
            }

            $threshold = (int) ($overrideGlobalTargetViews ?? $campaign->global_target_views ?? 0);

            $eligible = collect($this->getEligibleClippers($campaign, $filters));

            if ($manualWinnerUserId) {
                if ($forceManualWinner) {
                    $clipper = User::find($manualWinnerUserId);
                    if (!$clipper) {
                        return null;
                    }
                    // Ensure the user has at least one approved clip in this campaign
                    $hasApproved = Clip::where('campaign_id', $campaign->id)
                        ->where('clipper_id', $clipper->id)
                        ->where('status', 'approved')
                        ->exists();
                    if (!$hasApproved) {
                        return null;
                    }
                    $this->transferDistribution($campaign, $clipper, $remaining, 'single_winner_manual_forced');
                    $campaign->increment('total_spent', $remaining);
                    return $clipper;
                }
                $aggregate = $eligible->firstWhere('clipper_id', $manualWinnerUserId);
                if (!$aggregate) {
                    return null;
                }
                if ($threshold > 0 && (int) ($aggregate['total_valid_views'] ?? 0) < $threshold) {
                    return null;
                }
                $clipper = User::find($aggregate['clipper_id']);
                if (!$clipper) {
                    return null;
                }
                $this->transferDistribution($campaign, $clipper, $remaining, 'single_winner_manual');
                $campaign->increment('total_spent', $remaining);
                return $clipper;
            }

            $sorted = $this->sortByCompositeScore($eligible, $weights)->values();
            $top = $sorted->first();

            if (!$top) {
                return null;
            }

            if ($threshold > 0 && (int) ($top['total_valid_views'] ?? 0) < $threshold) {
                // No clipper meets global target
                return null;
            }

            $clipper = User::find($top['clipper_id']);
            if (!$clipper) {
                return null;
            }

            $this->transferDistribution($campaign, $clipper, $remaining, 'single_winner');

            // Update campaign stats
            $campaign->increment('total_spent', $remaining);

            return $clipper;
        });
    }

    /**
     * Transfer helper using platform fee deduction and ledger entries.
     */
    protected function transferDistribution(Campaign $campaign, User $clipper, float $grossAmount, string $reason): bool
    {
        return DB::transaction(function () use ($campaign, $clipper, $grossAmount, $reason) {
            $campaignWallet = $this->walletService->getCampaignWallet($campaign);
            $clipperWallet = $this->walletService->getClipperWallet($clipper);
            $platformWallet = $this->walletService->getPlatformWallet();

            if ($grossAmount <= 0) {
                throw new Exception('Invalid distribution amount.');
            }

            if ($campaignWallet->remaining_budget < $grossAmount) {
                throw new Exception('Insufficient campaign budget for distribution.');
            }

            // Deduct platform fee using same logic as AutoTransferService
            $platformFee = $this->autoTransferService->deductPlatformFee($grossAmount);
            $netAmount = $grossAmount - $platformFee;

            // Deduct campaign budget
            if (!$campaignWallet->deductBudget($grossAmount)) {
                throw new Exception('Failed to deduct campaign budget for distribution.');
            }

            // Add to clipper wallet
            if (!$clipperWallet->addReward((float) $netAmount)) {
                // rollback campaign deduction
                $campaignWallet->remaining_budget += $grossAmount;
                $campaignWallet->save();
                throw new Exception('Failed to add distribution to clipper wallet.');
            }

            // Add platform fee
            if ($platformFee > 0) {
                $platformWallet->addFee((float) $platformFee);
            }

            // Ledger entries
            \App\Models\LedgerEntry::createEntry([
                'from_wallet_type' => 'campaign',
                'from_wallet_id' => $campaignWallet->id,
                'to_wallet_type' => 'clipper',
                'to_wallet_id' => $clipperWallet->id,
                'amount' => (float) $netAmount,
                'reason' => $reason,
                'reference_type' => 'campaign',
                'reference_id' => $campaign->id,
            ]);

            if ($platformFee > 0) {
                \App\Models\LedgerEntry::createEntry([
                    'from_wallet_type' => 'campaign',
                    'from_wallet_id' => $campaignWallet->id,
                    'to_wallet_type' => 'platform',
                    'to_wallet_id' => $platformWallet->id,
                    'amount' => (float) $platformFee,
                    'reason' => 'fee',
                    'reference_type' => 'campaign',
                    'reference_id' => $campaign->id,
                ]);
            }

            return true;
        });
    }

    protected function sortByCompositeScore(\Illuminate\Support\Collection $eligible, array $weights): \Illuminate\Support\Collection
    {
        $wViews = $this->getWeight('views', $weights['weight_views'] ?? null);
        $wStab = $this->getWeight('stability', $weights['weight_stability'] ?? null);
        $wValid = $this->getWeight('validation', $weights['weight_validation'] ?? null);

        $maxViews = max(array_map(function ($row) {
            return (int) ($row['total_valid_views'] ?? 0);
        }, $eligible->all())) ?: 1;

        return $eligible->sortByDesc(function ($row) use ($wViews, $wStab, $wValid, $maxViews) {
            $viewsNorm = ((int) ($row['total_valid_views'] ?? 0)) / $maxViews;
            $stab = (float) ($row['avg_stability_score'] ?? 0.0);
            $validRate = (float) ($row['validation_rate'] ?? 0.0);
            $score = ($wViews * $viewsNorm) + ($wStab * $stab) + ($wValid * $validRate);
            return $score;
        });
    }

    protected function getWeight(string $key, $override): float
    {
        $cfg = (float) config('clipper.weights.' . $key, 0.0);
        $val = is_numeric($override) ? (float) $override : $cfg;
        if ($val < 0.0) $val = 0.0;
        if ($val > 1.0) $val = 1.0;
        return $val;
    }

    /**
     * Get eligible clippers for distribution with optional filters and weights.
     * Filters: min_total_valid_views, min_avg_stability_score, min_validation_rate, min_composite_score
     * Weights: weight_views, weight_stability, weight_validation (used for composite score calculation)
     */
    protected function getEligibleClippers(Campaign $campaign, array $filters = [], array $weights = []): array
    {
        $viewsAgg = Clip::query()
            ->select('clipper_id', DB::raw('SUM(valid_views) as total_valid_views'))
            ->where('campaign_id', $campaign->id)
            ->where('status', 'approved')
            ->groupBy('clipper_id')
            ->get()
            ->keyBy('clipper_id');

        $trackingAgg = ClipViewTracking::query()
            ->join('clips', 'clips.id', '=', 'clip_view_tracking.clip_id')
            ->select(
                'clips.clipper_id',
                DB::raw('AVG(clip_view_tracking.stability_score) as avg_stability_score'),
                DB::raw('SUM(CASE WHEN clip_view_tracking.is_valid = 1 THEN clip_view_tracking.views_count ELSE 0 END) as valid_count'),
                DB::raw('SUM(clip_view_tracking.views_count) as total_count')
            )
            ->where('clips.campaign_id', $campaign->id)
            ->where('clips.status', 'approved')
            ->groupBy('clips.clipper_id')
            ->get()
            ->keyBy('clipper_id');

        $wViews = $this->getWeight('views', $weights['weight_views'] ?? null);
        $wStab = $this->getWeight('stability', $weights['weight_stability'] ?? null);
        $wValid = $this->getWeight('validation', $weights['weight_validation'] ?? null);

        // Prepare rows
        $result = [];
        foreach ($viewsAgg as $clipperId => $vAgg) {
            $tAgg = $trackingAgg->get($clipperId);
            $avgStability = $tAgg ? (float) $tAgg->avg_stability_score : 0.0;
            $validCount = $tAgg ? (int) $tAgg->valid_count : 0;
            $totalCount = $tAgg ? (int) $tAgg->total_count : 0;
            $validationRate = $totalCount > 0 ? round($validCount / $totalCount, 4) : 0.0;

            $row = [
                'clipper_id' => $clipperId,
                'total_valid_views' => (int) $vAgg->total_valid_views,
                'avg_stability_score' => $avgStability,
                'validation_rate' => $validationRate,
            ];

            $minValidViews = isset($filters['min_total_valid_views']) ? (int) $filters['min_total_valid_views'] : null;
            $minStability = isset($filters['min_avg_stability_score']) ? (float) $filters['min_avg_stability_score'] : null;
            $minValidationRate = isset($filters['min_validation_rate']) ? (float) $filters['min_validation_rate'] : null;
            $minComposite = isset($filters['min_composite_score']) ? (float) $filters['min_composite_score'] : null;

            if ($minValidViews !== null && $row['total_valid_views'] < $minValidViews) {
                continue;
            }
            if ($minStability !== null && $row['avg_stability_score'] < $minStability) {
                continue;
            }
            if ($minValidationRate !== null && $row['validation_rate'] < $minValidationRate) {
                continue;
            }

            $result[] = $row;
        }

        // Compute composite score after initial filtering
        $maxViews = 0;
        foreach ($result as $r) {
            $maxViews = max($maxViews, (int) ($r['total_valid_views'] ?? 0));
        }
        $maxViews = $maxViews > 0 ? $maxViews : 1;
        foreach ($result as &$r) {
            $viewsNorm = ((int) ($r['total_valid_views'] ?? 0)) / $maxViews;
            $stab = (float) ($r['avg_stability_score'] ?? 0.0);
            $validRate = (float) ($r['validation_rate'] ?? 0.0);
            $r['composite_score'] = ($wViews * $viewsNorm) + ($wStab * $stab) + ($wValid * $validRate);
        }
        unset($r);

        // Apply min_composite_score if provided
        if (isset($filters['min_composite_score']) && $filters['min_composite_score'] !== null) {
            $minComposite = (float) $filters['min_composite_score'];
            $result = array_values(array_filter($result, function ($r) use ($minComposite) {
                return (float) ($r['composite_score'] ?? 0.0) >= $minComposite;
            }));
        }

        return $result;
    }
}
