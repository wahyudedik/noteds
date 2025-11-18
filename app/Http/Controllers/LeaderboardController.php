<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function __construct(private LeaderboardService $leaderboardService)
    {
    }

    /**
     * Display leaderboards index page.
     */
    public function index(Request $request): View
    {
        $type = $request->query('type', 'sellers');
        $metric = $request->query('metric', 'revenue');
        $period = $request->query('period', 'all-time');

        $leaderboard = [];
        $title = '';

        switch ($type) {
            case 'sellers':
                $title = 'Top Sellers';
                if ($metric === 'revenue') {
                    $leaderboard = $this->leaderboardService->getTopSellersByRevenue($period);
                } elseif ($metric === 'sales') {
                    $leaderboard = $this->leaderboardService->getTopSellersBySalesCount($period);
                } elseif ($metric === 'ratings') {
                    $leaderboard = $this->leaderboardService->getTopSellersByRatings($period);
                }
                break;

            case 'buyers':
                $title = 'Top Buyers';
                if ($metric === 'purchases') {
                    $leaderboard = $this->leaderboardService->getTopBuyersByPurchaseCount($period);
                } elseif ($metric === 'spending') {
                    $leaderboard = $this->leaderboardService->getTopBuyersBySpending($period);
                }
                break;

            case 'contributors':
                $title = 'Top Contributors';
                if ($metric === 'reviews') {
                    $leaderboard = $this->leaderboardService->getTopContributorsByReviews($period);
                } elseif ($metric === 'forum') {
                    $leaderboard = $this->leaderboardService->getTopContributorsByForumPosts($period);
                } elseif ($metric === 'shares') {
                    $leaderboard = $this->leaderboardService->getTopContributorsByShares($period);
                }
                break;
        }

        return view('leaderboard.index', compact('leaderboard', 'type', 'metric', 'period', 'title'));
    }
}
