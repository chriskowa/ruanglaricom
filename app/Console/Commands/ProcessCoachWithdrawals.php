<?php

namespace App\Console\Commands;

use App\Models\CoachWithdrawal;
use Illuminate\Console\Command;

class ProcessCoachWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdrawals:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending coach withdrawals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing coach withdrawals...');

        $withdrawals = CoachWithdrawal::where('status', 'pending')
            ->with('coach')
            ->get();

        if ($withdrawals->isEmpty()) {
            $this->info('No pending withdrawals found.');
            return 0;
        }

        $processed = 0;
        $failed = 0;

        foreach ($withdrawals as $withdrawal) {
            try {
                // Update status to processing
                $withdrawal->update(['status' => 'processing']);

                // TODO: Implement actual withdrawal logic
                // This could involve:
                // 1. Transfer to coach's bank account
                // 2. Update coach's wallet balance
                // 3. Create transaction record
                // 4. Send notification

                // For now, simulate processing delay
                sleep(1);

                // Mark as completed
                $withdrawal->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);

                $this->info("Processed withdrawal #{$withdrawal->id} - Rp " . number_format($withdrawal->amount, 0, ',', '.'));
                $processed++;

            } catch (\Exception $e) {
                $withdrawal->update([
                    'status' => 'rejected',
                    'rejection_reason' => $e->getMessage(),
                ]);

                $this->error("Failed to process withdrawal #{$withdrawal->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Completed. Processed: {$processed}, Failed: {$failed}");

        return 0;
    }
}
