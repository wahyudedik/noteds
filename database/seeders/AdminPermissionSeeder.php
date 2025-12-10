<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create admin role if not exists
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Dashboard permissions
        Permission::firstOrCreate(['name' => 'view-admin-dashboard']);

        // User management permissions
        Permission::firstOrCreate(['name' => 'manage-users']);
        Permission::firstOrCreate(['name' => 'delete-users']);
        Permission::firstOrCreate(['name' => 'verify-users']);
        Permission::firstOrCreate(['name' => 'ban-users']);

        // Note management permissions
        Permission::firstOrCreate(['name' => 'manage-notes']);
        Permission::firstOrCreate(['name' => 'delete-notes']);
        Permission::firstOrCreate(['name' => 'approve-notes']);
        Permission::firstOrCreate(['name' => 'feature-notes']);

        // Transaction management permissions
        Permission::firstOrCreate(['name' => 'manage-transactions']);
        Permission::firstOrCreate(['name' => 'export-transactions']);
        Permission::firstOrCreate(['name' => 'refund-transactions']);

        // Withdrawal management permissions
        Permission::firstOrCreate(['name' => 'manage-withdrawals']);
        Permission::firstOrCreate(['name' => 'export-withdrawals']);
        Permission::firstOrCreate(['name' => 'approve-withdrawals']);

        // Forum moderation permissions
        Permission::firstOrCreate(['name' => 'moderate-forum']);
        Permission::firstOrCreate(['name' => 'delete-forum-content']);

        // Report permissions
        Permission::firstOrCreate(['name' => 'view-reports']);
        Permission::firstOrCreate(['name' => 'export-reports']);

        // Settings permissions
        Permission::firstOrCreate(['name' => 'manage-settings']);
        Permission::firstOrCreate(['name' => 'manage-payment-settings']);
        Permission::firstOrCreate(['name' => 'manage-security-settings']);

        // Give all permissions to admin role
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);

        $this->command->info('✅ Admin permissions created and assigned to admin role');
    }
}
