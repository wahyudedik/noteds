<?php

namespace App\Jobs;

use App\Models\BuyerSubscription;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionRenewal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public BuyerSubscription $subscription;

    /**
     * Create a new job instance.
     */
    public function __construct(BuyerSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscription = $this->subscription->fresh();

        // Check if subscription should be renewed
        if (!$subscription->canRenew()) {
            Log::info("Subscription {$subscription->id} cannot be renewed. Status: {$subscription->status}");
            return;
        }

        // Check if billing date has arrived
        if ($subscription->next_billing_date && $subscription->next_billing_date->isFuture()) {
            Log::info("Subscription {$subscription->id} renewal not due yet. Next billing: {$subscription->next_billing_date}");
            return;
        }

        $user = $subscription->user;
        $price = $subscription->price;

        // Try to charge from wallet first
        if ($user->wallet_balance >= $price) {
            \DB::transaction(function () use ($subscription, $user, $price) {
                // Deduct from wallet
                $user->wallet_balance -= $price;
                $user->save();

                // Create transaction
                Transaction::create([
                    'buyer_id' => $user->id,
                    'seller_id' => $user->id,
                    'note_id' => null,
                    'amount' => $price,
                    'commission' => 0,
                    'currency' => config('app.currency', 'USD'),
                    'original_amount' => $price,
                    'original_currency' => config('app.currency', 'USD'),
                    'exchange_rate' => 1,
                    'status' => 'success',
                    'payment_method' => 'wallet',
                    'notes' => "Subscription renewal: {$subscription->plan->name} ({$subscription->billing_cycle})",
                ]);

                // Renew subscription
                $subscription->renew();

                Log::info("Subscription {$subscription->id} renewed successfully from wallet.");
            });
        } else {
            // Insufficient balance - mark as past_due
            $subscription->update([
                'status' => 'past_due',
            ]);

            Log::warning("Subscription {$subscription->id} renewal failed due to insufficient wallet balance.");

            // TODO: Send notification to user about failed renewal
        }
    }
}
