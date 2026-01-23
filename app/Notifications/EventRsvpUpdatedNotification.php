<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventRsvpUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Event $event,
        private User $responder,
        private string $status
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('RSVP Diperbarui: ' . $this->event->title)
            ->line($this->responder->name . ' memperbarui RSVP menjadi "' . $this->status . '".')
            ->action('Lihat Acara', url(route('events.show', $this->event->id)));
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'event_rsvp_updated',
            'event_id' => $this->event->id,
            'title' => $this->event->title,
            'responder' => $this->responder->only(['id','name']),
            'status' => $this->status,
        ];
    }
}
