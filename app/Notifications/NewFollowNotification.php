<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFollowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $follower
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Follower')
            ->line("{$this->follower->name} started following you.")
            ->action('View Profile', route('profile.show', $this->follower));
    }

    public function toArray(object $notifiable): array
    {
        // Get mutual connections count
        $mutualConnections = app(\App\Services\FollowService::class)
            ->getMutualConnections($notifiable, $this->follower);
        $mutualCount = $mutualConnections->count();

        // Get follower categories
        $categories = $this->follower->categories()
            ->active()
            ->limit(3)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
            ]);

        return [
            'type' => 'new_follow',
            'follower_id' => $this->follower->id,
            'follower_name' => $this->follower->name,
            'follower_business_name' => $this->follower->business_name,
            'follower_avatar_url' => $this->follower->avatar_url,
            'categories' => $categories,
            'mutual_connections_count' => $mutualCount,
            'title' => 'New Follower',
            'message' => "{$this->follower->name} started following you." . 
                        ($mutualCount > 0 ? " You have {$mutualCount} mutual " . ($mutualCount === 1 ? 'connection' : 'connections') . "." : ''),
        ];
    }
}

