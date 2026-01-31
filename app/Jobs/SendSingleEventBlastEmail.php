<?php

namespace App\Jobs;

use App\Mail\EventBlastEmail;
use App\Models\Event;
use App\Models\EventEmailDeliveryLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendSingleEventBlastEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $eventId,
        public readonly string $toEmail,
        public readonly string $toName,
        public readonly string $subject,
        public readonly string $content
    ) {}

    public function handle(): void
    {
        $event = Event::find($this->eventId);
        if (! $event) {
            return;
        }

        try {
            Mail::to($this->toEmail)->send(new EventBlastEmail(
                $event,
                $this->subject,
                $this->content,
                $this->toName
            ));

            EventEmailDeliveryLog::create([
                'event_id' => $event->id,
                'transaction_id' => null,
                'channel' => 'email',
                'to' => $this->toEmail,
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            EventEmailDeliveryLog::create([
                'event_id' => $event->id,
                'transaction_id' => null,
                'channel' => 'email',
                'to' => $this->toEmail,
                'status' => 'failed',
                'error_code' => 'blast_send_failed',
                'error_message' => mb_substr((string) $e->getMessage(), 0, 2000),
            ]);

            throw $e;
        }
    }
}

