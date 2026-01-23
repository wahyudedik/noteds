<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignalSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Conversation $conversation, public User $sender, public array $payload) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("conversation.{$this->conversation->id}")];
    }

    public function broadcastAs(): string
    {
        return 'call.signal';
    }
}
