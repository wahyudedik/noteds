<?php

namespace App\Services;

use App\Models\GamificationPoint;
use App\Models\UserBadge;
use App\Models\Badge;
use App\Models\Level;
use App\Models\UserLevel;
use App\Models\User;
use App\Models\GamificationConfig;
use Illuminate\Support\Facades\Cache;

class GamificationService
{
    protected array $defaults = [
        'login_daily' => 5,
        'upvote' => 2,
        'post_create' => 10,
        'comment_create' => 5,
        'repost' => 3,
        'invite_accepted' => 15,
    ];

    public function pointsForAction(string $action): int
    {
        $cfg = GamificationConfig::where('key', $action)->first();
        if ($cfg && $cfg->enabled) {
            return (int) $cfg->points;
        }
        return $this->defaults[$action] ?? 0;
    }

    public function awardAction(User $user, string $action, array $meta = []): ?GamificationPoint
    {
        $points = $this->pointsForAction($action);
        if ($points <= 0) {
            return null;
        }
        return $this->awardPoints($user, $action, $points, $meta);
    }

    public function awardPoints(User $user, string $action, int $points, array $meta = []): GamificationPoint
    {
        $record = GamificationPoint::create([
            'user_id' => $user->id,
            'action' => $action,
            'points' => $points,
            'meta' => $meta,
        ]);
        $user->notify(new \App\Notifications\PointsAwardedNotification($points, $action));
        $this->evaluateBadges($user);
        $this->evaluateLevels($user);
        Cache::forget($this->leaderboardCacheKey('daily'));
        Cache::forget($this->leaderboardCacheKey('weekly'));
        Cache::forget($this->leaderboardCacheKey('monthly'));
        Cache::forget($this->leaderboardCacheKey('alltime'));
        return $record;
    }

    public function awardDailyLogin(User $user, int $points = 5): ?GamificationPoint
    {
        $todayExists = GamificationPoint::where('user_id', $user->id)
            ->where('action', 'login_daily')
            ->whereDate('created_at', now()->toDateString())
            ->exists();
        if ($todayExists) {
            return null;
        }
        return $this->awardAction($user, 'login_daily');
    }

    public function getUserTotalPoints(User $user, ?string $period = null): int
    {
        $query = GamificationPoint::where('user_id', $user->id);
        if ($period === 'daily') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($period === 'weekly') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'monthly') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }
        return (int) $query->sum('points');
    }

    public function leaderboard(string $period = 'daily', int $limit = 20): array
    {
        $key = $this->leaderboardCacheKey($period);
        return Cache::remember($key, now()->addMinutes(10), function () use ($period, $limit) {
            $query = GamificationPoint::query();
            if ($period === 'daily') {
                $query->whereDate('created_at', now()->toDateString());
            } elseif ($period === 'weekly') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($period === 'monthly') {
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }
            $rows = $query->selectRaw('user_id, SUM(points) as total')
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->limit($limit)
                ->get();
            $users = User::whereIn('id', $rows->pluck('user_id'))->get()->keyBy('id');
            return $rows->map(function ($r) use ($users) {
                $u = $users[$r->user_id] ?? null;
                return [
                    'user_id' => $r->user_id,
                    'name' => $u?->name,
                    'total' => (int) $r->total,
                ];
            })->toArray();
        });
    }

    protected function leaderboardCacheKey(string $period): string
    {
        return 'leaderboard_' . $period;
    }

    public function evaluateBadges(User $user): void
    {
        $badges = Badge::where('is_active', true)->get();
        $total = $this->getUserTotalPoints($user, null);
        foreach ($badges as $badge) {
            $has = UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->exists();
            if ($has) {
                continue;
            }
            $criteria = $badge->criteria ?? [];
            $minPoints = $criteria['min_points'] ?? null;
            if ($minPoints !== null && $total >= $minPoints) {
                UserBadge::create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                    'awarded_at' => now(),
                ]);
                $user->notify(new \App\Notifications\BadgeUnlockedNotification($badge));
            }
        }
    }

    public function evaluateLevels(User $user): void
    {
        $levels = Level::orderBy('min_points', 'asc')->get();
        $total = $this->getUserTotalPoints($user, null);
        $current = UserLevel::where('user_id', $user->id)->orderBy('awarded_at', 'desc')->first();
        foreach ($levels as $level) {
            if ($total >= $level->min_points && (!$current || $level->min_points > $current->level->min_points)) {
                $ul = UserLevel::create([
                    'user_id' => $user->id,
                    'level_id' => $level->id,
                    'awarded_at' => now(),
                ]);
                $user->notify(new \App\Notifications\LevelUpNotification($level));
                break;
            }
        }
    }
}
