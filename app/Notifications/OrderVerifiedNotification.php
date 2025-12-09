<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ServiceOrder $order,
        public string $verificationNotes = ''
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
        $isVendor = $notifiable->id === $this->order->assigned_vendor_id;

        return (new MailMessage)
            ->subject("Order Verified: {$this->order->title}")
            ->markdown('emails.notifications.order-verified', [
                'order' => $this->order,
                'notifiable' => $notifiable,
                'vendor' => $this->order->assignedVendor,
                'buyer' => $this->order->user,
                'verificationNotes' => $this->verificationNotes,
                'isVendor' => $isVendor,
                'actionUrl' => $isVendor
                    ? route('vendor.orders.show', $this->order)
                    : route('studio.orders.detail', $this->order),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $isVendor = $notifiable->id === $this->order->assigned_vendor_id;

        return [
            'order_id' => $this->order->id,
            'order_title' => $this->order->title,
            'message' => "Order '{$this->order->title}' has been verified and payment released.",
            'verification_notes' => $this->verificationNotes,
            'is_vendor' => $isVendor,
            'action_url' => $isVendor
                ? route('vendor.orders.show', $this->order)
                : route('studio.orders.detail', $this->order),
            'type' => 'order_verified',
        ];
    }
}
