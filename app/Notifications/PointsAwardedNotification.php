<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PointsAwardedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $points,
        private string $action
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Poin Ditambahkan: ' . $this->points)
            ->line('Kamu mendapatkan ' . $this->points . ' poin untuk: ' . $this->action);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'points_awarded',
            'points' => $this->points,
            'action' => $this->action,
        ];
    }
}
