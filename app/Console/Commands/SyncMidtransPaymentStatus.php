<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Transaction as MidtransTransaction;

class SyncMidtransPaymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'midtrans:sync-status {order_id?} {--all}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sync Midtrans transaction status with database. Usage: midtrans:sync-status order_id OR midtrans:sync-status --all';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // Get Midtrans config
            if (empty(config('services.midtrans.server_key'))) {
                $this->error('❌ Midtrans Server Key is not configured!');
                return self::FAILURE;
            }

            $orderId = $this->argument('order_id');
            $syncAll = $this->option('all');

            if ($syncAll) {
                return $this->syncAllPendingTransactions();
            }

            if (!$orderId) {
                $this->error('❌ Please provide order_id or use --all flag');
                $this->info('Usage:');
                $this->info('  php artisan midtrans:sync-status order_id');
                $this->info('  php artisan midtrans:sync-status --all');
                return self::FAILURE;
            }

            return $this->syncSingleTransaction($orderId);
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('SyncMidtransPaymentStatus Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Sync a single transaction by order ID
     */
    private function syncSingleTransaction(string $orderId): int
    {
        $this->info("🔄 Syncing transaction: {$orderId}");

        $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

        if (!$transaction) {
            $this->error("❌ Transaction not found for order_id: {$orderId}");
            return self::FAILURE;
        }

        try {
            // Get status from Midtrans API
            $midtransStatus = MidtransTransaction::status($orderId);
            $transactionStatus = $midtransStatus->transaction_status ?? null;
            $fraudStatus = $midtransStatus->fraud_status ?? null;
            $grossAmount = $midtransStatus->gross_amount ?? null;
            $paymentType = $midtransStatus->payment_type ?? null;

            $this->info("Current DB Status: {$transaction->status}");
            $this->info("Midtrans Status: {$transactionStatus}");
            $this->info("Fraud Status: {$fraudStatus}");
            $this->info("Payment Type: {$paymentType}");
            $this->info("Amount: {$grossAmount}");

            // Check if payment is successful
            if (in_array($transactionStatus, ['settlement', 'capture']) && $fraudStatus === 'accept') {
                if ($transaction->status === 'success') {
                    $this->info('✅ Transaction already marked as success. No sync needed.');
                    return self::SUCCESS;
                }

                $this->warn('⚠️  Payment is settled in Midtrans but not updated in DB. Processing...');

                // Process the payment
                DB::transaction(function () use ($transaction, $grossAmount, $orderId, $transactionStatus) {
                    $transaction->lockForUpdate()->refresh();

                    if ($transaction->status === 'success') {
                        $this->info('✅ Transaction already processed by another process.');
                        return;
                    }

                    // Mark as success
                    $transaction->status = 'success';
                    $transaction->save();

                    // Update wallet if this is a top-up
                    if ($transaction->payment_method === 'topup') {
                        $baseCurrency = config('currency.base_currency', 'IDR');
                        $wallet = \App\Models\Wallet::where('user_id', $transaction->buyer_id)
                            ->lockForUpdate()
                            ->firstOrCreate(
                                ['user_id' => $transaction->buyer_id],
                                ['balance' => 0, 'currency' => $baseCurrency]
                            );

                        $amountToAdd = (float) ($grossAmount ?? $transaction->amount);
                        $wallet->balance = (float) $wallet->balance + $amountToAdd;
                        $wallet->save();

                        // Update user wallet_balance
                        $user = $transaction->buyer;
                        $user->wallet_balance = (float) $wallet->balance;
                        $user->save();

                        $this->info("✅ Wallet updated: +{$amountToAdd} {$baseCurrency}");
                        $this->info("New Balance: {$wallet->balance}");
                    }

                    Log::info('Sync Midtrans Payment Status - Success', [
                        'transaction_id' => $transaction->id,
                        'order_id' => $orderId,
                        'midtrans_status' => $transactionStatus,
                        'amount' => $grossAmount,
                    ]);
                });

                $this->info("✅ Transaction synced successfully!");
                return self::SUCCESS;
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                if ($transaction->status === 'failed') {
                    $this->info('ℹ️  Transaction already marked as failed.');
                    return self::SUCCESS;
                }

                $transaction->status = 'failed';
                $transaction->save();
                $this->warn('⚠️  Transaction marked as failed (status: ' . $transactionStatus . ')');
                return self::SUCCESS;
            } else {
                $this->info("ℹ️  Transaction status: {$transactionStatus} (no action needed)");
                return self::SUCCESS;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error checking Midtrans status: ' . $e->getMessage());
            Log::error('Error syncing Midtrans status', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Sync all pending transactions with Midtrans
     */
    private function syncAllPendingTransactions(): int
    {
        $pendingTransactions = Transaction::where('status', 'pending')
            ->whereNotNull('midtrans_order_id')
            ->where('payment_method', 'topup')
            ->get();

        if ($pendingTransactions->isEmpty()) {
            $this->info('✅ No pending transactions found.');
            return self::SUCCESS;
        }

        $this->info("🔄 Found " . $pendingTransactions->count() . " pending transactions. Syncing...\n");

        $successCount = 0;
        $failedCount = 0;

        foreach ($pendingTransactions as $transaction) {
            $this->line("Processing: {$transaction->midtrans_order_id}");

            if ($this->syncSingleTransaction($transaction->midtrans_order_id) === self::SUCCESS) {
                $successCount++;
            } else {
                $failedCount++;
            }

            $this->line('');
        }

        $this->info("═══════════════════════════════════════");
        $this->info("✅ Synced: {$successCount}");
        $this->error("❌ Failed: {$failedCount}");
        $this->info("═══════════════════════════════════════");

        return self::SUCCESS;
    }
}
