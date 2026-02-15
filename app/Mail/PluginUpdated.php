<?php

namespace App\Mail;

use App\Models\Plugin;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PluginUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $plugin;
    public $user;

    public function __construct(Plugin $plugin, User $user)
    {
        $this->plugin = $plugin;
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update Available: ' . $this->plugin->name . ' v' . $this->plugin->version,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.plugins.updated',
        );
    }
}
