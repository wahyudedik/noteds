<?php

namespace App\Events;

use App\Models\GroupEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class GroupEventCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public GroupEvent $event)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('group.' . $this->event->group_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->event->id,
            'title' => $this->event->title,
            'starts_at' => $this->event->starts_at,
            'ends_at' => $this->event->ends_at,
            'status' => $this->event->status,
        ];
    }
}
