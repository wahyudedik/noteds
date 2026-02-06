<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $stats = $this->analyticsService->getUserStats($user);
        $engagementData = $this->analyticsService->getEngagementData($user, 30);
        $topPosts = $this->analyticsService->getTopPosts($user, 5);
        $recentActivities = $this->analyticsService->getRecentActivities($user, 10);
        $purposeTypeStats = $this->analyticsService->getPurposeTypeStats($user);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'engagement_data' => $engagementData,
            'top_posts' => $topPosts,
            'recent_activities' => $recentActivities,
            'purpose_type_stats' => $purposeTypeStats,
        ]);
    }
}
