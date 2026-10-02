<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\CoachInvoice;
use App\Models\Notification;
use App\Models\ProgramEnrollment;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * List all invoices issued by coaches for this athlete (runner)
     */
    public function index(Request $request)
    {
        $runnerId = auth()->id();
        $status = $request->input('status', 'all');
        $search = $request->input('search');
        $coachId = $request->input('coach_id');
        $pricingType = $request->input('pricing_type');

        $query = CoachInvoice::where('runner_id', $runnerId)
            ->with(['coach', 'program', 'enrollment']);

        if ($status === 'paid') {
            $query->where('payment_status', 'paid');
        } elseif ($status === 'unpaid') {
            $query->where('payment_status', 'unpaid')
                ->where(function ($q) {
                    $q->whereNull('due_date')
                        ->orWhere('due_date', '>=', now()->toDateString());
                });
        } elseif ($status === 'overdue') {
            $query->where(function ($q) {
                $q->where('payment_status', 'overdue')
                    ->orWhere(function ($sub) {
                        $sub->where('payment_status', 'unpaid')
                            ->whereNotNull('due_date')
                            ->where('due_date', '<', now()->toDateString());
                    });
            });
        } elseif ($status === 'cancelled') {
            $query->where('payment_status', 'cancelled');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('coach', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('program', function ($sub) use ($search) {
                        $sub->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($coachId) {
            $query->where('coach_id', $coachId);
        }

        if ($pricingType) {
            $query->where('pricing_type', $pricingType);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Coaches who billed this runner for filter dropdown
        $coachIds = CoachInvoice::where('runner_id', $runnerId)->distinct()->pluck('coach_id');
        $coaches = User::whereIn('id', $coachIds)->get(['id', 'name']);

        // Counts for filter tabs
        $counts = [
            'all' => CoachInvoice::where('runner_id', $runnerId)->count(),
            'paid' => CoachInvoice::where('runner_id', $runnerId)->where('payment_status', 'paid')->count(),
            'unpaid' => CoachInvoice::where('runner_id', $runnerId)->where('payment_status', 'unpaid')->where(function ($q) {
                $q->whereNull('due_date')->orWhere('due_date', '>=', now()->toDateString());
            })->count(),
            'overdue' => CoachInvoice::where('runner_id', $runnerId)->where(function ($q) {
                $q->where('payment_status', 'overdue')->orWhere(function ($sub) {
                    $sub->where('payment_status', 'unpaid')->whereNotNull('due_date')->where('due_date', '<', now()->toDateString());
                });
            })->count(),
        ];

        // Summary metrics
        $totalUnpaidAmount = CoachInvoice::where('runner_id', $runnerId)
            ->whereIn('payment_status', ['unpaid', 'overdue'])
            ->sum('amount');

        $unpaidCount = $counts['unpaid'] + $counts['overdue'];

        $wallet = auth()->user()->wallet;
        $walletBalance = $wallet ? (float) $wallet->balance : 0.0;

        return view('runner.invoices.index', compact(
            'invoices',
            'coaches',
            'counts',
            'status',
            'totalUnpaidAmount',
            'unpaidCount',
            'walletBalance'
        ));
    }

    /**
     * Show single invoice details or return JSON for modal preview
     */
    public function show(Request $request, CoachInvoice $invoice)
    {
        if ($invoice->runner_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $invoice->load(['coach', 'program', 'enrollment']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'coach_name' => $invoice->coach->name ?? 'Coach',
                'coach_email' => $invoice->coach->email ?? '-',
                'coach_phone' => $invoice->coach->phone ?? '-',
                'program_title' => $invoice->program->title ?? 'Custom Coaching',
                'pricing_type' => $invoice->pricing_type,
                'pricing_label' => $invoice->pricing_label,
                'quantity' => $invoice->quantity,
                'amount' => (float) $invoice->amount,
                'amount_formatted' => 'Rp ' . number_format($invoice->amount, 0, ',', '.'),
                'period_start' => $invoice->period_start ? $invoice->period_start->format('d M Y') : '-',
                'period_end' => $invoice->period_end ? $invoice->period_end->format('d M Y') : '-',
                'due_date' => $invoice->due_date ? $invoice->due_date->format('d M Y') : '-',
                'is_overdue' => $invoice->is_overdue,
                'payment_status' => $invoice->payment_status,
                'status_label' => $invoice->status_label,
                'payment_method' => $invoice->payment_method,
                'payment_proof_url' => $invoice->payment_proof_url,
                'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('d M Y H:i') : null,
                'notes' => $invoice->notes,
                'created_at' => $invoice->created_at->format('d M Y'),
            ]);
        }

        return redirect()->route('runner.invoices.index');
    }

    /**
     * Dedicated printable invoice view
     */
    public function printInvoice(CoachInvoice $invoice)
    {
        if ($invoice->runner_id !== auth()->id() && $invoice->coach_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $invoice->load(['coach', 'runner', 'program', 'enrollment']);

        return view('runner.invoices.print', compact('invoice'));
    }

    /**
     * Confirm payment / upload transfer proof
     */
    public function confirmPayment(Request $request, CoachInvoice $invoice)
    {
        if ($invoice->runner_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($invoice->payment_status === 'paid') {
            return back()->withErrors(['error' => 'Invoice ini sudah dinyatakan lunas.']);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer,cash,wallet',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('payment_proof')) {
            if ($invoice->payment_proof && Storage::disk('public')->exists($invoice->payment_proof)) {
                Storage::disk('public')->delete($invoice->payment_proof);
            }
            $path = $request->file('payment_proof')->store('coach-invoices/proofs', 'public');
            $invoice->payment_proof = $path;
        }

        $invoice->payment_method = $validated['payment_method'];

        if (!empty($validated['notes'])) {
            $timestamp = now()->format('d/m/Y H:i');
            $newNote = "[Konfirmasi Atlet {$timestamp}]: {$validated['notes']}";
            $invoice->notes = $invoice->notes ? $invoice->notes . "\n" . $newNote : $newNote;
        }

        $invoice->save();

        // Send Notification to Coach
        Notification::create([
            'user_id' => $invoice->coach_id,
            'type' => 'coach_invoice_payment',
            'title' => 'Konfirmasi Pembayaran Tagihan Atlet',
            'message' => 'Atlet ' . (auth()->user()->name ?? 'Atlet') . ' telah mengunggah konfirmasi pembayaran untuk invoice ' . $invoice->invoice_number . ' (Rp ' . number_format($invoice->amount, 0, ',', '.') . '). Silakan lakukan verifikasi.',
            'reference_type' => CoachInvoice::class,
            'reference_id' => $invoice->id,
            'is_read' => false,
        ]);

        return back()->with('success', "Konfirmasi pembayaran untuk invoice {$invoice->invoice_number} berhasil dikirim ke Coach.");
    }

    /**
     * Pay invoice instantly using RuangLari Wallet balance
     */
    public function payWithWallet(CoachInvoice $invoice)
    {
        $user = auth()->user();

        if ($invoice->runner_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if ($invoice->payment_status === 'paid') {
            return back()->withErrors(['error' => 'Invoice ini sudah lunas.']);
        }

        $wallet = $user->wallet;
        if (!$wallet || (float) $wallet->balance < (float) $invoice->amount) {
            $balanceFormatted = 'Rp ' . number_format($wallet?->balance ?? 0, 0, ',', '.');
            return back()->withErrors(['error' => "Saldo wallet Anda ({$balanceFormatted}) tidak mencukupi untuk membayar tagihan Rp " . number_format($invoice->amount, 0, ',', '.') . ". Silakan lakukan Top Up terlebih dahulu."]);
        }

        DB::beginTransaction();
        try {
            $balanceBefore = (float) $wallet->balance;
            $wallet->decrement('balance', $invoice->amount);
            $balanceAfter = (float) $wallet->fresh()->balance;

            // Outgoing Wallet Transaction
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'transfer',
                'amount' => $invoice->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'description' => 'Pelunasan Tagihan ' . $invoice->invoice_number . ' kepada Coach ' . ($invoice->coach->name ?? 'Coach'),
                'reference_type' => CoachInvoice::class,
                'reference_id' => $invoice->id,
                'processed_at' => now(),
            ]);

            // Update Invoice Status
            $invoice->update([
                'payment_status' => 'paid',
                'payment_method' => 'wallet',
                'paid_at' => now(),
                'verified_by_coach' => true,
                'notes' => $invoice->notes ? $invoice->notes . "\n[Sistem]: Dibayar via Saldo Wallet pada " . now()->format('d/m/Y H:i') : "[Sistem]: Dibayar via Saldo Wallet pada " . now()->format('d/m/Y H:i'),
            ]);

            // If attached to an enrollment, update subscription quotas and periods
            if ($invoice->enrollment_id) {
                $enrollment = ProgramEnrollment::find($invoice->enrollment_id);
                if ($enrollment) {
                    $enrollment->subscription_status = 'active';
                    $enrollment->payment_status = 'paid';

                    if ($invoice->pricing_type === 'hourly') {
                        $sessionsToAdd = $invoice->quantity;
                        $enrollment->total_sessions_quota = ($enrollment->total_sessions_quota ?? 0) + $sessionsToAdd;
                        $enrollment->sessions_remaining = ($enrollment->sessions_remaining ?? 0) + $sessionsToAdd;
                    } elseif ($invoice->pricing_type === 'monthly') {
                        $startDate = $invoice->period_start ?: now()->toDateString();
                        $endDate = $invoice->period_end ?: Carbon::parse($startDate)->addMonths($invoice->quantity)->toDateString();
                        $enrollment->current_period_start = $startDate;
                        $enrollment->current_period_end = $endDate;
                        $enrollment->next_billing_date = $endDate;
                    } elseif ($invoice->pricing_type === 'weekly') {
                        $startDate = $invoice->period_start ?: now()->toDateString();
                        $endDate = $invoice->period_end ?: Carbon::parse($startDate)->addWeeks($invoice->quantity)->toDateString();
                        $enrollment->current_period_start = $startDate;
                        $enrollment->current_period_end = $endDate;
                        $enrollment->next_billing_date = $endDate;
                    } elseif ($invoice->pricing_type === 'daily') {
                        $startDate = $invoice->period_start ?: now()->toDateString();
                        $endDate = $invoice->period_end ?: Carbon::parse($startDate)->addDays($invoice->quantity)->toDateString();
                        $enrollment->current_period_start = $startDate;
                        $enrollment->current_period_end = $endDate;
                        $enrollment->next_billing_date = $endDate;
                    }

                    $enrollment->save();
                }
            }

            // Notification for Coach
            Notification::create([
                'user_id' => $invoice->coach_id,
                'type' => 'coach_invoice_paid',
                'title' => 'Tagihan Atlet Telah Dilunasi',
                'message' => 'Atlet ' . $user->name . ' telah melunasi invoice ' . $invoice->invoice_number . ' sebesar Rp ' . number_format($invoice->amount, 0, ',', '.') . ' menggunakan Saldo Wallet.',
                'reference_type' => CoachInvoice::class,
                'reference_id' => $invoice->id,
                'is_read' => false,
            ]);

            // Notification for Athlete
            Notification::create([
                'user_id' => $user->id,
                'type' => 'coach_invoice_paid',
                'title' => 'Pembayaran Tagihan Berhasil',
                'message' => 'Invoice ' . $invoice->invoice_number . ' sebesar Rp ' . number_format($invoice->amount, 0, ',', '.') . ' berhasil dibayar menggunakan Saldo Wallet.',
                'reference_type' => CoachInvoice::class,
                'reference_id' => $invoice->id,
                'is_read' => false,
            ]);

            DB::commit();

            return back()->with('success', "Invoice {$invoice->invoice_number} berhasil dilunasi menggunakan Saldo Wallet.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses pembayaran wallet: ' . $e->getMessage()]);
        }
    }
}
