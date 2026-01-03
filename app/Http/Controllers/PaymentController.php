<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use App\Services\MarketplaceService;
use App\Services\BalanceService;
use App\Services\NotificationService;
use App\Services\TopUpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private MidtransService $midtransService,
        private MarketplaceService $marketplaceService,
        private BalanceService $balanceService,
        private NotificationService $notificationService,
        private TopUpService $topUpService
    ) {}

    public function webhook(Request $request)
    {
        $data = $request->all();

        Log::info('Midtrans webhook received', $data);

        try {
            $orderId = $data['order_id'] ?? null;
            if (!$orderId) {
                Log::warning('Midtrans webhook: Order ID not found', $data);
                // Always return 200 to acknowledge receipt, even on error
                return response()->json(['status' => 'error', 'message' => 'Order ID not found'], 200);
            }

            // Check if this is a Top-Up (order_id starts with TOPUP-)
            // Handle Top-Up webhook separately BEFORE calling handleWebhook (which only handles Orders)
            if (str_starts_with($orderId, 'TOPUP-')) {
                // Handle Top-Up webhook
                $topUpId = str_replace('TOPUP-', '', $orderId);
                $topUp = \App\Models\TopUp::find($topUpId);

                if (!$topUp) {
                    Log::warning("Top-up not found for order_id: {$orderId}", $data);
                    // Always return 200 to acknowledge receipt, even on error
                    return response()->json(['status' => 'error', 'message' => 'Top-up not found'], 200);
                }

                // Verify webhook signature for TopUp
                if (!$this->midtransService->verifyWebhookSignature($data)) {
                    Log::warning("Invalid webhook signature for top-up: {$orderId}");
                    // Still return 200 to acknowledge receipt
                    return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 200);
                }

                // Update top-up transaction ID if available
                if (isset($data['transaction_id'])) {
                    $topUp->update([
                        'midtrans_transaction_id' => $data['transaction_id'],
                    ]);
                }

                $transactionStatus = $data['transaction_status'] ?? null;
                $fraudStatus = $data['fraud_status'] ?? null;

                // Handle transaction status for TopUp
                // Check for fraudulent captures first (capture with fraud_status = deny)
                if ($transactionStatus === 'capture' && $fraudStatus === 'deny') {
                    // Fraudulent capture - mark as failed
                    $topUp->markAsFailed();
                    Log::info("Top-up payment failed due to fraud: {$orderId}, status: {$transactionStatus}, fraud_status: {$fraudStatus}");
                } elseif ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                    // For capture, check fraud status (must be accept or null)
                    // For settlement, payment is confirmed successful
                    if ($transactionStatus === 'settlement' || ($fraudStatus === 'accept' || $fraudStatus === null)) {
                        // Process successful payment
                        $this->topUpService->processTopUpSuccess($topUp);
                        Log::info("Top-up processed successfully: {$topUpId}");
                    }
                } elseif ($transactionStatus === 'pending') {
                    // Payment is pending - no action needed, just log
                    Log::info("Top-up payment pending: {$orderId}");
                } elseif ($transactionStatus === 'deny' || $transactionStatus === 'expire' || $transactionStatus === 'cancel') {
                    // Payment failed
                    $topUp->markAsFailed();
                    Log::info("Top-up payment failed: {$orderId}, status: {$transactionStatus}");
                }

                // Always return 200 to acknowledge receipt
                return response()->json(['status' => 'ok'], 200);
            }

            // Handle Marketplace Order webhook
            // Marketplace orders use format: ORD-YYYYMMDD-XXXXXX or ORD-{uniqid}
            if (str_starts_with($orderId, 'ORD-')) {
                // Handle webhook for Order
                $result = $this->midtransService->handleWebhook($data);

                if ($result) {
                    $order = Order::where('order_number', $orderId)->first();
                    if (!$order) {
                        Log::warning("Midtrans webhook: Order not found for order_id: {$orderId}", $data);
                        // Always return 200 to acknowledge receipt, even on error
                        return response()->json(['status' => 'error', 'message' => 'Order not found'], 200);
                    }

                    // Refresh order to get updated status from handleWebhook
                    $order->refresh();

                    // Process completed payment
                    if ($order->payment_status === 'paid' && $order->status !== 'completed') {
                        // Complete the order (generates license key, updates sales count, etc.)
                        $this->marketplaceService->completeOrder($order);

                        // Refresh again to get updated order
                        $order->refresh();

                        // Add balance to seller
                        $seller = $order->product->seller;
                        $this->balanceService->addBalance(
                            $seller,
                            (float) $order->total,
                            "Sale: Order #{$order->order_number}",
                            $order->id,
                            'sale'
                        );

                        // Notify seller
                        $this->notificationService->notifyNewOrder($order);

                        // Send email to buyer
                        try {
                            \Illuminate\Support\Facades\Mail::to($order->user->email)
                                ->send(new \App\Mail\PaymentSuccessMail($order));
                        } catch (\Exception $e) {
                            Log::error('Failed to send payment success email: ' . $e->getMessage());
                        }

                        // Send email to seller
                        try {
                            \Illuminate\Support\Facades\Mail::to($seller->email)
                                ->send(new \App\Mail\NewOrderMail($order));
                        } catch (\Exception $e) {
                            Log::error('Failed to send new order email: ' . $e->getMessage());
                        }
                    }
                }
            } else {
                // Unknown order_id format - log for investigation
                Log::warning("Unknown order_id format in webhook: {$orderId}", $data);
            }

            // Always return 200 to acknowledge receipt
            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error: ' . $e->getMessage(), [
                'exception' => $e,
                'webhook_data' => $data,
            ]);
            // Always return 200 to acknowledge receipt, even on error
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 200);
        }
    }

    /**
     * Handle recurring payment notification from Midtrans.
     * Currently not implemented but required to avoid Midtrans errors.
     */
    public function recurring(Request $request)
    {
        $data = $request->all();

        Log::info('Midtrans recurring webhook received', $data);

        // Always return 200 to acknowledge receipt
        // TODO: Implement recurring payment handling if needed in the future
        return response()->json(['status' => 'ok', 'message' => 'Recurring notification received'], 200);
    }

    /**
     * Handle pay account notification from Midtrans.
     * Currently not implemented but required to avoid Midtrans errors.
     */
    public function payAccount(Request $request)
    {
        $data = $request->all();

        Log::info('Midtrans pay account webhook received', $data);

        // Always return 200 to acknowledge receipt
        // TODO: Implement pay account handling if needed in the future
        return response()->json(['status' => 'ok', 'message' => 'Pay account notification received'], 200);
    }
}
