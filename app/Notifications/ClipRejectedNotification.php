<?php

namespace App\Notifications;

use App\Models\Clip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClipRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Clip $clip
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Clip Rejected')
            ->line("Your clip for campaign '{$this->clip->campaign->title}' has been rejected.")
            ->line("Reason: {$this->clip->rejection_reason}")
            ->action('View Clip', route('clipper.clips.show', $this->clip));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'clip_rejected',
            'clip_id' => $this->clip->id,
            'title' => 'Clip Rejected',
            'message' => "Your clip for campaign '{$this->clip->campaign->title}' has been rejected.",
            'campaign_title' => $this->clip->campaign->title,
            'rejection_reason' => $this->clip->rejection_reason,
        ];
    }
}

