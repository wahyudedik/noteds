<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Repost;
use App\Models\RepostAnalytics;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RepostAnalyticsService
{
    /**
     * Track repost event.
     */
    public function trackRepost(Repost $repost): void
    {
        $post = $repost->post;
        $today = Carbon::today();

        $analytics = RepostAnalytics::firstOrCreate(
            [
                'post_id' => $post->id,
                'date' => $today,
            ],
            [
                'reposts_count' => 0,
                'quote_reposts_count' => 0,
                'reposts_with_comments_count' => 0,
                'unique_reposters_count' => 0,
            ]
        );

        // Increment counts
        $analytics->increment('reposts_count');

        if ($repost->is_quote_repost) {
            $analytics->increment('quote_reposts_count');
        }

        if ($repost->hasComment()) {
            $analytics->increment('reposts_with_comments_count');
        }

        // Update unique reposters count
        $uniqueReposters = $post->reposts()
            ->distinct('user_id')
            ->count('user_id');
        $analytics->update(['unique_reposters_count' => $uniqueReposters]);
    }

    /**
     * Get breakdown by type (regular, quote, with comment).
     */
    public function getRepostBreakdown(Post $post): array
    {
        $total = $post->reposts()->count();
        $quoteReposts = $post->reposts()->where('is_quote_repost', true)->count();
        $withComments = $post->reposts()->whereNotNull('comment')->count();
        $regular = $total - $quoteReposts;

        return [
            'total' => $total,
            'regular' => $regular,
            'quote' => $quoteReposts,
            'with_comments' => $withComments,
            'percentages' => [
                'regular' => $total > 0 ? round(($regular / $total) * 100, 2) : 0,
                'quote' => $total > 0 ? round(($quoteReposts / $total) * 100, 2) : 0,
                'with_comments' => $total > 0 ? round(($withComments / $total) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Get reposts over time.
     */
    public function getRepostTimeline(Post $post, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = RepostAnalytics::forPost($post);

        if ($startDate && $endDate) {
            $query->forDateRange($startDate, $endDate);
        } else {
            // Default to last 30 days
            $query->forDateRange(Carbon::now()->subDays(30), Carbon::now());
        }

        return $query->orderBy('date', 'asc')->get();
    }

    /**
     * Get list of users who reposted.
     */
    public function getRepostersList(Post $post, ?string $type = null, int $limit = 50): Collection
    {
        $query = $post->reposts()->with('user');

        if ($type === 'quote') {
            $query->where('is_quote_repost', true);
        } elseif ($type === 'with_comment') {
            $query->whereNotNull('comment');
        } elseif ($type === 'regular') {
            $query->where('is_quote_repost', false)->whereNull('comment');
        }

        return $query->latest()->limit($limit)->get();
    }

    /**
     * Calculate engagement metrics.
     */
    public function getRepostEngagement(Post $post): array
    {
        $totalReposts = $post->reposts()->count();
        $uniqueReposters = $post->reposts()->distinct('user_id')->count('user_id');
        $quoteReposts = $post->reposts()->where('is_quote_repost', true)->count();
        $repostsWithComments = $post->reposts()->whereNotNull('comment')->count();

        // Calculate average reposts per day
        $daysSinceCreation = max(1, Carbon::parse($post->created_at)->diffInDays(Carbon::now()));
        $avgRepostsPerDay = $totalReposts / $daysSinceCreation;

        // Calculate engagement rate (reposts / views, if views available)
        $engagementRate = $post->total_views > 0 
            ? round(($totalReposts / $post->total_views) * 100, 2) 
            : 0;

        return [
            'total_reposts' => $totalReposts,
            'unique_reposters' => $uniqueReposters,
            'quote_reposts' => $quoteReposts,
            'reposts_with_comments' => $repostsWithComments,
            'avg_reposts_per_day' => round($avgRepostsPerDay, 2),
            'engagement_rate' => $engagementRate,
            'days_since_creation' => $daysSinceCreation,
        ];
    }

    /**
     * Get users who reposted most.
     */
    public function getTopReposters(Post $post, int $limit = 10): Collection
    {
        return $post->reposts()
            ->select('user_id', DB::raw('count(*) as repost_count'))
            ->groupBy('user_id')
            ->with('user')
            ->orderBy('repost_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Aggregate daily statistics.
     */
    public function aggregateDailyStats(Post $post, Carbon $date): void
    {
        $reposts = $post->reposts()
            ->whereDate('created_at', $date)
            ->get();

        $analytics = RepostAnalytics::firstOrCreate(
            [
                'post_id' => $post->id,
                'date' => $date,
            ],
            [
                'reposts_count' => 0,
                'quote_reposts_count' => 0,
                'reposts_with_comments_count' => 0,
                'unique_reposters_count' => 0,
            ]
        );

        $analytics->update([
            'reposts_count' => $reposts->count(),
            'quote_reposts_count' => $reposts->where('is_quote_repost', true)->count(),
            'reposts_with_comments_count' => $reposts->whereNotNull('comment')->count(),
            'unique_reposters_count' => $reposts->pluck('user_id')->unique()->count(),
        ]);
    }
}

