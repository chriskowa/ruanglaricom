<?php

namespace App\Services;

use App\Models\User;

class RunningProfileService
{
    public function getProfile(User $user): array
    {
        $daniels = app(DanielsRunningService::class);

        $pbs = [
            '5k' => $user->pb_5k,
            '10k' => $user->pb_10k,
            '21k' => $user->pb_hm,
            '42k' => $user->pb_fm,
        ];

        $bestVdot = null;
        foreach ($pbs as $dist => $time) {
            if (! $time) {
                continue;
            }
            try {
                $vdot = $daniels->calculateVDOT($time, $dist);
                if ($bestVdot === null || $vdot > $bestVdot) {
                    $bestVdot = $vdot;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        $vdotPaces = $bestVdot ? $daniels->calculateTrainingPaces($bestVdot) : null;
        $customPacesRaw = $user->custom_training_paces;

        $paces = $vdotPaces;
        $isCustomPaces = false;

        if (is_array($customPacesRaw) && !empty($customPacesRaw)) {
            if (!$paces) {
                $paces = [];
            }
            foreach (['E', 'M', 'T', 'I', 'R', 'E_low', 'E_high'] as $type) {
                if (!empty($customPacesRaw[$type])) {
                    $val = $customPacesRaw[$type];
                    if (is_string($val) && str_contains($val, ':')) {
                        $parts = explode(':', $val);
                        $val = ((float)$parts[0]) + (((float)($parts[1] ?? 0)) / 60);
                    }
                    $paces[$type] = round((float)$val, 4);
                    $isCustomPaces = true;
                }
            }

            if (!empty($paces['E']) && (empty($paces['E_low']) || empty($paces['E_high']))) {
                $paces['E_high'] = round($paces['E'] * (0.70 / 0.72), 4);
                $paces['E_low'] = round($paces['E'] * (0.70 / 0.66), 4);
            }
        }

        $equivalent = $bestVdot ? $daniels->calculateEquivalentRaceTimes($bestVdot) : null;
        $trackTimes = $paces ? $daniels->calculateTrackTimesFromPaces($paces) : ($bestVdot ? $daniels->calculateTrackTimes($bestVdot) : null);

        return [
            'name' => $user->name,
            'pb' => [
                '5k' => $pbs['5k'],
                '10k' => $pbs['10k'],
                'hm' => $pbs['21k'],
                'fm' => $pbs['42k'],
                'balke' => $user->pb_balke,
            ],
            'vdot' => $bestVdot,
            'vo2max' => $bestVdot, // Approximation aligned with VDOT scale
            'weekly_km_target' => $user->weekly_km_target,
            'paces' => $paces,
            'is_custom_paces' => $isCustomPaces,
            'custom_paces_raw' => $customPacesRaw,
            'equivalent_race_times' => $equivalent,
            'track_times' => $trackTimes,
        ];
    }
}
