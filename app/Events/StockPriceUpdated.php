<?php

namespace App\Events;

use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockPriceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Stock $stock;
    public StockPrice $price;

    /**
     * Create a new event instance.
     */
    public function __construct(Stock $stock, StockPrice $price)
    {
        $this->stock = $stock;
        $this->price = $price;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('stock.' . $this->stock->code . '.prices');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'price.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'stock_code' => $this->stock->code,
            'stock_name' => $this->stock->name,
            'date' => $this->price->date->toDateString(),
            'open' => (float) $this->price->open,
            'high' => (float) $this->price->high,
            'low' => (float) $this->price->low,
            'close' => (float) $this->price->close,
            'volume' => (int) $this->price->volume,
            'value' => (float) $this->price->value,
            'is_intraday' => $this->price->is_intraday,
            'timestamp' => $this->price->timestamp?->toIso8601String(),
        ];
    }
}

