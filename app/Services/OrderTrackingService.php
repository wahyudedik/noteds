<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderTrackingHistory;
use App\Models\User;
use App\Events\OrderStatusUpdated;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderTrackingService
{
    /**
     * Add tracking entry to order.
     */
    public function addTracking(
        Order $order,
        string $status,
        ?string $paymentStatus = null,
        ?string $message = null,
        ?User $updatedBy = null
    ): OrderTrackingHistory {
        return DB::transaction(function () use ($order, $status, $paymentStatus, $message, $updatedBy) {
            $tracking = OrderTrackingHistory::create([
                'order_id' => $order->id,
                'status' => $status,
                'payment_status' => $paymentStatus ?? $order->payment_status,
                'message' => $message,
                'updated_by' => $updatedBy?->id,
            ]);

            // Update order status
            $order->update([
                'status' => $status,
                'payment_status' => $paymentStatus ?? $order->payment_status,
                'last_tracked_at' => now(),
            ]);

            // Dispatch event for real-time updates
            event(new OrderStatusUpdated($order, $tracking));

            return $tracking;
        });
    }

    /**
     * Get tracking timeline for an order.
     */
    public function getTrackingTimeline(Order $order): Collection
    {
        return $order->trackingHistory()
            ->with('updatedBy')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get latest tracking status.
     */
    public function getLatestStatus(Order $order): ?OrderTrackingHistory
    {
        return $order->trackingHistory()
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Update order status with tracking.
     */
    public function updateOrderStatus(
        Order $order,
        string $status,
        ?string $paymentStatus = null,
        ?string $message = null
    ): void {
        $this->addTracking($order, $status, $paymentStatus, $message, auth()->user());
    }
}

