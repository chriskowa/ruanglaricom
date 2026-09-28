<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\WalletTransaction;
use App\Models\Notification;
use App\Helpers\WhatsApp;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProgramPurchaseController extends Controller
{
    /**
     * Purchase a program
     */
    public function purchase(Program $program)
    {
        $user = auth()->user();

        $hasActiveProgram = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->exists();

        // Check if already enrolled
        $existingEnrollment = $program->enrollments()
            ->where('runner_id', $user->id)
            ->whereIn('status', ['purchased', 'active'])
            ->first();

        if ($existingEnrollment) {
            if ($existingEnrollment->status === 'purchased') {
                if (! $hasActiveProgram) {
                    $startDate = Carbon::today();
                    $durationWeeks = $program->duration_weeks ?? 12;
                    $endDate = $startDate->copy()->addWeeks($durationWeeks);

                    $existingEnrollment->update([
                        'status' => 'active',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]);

                    return redirect()->route('runner.dashboard', ['tab' => 'calendar'])
                        ->with('success', 'Program ' . $program->title . ' berhasil diaktifkan di kalender latihan Anda!');
                }

                return redirect()->route('runner.dashboard', ['activate_program' => $existingEnrollment->id])
                    ->with('new_program_bag_id', $existingEnrollment->id)
                    ->with('show_replace_modal', true)
                    ->with('info', 'Program ini sudah ada di Program Bag Anda. Silakan tentukan apakah ingin mengganti program aktif atau tetap menyimpannya di Program Bag.');
            }

            return redirect()->route('runner.dashboard', ['tab' => 'calendar'])
                ->with('info', 'Program ini sudah aktif di kalender latihan Anda.');
        }

        // Check if program can be purchased
        if (! $program->canBePurchasedBy($user)) {
            return back()->with('error', 'Program tidak dapat dibeli.');
        }

        // Free program - enroll directly
        if ($program->isFree()) {
            return $this->enrollFree($program);
        }

        // Check wallet balance
        $wallet = $user->wallet;
        if (! $wallet || $wallet->balance < $program->price) {
            return back()->with('error', 'Saldo wallet tidak cukup. Silakan top-up terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            // Deduct from wallet
            $balanceBefore = $wallet->balance;
            $wallet->decrement('balance', $program->price);
            $balanceAfter = $wallet->balance;

            $startDate = ! $hasActiveProgram ? Carbon::today() : null;
            $durationWeeks = $program->duration_weeks ?? 12;
            $endDate = (! $hasActiveProgram && $startDate) ? $startDate->copy()->addWeeks($durationWeeks) : null;
            $status = ! $hasActiveProgram ? 'active' : 'purchased';

            // Create enrollment
            $enrollment = ProgramEnrollment::create([
                'program_id' => $program->id,
                'runner_id' => $user->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
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
                'description' => 'Pembelian program: '.$program->title,
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

            // Notify Coach (in-app notification)
            try {
                $coach = $program->coach;
                if ($coach) {
                    Notification::create([
                        'user_id' => $coach->id,
                        'type' => 'program_order',
                        'title' => 'Pesanan Program Baru',
                        'message' => 'Runner '.$user->name.' membeli program: '.$program->title,
                        'reference_type' => ProgramEnrollment::class,
                        'reference_id' => $enrollment->id,
                        'is_read' => false,
                    ]);
                }
            } catch (\Throwable $e) {
                // swallow notification errors
            }

            if ($hasActiveProgram) {
                return redirect()->route('runner.dashboard', ['activate_program' => $enrollment->id])
                    ->with('new_program_bag_id', $enrollment->id)
                    ->with('show_replace_modal', true)
                    ->with('info', 'Program berhasil dibeli! Karena Anda sedang menjalankan program aktif, silakan tentukan apakah ingin mengganti program aktif atau menyimpannya di Program Bag.');
            }

            return redirect()->route('runner.dashboard', ['tab' => 'calendar'])
                ->with('success', 'Program ' . $program->title . ' berhasil dibeli dan langsung diaktifkan di kalender latihan Anda!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat membeli program: '.$e->getMessage());
        }
    }

    /**
     * Enroll in free program
     */
    private function enrollFree(Program $program)
    {
        $user = auth()->user();

        $hasActiveProgram = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->exists();

        $startDate = ! $hasActiveProgram ? Carbon::today() : null;
        $durationWeeks = $program->duration_weeks ?? 12;
        $endDate = (! $hasActiveProgram && $startDate) ? $startDate->copy()->addWeeks($durationWeeks) : null;
        $status = ! $hasActiveProgram ? 'active' : 'purchased';

        $enrollment = ProgramEnrollment::create([
            'program_id' => $program->id,
            'runner_id' => $user->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'payment_status' => 'paid', // Free programs are considered paid
        ]);

        $program->increment('enrolled_count');

        // Notify Coach (in-app notification)
        try {
            $coach = $program->coach;
            if ($coach) {
                Notification::create([
                    'user_id' => $coach->id,
                    'type' => 'program_order',
                    'title' => 'Pesanan Program (Free)',
                    'message' => 'Runner '.$user->name.' mendaftar program gratis: '.$program->title,
                    'reference_type' => ProgramEnrollment::class,
                    'reference_id' => $enrollment->id,
                    'is_read' => false,
                ]);
            }
        } catch (\Throwable $e) {
            // swallow notification errors
        }

        if ($hasActiveProgram) {
            return redirect()->route('runner.dashboard', ['activate_program' => $enrollment->id])
                ->with('new_program_bag_id', $enrollment->id)
                ->with('show_replace_modal', true)
                ->with('info', 'Program berhasil didaftarkan! Karena Anda sedang menjalankan program aktif, silakan tentukan apakah ingin mengganti program aktif atau menyimpannya di Program Bag.');
        }

        return redirect()->route('runner.dashboard', ['tab' => 'calendar'])
            ->with('success', 'Program ' . $program->title . ' berhasil diaktifkan di kalender latihan Anda!');
    }
}
