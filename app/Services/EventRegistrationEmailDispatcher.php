<?php

namespace App\Services;

use App\Jobs\SendEventRegistrationNotification;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class EventRegistrationEmailDispatcher
{
    public function __construct(
        private readonly EventInstantEmailLimiter $limiter
    ) {}

    public function dispatch(Transaction $transaction): void
    {
        $transaction->loadMissing(['event', 'participants']);

        $event = $transaction->event;
        if (! $event) {
            return;
        }

        $emails = $this->collectUniqueEmails($transaction);
        $emailCount = count($emails);

        $delaySeconds = 0;
        $maxPerMinute = null;
        if ((bool) ($event->is_instant_notification ?? false)) {
            $maxPerMinute = 5;
        } elseif (! empty($event->ticket_email_rate_limit_per_minute)) {
            $maxPerMinute = (int) $event->ticket_email_rate_limit_per_minute;
        }

        if ($maxPerMinute) {
            $effectiveMax = max($maxPerMinute, $emailCount);
            $delaySeconds = $this->limiter->reserve((int) $event->id, $emailCount, $effectiveMax);
        }

        $job = SendEventRegistrationNotification::dispatch($transaction)
            ->onQueue('emails-tickets');

        if ($delaySeconds > 0) {
            $job->delay(now()->addSeconds($delaySeconds));
        }

        Log::info('Registration email job queued', [
            'transaction_id' => $transaction->id,
            'event_id' => $event->id,
            'email_count' => $emailCount,
            'queue' => 'emails-tickets',
            'delay_seconds' => $delaySeconds,
            'is_instant_notification' => (bool) ($event->is_instant_notification ?? false),
            'max_per_minute' => $maxPerMinute,
        ]);
    }

    private function collectUniqueEmails(Transaction $transaction): array
    {
        $picEmail = (string) ($transaction->pic_data['email'] ?? '');
        $emails = [];

        if (filter_var($picEmail, FILTER_VALIDATE_EMAIL)) {
            $emails[] = strtolower($picEmail);
        }

        foreach ($transaction->participants as $participant) {
            $email = (string) ($participant->email ?? '');
            if ($email === '') {
                continue;
            }
            if ($email === $picEmail) {
                continue;
            }
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $emails[] = strtolower($email);
        }

        return array_values(array_unique($emails));
    }
}
