<?php

namespace App\Traits;

/**
 * Single source of truth for Training Phase calculations.
 * Shared by CalendarController (FullCalendar extendedProps.phase color mapping)
 * and ProgramAdaptationService (Adaptive Run Intelligence popup card).
 *
 * Phase mapping:
 *  0-25%   = foundation    → Build (sky)
 * 25-50%   = early_quality → Development (emerald)
 * 50-75%   = quality       → Peak (orange)
 * 75-100%  = final_prep    → Recovery/Taper (violet)
 */
trait TrainingPhaseAware
{
    /**
     * Full phase info (label, accent color, focus description + progress).
     * Used by Adaptive Run Intelligence UI popup cards.
     */
    public static function calculatePhase(int $day, int $totalWeeks): array
    {
        $totalDays = max(7, $totalWeeks * 7);
        $pct = min(100, max(0, ($day / $totalDays) * 100));

        $key = match (true) {
            $pct <= 25 => 'foundation',
            $pct <= 50 => 'early_quality',
            $pct <= 75 => 'quality',
            default => 'final_prep',
        };

        [$label, $accent, $focus] = match ($key) {
            'foundation'    => ['Build',            'sky',     'Tambah volume & aerobic base'],
            'early_quality' => ['Development',      'emerald', 'Tambah intensity threshold'],
            'quality'       => ['Peak',             'orange',  'Race specific training'],
            'final_prep'    => ['Recovery/Taper',   'violet',  'Adaptasi & taper jelang race'],
        };

        return [
            'key'           => $key,
            'label'         => $label,
            'accent'        => $accent,
            'focus'         => $focus,
            'progress_pct'  => (int) round($pct),
            'week_current'  => max(1, (int) ceil($day / 7)),
            'week_total'    => $totalWeeks,
        ];
    }

    /**
     * Legacy helper — returns only the ORIGINAL phase key string,
     * 100% backward compatible with CalendarController::events()
     * FullCalendar extendedProps.phase color mapping that existing code relies on.
     * DO NOT CHANGE RETURN STRINGS: foundation / early_quality / quality / final_prep
     * — these exact strings drive getEventColors() event tile coloring.
     */
    public static function legacyPhaseKey(int $day, int $totalWeeks): string
    {
        return self::calculatePhase($day, $totalWeeks)['key'];
    }
}
