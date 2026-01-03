<?php

namespace App\Notifications;

use App\Models\Clip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClipSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Clip $clip
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Clip Submitted')
            ->line("A new clip has been submitted by {$this->clip->clipper->name}.")
            ->line("Campaign: {$this->clip->campaign->title}")
            ->line("Platform: {$this->clip->platform}")
            ->action('Review Clip', route('admin.clips.show', $this->clip));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'clip_submitted',
            'clip_id' => $this->clip->id,
            'campaign_id' => $this->clip->campaign_id,
            'campaign_title' => $this->clip->campaign->title,
            'clipper_name' => $this->clip->clipper->name,
            'platform' => $this->clip->platform,
            'submitted_at' => $this->clip->submitted_at,
            'title' => 'New Clip Submitted',
            'message' => "A new clip has been submitted by {$this->clip->clipper->name} for campaign '{$this->clip->campaign->title}'.",
        ];
    }
}

