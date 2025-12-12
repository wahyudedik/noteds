<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CleanupPendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:cleanup-pending {--days=1} {--force : Skip confirmation} {--verify : Verify status with Midtrans before deleting}';

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
        $force = $this->option('force');
        $verify = $this->option('verify');
        $cutoffDate = now()->subDays($days);

        $this->line('🔍 Searching for pending transactions...');
        $this->line('');

        // Find pending top-up transactions older than X days
        $pendingTransactions = Transaction::where('status', 'pending')
            ->where('payment_method', 'topup')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($pendingTransactions->isEmpty()) {
            $this->info('✅ No pending transactions to clean up.');
            return;
        }

        $this->warn("⚠️  Found {$pendingTransactions->count()} pending transactions older than {$days} day(s).");
        $this->line('');

        $toDelete = collect();

        foreach ($pendingTransactions as $transaction) {
            $message = "{$transaction->id} | Amount: " . number_format($transaction->amount, 2) . " {$transaction->currency} | Created: {$transaction->created_at->diffForHumans()}";

            // Verify dengan Midtrans jika --verify flag
            if ($verify && $transaction->midtrans_order_id) {
                $status = $this->checkMidtransStatus($transaction->midtrans_order_id);

                if ($status === 'failed' || $status === 'cancelled') {
                    $this->line("🗑️  DELETE | {$message} (Status: {$status})");
                    $toDelete->push($transaction);
                } elseif ($status === 'pending') {
                    $this->line("⏳ KEEP   | {$message} (Still pending in Midtrans)");
                } elseif ($status === 'success') {
                    $this->line("✅ UPDATE | {$message} (Actually success - will update)");
                    // Update to success jika ternyata sudah success
                    $this->markAsSuccess($transaction);
                }
            } else {
                // Default behavior: mark for deletion jika sudah lama pending
                $this->line("🗑️  DELETE | {$message}");
                $toDelete->push($transaction);
            }
        }

        $this->line('');
        $this->line("Summary: {$toDelete->count()} transactions marked for deletion");
        $this->line('');

        if ($toDelete->isEmpty()) {
            $this->info('✅ No transactions to delete.');
            return;
        }

        // Ask for confirmation
        if (!$force && !$this->confirm('Delete these pending transactions?', false)) {
            foreach ($toDelete as $transaction) {
                $this->line("  - {$transaction->id}: {$transaction->amount} (Created: {$transaction->created_at})");
            }
            $this->info('No transactions deleted.');
            return;
        }

        // Delete them
        $deleted = 0;
        foreach ($toDelete as $transaction) {
            Log::warning('Cleaning up pending transaction', [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'created_at' => $transaction->created_at,
                'midtrans_order_id' => $transaction->midtrans_order_id,
                'reason' => 'Auto-cleanup: pending for more than ' . $days . ' day(s)',
            ]);

            $transaction->delete();
            $deleted++;
        }

        $this->info("✅ Successfully deleted {$deleted} pending transactions.");
        $this->line('');
        $this->table(['Metric', 'Value'], [
            ['Total Found', $pendingTransactions->count()],
            ['Deleted', $deleted],
            ['Cutoff Date', $cutoffDate->format('Y-m-d H:i:s')],
            ['Command Run At', now()->format('Y-m-d H:i:s')],
        ]);
    }

    /**
     * Check transaction status dengan Midtrans API
     */
    private function checkMidtransStatus(string $orderId): ?string
    {
        try {
            $serverKey = config('services.midtrans.server_key');
            $baseUrl = config('services.midtrans.is_production')
                ? 'https://api.midtrans.com/v2'
                : 'https://app.sandbox.midtrans.com/v2';

            $response = Http::withBasicAuth($serverKey, '')
                ->get("{$baseUrl}/{$orderId}/status");

            if ($response->ok()) {
                $data = $response->json();
                return $data['transaction_status'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to check Midtrans status', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Update transaction to success jika status Midtrans success
     */
    private function markAsSuccess(Transaction $transaction): void
    {
        try {
            $transaction->status = 'success';
            $transaction->save();

            Log::info('Updated pending transaction to success', [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
            ]);

            $this->info("✅ Updated transaction {$transaction->id} to success");
        } catch (\Exception $e) {
            Log::error('Failed to update transaction', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
