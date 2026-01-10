<?php

namespace App\Notifications;

use App\Models\BookmarkCollection;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CollectionInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BookmarkCollection $collection,
        public User $inviter
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bookmark Collection Invitation')
            ->line("{$this->inviter->name} invited you to view their bookmark collection: {$this->collection->name}")
            ->action('View Collection', route('bookmarks.collections.public', $this->collection->public_slug))
            ->line('You can accept or reject this invitation from your shared collections page.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'collection_invitation',
            'collection_id' => $this->collection->id,
            'collection_name' => $this->collection->name,
            'inviter_id' => $this->inviter->id,
            'inviter_name' => $this->inviter->name,
            'title' => 'Collection Invitation',
            'message' => "{$this->inviter->name} invited you to view their bookmark collection: {$this->collection->name}",
        ];
    }
}
