<?php

namespace App\Notifications;

use App\Models\Story;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentionedInStoryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Story $story)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You were mentioned in a story')
            ->line("You were mentioned in a story by {$this->story->user->name}")
            ->action('View Story', route('stories.show', $this->story))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mentioned_in_story',
            'story_id' => $this->story->id,
            'user_id' => $this->story->user_id,
            'user_name' => $this->story->user->name ?? $this->story->user->business_name,
            'title' => 'You were mentioned in a story',
            'message' => ($this->story->user->name ?? $this->story->user->business_name) . ' mentioned you in a story',
        ];
    }
}
