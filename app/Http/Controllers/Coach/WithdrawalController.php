<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\CoachInvoice;
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
        $coachId = auth()->id();
        $withdrawals = CoachWithdrawal::where('coach_id', $coachId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // 1. Total Net Earnings from Paid Invoices
        $totalNetRevenue = CoachInvoice::where('coach_id', $coachId)
            ->where('payment_status', 'paid')
            ->sum('net_coach_amount');

        // Fallback to gross amount if net is 0
        if ($totalNetRevenue == 0) {
            $totalNetRevenue = CoachInvoice::where('coach_id', $coachId)
                ->where('payment_status', 'paid')
                ->sum('amount');
        }

        // 2. Pending and Processing Withdrawals
        $pendingAmount = CoachWithdrawal::where('coach_id', $coachId)
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        // 3. Completed Withdrawals
        $completedAmount = CoachWithdrawal::where('coach_id', $coachId)
            ->where('status', 'completed')
            ->sum('amount');

        // 4. Available Balance
        $availableBalance = max(0, $totalNetRevenue - $pendingAmount - $completedAmount);

        return view('coach.withdrawals.index', [
            'withdrawals' => $withdrawals,
            'availableBalance' => $availableBalance,
            'pendingAmount' => $pendingAmount,
            'completedAmount' => $completedAmount,
        ]);
    }

    /**
     * Request withdrawal
     */
    public function request(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:50000', // Minimum 50k
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_holder' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        $coachId = $user->id;

        // Calculate available balance
        $totalNetRevenue = CoachInvoice::where('coach_id', $coachId)
            ->where('payment_status', 'paid')
            ->sum('net_coach_amount');

        if ($totalNetRevenue == 0) {
            $totalNetRevenue = CoachInvoice::where('coach_id', $coachId)
                ->where('payment_status', 'paid')
                ->sum('amount');
        }

        $pendingAmount = CoachWithdrawal::where('coach_id', $coachId)
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        $completedAmount = CoachWithdrawal::where('coach_id', $coachId)
            ->where('status', 'completed')
            ->sum('amount');

        $availableBalance = max(0, $totalNetRevenue - $pendingAmount - $completedAmount);

        if ($validated['amount'] > $availableBalance) {
            return back()->withErrors(['amount' => 'Saldo yang dapat ditarik tidak mencukupi (Tersedia: Rp ' . number_format($availableBalance, 0, ',', '.') . ').']);
        }

        DB::beginTransaction();
        try {
            // Update bank info on user profile if supplied
            if (!empty($validated['bank_name']) || !empty($validated['bank_account_number'])) {
                $user->update([
                    'bank_name' => $validated['bank_name'] ?? $user->bank_name,
                    'bank_account_number' => $validated['bank_account_number'] ?? $user->bank_account_number,
                    'bank_account_holder' => $validated['bank_account_holder'] ?? $user->bank_account_holder,
                ]);
            }

            $withdrawal = CoachWithdrawal::create([
                'coach_id' => $user->id,
                'amount' => $validated['amount'],
                'status' => 'pending',
                'bank_name' => $user->bank_name ?? $validated['bank_name'] ?? 'BCA',
                'account_number' => $user->bank_account_number ?? $validated['bank_account_number'] ?? '-',
                'account_holder' => $user->bank_account_holder ?? $validated['bank_account_holder'] ?? $user->name,
            ]);

            DB::commit();

            return redirect()->route('coach.withdrawals.index')
                ->with('success', 'Permintaan withdrawal Rp ' . number_format($validated['amount'], 0, ',', '.') . ' berhasil diajukan. Akan diproses dalam 1-2 hari kerja.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal membuat permintaan withdrawal: '.$e->getMessage()]);
        }
    }
}
