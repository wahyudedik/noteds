<?php

namespace App\Notifications;

use App\Models\PostCollaborator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CollaborationRejectedNotification extends Notification implements ShouldQueue
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
        $collaborator = $this->collaboration->user;
        $collaboratorName = $collaborator->business_name ?? $collaborator->name;

        return (new MailMessage)
            ->subject("Collaboration Rejected: {$this->collaboration->post->title}")
            ->line("{$collaboratorName} rejected your collaboration invitation for the post '{$this->collaboration->post->title}'.")
            ->action('View Post', route('posts.show', $this->collaboration->post));
    }

    public function toArray(object $notifiable): array
    {
        $collaborator = $this->collaboration->user;
        $collaboratorName = $collaborator->business_name ?? $collaborator->name;

        return [
            'type' => 'collaboration_rejected',
            'post_id' => $this->collaboration->post_id,
            'collaboration_id' => $this->collaboration->id,
            'collaborator_id' => $this->collaboration->user_id,
            'title' => 'Collaboration Rejected',
            'message' => "{$collaboratorName} rejected your collaboration invitation for the post '{$this->collaboration->post->title}'.",
        ];
    }
}
