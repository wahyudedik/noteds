<?php

namespace Database\Seeders;

use App\Models\CommissionTier;
use Illuminate\Database\Seeder;

class CommissionTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Starter',
                'description' => 'Default tier for new sellers',
                'volume_threshold' => 0,
                'platform_fee_percent' => 20,
                'creator_commission_percent' => 0,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Growth',
                'description' => 'For sellers with strong monthly volume',
                'volume_threshold' => 5000000, // 5M IDR
                'platform_fee_percent' => 18,
                'creator_commission_percent' => 2,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'description' => 'Top tier for high performing sellers',
                'volume_threshold' => 20000000, // 20M IDR
                'platform_fee_percent' => 15,
                'creator_commission_percent' => 3,
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tier) {
            CommissionTier::updateOrCreate(
                ['name' => $tier['name']],
                $tier
            );
        }
    }
}

