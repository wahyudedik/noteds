<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostAnalytics;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PostAnalyticsService
{
    /**
     * Track a post view.
     *
     * @param Post $post
     * @return void
     */
    public function trackView(Post $post): void
    {
        // Increment total views
        $post->increment('total_views');

        // Record daily analytics
        $today = Carbon::today();
        $analytics = PostAnalytics::firstOrNew([
            'post_id' => $post->id,
            'date' => $today,
        ]);
        $analytics->views_count = ($analytics->views_count ?? 0) + 1;
        $analytics->save();
    }

    /**
     * Get analytics data for a post.
     *
     * @param Post $post
     * @param int $days
     * @return array
     */
    public function getAnalytics(Post $post, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days);

        $dailyAnalytics = PostAnalytics::where('post_id', $post->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        // Calculate totals
        $totals = [
            'views' => $dailyAnalytics->sum('views_count'),
            'upvotes' => $dailyAnalytics->sum('upvotes_count'),
            'downvotes' => $dailyAnalytics->sum('downvotes_count'),
            'comments' => $dailyAnalytics->sum('comments_count'),
            'reposts' => $dailyAnalytics->sum('reposts_count'),
        ];

        // Calculate engagement rate
        $totalEngagement = $totals['upvotes'] + $totals['downvotes'] + $totals['comments'] + $totals['reposts'];
        $engagementRate = $totals['views'] > 0 
            ? round(($totalEngagement / $totals['views']) * 100, 2) 
            : 0;

        return [
            'post' => $post,
            'total_views' => $post->total_views,
            'totals' => $totals,
            'engagement_rate' => $engagementRate,
            'daily' => $dailyAnalytics,
            'period' => $days,
        ];
    }

    /**
     * Aggregate daily analytics from post counts.
     *
     * @param Post $post
     * @param Carbon $date
     * @return void
     */
    public function aggregateDaily(Post $post, Carbon $date): void
    {
        $analytics = PostAnalytics::firstOrNew([
            'post_id' => $post->id,
            'date' => $date,
        ]);
        
        // Only update counts if they haven't been set for today
        // This preserves views_count from tracking
        if (!$analytics->exists || $analytics->created_at->format('Y-m-d') === $date->format('Y-m-d')) {
            $analytics->upvotes_count = $post->upvotes_count;
            $analytics->downvotes_count = $post->downvotes_count;
            $analytics->comments_count = $post->comments_count;
            $analytics->reposts_count = $post->reposts_count;
        }
        
        $analytics->save();
    }

    /**
     * Aggregate analytics for all posts for a specific date.
     *
     * @param Carbon $date
     * @return int
     */
    public function aggregateForDate(Carbon $date): int
    {
        $posts = Post::where('status', 'active')
            ->whereDate('created_at', '<=', $date)
            ->get();

        $processed = 0;
        foreach ($posts as $post) {
            $this->aggregateDaily($post, $date);
            $processed++;
        }

        return $processed;
    }

    /**
     * Export analytics as CSV data.
     *
     * @param Post $post
     * @param int $days
     * @return array
     */
    public function exportCsv(Post $post, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days);

        $analytics = PostAnalytics::where('post_id', $post->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        $rows = [];
        $rows[] = ['Date', 'Views', 'Upvotes', 'Downvotes', 'Comments', 'Reposts'];

        foreach ($analytics as $record) {
            $rows[] = [
                $record->date->format('Y-m-d'),
                $record->views_count,
                $record->upvotes_count,
                $record->downvotes_count,
                $record->comments_count,
                $record->reposts_count,
            ];
        }

        return $rows;
    }
}

