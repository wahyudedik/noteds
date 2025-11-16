<?php

namespace Database\Seeders;

use App\Models\Refund;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class RefundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = Transaction::where('status', 'completed')
            ->whereHas('note')
            ->take(10)
            ->get();

        if ($transactions->isEmpty()) {
            return;
        }

        $reasons = [
            'not_as_described',
            'duplicate_purchase',
            'technical_issue',
            'changed_mind',
            'other',
        ];

        $statuses = ['pending', 'approved', 'rejected', 'processed'];

        foreach ($transactions->take(5) as $transaction) {
            $status = $statuses[array_rand($statuses)];
            $admin = User::role('admin')->first();

            $refund = Refund::create([
                'transaction_id' => $transaction->id,
                'buyer_id' => $transaction->buyer_id,
                'seller_id' => $transaction->seller_id,
                'note_id' => $transaction->note_id,
                'amount' => $transaction->amount,
                'status' => $status,
                'reason' => $reasons[array_rand($reasons)],
                'reason_description' => $this->getReasonDescription($status),
                'admin_notes' => $status !== 'pending' ? 'Processed by admin review.' : null,
                'processed_by' => $status !== 'pending' ? $admin?->id : null,
                'processed_at' => $status !== 'pending' ? now()->subDays(rand(1, 7)) : null,
            ]);
        }
    }

    private function getReasonDescription(string $status): string
    {
        $descriptions = [
            'pending' => 'The note content does not match the description provided.',
            'approved' => 'Refund approved due to content mismatch.',
            'rejected' => 'Refund request does not meet our refund policy criteria.',
            'processed' => 'Refund has been processed and returned to buyer.',
        ];

        return $descriptions[$status] ?? 'Refund request submitted.';
    }
}

