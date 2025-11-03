<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWithdrawRequest;
use App\Models\Withdraw;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WithdrawController extends Controller
{
    public function create(): View
    {
        $user = auth()->user();
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // Sync wallet balance with user wallet_balance for consistency
        if ($wallet->balance != $user->wallet_balance) {
            $wallet->balance = $user->wallet_balance;
            $wallet->save();
        }

        return view('wallet.withdraw', compact('wallet'));
    }

    public function store(StoreWithdrawRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // Sync wallet balance with user wallet_balance for consistency
        if ($wallet->balance != $user->wallet_balance) {
            $wallet->balance = $user->wallet_balance;
            $wallet->save();
        }

        // Double check balance
        if ($wallet->balance < $request->amount || $wallet->balance < 50000) {
            return redirect()->route('wallet.withdraw.create')
                ->withErrors(['amount' => 'Saldo tidak mencukupi. Minimum penarikan Rp 50.000.'])
                ->withInput();
        }

        Withdraw::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'status' => 'pending',
        ]);

        return redirect()->route('wallet.index')
            ->with('success', 'Permintaan withdraw telah dikirim. Admin akan memproses segera.');
    }
}
