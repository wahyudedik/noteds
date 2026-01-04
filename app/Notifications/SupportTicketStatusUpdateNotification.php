<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketStatusUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabels = [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];

        $newStatusLabel = $statusLabels[$this->newStatus] ?? ucfirst($this->newStatus);
        $isAdmin = $notifiable->isAdmin();
        
        return (new MailMessage)
            ->subject("Support Ticket Status Updated: {$newStatusLabel}")
            ->line("Ticket #{$this->ticket->ticket_number}: {$this->ticket->subject}")
            ->line("Status has been updated to: {$newStatusLabel}")
            ->action('View Ticket', $isAdmin 
                ? route('admin.support-tickets.show', $this->ticket->id)
                : route('support.tickets.show', $this->ticket->id));
    }

    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];

        $oldStatusLabel = $statusLabels[$this->oldStatus] ?? ucfirst($this->oldStatus);
        $newStatusLabel = $statusLabels[$this->newStatus] ?? ucfirst($this->newStatus);
        
        return [
            'type' => 'support_ticket_status_update',
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => 'Support Ticket Status Updated',
            'message' => "Ticket #{$this->ticket->ticket_number} status changed from {$oldStatusLabel} to {$newStatusLabel}",
            'subject' => $this->ticket->subject,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'old_status_label' => $oldStatusLabel,
            'new_status_label' => $newStatusLabel,
        ];
    }
}

