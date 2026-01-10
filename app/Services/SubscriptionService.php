<?php

namespace App\Services;

use App\Models\ProductSubscription;
use App\Models\SubscriptionRenewal;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionService
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Create subscription from order.
     */
    public function createSubscription(Order $order): ProductSubscription
    {
        return DB::transaction(function () use ($order) {
            $product = $order->product;

            if (!$product->is_subscription) {
                throw new \Exception('Product is not a subscription product');
            }

            $trialEndsAt = null;
            if ($product->trial_days) {
                $trialEndsAt = Carbon::now()->addDays($product->trial_days);
            }

            $nextBillingDate = $this->calculateNextBillingDate($product, $trialEndsAt);

            $subscription = ProductSubscription::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'product_id' => $product->id,
                'status' => 'active',
                'current_cycle' => 1,
                'total_cycles' => $product->subscription_duration,
                'next_billing_date' => $nextBillingDate,
                'trial_ends_at' => $trialEndsAt,
                'midtrans_subscription_id' => $this->createMidtransSubscription($order, $product),
            ]);

            // Update order
            $order->update([
                'is_subscription_order' => true,
                'subscription_id' => $subscription->id,
            ]);

            return $subscription;
        });
    }

    /**
     * Renew subscription.
     */
    public function renewSubscription(ProductSubscription $subscription): SubscriptionRenewal
    {
        return DB::transaction(function () use ($subscription) {
            if (!$subscription->isActive()) {
                throw new \Exception('Subscription is not active');
            }

            // Check if subscription has reached total cycles
            if ($subscription->total_cycles && $subscription->current_cycle >= $subscription->total_cycles) {
                $subscription->update(['status' => 'expired']);
                throw new \Exception('Subscription has reached maximum cycles');
            }

            $product = $subscription->product;
            $nextBillingDate = $this->calculateNextBillingDate($product);

            // Create renewal order
            $order = Order::create([
                'user_id' => $subscription->user_id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
                'total' => $product->price,
                'status' => 'pending',
                'payment_status' => 'pending',
                'is_subscription_order' => true,
                'subscription_id' => $subscription->id,
            ]);

            // Create renewal record
            $renewal = SubscriptionRenewal::create([
                'subscription_id' => $subscription->id,
                'order_id' => $order->id,
                'cycle_number' => $subscription->current_cycle + 1,
                'billing_date' => now(),
                'amount' => $product->price,
                'status' => 'pending',
            ]);

            // Update subscription
            $subscription->update([
                'current_cycle' => $subscription->current_cycle + 1,
                'next_billing_date' => $nextBillingDate,
                'last_billing_date' => now(),
            ]);

            // Process payment via Midtrans
            try {
                $this->processRenewalPayment($renewal, $order);
            } catch (\Exception $e) {
                Log::error('Subscription renewal payment failed: ' . $e->getMessage());
                $renewal->update(['status' => 'failed']);
                throw $e;
            }

            return $renewal;
        });
    }

    /**
     * Cancel subscription.
     */
    public function cancelSubscription(ProductSubscription $subscription): bool
    {
        return DB::transaction(function () use ($subscription) {
            $subscription->cancel();
            return true;
        });
    }

    /**
     * Pause subscription.
     */
    public function pauseSubscription(ProductSubscription $subscription): bool
    {
        return DB::transaction(function () use ($subscription) {
            $subscription->pause();
            return true;
        });
    }

    /**
     * Resume subscription.
     */
    public function resumeSubscription(ProductSubscription $subscription): bool
    {
        return DB::transaction(function () use ($subscription) {
            $subscription->resume();
            return true;
        });
    }

    /**
     * Process due renewals (for cron job).
     */
    public function processDueRenewals(): void
    {
        $subscriptions = ProductSubscription::dueForRenewal()->get();

        foreach ($subscriptions as $subscription) {
            try {
                $this->renewSubscription($subscription);
            } catch (\Exception $e) {
                Log::error("Failed to renew subscription {$subscription->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Create Midtrans subscription.
     */
    public function createMidtransSubscription(Order $order, Product $product): ?string
    {
        try {
            // Note: Midtrans subscription API integration
            // This is a placeholder - actual implementation depends on Midtrans subscription API
            // For now, we'll use recurring payments via transaction API
            
            $interval = $product->subscription_interval ?? 'monthly';
            $intervalDays = match ($interval) {
                'daily' => 1,
                'weekly' => 7,
                'monthly' => 30,
                'yearly' => 365,
                default => 30,
            };

            // Store subscription ID for tracking
            // In production, this would be the actual Midtrans subscription ID
            return 'SUB-' . $order->id . '-' . time();
        } catch (\Exception $e) {
            Log::error('Failed to create Midtrans subscription: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate next billing date.
     */
    protected function calculateNextBillingDate(Product $product, ?Carbon $trialEndsAt = null): Carbon
    {
        $startDate = $trialEndsAt ?? Carbon::now();
        $interval = $product->subscription_interval ?? 'monthly';

        return match ($interval) {
            'daily' => $startDate->copy()->addDay(),
            'weekly' => $startDate->copy()->addWeek(),
            'monthly' => $startDate->copy()->addMonth(),
            'yearly' => $startDate->copy()->addYear(),
            default => $startDate->copy()->addMonth(),
        };
    }

    /**
     * Process renewal payment.
     */
    protected function processRenewalPayment(SubscriptionRenewal $renewal, Order $order): void
    {
        // Create Midtrans transaction for renewal
        // This would typically use saved payment method or subscription API
        $result = $this->midtransService->createTransaction($order);
        
        // Store transaction ID if available
        if ($result['success'] && isset($order->midtrans_transaction_id)) {
            $renewal->update([
                'midtrans_transaction_id' => $order->midtrans_transaction_id,
            ]);
        }
    }
}

