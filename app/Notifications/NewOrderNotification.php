<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Order Received')
            ->line("You have received a new order: {$this->order->order_number}")
            ->line("Product: {$this->order->product->name}")
            ->line("Total: Rp " . number_format($this->order->total, 0, ',', '.'))
            ->action('View Order', route('marketplace.orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'product_name' => $this->order->product->name,
            'total' => $this->order->total,
        ];
    }
}
