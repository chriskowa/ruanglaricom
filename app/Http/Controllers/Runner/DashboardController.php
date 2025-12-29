<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\CustomWorkout;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
            if (!$program || !$enrollment->start_date) continue;
            $sessions = $program->program_json['sessions'] ?? [];
            if (!is_array($sessions) || empty($sessions)) continue;

            $trackings = ProgramSessionTracking::where('enrollment_id', $enrollment->id)->get()->keyBy('session_day');

            foreach ($sessions as $session) {
                $day = (int)($session['day'] ?? 0);
                if ($day <= 0) continue;

                try {
                    $sessionDate = $enrollment->start_date->copy()->addDays($day - 1);
                } catch (\Exception $e) {
                    continue;
                }

                if (isset($trackings[$day]) && $trackings[$day]->rescheduled_date) {
                    $sessionDate = $trackings[$day]->rescheduled_date;
                }

                if ($sessionDate->lt($startOfWeek) || $sessionDate->gt($endOfWeek)) continue;

                $weeklyVolumeKm += (float)($session['distance'] ?? 0);
            }
        }

        // Include custom workouts in the same week
        $customWorkouts = CustomWorkout::where('runner_id', $user->id)
            ->whereBetween('workout_date', [$startOfWeek, $endOfWeek])
            ->get();
        foreach ($customWorkouts as $cw) {
            $weeklyVolumeKm += (float)($cw->distance ?? 0);
        }

        return view('runner.dashboard', [
            'activeEnrollments' => $activeEnrollments,
            'walletBalance' => $user->wallet ? $user->wallet->balance : 0,
            'totalEarnings' => $totalEarnings,
            'weeklyVolumeKm' => round($weeklyVolumeKm, 1),
        ]);
    }
}
