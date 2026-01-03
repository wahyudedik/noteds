<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Campaign $campaign
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $brandName = $this->campaign->creator?->name ?? 'Unknown';
        
        return (new MailMessage)
            ->subject('New Campaign Created')
            ->line("A new campaign has been created by {$brandName}.")
            ->line("Campaign: {$this->campaign->title}")
            ->line("Budget: Rp " . number_format($this->campaign->max_budget ?? 0, 0, ',', '.'))
            ->action('Review Campaign', route('admin.campaigns.show', $this->campaign));
    }

    public function toArray(object $notifiable): array
    {
        $brandName = $this->campaign->creator?->name ?? 'Unknown';
        
        return [
            'type' => 'campaign_created',
            'campaign_id' => $this->campaign->id,
            'campaign_title' => $this->campaign->title,
            'brand_name' => $brandName,
            'max_budget' => $this->campaign->max_budget ?? 0,
            'title' => 'New Campaign Created',
            'message' => "A new campaign '{$this->campaign->title}' has been created by {$brandName}.",
        ];
    }
}

