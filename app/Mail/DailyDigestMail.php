<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public array $notifications,
        public array $summary = []
    ) {
    }

    public function build()
    {
        $subject = '📧 Your Daily Digest - ' . config('app.name');
        
        return $this->subject($subject)
            ->view('emails.daily-digest')
            ->with([
                'user' => $this->user,
                'notifications' => $this->notifications,
                'summary' => $this->summary,
            ]);
    }
}
