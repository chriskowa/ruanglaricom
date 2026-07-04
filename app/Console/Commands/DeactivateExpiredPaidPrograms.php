<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProgramEnrollment;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class DeactivateExpiredPaidPrograms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'program:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate paid programs after 1 month of start date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneMonthAgo = Carbon::now()->subDays(30);

        // Find active enrollments of paid programs starting more than 30 days ago
        $enrollments = ProgramEnrollment::query()
            ->where('status', 'active')
            ->whereHas('program', function ($query) {
                $query->where('price', '>', 0);
            })
            ->where('start_date', '<=', $oneMonthAgo)
            ->get();

        $count = 0;
        foreach ($enrollments as $enrollment) {
            $runner = $enrollment->runner;
            $program = $enrollment->program;

            if (!$runner || !$program) {
                continue;
            }

            // Deactivate
            $enrollment->update([
                'status' => 'inactive',
            ]);

            // Notify Runner
            try {
                Notification::create([
                    'user_id' => $runner->id,
                    'type' => 'program_expired',
                    'title' => 'Program Non-aktif',
                    'message' => 'Program berbayar Anda "' . $program->title . '" telah dinon-aktifkan karena sudah melebihi 1 bulan. Silakan lakukan pembayaran ulang untuk melanjutkan kembali.',
                    'reference_type' => ProgramEnrollment::class,
                    'reference_id' => $enrollment->id,
                    'is_read' => false,
                ]);

                // Send email
                if ($runner->email) {
                    $emailBody = "Halo " . $runner->name . ",\n\n"
                        . "Program latihan berbayar Anda \"" . $program->title . "\" telah dinon-aktifkan karena sudah berjalan selama 1 bulan.\n"
                        . "Untuk melanjutkan program latihan ini, silakan melakukan pembelian ulang program tersebut di RuangLari.\n\n"
                        . "Terima kasih,\n"
                        . "Tim RuangLari";

                    Mail::raw($emailBody, function ($m) use ($runner, $program) {
                        $m->to($runner->email)->subject('Program Berbayar RuangLari Non-aktif: ' . $program->title);
                    });
                }
            } catch (\Throwable $e) {
                $this->error("Failed to send notification for enrollment ID: " . $enrollment->id . ". Error: " . $e->getMessage());
            }

            $count++;
        }

        $this->info("Successfully deactivated {$count} expired paid programs.");
    }
}
