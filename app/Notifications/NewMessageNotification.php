<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\UserMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public UserMessage $message,
        public User $sender
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Message from {$this->sender->name}")
            ->view('emails.notifications.new-message', [
                'notifiable' => $notifiable,
                'sender' => $this->sender,
                'message' => $this->message,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'new_message',
            'sender_id' => $this->sender->id,
            'message_id' => $this->message->id,
            'message' => "{$this->sender->name} sent you a message",
            'preview' => substr($this->message->message, 0, 100),
            'action_url' => route('messages.show', $this->sender),
        ];
    }
}
