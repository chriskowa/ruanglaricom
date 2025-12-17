<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProgramEnrollmentController extends Controller
{
    /**
     * Enroll in a free program
     */
    public function enrollFree(Program $program)
    {
        $user = auth()->user();

        // Check if already enrolled
        if ($program->enrollments()->where('runner_id', $user->id)->exists()) {
            return redirect()->route('runner.calendar')
                ->with('error', 'Anda sudah terdaftar di program ini.');
        }

        // Check if program is free
        if (!$program->isFree()) {
            return back()->with('error', 'Program ini berbayar. Silakan beli program terlebih dahulu.');
        }

        // Check if program is published and active
        if (!$program->is_published || !$program->is_active) {
            return back()->with('error', 'Program tidak tersedia.');
        }

        // Create enrollment
        $enrollment = ProgramEnrollment::create([
            'program_id' => $program->id,
            'runner_id' => $user->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addWeeks($program->duration_weeks ?? 12),
            'status' => 'active',
            'payment_status' => 'paid', // Free programs are considered paid
        ]);

        // Increment enrolled count
        $program->increment('enrolled_count');

        return redirect()->route('runner.calendar')
            ->with('success', 'Program berhasil didaftarkan! Silakan cek kalender Anda.');
    }
}
