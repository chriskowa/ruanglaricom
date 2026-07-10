<?php

namespace App\Services\RunningAnalysis;

use Illuminate\Support\Collection;

class MetricCalculator
{
    /**
     * Calculate core metrics based on detected gait events.
     */
    public function calculate(Collection $events): array
    {
        $metrics = [
            'cadence' => 0,
            'contact_time_ms_left' => 0,
            'contact_time_ms_right' => 0,
            'flight_time_ms' => 0,
            'stride_time_ms' => 0,
        ];

        // We need at least a few events to calculate cadence
        if ($events->where('event_type', 'initial_contact')->count() < 3) {
            return $metrics;
        }

        $metrics['cadence'] = $this->calculateCadence($events);
        
        $contactTimes = $this->calculateContactTimes($events);
        $metrics['contact_time_ms_left'] = $contactTimes['left'];
        $metrics['contact_time_ms_right'] = $contactTimes['right'];

        $metrics['flight_time_ms'] = $this->calculateFlightTime($events);

        return $metrics;
    }

    /**
     * Steps per minute.
     */
    private function calculateCadence(Collection $events): int
    {
        $strikes = $events->where('event_type', 'initial_contact')->values();
        
        $firstStrike = $strikes->first();
        $lastStrike = $strikes->last();

        // Convert ms to seconds for cadence calculation
        $timeSpanSec = ($lastStrike['timestamp_ms'] - $firstStrike['timestamp_ms']) / 1000;
        $stepCount = $strikes->count() - 1; // intervals between strikes

        if ($timeSpanSec <= 0) return 0;

        return (int) round(($stepCount / $timeSpanSec) * 60);
    }

    /**
     * Ground contact time for each leg (Time from Heel Strike to Toe Off on the same leg).
     */
    private function calculateContactTimes(Collection $events): array
    {
        $contactLeft = [];
        $contactRight = [];

        $lastStrikeLeft = null;
        $lastStrikeRight = null;

        foreach ($events as $event) {
            if ($event['event_type'] === 'initial_contact') {
                if ($event['side'] === 'left') $lastStrikeLeft = $event['timestamp_ms'];
                if ($event['side'] === 'right') $lastStrikeRight = $event['timestamp_ms'];
            } elseif ($event['event_type'] === 'toe_off') {
                if ($event['side'] === 'left' && $lastStrikeLeft !== null) {
                    $contactLeft[] = ($event['timestamp_ms'] - $lastStrikeLeft);
                    $lastStrikeLeft = null;
                }
                if ($event['side'] === 'right' && $lastStrikeRight !== null) {
                    $contactRight[] = ($event['timestamp_ms'] - $lastStrikeRight);
                    $lastStrikeRight = null;
                }
            }
        }

        return [
            'left' => count($contactLeft) > 0 ? array_sum($contactLeft) / count($contactLeft) : 0,
            'right' => count($contactRight) > 0 ? array_sum($contactRight) / count($contactRight) : 0,
        ];
    }

    /**
     * Flight time (Time from Toe Off on one leg to Heel Strike on the other).
     */
    private function calculateFlightTime(Collection $events): float
    {
        $flightTimes = [];
        $lastToeOff = null;

        foreach ($events as $event) {
            if ($event['event_type'] === 'toe_off') {
                $lastToeOff = $event['timestamp_ms'];
            } elseif ($event['event_type'] === 'initial_contact' && $lastToeOff !== null) {
                $ft = ($event['timestamp_ms'] - $lastToeOff);
                // Flight time must be positive. If they overlap (walking), it's negative or 0.
                if ($ft > 0) {
                    $flightTimes[] = $ft;
                }
                $lastToeOff = null;
            }
        }

        return count($flightTimes) > 0 ? array_sum($flightTimes) / count($flightTimes) : 0;
    }
}
