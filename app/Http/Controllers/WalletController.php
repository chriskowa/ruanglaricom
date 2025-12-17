<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('wallet.index', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'topups' => $topups,
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
            if (!$user->wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'locked_balance' => 0,
                ]);
                $user->update(['wallet_id' => $wallet->id]);
            }

            // Create top-up transaction
            $result = $this->midtransService->createTopupTransaction($user, (float)$validated['amount']);

            if (!$result['success']) {
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
}
