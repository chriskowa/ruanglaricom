<?php

namespace App\Services;

use App\Models\EventEmailMinuteCounter;
use Illuminate\Support\Facades\DB;

class EventInstantEmailLimiter
{
    public function reserve(int $eventId, int $emailsToSend, int $limitPerMinute = 5): int
    {
        $emailsToSend = max(0, (int) $emailsToSend);
        if ($emailsToSend === 0) {
            return 0;
        }

        $now = now();
        $minute = $now->copy()->startOfMinute();

        for ($i = 0; $i < 30; $i++) {
            $targetMinute = $minute->copy()->addMinutes($i);
            $reserved = $this->tryReserveMinute($eventId, $targetMinute, $emailsToSend, $limitPerMinute);
            if ($reserved) {
                return max(0, $targetMinute->timestamp - $now->timestamp);
            }
        }

        return 60;
    }

    private function tryReserveMinute(int $eventId, $minuteAt, int $emailsToSend, int $limitPerMinute): bool
    {
        return (bool) DB::transaction(function () use ($eventId, $minuteAt, $emailsToSend, $limitPerMinute) {
            $row = EventEmailMinuteCounter::query()
                ->where('event_id', $eventId)
                ->where('minute_at', $minuteAt)
                ->lockForUpdate()
                ->first();

            if (! $row) {
                try {
                    EventEmailMinuteCounter::firstOrCreate([
                        'event_id' => $eventId,
                        'minute_at' => $minuteAt,
                    ], [
                        'reserved_emails' => 0,
                    ]);
                } catch (\Throwable $e) {
                }

                $row = EventEmailMinuteCounter::query()
                    ->where('event_id', $eventId)
                    ->where('minute_at', $minuteAt)
                    ->lockForUpdate()
                    ->first();
            }

            $current = (int) ($row->reserved_emails ?? 0);
            if (($current + $emailsToSend) > $limitPerMinute) {
                return false;
            }

            $row->reserved_emails = $current + $emailsToSend;
            $row->save();

            return true;
        }, 3);
    }
}
