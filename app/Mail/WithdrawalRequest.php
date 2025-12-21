<?php

namespace App\Mail;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawalRequest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Withdrawal $withdrawal
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Withdrawal Request - Rp ' . number_format($this->withdrawal->amount, 0, ',', '.'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.withdrawal-request',
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
