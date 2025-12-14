<?php

namespace App\Http\Controllers;

use App\Models\BuyerSubscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Midtrans\Config;
use Midtrans\Snap;

class BuyerSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'username.setup']);
    }

    /**
     * Display available subscription plans.
     */
    public function index(): View
    {
        $plans = SubscriptionPlan::active()->get();
        $user = auth()->user();
        $activeSubscription = $user->activeBuyerSubscription();

        return view('40-shared/subscriptions/plans', compact('plans', 'activeSubscription'));
    }

    /**
     * Show subscription details and purchase form.
     */
    public function show(SubscriptionPlan $plan): View
    {
        $user = auth()->user();
        $activeSubscription = $user->activeBuyerSubscription();

        return view('40-shared/subscriptions/show', compact('plan', 'activeSubscription'));
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'payment_method' => ['required', 'in:wallet,midtrans'],
        ]);

        $user = auth()->user();
        $billingCycle = $validated['billing_cycle'];
        $paymentMethod = $validated['payment_method'];
        $price = $plan->getPrice($billingCycle);

        // Check if user already has active subscription
        $activeSubscription = $user->activeBuyerSubscription();
        if ($activeSubscription) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'You already have an active subscription. Please cancel it first or upgrade/downgrade.');
        }

        // Handle wallet payment
        if ($paymentMethod === 'wallet') {
            if ($user->wallet_balance < $price) {
                return redirect()->back()
                    ->with('error', 'Insufficient wallet balance.')
                    ->with('insufficient_balance', true);
            }

            return $this->processWalletSubscription($user, $plan, $billingCycle, $price);
        }

        // Handle Midtrans payment
        return $this->processMidtransSubscription($user, $plan, $billingCycle, $price);
    }

    /**
     * Process wallet subscription.
     */
    private function processWalletSubscription($user, SubscriptionPlan $plan, string $billingCycle, float $price): RedirectResponse
    {
        DB::transaction(function () use ($user, $plan, $billingCycle, $price) {
            // Lock wallet for update to prevent race conditions
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'currency' => config('app.currency', 'USD')]
                );

            // Validate price before deduction
            if (!is_numeric($price) || $price <= 0 || is_nan($price) || is_infinite($price)) {
                throw new \Exception('Invalid subscription price');
            }

            // Double-check wallet balance with locking
            if ($wallet->balance < $price) {
                throw new \Exception('Insufficient wallet balance');
            }

            // Deduct from wallet
            $wallet->balance -= $price;
            $wallet->save();

            $user->wallet_balance = $wallet->balance;
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
                'notes' => "Subscription: {$plan->name} ({$billingCycle})",
            ]);

            // Create subscription
            $periodStart = now();
            $periodEnd = $billingCycle === 'monthly'
                ? $periodStart->copy()->addMonth()
                : $periodStart->copy()->addYear();

            BuyerSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'price' => $price,
                'status' => 'active',
                'started_at' => $periodStart,
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'next_billing_date' => $periodEnd,
                'auto_renew' => true,
                'payment_method' => 'wallet',
                'payment_status' => 'paid',
                'billing_cycle_count' => 1,
            ]);
        });

        return redirect()->route('subscriptions.my-subscription')
            ->with('success', 'Subscription activated successfully!');
    }

    /**
     * Process Midtrans subscription.
     */
    private function processMidtransSubscription($user, SubscriptionPlan $plan, string $billingCycle, float $price): RedirectResponse
    {
        // Configure Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Create subscription record
        $periodStart = now();
        $periodEnd = $billingCycle === 'monthly'
            ? $periodStart->copy()->addMonth()
            : $periodStart->copy()->addYear();

        $subscription = BuyerSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $billingCycle,
            'price' => $price,
            'status' => 'trialing', // Will be active after payment
            'started_at' => $periodStart,
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
            'next_billing_date' => $periodEnd,
            'auto_renew' => true,
            'payment_method' => 'midtrans',
            'payment_status' => 'pending',
            'billing_cycle_count' => 0,
        ]);

        // Create Midtrans transaction
        $params = [
            'transaction_details' => [
                'order_id' => 'SUB-' . $subscription->id . '-' . time(),
                'gross_amount' => $price,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => $plan->id,
                    'price' => $price,
                    'quantity' => 1,
                    'name' => "{$plan->name} Subscription ({$billingCycle})",
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            $subscription->update([
                'midtrans_order_id' => $params['transaction_details']['order_id'],
                'midtrans_token' => $snapToken,
            ]);

            return redirect()->route('subscriptions.payment', $subscription)
                ->with('snap_token', $snapToken);
        } catch (\Exception $e) {
            $subscription->delete();
            return redirect()->back()
                ->with('error', 'Failed to initialize payment. Please try again.');
        }
    }

    /**
     * Show payment page.
     */
    public function payment(BuyerSubscription $subscription): View
    {
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        if ($subscription->payment_status === 'paid') {
            return redirect()->route('subscriptions.my-subscription');
        }

        return view('40-shared/subscriptions/payment', compact('subscription'));
    }

    /**
     * Handle Midtrans callback.
     */
    public function callback(Request $request): void
    {
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $transactionStatus = $request->input('transaction_status');

        $subscription = BuyerSubscription::where('midtrans_order_id', $orderId)->first();

        if (!$subscription) {
            return;
        }

        if ($statusCode == 200 && $transactionStatus == 'settlement') {
            $subscription->update([
                'status' => 'active',
                'payment_status' => 'paid',
                'billing_cycle_count' => 1,
            ]);

            // Create transaction record
            Transaction::create([
                'buyer_id' => $subscription->user_id,
                'seller_id' => $subscription->user_id,
                'note_id' => null,
                'amount' => $subscription->price,
                'commission' => 0,
                'currency' => config('app.currency', 'USD'),
                'original_amount' => $subscription->price,
                'original_currency' => config('app.currency', 'USD'),
                'exchange_rate' => 1,
                'status' => 'success',
                'payment_method' => 'midtrans',
                'notes' => "Subscription: {$subscription->plan->name} ({$subscription->billing_cycle})",
            ]);
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'expire') {
            $subscription->update([
                'status' => 'expired',
                'payment_status' => 'failed',
            ]);
        }
    }

    /**
     * Show user's current subscription.
     */
    public function mySubscription(): View
    {
        $user = auth()->user();
        $subscription = $user->activeBuyerSubscription();
        $subscriptions = $user->buyerSubscriptions()->with('plan')->latest()->paginate(10);

        return view('40-shared/subscriptions/my-subscription', compact('subscription', 'subscriptions'));
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request, BuyerSubscription $subscription): RedirectResponse
    {
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $subscription->cancel($validated['reason'] ?? null);

        return redirect()->route('subscriptions.my-subscription')
            ->with('success', 'Subscription cancelled successfully. You will continue to have access until the end of your billing period.');
    }

    /**
     * Upgrade or downgrade subscription.
     */
    public function changePlan(Request $request, BuyerSubscription $subscription): RedirectResponse
    {
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        $newPlan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $proratedAmount = $subscription->calculateProratedAmount($newPlan, $validated['billing_cycle']);

        // Check wallet balance
        if (auth()->user()->wallet_balance < $proratedAmount) {
            return redirect()->back()
                ->with('error', 'Insufficient wallet balance for prorated upgrade.');
        }

        DB::transaction(function () use ($subscription, $newPlan, $validated, $proratedAmount) {
            // Deduct prorated amount
            $user = auth()->user();
            $user->wallet_balance -= $proratedAmount;
            $user->save();

            // Update subscription
            $periodStart = now();
            $periodEnd = $validated['billing_cycle'] === 'monthly'
                ? $periodStart->copy()->addMonth()
                : $periodStart->copy()->addYear();

            $subscription->update([
                'plan_id' => $newPlan->id,
                'billing_cycle' => $validated['billing_cycle'],
                'price' => $newPlan->getPrice($validated['billing_cycle']),
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'next_billing_date' => $periodEnd,
            ]);

            // Create transaction
            Transaction::create([
                'buyer_id' => $user->id,
                'seller_id' => $user->id,
                'note_id' => null,
                'amount' => $proratedAmount,
                'commission' => 0,
                'currency' => config('app.currency', 'USD'),
                'original_amount' => $proratedAmount,
                'original_currency' => config('app.currency', 'USD'),
                'exchange_rate' => 1,
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => "Subscription upgrade/downgrade: {$newPlan->name} ({$validated['billing_cycle']})",
            ]);
        });

        return redirect()->route('subscriptions.my-subscription')
            ->with('success', 'Subscription plan updated successfully!');
    }

    /**
     * Gift subscription to another user.
     */
    public function gift(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'recipient_email' => ['required', 'email', 'exists:users,email'],
            'payment_method' => ['required', 'in:wallet,midtrans'],
        ]);

        $user = auth()->user();
        $recipient = \App\Models\User::where('email', $validated['recipient_email'])->firstOrFail();
        $price = $plan->getPrice($validated['billing_cycle']);

        // Check if recipient already has active subscription
        if ($recipient->activeBuyerSubscription()) {
            return redirect()->back()
                ->with('error', 'Recipient already has an active subscription.');
        }

        // Process payment (similar to regular subscription)
        if ($validated['payment_method'] === 'wallet') {
            if ($user->wallet_balance < $price) {
                return redirect()->back()
                    ->with('error', 'Insufficient wallet balance.');
            }

            DB::transaction(function () use ($user, $recipient, $plan, $validated, $price) {
                $user->wallet_balance -= $price;
                $user->save();

                $periodStart = now();
                $periodEnd = $validated['billing_cycle'] === 'monthly'
                    ? $periodStart->copy()->addMonth()
                    : $periodStart->copy()->addYear();

                BuyerSubscription::create([
                    'user_id' => $recipient->id,
                    'plan_id' => $plan->id,
                    'billing_cycle' => $validated['billing_cycle'],
                    'price' => $price,
                    'status' => 'active',
                    'started_at' => $periodStart,
                    'current_period_start' => $periodStart,
                    'current_period_end' => $periodEnd,
                    'next_billing_date' => $periodEnd,
                    'auto_renew' => false, // Gifts don't auto-renew
                    'payment_method' => 'wallet',
                    'payment_status' => 'paid',
                    'gifted_by' => $user->id,
                    'gifted_to' => $recipient->id,
                    'is_gift' => true,
                    'gift_sent_at' => now(),
                    'billing_cycle_count' => 1,
                ]);

                // TODO: Send email notification to recipient
            });

            return redirect()->route('subscriptions.index')
                ->with('success', 'Gift subscription sent successfully!');
        }

        // TODO: Handle Midtrans for gift subscriptions
        return redirect()->back()
            ->with('error', 'Midtrans payment for gift subscriptions is not yet implemented.');
    }
}
