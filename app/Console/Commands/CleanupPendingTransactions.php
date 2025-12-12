<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupPendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:cleanup-pending {--days=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up pending transactions older than specified days that failed to complete payment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        // Find pending top-up transactions older than X days
        $pendingTransactions = Transaction::where('status', 'pending')
            ->where('payment_method', 'topup')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($pendingTransactions->isEmpty()) {
            $this->info('No pending transactions to clean up.');
            return;
        }

        $this->info("Found {$pendingTransactions->count()} pending transactions older than {$days} day(s).");

        // Optional: Delete them (be cautious!)
        if ($this->confirm('Delete these pending transactions?', false)) {
            foreach ($pendingTransactions as $transaction) {
                Log::warning('Deleting pending transaction', [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'created_at' => $transaction->created_at,
                    'midtrans_order_id' => $transaction->midtrans_order_id,
                ]);

                $transaction->delete();
            }

            $this->info("Deleted {$pendingTransactions->count()} pending transactions.");
        } else {
            // Just log them
            foreach ($pendingTransactions as $transaction) {
                $this->line("- {$transaction->id}: {$transaction->amount} (Created: {$transaction->created_at})");
            }

            $this->info('No transactions deleted.');
        }
    }
}
