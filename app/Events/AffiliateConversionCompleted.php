<?php

namespace App\Events;

use App\Models\AffiliateConversion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AffiliateConversionCompleted implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(
        public AffiliateConversion $conversion,
        public float $commission,
        public int $tier
    ) {}

    public function broadcastOn(): Channel|array
    {
        return new PrivateChannel('affiliate-notifications-' . $this->conversion->affiliateLink->affiliate_id);
    }

    public function broadcastAs(): string
    {
        return 'conversion.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->conversion->id,
            'converter_name' => $this->conversion->converter?->username,
            'link_name' => $this->conversion->affiliateLink->name,
            'commission' => $this->commission,
            'tier' => $this->tier,
            'created_at' => $this->conversion->created_at?->format('M d, Y H:i'),
        ];
    }
}
