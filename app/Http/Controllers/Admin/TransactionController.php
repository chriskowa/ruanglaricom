<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Order;
use App\Models\Notification;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            return back()->with('success', 'Withdraw berhasil disetujui.');
        } catch (\Throwable $e) {
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

            return back()->with('success', 'Withdraw berhasil ditolak dan dana direfund.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
