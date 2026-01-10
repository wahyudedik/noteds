<?php

namespace App\Notifications;

use App\Models\BookmarkCollection;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CollectionSharedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BookmarkCollection $collection,
        public bool $isPublic
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->isPublic) {
            return (new MailMessage)
                ->subject('Bookmark Collection Made Public')
                ->line("Your bookmark collection '{$this->collection->name}' is now public.")
                ->action('View Collection', route('bookmarks.collections.public', $this->collection->public_slug))
                ->line('Anyone with the link can now view this collection.');
        }

        return (new MailMessage)
            ->subject('Bookmark Collection Made Private')
            ->line("Your bookmark collection '{$this->collection->name}' is now private.")
            ->line('Only users you invite can view this collection.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'collection_shared',
            'collection_id' => $this->collection->id,
            'collection_name' => $this->collection->name,
            'is_public' => $this->isPublic,
            'public_url' => $this->isPublic ? route('bookmarks.collections.public', $this->collection->public_slug) : null,
            'title' => $this->isPublic ? 'Collection Made Public' : 'Collection Made Private',
            'message' => $this->isPublic 
                ? "Your bookmark collection '{$this->collection->name}' is now public."
                : "Your bookmark collection '{$this->collection->name}' is now private.",
        ];
    }
}
