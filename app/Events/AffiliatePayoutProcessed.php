<?php

namespace App\Events;

use App\Models\AffiliatePayout;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AffiliatePayoutProcessed implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(
        public AffiliatePayout $payout,
        public string $status,
        public ?string $notes = null
    ) {}

    public function broadcastOn(): Channel|array
    {
        return new PrivateChannel('affiliate-notifications-' . $this->payout->affiliate_id);
    }

    public function broadcastAs(): string
    {
        return 'payout.processed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->payout->id,
            'amount' => $this->payout->amount,
            'status' => $this->status,
            'method' => $this->payout->payout_method,
            'notes' => $this->notes,
            'processed_at' => now()->format('M d, Y H:i'),
        ];
    }
}
