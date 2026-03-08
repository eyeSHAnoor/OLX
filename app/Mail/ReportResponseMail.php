<?php

namespace App\Mail;

use App\Models\UserReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public UserReport $report;
    public string $responseMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(UserReport $report, string $responseMessage)
    {
        $this->report = $report;
        $this->responseMessage = $responseMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Response to Your Report - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.report-response',
        );
    }
}