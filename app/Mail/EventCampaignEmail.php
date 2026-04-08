<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventCampaignEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public Participant $participant,
        public string $subjectLine,
        public string $preset,
        public array $contentData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.events.campaign-preset',
            with: [
                'event' => $this->event,
                'participant' => $this->participant,
                'subjectLine' => $this->subjectLine,
                'preset' => $this->preset,
                'contentData' => $this->contentData,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
