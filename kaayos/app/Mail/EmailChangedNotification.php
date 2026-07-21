<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $oldEmail;
    public string $newEmail;

    public function __construct(string $oldEmail, string $newEmail)
    {
        $this->oldEmail = $oldEmail;
        $this->newEmail = $newEmail;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your KaAyos email address has been changed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.email-changed',
        );
    }
}
