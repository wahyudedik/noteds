<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\Repost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostRepostedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Post $post,
        public User $reposter,
        public ?Repost $repost = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reposterName = $this->reposter->business_name ?? $this->reposter->name;
        $repostType = $this->getRepostType();

        $message = (new MailMessage)
            ->subject("Your Post Was Reposted")
            ->line("{$reposterName} {$repostType} your post '{$this->post->title}'.");

        if ($this->repost && $this->repost->hasComment()) {
            $message->line('Comment: ' . \Illuminate\Support\Str::limit($this->repost->comment, 100));
        }

        if ($this->repost && $this->repost->is_quote_repost) {
            $message->action('View Quote Repost', $this->repost->quote_post_id 
                ? route('posts.show', $this->repost->quote_post_id)
                : route('posts.show', $this->post));
        } else {
            $message->action('View Post', route('posts.show', $this->post));
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $reposterName = $this->reposter->business_name ?? $this->reposter->name;
        $repostType = $this->getRepostType();

        $data = [
            'type' => 'post_reposted',
            'post_id' => $this->post->id,
            'reposter_id' => $this->reposter->id,
            'title' => 'Post Reposted',
            'message' => "{$reposterName} {$repostType} your post '{$this->post->title}'.",
            'repost_type' => $this->repost ? ($this->repost->is_quote_repost ? 'quote' : ($this->repost->hasComment() ? 'with_comment' : 'regular')) : 'regular',
        ];

        if ($this->repost && $this->repost->hasComment()) {
            $data['comment_preview'] = \Illuminate\Support\Str::limit($this->repost->comment, 100);
        }

        if ($this->repost && $this->repost->is_quote_repost) {
            $data['quote_post_id'] = $this->repost->quote_post_id;
            $data['quote_content_preview'] = $this->repost->quote_content 
                ? \Illuminate\Support\Str::limit($this->repost->quote_content, 100)
                : null;
        }

        return $data;
    }

    /**
     * Get repost type description.
     */
    private function getRepostType(): string
    {
        if ($this->repost) {
            if ($this->repost->is_quote_repost) {
                return 'quote reposted';
            }
            if ($this->repost->hasComment()) {
                return 'reposted with a comment';
            }
        }
        return 'reposted';
    }
}

