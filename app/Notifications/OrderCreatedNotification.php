<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $buyerName = $this->order->buyer?->name ?? 'Unknown';
        $sellerName = $this->order->product?->seller?->name ?? 'Unknown';
        
        return (new MailMessage)
            ->subject('New Order Created')
            ->line("A new order has been created.")
            ->line("Order Number: {$this->order->order_number}")
            ->line("Buyer: {$buyerName}")
            ->line("Seller: {$sellerName}")
            ->line("Amount: Rp " . number_format($this->order->total ?? 0, 0, ',', '.'));
    }

    public function toArray(object $notifiable): array
    {
        $buyerName = $this->order->buyer?->name ?? 'Unknown';
        $sellerName = $this->order->product?->seller?->name ?? 'Unknown';
        
        return [
            'type' => 'order_created',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'buyer_name' => $buyerName,
            'seller_name' => $sellerName,
            'amount' => $this->order->total ?? 0,
            'title' => 'New Order Created',
            'message' => "New order #{$this->order->order_number} from {$buyerName} to {$sellerName}.",
        ];
    }
}

