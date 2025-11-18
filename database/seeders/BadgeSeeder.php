<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Milestone Badges
        $milestoneBadges = [
            [
                'name' => 'First Sale',
                'slug' => 'first-sale',
                'description' => 'Made your first sale!',
                'icon' => '🎉',
                'color' => 'green',
                'category' => 'milestone',
                'criteria_type' => 'sales_count',
                'criteria_value' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => '10 Sales',
                'slug' => '10-sales',
                'description' => 'Reached 10 successful sales',
                'icon' => '🌟',
                'color' => 'blue',
                'category' => 'milestone',
                'criteria_type' => 'sales_count',
                'criteria_value' => 10,
                'sort_order' => 2,
            ],
            [
                'name' => '50 Sales',
                'slug' => '50-sales',
                'description' => 'Reached 50 successful sales',
                'icon' => '💎',
                'color' => 'purple',
                'category' => 'milestone',
                'criteria_type' => 'sales_count',
                'criteria_value' => 50,
                'sort_order' => 3,
            ],
            [
                'name' => '100 Sales',
                'slug' => '100-sales',
                'description' => 'Reached 100 successful sales',
                'icon' => '🏆',
                'color' => 'gold',
                'category' => 'milestone',
                'criteria_type' => 'sales_count',
                'criteria_value' => 100,
                'sort_order' => 4,
            ],
            [
                'name' => '500 Sales',
                'slug' => '500-sales',
                'description' => 'Reached 500 successful sales',
                'icon' => '👑',
                'color' => 'gold',
                'category' => 'milestone',
                'criteria_type' => 'sales_count',
                'criteria_value' => 500,
                'sort_order' => 5,
            ],
        ];

        // Quality Badges
        $qualityBadges = [
            [
                'name' => '5-Star Seller',
                'slug' => '5-star-seller',
                'description' => 'Maintained 4.5+ average rating with 10+ reviews',
                'icon' => '⭐',
                'color' => 'yellow',
                'category' => 'quality',
                'criteria_type' => 'rating',
                'criteria_value' => 45, // 4.5 * 10 for easier comparison
                'sort_order' => 10,
            ],
            [
                'name' => 'Top Rated',
                'slug' => 'top-rated',
                'description' => 'Maintained 4.0+ average rating with 50+ reviews',
                'icon' => '🏅',
                'color' => 'orange',
                'category' => 'quality',
                'criteria_type' => 'rating',
                'criteria_value' => 40, // 4.0 * 10
                'sort_order' => 11,
            ],
        ];

        // Community Badges
        $communityBadges = [
            [
                'name' => 'Helpful Reviewer',
                'slug' => 'helpful-reviewer',
                'description' => 'Received 10+ helpful marks on your reviews',
                'icon' => '💬',
                'color' => 'blue',
                'category' => 'community',
                'criteria_type' => 'helpful_reviews',
                'criteria_value' => 10,
                'sort_order' => 20,
            ],
        ];

        foreach ($milestoneBadges as $badge) {
            Badge::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }

        foreach ($qualityBadges as $badge) {
            Badge::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }

        foreach ($communityBadges as $badge) {
            Badge::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }
    }
}
