<?php

namespace App\Policies;

use App\Models\ChatQuickReply;
use App\Models\User;

class ChatQuickReplyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own quick replies
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChatQuickReply $chatQuickReply): bool
    {
        return $chatQuickReply->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create quick replies
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChatQuickReply $chatQuickReply): bool
    {
        return $chatQuickReply->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChatQuickReply $chatQuickReply): bool
    {
        return $chatQuickReply->user_id === $user->id;
    }
}
