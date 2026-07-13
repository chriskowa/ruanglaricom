<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Get active enrollments (My Programs)
        $activeEnrollments = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->whereHas('program', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['program.coach'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Map progress for each active program
        $activePrograms = $activeEnrollments->map(function ($enrollment) {
            $program = $enrollment->program;
            $sessions = $program->program_json['sessions'] ?? [];
            $totalSessions = is_array($sessions) ? count($sessions) : 0;
            
            $completedSessions = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                ->where('status', 'completed')
                ->count();
                
            $progressPercent = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100) : 0;
            
            $enrollment->total_sessions = $totalSessions;
            $enrollment->completed_sessions = $completedSessions;
            $enrollment->progress_percent = $progressPercent;
            
            return $enrollment;
        });

        // 2. Get Program Bag (Purchased, not yet started)
        $programBag = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'purchased')
            ->whereHas('program', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['program.coach'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Get available marketplace programs to explore
        $marketPrograms = Program::where('is_published', true)
            ->where('is_active', true)
            ->whereHas('coach', function ($q) {
                $q->where('role', 'coach');
            })
            ->where(function ($q) {
                $q->whereNull('is_self_generated')->orWhere('is_self_generated', false);
            })
            ->where(function ($q) {
                $q->whereNull('is_vdot_generated')->orWhere('is_vdot_generated', false);
            })
            ->with(['coach', 'city'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // 4. Get completed and inactive enrollments (History)
        $historyEnrollments = ProgramEnrollment::where('runner_id', $user->id)
            ->whereIn('status', ['inactive', 'completed'])
            ->with(['program.coach'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('runner.programs', [
            'activePrograms' => $activePrograms,
            'programBag' => $programBag,
            'marketPrograms' => $marketPrograms,
            'historyPrograms' => $historyEnrollments,
        ]);
    }
}
