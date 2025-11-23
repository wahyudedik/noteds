<?php

namespace App\Services;

use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\User;

class WorkspacePermissionService
{
    /**
     * Check if user has permission in workspace
     */
    public function hasPermission(
        User $user,
        Workspace $workspace,
        string $permission
    ): bool {
        // Owner has all permissions
        if ($workspace->owner_id === $user->id) {
            return true;
        }

        $member = WorkspaceMember::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$member) {
            return false;
        }

        // Check role-based permissions
        $role = $member->role;
        if ($role === 'admin') {
            return true; // Admins have all permissions
        }

        // Check specific permission
        return match($permission) {
            'manage_members' => $member->can_manage_members,
            'manage_workspace' => $member->can_manage_workspace,
            'create_notes' => $member->can_create_notes,
            'edit_notes' => $member->can_edit_notes,
            'delete_notes' => $member->can_delete_notes,
            'manage_folders' => $member->can_manage_folders,
            'invite_members' => $member->can_invite_members,
            default => false,
        };
    }

    /**
     * Update member permissions
     */
    public function updatePermissions(
        WorkspaceMember $member,
        array $permissions
    ): void {
        $member->update($permissions);
    }

    /**
     * Set default permissions based on role
     */
    public function setDefaultPermissions(WorkspaceMember $member): void
    {
        $defaults = match($member->role) {
            'admin' => [
                'can_manage_members' => true,
                'can_manage_workspace' => true,
                'can_create_notes' => true,
                'can_edit_notes' => true,
                'can_delete_notes' => true,
                'can_manage_folders' => true,
                'can_invite_members' => true,
            ],
            'editor' => [
                'can_manage_members' => false,
                'can_manage_workspace' => false,
                'can_create_notes' => true,
                'can_edit_notes' => true,
                'can_delete_notes' => false,
                'can_manage_folders' => false,
                'can_invite_members' => false,
            ],
            'viewer' => [
                'can_manage_members' => false,
                'can_manage_workspace' => false,
                'can_create_notes' => false,
                'can_edit_notes' => false,
                'can_delete_notes' => false,
                'can_manage_folders' => false,
                'can_invite_members' => false,
            ],
            default => [
                'can_manage_members' => false,
                'can_manage_workspace' => false,
                'can_create_notes' => true,
                'can_edit_notes' => true,
                'can_delete_notes' => false,
                'can_manage_folders' => false,
                'can_invite_members' => false,
            ],
        };

        $member->update($defaults);
    }
}

