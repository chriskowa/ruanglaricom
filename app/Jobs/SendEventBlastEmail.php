<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Participant;
use App\Services\EventInstantEmailLimiter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendEventBlastEmail implements ShouldQueue
{
    use Queueable;

    protected $event;

    protected $subject;

    protected $content;

    protected $filters;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event, string $subject, string $content, array $filters = [])
    {
        $this->event = $event;
        $this->subject = $subject;
        $this->content = $content;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     */
    public function handle(EventInstantEmailLimiter $limiter): void
    {
        Log::info('Starting blast email for event: '.$this->event->name);

        $this->event = $this->event->fresh();
        if (! $this->event) {
            return;
        }

        $query = Participant::whereHas('transaction', function ($q) {
            $q->where('event_id', $this->event->id)
                ->where('payment_status', 'paid');
        });

        // Apply filters
        if (! empty($this->filters['category_id'])) {
            $query->where('race_category_id', $this->filters['category_id']);
        }

        // Chunk results to handle large datasets
        $query->chunk(100, function ($participants) use ($limiter) {
            $this->dispatchBlastChunk($participants, $limiter);
        });

        Log::info('Blast email completed for event: '.$this->event->name);
    }

    private function dispatchBlastChunk($participants, EventInstantEmailLimiter $limiter): void
    {
        $event = $this->event;
        if (! $event) {
            return;
        }

        $maxPerMinute = (int) ($event->blast_email_rate_limit_per_minute ?? 0);
        $maxPerMinute = $maxPerMinute > 0 ? $maxPerMinute : null;

        $batchSize = $maxPerMinute ? min($maxPerMinute, 100) : 100;
        $stepSeconds = $maxPerMinute ? intdiv(60, max(1, $maxPerMinute)) : 0;

        $buffer = [];
        foreach ($participants as $participant) {
            $email = (string) ($participant->email ?? '');
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $buffer[] = [
                'email' => $email,
                'name' => (string) ($participant->name ?? 'Peserta'),
            ];

            if (count($buffer) >= $batchSize) {
                $this->flushBlastBuffer($buffer, $limiter, $maxPerMinute, $stepSeconds);
                $buffer = [];
            }
        }

        if ($buffer) {
            $this->flushBlastBuffer($buffer, $limiter, $maxPerMinute, $stepSeconds);
        }
    }

    private function flushBlastBuffer(array $buffer, EventInstantEmailLimiter $limiter, ?int $maxPerMinute, int $stepSeconds): void
    {
        $event = $this->event;
        if (! $event) {
            return;
        }

        $delaySeconds = 0;
        if ($maxPerMinute) {
            $delaySeconds = $limiter->reserve((int) $event->id, count($buffer), $maxPerMinute);
        }

        foreach ($buffer as $idx => $row) {
            $job = SendSingleEventBlastEmail::dispatch(
                (int) $event->id,
                $row['email'],
                $row['name'],
                $this->subject,
                $this->content
            )->onQueue('emails-blast');

            $extra = $stepSeconds > 0 ? ($idx * $stepSeconds) : 0;
            $totalDelay = $delaySeconds + $extra;
            if ($totalDelay > 0) {
                $job->delay(now()->addSeconds($totalDelay));
            }
        }
    }
}
