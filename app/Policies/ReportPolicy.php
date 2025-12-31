<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    /**
     * Determine if the user can report content.
     */
    public function create(User $user): bool
    {
        // Cannot report if banned
        if ($user->is_banned) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can report a post.
     */
    public function reportPost(User $user, $post): bool
    {
        // Cannot report if banned
        if ($user->is_banned) {
            return false;
        }

        // Cannot report own posts
        if ($user->id === $post->user_id) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can report a comment.
     */
    public function reportComment(User $user, $comment): bool
    {
        // Cannot report if banned
        if ($user->is_banned) {
            return false;
        }

        // Cannot report own comments
        if ($user->id === $comment->user_id) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can report another user.
     */
    public function reportUser(User $user, User $targetUser): bool
    {
        // Cannot report if banned
        if ($user->is_banned) {
            return false;
        }

        // Cannot report yourself
        if ($user->id === $targetUser->id) {
            return false;
        }

        return true;
    }
}

