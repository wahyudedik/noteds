<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Conversation $conversation;
    public string $action; // 'participant_added', 'participant_removed', 'name_changed', etc.

    /**
     * Create a new event instance.
     */
    public function __construct(Conversation $conversation, string $action = 'updated')
    {
        $this->conversation = $conversation->load(['participants.user']);
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('conversation.' . $this->conversation->id),
        ];

        // Also broadcast to user-specific channels for conversation list updates
        foreach ($this->conversation->activeParticipants as $participant) {
            $channels[] = new PrivateChannel('user.' . $participant->user_id . '.conversations');
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->conversation->id,
            'type' => $this->conversation->type,
            'name' => $this->conversation->name,
            'action' => $this->action,
            'last_message_at' => $this->conversation->last_message_at?->toIso8601String(),
            'participants' => $this->conversation->activeParticipants->map(function ($participant) {
                return [
                    'user_id' => $participant->user_id,
                    'user' => [
                        'id' => $participant->user->id,
                        'name' => $participant->user->name,
                        'avatar' => $participant->user->avatar_url,
                    ],
                    'role' => $participant->role,
                ];
            }),
        ];
    }
}
