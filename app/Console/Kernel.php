<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('leaderboard:sync')->dailyAt('02:00');
        $schedule->command('marketplace:auctions:finalize')->everyMinute();
        $schedule->command('eo-report-emails:monitor')->everyMinute();
        // Run pending payment reminders every hour (it handles 24h throttling internally)
        $schedule->command('payments:remind-pending')->hourly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
