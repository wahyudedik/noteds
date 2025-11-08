<?php

namespace App\Policies;

use App\Models\PostComment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine if user can update a comment.
     */
    public function update(User $user, PostComment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    /**
     * Determine if user can delete a comment.
     */
    public function delete(User $user, PostComment $comment): bool
    {
        return $user->id === $comment->user_id || $user->hasRole('admin');
    }
}

