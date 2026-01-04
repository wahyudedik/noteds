<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClipperApprovedNotification extends Notification
{
    public function __construct(
        public User $user
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Clipper Registration Approved')
            ->line("Congratulations! Your clipper registration has been approved.")
            ->line("You can now submit clips to available campaigns and earn rewards.")
            ->action('View Available Campaigns', route('clipper.campaigns.available'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'clipper_approved',
            'user_id' => $this->user->id,
            'title' => 'Clipper Registration Approved',
            'message' => 'Congratulations! Your clipper registration has been approved. You can now submit clips to available campaigns.',
        ];
    }
}