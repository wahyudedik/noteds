<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventInviteNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Event $event,
        private User $organizer
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Undangan Acara: ' . $this->event->title)
            ->line('Kamu diundang oleh ' . $this->organizer->name . ' ke acara:')
            ->line($this->event->title)
            ->line('Tanggal: ' . $this->event->start_at->setTimezone($this->event->timezone)->toDayDateTimeString())
            ->action('Lihat Acara', url(route('events.show', $this->event->id)));
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'event_invite',
            'event_id' => $this->event->id,
            'title' => $this->event->title,
            'organizer' => $this->organizer->only(['id','name']),
        ];
    }
}
