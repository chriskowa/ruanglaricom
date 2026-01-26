<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventBlastEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $subjectLine;
    public $content;
    public $participantName;

    /**
     * Create a new message instance.
     */
    public function __construct(Event $event, string $subject, string $content, string $participantName)
    {
        $this->event = $event;
        $this->subjectLine = $subject;
        $this->content = $content;
        $this->participantName = $participantName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . $this->event->name . '] ' . $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.events.blast',
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
