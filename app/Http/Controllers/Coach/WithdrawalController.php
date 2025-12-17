<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\CoachWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /**
     * Show withdrawal history
     */
    public function index()
    {
        $withdrawals = CoachWithdrawal::where('coach_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate available balance (total earnings - pending/processing withdrawals)
        $pendingAmount = CoachWithdrawal::where('coach_id', auth()->id())
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        // TODO: Calculate total earnings from program sales
        // For now, just show pending amount
        $availableBalance = 0; // This should be calculated from wallet or earnings

        return view('coach.withdrawals.index', [
            'withdrawals' => $withdrawals,
            'availableBalance' => $availableBalance,
            'pendingAmount' => $pendingAmount,
        ]);
    }

    /**
     * Request withdrawal
     */
    public function request(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:50000', // Minimum 50k
        ]);

        $user = auth()->user();

        // TODO: Check if user has enough balance
        // For now, just check minimum amount
        if ($validated['amount'] < 50000) {
            return back()->withErrors(['amount' => 'Minimum withdrawal adalah Rp 50.000']);
        }

        // Check pending/processing withdrawals
        $pendingAmount = CoachWithdrawal::where('coach_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        // TODO: Check available balance vs requested amount
        // For now, just create withdrawal request

        DB::beginTransaction();
        try {
            $withdrawal = CoachWithdrawal::create([
                'coach_id' => $user->id,
                'amount' => $validated['amount'],
                'status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('coach.withdrawals.index')
                ->with('success', 'Permintaan withdrawal berhasil dibuat. Akan diproses dalam 1-2 hari kerja.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat permintaan withdrawal: ' . $e->getMessage()]);
        }
    }
}
