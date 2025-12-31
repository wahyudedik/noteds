<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrandApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
            ->subject('Brand Registration Approved')
            ->line("Congratulations! Your brand registration has been approved.")
            ->line("You can now create campaigns and start promoting your content.")
            ->action('Create Campaign', route('clipper.campaigns.create'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'brand_approved',
            'user_id' => $this->user->id,
            'title' => 'Brand Registration Approved',
            'message' => 'Congratulations! Your brand registration has been approved. You can now create campaigns.',
            'business_name' => $this->user->business_name,
        ];
    }
}
