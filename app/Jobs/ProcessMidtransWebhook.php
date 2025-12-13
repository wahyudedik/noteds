<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\CurrencyService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMidtransWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 30;
    public int $backoff = 5; // Wait 5 seconds before retry

    protected string $orderId;
    protected array $notification;

    /**
     * Create a new job instance.
     */
    public function __construct(string $orderId, array $notification)
    {
        $this->orderId = $orderId;
        $this->notification = $notification;
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(CurrencyService $currencyService, NotificationService $notificationService): void
    {
        try {
            Log::info('⏳ Processing Midtrans webhook job', ['order_id' => $this->orderId]);

            $transaction = Transaction::where('midtrans_order_id', $this->orderId)->first();

            if (!$transaction) {
                Log::warning('Transaction not found for webhook: ' . $this->orderId);
                return;
            }

            $transactionStatus = $this->notification['transaction_status'] ?? null;
            $fraudStatus = $this->notification['fraud_status'] ?? null;
            $grossAmount = $this->notification['gross_amount'] ?? null;

            // Prevent duplicate processing
            if ($transaction->status === 'success' && in_array($transactionStatus, ['settlement', 'capture'])) {
                Log::info('Transaction already processed: ' . $this->orderId);
                return;
            }

            // Handle different transaction types
            if ($transaction->payment_method === 'topup') {
                $this->handleTopupWebhook($transaction, $transactionStatus, $fraudStatus, $grossAmount, $currencyService, $notificationService);
            } else {
                // Handle purchase webhook if needed
                $this->handlePurchaseWebhook($transaction, $transactionStatus, $fraudStatus);
            }

            Log::info('✅ Webhook job completed successfully', ['order_id' => $this->orderId]);
        } catch (\Exception $e) {
            Log::error('❌ Error processing webhook job: ' . $e->getMessage(), [
                'order_id' => $this->orderId,
                'trace' => $e->getTraceAsString(),
            ]);
            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle failed job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('❌ Webhook job failed after retries: ' . $exception->getMessage(), [
            'order_id' => $this->orderId,
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Handle topup webhook processing
     */
    private function handleTopupWebhook($transaction, $status, $fraudStatus, $grossAmount, CurrencyService $currencyService, NotificationService $notificationService): void
    {
        $successContext = null;
        $failureContext = null;

        if ($status === 'settlement' || $status === 'capture') {
            if ($fraudStatus === 'accept') {
                // Verify amount matches (with tolerance for fees)
                if ($grossAmount != $transaction->amount) {
                    // Check if within tolerance (5% or 100,000 IDR)
                    $expectedAmount = (float) $transaction->amount;
                    $tolerance = max($expectedAmount * 0.05, 100000);
                    $difference = abs((float) $grossAmount - $expectedAmount);

                    if ($difference > $tolerance) {
                        Log::warning('Amount mismatch exceeds tolerance', [
                            'transaction_id' => $transaction->id,
                            'expected' => $expectedAmount,
                            'received' => $grossAmount,
                            'difference' => $difference,
                            'tolerance' => $tolerance,
                        ]);
                        return;
                    }

                    Log::info('Amount variance within tolerance', [
                        'transaction_id' => $transaction->id,
                        'expected' => $expectedAmount,
                        'received' => $grossAmount,
                        'difference' => $difference,
                    ]);
                }

                DB::transaction(function () use ($transaction, $grossAmount, $currencyService, &$successContext) {
                    // Use pessimistic locking to prevent duplicate processing
                    $transaction = $transaction->lockForUpdate()->refresh();

                    if ($transaction->status === 'success') {
                        Log::info('Transaction already processed, skipping: ' . $transaction->id);
                        return;
                    }

                    $transaction->status = 'success';
                    $transaction->save();

                    $baseCurrency = $currencyService->getBaseCurrency();

                    // Lock and create/update wallet
                    $wallet = Wallet::where('user_id', $transaction->buyer_id)
                        ->lockForUpdate()
                        ->firstOrCreate(
                            ['user_id' => $transaction->buyer_id],
                            ['balance' => 0, 'currency' => $baseCurrency]
                        );

                    if ($wallet->currency !== $baseCurrency) {
                        $wallet->currency = $baseCurrency;
                    }

                    $amountToAdd = (float) $grossAmount;
                    $wallet->balance = (float) $wallet->balance + $amountToAdd;
                    $wallet->save();

                    // Update user wallet_balance to keep in sync
                    $user = $transaction->buyer;
                    $user->wallet_balance = (float) $wallet->balance;
                    $user->save();

                    Log::info('✅ Top-up successful via webhook', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $user->id,
                        'amount' => $amountToAdd,
                        'new_balance' => $wallet->balance,
                    ]);

                    $successContext = [
                        'user' => $user,
                        'amount' => $amountToAdd,
                        'balance' => (float) $wallet->balance,
                    ];
                });
            } elseif ($fraudStatus === 'challenge') {
                // Challenge status - payment is being reviewed
                $transaction->status = 'pending';
                $transaction->save();
                Log::info('Transaction under challenge review', ['transaction_id' => $transaction->id]);
            }
        } elseif (in_array($status, ['deny', 'expire', 'cancel'])) {
            $originalStatus = $transaction->status;
            $transaction->status = 'failed';
            $transaction->save();
            Log::info('Transaction failed', [
                'transaction_id' => $transaction->id,
                'status' => $status,
            ]);
            if ($originalStatus !== 'failed') {
                $failureContext = [
                    'user' => $transaction->buyer,
                    'amount' => (float) $transaction->amount,
                    'status' => $status,
                ];
            }
        } elseif ($status === 'pending') {
            $transaction->status = 'pending';
            $transaction->save();
        }

        if ($successContext) {
            $notificationService->notifyTopupSuccess(
                $successContext['user'],
                $successContext['amount'],
                $successContext['balance'],
                $transaction->id
            );
        }

        if ($failureContext && $failureContext['user']) {
            $notificationService->notifyTopupFailed(
                $failureContext['user'],
                $failureContext['amount'],
                $failureContext['status'],
                $transaction->id
            );
        }
    }

    /**
     * Handle purchase webhook processing
     */
    private function handlePurchaseWebhook($transaction, $status, $fraudStatus): void
    {
        // Will be handled in MarketplaceController purchase method
        // This is for future use if needed
        Log::info('Purchase webhook received', [
            'transaction_id' => $transaction->id,
            'status' => $status,
        ]);
    }
}
