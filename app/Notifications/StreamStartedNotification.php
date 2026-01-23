<?php

namespace App\Notifications;

use App\Models\LiveStream;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class StreamStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LiveStream $stream) {}

    public function via($notifiable): array
    {
        return ['mail', 'broadcast', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Stream Dimulai: ' . $this->stream->title)
            ->line('Stream telah dimulai.')
            ->line('Judul: ' . $this->stream->title)
            ->action('Lihat Stream', route('streams.show', $this->stream->id));
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'stream_started',
            'stream_id' => $this->stream->id,
            'title' => $this->stream->title,
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'stream_started',
            'stream_id' => $this->stream->id,
            'title' => $this->stream->title,
        ];
    }
}
