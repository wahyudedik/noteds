<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectText;
    public string $html;

    public function __construct(string $subjectText, string $html)
    {
        $this->subjectText = $subjectText;
        $this->html = $html;
    }

    public function build()
    {
        return $this->from(config('newsletter.from_email'), config('newsletter.from_name'))
            ->subject($this->subjectText)
            ->html($this->html);
    }
}
