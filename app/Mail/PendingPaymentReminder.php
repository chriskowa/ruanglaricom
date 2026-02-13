<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendingPaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Menunggu Pembayaran: ' . $this->transaction->event->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pending-payment-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
