<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsService
{
    /**
     * Get user growth trends.
     */
    public function getUserGrowthTrends(string $period = 'monthly'): array
    {
        $startDate = match($period) {
            'daily' => Carbon::now()->subDays(30),
            'weekly' => Carbon::now()->subWeeks(12),
            'monthly' => Carbon::now()->subMonths(12),
            default => Carbon::now()->subMonths(12),
        };

        $query = User::where('created_at', '>=', $startDate);

        if ($period === 'daily') {
            $data = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            return [
                'labels' => $data->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray(),
                'data' => $data->pluck('count')->toArray(),
            ];
        } elseif ($period === 'weekly') {
            $data = $query->selectRaw('YEAR(created_at) as year, WEEK(created_at) as week, COUNT(*) as count')
                ->groupBy('year', 'week')
                ->orderBy('year')
                ->orderBy('week')
                ->get();
            
            return [
                'labels' => $data->map(fn($item) => "Week {$item->week}, {$item->year}")->toArray(),
                'data' => $data->pluck('count')->toArray(),
            ];
        } else {
            $data = $query->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
            
            return [
                'labels' => $data->map(fn($item) => Carbon::create($item->year, $item->month)->format('M Y'))->toArray(),
                'data' => $data->pluck('count')->toArray(),
            ];
        }
    }

    /**
     * Get post creation trends.
     */
    public function getPostCreationTrends(string $period = 'monthly'): array
    {
        $startDate = match($period) {
            'daily' => Carbon::now()->subDays(30),
            'weekly' => Carbon::now()->subWeeks(12),
            'monthly' => Carbon::now()->subMonths(12),
            default => Carbon::now()->subMonths(12),
        };

        $query = Post::where('created_at', '>=', $startDate);

        if ($period === 'daily') {
            $data = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            return [
                'labels' => $data->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray(),
                'data' => $data->pluck('count')->toArray(),
            ];
        } elseif ($period === 'weekly') {
            $data = $query->selectRaw('YEAR(created_at) as year, WEEK(created_at) as week, COUNT(*) as count')
                ->groupBy('year', 'week')
                ->orderBy('year')
                ->orderBy('week')
                ->get();
            
            return [
                'labels' => $data->map(fn($item) => "Week {$item->week}, {$item->year}")->toArray(),
                'data' => $data->pluck('count')->toArray(),
            ];
        } else {
            $data = $query->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
            
            return [
                'labels' => $data->map(fn($item) => Carbon::create($item->year, $item->month)->format('M Y'))->toArray(),
                'data' => $data->pluck('count')->toArray(),
            ];
        }
    }

    /**
     * Get engagement metrics.
     */
    public function getEngagementMetrics(): array
    {
        $totalPosts = Post::count();
        $totalComments = \App\Models\Comment::count();
        $totalVotes = \App\Models\PostVote::count();
        $totalBookmarks = \App\Models\Bookmark::count();

        return [
            'total_posts' => $totalPosts,
            'total_comments' => $totalComments,
            'total_votes' => $totalVotes,
            'total_bookmarks' => $totalBookmarks,
            'avg_comments_per_post' => $totalPosts > 0 ? round($totalComments / $totalPosts, 2) : 0,
            'avg_votes_per_post' => $totalPosts > 0 ? round($totalVotes / $totalPosts, 2) : 0,
        ];
    }

}

