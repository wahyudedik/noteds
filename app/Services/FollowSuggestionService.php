<?php

namespace App\Services;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FollowSuggestionService
{
    /**
     * Weight distribution for scoring algorithm.
     */
    private const MUTUAL_FOLLOWS_WEIGHT = 0.40;
    private const ENGAGEMENT_WEIGHT = 0.25;
    private const CONTENT_SIMILARITY_WEIGHT = 0.20;
    private const CATEGORY_MATCH_WEIGHT = 0.15;

    /**
     * Get AI-powered follow suggestions.
     */
    public function getSuggestions(User $user, int $limit = 10): Collection
    {
        $cacheKey = "follow_suggestions:{$user->id}:{$limit}";

        return Cache::remember($cacheKey, 3600, function () use ($user, $limit) {
            // Get users that current user is already following
            $followingIds = Follow::where('follower_id', $user->id)
                ->pluck('following_id')
                ->push($user->id) // Exclude self
                ->toArray();

            // Get candidate users (not already following and not banned)
            $candidates = User::whereNotIn('id', $followingIds)
                ->where('is_banned', false)
                ->with('categories')
                ->get();

            if ($candidates->isEmpty()) {
                return collect();
            }

            // Calculate scores for each candidate
            $scoredCandidates = $candidates->map(function ($candidate) use ($user) {
                $scores = [
                    'mutual_follows' => $this->calculateMutualFollowsScore($user, $candidate),
                    'engagement' => $this->calculateEngagementScore($user, $candidate),
                    'content_similarity' => $this->calculateContentSimilarityScore($user, $candidate),
                    'category_match' => $this->calculateCategoryMatchScore($user, $candidate),
                ];

                $finalScore = $this->calculateFinalScore($scores);

                return [
                    'user' => $candidate,
                    'scores' => $scores,
                    'final_score' => $finalScore,
                ];
            });

            // Sort by final score and return top results
            return $scoredCandidates
                ->sortByDesc('final_score')
                ->take($limit)
                ->values();
        });
    }

    /**
     * Calculate mutual follows score (0-1).
     */
    public function calculateMutualFollowsScore(User $user, User $suggested): float
    {
        // Get users that both are following
        $userFollowingIds = Follow::where('follower_id', $user->id)
            ->pluck('following_id');

        $suggestedFollowingIds = Follow::where('follower_id', $suggested->id)
            ->pluck('following_id');

        $mutualIds = $userFollowingIds->intersect($suggestedFollowingIds);

        if ($mutualIds->isEmpty()) {
            return 0.0;
        }

        // Normalize: score = min(1.0, mutual_count / 10)
        // Having 10+ mutual follows gives max score
        $mutualCount = $mutualIds->count();
        return min(1.0, $mutualCount / 10.0);
    }

    /**
     * Calculate engagement pattern score (0-1).
     */
    public function calculateEngagementScore(User $user, User $suggested): float
    {
        $userMetrics = $this->getEngagementMetrics($user);
        $suggestedMetrics = $this->getEngagementMetrics($suggested);

        // Calculate similarity in engagement patterns
        $similarity = 0.0;

        // Post frequency similarity
        $postFreqDiff = abs($userMetrics['avg_posts_per_month'] - $suggestedMetrics['avg_posts_per_month']);
        $maxPostFreq = max($userMetrics['avg_posts_per_month'], $suggestedMetrics['avg_posts_per_month'], 1);
        $similarity += (1.0 - min(1.0, $postFreqDiff / $maxPostFreq)) * 0.4;

        // Average upvotes similarity
        $upvoteDiff = abs($userMetrics['avg_upvotes'] - $suggestedMetrics['avg_upvotes']);
        $maxUpvotes = max($userMetrics['avg_upvotes'], $suggestedMetrics['avg_upvotes'], 1);
        $similarity += (1.0 - min(1.0, $upvoteDiff / $maxUpvotes)) * 0.3;

        // Interaction rate similarity
        $interactionDiff = abs($userMetrics['interaction_rate'] - $suggestedMetrics['interaction_rate']);
        $similarity += (1.0 - min(1.0, $interactionDiff)) * 0.3;

        return min(1.0, $similarity);
    }

    /**
     * Calculate content similarity score (0-1).
     */
    public function calculateContentSimilarityScore(User $user, User $suggested): float
    {
        $similarity = 0.0;
        $factors = 0;

        // Hashtag similarity
        $userHashtags = $user->posts()
            ->with('hashtags')
            ->get()
            ->pluck('hashtags')
            ->flatten()
            ->pluck('name')
            ->unique();

        $suggestedHashtags = $suggested->posts()
            ->with('hashtags')
            ->get()
            ->pluck('hashtags')
            ->flatten()
            ->pluck('name')
            ->unique();

        if ($userHashtags->isNotEmpty() && $suggestedHashtags->isNotEmpty()) {
            $commonHashtags = $userHashtags->intersect($suggestedHashtags)->count();
            $totalHashtags = $userHashtags->merge($suggestedHashtags)->unique()->count();
            $similarity += ($commonHashtags / max($totalHashtags, 1)) * 0.4;
            $factors += 0.4;
        }

        // Purpose type similarity
        $userPurposeTypes = $user->posts()->pluck('purpose_type')->countBy();
        $suggestedPurposeTypes = $suggested->posts()->pluck('purpose_type')->countBy();

        if ($userPurposeTypes->isNotEmpty() && $suggestedPurposeTypes->isNotEmpty()) {
            $commonPurposeTypes = $userPurposeTypes->intersectByKeys($suggestedPurposeTypes);
            $totalPurposeTypes = $userPurposeTypes->merge($suggestedPurposeTypes)->keys()->unique()->count();
            $similarity += ($commonPurposeTypes->count() / max($totalPurposeTypes, 1)) * 0.3;
            $factors += 0.3;
        }

        // Keyword similarity (simple word frequency from post titles)
        $userKeywords = $this->extractKeywords($user->posts()->pluck('title')->toArray());
        $suggestedKeywords = $this->extractKeywords($suggested->posts()->pluck('title')->toArray());

        if (!empty($userKeywords) && !empty($suggestedKeywords)) {
            $commonKeywords = count(array_intersect_key($userKeywords, $suggestedKeywords));
            $totalKeywords = count(array_merge($userKeywords, $suggestedKeywords));
            $similarity += ($commonKeywords / max($totalKeywords, 1)) * 0.3;
            $factors += 0.3;
        }

        // Normalize by factors used
        return $factors > 0 ? ($similarity / $factors) : 0.0;
    }

    /**
     * Calculate category match score (0-1).
     */
    public function calculateCategoryMatchScore(User $user, User $suggested): float
    {
        $userCategories = $user->categories()->pluck('categories.id');
        $suggestedCategories = $suggested->categories()->pluck('categories.id');

        if ($userCategories->isEmpty() || $suggestedCategories->isEmpty()) {
            return 0.0;
        }

        $commonCategories = $userCategories->intersect($suggestedCategories);
        $totalCategories = $userCategories->merge($suggestedCategories)->unique();

        if ($totalCategories->isEmpty()) {
            return 0.0;
        }

        // Score based on percentage of shared categories
        return min(1.0, $commonCategories->count() / $totalCategories->count());
    }

    /**
     * Calculate final weighted score.
     */
    public function calculateFinalScore(array $scores): float
    {
        return ($scores['mutual_follows'] * self::MUTUAL_FOLLOWS_WEIGHT) +
               ($scores['engagement'] * self::ENGAGEMENT_WEIGHT) +
               ($scores['content_similarity'] * self::CONTENT_SIMILARITY_WEIGHT) +
               ($scores['category_match'] * self::CATEGORY_MATCH_WEIGHT);
    }

    /**
     * Get engagement metrics for a user.
     */
    public function getEngagementMetrics(User $user): array
    {
        $posts = $user->posts()->get();

        if ($posts->isEmpty()) {
            return [
                'post_count' => 0,
                'avg_upvotes' => 0,
                'avg_comments' => 0,
                'avg_posts_per_month' => 0,
                'interaction_rate' => 0,
            ];
        }

        $postCount = $posts->count();
        $avgUpvotes = $posts->avg('upvotes_count') ?? 0;
        $avgComments = $posts->avg('comments_count') ?? 0;

        // Calculate average posts per month
        $firstPost = $posts->min('created_at');
        $monthsActive = max(1, $firstPost ? now()->diffInMonths($firstPost) : 1);
        $avgPostsPerMonth = $postCount / $monthsActive;

        // Interaction rate: (upvotes + comments) / posts
        $totalInteractions = $posts->sum('upvotes_count') + $posts->sum('comments_count');
        $interactionRate = $postCount > 0 ? ($totalInteractions / $postCount) / 100 : 0; // Normalize

        return [
            'post_count' => $postCount,
            'avg_upvotes' => round($avgUpvotes, 2),
            'avg_comments' => round($avgComments, 2),
            'avg_posts_per_month' => round($avgPostsPerMonth, 2),
            'interaction_rate' => min(1.0, $interactionRate),
        ];
    }

    /**
     * Extract keywords from text array.
     */
    private function extractKeywords(array $texts): array
    {
        $allWords = [];
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should'];

        foreach ($texts as $text) {
            $words = str_word_count(strtolower($text), 1);
            foreach ($words as $word) {
                if (strlen($word) > 3 && !in_array($word, $stopWords)) {
                    $allWords[] = $word;
                }
            }
        }

        return array_count_values($allWords);
    }

    /**
     * Clear suggestions cache for a user.
     */
    public function clearCache(User $user): void
    {
        Cache::forget("follow_suggestions:{$user->id}:10");
        Cache::forget("follow_suggestions:{$user->id}:20");
    }
}

