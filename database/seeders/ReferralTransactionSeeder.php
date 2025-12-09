<?php

namespace Database\Seeders;

use App\Models\Referral;
use App\Models\ReferralTransaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferralTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::role('admin')->first();

        if (!$admin) {
            $this->command->warn('No admin user found. Skipping referral transaction seeding.');
            return;
        }

        // Get some referrals for seeding
        $referrals = Referral::with('referred')->take(20)->get();

        if ($referrals->isEmpty()) {
            $this->command->warn('No referrals found. Skipping referral transaction seeding.');
            return;
        }

        $transactions = [];
        $now = now();

        foreach ($referrals as $referral) {
            $user = $referral->referred;

            if (!$user) {
                continue;
            }

            // Create transaction for signup bonus
            $signupBonus = random_int(1, 3) <= 2; // 66% chance
            if ($signupBonus) {
                $status = ['pending', 'sent', 'sent', 'failed'][random_int(0, 3)]; // 50% sent, 25% pending, 25% failed
                $amount = \App\Models\Setting::getReferralSignupReward();

                $transaction = [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'referral_id' => $referral->id,
                    'user_id' => $user->id,
                    'admin_id' => $admin->id,
                    'amount' => $amount,
                    'type' => 'signup_bonus',
                    'status' => $status,
                    'sent_at' => $status === 'sent' ? now()->subDays(random_int(1, 30)) : null,
                    'notes' => 'Signup bonus for referral ' . $referral->code,
                    'created_at' => now()->subDays(random_int(0, 60)),
                    'updated_at' => now()->subDays(random_int(0, 60)),
                ];

                $transactions[] = $transaction;
            }

            // Create transaction for transaction commission (if they have referrals)
            $hasCommission = random_int(1, 3) <= 2; // 66% chance
            if ($hasCommission) {
                $status = ['pending', 'sent', 'sent', 'failed'][random_int(0, 3)]; // 50% sent, 25% pending, 25% failed
                $baseAmount = random_int(10000, 500000);
                $commissionPercent = \App\Models\Setting::getReferralCommissionPercent();
                $amount = round($baseAmount * ($commissionPercent / 100), 2);

                $transaction = [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'referral_id' => $referral->id,
                    'user_id' => $user->id,
                    'admin_id' => $admin->id,
                    'amount' => $amount,
                    'type' => 'transaction_commission',
                    'status' => $status,
                    'sent_at' => $status === 'sent' ? now()->subDays(random_int(1, 30)) : null,
                    'notes' => 'Commission from referral transaction (base: Rp ' . number_format($baseAmount, 0, ',', '.') . ')',
                    'created_at' => now()->subDays(random_int(0, 30)),
                    'updated_at' => now()->subDays(random_int(0, 30)),
                ];

                $transactions[] = $transaction;
            }
        }

        // Insert all transactions
        if (!empty($transactions)) {
            DB::table('referral_transactions')->insert($transactions);
            $this->command->info('Created ' . count($transactions) . ' referral transactions.');
        } else {
            $this->command->warn('No referral transactions were created.');
        }
    }
}
