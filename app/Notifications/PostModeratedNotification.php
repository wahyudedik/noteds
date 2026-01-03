<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostModeratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Post $post,
        public string $action,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $actionLabel = match($this->action) {
            'warn' => 'warned',
            'hide' => 'hidden',
            'delete' => 'deleted',
            default => 'moderated',
        };

        return (new MailMessage)
            ->subject("Your Post Has Been {$actionLabel}")
            ->line("Your post '{$this->post->title}' has been {$actionLabel}.")
            ->line("Reason: {$this->reason}")
            ->action('View Post', route('posts.show', $this->post));
    }

    public function toArray(object $notifiable): array
    {
        $actionLabel = match($this->action) {
            'warn' => 'warned',
            'hide' => 'hidden',
            'delete' => 'deleted',
            default => 'moderated',
        };

        return [
            'type' => 'post_moderated',
            'post_id' => $this->post->id,
            'action' => $this->action,
            'title' => "Post {$actionLabel}",
            'message' => "Your post '{$this->post->title}' has been {$actionLabel}. Reason: {$this->reason}",
            'reason' => $this->reason,
        ];
    }
}

