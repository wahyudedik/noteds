<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\ReferralTransaction;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    public function __construct(private NotificationService $notificationService) {}

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

        $currencyService = app(\App\Services\CurrencyService::class);
        $baseCurrency = config('currency.base_currency', 'IDR');
        $referrerCurrency = $currencyService->getUserCurrency($referrer);

        try {
            DB::beginTransaction();

            $signupReward = $this->getSignupReward();

            // Convert reward to referrer's currency if different from base
            $rewardInReferrerCurrency = $signupReward;
            $exchangeRate = 1;
            if ($referrerCurrency !== $baseCurrency) {
                $rewardInReferrerCurrency = $currencyService->convertFromBase($signupReward, $referrerCurrency);
                $exchangeRate = $currencyService->getExchangeRate($baseCurrency, $referrerCurrency);
            }

            // Create referral record
            $referral = Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $referredUser->id,
                'reward_type' => 'signup',
                'reward_amount' => $rewardInReferrerCurrency,
                'status' => 'pending',
                'notes' => 'Signup bonus',
            ]);

            // Create transaction record for audit trail
            \App\Models\Transaction::create([
                'buyer_id' => $referrer->id,
                'seller_id' => null,
                'amount' => $rewardInReferrerCurrency,
                'currency' => $referrerCurrency,
                'original_amount' => $signupReward,
                'original_currency' => $baseCurrency,
                'exchange_rate' => $exchangeRate,
                'status' => 'success',
                'payment_method' => 'referral_bonus',
                'notes' => 'Referral signup bonus from ' . $referredUser->name,
            ]);

            // Add reward to referrer's wallet immediately
            $referrer->increment('wallet_balance', $rewardInReferrerCurrency);

            // Sync Wallet model with user wallet_balance
            $referrerWallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $referrer->id],
                ['balance' => 0, 'currency' => $baseCurrency]
            );
            if ($referrerWallet->currency !== $baseCurrency) {
                $referrerWallet->currency = $baseCurrency;
            }
            $referrerWallet->balance = $referrer->wallet_balance;
            $referrerWallet->save();

            // Update referral status to paid
            $referral->update(['status' => 'paid']);

            DB::commit();

            $this->notificationService->notifyReferralSignup($referrer, $referredUser, $signupReward);

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
            $baseCurrency = config('app.currency', 'USD');
            $referrerWallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $referrer->id],
                ['balance' => 0, 'currency' => $baseCurrency]
            );
            if ($referrerWallet->currency !== $baseCurrency) {
                $referrerWallet->currency = $baseCurrency;
            }
            $referrerWallet->balance = $referrer->wallet_balance;
            $referrerWallet->save();

            // Update referral status to paid
            $referral->update(['status' => 'paid']);

            DB::commit();

            if ($rewardAmount > 0) {
                $this->notificationService->notifyReferralPurchase($referrer, $buyer, $rewardAmount, $commissionPercent);
            }

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

    /**
     * Get all pending referral commissions that need to be processed.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProcessableCommissions()
    {
        return Referral::where('status', 'pending')
            ->with('referrer', 'referred')
            ->get();
    }

    /**
     * Validate if admin wallet has enough balance for total amount.
     *
     * @param float $totalAmount Total amount to deduct
     * @return bool
     */
    public function validateAdminBalance(float $totalAmount): bool
    {
        $admin = User::role('admin')->first();
        if (!$admin) {
            return false;
        }

        return $admin->wallet_balance >= $totalAmount;
    }

    /**
     * Process individual commission - deduct from admin, credit to user.
     *
     * @param Referral $referral The referral record
     * @param User|null $admin The admin user sending the commission
     * @return ReferralTransaction|null
     */
    public function processCommission(Referral $referral, ?User $admin = null): ?ReferralTransaction
    {
        if (!$admin) {
            $admin = User::role('admin')->first();
        }

        if (!$admin) {
            logger()->error('No admin user found for referral commission processing', [
                'referral_id' => $referral->id,
            ]);
            return null;
        }

        try {
            DB::beginTransaction();

            $amount = $referral->reward_amount;

            // Validate admin balance
            if ($admin->wallet_balance < $amount) {
                logger()->warning('Insufficient admin balance for commission', [
                    'referral_id' => $referral->id,
                    'amount' => $amount,
                    'admin_balance' => $admin->wallet_balance,
                ]);
                DB::rollBack();
                return null;
            }

            // Deduct from admin wallet
            $admin->decrement('wallet_balance', $amount);

            // Credit to user wallet
            $user = $referral->referrer;
            $user->increment('wallet_balance', $amount);

            // Update admin wallet model
            $adminWallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $admin->id],
                ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
            );
            $adminWallet->balance = $admin->wallet_balance;
            $adminWallet->save();

            // Update user wallet model
            $baseCurrency = config('currency.base_currency', 'IDR');
            $userWallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => $baseCurrency]
            );
            $userWallet->balance = $user->wallet_balance;
            $userWallet->save();

            // Create referral transaction record
            $transaction = ReferralTransaction::create([
                'referral_id' => $referral->id,
                'user_id' => $user->id,
                'admin_id' => $admin->id,
                'amount' => $amount,
                'type' => $referral->reward_type,
                'status' => 'sent',
                'sent_at' => now(),
                'notes' => "Commission sent from admin. Original: {$referral->notes}",
            ]);

            // Update referral status
            $referral->update(['status' => 'paid']);

            DB::commit();

            logger()->info('Referral commission processed successfully', [
                'referral_id' => $referral->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Referral commission processing failed', [
                'referral_id' => $referral->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Send commission to user wallet.
     *
     * @param User $user The user receiving the commission
     * @param float $amount The amount to send
     * @param string $type The type of commission (signup/transaction)
     * @param string $notes Additional notes
     * @return ReferralTransaction|null
     */
    public function sendToWallet(User $user, float $amount, string $type = 'signup', string $notes = ''): ?ReferralTransaction
    {
        $admin = User::role('admin')->first();
        if (!$admin) {
            return null;
        }

        try {
            DB::beginTransaction();

            // Validate admin balance
            if ($admin->wallet_balance < $amount) {
                DB::rollBack();
                return null;
            }

            // Deduct from admin
            $admin->decrement('wallet_balance', $amount);

            // Credit to user
            $user->increment('wallet_balance', $amount);

            // Sync wallets
            $baseCurrency = config('currency.base_currency', 'IDR');

            $adminWallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $admin->id],
                ['balance' => 0, 'currency' => $baseCurrency]
            );
            $adminWallet->balance = $admin->wallet_balance;
            $adminWallet->save();

            $userWallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => $baseCurrency]
            );
            $userWallet->balance = $user->wallet_balance;
            $userWallet->save();

            // Create transaction record
            $transaction = ReferralTransaction::create([
                'user_id' => $user->id,
                'admin_id' => $admin->id,
                'amount' => $amount,
                'type' => $type,
                'status' => 'sent',
                'sent_at' => now(),
                'notes' => $notes,
            ]);

            DB::commit();

            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Failed to send commission to wallet', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
