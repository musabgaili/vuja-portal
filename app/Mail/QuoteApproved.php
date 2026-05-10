<?php

namespace App\Mail;

use App\Models\IdeaRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public IdeaRequest $ideaRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Quote Accepted: {$this->ideaRequest->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-approved',
        );
    }
}
