<?php

namespace App\Notifications;

use App\Models\ServiceOrderDispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisputeResolvedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public $order,
        public ServiceOrderDispute $dispute
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Dispute Resolved for Order #{$this->order->id}")
            ->view('emails.notifications.dispute-resolved', [
                'notifiable' => $notifiable,
                'order' => $this->order,
                'dispute' => $this->dispute,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'dispute_resolved',
            'dispute_id' => $this->dispute->id,
            'order_id' => $this->order->id,
            'resolution_type' => $this->dispute->resolution_type,
            'message' => "Dispute for order #{$this->order->id} has been resolved",
            'action_url' => route('disputes.show', $this->dispute),
        ];
    }
}
