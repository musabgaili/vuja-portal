<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Immediate (non-queued) payment-link email so Resend delivers without a queue worker. */
class PaymentRequestMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $heading,
        public string $body,
        public string $actionUrl,
        public string $actionText,
        public string $lang = 'en',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.notification');
    }
}
