<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ReferralCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate referral codes for all existing users
        User::whereNull('referral_code')->each(function (User $user) {
            $user->generateReferralCode();
        });

        $this->command->info('Referral codes generated for all users.');
    }
}
