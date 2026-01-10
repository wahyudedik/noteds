<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostCollaborator;
use App\Models\User;
use Illuminate\Support\Collection;

class PostCollaborationService
{
    /**
     * Invite a collaborator to a post.
     *
     * @param Post $post
     * @param User $inviter
     * @param User $invitee
     * @param string $role
     * @param array $permissions
     * @return PostCollaborator
     */
    public function inviteCollaborator(
        Post $post,
        User $inviter,
        User $invitee,
        string $role = 'co_author',
        array $permissions = []
    ): PostCollaborator {
        // Check if already invited
        $existing = PostCollaborator::where('post_id', $post->id)
            ->where('user_id', $invitee->id)
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException('User is already invited to collaborate on this post.');
        }

        // Cannot invite yourself
        if ($invitee->id === $inviter->id) {
            throw new \InvalidArgumentException('You cannot invite yourself as a collaborator.');
        }

        // Cannot invite the post owner
        if ($invitee->id === $post->user_id) {
            throw new \InvalidArgumentException('Post owner is already the author.');
        }

        return PostCollaborator::create([
            'post_id' => $post->id,
            'user_id' => $invitee->id,
            'role' => $role,
            'can_edit' => $permissions['can_edit'] ?? true,
            'can_publish' => $permissions['can_publish'] ?? false,
            'invited_at' => now(),
            'status' => 'pending',
        ]);
    }

    /**
     * Accept a collaboration invitation.
     *
     * @param PostCollaborator $collaboration
     * @return bool
     */
    public function acceptInvitation(PostCollaborator $collaboration): bool
    {
        if ($collaboration->status !== 'pending') {
            throw new \InvalidArgumentException('Invitation is not pending.');
        }

        $collaboration->accept();
        return true;
    }

    /**
     * Reject a collaboration invitation.
     *
     * @param PostCollaborator $collaboration
     * @return bool
     */
    public function rejectInvitation(PostCollaborator $collaboration): bool
    {
        if ($collaboration->status !== 'pending') {
            throw new \InvalidArgumentException('Invitation is not pending.');
        }

        $collaboration->reject();
        return true;
    }

    /**
     * Remove a collaborator from a post.
     *
     * @param Post $post
     * @param User $collaborator
     * @param User $remover
     * @return bool
     */
    public function removeCollaborator(Post $post, User $collaborator, User $remover): bool
    {
        // Only post owner can remove collaborators
        if ($post->user_id !== $remover->id) {
            throw new \UnauthorizedException('Only the post owner can remove collaborators.');
        }

        // Cannot remove yourself if you're the owner
        if ($collaborator->id === $post->user_id) {
            throw new \InvalidArgumentException('Cannot remove the post owner.');
        }

        $collaboration = PostCollaborator::where('post_id', $post->id)
            ->where('user_id', $collaborator->id)
            ->first();

        if (!$collaboration) {
            return false;
        }

        return $collaboration->delete();
    }

    /**
     * Update permissions for a collaborator.
     *
     * @param PostCollaborator $collaboration
     * @param array $permissions
     * @return bool
     */
    public function updatePermissions(PostCollaborator $collaboration, array $permissions): bool
    {
        $updates = [];

        if (isset($permissions['can_edit'])) {
            $updates['can_edit'] = (bool) $permissions['can_edit'];
        }

        if (isset($permissions['can_publish'])) {
            $updates['can_publish'] = (bool) $permissions['can_publish'];
        }

        if (isset($permissions['role'])) {
            $updates['role'] = $permissions['role'];
        }

        if (empty($updates)) {
            return false;
        }

        return $collaboration->update($updates);
    }

    /**
     * Get all collaborators for a post.
     *
     * @param Post $post
     * @return Collection
     */
    public function getCollaborators(Post $post): Collection
    {
        return $post->collaborators()->with('user')->get();
    }

    /**
     * Check if user can edit the post.
     *
     * @param Post $post
     * @param User $user
     * @return bool
     */
    public function canUserEdit(Post $post, User $user): bool
    {
        return $post->canUserEdit($user);
    }

    /**
     * Check if user can publish the post.
     *
     * @param Post $post
     * @param User $user
     * @return bool
     */
    public function canUserPublish(Post $post, User $user): bool
    {
        return $post->canUserPublish($user);
    }
}

