<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ReportEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $emailSubject;
    public $reportContent;
    public $csvData;
    public $csvFilename;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 120;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $reportContent, $csvData, $csvFilename)
    {
        $this->emailSubject = $subject;
        $this->reportContent = $reportContent;
        $this->csvData = $csvData;
        $this->csvFilename = $csvFilename;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.report',
            with: [
                'reportContent' => $this->reportContent,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->csvData, $this->csvFilename)
                ->withMime('text/csv'),
        ];
    }
}
