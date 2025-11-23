<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeeklyDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public $recommendedNotes
    ) {
    }

    public function build()
    {
        $subject = "Your Weekly Digest - Recommended Notes for You";
        
        return $this->subject($subject)
            ->view('emails.weekly-digest')
            ->with([
                'user' => $this->user,
                'recommendedNotes' => $this->recommendedNotes,
            ]);
    }
}

