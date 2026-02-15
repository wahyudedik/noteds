<?php

namespace App\Mail;

use App\Models\PluginOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(PluginOrder $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Submitted #' . substr($this->order->id, 0, 8),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.submitted',
        );
    }
}
