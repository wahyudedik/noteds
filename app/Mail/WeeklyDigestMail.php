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
        public $recommendedNotes = null,
        public array $notifications = [],
        public array $summary = []
    ) {
    }

    public function build()
    {
        $subject = "Your Weekly Digest - " . config('app.name');
        
        return $this->subject($subject)
            ->view('emails.weekly-digest')
            ->with([
                'user' => $this->user,
                'recommendedNotes' => $this->recommendedNotes ?? collect(),
                'notifications' => $this->notifications,
                'summary' => $this->summary,
            ]);
    }
}

