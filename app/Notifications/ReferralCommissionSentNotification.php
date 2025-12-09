<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralCommissionSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private int $processedCount,
        private float $totalAmount,
        private int $failedCount = 0
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Referral Commission Batch Sent')
            ->greeting("Hello Admin,")
            ->line("Referral commission batch has been processed and sent to users.")
            ->line("📊 **Batch Summary:**")
            ->line("• Successfully Sent: {$this->processedCount} commissions")
            ->line("• Total Amount: " . currency($this->totalAmount));

        if ($this->failedCount > 0) {
            $message->line("⚠️ Failed: {$this->failedCount} commissions (check logs)");
        }

        return $message
            ->action('View Referral Transactions', url('/admin/referral-transactions'))
            ->line('Thank you for managing the platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Referral Commissions Sent',
            'message' => "Successfully sent {$this->processedCount} referral commissions totaling " . currency($this->totalAmount),
            'processed_count' => $this->processedCount,
            'failed_count' => $this->failedCount,
            'total_amount' => $this->totalAmount,
            'type' => 'referral_commission_sent',
        ];
    }
}
