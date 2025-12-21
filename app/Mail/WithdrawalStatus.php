<?php

namespace App\Mail;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawalStatus extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Withdrawal $withdrawal
    ) {}

    public function envelope(): Envelope
    {
        $status = ucfirst($this->withdrawal->status);
        return new Envelope(
            subject: "Withdrawal Request {$status}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.withdrawal-status',
            with: [
                'withdrawal' => $this->withdrawal,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
