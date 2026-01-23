<?php

namespace App\Events;

use App\Models\LiveChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveChatMessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public LiveChatMessage $message) {}

    public function broadcastOn(): Channel
    {
        return new Channel('livestream.' . $this->message->live_stream_id);
    }

    public function broadcastAs(): string
    {
        return 'LiveChatMessageCreated';
    }
}
