<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignCollaborator;
use App\Models\User;
use Illuminate\Support\Collection;

class CampaignCollaborationService
{
    /**
     * Invite a collaborator to a campaign.
     */
    public function inviteCollaborator(
        Campaign $campaign,
        User $inviter,
        User $invitee,
        string $role = 'co_creator',
        array $permissions = []
    ): CampaignCollaborator {
        // Check if already invited
        $existing = CampaignCollaborator::where('campaign_id', $campaign->id)
            ->where('user_id', $invitee->id)
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException('User is already invited to collaborate on this campaign.');
        }

        // Cannot invite yourself
        if ($invitee->id === $inviter->id) {
            throw new \InvalidArgumentException('You cannot invite yourself as a collaborator.');
        }

        // Cannot invite the campaign owner
        if ($invitee->id === $campaign->creator_id) {
            throw new \InvalidArgumentException('Campaign owner is already the creator.');
        }

        return CampaignCollaborator::create([
            'campaign_id' => $campaign->id,
            'user_id' => $invitee->id,
            'role' => $role,
            'can_edit' => $permissions['can_edit'] ?? true,
            'can_manage_budget' => $permissions['can_manage_budget'] ?? false,
            'can_activate' => $permissions['can_activate'] ?? false,
            'invited_by' => $inviter->id,
            'invited_at' => now(),
            'status' => 'pending',
        ]);
    }

    /**
     * Accept a collaboration invitation.
     */
    public function acceptInvitation(CampaignCollaborator $collaboration): bool
    {
        if ($collaboration->status !== 'pending') {
            throw new \InvalidArgumentException('Invitation is not pending.');
        }

        $collaboration->accept();
        return true;
    }

    /**
     * Reject a collaboration invitation.
     */
    public function rejectInvitation(CampaignCollaborator $collaboration): bool
    {
        if ($collaboration->status !== 'pending') {
            throw new \InvalidArgumentException('Invitation is not pending.');
        }

        $collaboration->reject();
        return true;
    }

    /**
     * Remove a collaborator from a campaign.
     */
    public function removeCollaborator(Campaign $campaign, User $collaborator, User $remover): bool
    {
        // Only campaign owner can remove collaborators
        if ($campaign->creator_id !== $remover->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only the campaign owner can remove collaborators.');
        }

        // Cannot remove the campaign owner
        if ($collaborator->id === $campaign->creator_id) {
            throw new \InvalidArgumentException('Cannot remove the campaign owner.');
        }

        $collaboration = CampaignCollaborator::where('campaign_id', $campaign->id)
            ->where('user_id', $collaborator->id)
            ->first();

        if (!$collaboration) {
            return false;
        }

        return $collaboration->delete();
    }

    /**
     * Update permissions for a collaborator.
     */
    public function updatePermissions(CampaignCollaborator $collaboration, array $permissions): bool
    {
        $updates = [];

        if (isset($permissions['can_edit'])) {
            $updates['can_edit'] = (bool) $permissions['can_edit'];
        }

        if (isset($permissions['can_manage_budget'])) {
            $updates['can_manage_budget'] = (bool) $permissions['can_manage_budget'];
        }

        if (isset($permissions['can_activate'])) {
            $updates['can_activate'] = (bool) $permissions['can_activate'];
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
     * Get all collaborators for a campaign.
     */
    public function getCollaborators(Campaign $campaign): Collection
    {
        return $campaign->collaborators()->with('user', 'inviter')->get();
    }

    /**
     * Check if user can edit the campaign.
     */
    public function canUserEdit(Campaign $campaign, User $user): bool
    {
        return $campaign->canUserEdit($user);
    }

    /**
     * Check if user can manage budget.
     */
    public function canUserManageBudget(Campaign $campaign, User $user): bool
    {
        return $campaign->canUserManageBudget($user);
    }

    /**
     * Check if user can activate the campaign.
     */
    public function canUserActivate(Campaign $campaign, User $user): bool
    {
        return $campaign->canUserActivate($user);
    }
}

