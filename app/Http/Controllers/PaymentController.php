<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
                // Note: For consistency and security, only 'settlement' status is accepted as final payment
                // 'capture' status is NOT final - payment can still be charged back
                // This matches the order webhook handling logic
                if ($transactionStatus === 'settlement') {
                    // Settlement is the final confirmed state - payment is guaranteed
                    // Process successful payment
                    $this->topUpService->processTopUpSuccess($topUp);
                    Log::info("Top-up processed successfully (settlement): {$topUpId}");
                } elseif ($transactionStatus === 'capture') {
                    // Capture means payment is authorized but NOT yet settled
                    // Do NOT process yet - wait for settlement
                    // Check for fraudulent captures (capture with fraud_status = deny)
                    if ($fraudStatus === 'deny') {
                        // Fraudulent capture - mark as failed
                        $topUp->markAsFailed();
                        Log::info("Top-up payment failed due to fraud: {$orderId}, status: {$transactionStatus}, fraud_status: {$fraudStatus}");
                    } else {
                        // Valid capture but not settled yet - keep as pending
                        Log::info("Top-up payment captured but not settled yet: {$orderId}");
                    }
                } elseif ($transactionStatus === 'pending') {
                    // Payment is pending - no action needed, just log
                    Log::info("Top-up payment pending: {$orderId}");
                } elseif ($transactionStatus === 'deny' || $transactionStatus === 'expire' || $transactionStatus === 'cancel') {
                    // Payment failed
                    $topUp->markAsFailed();
                    Log::info("Top-up payment failed: {$orderId}, status: {$transactionStatus}");
                    // Note: Top-up failure notification can be added later if needed
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
                    if ($order->payment_status === 'paid') {
                        // IMPORTANT: Apply commission BEFORE completing the order
                        // This ensures commission data is set even if completeOrder fails
                        // applyCommission is idempotent, so it's safe to call multiple times
                        $commissionData = $this->commissionService->applyCommission($order);

                        // Refresh order to get updated commission fields
                        $order->refresh();

                        // Complete the order only if not already completed
                        // This is idempotent - completeOrder checks status before updating
                        if ($order->status !== 'completed') {
                            $this->marketplaceService->completeOrder($order);
                            $order->refresh();
                        }

                        // Add balance to seller (seller amount after commission deduction)
                        // Only add if seller_amount hasn't been added yet (check transaction history)
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
