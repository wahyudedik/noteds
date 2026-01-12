<?php

namespace App\Notifications;

use App\Models\CampaignCollaborator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignCollaborationAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CampaignCollaborator $collaboration
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $collaborator = $this->collaboration->user;
        $collaboratorName = $collaborator->business_name ?? $collaborator->name;

        return (new MailMessage)
            ->subject("Campaign Collaboration Accepted: {$this->collaboration->campaign->title}")
            ->line("{$collaboratorName} accepted your collaboration invitation for the campaign '{$this->collaboration->campaign->title}'.")
            ->action('View Campaign', route('clipper.campaigns.show', $this->collaboration->campaign));
    }

    public function toArray(object $notifiable): array
    {
        $collaborator = $this->collaboration->user;
        $collaboratorName = $collaborator->business_name ?? $collaborator->name;

        return [
            'type' => 'campaign_collaboration_accepted',
            'campaign_id' => $this->collaboration->campaign_id,
            'collaboration_id' => $this->collaboration->id,
            'collaborator_id' => $this->collaboration->user_id,
            'title' => 'Campaign Collaboration Accepted',
            'message' => "{$collaboratorName} accepted your collaboration invitation for the campaign '{$this->collaboration->campaign->title}'.",
        ];
    }
}
