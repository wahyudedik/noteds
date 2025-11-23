<?php

namespace App\Mail;

use App\Models\Note;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AbandonedCartMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected ?string $trackingToken = null;

    public function __construct(
        public Note $note,
        public ?User $user = null
    ) {
    }

    public function setTrackingToken(string $token): void
    {
        $this->trackingToken = $token;
    }

    public function build()
    {
        $subject = "Don't miss out: {$this->note->title}";
        
        $noteUrl = route('marketplace.show', $this->note);
        
        // Add tracking to URL if token exists
        if ($this->trackingToken) {
            $noteUrl = route('email.track-click', [
                'token' => $this->trackingToken,
                'url' => $noteUrl,
            ]);
        }
        
        $trackingPixel = null;
        if ($this->trackingToken) {
            $trackingPixel = route('email.track-open', ['token' => $this->trackingToken]);
        }
        
        $unsubscribeUrl = null;
        if ($this->user) {
            $unsubscribe = \App\Models\EmailUnsubscribe::firstOrCreate(
                ['email' => $this->user->email],
                [
                    'user_id' => $this->user->id,
                    'token' => \App\Models\EmailUnsubscribe::generateToken(),
                    'unsubscribed_at' => now()->subYear(), // Not actually unsubscribed yet
                ]
            );
            $unsubscribeUrl = route('email.unsubscribe', ['token' => $unsubscribe->token]);
        }
        
        return $this->subject($subject)
            ->view('emails.abandoned-cart')
            ->with([
                'note' => $this->note,
                'user' => $this->user,
                'noteUrl' => $noteUrl,
                'trackingPixel' => $trackingPixel,
                'unsubscribeUrl' => $unsubscribeUrl,
            ]);
    }
}

