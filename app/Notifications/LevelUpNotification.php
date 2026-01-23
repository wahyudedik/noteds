<?php

namespace App\Notifications;

use App\Models\Level;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LevelUpNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Level $level
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Naik Level: ' . $this->level->name)
            ->line('Selamat! Kamu naik ke level: ' . $this->level->name);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'level_up',
            'level' => [
                'id' => $this->level->id,
                'name' => $this->level->name,
                'min_points' => $this->level->min_points,
            ],
        ];
    }
}
