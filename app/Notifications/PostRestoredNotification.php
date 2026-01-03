<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostRestoredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Post $post,
        public ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Post Has Been Restored')
            ->line("Your post '{$this->post->title}' has been restored and is now visible.")
            ->when($this->reason, fn($mail) => $mail->line("Note: {$this->reason}"))
            ->action('View Post', route('posts.show', $this->post));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_restored',
            'post_id' => $this->post->id,
            'title' => 'Post Restored',
            'message' => "Your post '{$this->post->title}' has been restored and is now visible.",
            'reason' => $this->reason,
        ];
    }
}

