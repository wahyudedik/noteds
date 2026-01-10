<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->product->name} is Now Available!")
            ->line("Great news! The product you were waiting for is now available.")
            ->line("Product: {$this->product->name}")
            ->action('View Product', route('marketplace.products.show', $this->product->slug))
            ->line('Thank you for your patience!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'product_available',
            'product_id' => $this->product->id,
            'title' => 'Product Available',
            'message' => "{$this->product->name} is now available!",
            'product_name' => $this->product->name,
        ];
    }
}
