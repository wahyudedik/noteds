<?php

namespace App\Policies;

use App\Models\User;

/**
 * Admin Authorization Policy
 * 
 * Controls access to sensitive administrative operations:
 * - User suspension/unsuspension
 * - Permission management
 * - Audit log access
 * - System settings
 * - Data exports
 * - Refund processing
 * 
 * Security Requirements:
 * - Multi-factor authentication (when implemented)
 * - Super admin approval for destructive actions
 * - Complete audit logging of all admin actions
 * - IP whitelist verification
 * - Session timeout for long operations
 */
class AdminPolicy extends BasePolicy
{
    /**
     * Only super admins can access admin panel
     */
    public function accessAdminPanel(User $user): bool
    {
        return $this->isAdmin($user) && $user->hasRole('super_admin');
    }

    /**
     * Only admins can manage users
     */
    public function manageUsers(User $user): bool
    {
        return $this->isAdmin($user) && $user->hasPermission('manage_users');
    }

    /**
     * Only admins can view audit logs
     */
    public function viewAuditLogs(User $user): bool
    {
        return $this->isAdmin($user) && $user->hasPermission('view_audit_logs');
    }

    /**
     * Only super admins can export user data
     */
    public function exportData(User $user): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        if (!$user->hasPermission('export_data')) {
            return false;
        }

        $this->logAccess($user, 'export', 'Data');
        return true;
    }

    /**
     * Only super admins can suspend users
     */
    public function suspendUser(User $user, User $targetUser): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        // Cannot suspend other admins
        if ($targetUser->hasRole('admin') && !$user->hasRole('super_admin')) {
            return false;
        }

        // Cannot suspend self
        if ($user->id === $targetUser->id) {
            return false;
        }

        $this->logAccess($user, 'suspend_user', 'User', [
            'target_user_id' => $targetUser->id,
            'target_user_email' => $targetUser->email,
        ]);
        return true;
    }

    /**
     * Only super admins can unsuspend users
     */
    public function unsuspendUser(User $user, User $targetUser): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        if (!$user->hasPermission('unsuspend_users')) {
            return false;
        }

        $this->logAccess($user, 'unsuspend_user', 'User', [
            'target_user_id' => $targetUser->id,
        ]);
        return true;
    }

    /**
     * Only super admins can manage permissions
     */
    public function managePermissions(User $user): bool
    {
        return $this->isAdmin($user) && $user->hasPermission('manage_permissions');
    }

    /**
     * Only super admins can assign roles
     */
    public function assignRole(User $user, User $targetUser, string $role): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        if (!$user->hasPermission('assign_roles')) {
            return false;
        }

        // Cannot assign admin roles except super admin
        if (in_array($role, ['admin', 'super_admin']) && !$user->hasRole('super_admin')) {
            return false;
        }

        // Cannot assign role to self
        if ($user->id === $targetUser->id) {
            return false;
        }

        $this->logAccess($user, 'assign_role', 'User', [
            'target_user_id' => $targetUser->id,
            'role' => $role,
        ]);
        return true;
    }

    /**
     * Only super admins can process refunds
     */
    public function processRefund(User $user): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        if (!$user->hasPermission('process_refunds')) {
            return false;
        }

        $this->logAccess($user, 'process_refund', 'Refund');
        return true;
    }

    /**
     * Only super admins can modify system settings
     */
    public function modifySettings(User $user): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        if (!$user->hasPermission('modify_settings')) {
            return false;
        }

        $this->logAccess($user, 'modify_settings', 'Settings');
        return true;
    }

    /**
     * Only super admins can delete data (hard delete)
     */
    public function deleteData(User $user): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        if (!$user->hasPermission('delete_data')) {
            return false;
        }

        $this->logAccess($user, 'delete_data', 'Data');
        return true;
    }

    /**
     * Admin can view any dispute
     */
    public function viewDispute(User $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Only admin can resolve disputes
     */
    public function resolveDispute(User $user): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        return $user->hasPermission('resolve_disputes');
    }

    /**
     * Only super admin can modify admin logs
     */
    public function modifyAdminLogs(User $user): bool
    {
        return $this->isAdmin($user) &&
            $user->hasRole('super_admin') &&
            $user->hasPermission('modify_admin_logs');
    }
}
