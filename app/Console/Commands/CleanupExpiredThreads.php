<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RunThread;
use Carbon\Carbon;

class CleanupExpiredThreads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run-connect:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up run connect threads that are older than 2 days past their start date, permanently deleting them and cascading related chats/participants.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Run Connect database cleanup and auto-complete...');

        // 1. Auto-complete threads that are past their start time + 3 hours
        $this->info('Checking for threads to auto-complete...');
        $now = Carbon::now();
        $threeHoursAgo = $now->copy()->subHours(3);

        $threadsToComplete = RunThread::where('status', '!=', 'completed')
            ->where(function($query) use ($threeHoursAgo) {
                // If thread start_date is older than today, it should be completed
                $query->whereDate('start_date', '<', $threeHoursAgo->toDateString())
                      ->orWhere(function($q) use ($threeHoursAgo) {
                          // If thread start_date is today, check if start_time + 3 hours is past
                          $q->whereDate('start_date', '=', $threeHoursAgo->toDateString())
                            ->whereTime('start_time', '<=', $threeHoursAgo->toTimeString());
                      });
            })
            ->get();

        $completedCount = 0;
        foreach ($threadsToComplete as $thread) {
            $thread->status = 'completed';
            $thread->save();
            $completedCount++;
        }
        $this->info("Successfully auto-completed {$completedCount} thread(s).");

        // 2. Cleanup threads older than 2 days
        $this->info('Checking for old threads to delete...');
        $expiredDate = Carbon::now()->subDays(2)->toDateString();

        $expiredThreads = RunThread::where('start_date', '<', $expiredDate)->withTrashed()->get();
        $count = $expiredThreads->count();

        if ($count === 0) {
            $this->info('No expired threads found for deletion.');
            return Command::SUCCESS;
        }

        foreach ($expiredThreads as $thread) {
            // Using forceDelete to permanently delete the threads, triggering database cascading deletes on relations
            $thread->forceDelete();
        }

        $this->info("Successfully deleted {$count} expired thread(s) and all their associated messages/participants.");
        return Command::SUCCESS;
    }
}
