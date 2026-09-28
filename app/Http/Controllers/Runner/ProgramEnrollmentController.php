<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\Notification;
use App\Helpers\WhatsApp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class ProgramEnrollmentController extends Controller
{
    /**
     * Enroll in a free program
     */
    public function enrollFree(Program $program)
    {
        $user = auth()->user();

        // Check if user currently has an active program
        $hasActiveProgram = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->exists();

        // Check if already enrolled in this program
        $existingEnrollment = $program->enrollments()
            ->where('runner_id', $user->id)
            ->whereIn('status', ['purchased', 'active'])
            ->first();

        if ($existingEnrollment) {
            if ($existingEnrollment->status === 'purchased') {
                if (! $hasActiveProgram) {
                    // No active program -> directly activate this program!
                    $startDate = Carbon::today();
                    $durationWeeks = $program->duration_weeks ?? 12;
                    $endDate = $startDate->copy()->addWeeks($durationWeeks);

                    $existingEnrollment->update([
                        'status' => 'active',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]);

                    if (request()->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'has_active_program' => false,
                            'redirect_url' => route('runner.dashboard', ['tab' => 'calendar']),
                            'message' => 'Program ' . $program->title . ' berhasil diaktifkan di kalender latihan Anda!',
                        ]);
                    }

                    return redirect()->route('runner.dashboard', ['tab' => 'calendar'])
                        ->with('success', 'Program ' . $program->title . ' berhasil diaktifkan di kalender latihan Anda!');
                }

                // Has active program -> show replace confirmation modal
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'has_active_program' => true,
                        'redirect_url' => route('runner.dashboard', ['activate_program' => $existingEnrollment->id]),
                        'message' => 'Program sudah ada di Program Bag Anda.',
                    ]);
                }

                return redirect()->route('runner.dashboard', [
                    'tab' => 'calendar',
                    'apply_enrollment' => $existingEnrollment->id,
                ])->with('info', 'Program ini sudah ada di Program Bag Anda. Silakan tentukan tanggal mulai untuk mengaktifkannya.');
            }

            // If already active in calendar
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'already_active' => true,
                    'redirect_url' => route('runner.dashboard', ['tab' => 'calendar']),
                    'message' => 'Program ini sudah aktif di kalender latihan Anda.',
                ]);
            }

            return redirect()->route('runner.dashboard', ['tab' => 'calendar'])
                ->with('info', 'Program ini sudah aktif di kalender latihan Anda.');
        }

        // Check if program is free
        if (! $program->isFree()) {
            return back()->with('error', 'Program ini berbayar. Silakan beli program terlebih dahulu.');
        }

        // Check if program is published and active
        if (! $program->is_published || ! $program->is_active) {
            return back()->with('error', 'Program tidak tersedia.');
        }

        $startDate = ! $hasActiveProgram ? Carbon::today() : null;
        $durationWeeks = $program->duration_weeks ?? 12;
        $endDate = (! $hasActiveProgram && $startDate) ? $startDate->copy()->addWeeks($durationWeeks) : null;
        $status = ! $hasActiveProgram ? 'active' : 'purchased';

        // Create enrollment
        $enrollment = ProgramEnrollment::create([
            'program_id' => $program->id,
            'runner_id' => $user->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'payment_status' => 'paid',
        ]);

        // Increment enrolled count
        $program->increment('enrolled_count');

        // Notify Coach (in-app notification)
        try {
            $coach = $program->coach;
            if ($coach) {
                Notification::create([
                    'user_id' => $coach->id,
                    'type' => 'program_order',
                    'title' => 'Pesanan Program (Free)',
                    'message' => 'Runner '.$user->name.' mendaftar program gratis: '.$program->title,
                    'reference_type' => ProgramEnrollment::class,
                    'reference_id' => $enrollment->id,
                    'is_read' => false,
                ]);
            }
        } catch (\Throwable $e) {
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'has_active_program' => $hasActiveProgram,
                'redirect_url' => $hasActiveProgram 
                    ? route('runner.dashboard', ['tab' => 'calendar', 'apply_enrollment' => $enrollment->id])
                    : route('runner.dashboard', ['tab' => 'calendar']),
                'message' => $hasActiveProgram 
                    ? 'Program berhasil didaftarkan ke Program Bag!' 
                    : 'Program berhasil diaktifkan di kalender latihan Anda!',
            ]);
        }

        if ($hasActiveProgram) {
            return redirect()->route('runner.dashboard', [
                'tab' => 'calendar',
                'apply_enrollment' => $enrollment->id,
            ])->with('info', 'Program berhasil didaftarkan! Karena Anda sedang menjalankan program aktif, silakan konfirmasi untuk mengganti program aktif.');
        }

        return redirect()->route('runner.dashboard', ['tab' => 'calendar'])
            ->with('success', 'Program ' . $program->title . ' berhasil diaktifkan di kalender latihan Anda!');
    }
}
