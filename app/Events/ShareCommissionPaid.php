<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShareCommissionPaid implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $seller,
        public float $amount,
        public string $month,
        public int $shareCount
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('seller-notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'share.commission.paid';
    }

    public function broadcastWith(): array
    {
        return [
            'seller_id' => $this->seller->id,
            'amount' => $this->amount,
            'month' => $this->month,
            'share_count' => $this->shareCount,
        ];
    }
}
