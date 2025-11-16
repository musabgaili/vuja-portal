<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public User $client
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Project Completed: {$this->project->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.project-completed',
        );
    }
}
