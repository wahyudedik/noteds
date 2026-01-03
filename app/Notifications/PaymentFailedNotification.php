<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $failureReason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Failed')
            ->line("Your payment for order #{$this->order->order_number} has failed.")
            ->line("Reason: {$this->failureReason}")
            ->line("Amount: Rp " . number_format($this->order->total, 0, ',', '.'))
            ->action('Retry Payment', route('marketplace.orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_failed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'failure_reason' => $this->failureReason,
            'amount' => $this->order->total,
            'failed_at' => now(),
            'title' => 'Payment Failed',
            'message' => "Your payment for order #{$this->order->order_number} has failed. Reason: {$this->failureReason}",
        ];
    }
}

