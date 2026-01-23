<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\PlatformWalletService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessPaidEventTransaction implements ShouldQueue
{
    use Queueable;

    protected $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Reload transaction with relationships
            $this->transaction->refresh();
            $this->transaction->load(['participants.category', 'event.user', 'coupon']);

            // 1. Generate BIB Numbers (sort by target_time)
            $this->generateBibNumbers();

            // 2. Deposit to EO wallet
            $this->depositToEOWallet();

            // 3. Increment coupon used_count
            if ($this->transaction->coupon) {
                $this->transaction->coupon->increment('used_count');
            }

            // 4. Update participant status to confirmed
            $this->transaction->participants()->update(['status' => 'confirmed']);

            // 5. Send notifications (email/wa) via queue
            \App\Jobs\SendEventRegistrationNotification::dispatch($this->transaction);

            Log::info('ProcessPaidEventTransaction completed', [
                'transaction_id' => $this->transaction->id,
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessPaidEventTransaction failed', [
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate BIB numbers for participants
     */
    protected function generateBibNumbers(): void
    {
        $participants = $this->transaction->participants()
            ->orderByRaw('CASE WHEN target_time IS NULL THEN 1 ELSE 0 END')
            ->orderBy('target_time', 'asc')
            ->get();

        $event = $this->transaction->event;
        $eventCode = strtoupper(Str::limit(Str::slug($event->name, ''), 10, ''));
        $year = $event->start_at ? $event->start_at->format('Y') : date('Y');
        $baseNumber = 1;

        foreach ($participants as $participant) {
            $bibNumber = sprintf('%s-%s-%04d', $eventCode, $year, $baseNumber);

            // Ensure unique BIB number
            while (\App\Models\Participant::where('bib_number', $bibNumber)->exists()) {
                $baseNumber++;
                $bibNumber = sprintf('%s-%s-%04d', $eventCode, $year, $baseNumber);
            }

            $participant->update(['bib_number' => $bibNumber]);
            $baseNumber++;
        }
    }

    /**
     * Deposit organizer amount to EO wallet and platform fee to platform wallet
     */
    protected function depositToEOWallet(): void
    {
        $organizer = $this->transaction->event->user;

        if (! $organizer) {
            Log::warning('ProcessPaidEventTransaction: Event has no organizer', [
                'transaction_id' => $this->transaction->id,
                'event_id' => $this->transaction->event_id,
            ]);

            return;
        }

        // Get or create wallet for organizer
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $organizer->id],
            ['balance' => 0, 'locked_balance' => 0]
        );

        $finalAmount = (float) $this->transaction->final_amount;
        $adminFee = (float) $this->transaction->admin_fee;
        $organizerAmount = max(0, $finalAmount - $adminFee);

        $balanceBefore = (float) $wallet->balance;
        $wallet->increment('balance', $organizerAmount);
        $balanceAfter = (float) $wallet->balance;

        // Create wallet transaction record
        $wallet->transactions()->create([
            'type' => 'deposit',
            'amount' => $organizerAmount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'status' => 'completed',
            'description' => 'Pembayaran event: '.$this->transaction->event->name,
            'reference_id' => $this->transaction->id,
            'reference_type' => Transaction::class,
            'metadata' => [
                'event_id' => $this->transaction->event_id,
                'event_name' => $this->transaction->event->name,
                'midtrans_order_id' => $this->transaction->midtrans_order_id,
                'final_amount' => $finalAmount,
                'admin_fee' => $adminFee,
            ],
            'processed_at' => now(),
        ]);

        if ($adminFee > 0) {
            $platformWalletService = app(PlatformWalletService::class);
            $platformWallet = $platformWalletService->getPlatformWallet();
            $platformWallet->refresh();

            $platformBefore = (float) $platformWallet->balance;
            $platformWallet->balance = $platformBefore + $adminFee;
            $platformWallet->save();

            $platformWallet->transactions()->create([
                'type' => 'platform_fee_income',
                'amount' => $adminFee,
                'balance_before' => $platformBefore,
                'balance_after' => (float) $platformWallet->balance,
                'status' => 'completed',
                'description' => 'Platform fee event: '.$this->transaction->event->name,
                'reference_id' => $this->transaction->id,
                'reference_type' => Transaction::class,
                'metadata' => [
                    'event_id' => $this->transaction->event_id,
                    'event_name' => $this->transaction->event->name,
                    'midtrans_order_id' => $this->transaction->midtrans_order_id,
                ],
                'processed_at' => now(),
            ]);
        }
    }
}
