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

        $paces = $bestVdot ? $daniels->calculateTrainingPaces($bestVdot) : null;
        $equivalent = $bestVdot ? $daniels->calculateEquivalentRaceTimes($bestVdot) : null;
        $trackTimes = $bestVdot ? $daniels->calculateTrackTimes($bestVdot) : null;

        return [
            'pb' => [
                '5k' => $pbs['5k'],
                '10k' => $pbs['10k'],
                'hm' => $pbs['21k'],
                'fm' => $pbs['42k'],
            ],
            'vdot' => $bestVdot,
            'vo2max' => $bestVdot, // Approximation aligned with VDOT scale
            'weekly_km_target' => $user->weekly_km_target,
            'paces' => $paces,
            'equivalent_race_times' => $equivalent,
            'track_times' => $trackTimes,
        ];
    }
}
