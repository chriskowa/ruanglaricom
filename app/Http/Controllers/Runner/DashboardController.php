<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\CustomWorkout;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\StravaActivity;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load('wallet');

        $activeEnrollments = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->with('program')
            ->get();

        $totalEarnings = 0;
        if ($user->wallet) {
            $totalEarnings = $user->wallet->transactions()
                ->where('type', 'commission')
                ->where('status', 'completed')
                ->sum('amount');
        }

        $startOfWeek = Carbon::now()->startOfWeek()->startOfDay();
        $endOfWeek = Carbon::now()->endOfWeek()->endOfDay();

        $weeklyPlannedKm = 0.0;
        $weeklyCompletedKm = 0.0;
        $weeklySessionsPlanned = 0;
        $weeklySessionsCompleted = 0;

        foreach ($activeEnrollments as $enrollment) {
            $program = $enrollment->program;
            if (! $program || ! $enrollment->start_date) {
                continue;
            }
            $sessions = $program->program_json['sessions'] ?? [];
            if (! is_array($sessions) || empty($sessions)) {
                continue;
            }

            $trackings = ProgramSessionTracking::where('enrollment_id', $enrollment->id)->get()->keyBy('session_day');

            foreach ($sessions as $session) {
                $day = (int) ($session['day'] ?? 0);
                if ($day <= 0) {
                    continue;
                }

                try {
                    $sessionDate = $enrollment->start_date->copy()->addDays($day - 1);
                } catch (\Exception $e) {
                    continue;
                }

                if (isset($trackings[$day]) && $trackings[$day]->rescheduled_date) {
                    $sessionDate = $trackings[$day]->rescheduled_date;
                }

                if ($sessionDate->lt($startOfWeek) || $sessionDate->gt($endOfWeek)) {
                    continue;
                }

                $dist = (float) ($session['distance'] ?? 0);
                $weeklyPlannedKm += $dist;
                $weeklySessionsPlanned++;

                $tracking = $trackings->get($day);
                if ($tracking && $tracking->status === 'completed') {
                    $weeklyCompletedKm += $dist;
                    $weeklySessionsCompleted++;
                }
            }
        }

        $customWorkouts = CustomWorkout::where('runner_id', $user->id)
            ->whereBetween('workout_date', [$startOfWeek, $endOfWeek])
            ->get();
        foreach ($customWorkouts as $cw) {
            $dist = (float) ($cw->distance ?? 0);
            $weeklyPlannedKm += $dist;
            $weeklySessionsPlanned++;
            if (($cw->status ?? 'pending') === 'completed') {
                $weeklyCompletedKm += $dist;
                $weeklySessionsCompleted++;
            }
        }

        $stravaConnected = (bool) $user->strava_access_token;
        $lastStravaSyncAt = StravaActivity::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->first();
        $lastStravaSyncAt = $lastStravaSyncAt ? $lastStravaSyncAt->updated_at : null;
        $lastStravaActivity = StravaActivity::where('user_id', $user->id)->orderByDesc('start_date')->first();
        $stravaWeekDistanceKm = (float) (StravaActivity::query()
            ->where('user_id', $user->id)
            ->whereBetween('start_date', [$startOfWeek, $endOfWeek])
            ->sum('distance_m') / 1000);

        $recentStravaActivities = StravaActivity::where('user_id', $user->id)
            ->orderByDesc('start_date')
            ->take(5)
            ->get()
            ->map(function ($act) {
                $act->distance_km = $act->distance_m ? round($act->distance_m / 1000, 2) : 0;
                $act->pace_min_km = ($act->distance_m > 0) ? gmdate('i:s', (int) ($act->moving_time_s / ($act->distance_m / 1000))) : '-';
                $act->formatted_duration = gmdate('H:i:s', $act->moving_time_s);

                $type = strtolower($act->type ?? '');
                $act->border_color = match (true) {
                    in_array($type, ['run', 'virtualrun', 'trailrun', 'treadmill']) => '#4CAF50',
                    in_array($type, ['ride', 'virtualride', 'ebikeride']) => '#2196F3',
                    $type === 'swim' => '#00BCD4',
                    in_array($type, ['walk', 'hike']) => '#FF9800',
                    default => '#9E9E9E'
                };

                $act->icon = match (true) {
                    in_array($type, ['run', 'virtualrun', 'trailrun', 'treadmill']) => '🏃',
                    in_array($type, ['ride', 'virtualride', 'ebikeride']) => '🚴',
                    $type === 'swim' => '🏊',
                    in_array($type, ['walk', 'hike']) => '🚶',
                    default => '🏋️'
                };

                return $act;
            });

        $today = Carbon::now()->startOfDay();
        $next7 = Carbon::now()->addDays(6)->endOfDay();
        $rangeCursor = $today->copy();

        $workoutsByDate = [];
        for ($i = 0; $i < 7; $i++) {
            $workoutsByDate[$rangeCursor->format('Y-m-d')] = [];
            $rangeCursor->addDay();
        }

        $seenWorkoutKeys = [];
        $upcoming = [];

        foreach ($activeEnrollments as $enrollment) {
            $program = $enrollment->program;
            if (! $program || ! $enrollment->start_date) {
                continue;
            }
            $sessions = $program->program_json['sessions'] ?? [];
            if (! is_array($sessions) || empty($sessions)) {
                continue;
            }
            $trackings = ProgramSessionTracking::where('enrollment_id', $enrollment->id)->get()->keyBy('session_day');
            foreach ($sessions as $session) {
                $day = (int) ($session['day'] ?? 0);
                if ($day <= 0) {
                    continue;
                }
                try {
                    $date = $enrollment->start_date->copy()->addDays($day - 1);
                } catch (\Exception $e) {
                    continue;
                }
                $tracking = $trackings->get($day);
                if ($tracking && $tracking->rescheduled_date) {
                    $date = Carbon::parse($tracking->rescheduled_date);
                }
                if ($date->lt($today) || $date->gt($next7)) {
                    continue;
                }
                $key = 'program:'.$enrollment->id.':'.$day;
                if (isset($seenWorkoutKeys[$key])) {
                    continue;
                }
                $seenWorkoutKeys[$key] = true;

                $item = [
                    'date' => $date->format('Y-m-d'),
                    'date_label' => $date->format('D, d M'),
                    'type' => $session['type'] ?? 'Run',
                    'distance' => $session['distance'] ?? null,
                    'duration' => $session['duration'] ?? null,
                    'status' => $tracking ? ($tracking->status ?? 'pending') : 'pending',
                    'program_title' => $program->title,
                    'strava_link' => $tracking ? $tracking->strava_link : null,
                    'source' => 'program',
                    'enrollment_id' => $enrollment->id,
                    'session_day' => $day,
                    'week_number' => $session['week'] ?? floor(($day - 1) / 7) + 1,
                    'target_pace' => $session['target_pace'] ?? $this->getPaceForSessionType($session['type'] ?? 'Run', $user->training_paces),
                    'description' => $session['description'] ?? null,
                ];

                $upcoming[] = $item;
                $dateKey = $item['date'];
                if (isset($workoutsByDate[$dateKey])) {
                    $workoutsByDate[$dateKey][] = $item;
                }
            }
        }

        $customUpcoming = CustomWorkout::where('runner_id', $user->id)
            ->whereBetween('workout_date', [$today, $next7])
            ->orderBy('workout_date')
            ->get();
        foreach ($customUpcoming as $cw) {
            $key = 'custom:'.$cw->id;
            if (isset($seenWorkoutKeys[$key])) {
                continue;
            }
            $seenWorkoutKeys[$key] = true;

            $item = [
                'date' => $cw->workout_date->format('Y-m-d'),
                'date_label' => $cw->workout_date->format('D, d M'),
                'type' => $cw->type === 'race' ? ($cw->workout_structure['race_name'] ?? 'Race') : ($cw->type ?? 'Run'),
                'distance' => $cw->distance,
                'duration' => $cw->duration,
                'status' => $cw->status ?? 'pending',
                'program_title' => 'Custom',
                'strava_link' => null,
                'source' => 'custom',
                'custom_workout_id' => $cw->id,
                'week_number' => null,
                'target_pace' => null,
                'description' => $cw->description,
            ];

            $upcoming[] = $item;
            $dateKey = $item['date'];
            if (isset($workoutsByDate[$dateKey])) {
                $workoutsByDate[$dateKey][] = $item;
            }
        }

        usort($upcoming, fn ($a, $b) => strcmp($a['date'], $b['date']));
        $upcoming = array_slice($upcoming, 0, 8);

        $nextWorkout = null;
        foreach ($upcoming as $w) {
            if (($w['status'] ?? 'pending') !== 'completed') {
                $nextWorkout = $w;
                break;
            }
        }
        $nextWorkout = $nextWorkout ?: ($upcoming[0] ?? null);

        $todayKey = $today->format('Y-m-d');
        $todayWorkouts = $workoutsByDate[$todayKey] ?? [];
        $todayWorkout = $todayWorkouts[0] ?? null;

        $weekStrip = [];
        $cursor = $today->copy();
        for ($i = 0; $i < 7; $i++) {
            $k = $cursor->format('Y-m-d');
            $items = $workoutsByDate[$k] ?? [];

            $completed = 0;
            $started = 0;
            $pending = 0;
            foreach ($items as $it) {
                $st = $it['status'] ?? 'pending';
                if ($st === 'completed') {
                    $completed++;
                } elseif ($st === 'started') {
                    $started++;
                } else {
                    $pending++;
                }
            }

            $status = 'rest';
            if (count($items) > 0) {
                if ($started > 0) {
                    $status = 'started';
                } elseif ($pending > 0) {
                    $status = 'pending';
                } else {
                    $status = 'completed';
                }
            }

            $weekStrip[] = [
                'date' => $k,
                'day_short' => $cursor->format('D'),
                'day_num' => $cursor->format('d'),
                'is_today' => $k === $todayKey,
                'items_count' => count($items),
                'status' => $status,
                'completed_count' => $completed,
                'primary' => $items[0] ?? null,
            ];

            $cursor->addDay();
        }

        $now = Carbon::now();
        $hour = (int) $now->format('H');
        $greeting = match (true) {
            $hour >= 4 && $hour < 11 => 'Selamat pagi',
            $hour >= 11 && $hour < 15 => 'Selamat siang',
            $hour >= 15 && $hour < 19 => 'Selamat sore',
            default => 'Selamat malam',
        };

        return view('runner.dashboard', [
            'activeEnrollments' => $activeEnrollments,
            'walletBalance' => $user->wallet ? $user->wallet->balance : 0,
            'totalEarnings' => $totalEarnings,
            'weeklyPlannedKm' => round($weeklyPlannedKm, 1),
            'weeklyCompletedKm' => round($weeklyCompletedKm, 1),
            'weeklySessionsPlanned' => $weeklySessionsPlanned,
            'weeklySessionsCompleted' => $weeklySessionsCompleted,
            'stravaConnected' => $stravaConnected,
            'lastStravaSyncAt' => $lastStravaSyncAt,
            'lastStravaActivity' => $lastStravaActivity,
            'stravaWeekDistanceKm' => round($stravaWeekDistanceKm, 1),
            'upcomingWorkouts' => $upcoming,
            'recentStravaActivities' => $recentStravaActivities,
            'nextWorkout' => $nextWorkout,
            'weekStrip' => $weekStrip,
            'todayWorkout' => $todayWorkout,
            'todayWorkoutCount' => count($todayWorkouts),
            'greeting' => $greeting,
        ]);
    }

    /**
     * Helper to get pace string based on session type
     */
    private function getPaceForSessionType($type, $paces)
    {
        if (! $paces) {
            return null;
        }

        $type = strtolower($type);
        $pace = null;
        $label = '';

        if (str_contains($type, 'easy') || str_contains($type, 'long') || str_contains($type, 'recovery') || str_contains($type, 'warmup') || str_contains($type, 'cool')) {
            $pace = $paces['E'] ?? null;
            $label = 'E';
        } elseif (str_contains($type, 'tempo') || str_contains($type, 'threshold')) {
            $pace = $paces['T'] ?? null;
            $label = 'T';
        } elseif (str_contains($type, 'interval')) {
            $pace = $paces['I'] ?? null;
            $label = 'I';
        } elseif (str_contains($type, 'repetition')) {
            $pace = $paces['R'] ?? null;
            $label = 'R';
        } elseif (str_contains($type, 'marathon')) {
            $pace = $paces['M'] ?? null;
            $label = 'M';
        }

        if ($pace) {
            $m = floor($pace);
            $s = round(($pace - $m) * 60);

            return sprintf('@ %d:%02d/km', $m, $s);
        }

        return null;
    }
}
