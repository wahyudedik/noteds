<?php

namespace App\Notifications;

use App\Models\VerificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private VerificationType $type, private string $note) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verification Rejected')
            ->greeting('Update Verification')
            ->line('Your verification request has been rejected.')
            ->line('Type: ' . $this->type->name)
            ->line('Reason: ' . $this->note)
            ->action('Submit Again', url('/verification'));
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->type->slug,
            'message' => 'Your verification request has been rejected: ' . $this->note,
        ];
    }
}
