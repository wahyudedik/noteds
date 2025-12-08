<?php

namespace App\Events;

use App\Models\NoteShareReferral;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShareReferralCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public NoteShareReferral $shareReferral
    ) {
        $this->shareReferral->load(['note', 'sharedBy', 'seller']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('share-analytics'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'share.referral.created';
    }

    public function broadcastWith(): array
    {
        return [
            'seller_id' => $this->shareReferral->seller_id,
            'note_id' => $this->shareReferral->note_id,
            'shared_by_id' => $this->shareReferral->shared_by_id,
            'share_count' => $this->shareReferral->share_count,
        ];
    }
}
