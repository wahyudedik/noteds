<?php

namespace App\Http\Controllers;

use App\Services\GamificationService;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    public function __construct(
        private GamificationService $service
    ) {}

    public function overview(Request $request)
    {
        $user = $request->user();
        $profile = $this->me($request)->getData(true)['data'];
        $leaderboard = [
            'daily' => $this->service->leaderboard('daily', 10),
            'weekly' => $this->service->leaderboard('weekly', 10),
            'monthly' => $this->service->leaderboard('monthly', 10),
        ];
        return \Inertia\Inertia::render('Gamification/Overview', [
            'profile' => $profile,
            'leaderboard' => $leaderboard,
        ]);
    }
    public function leaderboard(Request $request)
    {
        $request->validate([
            'period' => 'nullable|in:daily,weekly,monthly,alltime',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);
        $period = $request->input('period', 'daily');
        $limit = (int) $request->input('limit', 20);
        return response()->json([
            'data' => $this->service->leaderboard($period, $limit),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $total = $this->service->getUserTotalPoints($user, null);
        $daily = $this->service->getUserTotalPoints($user, 'daily');
        $weekly = $this->service->getUserTotalPoints($user, 'weekly');
        $monthly = $this->service->getUserTotalPoints($user, 'monthly');
        $badges = \App\Models\UserBadge::where('user_id', $user->id)->with('badge')->orderBy('awarded_at', 'desc')->get();
        $levels = \App\Models\UserLevel::where('user_id', $user->id)->with('level')->orderBy('awarded_at', 'desc')->get();
        return response()->json([
            'data' => [
                'points' => [
                    'total' => $total,
                    'daily' => $daily,
                    'weekly' => $weekly,
                    'monthly' => $monthly,
                ],
                'badges' => $badges->map(fn($ub) => [
                    'id' => $ub->badge->id,
                    'name' => $ub->badge->name,
                    'icon' => $ub->badge->icon,
                    'awarded_at' => $ub->awarded_at,
                ]),
                'levels' => $levels->map(fn($ul) => [
                    'id' => $ul->level->id,
                    'name' => $ul->level->name,
                    'min_points' => $ul->level->min_points,
                    'awarded_at' => $ul->awarded_at,
                ]),
            ],
        ]);
    }
}
