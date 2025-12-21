<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    /**
     * Add balance to user account.
     */
    public function addBalance(User $user, float $amount, string $description, ?int $referenceId = null, string $type = 'sale'): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $description, $referenceId, $type) {
            $balanceBefore = $user->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update user balance
            $user->increment('balance', $amount);

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'reference_id' => $referenceId,
                'description' => $description,
            ]);

            return $transaction;
        });
    }

    /**
     * Deduct balance from user account.
     */
    public function deductBalance(User $user, float $amount, string $description, ?int $referenceId = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $description, $referenceId) {
            if ($user->balance < $amount) {
                throw new \Exception('Insufficient balance');
            }

            $balanceBefore = $user->balance;
            $balanceAfter = $balanceBefore - $amount;

            // Update user balance
            $user->decrement('balance', $amount);

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'reference_id' => $referenceId,
                'description' => $description,
            ]);

            return $transaction;
        });
    }

    /**
     * Get current balance.
     */
    public function getBalance(User $user): float
    {
        return (float) $user->fresh()->balance;
    }

    /**
     * Get balance history.
     */
    public function getBalanceHistory(User $user, int $limit = 50)
    {
        return Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

