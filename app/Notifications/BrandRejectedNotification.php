<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrandRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
            ->subject('Brand Registration Rejected')
            ->line('Your brand registration request has been rejected.')
            ->line("Reason: {$this->reason}")
            ->action('View Profile', route('profile.show'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'brand_rejected',
            'title' => 'Brand Registration Rejected',
            'message' => "Your brand registration request has been rejected. Reason: {$this->reason}",
            'reason' => $this->reason,
        ];
    }
}

