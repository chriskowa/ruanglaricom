<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\PlatformWalletService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        $lock = Cache::lock('process_paid_tx_' . $this->transaction->id, 30);

        $execute = function () {
            // Reload transaction with relationships
            $this->transaction->refresh();
            $this->transaction->load(['participants.category', 'event.user', 'coupon']);

            if (($this->transaction->payment_status ?? null) !== 'paid') {
                return;
            }

            // 0. Sync community registration participants if applicable (idempotent)
            $this->syncCommunityParticipants();

            // 1. Generate BIB Numbers (sort by target_time)
            $this->generateBibNumbers();

            // 2. Deposit to EO wallet (idempotent)
            $this->depositToEOWallet();

            // 3. Recalculate coupon used_count
            if ($this->transaction->coupon_id) {
                \App\Models\Coupon::recalculateUsedCount($this->transaction->coupon_id);
            }

            $event = $this->transaction->event;
            $hasFullCoupon = $this->transaction->coupon_id && (
                ((float) ($this->transaction->discount_amount ?? 0) >= (float) ($this->transaction->total_original ?? 0))
                || ($this->transaction->coupon && $this->transaction->coupon->type === 'percent' && (float) $this->transaction->coupon->value >= 100)
            );
            $isPaidTransaction = ((float) ($this->transaction->final_amount ?? 0) > 0)
                || ($this->transaction->payment_gateway !== 'free')
                || $hasFullCoupon;
            $requiresApproval = ! empty($event->premium_amenities['requires_approval']) && ! $isPaidTransaction;

            if ($requiresApproval) {
                // 4. Set participant to pending review (isApproved = 0) for free/challenge requiring approval
                $this->transaction->participants()->update([
                    'status' => 'pending',
                    'isApproved' => 0,
                ]);

                // 5. Note: e-Ticket email is deferred until EO approves the participant
            } else {
                // 4. Update participant status to confirmed (for paid tickets or events without approval)
                $this->transaction->participants()->update([
                    'status' => 'confirmed',
                    'isApproved' => 1,
                ]);

                // 5. Send notifications (email/wa) immediately
                app(\App\Services\EventRegistrationEmailDispatcher::class)->dispatch($this->transaction);
            }

            Log::info('ProcessPaidEventTransaction completed', [
                'transaction_id' => $this->transaction->id,
            ]);
        };

        try {
            $lock->block(10, $execute);
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
     * Sync staging community participants into main event participants table on payment confirmation.
     */
    protected function syncCommunityParticipants(): void
    {
        $picData = is_array($this->transaction->pic_data) ? $this->transaction->pic_data : [];
        $communityRegId = $picData['community_registration_id'] ?? null;
        if (! $communityRegId) {
            return;
        }

        \App\Models\CommunityRegistration::query()
            ->whereKey($communityRegId)
            ->update([
                'status' => 'paid',
                'paid_at' => $this->transaction->paid_at ?? now(),
            ]);

        \App\Models\CommunityInvoice::query()
            ->where('transaction_id', $this->transaction->id)
            ->update([
                'status' => 'paid',
            ]);

        $registration = \App\Models\CommunityRegistration::with('participants')->find($communityRegId);
        if (! $registration || $registration->imported_at || $this->transaction->participants()->exists()) {
            return;
        }

        $communityParticipants = $registration->participants;
        if ($communityParticipants->isEmpty()) {
            return;
        }

        $categoryIds = $communityParticipants->pluck('race_category_id')->filter()->unique()->values()->all();
        $categories = \App\Models\RaceCategory::query()->whereIn('id', $categoryIds)->get()->keyBy('id');
        $pricingService = app(\App\Services\CommunityPricingService::class);

        DB::transaction(function () use ($registration, $communityParticipants, $categories, $pricingService) {
            foreach ($communityParticipants as $p) {
                $category = $p->race_category_id ? ($categories[$p->race_category_id] ?? null) : null;
                $priceType = 'regular';
                if ($category) {
                    $info = $pricingService->getCategoryPrice($category);
                    $priceType = (string) ($info['type'] ?? 'regular');
                }

                \App\Models\Participant::create([
                    'transaction_id' => $this->transaction->id,
                    'event_package_id' => null,
                    'race_category_id' => $p->race_category_id,
                    'name' => $p->name,
                    'gender' => $p->gender,
                    'phone' => $p->phone,
                    'email' => strtolower(trim((string) $p->email)),
                    'id_card' => $p->id_card,
                    'address' => $p->address,
                    'emergency_contact_name' => $p->emergency_contact_name,
                    'emergency_contact_number' => $p->emergency_contact_number,
                    'date_of_birth' => $p->date_of_birth,
                    'target_time' => null,
                    'jersey_size' => $p->jersey_size,
                    'addons' => [],
                    'status' => 'pending',
                    'is_picked_up' => false,
                    'price_type' => $priceType,
                ]);
            }

            $registration->update([
                'imported_at' => now(),
            ]);
        });

        $this->transaction->load('participants.category');
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
            if (! empty($participant->bib_number)) {
                continue;
            }

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
     * Determine whether this transaction represents real platform-collected funds
     * that are eligible to be deposited into the EO wallet.
     */
    protected function isEligibleForWalletDeposit(): bool
    {
        if (($this->transaction->payment_status ?? null) !== 'paid') {
            return false;
        }

        if ((float) ($this->transaction->final_amount ?? 0) <= 0) {
            return false;
        }

        $gateway = strtolower(trim((string) ($this->transaction->payment_gateway ?? '')));

        if ($gateway === 'midtrans') {
            $mode = strtolower(trim((string) ($this->transaction->midtrans_mode ?? 'production')));
            $orderId = trim((string) ($this->transaction->midtrans_order_id ?? ''));
            $midtransStatus = strtolower(trim((string) ($this->transaction->midtrans_transaction_status ?? '')));

            if ($mode === 'sandbox' || str_starts_with($orderId, 'EVENT-SBX-') || str_starts_with($orderId, 'TEST-')) {
                return false;
            }

            if ($orderId === '') {
                return false;
            }

            // Require verified Midtrans settlement/capture so manual status edits cannot mint wallet balance.
            return in_array($midtransStatus, ['settlement', 'capture'], true);
        }

        if ($gateway === 'moota') {
            return ! empty($this->transaction->moota_transaction_id)
                || ! empty($this->transaction->payment_gateway_reference);
        }

        // Direct/offline gateways (manual, manual_csv, manual_transfer, cod, free, etc.) never credit platform wallet.
        return false;
    }

    /**
     * Deposit organizer amount to EO wallet and platform fee to platform wallet idempotently.
     */
    protected function depositToEOWallet(): void
    {
        if (! $this->isEligibleForWalletDeposit()) {
            Log::info('ProcessPaidEventTransaction: Skipped EO wallet deposit (non-gateway, sandbox, free, or unverified settlement)', [
                'transaction_id' => $this->transaction->id,
                'payment_gateway' => $this->transaction->payment_gateway,
                'midtrans_mode' => $this->transaction->midtrans_mode,
                'midtrans_transaction_status' => $this->transaction->midtrans_transaction_status,
                'midtrans_order_id' => $this->transaction->midtrans_order_id,
                'moota_transaction_id' => $this->transaction->moota_transaction_id,
                'final_amount' => $this->transaction->final_amount,
            ]);

            return;
        }

        $organizer = $this->transaction->event->user;

        if (! $organizer) {
            Log::warning('ProcessPaidEventTransaction: Event has no organizer', [
                'transaction_id' => $this->transaction->id,
                'event_id' => $this->transaction->event_id,
            ]);

            return;
        }

        $finalAmount = (float) $this->transaction->final_amount;
        $adminFee = (float) $this->transaction->admin_fee;
        $organizerAmount = max(0, $finalAmount - $adminFee);

        DB::transaction(function () use ($organizer, $finalAmount, $adminFee, $organizerAmount) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $organizer->id],
                ['balance' => 0, 'locked_balance' => 0]
            );
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $alreadyDeposited = WalletTransaction::where('wallet_id', $wallet->id)
                ->where('reference_type', Transaction::class)
                ->where('reference_id', $this->transaction->id)
                ->where('type', 'deposit')
                ->exists();

            if (! $alreadyDeposited && $organizerAmount > 0) {
                $balanceBefore = (float) $wallet->balance;
                $balanceAfter = $balanceBefore + $organizerAmount;
                $wallet->balance = $balanceAfter;
                $wallet->save();

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
            }

            if ($adminFee > 0) {
                $platformWalletService = app(PlatformWalletService::class);
                $platformWallet = $platformWalletService->getPlatformWallet();
                $platformWallet = Wallet::where('id', $platformWallet->id)->lockForUpdate()->first();

                $feeAlreadyRecorded = WalletTransaction::where('wallet_id', $platformWallet->id)
                    ->where('reference_type', Transaction::class)
                    ->where('reference_id', $this->transaction->id)
                    ->where('type', 'fee')
                    ->exists();

                if (! $feeAlreadyRecorded) {
                    $platformBefore = (float) $platformWallet->balance;
                    $platformAfter = $platformBefore + $adminFee;
                    $platformWallet->balance = $platformAfter;
                    $platformWallet->save();

                    $platformWallet->transactions()->create([
                        'type' => 'fee',
                        'amount' => $adminFee,
                        'balance_before' => $platformBefore,
                        'balance_after' => $platformAfter,
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
        });
    }
}
