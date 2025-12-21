<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create payment transaction.
     */
    public function createTransaction(Order $order, array $customerDetails = []): array
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => array_merge([
                'first_name' => $order->buyer->name,
                'email' => $order->buyer->email,
            ], $customerDetails),
            'item_details' => [
                [
                    'id' => $order->product_id,
                    'price' => (int) $order->price,
                    'quantity' => $order->quantity,
                    'name' => $order->product->name,
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            $order->update([
                'midtrans_order_id' => $order->order_number,
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'redirect_url' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans create transaction error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle payment notification webhook.
     */
    public function handleWebhook(array $data): bool
    {
        try {
            $orderId = $data['order_id'] ?? null;
            $transactionStatus = $data['transaction_status'] ?? null;
            $fraudStatus = $data['fraud_status'] ?? null;

            if (!$orderId) {
                return false;
            }

            $order = Order::where('order_number', $orderId)->first();

            if (!$order) {
                Log::warning("Order not found: {$orderId}");
                return false;
            }

            // Verify webhook signature
            if (!$this->verifyWebhookSignature($data)) {
                Log::warning("Invalid webhook signature for order: {$orderId}");
                return false;
            }

            // Update transaction ID
            if (isset($data['transaction_id'])) {
                $order->update([
                    'midtrans_transaction_id' => $data['transaction_id'],
                ]);
            }

            // Handle transaction status
            if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                if ($fraudStatus === 'accept') {
                    $order->markAsPaid();
                    return true;
                }
            } elseif ($transactionStatus === 'pending') {
                // Payment is pending
                $order->update(['payment_status' => 'pending']);
            } elseif ($transactionStatus === 'deny' || $transactionStatus === 'expire' || $transactionStatus === 'cancel') {
                // Payment failed
                $order->update(['payment_status' => 'failed']);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check transaction status.
     */
    public function checkTransactionStatus(string $transactionId): ?array
    {
        try {
            $status = Transaction::status($transactionId);
            return $status;
        } catch (\Exception $e) {
            Log::error('Midtrans check status error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(array $data): bool
    {
        // Midtrans doesn't provide signature verification in the same way
        // We'll verify by checking the transaction status via API
        $orderId = $data['order_id'] ?? null;
        
        if (!$orderId) {
            return false;
        }

        try {
            $status = Transaction::status($orderId);
            
            // Verify the status matches
            return isset($status->transaction_status) && 
                   isset($data['transaction_status']) &&
                   $status->transaction_status === $data['transaction_status'];
        } catch (\Exception $e) {
            Log::error('Webhook signature verification error: ' . $e->getMessage());
            return false;
        }
    }
}

