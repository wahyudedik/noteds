<?php

namespace App\Policies;

use App\Models\ChatRating;
use App\Models\User;

class ChatRatingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChatRating $chatRating): bool
    {
        return $chatRating->rater_id === $user->id || $chatRating->rated_user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create ratings
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChatRating $chatRating): bool
    {
        return $chatRating->rater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChatRating $chatRating): bool
    {
        return $chatRating->rater_id === $user->id;
    }
}
