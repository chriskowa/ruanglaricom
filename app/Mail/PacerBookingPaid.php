<?php

namespace App\Mail;

use App\Models\PacerBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PacerBookingPaid extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PacerBooking $booking)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Pacer Paid - ' . $this->booking->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pacer.booking-paid',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

