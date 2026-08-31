<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\CoachInvoice;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * List all athlete invoices and billing records
     */
    public function index(Request $request)
    {
        $coachId = auth()->id();
        $status = $request->input('status', 'all');
        $search = $request->input('search');
        $programId = $request->input('program_id');
        $pricingType = $request->input('pricing_type');

        $query = CoachInvoice::where('coach_id', $coachId)
            ->with(['runner', 'program', 'enrollment']);

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
                    ->orWhereHas('runner', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        if ($pricingType) {
            $query->where('pricing_type', $pricingType);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Get coach programs for filter and modal
        $programs = Program::where('coach_id', $coachId)->get();

        // Get enrolled athletes for selection in modal
        $athletes = User::whereHas('programEnrollments', function ($q) use ($coachId) {
            $q->whereHas('program', function ($p) use ($coachId) {
                $p->where('coach_id', $coachId);
            });
        })
            ->distinct()
            ->get();

        // Counts for tabs
        $counts = [
            'all' => CoachInvoice::where('coach_id', $coachId)->count(),
            'paid' => CoachInvoice::where('coach_id', $coachId)->where('payment_status', 'paid')->count(),
            'unpaid' => CoachInvoice::where('coach_id', $coachId)->where('payment_status', 'unpaid')->where(function ($q) {
                $q->whereNull('due_date')->orWhere('due_date', '>=', now()->toDateString());
            })->count(),
            'overdue' => CoachInvoice::where('coach_id', $coachId)->where(function ($q) {
                $q->where('payment_status', 'overdue')->orWhere(function ($sub) {
                    $sub->where('payment_status', 'unpaid')->whereNotNull('due_date')->where('due_date', '<', now()->toDateString());
                });
            })->count(),
        ];

        return view('coach.finance.invoices', compact('invoices', 'programs', 'athletes', 'counts', 'status'));
    }

    /**
     * Create a new invoice for an athlete
     */
    public function store(Request $request)
    {
        $coachId = auth()->id();

        $validated = $request->validate([
            'runner_id' => 'required|exists:users,id',
            'program_id' => 'nullable|exists:programs,id',
            'enrollment_id' => 'nullable|exists:program_enrollments,id',
            'pricing_type' => 'required|in:one_time,hourly,daily,weekly,monthly',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = (float) $validated['amount'];
        $platformFeeRate = 0; // Default 0 for coach-generated manual invoices
        $platformFee = $amount * $platformFeeRate;
        $netAmount = $amount - $platformFee;

        // Auto find enrollment if not provided
        $enrollmentId = $validated['enrollment_id'] ?? null;
        if (!$enrollmentId && !empty($validated['program_id'])) {
            $enrollment = ProgramEnrollment::where('runner_id', $validated['runner_id'])
                ->where('program_id', $validated['program_id'])
                ->latest()
                ->first();
            if ($enrollment) {
                $enrollmentId = $enrollment->id;
            }
        }

        $invoice = CoachInvoice::create([
            'invoice_number' => CoachInvoice::generateInvoiceNumber($coachId),
            'coach_id' => $coachId,
            'runner_id' => $validated['runner_id'],
            'program_id' => $validated['program_id'] ?? null,
            'enrollment_id' => $enrollmentId,
            'pricing_type' => $validated['pricing_type'],
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'net_coach_amount' => $netAmount,
            'quantity' => $validated['quantity'],
            'period_start' => $validated['period_start'] ?? now()->toDateString(),
            'period_end' => $validated['period_end'] ?? null,
            'due_date' => $validated['due_date'] ?? now()->addDays(7)->toDateString(),
            'payment_status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('coach.invoices.index')
            ->with('success', "Invoice {$invoice->invoice_number} berhasil dibuat.");
    }

    /**
     * Mark invoice as paid (e.g. manual bank transfer or cash)
     */
    public function markPaid(Request $request, CoachInvoice $invoice)
    {
        if ($invoice->coach_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer,cash,gateway,wallet',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $invoice->update([
                'payment_status' => 'paid',
                'payment_method' => $validated['payment_method'],
                'paid_at' => now(),
                'verified_by_coach' => true,
                'notes' => $validated['notes'] ? ($invoice->notes ? $invoice->notes . "\n" . $validated['notes'] : $validated['notes']) : $invoice->notes,
            ]);

            // If attached to an enrollment, update enrollment subscription and session quotas
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

            DB::commit();

            return back()->with('success', "Invoice {$invoice->invoice_number} berhasil ditandai LUNAS.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui status invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * Record session usage (decrement session quota) for hourly/session athletes
     */
    public function recordSessionUsage(Request $request, ProgramEnrollment $enrollment)
    {
        // Verify coach ownership
        if ($enrollment->program->coach_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        if (($enrollment->sessions_remaining ?? 0) <= 0) {
            return back()->withErrors(['error' => 'Kuota sesi atlet ini sudah habis. Silakan buat invoice sesi baru.']);
        }

        $sessionsToDeduct = (int) $request->input('count', 1);
        if ($sessionsToDeduct > $enrollment->sessions_remaining) {
            $sessionsToDeduct = $enrollment->sessions_remaining;
        }

        $enrollment->sessions_used = ($enrollment->sessions_used ?? 0) + $sessionsToDeduct;
        $enrollment->sessions_remaining = max(0, ($enrollment->sessions_remaining ?? 0) - $sessionsToDeduct);
        $enrollment->save();

        return back()->with('success', "Berhasil mencatat {$sessionsToDeduct} sesi latihan untuk atlet {$enrollment->runner->name}. Sisa sesi: {$enrollment->sessions_remaining}.");
    }

    /**
     * Cancel an invoice
     */
    public function cancel(CoachInvoice $invoice)
    {
        if ($invoice->coach_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        if ($invoice->payment_status === 'paid') {
            return back()->withErrors(['error' => 'Invoice yang sudah lunas tidak dapat dibatalkan.']);
        }

        $invoice->update(['payment_status' => 'cancelled']);

        return back()->with('success', "Invoice {$invoice->invoice_number} berhasil dibatalkan.");
    }
}
