<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Services\PostCollaborationService;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    public function __construct(
        private PostCollaborationService $collaborationService
    ) {}

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
    public function view(User $user, Post $post): bool
    {
        return true; // Posts are public
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
    public function update(User $user, Post $post): bool
    {
        // Owner can always update
        if ($post->user_id === $user->id) {
            return true;
        }

        // Check if user is an accepted collaborator with edit permission
        return $this->collaborationService->canUserEdit($post, $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // Only owner can delete
        return $post->user_id === $user->id;
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, Post $post): bool
    {
        // Owner can always publish
        if ($post->user_id === $user->id) {
            return true;
        }

        // Check if user is an accepted collaborator with publish permission
        return $this->collaborationService->canUserPublish($post, $user);
    }

    /**
     * Determine whether the user can invite collaborators.
     */
    public function inviteCollaborator(User $user, Post $post): bool
    {
        // Only owner can invite collaborators
        return $post->user_id === $user->id;
    }

    /**
     * Determine whether the user can remove collaborators.
     */
    public function removeCollaborator(User $user, Post $post): bool
    {
        // Only owner can remove collaborators
        return $post->user_id === $user->id;
    }
}
