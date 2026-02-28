<?php

namespace App\Mail;

use App\Models\WebhookFileScan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ThreatDetectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public WebhookFileScan $scan;
    public string $detectedBy;

    public function __construct(WebhookFileScan $scan, string $detectedBy)
    {
        $this->scan = $scan;
        $this->detectedBy = $detectedBy;
    }

    public function envelope(): Envelope
    {
        $emoji = $this->scan->verdict === 'MALICIOUS' ? '🔴' : '🟡';
        
        return new Envelope(
            subject: "{$emoji} [{$this->scan->verdict}] Threat Detected: {$this->scan->original_filename}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.threat-detected',
        );
    }
}
