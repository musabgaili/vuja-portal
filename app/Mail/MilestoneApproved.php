<?php

namespace App\Mail;

use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MilestoneApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProjectMilestone $milestone,
        public User $approver
    ) {}

    public function envelope(): Envelope
    {
        $action = $this->milestone->client_approved ? 'Approved' : 'Rejected';
        return new Envelope(
            subject: "Milestone {$action}: {$this->milestone->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.milestone-approved',
        );
    }
}
