<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostView;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PostAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $postBaseQuery = $user->posts()->whereNull('parent_id');
        $postIds = (clone $postBaseQuery)->pluck('id');

        $summary = [
            'total_posts' => (clone $postBaseQuery)->count(),
            'total_views' => (clone $postBaseQuery)->sum('views_count'),
            'total_likes' => (clone $postBaseQuery)->sum('likes_count'),
            'total_comments' => (clone $postBaseQuery)->sum('comments_count'),
            'total_shares' => (clone $postBaseQuery)->sum('shares_count'),
        ];

        $startDate = Carbon::today()->subDays(29);

        $dailyViews = collect();
        if ($postIds->isNotEmpty()) {
            $dailyViews = PostView::selectRaw('viewed_date, COUNT(*) as total')
                ->whereIn('post_id', $postIds)
                ->where('viewed_date', '>=', $startDate)
                ->groupBy('viewed_date')
                ->orderBy('viewed_date')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->viewed_date->format('Y-m-d') => $item->total];
                });
        }

        $chartLabels = [];
        $chartData = [];

        for ($date = $startDate->copy(); $date->lte(Carbon::today()); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d M');
            $chartData[] = $dailyViews[$key] ?? 0;
        }

        $topPosts = collect();
        if ($postIds->isNotEmpty()) {
            $topPosts = Post::whereIn('id', $postIds)
                ->select(['id', 'title', 'views_count', 'likes_count', 'comments_count', 'shares_count', 'created_at'])
                ->orderByDesc('views_count')
                ->limit(5)
                ->get();
        }

        $engagementChart = [
            'labels' => $topPosts->pluck('title')->map(fn ($title) => (string) str($title ?? 'Untitled Post')->limit(30)),
            'views' => $topPosts->pluck('views_count'),
            'likes' => $topPosts->pluck('likes_count'),
            'comments' => $topPosts->pluck('comments_count'),
            'shares' => $topPosts->pluck('shares_count'),
        ];

        return view('forum.analytics', [
            'summary' => $summary,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'topPosts' => $topPosts,
            'engagementChart' => $engagementChart,
        ]);
    }
}


