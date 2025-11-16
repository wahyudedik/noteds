<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudioNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public string $messageText;
    public ?string $actionUrl;

    public function __construct(string $title, string $messageText, ?string $actionUrl = null)
    {
        $this->title = $title;
        $this->messageText = $messageText;
        $this->actionUrl = $actionUrl;
    }

    public function build()
    {
        return $this->subject($this->title)
            ->view('emails.studio.notification')
            ->with([
                'title' => $this->title,
                'messageText' => $this->messageText,
                'actionUrl' => $this->actionUrl,
            ]);
    }
}


