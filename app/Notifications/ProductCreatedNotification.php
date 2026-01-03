<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Product Created')
            ->line("A new product has been created by {$this->product->seller->name}.")
            ->line("Product: {$this->product->name}")
            ->line("Price: Rp " . number_format($this->product->price, 0, ',', '.'))
            ->action('Review Product', route('admin.products.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'product_created',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'seller_name' => $this->product->seller->name,
            'price' => $this->product->price,
            'created_at' => $this->product->created_at,
            'title' => 'New Product Created',
            'message' => "A new product '{$this->product->name}' has been created by {$this->product->seller->name}.",
        ];
    }
}

