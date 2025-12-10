<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SellerNotesPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for seller notes and workspaces features
        $permissions = [
            // Notes permissions
            'view_notes',
            'create_notes',
            'edit_notes',
            'delete_notes',
            'publish_notes',
            'manage_notes',
            // Workspaces permissions
            'view_workspaces',
            'create_workspaces',
            'edit_workspaces',
            'delete_workspaces',
            'manage_workspaces',
            'invite_workspace_users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Get seller role
        $sellerRole = Role::where('name', 'seller')->first();

        // Assign all permissions to seller role only
        if ($sellerRole) {
            $sellerRole->syncPermissions(array_merge(
                $sellerRole->permissions()->pluck('name')->toArray(),
                $permissions
            ));
        }
    }
}
