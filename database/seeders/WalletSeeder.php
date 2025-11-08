<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Ensure every user has a wallet record synced with their wallet_balance column.
     */
    public function run(): void
    {
        $baseCurrency = config('currency.base_currency', 'IDR');

        User::all()->each(function (User $user) use ($baseCurrency) {
            $balance = $user->wallet_balance;

            if ($balance === null) {
                $balance = (float) rand(50_000, 500_000);
                $user->wallet_balance = $balance;
            }

            if ($user->currency === null) {
                $user->currency = $baseCurrency;
            }

            // Persist any adjustments on the user model
            if ($user->isDirty(['wallet_balance', 'currency'])) {
                $user->save();
            }

            Wallet::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => $balance,
                    'currency' => $baseCurrency,
                ]
            );
        });
    }
}


