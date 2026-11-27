<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileEoWallets extends Command
{
    protected $signature = 'wallet:reconcile-eo
        {--user= : Filter single EO by user ID}
        {--email= : Filter single EO by email}
        {--apply : Apply balance reconciliation to database (default is dry-run)}';

    protected $description = 'Audit and reconcile EO wallet balances strictly against verified paid gateway transactions (Midtrans Production settlement/capture & verified Moota) minus withdrawals.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $userIdOption = $this->option('user');
        $emailOption = $this->option('email');

        $query = User::query()->with('wallet');

        if ($userIdOption) {
            $query->where('id', (int) $userIdOption);
        } elseif ($emailOption) {
            $query->where('email', trim((string) $emailOption));
        } else {
            $eoUserIds = Event::query()
                ->whereNotNull('user_id')
                ->distinct()
                ->pluck('user_id');

            $query->whereIn('id', $eoUserIds)
                ->where('role', '!=', 'admin');
        }

        $users = $query->orderBy('id')->get();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada user EO yang cocok dengan filter.');

            return self::SUCCESS;
        }

        $this->info($apply
            ? 'MODE: APPLY (perubahan saldo akan disimpan ke database dengan jurnal koreksi)'
            : 'MODE: DRY-RUN (tidak ada perubahan database — tambahkan --apply untuk mengeksekusi)');

        $rows = [];
        $updatedCount = 0;

        foreach ($users as $user) {
            $wallet = $user->wallet;
            $eventIds = Event::query()->where('user_id', $user->id)->pluck('id');

            if ($eventIds->isEmpty() && (! $wallet || (float) $wallet->balance == 0.0)) {
                continue;
            }

            // 1. Verified Midtrans Production (settlement/capture) net for EO
            $midtransNet = (float) Transaction::query()
                ->whereIn('event_id', $eventIds)
                ->where('payment_status', 'paid')
                ->where('payment_gateway', 'midtrans')
                ->where('final_amount', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('midtrans_mode')
                        ->orWhere('midtrans_mode', '!=', 'sandbox');
                })
                ->whereNotNull('midtrans_order_id')
                ->where('midtrans_order_id', '!=', '')
                ->where('midtrans_order_id', 'not like', 'EVENT-SBX-%')
                ->where('midtrans_order_id', 'not like', 'TEST-%')
                ->whereIn('midtrans_transaction_status', ['settlement', 'capture'])
                ->selectRaw('COALESCE(SUM(GREATEST(final_amount - COALESCE(admin_fee, 0), 0)), 0) as net')
                ->value('net');

            // 2. Verified Moota net for EO
            $mootaNet = (float) Transaction::query()
                ->whereIn('event_id', $eventIds)
                ->where('payment_status', 'paid')
                ->where('payment_gateway', 'moota')
                ->where('final_amount', '>', 0)
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNotNull('moota_transaction_id')->where('moota_transaction_id', '!=', '');
                    })->orWhere(function ($sub) {
                        $sub->whereNotNull('payment_gateway_reference')->where('payment_gateway_reference', '!=', '');
                    });
                })
                ->selectRaw('COALESCE(SUM(GREATEST(final_amount - COALESCE(admin_fee, 0), 0)), 0) as net')
                ->value('net');

            $verifiedEventNet = $midtransNet + $mootaNet;

            // 3. Non-event wallet credits (e.g. topup, commission) & real withdrawals (completed/pending)
            $otherCredits = 0.0;
            $realWithdrawals = 0.0;
            if ($wallet) {
                $otherCredits = (float) WalletTransaction::query()
                    ->where('wallet_id', $wallet->id)
                    ->where('status', 'completed')
                    ->whereIn('type', ['deposit', 'commission'])
                    ->where(function ($q) {
                        $q->whereNull('reference_type')
                            ->orWhere('reference_type', '!=', Transaction::class);
                    })
                    ->whereRaw("(metadata IS NULL OR JSON_EXTRACT(metadata, '$.reconciliation') IS NULL OR JSON_EXTRACT(metadata, '$.reconciliation') = false)")
                    ->sum('amount');

                $realWithdrawals = (float) WalletTransaction::query()
                    ->where('wallet_id', $wallet->id)
                    ->where('type', 'withdraw')
                    ->whereIn('status', ['completed', 'pending'])
                    ->sum('amount');
            }

            $expectedBalance = max(0.0, round($verifiedEventNet + $otherCredits - $realWithdrawals, 2));
            $currentBalance = $wallet ? round((float) $wallet->balance, 2) : 0.0;
            $diff = round($currentBalance - $expectedBalance, 2);

            $statusLabel = abs($diff) < 0.01 ? 'OK' : ($diff > 0 ? 'OVERSTATED' : 'UNDERSTATED');

            if ($apply && $wallet && abs($diff) >= 0.01) {
                DB::transaction(function () use ($wallet, $expectedBalance, $verifiedEventNet, $otherCredits, $realWithdrawals, &$updatedCount) {
                    $lockedWallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
                    $balanceBefore = round((float) $lockedWallet->balance, 2);
                    $delta = round($balanceBefore - $expectedBalance, 2);

                    if (abs($delta) < 0.01) {
                        return;
                    }

                    $lockedWallet->balance = $expectedBalance;
                    $lockedWallet->save();

                    $lockedWallet->transactions()->create([
                        'type' => $delta > 0 ? 'refund' : 'deposit',
                        'amount' => abs($delta),
                        'balance_before' => $balanceBefore,
                        'balance_after' => $expectedBalance,
                        'status' => 'completed',
                        'description' => $delta > 0
                            ? 'Rekonsiliasi saldo wallet EO (koreksi duplikat / transaksi non-settlement)'
                            : 'Rekonsiliasi saldo wallet EO (penyesuaian transaksi terverifikasi)',
                        'metadata' => [
                            'reconciliation' => true,
                            'verified_event_net' => $verifiedEventNet,
                            'other_credits' => $otherCredits,
                            'real_withdrawals' => $realWithdrawals,
                            'balance_before' => $balanceBefore,
                            'balance_after' => $expectedBalance,
                        ],
                        'processed_at' => now(),
                    ]);

                    $updatedCount++;
                });

                $statusLabel = 'RECONCILED';
            }

            $rows[] = [
                $user->id,
                $user->email,
                'Rp '.number_format($verifiedEventNet, 0, ',', '.'),
                'Rp '.number_format($realWithdrawals, 0, ',', '.'),
                'Rp '.number_format($currentBalance, 0, ',', '.'),
                'Rp '.number_format($expectedBalance, 0, ',', '.'),
                ($diff >= 0 ? '+' : '-').'Rp '.number_format(abs($diff), 0, ',', '.'),
                $statusLabel,
            ];
        }

        $this->table(
            ['User ID', 'Email', 'Verified Gateway Net', 'Withdraw', 'DB Balance', 'Expected Balance', 'Selisih', 'Status'],
            $rows
        );

        if ($apply) {
            $this->info("Rekonsiliasi selesai. Total wallet diperbarui: {$updatedCount}.");
        } else {
            $this->line('Jalankan dengan --apply untuk menerapkan perubahan ke database.');
        }

        return self::SUCCESS;
    }
}
