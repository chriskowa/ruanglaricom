<?php

namespace App\Mail;

use App\Models\EoReportEmailDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EoReportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public EoReportEmailDelivery $delivery;

    public function __construct(EoReportEmailDelivery $delivery)
    {
        $this->delivery = $delivery;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: (string) $this->delivery->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.eo.report',
            with: [
                'delivery' => $this->delivery,
                'event' => $this->delivery->event,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

