<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWithdrawRequest;
use App\Models\Withdraw;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\NotificationService;

class WithdrawController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function create(): View
    {
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

        // Sync wallet balance with user wallet_balance for consistency
        if ($wallet->balance != $user->wallet_balance) {
            $wallet->balance = $user->wallet_balance;
            $wallet->save();
        }

        return view('40-shared/wallet/withdraw', compact('wallet', 'user'));
    }

    public function store(StoreWithdrawRequest $request): RedirectResponse
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

        // Sync wallet balance with user wallet_balance for consistency
        if ($wallet->balance != $user->wallet_balance) {
            $wallet->balance = $user->wallet_balance;
            $wallet->save();
        }

        $amountInUserCurrency = (float) $request->input('amount');
        $amountBase = $request->amountInBase();
        $minimumBase = 50000;

        // Double check balance
        if ($wallet->balance < $amountBase || $wallet->balance < $minimumBase) {
            $minDisplay = currency($minimumBase, $userCurrency, $baseCurrency);
            return redirect()->route('wallet.withdraw.create')
                ->withErrors(['amount' => __('messages.minimum_withdraw_amount', ['amount' => $minDisplay])])
                ->withInput();
        }

        $validated = $request->validated();

        // Calculate exchange rate for audit trail
        $exchangeRate = 1;
        if ($userCurrency !== $baseCurrency) {
            $exchangeRate = $currencyService->getExchangeRate($baseCurrency, $userCurrency);
        }

        $withdraw = Withdraw::create([
            'user_id' => $user->id,
            'amount' => $amountInUserCurrency,
            'currency' => $userCurrency,
            'original_amount' => $amountBase,
            'original_currency' => $baseCurrency,
            'exchange_rate' => $exchangeRate,
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'status' => 'pending',
        ]);

        $this->notificationService->notifyWithdrawRequested($user, (float) $amountBase);

        // 🔔 NOTIFY ALL ADMINS about new withdraw request
        $withdraw->setRelation('user', $user);
        $this->notificationService->notifyAdminNewWithdrawRequest($withdraw);

        $threshold = $this->notificationService->getHighValueWithdrawThreshold();
        if ($amountBase >= $threshold) {
            $this->notificationService->notifyAdminHighValueWithdraw($withdraw, $threshold);
        }

        return redirect()->route('wallet.index')
            ->with('success', 'Permintaan withdraw telah dikirim. Admin akan memproses segera.');
    }
}
