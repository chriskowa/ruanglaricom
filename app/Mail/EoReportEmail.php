<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EoReportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $subjectLine;

    public function __construct($data, $subject)
    {
        $this->data = $data;
        $this->subjectLine = $subject;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.eo.report',
            with: $this->data,
        );
    }
}
