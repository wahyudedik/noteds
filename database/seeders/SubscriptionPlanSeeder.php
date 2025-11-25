<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Perfect for individual buyers who want unlimited access to premium notes',
                'monthly_price' => 9.99,
                'yearly_price' => 99.99,
                'yearly_discount_percent' => 17, // ~17% discount
                'features' => [
                    'Unlimited access to premium notes',
                    '10% discount on all purchases',
                    'Priority customer support',
                    'Early access to new features',
                ],
                'max_team_members' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Ideal for power users and professionals who need advanced features',
                'monthly_price' => 19.99,
                'yearly_price' => 199.99,
                'yearly_discount_percent' => 17,
                'features' => [
                    'Everything in Basic',
                    'Unlimited access to premium notes',
                    '20% discount on all purchases',
                    'Priority customer support',
                    'Early access to new features',
                    'Advanced analytics dashboard',
                    'Export capabilities',
                ],
                'max_team_members' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For teams and organizations that need collaboration features',
                'monthly_price' => 49.99,
                'yearly_price' => 499.99,
                'yearly_discount_percent' => 17,
                'features' => [
                    'Everything in Pro',
                    'Unlimited access to premium notes',
                    '30% discount on all purchases',
                    'Priority customer support',
                    'Early access to new features',
                    'Advanced analytics dashboard',
                    'Export capabilities',
                    'Team collaboration (up to 10 members)',
                    'Shared collections',
                    'Team billing',
                ],
                'max_team_members' => 10,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
