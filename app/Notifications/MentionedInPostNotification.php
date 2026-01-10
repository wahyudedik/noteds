<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentionedInPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Post $post)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You were mentioned in a post')
            ->line("You were mentioned in a post by {$this->post->user->name}")
            ->action('View Post', route('posts.show', $this->post))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mentioned_in_post',
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'user_id' => $this->post->user_id,
            'user_name' => $this->post->user->name ?? $this->post->user->business_name,
            'title' => 'You were mentioned in a post',
            'message' => "{$this->post->user->name ?? $this->post->user->business_name} mentioned you in a post: {$this->post->title}",
        ];
    }
}
