<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LeaderboardService;
use Illuminate\View\View;

class AdminLeaderboardController extends Controller
{
    public function __construct(private LeaderboardService $leaderboardService) {}

    /**
     * Display admin leaderboard report with top rankings.
     */
    public function index(): View
    {
        // Get top 5 for each leaderboard type
        $topSellersByRevenue = $this->leaderboardService->getTopSellersByRevenue('all-time', 5);
        $topSellersByRatings = $this->leaderboardService->getTopSellersByRatings('all-time', 5);
        $topBuyersBySpending = $this->leaderboardService->getTopBuyersBySpending('all-time', 5);
        $topContributorsByReviews = $this->leaderboardService->getTopContributorsByReviews('all-time', 5);

        return view('admin.leaderboard.index', compact(
            'topSellersByRevenue',
            'topSellersByRatings',
            'topBuyersBySpending',
            'topContributorsByReviews'
        ));
    }
}
