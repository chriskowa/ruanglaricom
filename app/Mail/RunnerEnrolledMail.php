<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Program;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RunnerEnrolledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $runner;
    public $program;
    public $cleartextPassword;
    public $magicLink;

    /**
     * Create a new message instance.
     */
    public function __construct(User $runner, Program $program, ?string $cleartextPassword, string $magicLink)
    {
        $this->runner = $runner;
        $this->program = $program;
        $this->cleartextPassword = $cleartextPassword;
        $this->magicLink = $magicLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Program Latihan ' . $this->program->title . ' Berhasil',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.runner_enrolled',
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
