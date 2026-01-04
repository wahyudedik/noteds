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
            
            if (!empty($this->withdrawal->transfer_proof_approve)) {
                $message->line("Transfer proof has been uploaded. You can view it in your withdrawal details.");
            }
        } elseif ($this->withdrawal->status === 'rejected') {
            $message->line("Your withdrawal request has been rejected.")
                ->line("Reason: " . ($this->withdrawal->admin_notes ?? 'No reason provided'));
        } elseif ($this->withdrawal->status === 'completed') {
            $message->line("Your withdrawal has been processed and completed.")
                ->line("Amount: Rp " . number_format($this->withdrawal->amount, 0, ',', '.'));
            
            if (!empty($this->withdrawal->transfer_proof_complete)) {
                $message->line("Transfer proof has been uploaded. You can view it in your withdrawal details.");
            }
        }

        // Determine the correct route based on user type
        $route = 'marketplace.withdrawals.show';
        if ($this->withdrawal->user_type === 'clipper') {
            $route = 'clipper.withdrawals.show';
        } elseif ($this->withdrawal->user_type === 'creator') {
            $route = 'clipper.withdrawals.creator.show';
        }

        return $message->action('View Details', route($route, $this->withdrawal->id));
    }

    public function toArray(object $notifiable): array
    {
        $statusMessages = [
            'approved' => 'Your withdrawal request has been approved.',
            'rejected' => 'Your withdrawal request has been rejected.',
            'completed' => 'Your withdrawal has been processed and completed.',
        ];

        $message = $statusMessages[$this->withdrawal->status] ?? "Withdrawal status: {$this->withdrawal->status}";
        
        // Add transfer proof info to message
        if ($this->withdrawal->status === 'approved' && !empty($this->withdrawal->transfer_proof_approve)) {
            $message .= ' Transfer proof has been uploaded.';
        } elseif ($this->withdrawal->status === 'completed' && !empty($this->withdrawal->transfer_proof_complete)) {
            $message .= ' Transfer proof has been uploaded.';
        }

        // Determine the correct route based on user type
        $route = 'marketplace.withdrawals.show';
        if ($this->withdrawal->user_type === 'clipper') {
            $route = 'clipper.withdrawals.show';
        } elseif ($this->withdrawal->user_type === 'creator') {
            $route = 'clipper.withdrawals.creator.show';
        }

        return [
            'type' => 'withdrawal_status',
            'withdrawal_id' => $this->withdrawal->id,
            'title' => "Withdrawal Request {$this->withdrawal->status}",
            'message' => $message,
            'status' => $this->withdrawal->status,
            'amount' => $this->withdrawal->amount,
            'admin_notes' => $this->withdrawal->admin_notes,
            'route' => $route,
            'has_transfer_proof' => ($this->withdrawal->status === 'approved' && !empty($this->withdrawal->transfer_proof_approve)) || 
                                   ($this->withdrawal->status === 'completed' && !empty($this->withdrawal->transfer_proof_complete)),
        ];
    }
}
