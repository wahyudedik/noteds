<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignABTestingService
{
    /**
     * Create variants for A/B testing.
     */
    public function createVariants(Campaign $campaign, array $variantsData): array
    {
        // Validate total allocation percent
        $totalAllocation = array_sum(array_column($variantsData, 'allocation_percent'));
        if ($totalAllocation > 100) {
            throw new \InvalidArgumentException('Total allocation percent cannot exceed 100%.');
        }

        $variants = [];
        
        DB::transaction(function () use ($campaign, $variantsData, &$variants) {
            foreach ($variantsData as $variantData) {
                $variants[] = CampaignVariant::create([
                    'campaign_id' => $campaign->id,
                    'variant_name' => $variantData['variant_name'],
                    'cpm' => $variantData['cpm'],
                    'allocation_percent' => $variantData['allocation_percent'],
                    'status' => 'active',
                ]);
            }

            // Enable A/B testing on campaign
            $campaign->update([
                'ab_test_enabled' => true,
                'ab_test_status' => 'running',
            ]);
        });

        return $variants;
    }

    /**
     * Allocate traffic to a variant based on allocation_percent.
     * This would be called when a clip is created or viewed.
     */
    public function allocateTraffic(Campaign $campaign): ?CampaignVariant
    {
        if (!$campaign->ab_test_enabled || $campaign->ab_test_status !== 'running') {
            return null;
        }

        $variants = $campaign->activeVariants()->get();
        
        if ($variants->isEmpty()) {
            return null;
        }

        // Simple allocation based on percentage
        $random = rand(1, 100);
        $current = 0;

        foreach ($variants as $variant) {
            $current += $variant->allocation_percent;
            if ($random <= $current) {
                return $variant;
            }
        }

        // Fallback to first variant
        return $variants->first();
    }

    /**
     * Calculate performance metrics for each variant.
     */
    public function calculatePerformance(Campaign $campaign): array
    {
        $variants = $campaign->variants()->get();
        $results = [];

        foreach ($variants as $variant) {
            $views = $variant->total_views;
            $spent = $variant->total_spent;
            
            // Calculate performance score (views per rupiah spent, higher is better)
            $performanceScore = $views > 0 && $spent > 0 
                ? $views / $spent 
                : 0;

            $variant->update([
                'performance_score' => $performanceScore,
            ]);

            $results[] = [
                'variant_id' => $variant->id,
                'variant_name' => $variant->variant_name,
                'cpm' => $variant->cpm,
                'total_views' => $views,
                'total_spent' => $spent,
                'performance_score' => $performanceScore,
            ];
        }

        return $results;
    }

    /**
     * Determine winning variant based on performance metrics.
     */
    public function determineWinner(Campaign $campaign): ?CampaignVariant
    {
        $variants = $campaign->variants()->get();
        
        if ($variants->isEmpty()) {
            return null;
        }

        // Calculate performance for all variants
        $this->calculatePerformance($campaign);

        // Find variant with highest performance score
        $winner = $variants->sortByDesc('performance_score')->first();

        if ($winner) {
            $winner->markAsWinner();
        }

        return $winner;
    }

    /**
     * Apply winning variant CPM to main campaign.
     */
    public function applyWinner(Campaign $campaign): bool
    {
        $winner = $campaign->winningVariant()->first();

        if (!$winner) {
            return false;
        }

        $campaign->update([
            'cpm' => $winner->cpm,
            'ab_test_status' => 'completed',
        ]);

        // Deactivate all variants
        $campaign->variants()->update(['status' => 'completed']);

        return true;
    }

    /**
     * Get applicable rules for a campaign (variants).
     */
    public function getApplicableVariants(Campaign $campaign): \Illuminate\Database\Eloquent\Collection
    {
        if (!$campaign->ab_test_enabled || $campaign->ab_test_status !== 'running') {
            return collect();
        }

        return $campaign->activeVariants()->get();
    }

    /**
     * Update variant metrics (called when views/spending occurs).
     */
    public function updateVariantMetrics(CampaignVariant $variant, int $views, float $spent): void
    {
        $variant->increment('total_views', $views);
        $variant->increment('total_spent', $spent);
        
        // Recalculate performance score
        $performanceScore = $variant->total_views > 0 && $variant->total_spent > 0 
            ? $variant->total_views / $variant->total_spent 
            : 0;
            
        $variant->update(['performance_score' => $performanceScore]);
    }
}

