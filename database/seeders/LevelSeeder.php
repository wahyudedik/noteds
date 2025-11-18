<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seller Levels
        Level::updateOrCreate(
            ['slug' => 'bronze-seller'],
            [
                'name' => 'Bronze Seller',
                'type' => 'seller',
                'level_order' => 1,
                'description' => 'Starting your selling journey',
                'icon' => '🥉',
                'color' => 'bronze',
                'commission_discount_percent' => 0,
                'priority_support' => false,
                'early_access' => false,
                'benefits' => json_encode(['Basic seller features']),
                'criteria_type' => 'total_sales',
                'criteria_value' => 0,
                'is_active' => true,
            ]
        );

        Level::updateOrCreate(
            ['slug' => 'silver-seller'],
            [
                'name' => 'Silver Seller',
                'type' => 'seller',
                'level_order' => 2,
                'description' => 'Growing your business',
                'icon' => '🥈',
                'color' => 'silver',
                'commission_discount_percent' => 1,
                'priority_support' => false,
                'early_access' => false,
                'benefits' => json_encode(['1% commission discount', 'Enhanced analytics']),
                'criteria_type' => 'total_sales',
                'criteria_value' => 10,
                'is_active' => true,
            ]
        );

        Level::updateOrCreate(
            ['slug' => 'gold-seller'],
            [
                'name' => 'Gold Seller',
                'type' => 'seller',
                'level_order' => 3,
                'description' => 'Established seller',
                'icon' => '🥇',
                'color' => 'gold',
                'commission_discount_percent' => 2,
                'priority_support' => true,
                'early_access' => false,
                'benefits' => json_encode(['2% commission discount', 'Priority support', 'Advanced analytics']),
                'criteria_type' => 'total_sales',
                'criteria_value' => 50,
                'is_active' => true,
            ]
        );

        Level::updateOrCreate(
            ['slug' => 'platinum-seller'],
            [
                'name' => 'Platinum Seller',
                'type' => 'seller',
                'level_order' => 4,
                'description' => 'Top performer',
                'icon' => '💎',
                'color' => 'platinum',
                'commission_discount_percent' => 3,
                'priority_support' => true,
                'early_access' => true,
                'benefits' => json_encode(['3% commission discount', 'Priority support', 'Early access to features', 'Dedicated account manager']),
                'criteria_type' => 'total_sales',
                'criteria_value' => 100,
                'is_active' => true,
            ]
        );

        Level::updateOrCreate(
            ['slug' => 'diamond-seller'],
            [
                'name' => 'Diamond Seller',
                'type' => 'seller',
                'level_order' => 5,
                'description' => 'Elite seller',
                'icon' => '💠',
                'color' => 'diamond',
                'commission_discount_percent' => 5,
                'priority_support' => true,
                'early_access' => true,
                'benefits' => json_encode(['5% commission discount', 'Priority support', 'Early access to features', 'Dedicated account manager', 'Custom features']),
                'criteria_type' => 'total_sales',
                'criteria_value' => 500,
                'is_active' => true,
            ]
        );

        // Buyer Levels
        Level::updateOrCreate(
            ['slug' => 'explorer'],
            [
                'name' => 'Explorer',
                'type' => 'buyer',
                'level_order' => 1,
                'description' => 'Starting your journey',
                'icon' => '🔍',
                'color' => 'blue',
                'commission_discount_percent' => 0,
                'priority_support' => false,
                'early_access' => false,
                'benefits' => json_encode(['Basic buyer features']),
                'criteria_type' => 'purchase_count',
                'criteria_value' => 0,
                'is_active' => true,
            ]
        );

        Level::updateOrCreate(
            ['slug' => 'collector'],
            [
                'name' => 'Collector',
                'type' => 'buyer',
                'level_order' => 2,
                'description' => 'Building your collection',
                'icon' => '📚',
                'color' => 'green',
                'commission_discount_percent' => 0,
                'priority_support' => false,
                'early_access' => false,
                'benefits' => json_encode(['Enhanced library features', 'Purchase history tracking']),
                'criteria_type' => 'purchase_count',
                'criteria_value' => 5,
                'is_active' => true,
            ]
        );

        Level::updateOrCreate(
            ['slug' => 'enthusiast'],
            [
                'name' => 'Enthusiast',
                'type' => 'buyer',
                'level_order' => 3,
                'description' => 'Active buyer',
                'icon' => '⭐',
                'color' => 'purple',
                'commission_discount_percent' => 0,
                'priority_support' => true,
                'early_access' => false,
                'benefits' => json_encode(['Priority support', 'Advanced library features', 'Exclusive buyer perks']),
                'criteria_type' => 'purchase_count',
                'criteria_value' => 20,
                'is_active' => true,
            ]
        );

        Level::updateOrCreate(
            ['slug' => 'vip-buyer'],
            [
                'name' => 'VIP Buyer',
                'type' => 'buyer',
                'level_order' => 4,
                'description' => 'Valued customer',
                'icon' => '👑',
                'color' => 'gold',
                'commission_discount_percent' => 0,
                'priority_support' => true,
                'early_access' => true,
                'benefits' => json_encode(['Priority support', 'Early access to new notes', 'VIP buyer badge', 'Exclusive discounts']),
                'criteria_type' => 'purchase_count',
                'criteria_value' => 50,
                'is_active' => true,
            ]
        );

        Level::updateOrCreate(
            ['slug' => 'master-buyer'],
            [
                'name' => 'Master Buyer',
                'type' => 'buyer',
                'level_order' => 5,
                'description' => 'Ultimate collector',
                'icon' => '🏆',
                'color' => 'diamond',
                'commission_discount_percent' => 0,
                'priority_support' => true,
                'early_access' => true,
                'benefits' => json_encode(['Priority support', 'Early access to new notes', 'Master buyer badge', 'Exclusive discounts', 'Personalized recommendations']),
                'criteria_type' => 'purchase_count',
                'criteria_value' => 100,
                'is_active' => true,
            ]
        );
    }
}
