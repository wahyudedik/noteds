<?php

namespace App\Http\Requests;

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
        $minBalance = 50000;
        
        // Get wallet balance (sync if needed)
        $wallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );
        
        // Sync wallet balance with user wallet_balance
        if ($wallet->balance != $user->wallet_balance) {
            $wallet->balance = $user->wallet_balance;
            $wallet->save();
        }
        
        $maxBalance = max($wallet->balance, $user->wallet_balance ?? 0);

        return [
            'amount' => [
                'required',
                'numeric',
                'min:' . $minBalance,
                'max:' . $maxBalance,
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
        return [
            'amount.min' => 'Minimum penarikan adalah Rp 50.000.',
            'amount.max' => 'Saldo wallet tidak mencukupi untuk penarikan ini.',
        ];
    }
}
