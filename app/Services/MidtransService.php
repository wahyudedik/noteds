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
            // Note: Midtrans status flow: pending -> capture -> settlement (success) / deny/expire/cancel (failed)
            // Status 'capture' happens before settlement but is NOT final - payment can still be charged back
            // Only 'settlement' is the final confirmed state where payment is guaranteed
            if ($transactionStatus === 'settlement') {
                // Settlement is the final confirmed state - payment is guaranteed
                $order->markAsPaid();
                Log::info("Order marked as paid (settlement): {$orderId}");
                return true;
            } elseif ($transactionStatus === 'capture') {
                // Capture means payment is authorized but NOT yet settled
                // Do NOT mark as paid yet - wait for settlement
                // Update status to pending to reflect that payment is in progress
                $order->update(['payment_status' => 'pending']);
                Log::info("Order payment captured but not settled yet: {$orderId}");
            } elseif ($transactionStatus === 'pending') {
                // Payment is pending
                $order->update(['payment_status' => 'pending']);
                Log::info("Order payment pending: {$orderId}");
            } elseif ($transactionStatus === 'deny' || $transactionStatus === 'expire' || $transactionStatus === 'cancel') {
                // Payment failed
                $order->update(['payment_status' => 'failed']);
                Log::info("Order payment failed: {$orderId}, status: {$transactionStatus}");
                
                // Notify buyer about payment failure
                try {
                    $notificationService = app(\App\Services\NotificationService::class);
                    $failureReason = match($transactionStatus) {
                        'deny' => 'Payment was denied by payment provider',
                        'expire' => 'Payment expired',
                        'cancel' => 'Payment was cancelled',
                        default => 'Payment failed',
                    };
                    $notificationService->notifyPaymentFailed($order, $failureReason);
                } catch (\Exception $e) {
                    Log::warning('Failed to send payment failed notification', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
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
            // Convert stdClass to array
            return json_decode(json_encode($status), true);
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

