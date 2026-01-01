<?php

namespace App\Notifications;

use App\Models\Clip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ViewValidatedNotification extends Notification implements ShouldQueue
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
            ->subject('Views Validated')
            ->line("Views for your clip in campaign '{$this->clip->campaign->title}' have been validated!")
            ->line("Valid Views: " . number_format($this->clip->valid_views, 0, ',', '.'))
            ->line("Pending Reward: Rp " . number_format($this->clip->pending_reward ?? 0, 0, ',', '.'))
            ->action('View Clip', route('clipper.clips.show', $this->clip));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'view_validated',
            'clip_id' => $this->clip->id,
            'title' => 'Views Validated',
            'message' => "Views for your clip in campaign '{$this->clip->campaign->title}' have been validated!",
            'campaign_title' => $this->clip->campaign->title,
            'valid_views' => $this->clip->valid_views,
            'pending_reward' => $this->clip->pending_reward ?? 0,
        ];
    }
}

