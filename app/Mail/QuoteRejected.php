<?php

namespace App\Mail;

use App\Models\IdeaRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRejected extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public IdeaRequest $ideaRequest,
        public ?string $reason = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Quote Rejected: {$this->ideaRequest->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-rejected',
        );
    }
}
