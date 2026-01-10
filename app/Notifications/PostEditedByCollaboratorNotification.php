<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostEditedByCollaboratorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Post $post,
        public User $collaborator
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $collaboratorName = $this->collaborator->business_name ?? $this->collaborator->name;

        return (new MailMessage)
            ->subject("Post Edited by Collaborator: {$this->post->title}")
            ->line("{$collaboratorName} edited your post '{$this->post->title}'.")
            ->action('View Post', route('posts.show', $this->post));
    }

    public function toArray(object $notifiable): array
    {
        $collaboratorName = $this->collaborator->business_name ?? $this->collaborator->name;

        return [
            'type' => 'post_edited_by_collaborator',
            'post_id' => $this->post->id,
            'collaborator_id' => $this->collaborator->id,
            'title' => 'Post Edited by Collaborator',
            'message' => "{$collaboratorName} edited your post '{$this->post->title}'.",
        ];
    }
}
