<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignEndedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Campaign $campaign
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Campaign Ended')
            ->line("Your campaign '{$this->campaign->title}' has ended.")
            ->line("Total Views: " . number_format($this->campaign->total_views, 0, ',', '.'))
            ->line("Total Clips: {$this->campaign->total_clips}")
            ->line("Total Spent: Rp " . number_format($this->campaign->total_spent, 0, ',', '.'))
            ->action('View Analytics', route('clipper.campaigns.analytics.show', $this->campaign));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'campaign_ended',
            'campaign_id' => $this->campaign->id,
            'title' => "Campaign Ended: {$this->campaign->title}",
            'message' => "Your campaign '{$this->campaign->title}' has ended.",
            'total_views' => $this->campaign->total_views,
            'total_clips' => $this->campaign->total_clips,
            'total_spent' => $this->campaign->total_spent,
        ];
    }
}
