<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestMultiCurrencyUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create USD test user
        $usdUser = User::updateOrCreate(
            ['email' => 'test.usd@example.com'],
            [
                'name' => 'USD Test User',
                'username' => 'test_usd_user',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'suspended_at' => null,
                'locale' => 'en_US',
                'wallet_balance' => 5000000, // 5M IDR = ~$300 USD
                'role' => 'user',
            ]
        );

        // Create SAR test user
        $sarUser = User::updateOrCreate(
            ['email' => 'test.sar@example.com'],
            [
                'name' => 'SAR Test User',
                'username' => 'test_sar_user',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'suspended_at' => null,
                'locale' => 'ar_SA',
                'wallet_balance' => 5000000, // 5M IDR = ~1,125 SAR
                'role' => 'user',
            ]
        );

        // Create IDR test user for comparison
        $idrUser = User::updateOrCreate(
            ['email' => 'test.idr@example.com'],
            [
                'name' => 'IDR Test User',
                'username' => 'test_idr_user',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'suspended_at' => null,
                'locale' => 'id_ID',
                'wallet_balance' => 5000000, // 5M IDR
                'role' => 'user',
            ]
        );

        $this->command->info('Test multi-currency users created successfully!');
        $this->command->info('');
        $this->command->info('Test Users:');
        $this->command->info('  USD User: test.usd@example.com (password: password)');
        $this->command->info('  SAR User: test.sar@example.com (password: password)');
        $this->command->info('  IDR User: test.idr@example.com (password: password)');
        $this->command->info('');
        $this->command->info('Each user has 5M IDR wallet balance:');
        $this->command->info('  USD: ~$300 USD');
        $this->command->info('  SAR: ~1,125 SAR');
        $this->command->info('  IDR: 5,000,000 IDR');
    }
}
