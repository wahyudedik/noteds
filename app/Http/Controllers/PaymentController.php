<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Jobs\ProcessMidtransWebhook;
use App\Services\MidtransService;
use App\Services\MarketplaceService;
use App\Services\BalanceService;
use App\Services\NotificationService;
use App\Services\TopUpService;
use App\Services\MarketplaceCommissionService;
use App\Models\PlatformWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private MidtransService $midtransService,
        private MarketplaceService $marketplaceService,
        private BalanceService $balanceService,
        private NotificationService $notificationService,
        private TopUpService $topUpService,
        private MarketplaceCommissionService $commissionService
    ) {}

    /**
     * Handle Midtrans webhook notifications.
     * 
     * NOTE: This handler processes webhooks SYNCHRONOUSLY in the same request cycle.
     * All processing (signature verification, order updates, notifications, emails) 
     * happens immediately before returning response.
     * 
     * Trade-offs:
     * - Advantages: Immediate feedback, simpler debugging, no queue required
     * - Disadvantages: Blocking response, no automatic retry on transient failures
     * 
     * Future Enhancement Consideration:
     * - ProcessMidtransWebhook job exists but is not currently dispatched
     * - Could implement async processing by dispatching job here for better reliability
     * - Would require queue worker setup and monitoring
     * 
     * Always returns HTTP 200 to acknowledge receipt, even on errors.
     * This prevents Midtrans from retrying failed webhooks indefinitely.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

            // Check if this is a Top-Up (order_id starts with TOPUP-) → process via queue
            if (str_starts_with($orderId, 'TOPUP-')) {
                \App\Jobs\ProcessTopUpWebhook::dispatch($data);
                return response()->json(['status' => 'ok'], 200);
            }

            // Handle Marketplace Order webhook asynchronously
            if (str_starts_with($orderId, 'ORD-')) {
                ProcessMidtransWebhook::dispatch($data);
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
     * 
     * Recurring payments are used for subscription-based services or automatic recurring charges.
     * This handler processes recurring payment notifications and updates subscription status.
     * 
     * Expected webhook data structure:
     * - order_id: Unique order identifier for the recurring payment
     * - transaction_status: Payment status (settlement, capture, pending, deny, expire, cancel)
     * - fraud_status: Fraud detection status
     * - subscription_id: ID of the subscription (if applicable)
     * - recurring_type: Type of recurring payment (first, recurring)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recurring(Request $request)
    {
        $data = $request->all();

        Log::info('Midtrans recurring webhook received', $data);

        try {
            // Verify webhook signature for security
            if (!$this->midtransService->verifyWebhookSignature($data)) {
                Log::warning('Invalid webhook signature for recurring payment', $data);
                // Still return 200 to acknowledge receipt (prevent retries)
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 200);
            }

            $orderId = $data['order_id'] ?? null;
            $transactionStatus = $data['transaction_status'] ?? null;
            $fraudStatus = $data['fraud_status'] ?? null;
            $recurringType = $data['recurring_type'] ?? 'recurring';
            $subscriptionId = $data['subscription_id'] ?? null;

            if (!$orderId) {
                Log::warning('Recurring webhook: Order ID not found', $data);
                return response()->json(['status' => 'error', 'message' => 'Order ID not found'], 200);
            }

            Log::info("Processing recurring payment: {$orderId}, status: {$transactionStatus}, type: {$recurringType}");

            // Handle different transaction statuses
            if ($transactionStatus === 'settlement') {
                // Payment successfully settled - process the recurring payment
                // TODO: Implement subscription payment processing when subscription feature is added
                // Example implementation:
                // $subscription = Subscription::where('midtrans_order_id', $orderId)->first();
                // if ($subscription) {
                //     $subscription->processRecurringPayment($data);
                //     Log::info("Recurring payment processed successfully: {$orderId}");
                // }
                Log::info("Recurring payment settled (not processed - subscription feature not enabled): {$orderId}");
            } elseif ($transactionStatus === 'capture') {
                // Payment captured but not settled yet
                if ($fraudStatus === 'deny') {
                    Log::warning("Recurring payment failed due to fraud: {$orderId}");
                    // TODO: Handle fraudulent capture for subscriptions
                } else {
                    Log::info("Recurring payment captured but not settled: {$orderId}");
                }
            } elseif (in_array($transactionStatus, ['pending', 'authorize'])) {
                // Payment pending or authorized - wait for settlement
                Log::info("Recurring payment {$transactionStatus}: {$orderId}");
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                // Payment failed
                Log::warning("Recurring payment failed: {$orderId}, status: {$transactionStatus}");
                // TODO: Handle failed recurring payment (e.g., pause subscription, notify user)
            }

            // Always return 200 to acknowledge receipt
            return response()->json([
                'status' => 'ok',
                'message' => 'Recurring notification received and processed',
                'order_id' => $orderId,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Recurring payment webhook error: ' . $e->getMessage(), [
                'exception' => $e,
                'webhook_data' => $data,
            ]);
            // Always return 200 to acknowledge receipt, even on error
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 200);
        }
    }

    /**
     * Handle pay account notification from Midtrans.
     * 
     * Pay Account is Midtrans feature for direct bank account transfers.
     * This handler processes pay account payment notifications.
     * 
     * Expected webhook data structure:
     * - order_id: Unique order identifier for the pay account payment
     * - transaction_status: Payment status (settlement, pending, cancel, etc.)
     * - payment_type: Payment method type (e.g., 'bank_transfer', 'echannel')
     * - va_numbers: Virtual account numbers (if applicable)
     * - bank: Bank code (if applicable)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payAccount(Request $request)
    {
        $data = $request->all();

        Log::info('Midtrans pay account webhook received', $data);

        try {
            // Verify webhook signature for security
            if (!$this->midtransService->verifyWebhookSignature($data)) {
                Log::warning('Invalid webhook signature for pay account payment', $data);
                // Still return 200 to acknowledge receipt (prevent retries)
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 200);
            }

            $orderId = $data['order_id'] ?? null;
            $transactionStatus = $data['transaction_status'] ?? null;
            $paymentType = $data['payment_type'] ?? null;
            $fraudStatus = $data['fraud_status'] ?? null;

            if (!$orderId) {
                Log::warning('Pay account webhook: Order ID not found', $data);
                return response()->json(['status' => 'error', 'message' => 'Order ID not found'], 200);
            }

            Log::info("Processing pay account payment: {$orderId}, status: {$transactionStatus}, type: {$paymentType}");

            // Check if this is a Top-Up (order_id starts with TOPUP-)
            if (str_starts_with($orderId, 'TOPUP-')) {
                $topUpId = str_replace('TOPUP-', '', $orderId);
                $topUp = \App\Models\TopUp::find($topUpId);

                if ($topUp) {
                    // Update transaction ID if available
                    if (isset($data['transaction_id'])) {
                        $topUp->update([
                            'midtrans_transaction_id' => $data['transaction_id'],
                        ]);
                    }

                    // Handle transaction status for TopUp
                    if ($transactionStatus === 'settlement') {
                        $this->topUpService->processTopUpSuccess($topUp);
                        Log::info("Pay account top-up processed successfully: {$topUpId}");
                    } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                        $topUp->markAsFailed();
                        Log::info("Pay account top-up failed: {$orderId}, status: {$transactionStatus}");
                    }
                } else {
                    Log::warning("Top-up not found for pay account payment: {$orderId}");
                }
            }
            // Check if this is a Marketplace Order (order_id starts with ORD-)
            elseif (str_starts_with($orderId, 'ORD-')) {
                // Process order payment through standard webhook handler
                $result = $this->midtransService->handleWebhook($data);

                if ($result) {
                    $order = Order::where('order_number', $orderId)->first();
                    if ($order) {
                        $order->refresh();

                        // Process completed payment (same logic as main webhook)
                        if ($order->payment_status === 'paid') {
                            // Apply commission
                            $commissionData = $this->commissionService->applyCommission($order);
                            $order->refresh();

                            // Complete the order
                            if ($order->status !== 'completed') {
                                $this->marketplaceService->completeOrder($order);
                                $order->refresh();
                            }

                            // Add balance to seller
                            $seller = $order->product->seller;
                            $existingTransaction = \App\Models\Transaction::where('reference_id', $order->id)
                                ->where('type', 'sale')
                                ->first();
                            
                            if (!$existingTransaction) {
                                $this->balanceService->addBalance(
                                    $seller,
                                    $commissionData['seller_amount'],
                                    "Sale: Order #{$order->order_number}" . ($commissionData['commission_total'] > 0 ? " (Commission: Rp " . number_format($commissionData['commission_total'], 0, ',', '.') . ")" : ''),
                                    $order->id,
                                    'sale'
                                );
                            }

                            // Notify seller
                            $this->notificationService->notifyNewOrder($order);

                            Log::info("Pay account order payment processed successfully: {$orderId}");
                        }
                    } else {
                        Log::warning("Order not found for pay account payment: {$orderId}");
                    }
                }
            } else {
                // Unknown order_id format - log for investigation
                Log::warning("Unknown order_id format in pay account webhook: {$orderId}", $data);
            }

            // Always return 200 to acknowledge receipt
            return response()->json([
                'status' => 'ok',
                'message' => 'Pay account notification received and processed',
                'order_id' => $orderId,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Pay account webhook error: ' . $e->getMessage(), [
                'exception' => $e,
                'webhook_data' => $data,
            ]);
            // Always return 200 to acknowledge receipt, even on error
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 200);
        }
    }
}
