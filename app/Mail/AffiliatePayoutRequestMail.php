<?php

namespace App\Mail;

use App\Models\AffiliatePayout;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AffiliatePayoutRequestMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AffiliatePayout $payout,
        public User $affiliate
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Affiliate Payout Request - {$this->affiliate->username}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-payout-request',
            with: [
                'payout' => $this->payout,
                'affiliate' => $this->affiliate,
                'amount' => $this->payout->amount,
                'method' => $this->payout->payout_method,
            ]
        );
    }
}
