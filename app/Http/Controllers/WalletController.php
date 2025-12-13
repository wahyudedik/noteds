<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Services\CurrencyService;
use App\Services\NotificationService;
use App\Services\MidtransWebhookSecurityService;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;

class WalletController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private CurrencyService $currencyService
    ) {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function index(): View
    {
        $user = auth()->user();

        // Ensure wallet exists
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 0,
                'currency' => $this->currencyService->getBaseCurrency(),
            ]
        );
        if ($wallet->currency !== $this->currencyService->getBaseCurrency()) {
            $wallet->currency = $this->currencyService->getBaseCurrency();
            $wallet->save();
        }

        // Update user wallet_balance for backward compatibility
        $user->wallet_balance = $wallet->balance;
        $user->save();

        $transactions = Transaction::where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->with(['note', 'buyer', 'seller'])
            ->latest()
            ->paginate(20);

        return view('wallet.index', compact('wallet', 'transactions'));
    }

    public function topup(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $userCurrency = $this->currencyService->getUserCurrency($user);
        $baseCurrency = $this->currencyService->getBaseCurrency();

        // Strict validation to prevent injection/invalid data
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'regex:/^\d+(\.\d{1,4})?$/', // Only allow numbers with max 4 decimal places
                'min:0.01',
                'not_in:0', // Prevent 0 amount
            ],
        ], [
            'amount.regex' => 'Invalid amount format. Use numbers only with max 4 decimal places.',
            'amount.not_in' => 'Amount must be greater than 0.',
        ]);

        $inputAmount = (float) $request->amount;

        // Additional validation to prevent injection
        if (!is_numeric($inputAmount) || $inputAmount <= 0 || is_infinite($inputAmount) || is_nan($inputAmount)) {
            return redirect()->route('wallet.index')
                ->with('error', 'Invalid amount provided.');
        }

        $amount = $this->currencyService->convert($inputAmount, $userCurrency, $baseCurrency);

        $minimumBaseTopup = 10000; // in base currency (IDR)
        $maximumBaseTopup = 100000000;

        if ($amount < $minimumBaseTopup) {
            $minDisplay = currency($minimumBaseTopup, $userCurrency, $baseCurrency);
            return redirect()->route('wallet.index')
                ->with('error', __('messages.minimum_topup_amount', ['amount' => $minDisplay]));
        }

        if ($amount > $maximumBaseTopup) {
            $maxDisplay = currency($maximumBaseTopup, $userCurrency, $baseCurrency);
            return redirect()->route('wallet.index')
                ->with('error', __('messages.maximum_topup_amount', ['amount' => $maxDisplay]));
        }

        // Validate amount is not NaN or Infinite after conversion
        if (!is_numeric($amount) || is_infinite($amount) || is_nan($amount) || $amount <= 0) {
            return redirect()->route('wallet.index')
                ->with('error', 'Invalid amount conversion. Please try again.');
        }

        $exchangeRate = $amount > 0 ? $amount / max($inputAmount, 0.00001) : null;

        // Check if Midtrans is configured
        if (empty(config('services.midtrans.server_key')) || empty(config('services.midtrans.client_key'))) {
            return redirect()->route('wallet.index')
                ->with('error', 'Payment gateway belum dikonfigurasi. Silakan hubungi administrator.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 0,
                'currency' => $baseCurrency,
            ]
        );
        if ($wallet->currency !== $baseCurrency) {
            $wallet->currency = $baseCurrency;
            $wallet->save();
        }

        // Create transaction record for top-up
        $transaction = Transaction::create([
            'buyer_id' => $user->id,
            'seller_id' => $user->id, // Self top-up
            'note_id' => null,
            'amount' => round($amount, 2), // Round to 2 decimal places for currency
            'commission' => 0,
            'currency' => $baseCurrency,
            'original_amount' => $inputAmount,
            'original_currency' => $userCurrency,
            'exchange_rate' => $exchangeRate,
            'status' => 'pending',
            'payment_method' => 'topup',
            'notes' => 'Top-up saldo wallet',
        ]);

        // Generate order_id: Midtrans requires max 50 characters
        // Format: topup-{timestamp}-{short_hash}
        // Using first 8 chars of UUID + timestamp for uniqueness
        $shortId = substr(str_replace('-', '', $transaction->id), 0, 8);
        $orderId = 'topup-' . time() . '-' . $shortId;

        // Ensure order_id doesn't exceed 50 characters (Midtrans limit)
        if (strlen($orderId) > 50) {
            // Fallback: use hash if still too long
            $orderId = 'topup-' . time() . '-' . substr(md5($transaction->id), 0, 8);
        }

        $transaction->midtrans_order_id = $orderId;
        $transaction->save();

        // Validate amount before sending to Midtrans
        $finalAmount = round($transaction->amount, 2);
        if (!is_numeric($finalAmount) || $finalAmount <= 0 || is_nan($finalAmount) || is_infinite($finalAmount)) {
            $transaction->delete();
            return redirect()->route('wallet.index')
                ->with('error', 'Invalid transaction amount. Transaction has been cancelled.');
        }

        // Validate customer details
        if (empty($user->name) || empty($user->email)) {
            $transaction->delete();
            return redirect()->route('wallet.index')
                ->with('error', 'User profile incomplete. Please update your name and email.');
        }

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $finalAmount, // Convert to int for Midtrans (in cents/smallest unit)
            ],
            'customer_details' => [
                'first_name' => substr($user->name, 0, 50), // Limit to 50 chars
                'email' => substr($user->email, 0, 100), // Limit to 100 chars
            ],
            'item_details' => [
                [
                    'id' => 'topup',
                    'price' => (int) $finalAmount,
                    'quantity' => 1,
                    'name' => 'Top-up Wallet',
                ],
            ],
            'callbacks' => [
                'finish' => route('payment.finish'),
                'unfinish' => route('payment.unfinish'),
                'error' => route('payment.error'),
            ],
        ];

        try {
            // Log configuration for debugging
            Log::info('Midtrans Configuration Check:', [
                'server_key_set' => !empty(config('services.midtrans.server_key')),
                'client_key_set' => !empty(config('services.midtrans.client_key')),
                'is_production' => config('services.midtrans.is_production', false),
                'merchant_id' => config('services.midtrans.merchant_id'),
                'amount' => $finalAmount,
                'order_id' => $orderId,
            ]);

            // Verify Midtrans config is set
            if (empty(config('services.midtrans.server_key'))) {
                Log::error('Midtrans Server Key is empty!');
                return redirect()->route('wallet.index')
                    ->with('error', 'Payment gateway belum dikonfigurasi dengan benar. Server Key tidak ditemukan.');
            }

            Log::info('Generating Snap Token:', [
                'order_id' => $orderId,
                'order_id_length' => strlen($orderId),
                'amount' => $amount,
                'user_id' => $user->id,
            ]);

            $snapToken = Snap::getSnapToken($params);

            if (empty($snapToken)) {
                Log::error('Snap Token is empty!');
                return redirect()->route('wallet.index')
                    ->with('error', 'Gagal membuat transaksi top-up. Token tidak diterima dari payment gateway.');
            }

            Log::info('Snap Token generated successfully', [
                'token_length' => strlen($snapToken),
                'transaction_id' => $transaction->id,
            ]);

            return redirect()->route('wallet.topup-checkout', ['token' => $snapToken, 'transaction' => $transaction->id]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'params' => $params,
            ]);

            return redirect()->route('wallet.index')
                ->with('error', 'Gagal membuat transaksi top-up: ' . $e->getMessage() . '. Silakan coba lagi atau hubungi support.');
        }
    }

    public function topupCheckout(Request $request): View
    {
        $transactionId = $request->transaction;
        $snapToken = $request->token;

        $transaction = Transaction::findOrFail($transactionId);

        if ($transaction->buyer_id !== auth()->id()) {
            abort(403);
        }

        return view('wallet.topup-checkout', compact('snapToken', 'transaction'));
    }

    public function webhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $notification = null;
        
        try {
            // Log incoming webhook
            Log::info('🔔 Webhook received from Midtrans', [
                'ip' => $request->ip(),
                'method' => $request->method(),
                'content_type' => $request->header('content-type'),
                'timestamp' => now()->toDateTimeString(),
            ]);

            $notification = json_decode($request->getContent(), true);

            if (!$notification) {
                Log::warning('Invalid JSON in webhook payload');
                return response()->json(['status' => 'ok'], 200); // Still return 200 to acknowledge
            }

            Log::info('Webhook Payload:', [
                'order_id' => $notification['order_id'] ?? null,
                'transaction_status' => $notification['transaction_status'] ?? null,
                'gross_amount' => $notification['gross_amount'] ?? null,
            ]);

            // ⚠️ CRITICAL: Verify webhook using comprehensive security service
            // This includes: signature verification, IP check, amount validation, rate limiting
            MidtransWebhookSecurityService::verifyWebhook($request, $notification);
            MidtransWebhookSecurityService::checkRateLimit($notification['order_id'] ?? '');

            $orderId = $notification['order_id'] ?? null;
            $transactionStatus = $notification['transaction_status'] ?? null;
            $fraudStatus = $notification['fraud_status'] ?? null;
            $grossAmount = $notification['gross_amount'] ?? null;

            if (!$orderId) {
                Log::warning('Webhook received without order_id');
                return response()->json(['status' => 'ok'], 200); // Return 200 to prevent Midtrans retry
            }

            // IMPORTANT: Process webhook asynchronously to prevent timeout
            // Queue the webhook processing job to ensure Midtrans gets 200 OK response quickly
            \Illuminate\Support\Facades\Queue::pushOn(
                'default',
                new \App\Jobs\ProcessMidtransWebhook($orderId, $notification)
            );

            // Return 200 OK immediately to Midtrans
            // The actual processing happens in the job queue
            Log::info('✅ Webhook queued for processing: ' . $orderId);
            return response()->json(['status' => 'ok', 'message' => 'Webhook queued for processing'], 200);
            
        } catch (\Exception $e) {
            // Log the error but still return 200 OK to prevent Midtrans from retrying excessively
            Log::error('❌ Webhook Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'order_id' => $notification['order_id'] ?? 'unknown',
            ]);

            // Log failed webhook attempt for security
            if ($notification) {
                Log::warning(
                    '⚠️ Webhook validation failed',
                    MidtransWebhookSecurityService::auditLog($request, $notification, 'failed')
                );
            }

            // CRITICAL: Return 200 OK even on error to prevent Midtrans from retrying excessively
            // The cron job will handle missed updates every 5 minutes
            return response()->json(['status' => 'ok', 'message' => 'Acknowledged'], 200);
        }
    }

    /**
     * Verify Midtrans webhook signature to prevent spoofed callbacks
     * ⚠️ CRITICAL SECURITY: This prevents attackers from faking payment confirmations
     */
    private function verifyMidtransSignature(array $notification): void
    {
        $orderId = $notification['order_id'] ?? null;
        $statusCode = $notification['status_code'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? null;
        $serverKey = config('services.midtrans.server_key');
        $signatureKey = $notification['signature_key'] ?? null;

        if (!$signatureKey || !$serverKey) {
            throw new \Exception('Missing signature key or server key configuration');
        }

        // Reconstruct signature as per Midtrans documentation
        $inputString = $orderId . $statusCode . $grossAmount . $serverKey;
        $computedSignature = hash('sha512', $inputString);

        // Compare signatures (use timing-safe comparison)
        if (!hash_equals($computedSignature, $signatureKey)) {
            Log::warning('Midtrans Signature Verification Failed', [
                'order_id' => $orderId,
                'expected' => $signatureKey,
                'computed' => $computedSignature,
            ]);
            throw new \Exception('Invalid Midtrans signature. Possible spoofed webhook.');
        }
    }

    protected function handleTopupWebhook($transaction, $status, $fraudStatus, $grossAmount): void
    {
        $successContext = null;
        $failureContext = null;

        if ($status === 'settlement' || $status === 'capture') {
            if ($fraudStatus === 'accept') {
                // Verify amount matches
                if ($grossAmount != $transaction->amount) {
                    Log::warning('Amount mismatch in webhook', [
                        'transaction_id' => $transaction->id,
                        'expected' => $transaction->amount,
                        'received' => $grossAmount,
                    ]);
                    // Still process but log the mismatch
                }

                DB::transaction(function () use ($transaction, $grossAmount, &$successContext) {
                    // Use pessimistic locking to prevent duplicate processing
                    $transaction->lockForUpdate()->refresh();
                    if ($transaction->status === 'success') {
                        Log::info('Transaction already processed, skipping: ' . $transaction->id);
                        return;
                    }

                    $transaction->status = 'success';
                    $transaction->save();

                    $baseCurrency = $this->currencyService->getBaseCurrency();
                    // Use lockForUpdate to prevent race condition on wallet balance
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

                    Log::info('Top-up successful', [
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
        } elseif ($status === 'deny' || $status === 'expire' || $status === 'cancel') {
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
            $this->notificationService->notifyTopupSuccess(
                $successContext['user'],
                $successContext['amount'],
                $successContext['balance'],
                $transaction->id
            );
        }

        if ($failureContext && $failureContext['user']) {
            $this->notificationService->notifyTopupFailed(
                $failureContext['user'],
                $failureContext['amount'],
                $failureContext['status'],
                $transaction->id
            );
        }
    }

    protected function handlePurchaseWebhook($transaction, $status, $fraudStatus): void
    {
        // Will be handled in MarketplaceController purchase method
        // This is for future use if needed
    }

    /**
     * Handle payment finish redirect from Midtrans.
     * This method checks the actual transaction status from Midtrans API
     * and updates the wallet balance if payment is successful.
     */
    public function paymentFinish(Request $request): RedirectResponse
    {
        $orderId = $request->get('order_id');

        if (!$orderId) {
            return redirect()->route('wallet.index')
                ->with('warning', 'Order ID tidak ditemukan. Silakan cek status transaksi di halaman wallet.');
        }

        $transaction = \App\Models\Transaction::where('midtrans_order_id', $orderId)->first();

        if (!$transaction) {
            Log::warning('Transaction not found for order_id in paymentFinish: ' . $orderId);
            return redirect()->route('wallet.index')
                ->with('warning', 'Transaksi tidak ditemukan. Silakan hubungi support jika masalah berlanjut.');
        }

        // Check transaction status from Midtrans API (important for local testing without webhook)
        try {
            $midtransStatus = MidtransTransaction::status($orderId);
            $transactionStatus = $midtransStatus->transaction_status ?? null;
            $fraudStatus = $midtransStatus->fraud_status ?? null;
            $grossAmount = $midtransStatus->gross_amount ?? null;

            $successContext = null;
            $failureContext = null;

            Log::info('Payment Finish - Checking Midtrans Status:', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'current_db_status' => $transaction->status,
            ]);

            // If transaction is already successful, just redirect
            if ($transaction->status === 'success') {
                return redirect()->route('wallet.index')
                    ->with('success', 'Pembayaran berhasil! Saldo wallet telah diperbarui.');
            }

            // Handle successful payment
            if (in_array($transactionStatus, ['settlement', 'capture']) && $fraudStatus === 'accept') {
                // Process top-up if this is a top-up transaction
                if ($transaction->payment_method === 'topup') {
                    DB::transaction(function () use ($transaction, $grossAmount, &$successContext) {
                        // Double-check to prevent duplicate processing
                        $transaction->lockForUpdate()->refresh();
                        if ($transaction->status === 'success') {
                            Log::info('Transaction already processed in paymentFinish: ' . $transaction->id);
                            return;
                        }

                        $transaction->status = 'success';
                        $transaction->save();

                        $baseCurrency = $this->currencyService->getBaseCurrency();
                        // Use lockForUpdate to prevent race condition on wallet balance
                        $wallet = Wallet::where('user_id', $transaction->buyer_id)
                            ->lockForUpdate()
                            ->firstOrCreate(
                                ['user_id' => $transaction->buyer_id],
                                ['balance' => 0, 'currency' => $baseCurrency]
                            );

                        if ($wallet->currency !== $baseCurrency) {
                            $wallet->currency = $baseCurrency;
                        }

                        $amountToAdd = (float) ($grossAmount ?? $transaction->amount);
                        $wallet->balance = (float) $wallet->balance + $amountToAdd;
                        $wallet->save();

                        // Update user wallet_balance to keep in sync
                        $user = $transaction->buyer;
                        $user->wallet_balance = (float) $wallet->balance;
                        $user->save();

                        Log::info('Top-up successful via paymentFinish', [
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

                    if ($successContext) {
                        $this->notificationService->notifyTopupSuccess(
                            $successContext['user'],
                            $successContext['amount'],
                            $successContext['balance'],
                            $transaction->id
                        );
                    }

                    return redirect()->route('wallet.index')
                        ->with('success', 'Pembayaran berhasil! Saldo wallet telah diperbarui.');
                }
            } elseif ($transactionStatus === 'pending') {
                // SPECIAL HANDLING FOR SANDBOX: Credit Card payments in sandbox might show as pending
                // but are actually successful. Check payment_type to determine if we should wait or process.
                $paymentType = $midtransStatus->payment_type ?? null;
                $isCreditCard = in_array($paymentType, ['credit_card', 'cstore']);

                // For Credit Card in sandbox, if status is pending but fraud_status is accept,
                // it usually means the payment is successful but not yet settled
                // In sandbox, Credit Card usually settles immediately, so we can process it
                if ($isCreditCard && $fraudStatus === 'accept' && !config('services.midtrans.is_production', false)) {
                    Log::info('Sandbox Credit Card pending with accept fraud_status - processing as success', [
                        'order_id' => $orderId,
                        'payment_type' => $paymentType,
                    ]);

                    // Process as success for sandbox Credit Card
                    if ($transaction->payment_method === 'topup') {
                        DB::transaction(function () use ($transaction, $grossAmount, &$successContext) {
                            $transaction->refresh();
                            if ($transaction->status === 'success') {
                                return;
                            }

                            $transaction->status = 'success';
                            $transaction->save();

                            $baseCurrency = $this->currencyService->getBaseCurrency();
                            $wallet = Wallet::firstOrCreate(
                                ['user_id' => $transaction->buyer_id],
                                ['balance' => 0, 'currency' => $baseCurrency]
                            );
                            if ($wallet->currency !== $baseCurrency) {
                                $wallet->currency = $baseCurrency;
                            }

                            $wallet->balance += $grossAmount ?? $transaction->amount;
                            $wallet->save();

                            $user = $transaction->buyer;
                            $user->wallet_balance = $wallet->balance;
                            $user->save();

                            Log::info('Top-up successful via paymentFinish (sandbox credit card)', [
                                'transaction_id' => $transaction->id,
                                'user_id' => $user->id,
                                'amount' => $grossAmount ?? $transaction->amount,
                                'new_balance' => $wallet->balance,
                            ]);

                            $successContext = [
                                'user' => $user,
                                'amount' => (float) ($grossAmount ?? $transaction->amount),
                                'balance' => (float) $wallet->balance,
                            ];
                        });

                        if ($successContext) {
                            $this->notificationService->notifyTopupSuccess(
                                $successContext['user'],
                                $successContext['amount'],
                                $successContext['balance'],
                                $transaction->id
                            );
                        }

                        return redirect()->route('wallet.index')
                            ->with('success', 'Pembayaran berhasil! Saldo wallet telah diperbarui.');
                    }
                }

                // Regular pending handling
                // Payment is still pending
                if ($transaction->status !== 'pending') {
                    $transaction->status = 'pending';
                    $transaction->save();
                }

                return redirect()->route('wallet.index')
                    ->with('info', 'Pembayaran sedang diproses. Saldo akan diperbarui setelah pembayaran dikonfirmasi.');
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                // Payment failed
                if ($transaction->status !== 'failed') {
                    $transaction->status = 'failed';
                    $transaction->save();

                    $failureContext = [
                        'user' => $transaction->buyer,
                        'amount' => (float) $transaction->amount,
                        'status' => $transactionStatus,
                    ];
                }

                if ($failureContext && $failureContext['user']) {
                    $this->notificationService->notifyTopupFailed(
                        $failureContext['user'],
                        $failureContext['amount'],
                        $failureContext['status'],
                        $transaction->id
                    );
                }

                return redirect()->route('wallet.index')
                    ->with('error', 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
            } elseif ($fraudStatus === 'challenge') {
                // Payment is under review
                if ($transaction->status !== 'pending') {
                    $transaction->status = 'pending';
                    $transaction->save();
                }

                return redirect()->route('wallet.index')
                    ->with('info', 'Pembayaran sedang direview. Saldo akan diperbarui setelah pembayaran dikonfirmasi.');
            }
        } catch (\Exception $e) {
            Log::error('Error checking Midtrans status in paymentFinish:', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Fallback: check if we have status from query parameter
            $queryStatus = $request->get('transaction_status');
            if ($queryStatus && in_array($queryStatus, ['settlement', 'capture'])) {
                return redirect()->route('wallet.index')
                    ->with('success', 'Pembayaran berhasil! Saldo wallet telah diperbarui.');
            }
        }

        return redirect()->route('wallet.index')
            ->with('info', 'Terima kasih! Pembayaran Anda sedang diproses.');
    }

    /**
     * Handle payment unfinish redirect from Midtrans.
     */
    public function paymentUnfinish(Request $request): RedirectResponse
    {
        $orderId = $request->get('order_id');

        if ($orderId) {
            $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

            if ($transaction && $transaction->status === 'pending') {
                return redirect()->route('wallet.index')
                    ->with('info', 'Pembayaran belum selesai. Silakan selesaikan pembayaran Anda untuk mengaktifkan saldo.');
            }
        }

        return redirect()->route('wallet.index')
            ->with('warning', 'Pembayaran belum selesai. Silakan coba lagi atau hubungi support jika ada masalah.');
    }

    /**
     * Handle payment error redirect from Midtrans.
     */
    public function paymentError(Request $request): RedirectResponse
    {
        $orderId = $request->get('order_id');
        $transactionStatus = $request->get('transaction_status');

        if ($orderId) {
            $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

            if ($transaction) {
                // Update transaction status to failed if not already updated
                if ($transaction->status === 'pending') {
                    $transaction->status = 'failed';
                    $transaction->save();

                    if ($transaction->buyer) {
                        $this->notificationService->notifyTopupFailed(
                            $transaction->buyer,
                            (float) $transaction->amount,
                            $transactionStatus ?? 'error',
                            $transaction->id
                        );
                    }
                }
            }
        }

        return redirect()->route('wallet.index')
            ->with('error', 'Pembayaran gagal. Silakan coba lagi atau gunakan metode pembayaran lain.');
    }

    /**
     * Handle payment callback (alternative webhook endpoint).
     * This can be used for recurring payments or pay account notifications.
     */
    public function paymentCallback(Request $request): \Illuminate\Http\JsonResponse
    {
        // This endpoint can handle additional payment notifications
        // For now, redirect to webhook handler
        return $this->webhook($request);
    }

    /**
     * Admin transaction report - view all user transactions
     */
    public function adminReport(Request $request): View
    {
        // Only admin can access this
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        // Get filter parameters
        $userSearch = $request->input('user_search', '');
        $type = $request->input('type', ''); // purchase, topup, withdraw, commission, etc
        $status = $request->input('status', ''); // pending, completed, failed, etc
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        // Build query
        $query = Transaction::with(['buyer', 'seller', 'note']);

        // Filter by user (buyer or seller name/email)
        if ($userSearch) {
            $query->whereHas('buyer', function ($q) use ($userSearch) {
                $q->where('name', 'like', "%{$userSearch}%")
                    ->orWhere('email', 'like', "%{$userSearch}%");
            })->orWhereHas('seller', function ($q) use ($userSearch) {
                $q->where('name', 'like', "%{$userSearch}%")
                    ->orWhere('email', 'like', "%{$userSearch}%");
            });
        }

        // Filter by transaction type
        if ($type) {
            $query->where('type', $type);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Get statistics
        $totalTransactions = Transaction::count();
        $totalAmount = Transaction::where('status', 'completed')->sum('amount');
        $totalCommission = Transaction::where('status', 'completed')->sum('commission');
        $pendingAmount = Transaction::where('status', 'pending')->sum('amount');

        // Transaction status breakdown
        $byStatus = Transaction::selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get();

        // Transaction breakdown by payment method (as type substitute)
        $byType = Transaction::where('status', 'completed')
            ->selectRaw('COALESCE(payment_method, "other") as payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        // Get paginated transactions
        $transactions = $query->latest()->paginate(50);

        return view('wallet.admin-report', compact(
            'transactions',
            'totalTransactions',
            'totalAmount',
            'totalCommission',
            'pendingAmount',
            'byType',
            'byStatus',
            'userSearch',
            'type',
            'status',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Export admin transaction report to CSV
     */
    public function exportReport(Request $request)
    {
        // Only admin can access this
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        // Get filter parameters
        $userSearch = $request->input('user_search', '');
        $type = $request->input('type', '');
        $status = $request->input('status', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        // Build query
        $query = Transaction::with(['buyer', 'seller', 'note']);

        if ($userSearch) {
            $query->whereHas('buyer', function ($q) use ($userSearch) {
                $q->where('name', 'like', "%{$userSearch}%")
                    ->orWhere('email', 'like', "%{$userSearch}%");
            })->orWhereHas('seller', function ($q) use ($userSearch) {
                $q->where('name', 'like', "%{$userSearch}%")
                    ->orWhere('email', 'like', "%{$userSearch}%");
            });
        }

        if ($type) {
            $query->where('payment_method', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $transactions = $query->latest()->get();

        // Generate CSV
        $filename = 'transaction-report-' . now()->format('Y-m-d-H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Date',
                'Payment Method',
                'Buyer',
                'Seller',
                'Amount',
                'Commission',
                'Status',
                'Note',
                'Description'
            ]);

            // Data rows
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->payment_method ?? 'other',
                    $transaction->buyer?->name ?? '-',
                    $transaction->seller?->name ?? '-',
                    number_format($transaction->amount, 2),
                    number_format($transaction->commission ?? 0, 2),
                    $transaction->status,
                    $transaction->note?->title ?? '-',
                    $transaction->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
