<?php

namespace App\Services;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrendingService
{
    /**
     * Calculate trending score for a post.
     * 
     * Formula: (engagement * time_decay_factor)
     * - Engagement = upvotes + downvotes + comments + reposts
     * - Time decay: newer posts get higher scores
     * 
     * @param Post $post
     * @return float
     */
    public function calculateTrendingScore(Post $post): float
    {
        $engagement = $post->upvotes_count 
            + $post->downvotes_count 
            + $post->comments_count 
            + $post->reposts_count;

        // Time decay: posts get less trending over time
        // Score decreases exponentially based on hours since creation
        $hoursSinceCreation = Carbon::now()->diffInHours($post->created_at);
        
        // Decay factor: 1.0 for posts less than 1 hour old, decreasing to ~0.1 after 7 days
        // Using exponential decay: e^(-hours/168) where 168 = 7 days in hours
        $decayFactor = exp(-$hoursSinceCreation / 168);
        
        // Minimum decay factor to keep older posts with high engagement visible
        $decayFactor = max($decayFactor, 0.1);
        
        // Calculate trending score
        $trendingScore = $engagement * $decayFactor;
        
        // Boost for recent posts (last 24 hours)
        if ($hoursSinceCreation < 24) {
            $trendingScore *= 1.5;
        }
        
        // Boost for very recent posts (last 6 hours)
        if ($hoursSinceCreation < 6) {
            $trendingScore *= 1.3;
        }
        
        return round($trendingScore, 4);
    }

    /**
     * Calculate and update trending scores for all active posts.
     *
     * @param int $limit Limit number of posts to process (null for all)
     * @return int Number of posts processed
     */
    public function calculateTrendingScores(?int $limit = null): int
    {
        $query = Post::where('status', 'active')
            ->where('created_at', '>=', now()->subDays(30)); // Only process posts from last 30 days
        
        if ($limit) {
            $query->limit($limit);
        }
        
        $posts = $query->get();
        $processed = 0;
        
        foreach ($posts as $post) {
            $trendingScore = $this->calculateTrendingScore($post);
            
            $post->update([
                'trending_score' => $trendingScore,
                'last_trending_calculated_at' => now(),
            ]);
            
            $processed++;
        }
        
        return $processed;
    }

    /**
     * Get trending posts.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrendingPosts(int $limit = 20)
    {
        return Post::with(['user', 'media', 'hashtags', 'poll.options'])
            ->where('status', 'active')
            ->whereNotNull('trending_score')
            ->where('trending_score', '>', 0)
            ->orderByDesc('trending_score')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}


