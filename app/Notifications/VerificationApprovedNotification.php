<?php

namespace App\Notifications;

use App\Models\VerificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private VerificationType $type) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verification Approved')
            ->greeting('Congratulations!')
            ->line('Your verification has been approved.')
            ->line('Type: ' . $this->type->name)
            ->action('View Profile', url('/profile/' . $notifiable->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->type->slug,
            'message' => 'Your verification has been approved.',
        ];
    }
}
