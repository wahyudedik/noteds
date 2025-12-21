<?php

namespace App\Jobs;

use App\Services\MidtransService;
use App\Services\MarketplaceService;
use App\Services\BalanceService;
use App\Services\NotificationService;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        NotificationService $notificationService
    ): void {
        try {
            $result = $midtransService->handleWebhook($this->webhookData);

            if ($result) {
                $orderId = $this->webhookData['order_id'] ?? null;
                if ($orderId) {
                    $order = Order::where('order_number', $orderId)->first();
                    
                    if ($order && $order->payment_status === 'paid') {
                        // Complete the order
                        $marketplaceService->completeOrder($order);

                        // Add balance to seller
                        $seller = $order->product->seller;
                        $balanceService->addBalance(
                            $seller,
                            $order->total,
                            "Sale: Order #{$order->order_number}",
                            $order->id,
                            'sale'
                        );

                        // Notify seller
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
