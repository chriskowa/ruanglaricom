<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventParticipantRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;

    public Participant $participant;

    public string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Event $event, Participant $participant, string $reason = '')
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->reason = $reason ?: 'Pendaftaran belum memenuhi kriteria atau kuota telah terpenuhi.';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Status Pendaftaran Event "' . $this->event->name . '": Ditolak',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.events.participant-rejected',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
