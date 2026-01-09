<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventRegistrationSuccess extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $transaction;
    public $participants;
    public $notifiableName;

    /**
     * Create a new message instance.
     */
    public function __construct(Event $event, Transaction $transaction, $participants, $notifiableName)
    {
        $this->event = $event;
        $this->transaction = $transaction;
        $this->participants = $participants;
        $this->notifiableName = $notifiableName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tiket & Konfirmasi Registrasi: ' . $this->event->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.events.registration-success',
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
