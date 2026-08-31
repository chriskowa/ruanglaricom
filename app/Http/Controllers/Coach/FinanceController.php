<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\CoachInvoice;
use App\Models\CoachWithdrawal;
use App\Models\ProgramEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceController extends Controller
{
    /**
     * Display Coach Financial Overview & Wallet
     */
    public function index(Request $request)
    {
        $coachId = auth()->id();
        $now = Carbon::now();

        // 1. Total Revenue (Net after fees)
        $totalGrossRevenue = CoachInvoice::where('coach_id', $coachId)
            ->where('payment_status', 'paid')
            ->sum('amount');

        $totalNetRevenue = CoachInvoice::where('coach_id', $coachId)
            ->where('payment_status', 'paid')
            ->sum('net_coach_amount');

        // If net_coach_amount is 0/not set on some older records, fallback to amount
        if ($totalNetRevenue == 0 && $totalGrossRevenue > 0) {
            $totalNetRevenue = $totalGrossRevenue;
        }

        // 2. Withdrawals
        $pendingWithdrawals = CoachWithdrawal::where('coach_id', $coachId)
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        $completedWithdrawals = CoachWithdrawal::where('coach_id', $coachId)
            ->where('status', 'completed')
            ->sum('amount');

        // 3. Available Balance
        $availableBalance = max(0, $totalNetRevenue - $pendingWithdrawals - $completedWithdrawals);

        // 4. Monthly Recurring Revenue (MRR) - Estimated from active subscriptions
        $monthlyIncome = CoachInvoice::where('coach_id', $coachId)
            ->where('payment_status', 'paid')
            ->where('pricing_type', 'monthly')
            ->where('period_start', '<=', $now)
            ->where('period_end', '>=', $now)
            ->sum('net_coach_amount');

        $weeklyIncomeNormalized = CoachInvoice::where('coach_id', $coachId)
            ->where('payment_status', 'paid')
            ->where('pricing_type', 'weekly')
            ->where('period_start', '<=', $now)
            ->where('period_end', '>=', $now)
            ->sum('net_coach_amount') * 4.33; // normalized to 1 month

        $estimatedMRR = $monthlyIncome + $weeklyIncomeNormalized;

        // 5. Unpaid / Overdue Invoices
        $unpaidInvoicesCount = CoachInvoice::where('coach_id', $coachId)
            ->whereIn('payment_status', ['unpaid', 'overdue'])
            ->count();

        $unpaidInvoicesAmount = CoachInvoice::where('coach_id', $coachId)
            ->whereIn('payment_status', ['unpaid', 'overdue'])
            ->sum('amount');

        // 6. Active athlete subscriptions count
        $activeSubscriptionsCount = ProgramEnrollment::whereHas('program', function ($q) use ($coachId) {
            $q->where('coach_id', $coachId);
        })
            ->where('status', 'active')
            ->whereIn('subscription_status', ['active', 'grace_period'])
            ->count();

        // 7. Breakdown by Pricing Type
        $revenueByPricingType = [
            'monthly' => CoachInvoice::where('coach_id', $coachId)->where('payment_status', 'paid')->where('pricing_type', 'monthly')->sum('net_coach_amount'),
            'hourly' => CoachInvoice::where('coach_id', $coachId)->where('payment_status', 'paid')->where('pricing_type', 'hourly')->sum('net_coach_amount'),
            'weekly' => CoachInvoice::where('coach_id', $coachId)->where('payment_status', 'paid')->where('pricing_type', 'weekly')->sum('net_coach_amount'),
            'daily' => CoachInvoice::where('coach_id', $coachId)->where('payment_status', 'paid')->where('pricing_type', 'daily')->sum('net_coach_amount'),
            'one_time' => CoachInvoice::where('coach_id', $coachId)->where('payment_status', 'paid')->where('pricing_type', 'one_time')->sum('net_coach_amount'),
        ];

        // 8. Recent Transactions / Invoices
        $recentInvoices = CoachInvoice::where('coach_id', $coachId)
            ->with(['runner', 'program'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // 9. Recent Withdrawals
        $recentWithdrawals = CoachWithdrawal::where('coach_id', $coachId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('coach.finance.index', compact(
            'totalGrossRevenue',
            'totalNetRevenue',
            'availableBalance',
            'pendingWithdrawals',
            'completedWithdrawals',
            'estimatedMRR',
            'unpaidInvoicesCount',
            'unpaidInvoicesAmount',
            'activeSubscriptionsCount',
            'revenueByPricingType',
            'recentInvoices',
            'recentWithdrawals'
        ));
    }
}
