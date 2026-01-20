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

        // Calculate total earnings (income from commission, etc)
        $totalEarnings = 0;
        if ($user->wallet) {
            $totalEarnings = $user->wallet->transactions()
                ->where('type', 'commission')
                ->where('status', 'completed')
                ->sum('amount');
        }

        // Weekly volume calculation (planned for current week)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $weeklyVolumeKm = 0.0;

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

                $weeklyVolumeKm += (float) ($session['distance'] ?? 0);
            }
        }

        // Include custom workouts in the same week
        $customWorkouts = CustomWorkout::where('runner_id', $user->id)
            ->whereBetween('workout_date', [$startOfWeek, $endOfWeek])
            ->get();
        foreach ($customWorkouts as $cw) {
            $weeklyVolumeKm += (float) ($cw->distance ?? 0);
        }

        $stravaConnected = (bool) $user->strava_access_token;
        $lastStravaSyncAt = StravaActivity::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->first()?->updated_at;
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
                $act->pace_min_km = ($act->distance_m > 0) ? gmdate("i:s", (int)($act->moving_time_s / ($act->distance_m / 1000))) : '-';
                $act->formatted_duration = gmdate("H:i:s", $act->moving_time_s);
                
                $type = strtolower($act->type ?? '');
                $act->border_color = match(true) {
                    in_array($type, ['run', 'virtualrun', 'trailrun', 'treadmill']) => '#4CAF50',
                    in_array($type, ['ride', 'virtualride', 'ebikeride']) => '#2196F3',
                    $type === 'swim' => '#00BCD4',
                    in_array($type, ['walk', 'hike']) => '#FF9800',
                    default => '#9E9E9E'
                };
                
                $act->icon = match(true) {
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
                $upcoming[] = [
                    'date' => $date->format('Y-m-d'),
                    'date_label' => $date->format('D, d M'),
                    'type' => $session['type'] ?? 'Run',
                    'distance' => $session['distance'] ?? null,
                    'duration' => $session['duration'] ?? null,
                    'status' => $tracking ? ($tracking->status ?? 'pending') : 'pending',
                    'program_title' => $program->title,
                    'strava_link' => $tracking ? $tracking->strava_link : null,
                ];
            }
        }

        $customUpcoming = CustomWorkout::where('runner_id', $user->id)
            ->whereBetween('workout_date', [$today, $next7])
            ->orderBy('workout_date')
            ->get();
        foreach ($customUpcoming as $cw) {
            $upcoming[] = [
                'date' => $cw->workout_date->format('Y-m-d'),
                'date_label' => $cw->workout_date->format('D, d M'),
                'type' => $cw->type === 'race' ? ($cw->workout_structure['race_name'] ?? 'Race') : ($cw->type ?? 'Run'),
                'distance' => $cw->distance,
                'duration' => $cw->duration,
                'status' => $cw->status ?? 'pending',
                'program_title' => 'Custom',
                'strava_link' => null,
            ];
        }

        usort($upcoming, fn ($a, $b) => strcmp($a['date'], $b['date']));
        $upcoming = array_slice($upcoming, 0, 8);

        return view('runner.dashboard', [
            'activeEnrollments' => $activeEnrollments,
            'walletBalance' => $user->wallet ? $user->wallet->balance : 0,
            'totalEarnings' => $totalEarnings,
            'weeklyVolumeKm' => round($weeklyVolumeKm, 1),
            'stravaConnected' => $stravaConnected,
            'lastStravaSyncAt' => $lastStravaSyncAt,
            'lastStravaActivity' => $lastStravaActivity,
            'stravaWeekDistanceKm' => round($stravaWeekDistanceKm, 1),
            'upcomingWorkouts' => $upcoming,
            'recentStravaActivities' => $recentStravaActivities,
        ]);
    }
}
