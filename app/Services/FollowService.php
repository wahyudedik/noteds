<?php

namespace App\Services;

use App\Models\User;
use App\Models\Follow;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class FollowService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Follow a user.
     *
     * @param User $follower
     * @param User $following
     * @return Follow
     * @throws \Exception
     */
    public function follow(User $follower, User $following): Follow
    {
        // Prevent self-follow
        if ($follower->id === $following->id) {
            throw new \Exception('You cannot follow yourself.');
        }

        return DB::transaction(function () use ($follower, $following) {
            // Check if already following
            $existingFollow = Follow::where('follower_id', $follower->id)
                ->where('following_id', $following->id)
                ->first();

            if ($existingFollow) {
                return $existingFollow;
            }

            // Create follow relationship
            $follow = Follow::create([
                'follower_id' => $follower->id,
                'following_id' => $following->id,
            ]);

            // Notify the user being followed
            $this->notificationService->notifyNewFollow($following, $follower);

            return $follow;
        });
    }

    /**
     * Unfollow a user.
     *
     * @param User $follower
     * @param User $following
     * @return bool
     */
    public function unfollow(User $follower, User $following): bool
    {
        return Follow::where('follower_id', $follower->id)
            ->where('following_id', $following->id)
            ->delete() > 0;
    }

    /**
     * Check if user is following another user.
     *
     * @param User $follower
     * @param User $following
     * @return bool
     */
    public function isFollowing(User $follower, User $following): bool
    {
        return Follow::where('follower_id', $follower->id)
            ->where('following_id', $following->id)
            ->exists();
    }

    /**
     * Get mutual connections between two users.
     * Optimized to use a single query with joins instead of multiple queries and in-memory operations.
     *
     * @param User $user1
     * @param User $user2
     * @return \Illuminate\Support\Collection
     */
    public function getMutualConnections(User $user1, User $user2): \Illuminate\Support\Collection
    {
        // Optimized: Use a single query with subquery to find mutual connections
        // This avoids loading all following IDs into memory and then querying users separately
        return User::whereIn('id', function ($query) use ($user1, $user2) {
            // Get users that user1 is following
            $query->select('following_id')
                ->from('follows')
                ->where('follower_id', $user1->id)
                ->whereIn('following_id', function ($subQuery) use ($user2) {
                    // Get users that user2 is also following (mutual connections)
                    $subQuery->select('following_id')
                        ->from('follows')
                        ->where('follower_id', $user2->id);
                });
        })->get();
    }

    /**
     * Get follow suggestions for a user.
     *
     * @param User $user
     * @param int $limit
     * @param bool $useAISuggestions
     * @return \Illuminate\Support\Collection
     */
    public function getFollowSuggestions(User $user, int $limit = 10, bool $useAISuggestions = true): \Illuminate\Support\Collection
    {
        // Use AI-powered suggestions if available and enabled
        if ($useAISuggestions && class_exists(\App\Services\FollowSuggestionService::class)) {
            $suggestionService = app(\App\Services\FollowSuggestionService::class);
            $suggestions = $suggestionService->getSuggestions($user, $limit);
            
            // Extract users from suggestions
            return $suggestions->pluck('user');
        }

        // Fallback to basic algorithm (backward compatibility)
        // Get users that the current user is already following
        $followingIds = Follow::where('follower_id', $user->id)
            ->pluck('following_id')
            ->push($user->id); // Exclude self

        // Get users that are followed by people the current user follows
        $suggestedIds = Follow::whereIn('follower_id', $followingIds)
            ->whereNotIn('following_id', $followingIds)
            ->where('following_id', '!=', $user->id)
            ->select('following_id', DB::raw('count(*) as count'))
            ->groupBy('following_id')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('following_id');

        // If not enough suggestions, add random active users
        if ($suggestedIds->count() < $limit) {
            $additionalIds = User::whereNotIn('id', $followingIds)
                ->where('is_banned', false)
                ->whereHas('posts')
                ->inRandomOrder()
                ->limit($limit - $suggestedIds->count())
                ->pluck('id');

            $suggestedIds = $suggestedIds->merge($additionalIds);
        }

        return User::whereIn('id', $suggestedIds)
            ->where('is_banned', false)
            ->limit($limit)
            ->get();
    }

    /**
     * Get followers count for a user.
     *
     * @param User $user
     * @return int
     */
    public function getFollowersCount(User $user): int
    {
        return Follow::where('following_id', $user->id)->count();
    }

    /**
     * Get following count for a user.
     *
     * @param User $user
     * @return int
     */
    public function getFollowingCount(User $user): int
    {
        return Follow::where('follower_id', $user->id)->count();
    }
}

