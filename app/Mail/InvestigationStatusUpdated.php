<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\MitigationLog;

class InvestigationStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $mitigationLog;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(MitigationLog $mitigationLog, $oldStatus, $newStatus)
    {
        $this->mitigationLog = $mitigationLog;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $prefix = $this->newStatus === 'Resolved'
            ? '[SecOps] Investigation Resolved: '
            : '[SecOps] Investigation Status Updated: ';

        return new Envelope(
            subject: $prefix . $this->mitigationLog->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Build timeline from status changes
        $timeline = [];

        // Investigation created
        $timeline[] = [
            'action' => 'Investigation Created',
            'date' => $this->mitigationLog->created_at,
        ];

        // Load investigation details for timeline
        $details = $this->mitigationLog->details()->oldest('log_date')->get();
        foreach ($details as $detail) {
            $timeline[] = [
                'action' => $detail->action,
                'date' => $detail->log_date,
            ];
        }

        // Current status update
        $timeline[] = [
            'action' => 'Status Updated to "' . $this->newStatus . '"',
            'date' => now(),
        ];

        // Lookup reporter department
        $department = null;
        if ($this->mitigationLog->reporter_email) {
            $emailUser = \App\Models\EmailsUser::where('email_address', $this->mitigationLog->reporter_email)->first();
            $department = $emailUser?->department;
        }

        return new Content(
            markdown: 'emails.investigation.updated',
            with: [
                'timeline' => $timeline,
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
        // Attach PDF report when status is Resolved
        if ($this->newStatus === 'Resolved') {
            $this->mitigationLog->load(['user', 'details.user', 'files']);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mitigation.investigation_report', [
                'log' => $this->mitigationLog,
            ]);
            $pdf->setPaper('a4', 'portrait');

            $filename = 'Investigation_Report_' . $this->mitigationLog->id . '_' . now()->format('Ymd') . '.pdf';

            return [
                \Illuminate\Mail\Mailables\Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
