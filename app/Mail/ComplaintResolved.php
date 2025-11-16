<?php

namespace App\Mail;

use App\Models\ProjectComplaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComplaintResolved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ProjectComplaint $complaint) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Complaint Resolved: {$this->complaint->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.complaint-resolved',
        );
    }
}
