<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSupportTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Support Ticket Created')
            ->line("A new support ticket has been created: {$this->ticket->ticket_number}")
            ->line("Subject: {$this->ticket->subject}")
            ->line("From: {$this->ticket->user->name} ({$this->ticket->user->email})")
            ->line("Priority: " . ucfirst($this->ticket->priority))
            ->action('View Ticket', route('admin.support-tickets.show', $this->ticket->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_support_ticket',
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => 'New Support Ticket Created',
            'message' => "New ticket #{$this->ticket->ticket_number} from {$this->ticket->user->name}",
            'subject' => $this->ticket->subject,
            'priority' => $this->ticket->priority,
            'category' => $this->ticket->category,
            'user_name' => $this->ticket->user->name,
            'user_email' => $this->ticket->user->email,
        ];
    }
}

