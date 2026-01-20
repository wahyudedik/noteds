<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Message $message
    ) {
        $this->message->load(['user', 'conversation']);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $conversation = $this->message->conversation;
        $sender = $this->message->user;
        
        $conversationName = $conversation->type === 'direct' 
            ? $sender->name 
            : ($conversation->name ?? 'Group Chat');

        return [
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'conversation_id' => $conversation->id,
            'title' => 'New Message',
            'message' => $this->message->content 
                ? (strlen($this->message->content) > 100 ? substr($this->message->content, 0, 100) . '...' : $this->message->content)
                : 'Sent an attachment',
            'sender_name' => $sender->name,
            'sender_avatar' => $sender->avatar_url,
            'conversation_name' => $conversationName,
            'conversation_type' => $conversation->type,
        ];
    }
}
