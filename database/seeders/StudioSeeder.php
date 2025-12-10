<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class StudioSeeder extends Seeder
{
    /**
     * Seed Studio-related data.
     */
    public function run(): void
    {
        // Ensure seller role exists (created by RoleSeeder)
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);

        // Create sample seller users (freelancers for Studio)
        $sellers = [
            [
                'name' => 'Seller Creative',
                'email' => 'vendor@noteds.com',
                'password' => bcrypt('password'),
                'role' => 'seller',
                'verification_status' => 'approved',
            ],
            [
                'name' => 'Seller Design',
                'email' => 'vendor2@noteds.com',
                'password' => bcrypt('password'),
                'role' => 'seller',
                'verification_status' => 'approved',
            ],
        ];

        foreach ($sellers as $sellerData) {
            $seller = User::firstOrCreate(
                ['email' => $sellerData['email']],
                $sellerData
            );
            $seller->assignRole('seller');
        }

        // Studio Settings
        $studioSettings = [
            [
                'key' => 'studio_platform_fee_percent',
                'value' => '10',
                'type' => 'number',
                'group' => 'studio',
                'description' => 'Studio platform fee percentage deducted on escrow releases',
            ],
            [
                'key' => 'studio_sla_funding_reminder_days',
                'value' => '3',
                'type' => 'number',
                'group' => 'studio',
                'description' => 'Days before sending funding reminder for quoted orders',
            ],
            [
                'key' => 'studio_email_quote_created',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'studio',
                'description' => 'Send email when quote is created',
            ],
            [
                'key' => 'studio_email_quote_accepted',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'studio',
                'description' => 'Send email when quote is accepted',
            ],
            [
                'key' => 'studio_email_quote_rejected',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'studio',
                'description' => 'Send email when quote is rejected',
            ],
            [
                'key' => 'studio_email_escrow_funded',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'studio',
                'description' => 'Send email when escrow is funded',
            ],
            [
                'key' => 'studio_email_escrow_released',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'studio',
                'description' => 'Send email when escrow is released',
            ],
            [
                'key' => 'studio_email_escrow_refunded',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'studio',
                'description' => 'Send email when escrow is refunded',
            ],
            [
                'key' => 'studio_email_vendor_assigned',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'studio',
                'description' => 'Send email when vendor is assigned to order',
            ],
        ];

        foreach ($studioSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key'], 'group' => $setting['group']],
                $setting
            );
        }

        $this->command->info('Studio seeder completed: Vendor role, sample vendors, and settings created.');
    }
}
