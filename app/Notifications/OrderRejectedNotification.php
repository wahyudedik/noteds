<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ServiceOrder $order,
        public float $refundAmount = 0,
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
            ->subject("Order Rejected: {$this->order->title}")
            ->markdown('emails.notifications.order-rejected', [
                'order' => $this->order,
                'buyer' => $notifiable,
                'refundAmount' => $this->refundAmount,
                'rejectionReason' => $this->rejectionReason,
                'actionUrl' => route('studio.orders.detail', $this->order),
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
            'refund_amount' => $this->refundAmount,
            'message' => "Admin rejected the work for '{$this->order->title}'. Refund of Rp " . number_format($this->refundAmount, 0, ',', '.') . " has been returned to your wallet.",
            'rejection_reason' => $this->rejectionReason,
            'action_url' => route('studio.orders.detail', $this->order),
            'type' => 'order_rejected',
        ];
    }
}
