<?php

namespace App\Notifications;

use App\Models\Clip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClipApprovedNotification extends Notification implements ShouldQueue
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
            ->subject('Clip Approved')
            ->line("Your clip for campaign '{$this->clip->campaign->title}' has been approved!")
            ->line("Views: " . number_format($this->clip->valid_views, 0, ',', '.'))
            ->line("Reward: Rp " . number_format($this->clip->approved_reward, 0, ',', '.'))
            ->action('View Clip', route('clipper.clips.show', $this->clip));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'clip_approved',
            'clip_id' => $this->clip->id,
            'title' => 'Clip Approved',
            'message' => "Your clip for campaign '{$this->clip->campaign->title}' has been approved!",
            'campaign_title' => $this->clip->campaign->title,
            'valid_views' => $this->clip->valid_views,
            'approved_reward' => $this->clip->approved_reward,
        ];
    }
}
