<?php

namespace App\Http\Requests;

use App\Services\CurrencyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWithdrawRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = auth()->user();
        $currencyService = app(CurrencyService::class);
        $baseCurrency = $currencyService->getBaseCurrency();
        $userCurrency = $currencyService->getUserCurrency($user);
        $minBalanceBase = 50000;

        // Get wallet balance (sync if needed)
        $wallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => $baseCurrency]
        );

        // Sync wallet balance with user wallet_balance
        if ($wallet->balance != $user->wallet_balance) {
            $wallet->balance = $user->wallet_balance;
            $wallet->save();
        }

        $maxBalanceBase = max($wallet->balance, $user->wallet_balance ?? 0);
        $minAmount = $currencyService->convert($minBalanceBase, $baseCurrency, $userCurrency);
        $maxAmount = $currencyService->convert($maxBalanceBase, $baseCurrency, $userCurrency);

        return [
            'amount' => [
                'required',
                'numeric',
                'min:' . $minAmount,
                'max:' . $maxAmount,
            ],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $currencyService = app(CurrencyService::class);
        $baseCurrency = $currencyService->getBaseCurrency();
        $userCurrency = $currencyService->getUserCurrency(auth()->user());
        $minBalanceBase = 50000;
        $minAmountDisplay = currency($minBalanceBase, $userCurrency, $baseCurrency);

        return [
            'amount.min' => __('messages.minimum_withdraw_amount', ['amount' => $minAmountDisplay]),
            'amount.max' => 'Saldo wallet tidak mencukupi untuk penarikan ini.',
        ];
    }

    public function amountInBase(): float
    {
        $currencyService = app(CurrencyService::class);
        $baseCurrency = $currencyService->getBaseCurrency();
        $userCurrency = $currencyService->getUserCurrency(auth()->user());

        return $currencyService->convert((float) $this->input('amount'), $userCurrency, $baseCurrency);
    }
}
