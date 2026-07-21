<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Program;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProgramReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $runner;
    public $sessionData;
    public $program;
    public $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(User $runner, array $sessionData, Program $program, ?string $customMessage = null)
    {
        $this->runner = $runner;
        $this->sessionData = $sessionData;
        $this->program = $program;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat Program Latihan: ' . ($this->sessionData['type'] ?? 'Sesi Latihan') . ' - ' . $this->program->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.programs.reminder',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
