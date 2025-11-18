<?php

namespace App\Http\Controllers;

use App\Services\ShareToEarnService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShareLeaderboardController extends Controller
{
    public function __construct(private ShareToEarnService $shareToEarnService)
    {
    }

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

        return view('share.leaderboard', [
            'leaderboard' => $leaderboard,
            'title' => $title,
            'month' => $month,
            'type' => $type,
            'userRank' => $userRank,
            'userPoints' => $userPoints,
        ]);
    }
}
