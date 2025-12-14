<?php

namespace App\Http\Controllers;

use App\Models\LeaderboardSetting;
use App\Services\ShareToEarnService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShareLeaderboardController extends Controller
{
    public function __construct(private ShareToEarnService $shareToEarnService) {}

    /**
     * Display share leaderboard.
     */
    public function index(Request $request): View
    {
        $month = $request->get('month', now()->format('Y-m'));
        $type = $request->get('type', 'monthly'); // monthly or alltime

        if ($type === 'alltime') {
            $leaderboard = $this->shareToEarnService->getAllTimeLeaderboard(100);
            $title = 'All-Time Share Leaderboard';
        } else {
            $leaderboard = $this->shareToEarnService->getLeaderboard($month, 100);
            $title = 'Monthly Share Leaderboard - ' . date('F Y', strtotime($month . '-01'));
        }

        $user = auth()->user();
        $userRank = null;
        $userPoints = 0;

        if ($user) {
            if ($type === 'alltime') {
                $userPoints = $user->total_share_points;
            } else {
                $userPoints = $user->current_month_share_points;
            }

            // Find user rank
            foreach ($leaderboard as $entry) {
                if ($entry['user'] && $entry['user']->id === $user->id) {
                    $userRank = $entry['rank'];
                    break;
                }
            }
        }

        // Get leaderboard settings
        $settings = [
            'share_points_per_share' => LeaderboardSetting::get('share_points_per_share', 10),
            'share_points_per_click' => LeaderboardSetting::get('share_points_per_click', 5),
            'share_points_per_purchase' => LeaderboardSetting::get('share_points_per_purchase', 50),
            'monthly_reward_rank_1' => LeaderboardSetting::get('monthly_reward_rank_1', 100000),
            'monthly_reward_rank_2' => LeaderboardSetting::get('monthly_reward_rank_2', 50000),
            'monthly_reward_rank_3' => LeaderboardSetting::get('monthly_reward_rank_3', 25000),
            'monthly_reward_top_10' => LeaderboardSetting::get('monthly_reward_top_10', 10000),
            'monthly_reward_top_50' => LeaderboardSetting::get('monthly_reward_top_50', 5000),
            'leaderboard_monthly_point_cap' => LeaderboardSetting::get('leaderboard_monthly_point_cap', 1000),
            'leaderboard_enabled' => LeaderboardSetting::get('leaderboard_enabled', true),
            'duplicate_share_prevention' => LeaderboardSetting::get('duplicate_share_prevention', true),
        ];

        return view('40-shared/share/leaderboard', [
            'leaderboard' => $leaderboard,
            'title' => $title,
            'month' => $month,
            'type' => $type,
            'userRank' => $userRank,
            'userPoints' => $userPoints,
            'settings' => $settings,
        ]);
    }
}
