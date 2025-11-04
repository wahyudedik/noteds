<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sellers
        for ($i = 1; $i <= 5; $i++) {
            $seller = User::firstOrCreate(
                ['email' => "seller{$i}@noteds.com"],
                [
                    'name' => "Seller $i",
                    'username' => 'seller' . $i,
                    'password' => Hash::make('password'),
                    'role' => 'seller',
                    'wallet_balance' => rand(50000, 500000),
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode("Seller $i") . '&background=4ade80&color=fff',
                    'bio' => 'Passionate content creator sharing knowledge and insights.',
                    'location' => 'Jakarta, Indonesia',
                ]
            );

            if (!$seller->hasRole('seller')) {
                $seller->assignRole('seller');
            }

            // Generate referral code if not exists
            if (!$seller->referral_code) {
                $seller->generateReferralCode();
            }
        }

        // Create buyers
        for ($i = 1; $i <= 10; $i++) {
            $buyer = User::firstOrCreate(
                ['email' => "buyer{$i}@noteds.com"],
                [
                    'name' => "Buyer $i",
                    'username' => 'buyer' . $i,
                    'password' => Hash::make('password'),
                    'role' => 'buyer',
                    'wallet_balance' => rand(50000, 200000),
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode("Buyer $i") . '&background=60a5fa&color=fff',
                    'bio' => 'Knowledge seeker and avid learner.',
                    'location' => 'Surabaya, Indonesia',
                ]
            );

            if (!$buyer->hasRole('buyer')) {
                $buyer->assignRole('buyer');
            }

            // Generate referral code if not exists
            if (!$buyer->referral_code) {
                $buyer->generateReferralCode();
            }
        }
    }
}
