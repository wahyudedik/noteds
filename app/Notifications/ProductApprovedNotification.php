<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductApprovedNotification extends Notification implements ShouldQueue
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
            ->subject('Product Approved')
            ->line("Your product '{$this->product->name}' has been approved and is now live.")
            ->action('View Product', route('marketplace.products.show', $this->product));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'product_approved',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'approved_at' => now(),
            'title' => 'Product Approved',
            'message' => "Your product '{$this->product->name}' has been approved and is now live.",
        ];
    }
}

