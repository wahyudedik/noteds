<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ShareCommissionPaidMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $seller,
        public float $amount,
        public string $month,
        public int $shareCount
    ) {
    }

    public function build()
    {
        $monthFormatted = \Carbon\Carbon::createFromFormat('Y-m', $this->month)->format('F Y');
        
        $subject = "Share Analytics Commission Paid - {$monthFormatted}";
        
        return $this->subject($subject)
            ->view('emails.share-commission-paid')
            ->with([
                'seller' => $this->seller,
                'amount' => $this->amount,
                'month' => $monthFormatted,
                'shareCount' => $this->shareCount,
                'dashboardUrl' => route('seller.share-analytics.dashboard'),
            ]);
    }
}
