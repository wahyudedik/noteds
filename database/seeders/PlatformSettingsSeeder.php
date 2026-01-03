<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Marketplace Commission Settings
        PlatformSetting::set(
            'marketplace_commission_enabled',
            config('marketplace.commission_enabled', true),
            'boolean',
            'Enable or disable marketplace commission system'
        );

        PlatformSetting::set(
            'marketplace_commission_percentage',
            config('marketplace.commission_percentage', 5),
            'number',
            'Commission percentage (0-100) applied to marketplace orders'
        );

        PlatformSetting::set(
            'marketplace_commission_flat_fee',
            config('marketplace.commission_flat_fee', 0),
            'number',
            'Flat fee commission amount (in Rupiah) per marketplace transaction'
        );
    }
}