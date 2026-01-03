<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Services\CampaignAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignAnalyticsController extends Controller
{
    public function __construct(
        private CampaignAnalyticsService $analyticsService
    ) {}

    public function index()
    {
        $overallStats = $this->analyticsService->getBrandDashboardStats(auth()->user());
        $campaigns = auth()->user()->campaigns()
            ->with('wallet')
            ->latest()
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'total_views' => $campaign->total_views ?? 0,
                    'total_spent' => (float) ($campaign->total_spent ?? 0),
                    'status' => $campaign->status,
                ];
            });

        return Inertia::render('Clipper/Campaigns/Analytics', [
            'overallStats' => $overallStats,
            'campaigns' => $campaigns,
            'campaign' => null,
            'viewsChartData' => [],
            'topClips' => [],
        ]);
    }

    public function show($campaignId)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($campaignId);
        
        $stats = $this->analyticsService->getCampaignStats($campaign);
        $viewsChart = $this->analyticsService->getViewsChart($campaign, '30d');
        $topClips = $this->analyticsService->getTopClips($campaign, 10);

        // Convert viewsChart array format to object format expected by Analytics.vue
        $viewsChartData = [];
        if (isset($viewsChart['labels']) && isset($viewsChart['data'])) {
            foreach ($viewsChart['labels'] as $index => $label) {
                $viewsChartData[$label] = $viewsChart['data'][$index] ?? 0;
            }
        }

        // Prepare campaign data with stats
        $campaignData = [
            'id' => $campaign->id,
            'title' => $campaign->title,
            'total_views' => $stats['total_views'] ?? $campaign->total_views ?? 0,
            'total_clips' => $stats['total_clips'] ?? $campaign->total_clips ?? 0,
            'total_spent' => $stats['total_spent'] ?? (float) ($campaign->total_spent ?? 0),
            'roi' => $stats['roi'] ?? 0,
            'status' => $campaign->status,
        ];

        return Inertia::render('Clipper/Campaigns/Analytics', [
            'campaign' => $campaignData,
            'viewsChartData' => $viewsChartData,
            'topClips' => $topClips->map(function ($clip) {
                return [
                    'id' => $clip->id,
                    'valid_views' => $clip->valid_views ?? 0,
                    'approved_reward' => (float) ($clip->approved_reward ?? 0),
                ];
            })->toArray(),
            'overallStats' => null,
            'campaigns' => [],
        ]);
    }

    public function getViewsChart($campaignId, Request $request)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($campaignId);
        $period = $request->get('period', '7d');

        $chart = $this->analyticsService->getViewsChart($campaign, $period);

        return response()->json($chart);
    }

    public function getROI($campaignId)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($campaignId);
        $roi = $this->analyticsService->getROI($campaign);

        return response()->json(['roi' => $roi]);
    }

    /**
     * Get live views data for real-time tracking.
     */
    public function getLiveViews($campaignId)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($campaignId);
        
        // Cache for 5 seconds to reduce load
        $cacheKey = "campaign_live_views_{$campaignId}";
        $cached = cache()->get($cacheKey);
        if ($cached) {
            return response()->json($cached);
        }
        
        // Get latest views from all clips in campaign with eager loading
        $clips = $campaign->clips()
            ->with(['viewTrackings' => function ($query) {
                $query->latest('tracked_at')->limit(1);
            }])
            ->get();
        
        $totalViews = 0;
        $validViews = 0;
        $viewsPerClip = [];
        $lastUpdated = null;
        $previousTotalViews = $campaign->total_views ?? 0;
        
        foreach ($clips as $clip) {
            $latestTracking = $clip->viewTrackings->first();
            if ($latestTracking) {
                $totalViews += $latestTracking->views_count;
                if ($latestTracking->is_valid) {
                    $validViews += $latestTracking->views_count;
                }
                
                if (!$lastUpdated || $latestTracking->tracked_at > $lastUpdated) {
                    $lastUpdated = $latestTracking->tracked_at;
                }
            } else {
                // Fallback to clip's stored views
                $totalViews += $clip->total_views ?? 0;
                $validViews += $clip->valid_views ?? 0;
            }
            
            // Check fraud detection status
            $viewValidationService = app(\App\Services\ViewValidationService::class);
            $hasFraud = false;
            try {
                $hasFraud = $viewValidationService->detectFraud($clip);
            } catch (\Exception $e) {
                // Ignore errors
            }
            
            $viewsPerClip[] = [
                'clip_id' => $clip->id,
                'total_views' => $latestTracking->views_count ?? $clip->total_views ?? 0,
                'valid_views' => $clip->valid_views ?? 0,
                'fraud_detected' => $hasFraud,
            ];
        }
        
        // Calculate growth rate
        $growthRate = 0;
        if ($previousTotalViews > 0) {
            $growthRate = (($totalViews - $previousTotalViews) / $previousTotalViews) * 100;
        } elseif ($totalViews > 0) {
            $growthRate = 100; // New views
        }
        
        $result = [
            'total_views' => $totalViews,
            'valid_views' => $validViews,
            'invalid_views' => $totalViews - $validViews,
            'views_per_clip' => $viewsPerClip,
            'last_updated' => $lastUpdated ? $lastUpdated->toIso8601String() : now()->toIso8601String(),
            'campaign_id' => $campaign->id,
            'growth_rate' => round($growthRate, 2),
            'previous_views' => $previousTotalViews,
        ];
        
        // Cache for 5 seconds
        cache()->put($cacheKey, $result, 5);
        
        return response()->json($result);
    }

    /**
     * Get validation details for campaign.
     */
    public function getValidationDetails($campaignId)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($campaignId);
        
        // Cache for 10 seconds
        $cacheKey = "campaign_validation_details_{$campaignId}";
        $cached = cache()->get($cacheKey);
        if ($cached) {
            return response()->json($cached);
        }
        
        // Optimize query with eager loading and limit
        $clips = $campaign->clips()
            ->with(['viewTrackings' => function ($query) {
                $query->latest('tracked_at')->limit(1);
            }])
            ->get();
        
        $totalValidViews = 0;
        $totalInvalidViews = 0;
        $clipsWithFraud = 0;
        $averageStabilityScore = 0;
        $validationDetails = [];
        
        $viewValidationService = app(\App\Services\ViewValidationService::class);
        
        foreach ($clips as $clip) {
            $latestTracking = $clip->viewTrackings->first();
            
            $validViews = $clip->valid_views ?? 0;
            $totalViews = $latestTracking->views_count ?? $clip->total_views ?? 0;
            $invalidViews = $totalViews - $validViews;
            
            $totalValidViews += $validViews;
            $totalInvalidViews += $invalidViews;
            
            // Check for fraud (with error handling)
            $hasFraud = false;
            $stabilityScore = null;
            
            try {
                $hasFraud = $viewValidationService->detectFraud($clip);
                $stabilityScore = $viewValidationService->checkStability($clip);
                if ($stabilityScore !== null) {
                    $averageStabilityScore += $stabilityScore;
                }
            } catch (\Exception $e) {
                // If validation fails, use stored stability score
                $stabilityScore = $latestTracking->stability_score ?? null;
                if ($stabilityScore !== null) {
                    $averageStabilityScore += $stabilityScore;
                }
            }
            
            if ($hasFraud) {
                $clipsWithFraud++;
            }
            
            $validationDetails[] = [
                'clip_id' => $clip->id,
                'valid_views' => $validViews,
                'invalid_views' => $invalidViews,
                'total_views' => $totalViews,
                'stability_score' => $stabilityScore,
                'fraud_detected' => $hasFraud,
            ];
        }
        
        $totalClips = $clips->count();
        $clipsWithStability = $clips->filter(function ($clip) {
            $latestTracking = $clip->viewTrackings->first();
            return $latestTracking && $latestTracking->stability_score !== null;
        })->count();
        
        $averageStabilityScore = $clipsWithStability > 0 ? $averageStabilityScore / $clipsWithStability : 0;
        
        $result = [
            'campaign_id' => $campaign->id,
            'total_valid_views' => $totalValidViews,
            'total_invalid_views' => $totalInvalidViews,
            'total_views' => $totalValidViews + $totalInvalidViews,
            'clips_with_fraud' => $clipsWithFraud,
            'average_stability_score' => round($averageStabilityScore, 2),
            'validation_rate' => ($totalValidViews + $totalInvalidViews) > 0 
                ? round(($totalValidViews / ($totalValidViews + $totalInvalidViews)) * 100, 2) 
                : 0,
            'clips' => $validationDetails,
        ];
        
        // Cache for 10 seconds
        cache()->put($cacheKey, $result, 10);
        
        return response()->json($result);
    }
}
