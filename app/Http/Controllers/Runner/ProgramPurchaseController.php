<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProgramPurchaseController extends Controller
{
    /**
     * Purchase a program
     */
    public function purchase(Program $program)
    {
        $user = auth()->user();

        // Check if already enrolled
        if ($program->enrollments()->where('runner_id', $user->id)->exists()) {
            return redirect()->route('runner.calendar')
                ->with('error', 'Anda sudah terdaftar di program ini.');
        }

        // Check if program can be purchased
        if (!$program->canBePurchasedBy($user)) {
            return back()->with('error', 'Program tidak dapat dibeli.');
        }

        // Free program - enroll directly
        if ($program->isFree()) {
            return $this->enrollFree($program);
        }

        // Check wallet balance
        $wallet = $user->wallet;
        if (!$wallet || $wallet->balance < $program->price) {
            return back()->with('error', 'Saldo wallet tidak cukup. Silakan top-up terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            // Deduct from wallet
            $balanceBefore = $wallet->balance;
            $wallet->decrement('balance', $program->price);
            $balanceAfter = $wallet->balance;

            // Create enrollment
            $enrollment = ProgramEnrollment::create([
                'program_id' => $program->id,
                'runner_id' => $user->id,
                'start_date' => null,
                'end_date' => null,
                'status' => 'purchased',
                'payment_status' => 'paid',
            ]);

            // Create wallet transaction (outgoing)
            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'transfer',
                'amount' => $program->price,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'description' => 'Pembelian program: ' . $program->title,
                'reference_id' => $enrollment->id,
                'reference_type' => ProgramEnrollment::class,
                'processed_at' => now(),
            ]);

            // Update enrollment with transaction ID
            $enrollment->update(['payment_transaction_id' => $transaction->id]);

            // Increment enrolled count
            $program->increment('enrolled_count');

            // TODO: Create commission transaction for coach if needed

            DB::commit();

            return redirect()->route('runner.calendar')
                ->with('success', 'Program berhasil dibeli! Program telah ditambahkan ke Program Bag Anda.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat membeli program: ' . $e->getMessage());
        }
    }

    /**
     * Enroll in free program
     */
    private function enrollFree(Program $program)
    {
        $user = auth()->user();

        $enrollment = ProgramEnrollment::create([
            'program_id' => $program->id,
            'runner_id' => $user->id,
            'start_date' => null,
            'end_date' => null,
            'status' => 'purchased',
            'payment_status' => 'paid', // Free programs are considered paid
        ]);

        $program->increment('enrolled_count');

        return redirect()->route('runner.calendar')
            ->with('success', 'Program berhasil didaftarkan! Program telah ditambahkan ke Program Bag Anda.');
    }
}
