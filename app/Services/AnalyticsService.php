<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostVote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get basic stats for a user
     */
    public function getUserStats(User $user): array
    {
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

        $postsLast30Days = Post::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        return [
            'total_posts' => $totalPosts,
            'total_comments' => $totalComments,
            'total_upvotes' => $totalUpvotes,
            'total_downvotes' => $totalDownvotes,
            'engagement_rate' => $engagementRate,
            'posts_last_30_days' => $postsLast30Days,
        ];
    }

    /**
     * Get engagement data for charts (last N days)
     */
    public function getEngagementData(User $user, int $days = 30): Collection
    {
        $startDate = Carbon::now()->subDays($days);

        return Post::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
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
    }

    /**
     * Get top performing posts for a user
     */
    public function getTopPosts(User $user, int $limit = 5): Collection
    {
        return Post::where('user_id', $user->id)
            ->orderByDesc(DB::raw('upvotes_count + comments_count'))
            ->limit($limit)
            ->with('user')
            ->get();
    }

    /**
     * Get recent activities timeline for a user
     */
    public function getRecentActivities(User $user, int $limit = 10): Collection
    {
        $activities = collect()
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
            ->take($limit)
            ->values();

        return $activities;
    }

    /**
     * Get purpose type distribution stats for a user
     */
    public function getPurposeTypeStats(User $user): Collection
    {
        return Post::where('user_id', $user->id)
            ->select('purpose_type', DB::raw('count(*) as count'))
            ->groupBy('purpose_type')
            ->get()
            ->pluck('count', 'purpose_type');
    }
}
