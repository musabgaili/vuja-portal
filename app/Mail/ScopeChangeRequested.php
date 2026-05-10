<?php

namespace App\Mail;

use App\Models\ProjectScopeChange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScopeChangeRequested extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ProjectScopeChange $scopeChange) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Scope Change Requested: {$this->scopeChange->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.scope-change-requested',
        );
    }
}
