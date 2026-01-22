<?php

namespace App\Events;

use App\Models\GroupInvite;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class GroupInviteCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public GroupInvite $invite)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('group.' . $this->invite->group_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->invite->id,
            'email' => $this->invite->email,
            'token' => $this->invite->token,
            'expires_at' => $this->invite->expires_at,
        ];
    }
}
