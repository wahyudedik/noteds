<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ServiceOrder $order)
    {
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
            ->subject("Work Submitted: {$this->order->title}")
            ->markdown('emails.notifications.work-submitted', [
                'order' => $this->order,
                'buyer' => $notifiable,
                'vendor' => $this->order->assignedVendor,
                'actionUrl' => route('studio.orders.work-detail', $this->order),
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
            'vendor_name' => $this->order->assignedVendor->name,
            'message' => "{$this->order->assignedVendor->name} has submitted work for '{$this->order->title}'",
            'action_url' => route('studio.orders.work-detail', $this->order),
            'type' => 'work_submitted',
        ];
    }
}
