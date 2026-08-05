<?php

namespace App\Services;

use App\Jobs\SendEventRegistrationNotification;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class EventRegistrationEmailDispatcher
{
    public function dispatch(Transaction $transaction): void
    {
        $transaction->loadMissing(['event', 'participants']);

        $event = $transaction->event;
        if (! $event) {
            return;
        }

        // Send registration ticket email immediately via Laravel Queue
        SendEventRegistrationNotification::dispatch($transaction)
            ->onQueue('emails-tickets');

        Log::info('Registration ticket email job queued for immediate processing', [
            'transaction_id' => $transaction->id,
            'event_id' => $event->id,
            'queue' => 'emails-tickets',
        ]);
    }
}
