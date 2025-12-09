<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReleasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ServiceOrder $order,
        public float $amountReceived = 0
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
            ->subject("Payment Released: {$this->order->title}")
            ->markdown('emails.notifications.payment-released', [
                'order' => $this->order,
                'vendor' => $notifiable,
                'amountReceived' => $this->amountReceived,
                'actionUrl' => route('vendor.wallet.show'),
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
            'amount_received' => $this->amountReceived,
            'message' => "Payment of Rp " . number_format($this->amountReceived, 0, ',', '.') . " has been released to your wallet for '{$this->order->title}'",
            'action_url' => route('vendor.wallet.show'),
            'type' => 'payment_released',
        ];
    }
}
