<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventPayout;
use App\Models\Participant;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EventFinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $eventsQuery = Event::query()
            ->with('user')
            ->select(['id', 'name', 'user_id', 'start_at', 'created_at'])
            ->orderByDesc('created_at');

        if ($q !== '') {
            $eventsQuery->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        $events = $eventsQuery->paginate(20)->withQueryString();

        return view('admin.reports.event-finance.index', [
            'events' => $events,
            'q' => $q,
        ]);
    }

    public function show(Event $event)
    {
        $event->load('user');

        $paidStatuses = ['paid', 'cod'];

        $paidTx = Transaction::query()
            ->where('event_id', $event->id)
            ->whereIn('payment_status', $paidStatuses);

        $pendingTx = Transaction::query()
            ->where('event_id', $event->id)
            ->where('payment_status', 'pending');

        $paidAgg = $paidTx
            ->selectRaw('COUNT(*) as tx_count')
            ->selectRaw('COALESCE(SUM(total_original), 0) as total_original')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(admin_fee), 0) as admin_fee')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as final_amount')
            ->selectRaw('COALESCE(SUM(unique_code), 0) as unique_code')
            ->first();

        $pendingAgg = $pendingTx
            ->selectRaw('COUNT(*) as tx_count')
            ->selectRaw('COALESCE(SUM(total_original), 0) as total_original')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(admin_fee), 0) as admin_fee')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as final_amount')
            ->selectRaw('COALESCE(SUM(unique_code), 0) as unique_code')
            ->first();

        $paidParticipants = Participant::query()
            ->whereHas('transaction', function ($q) use ($event, $paidStatuses) {
                $q->where('event_id', $event->id)->whereIn('payment_status', $paidStatuses);
            })
            ->count();

        $pendingParticipants = Participant::query()
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id)->where('payment_status', 'pending');
            })
            ->count();

        $accruedToEo = max(0, (float) $paidAgg->final_amount - (float) $paidAgg->admin_fee);

        $payouts = collect();
        $settled = 0.0;
        if (Schema::hasTable('event_payouts')) {
            $payouts = EventPayout::query()
                ->where('event_id', $event->id)
                ->orderByDesc(DB::raw('COALESCE(paid_at, created_at)'))
                ->get();
            $settled = (float) $payouts->where('status', 'completed')->sum('amount');
        }
        $remaining = max(0, $accruedToEo - $settled);

        $couponTxAgg = Transaction::query()
            ->where('event_id', $event->id)
            ->whereIn('payment_status', $paidStatuses)
            ->groupBy('coupon_id')
            ->selectRaw('coupon_id, COUNT(*) as tx_count')
            ->selectRaw('COALESCE(SUM(total_original), 0) as total_original')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(admin_fee), 0) as admin_fee')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as final_amount')
            ->get()
            ->keyBy('coupon_id');

        $couponParticipantAgg = Participant::query()
            ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
            ->where('transactions.event_id', $event->id)
            ->whereIn('transactions.payment_status', $paidStatuses)
            ->groupBy('transactions.coupon_id')
            ->selectRaw('transactions.coupon_id as coupon_id, COUNT(participants.id) as participants_count')
            ->get()
            ->keyBy('coupon_id');

        $couponCodes = $couponTxAgg->keys()->filter()->values()->all();
        $coupons = Coupon::query()
            ->whereIn('id', $couponCodes)
            ->select(['id', 'code'])
            ->get()
            ->keyBy('id');

        $couponRows = $couponTxAgg->map(function ($txRow, $couponId) use ($couponParticipantAgg, $coupons) {
            $coupon = $couponId ? $coupons->get($couponId) : null;
            $participantRow = $couponParticipantAgg->get($couponId);
            $participantsCount = (int) ($participantRow ? $participantRow->participants_count : 0);
            $final = (float) $txRow->final_amount;
            $fee = (float) $txRow->admin_fee;

            return [
                'coupon_code' => $coupon ? $coupon->code : '-',
                'tx_count' => (int) $txRow->tx_count,
                'participants_count' => $participantsCount,
                'total_original' => (float) $txRow->total_original,
                'discount_amount' => (float) $txRow->discount_amount,
                'admin_fee' => $fee,
                'final_amount' => $final,
                'eo_amount' => max(0, $final - $fee),
            ];
        })->values()->all();

        $addonsSummary = [
            'total_amount' => 0.0,
            'by_name' => [],
        ];
        Participant::query()
            ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
            ->where('transactions.event_id', $event->id)
            ->whereIn('transactions.payment_status', $paidStatuses)
            ->select('participants.addons')
            ->chunk(500, function ($rows) use (&$addonsSummary) {
                foreach ($rows as $p) {
                    $addons = is_array($p->addons) ? $p->addons : [];
                    foreach ($addons as $a) {
                        if (is_array($a)) {
                            $price = (float) ($a['price'] ?? $a['value'] ?? 0);
                            $name = (string) ($a['name'] ?? 'Unknown');
                            $addonsSummary['total_amount'] += $price;
                            if (! isset($addonsSummary['by_name'][$name])) {
                                $addonsSummary['by_name'][$name] = [
                                    'count' => 0,
                                    'total_amount' => 0.0
                                ];
                            }
                            $addonsSummary['by_name'][$name]['count']++;
                            $addonsSummary['by_name'][$name]['total_amount'] += $price;
                        }
                    }
                }
            });

        uasort($addonsSummary['by_name'], function ($a, $b) {
            return $b['total_amount'] <=> $a['total_amount'];
        });

        usort($couponRows, function ($a, $b) {
            return $b['participants_count'] <=> $a['participants_count'];
        });

        return view('admin.reports.event-finance.show', [
            'event' => $event,
            'paid' => [
                'tx_count' => (int) $paidAgg->tx_count,
                'participants_count' => (int) $paidParticipants,
                'total_original' => (float) $paidAgg->total_original,
                'discount_amount' => (float) $paidAgg->discount_amount,
                'admin_fee' => (float) $paidAgg->admin_fee,
                'final_amount' => (float) $paidAgg->final_amount,
                'unique_code' => (float) $paidAgg->unique_code,
                'eo_amount' => $accruedToEo,
            ],
            'pending' => [
                'tx_count' => (int) $pendingAgg->tx_count,
                'participants_count' => (int) $pendingParticipants,
                'final_amount' => (float) $pendingAgg->final_amount,
            ],
            'payouts' => $payouts,
            'settled_amount' => $settled,
            'remaining_amount' => $remaining,
            'coupon_rows' => $couponRows,
            'addons' => $addonsSummary,
        ]);
    }

    public function storePayout(Request $request, Event $event)
    {
        if (! Schema::hasTable('event_payouts')) {
            abort(503, 'event_payouts table not found. Please run migrations.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'participants_count' => 'nullable|integer|min:1|max:1000000',
            'paid_at' => 'nullable|date',
            'method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ]);

        EventPayout::create([
            'event_id' => $event->id,
            'created_by' => $request->user()->id,
            'amount' => $validated['amount'],
            'participants_count' => $validated['participants_count'] ?? null,
            'paid_at' => $validated['paid_at'] ?? now(),
            'method' => $validated['method'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'completed',
        ]);

        return redirect()
            ->route('admin.reports.event-finance.show', $event)
            ->with('success', 'Payout berhasil dicatat.');
    }

    public function destroyPayout(EventPayout $payout)
    {
        if (! Schema::hasTable('event_payouts')) {
            abort(503, 'event_payouts table not found. Please run migrations.');
        }

        $payout->update(['status' => 'cancelled']);

        return redirect()
            ->route('admin.reports.event-finance.show', $payout->event_id)
            ->with('success', 'Payout dibatalkan.');
    }
}
