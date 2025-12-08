<?php

namespace App\Mail;

use App\Models\NoteShareReferral;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ShareReferralCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $seller,
        public NoteShareReferral $shareReferral,
        public float $commissionAmount
    ) {
    }

    public function build()
    {
        $subject = "New Share - {$this->shareReferral->note->title}";
        
        return $this->subject($subject)
            ->view('emails.share-referral-created')
            ->with([
                'seller' => $this->seller,
                'shareReferral' => $this->shareReferral,
                'commissionAmount' => $this->commissionAmount,
                'noteTitle' => $this->shareReferral->note->title,
                'sharedByName' => $this->shareReferral->sharedBy->name ?? 'Someone',
                'dashboardUrl' => route('seller.share-analytics.dashboard'),
            ]);
    }
}
