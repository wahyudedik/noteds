<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Product Rejected')
            ->line("Your product '{$this->product->name}' has been rejected.")
            ->line("Reason: {$this->reason}")
            ->action('View Product', route('marketplace.products.show', $this->product));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'product_rejected',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'reason' => $this->reason,
            'rejected_at' => now(),
            'title' => 'Product Rejected',
            'message' => "Your product '{$this->product->name}' has been rejected. Reason: {$this->reason}",
        ];
    }
}

