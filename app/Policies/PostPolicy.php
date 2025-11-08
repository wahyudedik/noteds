<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine if user can delete a post.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->hasRole('admin');
    }

    /**
     * Determine if user can update a post.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine if user can pin/unpin a post.
     */
    public function pin(User $user, Post $post): bool
    {
        // Only post owner can pin their own posts
        return $user->id === $post->user_id;
    }
}

