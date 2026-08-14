<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule coach withdrawals processing (daily at 2 AM)
Schedule::command('withdrawals:process')
    ->dailyAt('02:00')
    ->timezone('Asia/Jakarta');

// Schedule Program Reminders (daily at 2 PM / 14:00 WIB)
Schedule::command('programs:schedule-reminders')
    ->dailyAt('16:00')
    ->timezone('Asia/Jakarta');

// Process queue jobs automatically (Shared Hosting friendly)
// Runs worker every minute, processes all jobs, then stops.
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();
