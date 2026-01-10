<?php

namespace App\Notifications;

use App\Models\PostCollaborator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CollaborationInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PostCollaborator $collaboration
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $postOwner = $this->collaboration->post->user;
        $ownerName = $postOwner->business_name ?? $postOwner->name;

        return (new MailMessage)
            ->subject("Collaboration Invitation: {$this->collaboration->post->title}")
            ->line("{$ownerName} invited you to collaborate on the post '{$this->collaboration->post->title}'.")
            ->action('View Post', route('posts.show', $this->collaboration->post));
    }

    public function toArray(object $notifiable): array
    {
        $postOwner = $this->collaboration->post->user;
        $ownerName = $postOwner->business_name ?? $postOwner->name;

        return [
            'type' => 'collaboration_invitation',
            'post_id' => $this->collaboration->post_id,
            'collaboration_id' => $this->collaboration->id,
            'inviter_id' => $this->collaboration->post->user_id,
            'title' => 'Collaboration Invitation',
            'message' => "{$ownerName} invited you to collaborate on the post '{$this->collaboration->post->title}'.",
        ];
    }
}
