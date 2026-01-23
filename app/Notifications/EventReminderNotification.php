<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Event $event,
        private int $minutesBefore
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Pengingat Acara: ' . $this->event->title)
            ->line('Acara akan dimulai dalam ' . $this->minutesBefore . ' menit.')
            ->line('Tanggal: ' . $this->event->start_at->setTimezone($this->event->timezone)->toDayDateTimeString())
            ->action('Lihat Acara', url(route('events.show', $this->event->id)));
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'event_reminder',
            'event_id' => $this->event->id,
            'title' => $this->event->title,
            'minutes_before' => $this->minutesBefore,
        ];
    }
}
