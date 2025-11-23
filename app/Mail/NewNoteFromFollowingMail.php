<?php

namespace App\Mail;

use App\Models\Note;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewNoteFromFollowingMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Note $note,
        public User $seller,
        public User $follower
    ) {
    }

    public function build()
    {
        $subject = "{$this->seller->name} published a new note: {$this->note->title}";
        
        return $this->subject($subject)
            ->view('emails.new-note-following')
            ->with([
                'note' => $this->note,
                'seller' => $this->seller,
                'follower' => $this->follower,
                'noteUrl' => route('marketplace.show', $this->note),
            ]);
    }
}

