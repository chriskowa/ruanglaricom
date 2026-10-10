<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Order;
use App\Models\Notification;
use App\Models\Transaction as EventTransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->string('tab')->toString() ?: 'withdrawals';
        $status = $request->string('status')->toString();
        $q = $request->string('q')->toString();

        $withdrawals = null;
        $topups = null;
        $transactions = null;
        $programOrders = null;
        $marketplaceOrders = null;
        $eventTransactions = null;

        if ($tab === 'topups') {
            $topups = WalletTopup::query()
                ->with('user')
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($q !== '', function ($query) use ($q) {
                    $query->whereHas('user', function ($userQuery) use ($q) {
                        $userQuery
                            ->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('username', 'like', "%{$q}%");
                    })->orWhere('midtrans_order_id', 'like', "%{$q}%");
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'event_tickets') {
            $eventTransactions = EventTransaction::query()
                ->with(['event.user', 'user', 'participants'])
                ->when($status !== '', fn ($query) => $query->where('payment_status', $status))
                ->when($q !== '', function ($query) use ($q) {
                    $query->where('public_ref', 'like', "%{$q}%")
                        ->orWhere('midtrans_order_id', 'like', "%{$q}%")
                        ->orWhereHas('event', function ($eventQuery) use ($q) {
                            $eventQuery->where('name', 'like', "%{$q}%")
                                ->orWhereHas('user', function ($eoQuery) use ($q) {
                                    $eoQuery->where('name', 'like', "%{$q}%")
                                        ->orWhere('email', 'like', "%{$q}%");
                                });
                        })
                        ->orWhereHas('user', function ($userQuery) use ($q) {
                            $userQuery->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'ledger') {
            $transactions = WalletTransaction::query()
                ->with(['wallet.user'])
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($q !== '', function ($query) use ($q) {
                    $query
                        ->where('description', 'like', "%{$q}%")
                        ->orWhereHas('wallet.user', function ($userQuery) use ($q) {
                            $userQuery
                                ->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%")
                                ->orWhere('username', 'like', "%{$q}%");
                        });
                })
                ->latest()
                ->paginate(30)
                ->withQueryString();
        } elseif ($tab === 'program_orders') {
            $programOrders = Order::query()
                ->with('user')
                ->when($status !== '', fn ($query) => $query->where('payment_status', $status))
                ->when($q !== '', function ($query) use ($q) {
                    $query->whereHas('user', function ($userQuery) use ($q) {
                        $userQuery
                            ->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('username', 'like', "%{$q}%");
                    })->orWhere('order_number', 'like', "%{$q}%");
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'marketplace_orders') {
            $marketplaceOrders = MarketplaceOrder::query()
                ->with(['buyer', 'seller'])
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($q !== '', function ($query) use ($q) {
                    $query->where('invoice_number', 'like', "%{$q}%")
                        ->orWhereHas('buyer', function ($buyerQuery) use ($q) {
                            $buyerQuery
                                ->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%")
                                ->orWhere('username', 'like', "%{$q}%");
                        })
                        ->orWhereHas('seller', function ($sellerQuery) use ($q) {
                            $sellerQuery
                                ->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%")
                                ->orWhere('username', 'like', "%{$q}%");
                        });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();
        } else {
            $tab = 'withdrawals';
            $withdrawals = WalletWithdrawal::query()
                ->with('user')
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($q !== '', function ($query) use ($q) {
                    $query->whereHas('user', function ($userQuery) use ($q) {
                        $userQuery
                            ->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('username', 'like', "%{$q}%");
                    });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        $counts = [
            'withdrawals_pending' => WalletWithdrawal::where('status', 'pending')->count(),
            'topups_pending' => WalletTopup::where('status', 'pending')->count(),
            'event_tickets_paid' => EventTransaction::where('payment_status', 'paid')->count(),
        ];

        return view('admin.transactions.index', [
            'tab' => $tab,
            'status' => $status,
            'q' => $q,
            'withdrawals' => $withdrawals,
            'topups' => $topups,
            'transactions' => $transactions,
            'programOrders' => $programOrders,
            'marketplaceOrders' => $marketplaceOrders,
            'eventTransactions' => $eventTransactions,
            'counts' => $counts,
        ]);
    }

    public function approveWithdrawal(Request $request, WalletWithdrawal $withdrawal)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($withdrawal, $validated) {
                $wd = WalletWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
                if ($wd->status !== 'pending') {
                    throw new \RuntimeException('Withdraw ini sudah diproses.');
                }

                $wallet = Wallet::query()->where('user_id', $wd->user_id)->lockForUpdate()->first();
                if (! $wallet) {
                    throw new \RuntimeException('Wallet user tidak ditemukan.');
                }

                $amount = (float) $wd->amount;
                $wallet->refresh();
                if ((float) $wallet->locked_balance < $amount) {
                    throw new \RuntimeException('Locked balance tidak cukup untuk memproses withdraw ini.');
                }

                $wallet->decrement('locked_balance', $amount);

                $wd->update([
                    'status' => 'approved',
                    'notes' => $validated['notes'] ?? $wd->notes,
                ]);

                $txn = WalletTransaction::query()
                    ->where('reference_type', WalletWithdrawal::class)
                    ->where('reference_id', $wd->id)
                    ->where('type', 'withdraw')
                    ->orderByDesc('id')
                    ->first();

                if ($txn) {
                    $txn->update([
                        'status' => 'completed',
                        'processed_at' => now(),
                        'description' => $txn->description ?: 'Withdraw approved',
                    ]);
                }

                Notification::create([
                    'user_id' => $wd->user_id,
                    'type' => 'wallet_withdrawal',
                    'title' => 'Withdraw Disetujui',
                    'message' => 'Withdraw kamu telah disetujui dan sedang diproses.',
                    'reference_type' => WalletWithdrawal::class,
                    'reference_id' => $wd->id,
                    'is_read' => false,
                ]);

                User::query()
                    ->where('role', 'admin')
                    ->pluck('id')
                    ->each(function ($adminId) use ($wd) {
                        Notification::create([
                            'user_id' => $adminId,
                            'type' => 'wallet_withdrawal',
                            'title' => 'Withdraw Approved',
                            'message' => 'Withdraw #'.$wd->id.' disetujui.',
                            'reference_type' => WalletWithdrawal::class,
                            'reference_id' => $wd->id,
                            'is_read' => false,
                        ]);
                    });
            });

            if ($request->ajax()) {
                return response()->json(['status' => 'success', 'message' => 'Withdrawal berhasil disetujui.']);
            }

            return back()->with('success', 'Withdraw berhasil disetujui.');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectWithdrawal(Request $request, WalletWithdrawal $withdrawal)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($withdrawal, $validated) {
                $wd = WalletWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
                if ($wd->status !== 'pending') {
                    throw new \RuntimeException('Withdraw ini sudah diproses.');
                }

                $wallet = Wallet::query()->where('user_id', $wd->user_id)->lockForUpdate()->first();
                if (! $wallet) {
                    throw new \RuntimeException('Wallet user tidak ditemukan.');
                }

                $amount = (float) $wd->amount;
                $wallet->refresh();
                if ((float) $wallet->locked_balance < $amount) {
                    throw new \RuntimeException('Locked balance tidak cukup untuk menolak withdraw ini.');
                }

                $balanceBefore = (float) $wallet->balance;
                $wallet->decrement('locked_balance', $amount);
                $wallet->increment('balance', $amount);
                $balanceAfter = (float) $wallet->fresh()->balance;

                $wd->update([
                    'status' => 'rejected',
                    'notes' => $validated['notes'] ?? $wd->notes,
                ]);

                $txn = WalletTransaction::query()
                    ->where('reference_type', WalletWithdrawal::class)
                    ->where('reference_id', $wd->id)
                    ->where('type', 'withdraw')
                    ->orderByDesc('id')
                    ->first();

                if ($txn) {
                    $txn->update([
                        'status' => 'cancelled',
                        'processed_at' => now(),
                        'description' => 'Withdraw rejected',
                    ]);
                }

                $wallet->transactions()->create([
                    'type' => 'refund',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'status' => 'completed',
                    'description' => 'Refund from rejected withdrawal',
                    'reference_type' => WalletWithdrawal::class,
                    'reference_id' => $wd->id,
                    'metadata' => [
                        'withdrawal_id' => $wd->id,
                    ],
                    'processed_at' => now(),
                ]);

                $message = 'Withdraw kamu ditolak. Dana dikembalikan ke saldo.';
                if (! empty($validated['notes'])) {
                    $message .= ' Catatan admin: '.$validated['notes'];
                }

                Notification::create([
                    'user_id' => $wd->user_id,
                    'type' => 'wallet_withdrawal',
                    'title' => 'Withdraw Ditolak',
                    'message' => $message,
                    'reference_type' => WalletWithdrawal::class,
                    'reference_id' => $wd->id,
                    'is_read' => false,
                ]);

                User::query()
                    ->where('role', 'admin')
                    ->pluck('id')
                    ->each(function ($adminId) use ($wd) {
                        Notification::create([
                            'user_id' => $adminId,
                            'type' => 'wallet_withdrawal',
                            'title' => 'Withdraw Rejected',
                            'message' => 'Withdraw #'.$wd->id.' ditolak.',
                            'reference_type' => WalletWithdrawal::class,
                            'reference_id' => $wd->id,
                            'is_read' => false,
                        ]);
                    });
            });

            if ($request->ajax()) {
                return response()->json(['status' => 'success', 'message' => 'Withdrawal ditolak & dana berhasil di-refund.']);
            }

            return back()->with('success', 'Withdraw berhasil ditolak dan dana direfund.');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete and clean an event ticket transaction, rolling back EO wallet if credited.
     */
    public function destroyEventTransaction(Request $request, EventTransaction $transaction)
    {
        try {
            DB::transaction(function () use ($transaction) {
                $transaction->load(['event.user.wallet', 'participants']);
                $organizer = $transaction->event?->user;
                $couponId = $transaction->coupon_id;

                // 1. Rollback EO wallet if deposit mutation exists
                $walletTxns = WalletTransaction::query()
                    ->where('reference_type', EventTransaction::class)
                    ->where('reference_id', $transaction->id)
                    ->get();

                foreach ($walletTxns as $wTxn) {
                    $wallet = $wTxn->wallet;
                    if ($wTxn->type === 'deposit' && $wTxn->status === 'completed' && $wallet) {
                        $amountToDeduct = (float) $wTxn->amount;
                        $wallet->refresh();
                        $newBalance = max(0, (float) $wallet->balance - $amountToDeduct);
                        $wallet->update(['balance' => $newBalance]);
                    }
                    $wTxn->delete();
                }

                // Fallback: If no direct wallet mutation record was found, but transaction was paid and organizer has wallet
                if ($walletTxns->isEmpty() && in_array($transaction->payment_status, ['paid', 'settlement', 'capture']) && $organizer && $organizer->wallet) {
                    if (! in_array($transaction->payment_gateway, ['manual_transfer', 'cod'], true)) {
                        $finalAmount = (float) $transaction->final_amount;
                        $adminFee = (float) $transaction->admin_fee;
                        $organizerAmount = max(0, $finalAmount - $adminFee);
                        if ($organizerAmount > 0) {
                            $wallet = $organizer->wallet;
                            $wallet->refresh();
                            $newBalance = max(0, (float) $wallet->balance - $organizerAmount);
                            $wallet->update(['balance' => $newBalance]);
                        }
                    }
                }

                // 2. Delete any remaining participants tied to this transaction
                $transaction->participants()->delete();

                // 3. Delete payment proof if stored
                if ($transaction->payment_proof && Storage::disk('public')->exists($transaction->payment_proof)) {
                    Storage::disk('public')->delete($transaction->payment_proof);
                }

                // 4. Delete transaction
                $transaction->delete();

                // 5. Recalculate coupon count if used
                if ($couponId) {
                    \App\Models\Coupon::recalculateUsedCount($couponId);
                }
            });

            return back()->with('success', "Transaksi tiket event #{$transaction->public_ref} berhasil dibersihkan beserta saldo dan mutasi dompet EO.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membersihkan transaksi: '.$e->getMessage());
        }
    }

    /**
     * Delete a single ledger wallet mutation record and optionally rollback balance.
     */
    public function destroyLedgerTransaction(Request $request, WalletTransaction $transaction)
    {
        try {
            DB::transaction(function () use ($transaction) {
                $wallet = $transaction->wallet;
                if ($transaction->type === 'deposit' && $transaction->status === 'completed' && $wallet) {
                    $wallet->refresh();
                    $wallet->update(['balance' => max(0, (float) $wallet->balance - (float) $transaction->amount)]);
                }
                $transaction->delete();
            });

            return back()->with('success', 'Mutasi transaksi ledger berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus mutasi ledger: '.$e->getMessage());
        }
    }
}
