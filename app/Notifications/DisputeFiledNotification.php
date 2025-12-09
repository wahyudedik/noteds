<?php

namespace App\Notifications;

use App\Models\ServiceOrderDispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisputeFiledNotification extends Notification
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
            ->subject("Dispute Filed for Order #{$this->order->id}")
            ->view('emails.notifications.dispute-filed', [
                'notifiable' => $notifiable,
                'order' => $this->order,
                'dispute' => $this->dispute,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'dispute_filed',
            'dispute_id' => $this->dispute->id,
            'order_id' => $this->order->id,
            'message' => "A dispute has been filed for order #{$this->order->id}",
            'action_url' => route('disputes.show', $this->dispute),
        ];
    }
}
