<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrendingController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->get('period', 'today'); // today|week|month
        $days = match ($period) {
            'today' => 1,
            'week' => 7,
            'month' => 30,
            default => 7,
        };

        $trendingService = app(\App\Services\TrendingService::class);

        // Trending posts (apply privacy filter similarly to PostController@trending)
        $posts = $trendingService->getTrendingPosts(50)->filter(function ($post) use ($days) {
            return $post->created_at >= now()->subDays($days);
        });
        $viewer = $request->user();
        $posts = $posts->filter(function ($post) use ($viewer) {
            $author = $post->user;
            if (!$author) return false;
            $vis = $author->settings?->privacy_settings['posts_visibility'] ?? 'public';
            if ($vis === 'public') return true;
            if ($viewer && $viewer->id === $author->id) return true;
            if ($vis === 'followers') {
                return $viewer ? $viewer->isFollowing($author) : false;
            }
            if ($vis === 'private') {
                return false;
            }
            return true;
        })->values();

        // Trending hashtags by posts_count within period
        $hashtags = \App\Models\Hashtag::withCount(['posts' => function ($q) use ($days) {
            $q->where('status', 'active')->where('created_at', '>=', now()->subDays($days));
        }])->orderByDesc('posts_count')->limit(20)->get();

        // Trending topics (purpose_type) in period
        $topics = \App\Models\Post::where('status', 'active')
            ->where('created_at', '>=', now()->subDays($days))
            ->select('purpose_type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('purpose_type')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $labels = [
                    'idea_business' => '💡 Ide Bisnis',
                    'ask_question' => '❓ Tanya Masalah Bisnis',
                    'share_experience' => '📈 Sharing Pengalaman',
                    'find_partner' => '🤝 Cari Partner',
                    'find_tools' => '🛠 Cari Tools / Resource',
                    'validate_idea' => '🧪 Validasi Ide',
                ];
                return [
                    'id' => $item->purpose_type,
                    'name' => $labels[$item->purpose_type] ?? $item->purpose_type,
                    'count' => $item->count,
                ];
            });

        // Trending users: most posts in period
        $users = \App\Models\User::whereHas('posts', function ($query) use ($days) {
            $query->where('status', 'active')->where('created_at', '>=', now()->subDays($days));
        })
            ->withCount(['posts' => function ($query) use ($days) {
                $query->where('created_at', '>=', now()->subDays($days))->where('status', 'active');
            }])
            ->orderByDesc('posts_count')
            ->limit(20)
            ->get()
            ->map(function ($u) {
                $uArray = $u->toArray();
                $uArray['avatar_url'] = $u->avatar_url;
                return $uArray;
            });

        return Inertia::render('Explore/Trending', [
            'period' => $period,
            'posts' => $posts,
            'hashtags' => $hashtags,
            'topics' => $topics,
            'users' => $users,
        ]);
    }
}
