<?php

namespace App\Events;

use App\Models\AffiliatePayout;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AffiliatePayoutRequested implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(
        public AffiliatePayout $payout,
        public string $affiliateName,
        public string $email
    ) {}

    public function broadcastOn(): Channel|array
    {
        return new PrivateChannel('admin-notifications');
    }

    public function broadcastAs(): string
    {
        return 'payout.requested';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->payout->id,
            'affiliate_name' => $this->affiliateName,
            'amount' => $this->payout->amount,
            'method' => $this->payout->payout_method,
            'created_at' => $this->payout->created_at?->format('M d, Y H:i'),
        ];
    }
}
