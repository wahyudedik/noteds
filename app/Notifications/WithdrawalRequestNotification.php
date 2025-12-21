<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Withdrawal $withdrawal
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Withdrawal Request')
            ->line("A new withdrawal request has been submitted.")
            ->line("User: {$this->withdrawal->user->name}")
            ->line("Amount: Rp " . number_format($this->withdrawal->amount, 0, ',', '.'))
            ->line("Method: {$this->withdrawal->method}")
            ->action('Review Withdrawal', route('admin.withdrawals.show', $this->withdrawal));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'user_name' => $this->withdrawal->user->name,
            'amount' => $this->withdrawal->amount,
            'method' => $this->withdrawal->method,
        ];
    }
}
