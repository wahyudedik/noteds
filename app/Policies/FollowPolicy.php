<?php

namespace App\Policies;

use App\Models\User;

class FollowPolicy
{
    /**
     * Determine if the user can follow another user.
     */
    public function follow(User $user, User $targetUser): bool
    {
        // Cannot follow yourself
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Cannot follow if banned
        if ($user->is_banned || $targetUser->is_banned) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can unfollow another user.
     */
    public function unfollow(User $user, User $targetUser): bool
    {
        // Cannot unfollow yourself
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Must be following to unfollow
        return $user->isFollowing($targetUser);
    }

    /**
     * Determine if the user can view followers list.
     */
    public function viewFollowers(User $user, User $targetUser): bool
    {
        // Can always view followers (unless profile is private in future)
        return true;
    }

    /**
     * Determine if the user can view following list.
     */
    public function viewFollowing(User $user, User $targetUser): bool
    {
        // Can always view following list (unless profile is private in future)
        return true;
    }
}

