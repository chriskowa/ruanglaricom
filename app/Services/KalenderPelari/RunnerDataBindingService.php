<?php

namespace App\Services\KalenderPelari;

use App\Models\StravaActivity;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Support\Carbon;

class RunnerDataBindingService
{
    public function getMonthlyAggregate(User $user, int $year): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1, 0, 0, 0, config('app.timezone'))->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $months[$m] = [
                'month_number' => $m,
                'month_label' => $start->locale('id_ID')->monthName,
                'year' => $year,
                'range_start' => $start->toDateString(),
                'range_end' => $end->toDateString(),
                'distance_km' => 0.0,
                'moving_time_s' => 0,
                'elevation_gain_m' => 0.0,
                'activity_count' => 0,
                'strava_count' => 0,
                'manual_count' => 0,
            ];
        }

        $stravaRows = StravaActivity::where('user_id', $user->id)
            ->whereBetween('start_date', [
                Carbon::create($year, 1, 1, 0, 0, 0, config('app.timezone'))->startOfDay(),
                Carbon::create($year, 12, 31, 23, 59, 59, config('app.timezone'))->endOfDay(),
            ])
            ->selectRaw('EXTRACT(YEAR_MONTH FROM start_date) as ym, COUNT(*) as c, SUM(distance_m) as d, SUM(moving_time_s) as t, SUM(total_elevation_gain) as e')
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        $manualRows = UserActivity::where('user_id', $user->id)
            ->whereBetween('start_time', [
                Carbon::create($year, 1, 1, 0, 0, 0, config('app.timezone'))->startOfDay(),
                Carbon::create($year, 12, 31, 23, 59, 59, config('app.timezone'))->endOfDay(),
            ])
            ->selectRaw('EXTRACT(YEAR_MONTH FROM start_time) as ym, COUNT(*) as c, SUM(distance_km) as d, SUM(moving_time_s) as t, SUM(elevation_gain_m) as e')
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        foreach ($months as $m => $row) {
            $ym = (int) sprintf('%d%02d', $year, $m);

            $strava = $stravaRows->get($ym);
            if ($strava) {
                $months[$m]['distance_km'] += (float) bcdiv((string) ((int) ($strava->d ?? 0)), '1000', 3);
                $months[$m]['moving_time_s'] += (int) ($strava->t ?? 0);
                $months[$m]['elevation_gain_m'] += (float) ($strava->e ?? 0);
                $months[$m]['activity_count'] += (int) ($strava->c ?? 0);
                $months[$m]['strava_count'] += (int) ($strava->c ?? 0);
            }

            $manual = $manualRows->get($ym);
            if ($manual) {
                $months[$m]['distance_km'] += (float) ($manual->d ?? 0);
                $months[$m]['moving_time_s'] += (int) ($manual->t ?? 0);
                $months[$m]['elevation_gain_m'] += (float) ($manual->e ?? 0);
                $months[$m]['activity_count'] += (int) ($manual->c ?? 0);
                $months[$m]['manual_count'] += (int) ($manual->c ?? 0);
            }

            $months[$m]['distance_km'] = round((float) $months[$m]['distance_km'], 3);
            $months[$m]['elevation_gain_m'] = round((float) $months[$m]['elevation_gain_m'], 2);
            $months[$m]['avg_pace_sec_per_km'] = $this->avgPace(
                (int) $months[$m]['moving_time_s'],
                (float) $months[$m]['distance_km']
            );
        }

        $totals = [
            'distance_km' => round(array_sum(array_column($months, 'distance_km')), 3),
            'moving_time_s' => (int) array_sum(array_column($months, 'moving_time_s')),
            'elevation_gain_m' => round(array_sum(array_column($months, 'elevation_gain_m')), 2),
            'activity_count' => (int) array_sum(array_column($months, 'activity_count')),
            'strava_count' => (int) array_sum(array_column($months, 'strava_count')),
            'manual_count' => (int) array_sum(array_column($months, 'manual_count')),
        ];
        $totals['avg_pace_sec_per_km'] = $this->avgPace($totals['moving_time_s'], (float) $totals['distance_km']);

        $stravaConnected = ! empty($user->strava_access_token) && ! empty($user->strava_refresh_token);

        return [
            'year' => $year,
            'timezone' => config('app.timezone'),
            'strava_connected' => $stravaConnected,
            'strava_connect_url' => $stravaConnected ? null : route('runner.strava.connect'),
            'totals' => $totals,
            'months' => array_values($months),
            'rules' => [
                'strava_distance_field' => 'distance_m (meters)',
                'manual_distance_field' => 'distance_km (kilometers)',
                'aggregate_formula' => 'SUM(strava.distance_m)/1000 + SUM(manual.distance_km)',
            ],
            'generated_at' => Carbon::now(config('app.timezone'))->toIso8601String(),
        ];
    }

    public function storeManualActivity(User $user, array $validated): UserActivity
    {
        $start = Carbon::parse($validated['start_time'])->setTimezone(config('app.timezone'));
        $distanceKm = (float) $validated['distance_km'];
        $movingSec = (int) ($validated['moving_time_s'] ?? 0);

        if ($movingSec <= 0 && $distanceKm > 0) {
            $avgPace = (int) ($validated['avg_pace_sec_per_km'] ?? 360);
            $movingSec = (int) round($distanceKm * $avgPace);
        }

        $avgPaceSec = $distanceKm > 0 && $movingSec > 0
            ? (int) round($movingSec / $distanceKm)
            : 0;
        $avgSpeedKmh = $movingSec > 0 && $distanceKm > 0
            ? round($distanceKm / ($movingSec / 3600), 2)
            : 0.0;

        return UserActivity::create([
            'user_id' => $user->id,
            'master_gpx_id' => null,
            'title' => mb_substr($validated['title'] ?? 'Lari Mandiri', 0, 160),
            'sport_type' => $validated['sport_type'] ?? 'Run',
            'start_time' => $start,
            'end_time' => $movingSec > 0 ? $start->copy()->addSeconds($movingSec) : $start->copy()->addHour(),
            'distance_km' => $distanceKm,
            'moving_time_s' => $movingSec,
            'elapsed_time_s' => (int) ($validated['elapsed_time_s'] ?? $movingSec),
            'avg_pace_sec' => $avgPaceSec,
            'max_pace_sec' => (int) ($validated['max_pace_sec'] ?? max(1, (int) round($avgPaceSec * 0.75))),
            'avg_speed_kmh' => $avgSpeedKmh,
            'elevation_gain_m' => (float) ($validated['elevation_gain_m'] ?? 0),
            'elevation_loss_m' => (float) ($validated['elevation_loss_m'] ?? 0),
            'calories' => (int) ($validated['calories'] ?? 0),
            'coordinates_json' => null,
            'splits_json' => null,
            'notes' => isset($validated['notes']) ? mb_substr($validated['notes'], 0, 1000) : null,
            'is_public' => (bool) ($validated['is_public'] ?? false),
        ]);
    }

    private function avgPace(int $seconds, float $km): ?int
    {
        if ($seconds <= 0 || $km <= 0) {
            return null;
        }

        return (int) round($seconds / $km);
    }
}
