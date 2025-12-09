<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ServiceOrder $order,
        public string $rejectionReason = ''
    ) {
        $this->queue = 'notifications';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Work Rejected: {$this->order->title}")
            ->markdown('emails.notifications.work-rejected', [
                'order' => $this->order,
                'vendor' => $notifiable,
                'buyer' => $this->order->user,
                'rejectionReason' => $this->rejectionReason,
                'actionUrl' => route('vendor.orders.show', $this->order),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_title' => $this->order->title,
            'buyer_name' => $this->order->user->name,
            'message' => "{$this->order->user->name} has rejected your work for '{$this->order->title}'. Please resubmit.",
            'rejection_reason' => $this->rejectionReason,
            'action_url' => route('vendor.orders.show', $this->order),
            'type' => 'work_rejected',
        ];
    }
}
