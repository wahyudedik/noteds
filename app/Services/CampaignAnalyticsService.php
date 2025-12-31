<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CampaignAnalyticsService
{
    public function __construct(
        private ClipperCacheService $cacheService
    ) {}

    /**
     * Get campaign statistics.
     */
    public function getCampaignStats(Campaign $campaign): array
    {
        return $this->cacheService->getCampaignStats($campaign->id, function () use ($campaign) {
            return $this->calculateCampaignStats($campaign);
        });
    }

    /**
     * Calculate campaign statistics (without cache).
     */
    protected function calculateCampaignStats(Campaign $campaign): array
    {
        $totalViews = $campaign->total_views;
        $totalClips = $campaign->total_clips;
        $totalSpent = (float) $campaign->total_spent;
        $remainingBudget = $campaign->getRemainingBudget();
        $roi = $this->getROI($campaign);

        return [
            'total_views' => $totalViews,
            'total_clips' => $totalClips,
            'total_spent' => $totalSpent,
            'remaining_budget' => $remainingBudget,
            'budget_usage_percent' => $campaign->max_budget > 0 
                ? round(($totalSpent / $campaign->max_budget) * 100, 2) 
                : 0,
            'roi' => $roi,
            'average_cpm' => $totalViews > 0 
                ? round(($totalSpent / $totalViews) * 1000, 2) 
                : 0,
        ];
    }

    /**
     * Get views chart data.
     */
    public function getViewsChart(Campaign $campaign, string $period = '7d'): array
    {
        $days = match ($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 7,
        };

        $startDate = now()->subDays($days);

        $trackingData = DB::table('clip_view_tracking')
            ->join('clips', 'clip_view_tracking.clip_id', '=', 'clips.id')
            ->where('clips.campaign_id', $campaign->id)
            ->where('clip_view_tracking.tracked_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(clip_view_tracking.tracked_at) as date'),
                DB::raw('SUM(clip_view_tracking.views_count) as views')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'labels' => $trackingData->pluck('date')->toArray(),
            'data' => $trackingData->pluck('views')->toArray(),
        ];
    }

    /**
     * Get top performing clips.
     */
    public function getTopClips(Campaign $campaign, int $limit = 10)
    {
        return $campaign->clips()
            ->where('status', 'approved')
            ->orderBy('valid_views', 'desc')
            ->limit($limit)
            ->with('clipper')
            ->get();
    }

    /**
     * Calculate ROI (Return on Investment).
     */
    public function getROI(Campaign $campaign): float
    {
        $totalSpent = (float) $campaign->total_spent;
        $totalViews = $campaign->total_views;

        if ($totalSpent <= 0 || $totalViews <= 0) {
            return 0;
        }

        // ROI = (Value Generated / Cost) * 100
        // For simplicity, we'll use views as value indicator
        // In real scenario, you might want to use engagement metrics
        $valueGenerated = $totalViews; // This could be weighted by engagement
        $roi = ($valueGenerated / $totalSpent) * 100;

        return round($roi, 2);
    }

    /**
     * Get brand dashboard stats (all campaigns).
     */
    public function getBrandDashboardStats(User $brand): array
    {
        return $this->cacheService->getBrandDashboard($brand->id, function () use ($brand) {
            return $this->calculateBrandDashboardStats($brand);
        });
    }

    /**
     * Calculate brand dashboard stats (without cache).
     */
    protected function calculateBrandDashboardStats(User $brand): array
    {
        $campaigns = $brand->campaigns;

        $totalCampaigns = $campaigns->count();
        $activeCampaigns = $campaigns->where('status', 'active')->count();
        $completedCampaigns = $campaigns->where('status', 'completed')->count();
        $cancelledCampaigns = $campaigns->where('status', 'cancelled')->count();

        $totalViews = $campaigns->sum('total_views');
        $totalSpent = $campaigns->sum('total_spent');
        $averageROI = $campaigns->count() > 0 
            ? $campaigns->map(fn($c) => $this->getROI($c))->average() 
            : 0;

        return [
            'total_campaigns' => $totalCampaigns,
            'active_campaigns' => $activeCampaigns,
            'completed_campaigns' => $completedCampaigns,
            'cancelled_campaigns' => $cancelledCampaigns,
            'total_views' => $totalViews,
            'total_spent' => $totalSpent,
            'average_roi' => round($averageROI, 2),
        ];
    }
}

