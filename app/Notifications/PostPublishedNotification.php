<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Post $post
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your Scheduled Post Has Been Published")
            ->line("Your scheduled post '{$this->post->title}' has been published.")
            ->action('View Post', route('posts.show', $this->post));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_published',
            'post_id' => $this->post->id,
            'title' => 'Post Published',
            'message' => "Your scheduled post '{$this->post->title}' has been published.",
        ];
    }
}
