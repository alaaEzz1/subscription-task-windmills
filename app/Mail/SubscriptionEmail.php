<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SubscriptionEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $bodyText;

    public function __construct(
        public string $subjectText,
        public string $body
    ) {
        $this->bodyText = $body;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectText,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription',
            with: [
                'bodyText' => $this->bodyText,
            ],
        );
    }
}
