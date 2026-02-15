<?php

namespace App\Mail;

use App\Models\PluginOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentVerified extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $adminWhatsapp;

    public function __construct(PluginOrder $order)
    {
        $this->order = $order;
        $this->adminWhatsapp = \App\Models\PlatformSetting::get('admin_whatsapp', '');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Verified - Download Your Plugin',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.verified',
        );
    }
}
