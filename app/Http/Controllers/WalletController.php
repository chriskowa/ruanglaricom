<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawal;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Show wallet page with balance, transaction history, and top-up form
     */
    public function index()
    {
        $user = Auth::user();
        $user->load('wallet');

        $wallet = $user->wallet ?? Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'locked_balance' => 0,
        ]);

        // Get transaction history
        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get top-up history
        $topups = WalletTopup::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $withdrawals = WalletWithdrawal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('wallet.index', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'topups' => $topups,
            'withdrawals' => $withdrawals,
        ]);
    }

    /**
     * Initiate top-up with Midtrans
     */
    public function topup(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:10000|max:10000000', // Min 10k, Max 10M
            ]);

            $user = Auth::user();

            // Ensure user has wallet
            if (! $user->wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'locked_balance' => 0,
                ]);
                $user->update(['wallet_id' => $wallet->id]);
            }

            // Create top-up transaction
            $result = $this->midtransService->createTopupTransaction($user, (float) $validated['amount']);

            if (! $result['success']) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['message'] ?? 'Gagal membuat transaksi top-up.',
                    ], 400);
                }

                return back()->withErrors(['amount' => $result['message'] ?? 'Gagal membuat transaksi top-up.']);
            }

            // Return JSON for AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                // If testing mode, return success immediately
                if (isset($result['testing_mode']) && $result['testing_mode']) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Top-up berhasil! (Testing Mode - Pembayaran otomatis approved)',
                        'testing_mode' => true,
                        'amount' => $validated['amount'],
                    ]);
                }

                return response()->json($result);
            }

            return view('wallet.topup-payment', [
                'snap_token' => $result['snap_token'],
                'topup_id' => $result['topup_id'],
                'amount' => $validated['amount'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle Midtrans webhook/callback
     */
    public function topupCallback(Request $request)
    {
        // Verify webhook signature if needed
        $result = $this->midtransService->handleWebhook($request);

        return response()->json($result);
    }

    public function withdraw(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:50000',
            ]);

            $user = Auth::user();
            if (! $user->wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'locked_balance' => 0,
                ]);
                $user->refresh();
            }

            if (! $user->bank_name || ! $user->bank_account_name || ! $user->bank_account_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lengkapi data bank di Profile terlebih dahulu.',
                ], 422);
            }

            $amount = (float) $validated['amount'];
            $withdrawalId = null;

            DB::transaction(function () use ($user, $amount, &$withdrawalId) {
                $wallet = Wallet::query()->whereKey($user->wallet->id)->lockForUpdate()->firstOrFail();
                $wallet->refresh();

                if ((float) $wallet->balance < $amount) {
                    throw new \RuntimeException('Saldo tidak cukup.');
                }

                $balanceBefore = (float) $wallet->balance;
                $wallet->decrement('balance', $amount);
                $wallet->increment('locked_balance', $amount);
                $balanceAfter = (float) $wallet->fresh()->balance;

                $withdrawal = WalletWithdrawal::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'bank_name' => $user->bank_name,
                    'bank_account_name' => $user->bank_account_name,
                    'bank_account_number' => $user->bank_account_number,
                    'status' => 'pending',
                ]);
                $withdrawalId = $withdrawal->id;

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'withdraw',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'status' => 'pending',
                    'description' => 'Withdraw request',
                    'reference_type' => WalletWithdrawal::class,
                    'reference_id' => $withdrawal->id,
                    'metadata' => [
                        'withdrawal_id' => $withdrawal->id,
                    ],
                ]);

                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'wallet_withdrawal',
                    'title' => 'Withdraw Diterima',
                    'message' => 'Permintaan withdraw kamu sudah diterima dan menunggu persetujuan admin.',
                    'reference_type' => WalletWithdrawal::class,
                    'reference_id' => $withdrawal->id,
                    'is_read' => false,
                ]);

                $adminIds = User::query()->where('role', 'admin')->pluck('id');
                foreach ($adminIds as $adminId) {
                    Notification::create([
                        'user_id' => $adminId,
                        'type' => 'wallet_withdrawal',
                        'title' => 'Withdraw Baru',
                        'message' => ($user->name ?? 'User').' mengajukan withdraw Rp '.number_format($amount, 0, ',', '.').'.',
                        'reference_type' => WalletWithdrawal::class,
                        'reference_id' => $withdrawal->id,
                        'is_read' => false,
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Withdraw berhasil dibuat dan menunggu proses.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
