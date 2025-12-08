<?php

namespace App\Mail;

use App\Models\AffiliateConversion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AffiliateConversionMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AffiliateConversion $conversion,
        public float $commission,
        public int $tier
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Conversion - {$this->conversion->affiliateLink->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-conversion',
            with: [
                'conversion' => $this->conversion,
                'commission' => $this->commission,
                'tier' => $this->tier,
                'linkName' => $this->conversion->affiliateLink->name,
                'converterName' => $this->conversion->converter?->username ?? 'Unknown',
            ]
        );
    }
}
