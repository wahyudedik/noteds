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
    }
}

