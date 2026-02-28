<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IpBlockedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $blockedIp;

    /**
     * Create a new message instance.
     */
    public function __construct(\App\Models\BlockedIp $blockedIp)
    {
        $this->blockedIp = $blockedIp;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SecOps] IP Blocked Alert: ' . $this->blockedIp->ip_address,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.blocked_ip.notification',
            with: [
                'ip' => $this->blockedIp->ip_address,
                'agent' => $this->blockedIp->agent->hostname ?? 'Unknown Agent',
                'reason' => $this->blockedIp->reason,
                'time' => $this->blockedIp->blocked_at,
            ]
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
