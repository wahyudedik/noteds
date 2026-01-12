<?php

namespace App\Notifications;

use App\Models\CampaignCollaborator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignCollaborationRejectedNotification extends Notification implements ShouldQueue
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
            ->subject("Campaign Collaboration Rejected: {$this->collaboration->campaign->title}")
            ->line("{$collaboratorName} rejected your collaboration invitation for the campaign '{$this->collaboration->campaign->title}'.");
    }

    public function toArray(object $notifiable): array
    {
        $collaborator = $this->collaboration->user;
        $collaboratorName = $collaborator->business_name ?? $collaborator->name;

        return [
            'type' => 'campaign_collaboration_rejected',
            'campaign_id' => $this->collaboration->campaign_id,
            'collaboration_id' => $this->collaboration->id,
            'collaborator_id' => $this->collaboration->user_id,
            'title' => 'Campaign Collaboration Rejected',
            'message' => "{$collaboratorName} rejected your collaboration invitation for the campaign '{$this->collaboration->campaign->title}'.",
        ];
    }
}
