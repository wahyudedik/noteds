<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostRankingService
{
    public function getTopPosts(string $period = 'week', string $metric = 'engagement', int $perPage = 15, ?string $purposeType = null): LengthAwarePaginator
    {
        $cacheKey = sprintf('posts_top_%s_%s_%s_page_%s', $period, $metric, $purposeType ?: 'all', request('page', 1));
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($period, $metric, $perPage, $purposeType) {
            $query = Post::query()
                ->with(['user'])
                ->withCount(['comments', 'reposts'])
                ->where('status', 'active');

            $start = $this->getStartForPeriod($period);
            if ($start) {
                $query->where('created_at', '>=', $start);
            }
            if ($purposeType && $purposeType !== 'all') {
                $query->where('purpose_type', $purposeType);
            }

            $query->select('*')
                ->selectRaw($this->scoreExpression($metric));

            $query->orderByDesc('score');

            return $query->paginate($perPage)->appends([
                'period' => $period,
                'metric' => $metric,
                'purpose_type' => $purposeType ?: 'all',
            ]);
        });
    }

    private function getStartForPeriod(string $period): ?Carbon
    {
        $now = now();
        return match ($period) {
            'day', '24h', 'today' => $now->copy()->subHours(24),
            'week' => $now->copy()->subDays(7),
            'month' => $now->copy()->subDays(30),
            'quarter' => $now->copy()->subDays(90),
            'year' => $now->copy()->subDays(365),
            'all' => null,
            default => $now->copy()->subDays(7),
        };
    }

    private function scoreExpression(string $metric): string
    {
        $halfLifeHours = 36;
        $driver = DB::getDriverName();
        $timeDecay = $driver === 'sqlite'
            ? "(1 + (max((julianday('now') - julianday(created_at)) * 24, 1) / {$halfLifeHours}))"
            : "POWER(GREATEST(TIMESTAMPDIFF(HOUR, created_at, NOW()), 1) / {$halfLifeHours}, 1.3)";

        return match ($metric) {
            'upvotes' => "((COALESCE(upvotes_count,0) - COALESCE(downvotes_count,0)) + (comments_count) * 0.5 + (reposts_count) * 1.0) / {$timeDecay} as score",
            'mixed' => "((COALESCE(upvotes_count,0) - COALESCE(downvotes_count,0)) * 1.0 + (comments_count) * 0.8 + (reposts_count) * 1.5) / {$timeDecay} as score",
            'engagement' => "((comments_count) * 1.0 + (reposts_count) * 2.0) / {$timeDecay} as score",
            default => "((comments_count) * 1.0 + (reposts_count) * 2.0) / {$timeDecay} as score",
        };
    }
}
