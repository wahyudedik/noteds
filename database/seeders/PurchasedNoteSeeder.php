<?php

namespace Database\Seeders;

use App\Models\PurchasedNote;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PurchasedNoteSeeder extends Seeder
{
    /**
     * Seed purchased_notes table based on successful transactions.
     */
    public function run(): void
    {
        $transactions = Transaction::query()
            ->whereNotNull('note_id')
            ->where('status', 'success')
            ->get();

        if ($transactions->isEmpty()) {
            return;
        }

        foreach ($transactions as $transaction) {
            $buyer = $transaction->buyer;
            $note = $transaction->note;

            if (!$buyer || !$note) {
                continue;
            }

            $purchasedAt = $transaction->created_at instanceof Carbon
                ? $transaction->created_at
                : now()->subDays(rand(1, 14));

            PurchasedNote::updateOrCreate(
                [
                    'user_id' => $buyer->id,
                    'note_id' => $note->id,
                ],
                [
                    'transaction_id' => $transaction->id,
                    'purchase_price' => $transaction->amount,
                    'purchased_at' => $purchasedAt,
                    'download_count' => rand(0, 4),
                    'last_accessed_at' => rand(0, 1) ? $purchasedAt->copy()->addDays(rand(1, 7)) : null,
                ]
            );
        }
    }
}


