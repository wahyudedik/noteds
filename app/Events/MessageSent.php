<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message->load(['user', 'media', 'replyTo']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'user_id' => $this->message->user_id,
            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
                'avatar' => $this->message->user->avatar_url,
            ],
            'content' => $this->message->content,
            'type' => $this->message->type,
            'reply_to_id' => $this->message->reply_to_id,
            'reply_to' => $this->message->replyTo ? [
                'id' => $this->message->replyTo->id,
                'content' => $this->message->replyTo->content,
                'user' => [
                    'name' => $this->message->replyTo->user->name,
                ],
            ] : null,
            'media' => $this->message->media->map(function ($media) {
                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'file_size' => $media->file_size,
                    'url' => $media->url,
                    'thumbnail_url' => $media->thumbnail_url,
                    'duration' => $media->duration,
                ];
            }),
            'is_edited' => $this->message->is_edited,
            'edited_at' => $this->message->edited_at?->toIso8601String(),
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
