<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Campaign $campaign,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Campaign Suspended')
            ->line("Your campaign '{$this->campaign->title}' has been suspended.")
            ->line("Reason: {$this->reason}")
            ->action('View Campaign', route('clipper.campaigns.show', $this->campaign));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'campaign_suspended',
            'campaign_id' => $this->campaign->id,
            'campaign_title' => $this->campaign->title,
            'reason' => $this->reason,
            'suspended_at' => now(),
            'title' => 'Campaign Suspended',
            'message' => "Your campaign '{$this->campaign->title}' has been suspended. Reason: {$this->reason}",
        ];
    }
}

