<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCampaignNotification extends Notification implements ShouldQueue
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
            ->subject('New Campaign Available')
            ->line("A new campaign is available: {$this->campaign->title}")
            ->line("CPM: Rp " . number_format($this->campaign->cpm, 0, ',', '.'))
            ->line("Budget: Rp " . number_format($this->campaign->max_budget, 0, ',', '.'))
            ->action('View Campaign', route('clipper.campaigns.available'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_campaign',
            'campaign_id' => $this->campaign->id,
            'title' => "New Campaign: {$this->campaign->title}",
            'message' => "A new campaign is available: {$this->campaign->title}",
            'cpm' => $this->campaign->cpm,
            'max_budget' => $this->campaign->max_budget,
        ];
    }
}
