<?php

namespace App\Notifications;

use App\Models\LiveStream;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class StreamEndedNotification extends Notification implements ShouldQueue
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
            ->subject('Stream Berakhir: ' . $this->stream->title)
            ->line('Stream telah berakhir.')
            ->line('Judul: ' . $this->stream->title)
            ->action('Lihat Stream', route('streams.show', $this->stream->id));
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'stream_ended',
            'stream_id' => $this->stream->id,
            'title' => $this->stream->title,
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'stream_ended',
            'stream_id' => $this->stream->id,
            'title' => $this->stream->title,
        ];
    }
}
