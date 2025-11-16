<?php

namespace App\Mail;

use App\Models\ProjectRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ProjectRequest $projectRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Client Request: {$this->projectRequest->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-received',
        );
    }
}
