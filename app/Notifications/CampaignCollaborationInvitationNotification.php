<?php

namespace App\Notifications;

use App\Models\CampaignCollaborator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignCollaborationInvitationNotification extends Notification implements ShouldQueue
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
        $campaignOwner = $this->collaboration->campaign->creator;
        $ownerName = $campaignOwner->business_name ?? $campaignOwner->name;

        return (new MailMessage)
            ->subject("Campaign Collaboration Invitation: {$this->collaboration->campaign->title}")
            ->line("{$ownerName} invited you to collaborate on the campaign '{$this->collaboration->campaign->title}'.")
            ->action('View Campaign', route('clipper.campaigns.show', $this->collaboration->campaign));
    }

    public function toArray(object $notifiable): array
    {
        $campaignOwner = $this->collaboration->campaign->creator;
        $ownerName = $campaignOwner->business_name ?? $campaignOwner->name;

        return [
            'type' => 'campaign_collaboration_invitation',
            'campaign_id' => $this->collaboration->campaign_id,
            'collaboration_id' => $this->collaboration->id,
            'inviter_id' => $this->collaboration->invited_by,
            'title' => 'Campaign Collaboration Invitation',
            'message' => "{$ownerName} invited you to collaborate on the campaign '{$this->collaboration->campaign->title}'.",
        ];
    }
}
