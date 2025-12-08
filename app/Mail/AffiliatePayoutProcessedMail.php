<?php

namespace App\Mail;

use App\Models\AffiliatePayout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AffiliatePayoutProcessedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AffiliatePayout $payout,
        public string $status,
        public ?string $notes = null
    ) {}

    public function envelope(): Envelope
    {
        $statusText = match ($this->status) {
            'completed' => 'Completed',
            'failed' => 'Failed',
            'rejected' => 'Rejected',
            default => 'Processed'
        };

        return new Envelope(
            subject: "Payout {$statusText} - {$this->payout->amount} {$this->payout->payout_method}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-payout-processed',
            with: [
                'payout' => $this->payout,
                'amount' => $this->payout->amount,
                'status' => $this->status,
                'method' => $this->payout->payout_method,
                'notes' => $this->notes,
            ]
        );
    }
}
