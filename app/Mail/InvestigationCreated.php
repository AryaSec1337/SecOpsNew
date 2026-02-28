<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\MitigationLog;

class InvestigationCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $mitigationLog;

    /**
     * Create a new message instance.
     */
    public function __construct(MitigationLog $mitigationLog)
    {
        $this->mitigationLog = $mitigationLog;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SecOps] New Investigation Log Created: ' . $this->mitigationLog->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Lookup reporter department from emails_users table
        $department = null;
        if ($this->mitigationLog->reporter_email) {
            $emailUser = \App\Models\EmailsUser::where('email_address', $this->mitigationLog->reporter_email)->first();
            $department = $emailUser?->department;
        }

        return new Content(
            markdown: 'emails.investigation.created',
            with: [
                'department' => $department,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
