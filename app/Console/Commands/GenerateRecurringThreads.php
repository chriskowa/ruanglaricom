<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RunThread;
use Carbon\Carbon;

class GenerateRecurringThreads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run-connect:recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new threads for recurring run connect threads (1 week ahead)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting GenerateRecurringThreads...');
        
        // Find threads that are recurring, not cancelled, and starting today or tomorrow
        // We will generate the thread for next week (start_date + 7 days)
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        $recurringThreads = RunThread::where('is_recurring', true)
            ->whereIn('status', ['open', 'completed'])
            ->whereBetween('start_date', [$today, $tomorrow])
            ->get();

        $count = 0;
        foreach ($recurringThreads as $thread) {
            $nextWeekDate = Carbon::parse($thread->start_date)->addDays(7)->toDateString();

            // Check if we already generated a child thread for this date
            // The child would have parent_thread_id = thread->id or thread->parent_thread_id
            $parentId = $thread->parent_thread_id ?: $thread->id;

            $exists = RunThread::where('parent_thread_id', $parentId)
                ->whereDate('start_date', $nextWeekDate)
                ->exists();

            if (!$exists) {
                $newThread = $thread->replicate(['id', 'status', 'created_at', 'updated_at', 'recap_notes', 'recap_image_path']);
                $newThread->start_date = $nextWeekDate;
                $newThread->status = 'open';
                $newThread->parent_thread_id = $parentId;
                $newThread->save();
                
                // Auto-join the creator
                \App\Models\RunThreadParticipant::create([
                    'run_thread_id' => $newThread->id,
                    'user_id' => $newThread->creator_id,
                    'status' => 'joined',
                    'joined_at' => now(),
                ]);

                $count++;
                $this->info("Generated recurring thread ID {$newThread->id} for {$nextWeekDate}");
            }
        }

        $this->info("Successfully generated {$count} recurring thread(s).");
    }
}
