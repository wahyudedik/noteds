<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StudioPaymentVerificationPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view_orders',          // Can view orders
            'submit_work',          // Vendor: Can submit work
            'approve_work',         // Buyer: Can approve work
            'verify_orders',        // Admin: Can verify and release payments
            'manage_orders',        // Admin: Can manage all orders
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Get or create roles
        $vendorRole = Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);
        $buyerRole = Role::firstOrCreate(['name' => 'buyer', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Assign permissions to vendor role
        $vendorRole->syncPermissions([
            'view_orders',
            'submit_work',
        ]);

        // Assign permissions to buyer role
        $buyerRole->syncPermissions([
            'view_orders',
            'approve_work',
        ]);

        // Assign permissions to admin role
        $adminRole->syncPermissions([
            'view_orders',
            'verify_orders',
            'manage_orders',
        ]);

        $this->command->info('Studio Payment Verification permissions seeded successfully.');
    }
}
