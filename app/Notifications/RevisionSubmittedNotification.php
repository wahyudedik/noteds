<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use App\Models\WorkRevision;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionSubmittedNotification extends Notification
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
            ->subject("Revision Submitted for Order #{$this->order->id}")
            ->view('emails.notifications.revision-submitted', [
                'notifiable' => $notifiable,
                'order' => $this->order,
                'revision' => $this->revision,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'revision_submitted',
            'order_id' => $this->order->id,
            'revision_id' => $this->revision->id,
            'message' => "Revision #{$this->revision->revision_number} submitted for order #{$this->order->id}",
            'action_url' => route('studio.orders.buyer-approval', $this->order),
        ];
    }
}
