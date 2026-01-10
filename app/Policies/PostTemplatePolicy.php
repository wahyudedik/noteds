<?php

namespace App\Policies;

use App\Models\PostTemplate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostTemplatePolicy
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
    public function view(User $user, PostTemplate $postTemplate): bool
    {
        return $postTemplate->is_public || $user->id === $postTemplate->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PostTemplate $postTemplate): bool
    {
        return $user->id === $postTemplate->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PostTemplate $postTemplate): bool
    {
        return $user->id === $postTemplate->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PostTemplate $postTemplate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PostTemplate $postTemplate): bool
    {
        return false;
    }
}
