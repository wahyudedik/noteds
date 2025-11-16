<?php

namespace App\Events;

use App\Models\AppNotification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public AppNotification $notification;

    public function __construct(AppNotification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->notification->user_id);
    }

    public function broadcastAs(): string
    {
        return 'NotificationCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'link' => $this->notification->link,
            'type' => $this->notification->type,
            'created_at' => optional($this->notification->created_at)->toDateTimeString(),
        ];
    }
}


