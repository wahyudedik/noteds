<?php

namespace Database\Seeders;

use App\Models\Referral;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReferralSeeder extends Seeder
{
    /**
     * Seed referral relationships and rewards for signup and transactions.
     */
    public function run(): void
    {
        $referrers = User::role('seller')->get();

        if ($referrers->isEmpty()) {
            return;
        }

        $buyers = User::role('buyer')->get();
        $signupReward = Setting::getReferralSignupReward();
        $transactionPercent = Setting::getReferralCommissionPercent();

        // Assign referrers to a subset of buyers and create signup rewards.
        $buyers->take(8)->each(function (User $buyer) use ($referrers, $signupReward) {
            if ($buyer->referred_by) {
                return;
            }

            $referrer = $referrers->random();
            $buyer->referred_by = $referrer->id;
            $buyer->save();

            Referral::updateOrCreate(
                [
                    'referrer_id' => $referrer->id,
                    'referred_id' => $buyer->id,
                    'reward_type' => 'signup',
                ],
                [
                    'reward_amount' => $signupReward,
                    'status' => 'paid',
                    'notes' => 'Signup reward seeded for referral system demo.',
                ]
            );
        });

        if ($transactionPercent <= 0) {
            return;
        }

        // Create referral rewards for seeded transactions where applicable.
        Transaction::query()
            ->whereNotNull('note_id')
            ->where('status', 'success')
            ->get()
            ->each(function (Transaction $transaction) use ($transactionPercent) {
                $buyer = $transaction->buyer;

                if (!$buyer || !$buyer->referred_by) {
                    return;
                }

                $referrerId = $buyer->referred_by;

                Referral::firstOrCreate(
                    [
                        'referrer_id' => $referrerId,
                        'referred_id' => $buyer->id,
                        'reward_type' => 'transaction',
                    ],
                    [
                        'reward_amount' => $transaction->amount * ($transactionPercent / 100),
                        'status' => 'paid',
                        'notes' => 'Transaction commission seeded for referral analytics.',
                    ]
                );
            });
    }
}


