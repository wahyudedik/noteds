<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalStatusNotification extends Notification implements ShouldQueue
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
        $message = (new MailMessage)
            ->subject("Withdrawal Request {$this->withdrawal->status}");

        if ($this->withdrawal->status === 'approved') {
            $message->line("Your withdrawal request has been approved.")
                ->line("Amount: Rp " . number_format($this->withdrawal->amount, 0, ',', '.'));
        } elseif ($this->withdrawal->status === 'rejected') {
            $message->line("Your withdrawal request has been rejected.")
                ->line("Reason: " . ($this->withdrawal->admin_notes ?? 'No reason provided'));
        } elseif ($this->withdrawal->status === 'completed') {
            $message->line("Your withdrawal has been processed and completed.")
                ->line("Amount: Rp " . number_format($this->withdrawal->amount, 0, ',', '.'));
        }

        return $message->action('View Details', route('marketplace.withdrawals.show', $this->withdrawal));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'status' => $this->withdrawal->status,
            'amount' => $this->withdrawal->amount,
            'admin_notes' => $this->withdrawal->admin_notes,
        ];
    }
}
