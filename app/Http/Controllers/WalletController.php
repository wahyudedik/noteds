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
            'amount' => ['required', 'numeric', 'min:10000'],
        ]);

        $user = auth()->user();
        $amount = $request->amount;

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

        $orderId = 'topup-' . $transaction->id . '-' . time();
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
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            return redirect()->route('wallet.topup-checkout', ['token' => $snapToken, 'transaction' => $transaction->id]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Error: ' . $e->getMessage());
            
            return redirect()->route('wallet.index')
                ->with('error', 'Gagal membuat transaksi top-up. Silakan coba lagi.');
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

    public function webhook(Request $request): void
    {
        try {
            $notification = json_decode($request->getContent(), true);
            
            Log::info('Midtrans Webhook:', $notification);

            $orderId = $notification['order_id'] ?? null;
            $transactionStatus = $notification['transaction_status'] ?? null;
            $fraudStatus = $notification['fraud_status'] ?? null;
            $grossAmount = $notification['gross_amount'] ?? null;

            if (!$orderId) {
                return;
            }

            $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

            if (!$transaction) {
                Log::warning('Transaction not found for order_id: ' . $orderId);
                return;
            }

            // Handle different transaction types
            if ($transaction->payment_method === 'topup') {
                $this->handleTopupWebhook($transaction, $transactionStatus, $fraudStatus, $grossAmount);
            } else {
                // Handle purchase webhook if needed
                $this->handlePurchaseWebhook($transaction, $transactionStatus, $fraudStatus);
            }
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    protected function handleTopupWebhook($transaction, $status, $fraudStatus, $grossAmount): void
    {
        if ($status === 'settlement' || $status === 'capture') {
            if ($fraudStatus === 'accept') {
                DB::transaction(function () use ($transaction, $grossAmount) {
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
                });
            }
        } elseif ($status === 'deny' || $status === 'expire' || $status === 'cancel') {
            $transaction->status = 'failed';
            $transaction->save();
        }
    }

    protected function handlePurchaseWebhook($transaction, $status, $fraudStatus): void
    {
        // Will be handled in MarketplaceController purchase method
        // This is for future use if needed
    }
}
