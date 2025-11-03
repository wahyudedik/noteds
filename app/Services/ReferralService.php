<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    /**
     * Get signup reward amount (dynamic from settings).
     */
    protected function getSignupReward(): float
    {
        return Setting::getReferralSignupReward();
    }

    /**
     * Get transaction reward percentage (dynamic from settings).
     */
    protected function getTransactionRewardPercent(): float
    {
        return Setting::getReferralCommissionPercent();
    }

    /**
     * Process signup reward for referral.
     *
     * @param User $referredUser The newly registered user
     * @return Referral|null Created referral record or null
     */
    public function processSignupReward(User $referredUser): ?Referral
    {
        if (! $referredUser->referred_by) {
            return null;
        }

        $referrer = User::find($referredUser->referred_by);
        if (! $referrer) {
            return null;
        }

        try {
            DB::beginTransaction();

            $signupReward = $this->getSignupReward();

            // Create referral record
            $referral = Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $referredUser->id,
                'reward_type' => 'signup',
                'reward_amount' => $signupReward,
                'status' => 'pending',
                'notes' => 'Signup bonus',
            ]);

            // Add reward to referrer's wallet immediately
            $referrer->increment('wallet_balance', $signupReward);
            
            // Sync Wallet model with user wallet_balance
            $referrerWallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $referrer->id],
                ['balance' => 0]
            );
            $referrerWallet->balance = $referrer->wallet_balance;
            $referrerWallet->save();
            
            // Update referral status to paid
            $referral->update(['status' => 'paid']);

            DB::commit();

            return $referral;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Signup reward processing failed', [
                'referrer_id' => $referrer->id,
                'referred_id' => $referredUser->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Process transaction reward for referral.
     *
     * @param \App\Models\Transaction $transaction The completed transaction
     * @return Referral|null Created referral record or null
     */
    public function processTransactionReward($transaction): ?Referral
    {
        // Get the buyer
        $buyer = User::find($transaction->buyer_id);
        if (! $buyer || ! $buyer->referred_by) {
            return null;
        }

        $referrer = User::find($buyer->referred_by);
        if (! $referrer) {
            return null;
        }

        // Calculate reward (dynamic percentage from settings)
        $commissionPercent = $this->getTransactionRewardPercent();
        $rewardAmount = $transaction->amount * ($commissionPercent / 100);

        try {
            DB::beginTransaction();

            // Check if referral reward already processed for this buyer
            $existingReferral = Referral::where('referrer_id', $referrer->id)
                ->where('referred_id', $buyer->id)
                ->where('reward_type', 'transaction')
                ->first();

            if ($existingReferral) {
                DB::rollBack();
                return null; // Already rewarded
            }

            // Create referral record
            $referral = Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $buyer->id,
                'reward_type' => 'transaction',
                'reward_amount' => $rewardAmount,
                'status' => 'pending',
                'notes' => "Transaction commission from purchase: {$transaction->id}",
            ]);

            // Add reward to referrer's wallet immediately
            $referrer->increment('wallet_balance', $rewardAmount);
            
            // Sync Wallet model with user wallet_balance
            $referrerWallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $referrer->id],
                ['balance' => 0]
            );
            $referrerWallet->balance = $referrer->wallet_balance;
            $referrerWallet->save();
            
            // Update referral status to paid
            $referral->update(['status' => 'paid']);

            DB::commit();

            return $referral;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Transaction reward processing failed', [
                'referrer_id' => $referrer->id,
                'referred_id' => $buyer->id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get reward configuration.
     *
     * @return array
     */
    public function getRewardConfig(): array
    {
        return [
            'signup_reward' => $this->getSignupReward(),
            'transaction_reward_percent' => $this->getTransactionRewardPercent(),
        ];
    }
}
