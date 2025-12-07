<?php

namespace Database\Seeders;

use App\Models\LeaderboardSetting;
use Illuminate\Database\Seeder;

class LeaderboardSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'share_points_per_share',
                'label' => 'Points per Share',
                'value' => 10,
                'type' => 'int',
                'description' => 'Poin yang didapat user ketika share produk',
                'category' => 'points',
            ],
            [
                'key' => 'share_points_per_click',
                'label' => 'Points per Click',
                'value' => 5,
                'type' => 'int',
                'description' => 'Poin yang didapat user ketika link di-klik',
                'category' => 'points',
            ],
            [
                'key' => 'share_points_per_purchase',
                'label' => 'Points per Purchase',
                'value' => 50,
                'type' => 'int',
                'description' => 'Poin yang didapat user ketika ada pembelian dari link share',
                'category' => 'points',
            ],
            [
                'key' => 'leaderboard_monthly_point_cap',
                'label' => 'Monthly Point Cap',
                'value' => 10000,
                'type' => 'int',
                'description' => 'Maksimal poin yang bisa dikumpulkan per bulan',
                'category' => 'leaderboard',
            ],
            [
                'key' => 'leaderboard_monthly_target',
                'label' => 'Monthly Target',
                'value' => 10000,
                'type' => 'int',
                'description' => 'Target poin bulanan untuk leaderboard',
                'category' => 'leaderboard',
            ],
            [
                'key' => 'leaderboard_reset_day',
                'label' => 'Reset Day',
                'value' => 1,
                'type' => 'int',
                'description' => 'Hari dalam bulan untuk reset leaderboard (1-31)',
                'category' => 'leaderboard',
            ],
            [
                'key' => 'monthly_reward_rank_1',
                'label' => 'Reward Rank 1',
                'value' => 100000,
                'type' => 'int',
                'description' => 'Hadiah untuk juara 1 leaderboard bulanan',
                'category' => 'rewards',
            ],
            [
                'key' => 'monthly_reward_rank_2',
                'label' => 'Reward Rank 2',
                'value' => 50000,
                'type' => 'int',
                'description' => 'Hadiah untuk juara 2 leaderboard bulanan',
                'category' => 'rewards',
            ],
            [
                'key' => 'monthly_reward_rank_3',
                'label' => 'Reward Rank 3',
                'value' => 25000,
                'type' => 'int',
                'description' => 'Hadiah untuk juara 3 leaderboard bulanan',
                'category' => 'rewards',
            ],
            [
                'key' => 'monthly_reward_top_10',
                'label' => 'Reward Top 10',
                'value' => 5000,
                'type' => 'int',
                'description' => 'Hadiah untuk top 4-10 leaderboard bulanan',
                'category' => 'rewards',
            ],
            [
                'key' => 'monthly_reward_top_50',
                'label' => 'Reward Top 50',
                'value' => 1000,
                'type' => 'int',
                'description' => 'Hadiah untuk top 11-50 leaderboard bulanan',
                'category' => 'rewards',
            ],
            [
                'key' => 'leaderboard_enabled',
                'label' => 'Enable Leaderboard',
                'value' => 1,
                'type' => 'boolean',
                'description' => 'Aktifkan/nonaktifkan sistem leaderboard',
                'category' => 'leaderboard',
            ],
            [
                'key' => 'duplicate_share_prevention',
                'label' => 'Prevent Duplicate Shares',
                'value' => 1,
                'type' => 'boolean',
                'description' => 'Satu produk hanya bisa di-share 1x untuk mendapat poin',
                'category' => 'leaderboard',
            ],
            [
                'key' => 'auto_transfer_rewards',
                'label' => 'Auto Transfer Rewards',
                'value' => 1,
                'type' => 'boolean',
                'description' => 'Otomatis transfer hadiah ke user tiap bulan',
                'category' => 'rewards',
            ],
            [
                'key' => 'reward_transfer_day',
                'label' => 'Reward Transfer Day',
                'value' => 5,
                'type' => 'int',
                'description' => 'Hari dalam bulan untuk transfer hadiah (1-31)',
                'category' => 'rewards',
            ],
        ];

        foreach ($settings as $setting) {
            LeaderboardSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
