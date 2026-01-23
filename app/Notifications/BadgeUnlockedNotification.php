<?php

namespace App\Notifications;

use App\Models\Badge;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BadgeUnlockedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Badge $badge
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Badge Terbuka: ' . $this->badge->name)
            ->line('Selamat! Kamu membuka badge: ' . $this->badge->name);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'badge_unlocked',
            'badge' => [
                'id' => $this->badge->id,
                'name' => $this->badge->name,
                'icon' => $this->badge->icon,
            ],
        ];
    }
}
