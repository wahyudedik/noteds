<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralCommissionReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private float $amount,
        private string $type = 'signup'
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
        $typeLabel = match ($this->type) {
            'signup' => 'Signup Bonus',
            'transaction' => 'Transaction Commission',
            default => 'Referral Bonus',
        };

        return (new MailMessage)
            ->subject('You Received a Referral Commission! 🎉')
            ->greeting("Hello {$notifiable->name},")
            ->line("Great news! Your referral bonus has been credited to your wallet.")
            ->line("📌 **Commission Details:**")
            ->line("• Type: {$typeLabel}")
            ->line("• Amount: " . currency($this->amount))
            ->line("Your wallet has been updated. You can now withdraw this amount anytime!")
            ->action('View Your Wallet', url('/wallet'))
            ->line('Thank you for being part of our community!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $typeLabel = match ($this->type) {
            'signup' => 'Signup Bonus',
            'transaction' => 'Transaction Commission',
            default => 'Referral Bonus',
        };

        return [
            'title' => 'Referral Commission Received!',
            'message' => "You received {$typeLabel} of " . currency($this->amount),
            'amount' => $this->amount,
            'type' => $this->type,
            'type_label' => $typeLabel,
            'notification_type' => 'referral_commission_received',
        ];
    }
}
