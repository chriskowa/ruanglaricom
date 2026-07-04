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
        $this->info('Starting Run Connect database cleanup...');

        // Find threads where start_date is older than 2 days from now
        $expiredDate = Carbon::now()->subDays(2)->toDateString();

        $expiredThreads = RunThread::where('start_date', '<', $expiredDate)->withTrashed()->get();
        $count = $expiredThreads->count();

        if ($count === 0) {
            $this->info('No expired threads found.');
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
