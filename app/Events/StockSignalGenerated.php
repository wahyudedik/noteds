<?php

namespace App\Events;

use App\Models\StockSignal;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockSignalGenerated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public StockSignal $signal;

    /**
     * Create a new event instance.
     */
    public function __construct(StockSignal $signal)
    {
        $this->signal = $signal->load(['stock', 'mlModel']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('stock.' . $this->signal->stock->code . '.signals');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'signal.generated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->signal->id,
            'stock_code' => $this->signal->stock->code,
            'stock_name' => $this->signal->stock->name,
            'signal_type' => $this->signal->signal_type,
            'signal_strength' => $this->signal->signal_strength,
            'signal_date' => $this->signal->signal_date->toDateString(),
            'source' => $this->signal->source,
            'reason' => $this->signal->reason,
            'price_target' => $this->signal->price_target,
            'stop_loss' => $this->signal->stop_loss,
            'take_profit' => $this->signal->take_profit,
            'risk_level' => $this->signal->risk_level,
            'expires_at' => $this->signal->expires_at?->toIso8601String(),
        ];
    }
}

