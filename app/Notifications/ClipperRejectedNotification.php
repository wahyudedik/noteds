<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClipperRejectedNotification extends Notification
{
    public function __construct(
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Clipper Registration Rejected')
            ->line('Your clipper registration request has been rejected.')
            ->line("Reason: {$this->reason}")
            ->action('View Profile', route('profile.show'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'clipper_rejected',
            'title' => 'Clipper Registration Rejected',
            'message' => "Your clipper registration request has been rejected. Reason: {$this->reason}",
            'reason' => $this->reason,
        ];
    }
}