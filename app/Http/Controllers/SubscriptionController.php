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
        $currencyService = app(\App\Services\CurrencyService::class);
        $baseCurrency = $currencyService->getBaseCurrency();
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => $baseCurrency]
        );
        if ($wallet->currency !== $baseCurrency) {
            $wallet->currency = $baseCurrency;
            $wallet->save();
        }

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
        $currencyService = app(\App\Services\CurrencyService::class);
        $baseCurrency = $currencyService->getBaseCurrency();
        $userCurrency = $currencyService->getUserCurrency($user);
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => $baseCurrency]
        );
        if ($wallet->currency !== $baseCurrency) {
            $wallet->currency = $baseCurrency;
            $wallet->save();
        }

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
            $currentBalanceDisplay = currency($wallet->balance, $userCurrency, $baseCurrency);
            $requiredDisplay = currency($premiumPrice, $userCurrency, $baseCurrency);
            return redirect()->route('subscription.create')
                ->with('error', __('messages.insufficient_wallet_balance', ['balance' => $currentBalanceDisplay, 'required' => $requiredDisplay]))
                ->with('insufficient_balance', true);
        }

        DB::transaction(function () use ($user, $wallet, $premiumPrice, $baseCurrency, $userCurrency, $currencyService) {
            // Calculate exchange rate for user's currency
            $exchangeRate = 1;
            $premiumInUserCurrency = $premiumPrice;
            if ($userCurrency !== $baseCurrency) {
                $exchangeRate = $currencyService->getExchangeRate($baseCurrency, $userCurrency);
                $premiumInUserCurrency = $premiumPrice * $exchangeRate;
            }

            // Deduct from wallet
            $wallet->balance -= $premiumInUserCurrency;
            $wallet->save();

            // Update user wallet_balance
            $user->wallet_balance = $wallet->balance;
            $user->save();

            // Create transaction record
            Transaction::create([
                'buyer_id' => $user->id,
                'seller_id' => $user->id, // Self payment
                'note_id' => null,
                'amount' => $premiumInUserCurrency,
                'commission' => 0,
                'currency' => $userCurrency,
                'original_amount' => $premiumPrice,
                'original_currency' => $baseCurrency,
                'exchange_rate' => $exchangeRate,
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
