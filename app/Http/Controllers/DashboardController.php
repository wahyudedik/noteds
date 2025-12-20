<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\PostVote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Basic Stats
        $totalPosts = Post::where('user_id', $user->id)->count();
        $totalComments = Comment::where('user_id', $user->id)->count();
        
        $totalUpvotes = PostVote::whereHas('post', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('vote_type', 'upvote')->count();
        
        $totalDownvotes = PostVote::whereHas('post', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('vote_type', 'downvote')->count();

        $totalEngagement = $totalUpvotes + $totalComments;
        $engagementRate = $totalPosts > 0 ? round(($totalEngagement / $totalPosts) * 100, 2) : 0;

        // Detailed Analytics - Last 30 days
        $last30Days = Carbon::now()->subDays(30);
        
        $postsLast30Days = Post::where('user_id', $user->id)
            ->where('created_at', '>=', $last30Days)
            ->count();

        $engagementData = Post::where('user_id', $user->id)
            ->where('created_at', '>=', $last30Days)
            ->get()
            ->map(function ($post) {
                return [
                    'date' => $post->created_at->format('Y-m-d'),
                    'upvotes' => $post->upvotes_count,
                    'comments' => $post->comments_count,
                ];
            })
            ->groupBy('date')
            ->map(function ($items) {
                return [
                    'upvotes' => $items->sum('upvotes'),
                    'comments' => $items->sum('comments'),
                ];
            });

        // Top Performing Posts
        $topPosts = Post::where('user_id', $user->id)
            ->orderByDesc(DB::raw('upvotes_count + comments_count'))
            ->limit(5)
            ->with('user')
            ->get();

        // Activity Timeline
        $recentActivities = collect()
            ->merge(
                Post::where('user_id', $user->id)
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function ($post) {
                        return [
                            'type' => 'post',
                            'title' => $post->title,
                            'created_at' => $post->created_at,
                            'url' => route('posts.show', $post),
                        ];
                    })
            )
            ->merge(
                Comment::where('user_id', $user->id)
                    ->latest()
                    ->limit(5)
                    ->with('post')
                    ->get()
                    ->map(function ($comment) {
                        return [
                            'type' => 'comment',
                            'title' => 'Commented on: ' . $comment->post->title,
                            'created_at' => $comment->created_at,
                            'url' => route('posts.show', $comment->post),
                        ];
                    })
            )
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        // Purpose type distribution
        $purposeTypeStats = Post::where('user_id', $user->id)
            ->select('purpose_type', DB::raw('count(*) as count'))
            ->groupBy('purpose_type')
            ->get()
            ->pluck('count', 'purpose_type');

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_posts' => $totalPosts,
                'total_comments' => $totalComments,
                'total_upvotes' => $totalUpvotes,
                'total_downvotes' => $totalDownvotes,
                'engagement_rate' => $engagementRate,
                'posts_last_30_days' => $postsLast30Days,
            ],
            'engagement_data' => $engagementData,
            'top_posts' => $topPosts,
            'recent_activities' => $recentActivities,
            'purpose_type_stats' => $purposeTypeStats,
        ]);
    }
}
