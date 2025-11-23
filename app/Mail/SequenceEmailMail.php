<?php

namespace App\Mail;

use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SequenceEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailSequence $sequence,
        public User $user
    ) {
    }

    public function build()
    {
        return $this->subject($this->sequence->subject)
            ->view('emails.sequence')
            ->with([
                'sequence' => $this->sequence,
                'user' => $this->user,
                'content' => $this->parseContent($this->sequence->content),
            ]);
    }

    protected function parseContent(string $content): string
    {
        // Replace placeholders
        $content = str_replace('{{user_name}}', $this->user->name, $content);
        $content = str_replace('{{user_email}}', $this->user->email, $content);
        
        return $content;
    }
}

