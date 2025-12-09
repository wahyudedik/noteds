<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use App\Models\WorkRevision;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ServiceOrder $order,
        public WorkRevision $revision
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Revision Rejected for Order #{$this->order->id}")
            ->view('emails.notifications.revision-rejected', [
                'notifiable' => $notifiable,
                'order' => $this->order,
                'revision' => $this->revision,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'revision_rejected',
            'order_id' => $this->order->id,
            'revision_id' => $this->revision->id,
            'message' => "Revision #{$this->revision->revision_number} rejected - " . $this->order->getRemainingRevisions() . " revisions remaining",
            'action_url' => route('studio.orders.work-detail', $this->order),
        ];
    }
}
