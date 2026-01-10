<?php

namespace App\Events;

use App\Models\Order;
use App\Models\OrderTrackingHistory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    public OrderTrackingHistory $trackingHistory;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, OrderTrackingHistory $trackingHistory)
    {
        $this->order = $order;
        $this->trackingHistory = $trackingHistory;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->order->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->trackingHistory->status,
            'payment_status' => $this->trackingHistory->payment_status,
            'message' => $this->trackingHistory->message,
            'updated_at' => $this->trackingHistory->created_at?->toIso8601String(),
        ];
    }
}
