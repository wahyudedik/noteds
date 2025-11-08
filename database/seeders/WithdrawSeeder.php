<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class WithdrawSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('admin')->first();
        $sellers = User::role('seller')->get();

        if ($sellers->isEmpty()) {
            return;
        }

        foreach ($sellers->take(6) as $seller) {
            $amount = max(50_000, min((float) ($seller->wallet_balance ?? 200_000), rand(80_000, 200_000)));
            $status = Arr::random(['pending', 'approved', 'rejected']);

            $withdraw = Withdraw::updateOrCreate(
                [
                    'user_id' => $seller->id,
                    'amount' => $amount,
                ],
                [
                    'status' => $status,
                    'bank_name' => $seller->bank_name ?? 'Bank BCA',
                    'account_number' => $seller->bank_account_number ?? '0123456789',
                    'account_name' => $seller->bank_account_name ?? $seller->name,
                    'admin_notes' => $status === 'rejected' ? 'Saldo tidak mencukupi, silakan cek kembali.' : null,
                    'processed_by' => $status === 'pending' ? null : $admin?->id,
                    'processed_at' => $status === 'pending' ? null : now()->subHours(rand(2, 24)),
                ]
            );

            if ($status === 'approved') {
                $seller->decrement('wallet_balance', $amount);
                $seller->wallet?->update(['balance' => $seller->wallet_balance]);
            }
        }
    }
}


