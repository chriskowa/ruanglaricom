<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\Notification;
use App\Helpers\WhatsApp;
use Illuminate\Support\Facades\Mail;

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
        if (! $program->isFree()) {
            return back()->with('error', 'Program ini berbayar. Silakan beli program terlebih dahulu.');
        }

        // Check if program is published and active
        if (! $program->is_published || ! $program->is_active) {
            return back()->with('error', 'Program tidak tersedia.');
        }

        // Create enrollment
        $enrollment = ProgramEnrollment::create([
            'program_id' => $program->id,
            'runner_id' => $user->id,
            'start_date' => null, // Reset to null so it goes to Bag first
            'end_date' => null,
            'status' => 'purchased', // Reset to purchased (Bag)
            'payment_status' => 'paid',
        ]);

        // Increment enrolled count
        $program->increment('enrolled_count');

        // Notify Coach (order - free)
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
                if ($coach->email) {
                    Mail::raw('Ada pendaftaran program gratis dari '.$user->name.' untuk program "'.$program->title.'".', function ($m) use ($coach, $program) {
                        $m->to($coach->email)->subject('Pendaftaran Program (Free): '.$program->title);
                    });
                }
                $phone = $coach->phone ?? null;
                if ($phone) {
                    $normalized = preg_replace('/\D+/', '', $phone);
                    if (str_starts_with($normalized, '0')) {
                        $normalized = '62'.substr($normalized, 1);
                    } elseif (! str_starts_with($normalized, '62')) {
                        $normalized = '62'.$normalized;
                    }
                    WhatsApp::send($normalized, "*Pendaftaran Program (Free)*\nRunner: ".$user->name."\nProgram: ".$program->title);
                }
            }
        } catch (\Throwable $e) {
        }

        return redirect()->route('runner.calendar')
            ->with('success', 'Program berhasil didaftarkan! Program telah ditambahkan ke Program Bag Anda.');
    }
}
