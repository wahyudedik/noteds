<?php

namespace App\Jobs;

use App\Services\MidtransService;
use App\Services\MarketplaceService;
use App\Services\BalanceService;
use App\Services\NotificationService;
use App\Services\MarketplaceCommissionService;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process Midtrans webhook asynchronously via queue.
 * 
 * NOTE: This job class exists but is NOT currently dispatched from PaymentController.
 * Webhook processing happens synchronously in PaymentController::webhook() instead.
 * 
 * This job is primarily used in tests (see tests/Feature/Marketplace/WebhookRetryTest.php).
 * 
 * Future Enhancement:
 * - Could be dispatched from PaymentController::webhook() for async processing
 * - Would enable automatic retry on transient failures
 * - Would improve webhook response time (immediate 200 response)
 * - Requires queue worker to be running
 */
class ProcessMidtransWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $webhookData
    ) {}

    public function handle(
        MidtransService $midtransService,
        MarketplaceService $marketplaceService,
        BalanceService $balanceService,
        NotificationService $notificationService,
        MarketplaceCommissionService $commissionService
    ): void {
        try {
            $result = $midtransService->handleWebhook($this->webhookData);

            if ($result) {
                $orderId = $this->webhookData['order_id'] ?? null;
                if ($orderId) {
                    $order = Order::where('order_number', $orderId)->first();
                    
                    if ($order && $order->payment_status === 'paid') {
                        // IMPORTANT: Apply commission BEFORE completing the order
                        // This ensures commission data is set even if completeOrder fails
                        // applyCommission is idempotent, so it's safe to call multiple times
                        $commissionData = $commissionService->applyCommission($order);

                        // Refresh order to get updated commission fields
                        $order->refresh();

                        // Complete the order only if not already completed
                        // This is idempotent - completeOrder checks status before updating
                        if ($order->status !== 'completed') {
                            $marketplaceService->completeOrder($order);
                            $order->refresh();
                        }

                        // Add balance to seller (seller amount after commission deduction)
                        // Only add if seller_amount hasn't been added yet (check transaction history)
                        $seller = $order->product->seller;
                        $existingTransaction = \App\Models\Transaction::where('reference_id', $order->id)
                            ->where('type', 'sale')
                            ->first();
                        
                        if (!$existingTransaction) {
                            $balanceService->addBalance(
                                $seller,
                                $commissionData['seller_amount'],
                                "Sale: Order #{$order->order_number}" . ($commissionData['commission_total'] > 0 ? " (Commission: Rp " . number_format($commissionData['commission_total'], 0, ',', '.') . ")" : ''),
                                $order->id,
                                'sale'
                            );
                        }

                        // Notify seller (idempotent - notification service should handle duplicates)
                        $notificationService->notifyNewOrder($order);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('ProcessMidtransWebhook failed: ' . $e->getMessage(), [
                'webhook_data' => $this->webhookData,
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}
