<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $role = $this->order->buyer_id === $notifiable->id ? 'buyer' : 'seller';
        
        return (new MailMessage)
            ->subject('Order Cancelled')
            ->line("Order #{$this->order->order_number} has been cancelled.")
            ->line("Reason: {$this->reason}")
            ->when($role === 'buyer', function ($mail) {
                return $mail->line("Amount: Rp " . number_format($this->order->total, 0, ',', '.'));
            })
            ->action('View Order', route('marketplace.orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        $role = $this->order->buyer_id === $notifiable->id ? 'buyer' : 'seller';
        
        return [
            'type' => 'order_cancelled',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'reason' => $this->reason,
            'cancelled_at' => now(),
            'role' => $role,
            'title' => 'Order Cancelled',
            'message' => "Order #{$this->order->order_number} has been cancelled. Reason: {$this->reason}",
        ];
    }
}

