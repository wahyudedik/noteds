<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Premium subscription price
        Setting::updateOrCreate(
            ['key' => 'premium_price_monthly', 'group' => 'subscription'],
            [
                'value' => '25000',
                'type' => 'number',
                'description' => 'Monthly premium subscription price in Rupiah',
            ]
        );

        // Referral signup reward
        Setting::updateOrCreate(
            ['key' => 'referral_reward_signup', 'group' => 'referral'],
            [
                'value' => '5000',
                'type' => 'number',
                'description' => 'Referral signup reward amount in Rupiah',
            ]
        );

        // Referral transaction commission percentage
        Setting::updateOrCreate(
            ['key' => 'referral_reward_commission_percent', 'group' => 'referral'],
            [
                'value' => '5',
                'type' => 'number',
                'description' => 'Referral transaction commission percentage',
            ]
        );

        // Marketplace commission settings
        Setting::updateOrCreate(
            ['key' => 'platform_commission_percent', 'group' => 'marketplace'],
            [
                'value' => '20',
                'type' => 'number',
                'description' => 'Platform commission percentage (deducted from every transaction)',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'creator_commission_percent', 'group' => 'marketplace'],
            [
                'value' => '0',
                'type' => 'number',
                'description' => 'Creator commission percentage (only for original creator on resale)',
            ]
        );

        // Premium buyer exclusive discount
        Setting::updateOrCreate(
            ['key' => 'premium_buyer_discount_percent', 'group' => 'marketplace'],
            [
                'value' => '10',
                'type' => 'number',
                'description' => 'Exclusive discount percentage for premium buyers (applied to all purchases)',
            ]
        );

        // Featured Notes Pricing
        $locations = ['marketplace_grid', 'marketplace_banner', 'landing_hero', 'landing_carousel'];
        $durations = [7, 14, 30];

        $defaultPricing = [
            'marketplace_grid' => [7 => 50000, 14 => 90000, 30 => 150000],
            'marketplace_banner' => [7 => 75000, 14 => 140000, 30 => 250000],
            'landing_hero' => [7 => 150000, 14 => 280000, 30 => 500000],
            'landing_carousel' => [7 => 100000, 14 => 180000, 30 => 350000],
        ];

        foreach ($locations as $location) {
            foreach ($durations as $duration) {
                $key = "featured_price_{$location}_{$duration}";
                Setting::updateOrCreate(
                    ['key' => $key, 'group' => 'featured_notes'],
                    [
                        'value' => (string) $defaultPricing[$location][$duration],
                        'type' => 'number',
                        'description' => "Price for featured note at {$location} for {$duration} days",
                    ]
                );
            }
        }
    }
}

