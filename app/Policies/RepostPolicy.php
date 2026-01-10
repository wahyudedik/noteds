<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\Repost;
use App\Models\User;

class RepostPolicy
{
    /**
     * Determine if the user can create a repost.
     */
    public function create(User $user, Post $post): bool
    {
        // Cannot repost if banned
        if ($user->is_banned) {
            return false;
        }

        // Post must be active
        if ($post->status !== 'active') {
            return false;
        }

        // Cannot repost own post (or can we? Let's allow it)
        return true;
    }

    /**
     * Determine if the user can update repost comment.
     */
    public function updateComment(User $user, Repost $repost): bool
    {
        return $user->id === $repost->user_id;
    }

    /**
     * Determine if the user can update quote repost.
     */
    public function updateQuote(User $user, Repost $repost): bool
    {
        return $user->id === $repost->user_id && $repost->is_quote_repost;
    }

    /**
     * Determine if the user can delete a repost.
     */
    public function delete(User $user, Repost $repost): bool
    {
        return $user->id === $repost->user_id;
    }

    /**
     * Determine if the user can view repost analytics.
     */
    public function viewAnalytics(User $user, Post $post): bool
    {
        // Only post author can view analytics
        return $user->id === $post->user_id;
    }
}
