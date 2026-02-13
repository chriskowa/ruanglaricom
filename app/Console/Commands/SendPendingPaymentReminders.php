<?php

namespace App\Console\Commands;

use App\Jobs\SendPendingPaymentReminder;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPendingPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:remind-pending {--limit=50 : Limit number of transactions to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for pending transactions older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $this->info("Starting pending payment reminders check (limit: {$limit})...");

        // Find pending transactions created > 24 hours ago
        // AND (reminder never sent OR reminder sent > 24 hours ago)
        $transactions = Transaction::where('payment_status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->where(function ($query) {
                $query->whereNull('pending_reminder_last_sent_at')
                      ->orWhere('pending_reminder_last_sent_at', '<', now()->subHours(24));
            })
            // Optimization: limit number of records to process per run
            ->limit($limit)
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No eligible pending transactions found.');
            return 0;
        }

        $this->info("Found {$transactions->count()} transactions to remind.");

        $count = 0;
        foreach ($transactions as $transaction) {
            try {
                // Dispatch job for each transaction
                // The job itself has throttling logic, but we filtered in query too
                SendPendingPaymentReminder::dispatch($transaction);
                $this->info("Dispatched reminder for Transaction #{$transaction->id}");
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to dispatch for Transaction #{$transaction->id}: " . $e->getMessage());
                Log::error("Failed to dispatch pending reminder for Transaction #{$transaction->id}: " . $e->getMessage());
            }
        }

        $this->info("Processed {$count} reminders.");
        return 0;
    }
}
