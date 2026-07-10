<?php

namespace App\Console\Commands;

use App\Jobs\SendProgramReminderJob;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ScheduleProgramReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'programs:schedule-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule WhatsApp reminders for runners who have a program session tomorrow.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Membersihkan data pendaftaran program yang telah berakhir...");

        // Auto-complete expired enrollments
        $activeEnrollments = ProgramEnrollment::with(['program'])
            ->where('status', 'active')
            ->get();
        
        $completedCount = 0;
        foreach ($activeEnrollments as $enrollment) {
            $program = $enrollment->program;
            if (!$program) continue;
            
            try {
                $startDate = Carbon::parse($enrollment->start_date)->startOfDay();
                $totalWeeks = $program->duration_weeks ?? 12;
                $totalDays = $totalWeeks * 7;
                $endDate = $startDate->copy()->addDays($totalDays - 1);
                
                if (Carbon::today()->gt($endDate)) {
                    $enrollment->update(['status' => 'completed']);
                    $completedCount++;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to auto-complete enrollment #{$enrollment->id}: " . $e->getMessage());
            }
        }
        if ($completedCount > 0) {
            $this->info("Berhasil menyelesaiakan {$completedCount} pendaftaran yang telah kedaluwarsa.");
        }

        $this->info("Mencari pelari yang memiliki jadwal program besok...");

        $tomorrow = Carbon::tomorrow();
        $enrollments = ProgramEnrollment::with(['program', 'runner'])
            ->where('status', 'active')
            ->whereHas('runner', function ($query) {
                $query->where('is_receive_wa', true);
            })
            ->get();

        $jobsToDispatch = [];

        foreach ($enrollments as $enrollment) {
            $runner = $enrollment->runner;
            if (!$runner || !$runner->phone) {
                continue;
            }

            $program = $enrollment->program;
            if (!$program) {
                continue;
            }

            try {
                $startDate = Carbon::parse($enrollment->start_date)->startOfDay();
            } catch (\Exception $e) {
                continue;
            }

            $totalWeeks = $program->duration_weeks ?? 12;
            $totalDays = $totalWeeks * 7;
            $endDate = $startDate->copy()->addDays($totalDays - 1);

            // Jika besok berada di luar periode program, skip
            if ($tomorrow->lt($startDate) || $tomorrow->gt($endDate)) {
                continue;
            }

            $sessions = $program->program_json['sessions'] ?? [];
            $trackings = ProgramSessionTracking::where('enrollment_id', $enrollment->id)->get();
            
            $tomorrowSession = null;

            // Cari apakah ada sesi yang dijadwalkan besok (termasuk yang di-reschedule ke besok)
            foreach ($sessions as $session) {
                $day = (int) ($session['day'] ?? 0);
                if ($day <= 0) continue;

                $sessionDate = $startDate->copy()->addDays($day - 1);
                
                $tracking = $trackings->firstWhere('session_day', $day);
                
                if ($tracking && $tracking->rescheduled_date) {
                    $sessionDate = Carbon::parse($tracking->rescheduled_date)->startOfDay();
                }

                if ($sessionDate->isSameDay($tomorrow)) {
                    // Ditemukan sesi untuk besok
                    if ($tracking && $tracking->status === 'completed') {
                        $tomorrowSession = 'completed';
                    } else {
                        $tomorrowSession = $session;
                    }
                    break;
                }
            }

            // Jika sesi sudah diselesaikan (walau jadwalnya besok), skip reminder
            if ($tomorrowSession === 'completed') {
                continue;
            }

            // Jika tidak ada sesi yang jatuh pada besok hari (dalam JSON tidak ada, atau di-reschedule ke hari lain),
            // maka kita anggap besok adalah Rest Day.
            if (!$tomorrowSession) {
                $daysDiff = $startDate->diffInDays($tomorrow, false);
                $normalDay = $daysDiff + 1;
                $tomorrowSession = ['type' => 'Rest', 'day' => $normalDay];
            }

            // Tambahkan ke daftar dispatch
            $jobsToDispatch[] = [
                'runner' => $runner,
                'session' => $tomorrowSession,
                'program' => $program
            ];
        }

        // Sort: Prioritaskan pengiriman pelari yang besok jadwalnya LARI/WORKOUT terlebih dahulu sebelum REST DAY
        usort($jobsToDispatch, function($a, $b) {
            $aType = strtolower($a['session']['type'] ?? 'rest');
            $bType = strtolower($b['session']['type'] ?? 'rest');
            $aIsRest = in_array($aType, ['rest', 'rest day', 'libur']);
            $bIsRest = in_array($bType, ['rest', 'rest day', 'libur']);
            
            if ($aIsRest === $bIsRest) {
                return 0;
            }
            return $aIsRest ? 1 : -1;
        });

        $totalJobs = count($jobsToDispatch);
        $this->info("Ditemukan {$totalJobs} pelari yang akan diingatkan.");

        if ($totalJobs === 0) {
            return 0;
        }

        // Dispatch jobs dengan rate limit aman: mengirim 5 pesan setiap 30 menit (batching).
        // Setiap runner di dalam batch yang sama akan diberi jeda (stagger) 1 menit untuk keamanan API.
        foreach ($jobsToDispatch as $index => $data) {
            $batchIndex = floor($index / 5);
            $staggerMinutes = $index % 5;
            $delayMinutes = ($batchIndex * 30) + $staggerMinutes;
            
            SendProgramReminderJob::dispatch($data['runner'], $data['session'], $data['program'])
                ->delay(now()->addMinutes($delayMinutes));
                
            $this->line("Dispatched reminder for {$data['runner']->name} with delay {$delayMinutes} minutes.");
        }

        $this->info("Semua reminder berhasil dimasukkan ke dalam antrean (Queue).");
        return 0;
    }
}
