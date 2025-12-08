<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AffiliatePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for affiliate feature
        $permissions = [
            'view_affiliate_dashboard',
            'create_affiliate_links',
            'manage_affiliate_links',
            'request_affiliate_payout',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Get seller and buyer roles
        $sellerRole = Role::where('name', 'seller')->first();
        $buyerRole = Role::where('name', 'buyer')->first();

        // Assign permissions to seller and buyer roles (not admin)
        if ($sellerRole) {
            $sellerRole->syncPermissions($permissions);
        }

        if ($buyerRole) {
            $buyerRole->syncPermissions($permissions);
        }
    }
}
