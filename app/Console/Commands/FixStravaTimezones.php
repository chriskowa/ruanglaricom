<?php

namespace App\Console\Commands;

use App\Models\StravaActivity;
use Illuminate\Console\Command;

class FixStravaTimezones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'strava:fix-timezones {--dry-run : Only show what would be updated without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate and repair Strava activity start_date timestamps to match athlete local wall-clock time';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $this->info('Memulai perbaikan timezone aktivitas Strava' . ($isDryRun ? ' (DRY RUN)...' : '...'));

        $activities = StravaActivity::whereNotNull('raw')->get();
        $total = $activities->count();
        $updated = 0;
        $unchanged = 0;

        $this->output->progressStart($total);

        foreach ($activities as $act) {
            $raw = $act->raw;
            $correct = StravaActivity::parseStravaStartDate($raw);

            if (! $correct) {
                $unchanged++;
                $this->output->progressAdvance();
                continue;
            }

            $currentFormatted = $act->getRawOriginal('start_date');
            $correctFormatted = $correct->format('Y-m-d H:i:s');

            if ($currentFormatted !== $correctFormatted) {
                if (! $isDryRun) {
                    $act->update(['start_date' => $correct]);
                }
                $updated++;
            } else {
                $unchanged++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("\nSelesai! Total aktivitas diperiksa: {$total}");
        $this->info("Diperbarui ke jam lokal yang tepat: {$updated}");
        $this->info("Sudah tepat / tidak berubah: {$unchanged}");

        return Command::SUCCESS;
    }
}

