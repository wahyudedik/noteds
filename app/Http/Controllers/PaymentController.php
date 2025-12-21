<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use App\Services\MarketplaceService;
use App\Services\BalanceService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private MidtransService $midtransService,
        private MarketplaceService $marketplaceService,
        private BalanceService $balanceService,
        private NotificationService $notificationService
    ) {}

    public function webhook(Request $request)
    {
        $data = $request->all();

        Log::info('Midtrans webhook received', $data);

        $orderId = $data['order_id'] ?? null;
        if (!$orderId) {
            return response()->json(['status' => 'error', 'message' => 'Order ID not found'], 400);
        }

        $order = Order::where('order_number', $orderId)->first();
        if (!$order) {
            Log::warning("Order not found for webhook: {$orderId}");
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        // Handle webhook
        $result = $this->midtransService->handleWebhook($data);

        if ($result) {
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
                    $order->total,
                    "Sale: Order #{$order->order_number}",
                    $order->id,
                    'sale'
                );

                // Notify seller
                $this->notificationService->notifyNewOrder($order);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
