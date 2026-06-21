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

    private function calculateFinanceReport(Event $event)
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

        $accruedToEo = (float) $paidAgg->final_amount - (float) $paidAgg->admin_fee;

        $payouts = collect();
        $settled = 0.0;
        if (Schema::hasTable('event_payouts')) {
            $payouts = EventPayout::query()
                ->where('event_id', $event->id)
                ->orderByDesc(DB::raw('COALESCE(paid_at, created_at)'))
                ->get();
            $settled = (float) $payouts->where('status', 'completed')->sum('amount');
        }
        $remaining = $accruedToEo - $settled;

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
                'eo_amount' => $final - $fee,
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

        // 1. Online
        $onlineAgg = Transaction::query()
            ->where('event_id', $event->id)
            ->whereIn('payment_status', $paidStatuses)
            ->whereNotIn('payment_gateway', ['manual', 'manual_csv'])
            ->selectRaw('COUNT(*) as tx_count')
            ->selectRaw('COALESCE(SUM(total_original), 0) as total_original')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(admin_fee), 0) as admin_fee')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as final_amount')
            ->first();

        $onlineParticipantsCount = Participant::query()
            ->whereHas('transaction', function ($q) use ($event, $paidStatuses) {
                $q->where('event_id', $event->id)
                  ->whereIn('payment_status', $paidStatuses)
                  ->whereNotIn('payment_gateway', ['manual', 'manual_csv']);
            })
            ->count();

        // 2. Manual Input
        $manualAgg = Transaction::query()
            ->where('event_id', $event->id)
            ->whereIn('payment_status', $paidStatuses)
            ->where('payment_gateway', 'manual')
            ->selectRaw('COUNT(*) as tx_count')
            ->selectRaw('COALESCE(SUM(total_original), 0) as total_original')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(admin_fee), 0) as admin_fee')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as final_amount')
            ->first();

        $manualParticipantsCount = Participant::query()
            ->whereHas('transaction', function ($q) use ($event, $paidStatuses) {
                $q->where('event_id', $event->id)
                  ->whereIn('payment_status', $paidStatuses)
                  ->where('payment_gateway', 'manual');
            })
            ->count();

        // 3. Manual CSV
        $csvAgg = Transaction::query()
            ->where('event_id', $event->id)
            ->whereIn('payment_status', $paidStatuses)
            ->where('payment_gateway', 'manual_csv')
            ->selectRaw('COUNT(*) as tx_count')
            ->selectRaw('COALESCE(SUM(total_original), 0) as total_original')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_amount')
            ->selectRaw('COALESCE(SUM(admin_fee), 0) as admin_fee')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as final_amount')
            ->first();

        $csvParticipantsCount = Participant::query()
            ->whereHas('transaction', function ($q) use ($event, $paidStatuses) {
                $q->where('event_id', $event->id)
                  ->whereIn('payment_status', $paidStatuses)
                  ->where('payment_gateway', 'manual_csv');
            })
            ->count();

        $registrationBreakdown = [
            'online' => [
                'name' => 'Online (Payment Gateway)',
                'tx_count' => (int) $onlineAgg->tx_count,
                'participants_count' => $onlineParticipantsCount,
                'total_original' => (float) $onlineAgg->total_original,
                'discount_amount' => (float) $onlineAgg->discount_amount,
                'admin_fee' => (float) $onlineAgg->admin_fee,
                'final_amount' => (float) $onlineAgg->final_amount,
                'eo_amount' => (float) $onlineAgg->final_amount - (float) $onlineAgg->admin_fee,
            ],
            'manual' => [
                'name' => 'Input Manual (Admin/EO)',
                'tx_count' => (int) $manualAgg->tx_count,
                'participants_count' => $manualParticipantsCount,
                'total_original' => (float) $manualAgg->total_original,
                'discount_amount' => (float) $manualAgg->discount_amount,
                'admin_fee' => (float) $manualAgg->admin_fee,
                'final_amount' => (float) $manualAgg->final_amount,
                'eo_amount' => (float) $manualAgg->final_amount - (float) $manualAgg->admin_fee,
            ],
            'manual_csv' => [
                'name' => 'Import CSV (Manual CSV)',
                'tx_count' => (int) $csvAgg->tx_count,
                'participants_count' => $csvParticipantsCount,
                'total_original' => (float) $csvAgg->total_original,
                'discount_amount' => (float) $csvAgg->discount_amount,
                'admin_fee' => (float) $csvAgg->admin_fee,
                'final_amount' => (float) $csvAgg->final_amount,
                'eo_amount' => (float) $csvAgg->final_amount - (float) $csvAgg->admin_fee,
            ],
        ];

        usort($couponRows, function ($a, $b) {
            return $b['participants_count'] <=> $a['participants_count'];
        });

        return [
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
            'registration_breakdown' => $registrationBreakdown,
        ];
    }

    public function show(Event $event)
    {
        $data = $this->calculateFinanceReport($event);
        return view('admin.reports.event-finance.show', array_merge(['event' => $event], $data));
    }

    public function exportExcel(Event $event)
    {
        $data = $this->calculateFinanceReport($event);
        $filename = 'finance_report_' . $event->slug . '_' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($event, $data) {
            $writer = new \OpenSpout\Writer\XLSX\Writer;
            $writer->openToFile('php://output');

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'LAPORAN KEUANGAN EVENT: ' . strtoupper($event->name)
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Penyelenggara / EO: ' . ($event->user ? $event->user->name : '-')
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Tanggal Laporan: ' . date('d M Y H:i')
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([]));

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['RINGKASAN UTAMA']));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Parameter', 'Nilai'
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Hak EO (Accrued)', $data['paid']['eo_amount']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Sudah Dibayar (Payout)', $data['settled_amount']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Sisa Harus Dibayar', $data['remaining_amount']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Total Transaksi Lunas', $data['paid']['tx_count']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Total Peserta Lunas', $data['paid']['participants_count']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([]));

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['DETAIL TRANSAKSI LUNAS']));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Item', 'Nominal'
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Gross Revenue (Termasuk Addons)', $data['paid']['total_original']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Total Addons', $data['addons']['total_amount']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Net Tiket (Tanpa Addons)', $data['paid']['total_original'] - $data['addons']['total_amount']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Diskon Kupon', $data['paid']['discount_amount']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Platform Fee', $data['paid']['admin_fee']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Unique Code', $data['paid']['unique_code']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Total Dibayar Peserta', $data['paid']['final_amount']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([]));

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['TRANSAKSI PENDING']));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Jumlah Transaksi Pending', $data['pending']['tx_count']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Jumlah Peserta Pending', $data['pending']['participants_count']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Nominal Pending', $data['pending']['final_amount']
            ]));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([]));

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['BREAKDOWN ADDONS (LUNAS)']));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Nama Addon', 'Terjual (Qty)', 'Total Nominal'
            ]));
            foreach ($data['addons']['by_name'] as $name => $info) {
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    $name, $info['count'], $info['total_amount']
                ]));
            }
            if (empty($data['addons']['by_name'])) {
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['-', '-', 0]));
            }
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([]));

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['BREAKDOWN TIPE PENDAFTARAN (LUNAS)']));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Tipe Pendaftaran', 'Transaksi', 'Peserta', 'Gross', 'Diskon', 'Platform Fee', 'Hak EO'
            ]));
            foreach ($data['registration_breakdown'] as $r) {
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    $r['name'],
                    $r['tx_count'],
                    $r['participants_count'],
                    $r['total_original'],
                    $r['discount_amount'],
                    $r['admin_fee'],
                    $r['eo_amount']
                ]));
            }
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([]));

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['BREAKDOWN KUPON (LUNAS)']));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Kode Kupon', 'Transaksi', 'Peserta', 'Diskon', 'Fee', 'Hak EO'
            ]));
            foreach ($data['coupon_rows'] as $r) {
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    $r['coupon_code'],
                    $r['tx_count'],
                    $r['participants_count'],
                    $r['discount_amount'],
                    $r['admin_fee'],
                    $r['eo_amount']
                ]));
            }
            if (empty($data['coupon_rows'])) {
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['-', 0, 0, 0, 0, 0]));
            }
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([]));

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['RIWAYAT PAYOUT']));
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'Tanggal Payout', 'Metode', 'Peserta', 'Catatan', 'Nominal'
            ]));
            foreach ($data['payouts'] as $p) {
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    $p->paid_at ? $p->paid_at->format('Y-m-d H:i') : ($p->created_at ? $p->created_at->format('Y-m-d H:i') : '-'),
                    $p->method ?: '-',
                    $p->participants_count ?: '-',
                    $p->notes ?: '-',
                    $p->amount
                ]));
            }
            if ($data['payouts']->isEmpty()) {
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['-', '-', '-', '-', 0]));
            }

            $writer->close();
        }, $filename);
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

    public function updatePayout(Request $request, EventPayout $payout)
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

        $payout->update([
            'amount' => $validated['amount'],
            'participants_count' => $validated['participants_count'] ?? null,
            'paid_at' => $validated['paid_at'] ?? $payout->paid_at,
            'method' => $validated['method'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.reports.event-finance.show', $payout->event_id)
            ->with('success', 'Payout berhasil diperbarui.');
    }
}
