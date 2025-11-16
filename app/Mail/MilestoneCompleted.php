<?php

namespace App\Mail;

use App\Models\ProjectMilestone;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MilestoneCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProjectMilestone $milestone
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Milestone Completed: {$this->milestone->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.milestone-completed',
        );
    }
}
