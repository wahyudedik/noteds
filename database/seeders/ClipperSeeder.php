<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Clip;
use App\Models\CampaignWallet;
use App\Models\CreatorWallet;
use App\Models\ClipperWallet;
use App\Models\ClipViewTracking;
use App\Models\TopUp;
use App\Models\Withdrawal;
use App\Models\ClipperRegistration;
use App\Models\BrandRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClipperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create users
        $admin = User::where('email', 'admin@noteds.com')->first();
        $brandUser = User::where('email', 'budi@example.com')->first();
        
        // Create brand user if doesn't exist
        if (!$brandUser) {
            $brandUser = User::create([
                'id' => (string) Str::uuid(),
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'password' => bcrypt('password'),
                'business_name' => 'TechStart Indonesia',
                'business_field' => 'Software Development',
                'clipper_role' => 'brand',
                'email_verified_at' => now(),
                'role' => 'user',
                'balance' => 10000000, // 10 juta untuk testing
            ]);
        } else {
            $brandUser->update([
                'clipper_role' => 'brand',
                'balance' => 10000000,
            ]);
        }

        // Create clipper users
        $clipperUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $clipper = User::firstOrCreate(
                ['email' => "clipper{$i}@example.com"],
                [
                    'id' => (string) Str::uuid(),
                    'name' => "Clipper User {$i}",
                    'password' => bcrypt('password'),
                    'business_name' => "Content Creator {$i}",
                    'business_field' => 'Content Creation',
                    'portfolio_url' => "https://portfolio{$i}.example.com",
                    'skills' => json_encode(['Video Editing', 'Content Creation', 'Social Media Marketing']),
                    'goals' => json_encode(['Grow audience', 'Increase engagement']),
                    'email_verified_at' => now(),
                    'role' => 'user',
                    'balance' => 0,
                    // Don't set clipper_role directly - let approval system handle it
                ]
            );
            
            // Create approved clipper registration for seeded users (for testing)
            if ($clipper->wasRecentlyCreated || !$clipper->clipperRegistrations()->exists()) {
                $clipperRegistration = ClipperRegistration::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $clipper->id,
                    'portfolio_url' => "https://portfolio{$i}.example.com",
                    'skills' => ['Video Editing', 'Content Creation', 'Social Media Marketing'],
                    'goals' => ['Grow audience', 'Increase engagement'],
                    'status' => 'approved',
                    'approved_at' => now()->subDays(rand(10, 60)),
                    'admin_id' => $admin?->id,
                    'created_at' => now()->subDays(rand(15, 70)),
                    'updated_at' => now()->subDays(rand(10, 60)),
                ]);
                
                // Set clipper_role after approval
                $clipper->update(['clipper_role' => 'clipper']);
            }
            
            $clipperUsers[] = $clipper;
        }

        // Create brand registration (approved)
        if ($admin) {
            // Check if brand registration already exists
            if (!$brandUser->brandRegistrations()->exists()) {
                BrandRegistration::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $brandUser->id,
                    'company_name' => 'TechStart Indonesia',
                    'business_type' => 'E-commerce',
                    'website' => 'https://techstart.id',
                    'social_media' => ['instagram' => '@techstart', 'linkedin' => 'techstart'],
                    'contact_person' => 'Budi Santoso',
                    'phone' => '+6281234567890',
                    'status' => 'approved',
                    'approved_at' => now()->subDays(30),
                    'admin_id' => $admin->id,
                    'created_at' => now()->subDays(35),
                    'updated_at' => now()->subDays(30),
                ]);
            }
        }

        // Note: clipper_profiles table appears to be legacy/unused
        // Current implementation uses User model fields (portfolio_url, skills, goals)
        // Clipper profile data is stored in ClipperRegistration and User model

        // Create creator wallet for brand
        $creatorWallet = CreatorWallet::firstOrCreate(
            ['user_id' => $brandUser->id],
            [
                'id' => (string) Str::uuid(),
                'balance_available' => 10000000,
                'balance_locked' => 0,
            ]
        );

        // Create clipper wallets
        foreach ($clipperUsers as $clipper) {
            ClipperWallet::firstOrCreate(
                ['user_id' => $clipper->id],
                [
                    'id' => (string) Str::uuid(),
                    'balance_pending' => rand(50000, 500000),
                    'balance_available' => rand(100000, 2000000),
                    'balance_withdrawn' => rand(0, 1000000),
                ]
            );
        }

        // Create top ups for brand
        $paymentMethods = ['ewallet', 'virtual_account', 'credit_card'];
        for ($i = 0; $i < 3; $i++) {
            TopUp::create([
                'id' => (string) Str::uuid(),
                'user_id' => $brandUser->id,
                'amount' => rand(1000000, 5000000),
                'status' => 'success',
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'paid_at' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(2, 35)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        // Create campaigns
        $campaigns = [];
        $campaignTitles = [
            'Promote Our New Product Launch',
            'Increase Brand Awareness Campaign',
            'Holiday Special Promotion',
            'Summer Collection Marketing',
            'Tech Innovation Showcase',
        ];

        foreach ($campaignTitles as $index => $title) {
            $campaign = Campaign::create([
                'id' => (string) Str::uuid(),
                'creator_id' => $brandUser->id,
                'title' => $title,
                'description' => "Join us in promoting {$title}. We're looking for creative content creators to help spread the word about our amazing products and services.",
                'cpm' => rand(50000, 200000), // 50k - 200k per 1000 views
                'max_budget' => rand(5000000, 20000000), // 5M - 20M
                'max_reward_per_clipper' => rand(1000000, 5000000), // 1M - 5M
                'duration_days' => rand(7, 60),
                'status' => ['active', 'active', 'draft', 'paused', 'completed'][$index],
                'started_at' => $index < 2 ? now()->subDays(rand(1, 15)) : null,
                'ended_at' => $index < 2 ? now()->addDays(rand(10, 45)) : null,
                'total_views' => rand(10000, 500000),
                'total_clips' => rand(5, 25),
                'total_spent' => rand(1000000, 8000000),
                'created_at' => now()->subDays(rand(10, 60)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);

            // Create campaign wallet for active campaigns
            if (in_array($campaign->status, ['active', 'paused', 'completed'])) {
                CampaignWallet::create([
                    'id' => (string) Str::uuid(),
                    'campaign_id' => $campaign->id,
                    'total_budget' => $campaign->max_budget,
                    'remaining_budget' => $campaign->max_budget - $campaign->total_spent,
                    'locked_amount' => $campaign->max_budget,
                ]);
            }

            $campaigns[] = $campaign;
        }

        // Create clips
        foreach ($campaigns as $campaign) {
            if (!in_array($campaign->status, ['active', 'paused', 'completed'])) {
                continue;
            }

            $clipsPerCampaign = rand(3, 8);
            for ($i = 0; $i < $clipsPerCampaign; $i++) {
                $clipper = $clipperUsers[array_rand($clipperUsers)];
                $status = ['pending', 'approved', 'approved', 'rejected', 'paid'][rand(0, 4)];
                $validViews = rand(1000, 50000);
                $cpm = (float) $campaign->cpm;
                $reward = ($validViews / 1000) * $cpm;
                
                if ($campaign->max_reward_per_clipper) {
                    $reward = min($reward, (float) $campaign->max_reward_per_clipper);
                }

                $platform = ['tiktok', 'instagram', 'youtube', 'tiktok'][rand(0, 3)];
                $platformUsername = str_replace(' ', '', strtolower($clipper->business_name));
                
                $clip = Clip::create([
                    'id' => (string) Str::uuid(),
                    'campaign_id' => $campaign->id,
                    'clipper_id' => $clipper->id,
                    'content_url' => "https://{$platform}.com/@{$platformUsername}/video/{$i}",
                    'platform' => $platform,
                    'platform_content_id' => "video_{$i}_" . Str::random(10),
                    'status' => $status,
                    'valid_views' => $status === 'approved' || $status === 'paid' ? $validViews : 0,
                    'estimated_reward' => $reward,
                    'pending_reward' => $status === 'pending' ? $reward : 0,
                    'approved_reward' => $status === 'approved' || $status === 'paid' ? $reward : 0,
                    'rejected_reward' => 0,
                    'submitted_at' => now()->subDays(rand(1, 20)),
                    'approved_at' => in_array($status, ['approved', 'paid']) ? now()->subDays(rand(1, 15)) : null,
                    'rejected_at' => $status === 'rejected' ? now()->subDays(rand(1, 10)) : null,
                    'paid_at' => $status === 'paid' ? now()->subDays(rand(1, 5)) : null,
                    'rejection_reason' => $status === 'rejected' ? 'Content does not meet campaign requirements.' : null,
                    'created_at' => now()->subDays(rand(1, 20)),
                    'updated_at' => now()->subDays(rand(1, 15)),
                ]);

                // Create view tracking for approved/paid clips
                if (in_array($status, ['approved', 'paid'])) {
                    $trackingDays = rand(5, 15);
                    for ($day = 0; $day < $trackingDays; $day++) {
                        ClipViewTracking::create([
                            'id' => (string) Str::uuid(),
                            'clip_id' => $clip->id,
                            'views_count' => rand(100, 5000),
                            'tracked_at' => now()->subDays($trackingDays - $day),
                            'stability_score' => rand(70, 100) / 100,
                            'is_valid' => true,
                            'created_at' => now()->subDays($trackingDays - $day),
                            'updated_at' => now()->subDays($trackingDays - $day),
                        ]);
                    }
                }
            }
        }

        // Create withdrawals for clippers
        foreach ($clipperUsers as $clipper) {
            $wallet = ClipperWallet::where('user_id', $clipper->id)->first();
            if ($wallet && $wallet->balance_available > 100000) {
                Withdrawal::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $clipper->id,
                    'amount' => rand(500000, min(2000000, (int) $wallet->balance_available)),
                    'method' => 'bank_transfer',
                    'bank_name' => 'Bank BCA',
                    'account_number' => '1234567890',
                    'account_name' => $clipper->name,
                    'status' => ['pending', 'approved', 'completed', 'rejected'][rand(0, 3)],
                    'admin_notes' => rand(0, 1) ? 'Withdrawal processed successfully' : null,
                    'processed_at' => rand(0, 1) ? now()->subDays(rand(1, 7)) : null,
                    'user_type' => 'clipper',
                    'created_at' => now()->subDays(rand(1, 10)),
                    'updated_at' => now()->subDays(rand(0, 7)),
                ]);
            }
        }

        $this->command->info('Clipper seeder completed!');
        $this->command->info('- Created ' . count($clipperUsers) . ' clipper users');
        $this->command->info('- Created ' . count($campaigns) . ' campaigns');
        $this->command->info('- Created ' . Clip::count() . ' clips');
        $this->command->info('- Created ' . ClipViewTracking::count() . ' view trackings');
        $this->command->info('- Created ' . TopUp::count() . ' top ups');
        $this->command->info('- Created ' . Withdrawal::count() . ' withdrawals');
    }
}

