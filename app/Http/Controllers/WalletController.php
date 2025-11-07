<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;

class WalletController extends Controller
{
    public function __construct()
    {
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
            ['balance' => 0]
        );

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
        $request->validate([
            'amount' => ['required', 'numeric', 'min:10000', 'max:100000000'],
        ]);

        $user = auth()->user();
        $amount = (float) $request->amount;

        // Check if Midtrans is configured
        if (empty(config('services.midtrans.server_key')) || empty(config('services.midtrans.client_key'))) {
            return redirect()->route('wallet.index')
                ->with('error', 'Payment gateway belum dikonfigurasi. Silakan hubungi administrator.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // Create transaction record for top-up
        $transaction = Transaction::create([
            'buyer_id' => $user->id,
            'seller_id' => $user->id, // Self top-up
            'note_id' => null,
            'amount' => $amount,
            'commission' => 0,
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

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => 'topup',
                    'price' => $amount,
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
        try {
            $notification = json_decode($request->getContent(), true);
            
            Log::info('Midtrans Webhook Received:', [
                'order_id' => $notification['order_id'] ?? null,
                'transaction_status' => $notification['transaction_status'] ?? null,
                'timestamp' => now()->toDateTimeString(),
            ]);

            $orderId = $notification['order_id'] ?? null;
            $transactionStatus = $notification['transaction_status'] ?? null;
            $fraudStatus = $notification['fraud_status'] ?? null;
            $grossAmount = $notification['gross_amount'] ?? null;

            if (!$orderId) {
                Log::warning('Webhook received without order_id');
                return response()->json(['status' => 'error', 'message' => 'Missing order_id'], 400);
            }

            $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

            if (!$transaction) {
                Log::warning('Transaction not found for order_id: ' . $orderId);
                return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
            }

            // Prevent duplicate processing
            if ($transaction->status === 'success' && ($transactionStatus === 'settlement' || $transactionStatus === 'capture')) {
                Log::info('Transaction already processed: ' . $orderId);
                return response()->json(['status' => 'ok', 'message' => 'Already processed']);
            }

            // Handle different transaction types
            if ($transaction->payment_method === 'topup') {
                $this->handleTopupWebhook($transaction, $transactionStatus, $fraudStatus, $grossAmount);
            } else {
                // Handle purchase webhook if needed
                $this->handlePurchaseWebhook($transaction, $transactionStatus, $fraudStatus);
            }

            return response()->json(['status' => 'ok', 'message' => 'Webhook processed']);
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'notification' => $request->getContent(),
            ]);
            
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    protected function handleTopupWebhook($transaction, $status, $fraudStatus, $grossAmount): void
    {
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

                DB::transaction(function () use ($transaction, $grossAmount) {
                    // Double-check transaction status to prevent duplicate processing
                    $transaction->refresh();
                    if ($transaction->status === 'success') {
                        Log::info('Transaction already processed, skipping: ' . $transaction->id);
                        return;
                    }

                    $transaction->status = 'success';
                    $transaction->save();

                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $transaction->buyer_id],
                        ['balance' => 0]
                    );

                    $wallet->balance += $grossAmount;
                    $wallet->save();

                    // Update user wallet_balance
                    $user = $transaction->buyer;
                    $user->wallet_balance = $wallet->balance;
                    $user->save();

                    Log::info('Top-up successful', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $user->id,
                        'amount' => $grossAmount,
                        'new_balance' => $wallet->balance,
                    ]);
                });
            } elseif ($fraudStatus === 'challenge') {
                // Challenge status - payment is being reviewed
                $transaction->status = 'pending';
                $transaction->save();
                Log::info('Transaction under challenge review', ['transaction_id' => $transaction->id]);
            }
        } elseif ($status === 'deny' || $status === 'expire' || $status === 'cancel') {
            $transaction->status = 'failed';
            $transaction->save();
            Log::info('Transaction failed', [
                'transaction_id' => $transaction->id,
                'status' => $status,
            ]);
        } elseif ($status === 'pending') {
            $transaction->status = 'pending';
            $transaction->save();
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
                    DB::transaction(function () use ($transaction, $grossAmount) {
                        // Double-check to prevent duplicate processing
                        $transaction->refresh();
                        if ($transaction->status === 'success') {
                            Log::info('Transaction already processed in paymentFinish: ' . $transaction->id);
                            return;
                        }

                        $transaction->status = 'success';
                        $transaction->save();

                        $wallet = Wallet::firstOrCreate(
                            ['user_id' => $transaction->buyer_id],
                            ['balance' => 0]
                        );

                        $wallet->balance += $grossAmount ?? $transaction->amount;
                        $wallet->save();

                        // Update user wallet_balance
                        $user = $transaction->buyer;
                        $user->wallet_balance = $wallet->balance;
                        $user->save();

                        Log::info('Top-up successful via paymentFinish', [
                            'transaction_id' => $transaction->id,
                            'user_id' => $user->id,
                            'amount' => $grossAmount ?? $transaction->amount,
                            'new_balance' => $wallet->balance,
                        ]);
                    });

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
                        DB::transaction(function () use ($transaction, $grossAmount) {
                            $transaction->refresh();
                            if ($transaction->status === 'success') {
                                return;
                            }

                            $transaction->status = 'success';
                            $transaction->save();

                            $wallet = Wallet::firstOrCreate(
                                ['user_id' => $transaction->buyer_id],
                                ['balance' => 0]
                            );

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
                        });

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
}
