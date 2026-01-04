<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\SupportTicketResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket,
        public SupportTicketResponse $response
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isAdminResponse = $this->response->is_admin_response;
        $responderName = $this->response->user->name;
        
        return (new MailMessage)
            ->subject($isAdminResponse ? 'Admin Response to Your Ticket' : 'New Response to Support Ticket')
            ->line("Ticket #{$this->ticket->ticket_number}: {$this->ticket->subject}")
            ->line($isAdminResponse 
                ? "Admin {$responderName} has responded to your ticket."
                : "{$responderName} has added a new response to the ticket.")
            ->line("Message: " . substr($this->response->message, 0, 200) . (strlen($this->response->message) > 200 ? '...' : ''))
            ->action('View Ticket', $isAdminResponse 
                ? route('support.tickets.show', $this->ticket->id)
                : route('admin.support-tickets.show', $this->ticket->id));
    }

    public function toArray(object $notifiable): array
    {
        $isAdminResponse = $this->response->is_admin_response;
        $responderName = $this->response->user->name;
        
        return [
            'type' => 'support_ticket_response',
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'response_id' => $this->response->id,
            'title' => $isAdminResponse ? 'Admin Response to Your Ticket' : 'New Response to Support Ticket',
            'message' => $isAdminResponse
                ? "Admin {$responderName} has responded to ticket #{$this->ticket->ticket_number}"
                : "{$responderName} has added a response to ticket #{$this->ticket->ticket_number}",
            'subject' => $this->ticket->subject,
            'is_admin_response' => $isAdminResponse,
            'responder_name' => $responderName,
            'response_preview' => substr($this->response->message, 0, 100) . (strlen($this->response->message) > 100 ? '...' : ''),
        ];
    }
}

