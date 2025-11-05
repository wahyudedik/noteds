<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $subscription = auth()->user()->subscription;
        
        return view('subscription.index', compact('subscription'));
    }

    public function create(): View|RedirectResponse
    {
        // Check if user already has an active or pending subscription
        $existingSubscription = auth()->user()->subscription;
        
        if ($existingSubscription && in_array($existingSubscription->status, ['active', 'pending'])) {
            return redirect()->route('subscription.index')
                ->with('error', 'You already have a subscription request or active subscription.');
        }

        $user = auth()->user();
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );
        
        // Sync wallet balance
        if ($wallet->balance != $user->wallet_balance) {
            $wallet->balance = $user->wallet_balance;
            $wallet->save();
        }

        $premiumPrice = Setting::getPremiumPrice();

        return view('subscription.create', compact('wallet', 'premiumPrice'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );
        
        // Sync wallet balance
        if ($wallet->balance != $user->wallet_balance) {
            $wallet->balance = $user->wallet_balance;
            $wallet->save();
        }

        $premiumPrice = Setting::getPremiumPrice();

        // Check if user already has an active or pending subscription
        $existingSubscription = $user->subscription;
        if ($existingSubscription && in_array($existingSubscription->status, ['active', 'pending'])) {
            return redirect()->route('subscription.index')
                ->with('error', 'You already have a subscription request or active subscription.');
        }

        // Check wallet balance
        if ($wallet->balance < $premiumPrice) {
            return redirect()->route('subscription.create')
                ->with('error', 'Insufficient wallet balance. Please top up your wallet first.')
                ->with('insufficient_balance', true);
        }

        DB::transaction(function () use ($user, $wallet, $premiumPrice) {
            // Deduct from wallet
            $wallet->balance -= $premiumPrice;
            $wallet->save();

            // Update user wallet_balance
            $user->wallet_balance = $wallet->balance;
            $user->save();

            // Create transaction record
            Transaction::create([
                'buyer_id' => $user->id,
                'seller_id' => $user->id, // Self payment
                'note_id' => null,
                'amount' => $premiumPrice,
                'commission' => 0,
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => 'Premium subscription payment',
            ]);

            // Create subscription (directly active)
            Subscription::create([
                'user_id' => $user->id,
                'plan' => 'premium',
                'status' => 'active',
                'payment_proof' => null,
                'expired_at' => now()->addMonth(),
            ]);
        });

        return redirect()->route('subscription.index')
            ->with('success', 'Premium subscription activated successfully! Enjoy all premium features.');
    }

    public function show(Subscription $subscription): View
    {
        // Ensure user can only view their own subscription
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        return view('subscription.show', compact('subscription'));
    }
}
