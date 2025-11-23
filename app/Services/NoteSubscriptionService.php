<?php

namespace App\Services;

use App\Models\Note;
use App\Models\NoteSubscription;
use App\Models\NoteSubscriptionPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NoteSubscriptionService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Subscribe user to a note
     */
    public function subscribe(
        User $user,
        Note $note,
        string $tier = 'basic',
        ?float $monthlyPrice = null
    ): NoteSubscription {
        // Check if user already has active subscription
        $existingSubscription = NoteSubscription::where('note_id', $note->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            throw new \Exception('You already have an active subscription to this note.');
        }

        // Get subscription price (from note or default)
        $price = $monthlyPrice ?? $this->getSubscriptionPrice($note, $tier);

        if ($price <= 0) {
            throw new \Exception('Subscription price must be greater than 0.');
        }

        // Create subscription
        $subscription = NoteSubscription::create([
            'note_id' => $note->id,
            'user_id' => $user->id,
            'tier' => $tier,
            'monthly_price' => $price,
            'status' => 'active',
            'started_at' => now(),
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'next_billing_date' => now()->addMonth(),
            'auto_renew' => true,
        ]);

        // Process first payment
        $this->processPayment($subscription);

        return $subscription;
    }

    /**
     * Cancel subscription
     */
    public function cancel(
        NoteSubscription $subscription,
        ?string $reason = null
    ): void {
        if ($subscription->isCancelled()) {
            throw new \Exception('Subscription is already cancelled.');
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'auto_renew' => false,
            'expires_at' => $subscription->current_period_end, // Expires at end of current period
        ]);

        // Notify seller
        $this->notificationService->create(
            $subscription->note->user,
            'subscription_cancelled',
            '📉 Subscription Cancelled',
            "{$subscription->user->name} has cancelled their subscription to '{$subscription->note->title}'.",
            route('notes.show', $subscription->note),
            ['subscription_id' => $subscription->id, 'note_id' => $subscription->note_id]
        );

        // Notify buyer
        $this->notificationService->create(
            $subscription->user,
            'subscription_cancelled_buyer',
            '✅ Subscription Cancelled',
            "Your subscription to '{$subscription->note->title}' has been cancelled. You will retain access until " . $subscription->current_period_end->format('M d, Y') . ".",
            route('subscriptions.show', $subscription),
            ['subscription_id' => $subscription->id]
        );
    }

    /**
     * Renew subscription
     */
    public function renew(NoteSubscription $subscription): bool
    {
        if (!$subscription->canRenew()) {
            return false;
        }

        try {
            return DB::transaction(function () use ($subscription) {
                // Process payment
                $payment = $this->processPayment($subscription);

                if ($payment->isSuccessful()) {
                    // Extend subscription period
                    $newPeriodEnd = $subscription->current_period_end->copy()->addMonth();
                    $subscription->update([
                        'current_period_start' => $subscription->current_period_end,
                        'current_period_end' => $newPeriodEnd,
                        'next_billing_date' => $newPeriodEnd,
                        'billing_cycle_count' => $subscription->billing_cycle_count + 1,
                    ]);

                    // Refresh subscription to get updated dates
                    $subscription->refresh();

                    // Notify user
                    $this->notificationService->create(
                        $subscription->user,
                        'subscription_renewed',
                        '✅ Subscription Renewed',
                        "Your subscription to '{$subscription->note->title}' has been renewed. Next billing: " . $subscription->next_billing_date->format('M d, Y') . ".",
                        route('subscriptions.show', $subscription),
                        ['subscription_id' => $subscription->id]
                    );

                    return true;
                } else {
                    // Payment failed
                    $this->handlePaymentFailure($subscription, $payment);
                    return false;
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to renew subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Process payment for subscription
     */
    public function processPayment(NoteSubscription $subscription): NoteSubscriptionPayment
    {
        $user = $subscription->user;

        // Check if user has sufficient balance
        if ($user->wallet_balance < $subscription->monthly_price) {
            $payment = NoteSubscriptionPayment::create([
                'subscription_id' => $subscription->id,
                'amount' => $subscription->monthly_price,
                'status' => 'failed',
                'period_start' => $subscription->current_period_start,
                'period_end' => $subscription->current_period_end,
                'failure_reason' => 'Insufficient wallet balance',
                'attempt_number' => 1,
            ]);

            return $payment;
        }

        // Deduct from wallet
        $user->decrement('wallet_balance', $subscription->monthly_price);

        // Sync Wallet model
        $wallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
        );
        $wallet->balance = $user->wallet_balance;
        $wallet->save();

        // Create transaction
        $transaction = Transaction::create([
            'buyer_id' => $user->id,
            'seller_id' => $subscription->note->user_id,
            'note_id' => $subscription->note_id,
            'amount' => $subscription->monthly_price,
            'status' => 'success',
            'payment_method' => 'wallet',
            'currency' => config('currency.base_currency', 'IDR'),
        ]);

        // Create payment record
        $payment = NoteSubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'transaction_id' => $transaction->id,
            'amount' => $subscription->monthly_price,
            'status' => 'success',
            'payment_method' => 'wallet',
            'paid_at' => now(),
            'period_start' => $subscription->current_period_start,
            'period_end' => $subscription->current_period_end,
            'attempt_number' => 1,
        ]);

        // Add to seller wallet
        $seller = $subscription->note->user;
        $seller->increment('wallet_balance', $subscription->monthly_price);

        $sellerWallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $seller->id],
            ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
        );
        $sellerWallet->balance = $seller->wallet_balance;
        $sellerWallet->save();

        return $payment;
    }

    /**
     * Handle payment failure
     */
    protected function handlePaymentFailure(
        NoteSubscription $subscription,
        NoteSubscriptionPayment $payment
    ): void {
        // Retry logic can be added here
        // For now, we'll suspend subscription after multiple failures

        $failedPayments = NoteSubscriptionPayment::where('subscription_id', $subscription->id)
            ->where('status', 'failed')
            ->count();

        if ($failedPayments >= 3) {
            $subscription->update([
                'status' => 'suspended',
                'auto_renew' => false,
            ]);

            // Notify user
            $this->notificationService->create(
                $subscription->user,
                'subscription_suspended',
                '⚠️ Subscription Suspended',
                "Your subscription to '{$subscription->note->title}' has been suspended due to payment failures. Please update your payment method.",
                route('subscriptions.show', $subscription),
                ['subscription_id' => $subscription->id]
            );
        } else {
            // Notify user of payment failure
            $this->notificationService->create(
                $subscription->user,
                'subscription_payment_failed',
                '⚠️ Payment Failed',
                "Payment for your subscription to '{$subscription->note->title}' has failed. Please ensure you have sufficient balance.",
                route('subscriptions.show', $subscription),
                ['subscription_id' => $subscription->id]
            );
        }
    }

    /**
     * Auto-renew subscriptions
     */
    public function autoRenewSubscriptions(): int
    {
        $subscriptions = NoteSubscription::where('status', 'active')
            ->where('auto_renew', true)
            ->where('next_billing_date', '<=', now())
            ->whereNull('cancelled_at')
            ->get();

        $renewed = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->renew($subscription)) {
                $renewed++;
            }
        }

        return $renewed;
    }

    /**
     * Expire subscriptions
     */
    public function expireSubscriptions(): int
    {
        $subscriptions = NoteSubscription::where('status', 'active')
            ->where(function ($query) {
                $query->where('expires_at', '<=', now())
                    ->orWhere('current_period_end', '<=', now());
            })
            ->get();

        $expired = 0;

        foreach ($subscriptions as $subscription) {
            $subscription->update([
                'status' => 'expired',
            ]);

            // Notify user
            $this->notificationService->create(
                $subscription->user,
                'subscription_expired',
                '⏰ Subscription Expired',
                "Your subscription to '{$subscription->note->title}' has expired.",
                route('subscriptions.show', $subscription),
                ['subscription_id' => $subscription->id]
            );

            $expired++;
        }

        return $expired;
    }

    /**
     * Get subscription price for note and tier
     */
    public function getSubscriptionPrice(Note $note, string $tier): float
    {
        // Check if note has subscription pricing
        if (isset($note->subscription_pricing)) {
            $pricing = is_string($note->subscription_pricing) 
                ? json_decode($note->subscription_pricing, true) 
                : $note->subscription_pricing;
            
            return $pricing[$tier] ?? $pricing['basic'] ?? 0;
        }

        // Default pricing based on tier
        return match($tier) {
            'premium' => $note->price * 0.5, // 50% of note price
            'basic' => $note->price * 0.3, // 30% of note price
            default => $note->price * 0.3,
        };
    }

    /**
     * Check if user has active subscription to note
     */
    public function userHasSubscription(User $user, Note $note): bool
    {
        return NoteSubscription::where('note_id', $note->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('current_period_end', '>', now())
            ->exists();
    }

    /**
     * Get user's subscription to note
     */
    public function getUserSubscription(User $user, Note $note): ?NoteSubscription
    {
        return NoteSubscription::where('note_id', $note->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
    }
}

