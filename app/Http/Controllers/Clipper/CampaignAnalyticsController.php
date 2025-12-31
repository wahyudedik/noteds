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
        $stats = $this->analyticsService->getBrandDashboardStats(auth()->user());
        $campaigns = auth()->user()->campaigns()
            ->with('wallet')
            ->latest()
            ->get();

        return Inertia::render('Clipper/Campaigns/Analytics', [
            'stats' => $stats,
            'campaigns' => $campaigns,
        ]);
    }

    public function show($campaignId)
    {
        $campaign = auth()->user()->campaigns()->findOrFail($campaignId);
        
        $stats = $this->analyticsService->getCampaignStats($campaign);
        $viewsChart = $this->analyticsService->getViewsChart($campaign, '30d');
        $topClips = $this->analyticsService->getTopClips($campaign, 10);

        return Inertia::render('Clipper/Campaigns/AnalyticsShow', [
            'campaign' => $campaign,
            'stats' => $stats,
            'viewsChart' => $viewsChart,
            'topClips' => $topClips,
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
}
