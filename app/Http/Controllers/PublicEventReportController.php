<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Transaction;
use App\Services\EventReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PublicEventReportController extends Controller
{
    public function show(Request $request, EventReportService $reportService, $event)
    {
        $sessionKey = 'report_access_' . $event;

        // 1. Check strict signature (validates full URL)
        if ($request->hasValidSignature()) {
            session([$sessionKey => true]);
        } 
        // 2. Check lenient signature (validates base URL without extra params)
        elseif ($request->has('signature')) {
            $queryParams = $request->query();
            $allowedParams = ['signature', 'expires'];
            $filteredParams = array_intersect_key($queryParams, array_flip($allowedParams));
            
            // Reconstruct the URL properly
            $checkUrl = $request->url();
            if (!empty($filteredParams)) {
                $checkUrl .= '?' . http_build_query($filteredParams);
            }
            
            // Create a temporary request to validate the signature
            $tempRequest = Request::create($checkUrl);
            
            if ($tempRequest->hasValidSignature()) {
                session([$sessionKey => true]);
            }
        }

        // 3. Final session check
        if (! session($sessionKey)) {
            abort(403, 'Invalid signature or session expired.');
        }

        $eventModel = Event::query()
            ->whereKey($event)
            ->where('is_active', true)
            ->where('status', 'published')
            ->with(['categories' => function ($q) {
                $q->where('is_active', true);
            }])
            ->firstOrFail();

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'payment_status' => ['nullable', 'in:all,paid,settlement,capture,pending,failed,cancel,expire,deny'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'category_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sales_group' => ['nullable', 'in:day,month'],
            'sales_start_date' => ['nullable', 'date'],
            'sales_end_date' => ['nullable', 'date'],
        ]);

        $filters = [
            'search' => trim((string) ($validated['search'] ?? '')),
            'payment_status' => (string) ($validated['payment_status'] ?? 'all'),
            'start_date' => (string) ($validated['start_date'] ?? ''),
            'end_date' => (string) ($validated['end_date'] ?? ''),
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'per_page' => (int) ($validated['per_page'] ?? 25),
            'page' => (int) ($validated['page'] ?? 1),
            'sales_group' => (string) ($validated['sales_group'] ?? 'day'),
            'sales_start_date' => (string) ($validated['sales_start_date'] ?? ''),
            'sales_end_date' => (string) ($validated['sales_end_date'] ?? ''),
        ];

        if ($filters['category_id'] && ! $eventModel->categories->contains('id', $filters['category_id'])) {
            $filters['category_id'] = null;
        }

        $salesStart = $filters['sales_start_date'] ?: ($filters['start_date'] ?: '');
        $salesEnd = $filters['sales_end_date'] ?: ($filters['end_date'] ?: '');
        if ($salesStart === '' && $salesEnd === '') {
            $salesEnd = now()->toDateString();
            $salesStart = now()->subDays(29)->toDateString();
        } elseif ($salesStart !== '' && $salesEnd === '') {
            $salesEnd = now()->toDateString();
        } elseif ($salesStart === '' && $salesEnd !== '') {
            $salesStart = \Carbon\Carbon::parse($salesEnd)->subDays(29)->toDateString();
        }

        try {
            $salesStartCarbon = \Carbon\Carbon::parse($salesStart)->startOfDay();
            $salesEndCarbon = \Carbon\Carbon::parse($salesEnd)->endOfDay();
            if ($salesEndCarbon->lt($salesStartCarbon)) {
                [$salesStartCarbon, $salesEndCarbon] = [$salesEndCarbon->startOfDay(), $salesStartCarbon->endOfDay()];
            }
            if ($salesEndCarbon->diffInDays($salesStartCarbon) > 366) {
                $salesStartCarbon = $salesEndCarbon->copy()->subDays(366)->startOfDay();
            }
            $salesStart = $salesStartCarbon->toDateString();
            $salesEnd = $salesEndCarbon->toDateString();
        } catch (\Exception $e) {
            $salesEnd = now()->toDateString();
            $salesStart = now()->subDays(29)->toDateString();
        }

        $filters['sales_start_date'] = $salesStart;
        $filters['sales_end_date'] = $salesEnd;

        $cacheKey = 'public_event_report_'.$eventModel->id.'_'.md5(json_encode($filters));

        $payload = Cache::remember($cacheKey, 30, function () use ($eventModel, $filters, $reportService) {
            $participantsQuery = Participant::query()
                ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
                ->leftJoin('coupons', 'coupons.id', '=', 'transactions.coupon_id')
                ->where('transactions.event_id', $eventModel->id)
                ->select([
                    'participants.id',
                    'participants.name',
                    'participants.email',
                    'participants.phone',
                    'participants.created_at',
                    'participants.target_time',
                    'participants.isApproved',
                    'participants.photo',
                    'participants.jersey_size',
                    'participants.addons',
                    'transactions.payment_status',
                    'transactions.final_amount',
                    'transactions.discount_amount',
                    DB::raw('COALESCE(coupons.code, "") as coupon_code'),
                ]);

            if (! empty($filters['category_id'])) {
                $participantsQuery->where('participants.race_category_id', $filters['category_id']);
            }

            if (! empty($filters['search'])) {
                $s = $filters['search'];
                $participantsQuery->where(function ($q) use ($s) {
                    $q->where('participants.name', 'like', "%{$s}%")
                        ->orWhere('participants.email', 'like', "%{$s}%");
                });
            }

            if (! empty($filters['start_date'])) {
                $participantsQuery->whereDate('participants.created_at', '>=', $filters['start_date']);
            }

            if (! empty($filters['end_date'])) {
                $participantsQuery->whereDate('participants.created_at', '<=', $filters['end_date']);
            }

            if (! empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
                $participantsQuery->where('transactions.payment_status', $filters['payment_status']);
            }

            $participants = $participantsQuery
                ->orderByDesc('participants.created_at')
                ->paginate($filters['per_page'])
                ->withQueryString();

            $reportFilters = [
                'start_date' => $filters['start_date'] ?: null,
                'end_date' => $filters['end_date'] ?: null,
            ];
            $report = $reportService->getEventReport($eventModel, $reportFilters);

            $couponUsageQuery = Transaction::query()
                ->join('coupons', 'coupons.id', '=', 'transactions.coupon_id')
                ->where('transactions.event_id', $eventModel->id)
                ->whereNotNull('transactions.coupon_id')
                ->whereIn('transactions.payment_status', ['paid', 'settlement', 'capture', 'pending'])
                ->select([
                    'coupons.code',
                    DB::raw('count(*) as total_transactions'),
                    DB::raw('sum(transactions.discount_amount) as total_discount'),
                ])
                ->groupBy('coupons.code')
                ->orderByDesc('total_transactions');

            if (! empty($filters['start_date'])) {
                $couponUsageQuery->whereDate('transactions.created_at', '>=', $filters['start_date']);
            }

            if (! empty($filters['end_date'])) {
                $couponUsageQuery->whereDate('transactions.created_at', '<=', $filters['end_date']);
            }

            $couponUsage = $couponUsageQuery->get();

            return [
                'report' => $report,
                'coupon_usage' => $couponUsage,
                'participants' => $participants,
                'sales' => $this->buildSalesSeries($eventModel->id, $filters),
            ];
        });

        $noIndexHeader = 'noindex, nofollow, noarchive';

        if ($request->expectsJson()) {
            return response()
                ->json([
                    'event' => [
                        'id' => $eventModel->id,
                        'name' => $eventModel->name,
                        'slug' => $eventModel->slug,
                    ],
                    'report' => $payload['report'],
                    'coupon_usage' => $payload['coupon_usage'],
                    'participants' => $payload['participants'],
                    'sales' => $payload['sales'],
                    'filters' => [
                        'payment_status' => $filters['payment_status'],
                        'start_date' => $filters['start_date'] ?: null,
                        'end_date' => $filters['end_date'] ?: null,
                        'category_id' => $filters['category_id'],
                        'search' => $filters['search'] ?: null,
                        'per_page' => $filters['per_page'],
                        'sales_group' => $filters['sales_group'],
                        'sales_start_date' => $filters['sales_start_date'] ?: null,
                        'sales_end_date' => $filters['sales_end_date'] ?: null,
                    ],
                ])
                ->header('X-Robots-Tag', $noIndexHeader);
        }

        return response()
            ->view('reports.event', [
                'event' => $eventModel,
                'report' => $payload['report'],
                'couponUsage' => $payload['coupon_usage'],
                'participants' => $payload['participants'],
                'sales' => $payload['sales'],
                'filters' => $filters,
            ])
            ->header('X-Robots-Tag', $noIndexHeader);
    }

    public function updateParticipant(Request $request, $event, $participantId)
    {
        // Check session access
        if (! session('report_access_' . $event)) {
            abort(403, 'Unauthorized action.');
        }

        $eventModel = Event::query()
            ->whereKey($event)
            ->firstOrFail();

        $participant = Participant::whereHas('transaction', function ($q) use ($eventModel) {
            $q->where('event_id', $eventModel->id);
        })->findOrFail($participantId);

        $validated = $request->validate([
            'isApproved' => ['required', 'boolean'],
            'target_time' => ['nullable', 'string', 'max:50'],
            'photo' => ['nullable', 'image', 'max:5120'], // Max 5MB
        ]);

        $participant->isApproved = (bool) $validated['isApproved'];
        
        if ($request->has('target_time')) {
            $participant->target_time = $validated['target_time'];
        }

        if ($request->hasFile('photo')) {
            if ($participant->photo && Storage::disk('public')->exists($participant->photo)) {
                Storage::disk('public')->delete($participant->photo);
            }
            $path = $request->file('photo')->store('participants', 'public');
            $participant->photo = $path;
        }

        $participant->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Peserta berhasil diperbarui.']);
        }
        
        return back()->with('success', 'Peserta berhasil diperbarui.');
    }

    private function buildSalesSeries(int $eventId, array $filters): array
    {
        $group = ($filters['sales_group'] ?? 'day') === 'month' ? 'month' : 'day';
        $start = (string) ($filters['sales_start_date'] ?? '');
        $end = (string) ($filters['sales_end_date'] ?? '');

        $startCarbon = $start ? \Carbon\Carbon::parse($start)->startOfDay() : now()->subDays(29)->startOfDay();
        $endCarbon = $end ? \Carbon\Carbon::parse($end)->endOfDay() : now()->endOfDay();
        if ($endCarbon->lt($startCarbon)) {
            [$startCarbon, $endCarbon] = [$endCarbon->startOfDay(), $startCarbon->endOfDay()];
        }

        $paidStatuses = ['paid', 'settlement', 'capture'];
        $pendingStatuses = ['pending'];

        $bucketExpr = $group === 'month'
            ? DB::raw("DATE_FORMAT(participants.created_at, '%Y-%m') as bucket")
            : DB::raw('DATE(participants.created_at) as bucket');

        $rows = Participant::query()
            ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
            ->where('transactions.event_id', $eventId)
            ->whereBetween('participants.created_at', [$startCarbon, $endCarbon])
            ->select([
                $bucketExpr,
                DB::raw('SUM(CASE WHEN transactions.payment_status IN ("'.implode('","', $paidStatuses).'") THEN 1 ELSE 0 END) as paid_slots'),
                DB::raw('SUM(CASE WHEN transactions.payment_status IN ("'.implode('","', $pendingStatuses).'") THEN 1 ELSE 0 END) as pending_slots'),
                DB::raw('COUNT(*) as total_slots'),
            ])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $key = (string) ($r->bucket ?? '');
            if ($key === '') {
                continue;
            }
            $map[$key] = [
                'paid' => (int) ($r->paid_slots ?? 0),
                'pending' => (int) ($r->pending_slots ?? 0),
                'total' => (int) ($r->total_slots ?? 0),
            ];
        }

        $labels = [];
        if ($group === 'month') {
            $cursor = $startCarbon->copy()->startOfMonth();
            $last = $endCarbon->copy()->startOfMonth();
            while ($cursor->lte($last)) {
                $labels[] = $cursor->format('Y-m');
                $cursor->addMonthNoOverflow();
            }
        } else {
            $cursor = $startCarbon->copy()->startOfDay();
            $last = $endCarbon->copy()->startOfDay();
            while ($cursor->lte($last)) {
                $labels[] = $cursor->toDateString();
                $cursor->addDay();
            }
        }

        $paid = [];
        $pending = [];
        $total = [];
        $cumulativePaid = [];

        $sumPaid = 0;
        $sumPending = 0;
        $runningPaid = 0;

        foreach ($labels as $label) {
            $v = $map[$label] ?? ['paid' => 0, 'pending' => 0, 'total' => 0];
            $paid[] = (int) $v['paid'];
            $pending[] = (int) $v['pending'];
            $total[] = (int) $v['total'];

            $sumPaid += (int) $v['paid'];
            $sumPending += (int) $v['pending'];
            $runningPaid += (int) $v['paid'];
            $cumulativePaid[] = $runningPaid;
        }

        return [
            'group' => $group,
            'start_date' => $startCarbon->toDateString(),
            'end_date' => $endCarbon->toDateString(),
            'labels' => $labels,
            'series' => [
                'paid' => $paid,
                'pending' => $pending,
                'total' => $total,
                'cumulative_paid' => $cumulativePaid,
            ],
            'totals' => [
                'paid' => $sumPaid,
                'pending' => $sumPending,
                'total' => $sumPaid + $sumPending,
            ],
        ];
    }

    public function exportParticipants(Request $request, $event)
    {
        if (! session('report_access_' . $event)) {
            abort(403, 'Unauthorized action.');
        }

        $eventModel = Event::query()
            ->whereKey($event)
            ->firstOrFail();

        $query = Participant::whereHas('transaction', function ($q) use ($eventModel, $request) {
            $q->where('event_id', $eventModel->id);
            if ($request->has('payment_status') && $request->payment_status && $request->payment_status !== 'all') {
                $q->where('payment_status', $request->payment_status);
            }
        })
        ->with(['transaction', 'category']);

        if ($request->has('category_id') && $request->category_id) {
            $query->where('race_category_id', $request->category_id);
        }

        if ($request->has('search') && trim($request->search) !== '') {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $filename = 'participants_'.$eventModel->slug.'_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $queryForStream = clone $query;

        $callback = function () use ($queryForStream) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, \App\Services\GoogleSheetsParticipantExporter::OUTPUT_COLUMNS);

            // Data
            $rowNumber = 0;
            $queryForStream->orderBy('id')->chunkById(1000, function ($participants) use ($file, &$rowNumber) {
                foreach ($participants as $participant) {
                    $rowNumber++;
                    fputcsv($file, [
                        $rowNumber,
                        $participant->name,
                        ucfirst($participant->gender ?? '-'),
                        $participant->email,
                        $participant->phone,
                        $participant->id_card,
                        $participant->address ?? '-',
                        $participant->category ? $participant->category->name : '-',
                        $participant->bib_number ?? '-',
                        $participant->jersey_size ?? '-',
                        (! empty($participant->addons) && is_array($participant->addons)) ? collect($participant->addons)->pluck('name')->filter()->implode(', ') : '-',
                        $participant->target_time ?? '-',
                        ucfirst($participant->transaction->payment_status ?? 'pending'),
                        $participant->is_picked_up ? 'Sudah Diambil' : 'Belum Diambil',
                        $participant->created_at ? $participant->created_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_at ? $participant->picked_up_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_by ?? '-',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportParticipantsXlsx(Request $request, $event)
    {
        if (! session('report_access_' . $event)) {
            abort(403, 'Unauthorized action.');
        }

        $eventModel = Event::query()
            ->whereKey($event)
            ->firstOrFail();

        $query = Participant::whereHas('transaction', function ($q) use ($eventModel, $request) {
            $q->where('event_id', $eventModel->id);
            if ($request->has('payment_status') && $request->payment_status && $request->payment_status !== 'all') {
                $q->where('payment_status', $request->payment_status);
            }
        })
        ->with(['transaction', 'category']);

        if ($request->has('category_id') && $request->category_id) {
            $query->where('race_category_id', $request->category_id);
        }

        if ($request->has('search') && trim($request->search) !== '') {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $filename = 'participants_'.$eventModel->slug.'_'.date('Y-m-d').'.xlsx';

        $queryForStream = clone $query;

        return response()->streamDownload(function () use ($queryForStream) {
            $writer = new \OpenSpout\Writer\XLSX\Writer;
            $writer->openToFile('php://output');

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(\App\Services\GoogleSheetsParticipantExporter::OUTPUT_COLUMNS));

            $rowNumber = 0;
            $queryForStream->orderBy('id')->chunkById(1000, function ($participants) use (&$rowNumber, $writer) {
                $rows = [];
                foreach ($participants as $participant) {
                    $rowNumber++;
                    $rows[] = \OpenSpout\Common\Entity\Row::fromValues([
                        $rowNumber,
                        $participant->name,
                        ucfirst($participant->gender ?? '-'),
                        $participant->email,
                        $participant->phone,
                        $participant->id_card,
                        $participant->address ?? '-',
                        $participant->category ? $participant->category->name : '-',
                        $participant->bib_number ?? '-',
                        $participant->jersey_size ?? '-',
                        (! empty($participant->addons) && is_array($participant->addons)) ? collect($participant->addons)->pluck('name')->filter()->implode(', ') : '-',
                        $participant->target_time ?? '-',
                        ucfirst($participant->transaction->payment_status ?? 'pending'),
                        $participant->is_picked_up ? 'Sudah Diambil' : 'Belum Diambil',
                        $participant->created_at ? $participant->created_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_at ? $participant->picked_up_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_by ?? '-',
                    ]);
                }
                if ($rows) {
                    $writer->addRows($rows);
                }
            });

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function updateParticipantStatus(Request $request, $event, Participant $participant)
    {
        if (! session('report_access_' . $event)) {
            abort(403, 'Unauthorized action.');
        }

        $eventModel = Event::query()
            ->whereKey($event)
            ->firstOrFail();

        // Verify participant belongs to this event
        $participantEventId = (int) ($participant?->transaction?->event_id ?? 0);
        if ($participantEventId !== (int) $eventModel->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'is_picked_up' => 'required|boolean',
            'picked_up_by' => 'nullable|string|max:255',
        ]);

        $wasPickedUp = (bool) $participant->is_picked_up;
        $isPickedUp = (bool) $validated['is_picked_up'];
        if ($isPickedUp) {
            $paymentStatus = (string) ($participant->transaction->payment_status ?? '');
            if (! in_array($paymentStatus, ['paid', 'cod'], true)) {
                $message = 'Tidak bisa pickup: status pembayaran belum paid.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }
                return back()->with('error', $message);
            }
        }

        $participant->update([
            'is_picked_up' => $isPickedUp,
            'picked_up_at' => $isPickedUp ? now() : null,
            'picked_up_by' => $isPickedUp ? (($validated['picked_up_by'] ?? null) ?: 'Public Report') : null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pengambilan berhasil diperbarui',
                'pickup_changed' => $wasPickedUp !== $isPickedUp,
                'participant' => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'bib_number' => $participant->bib_number,
                    'jersey_size' => $participant->jersey_size,
                    'is_picked_up' => (bool) $participant->is_picked_up,
                    'picked_up_at' => $participant->picked_up_at ? $participant->picked_up_at->format('Y-m-d H:i:s') : null,
                    'picked_up_by' => $participant->picked_up_by,
                    'payment_status' => $participant->transaction->payment_status ?? 'pending',
                ],
                'jersey_sizes_pending_pickup' => $this->getJerseyPendingPickupCounts($eventModel),
            ]);
        }

        return back()->with('success', 'Status pengambilan berhasil diperbarui');
    }

    private function getJerseyPendingPickupCounts(Event $event): array
    {
        $soldStatuses = ['paid', 'settlement', 'capture', 'cod'];

        $raw = Participant::query()
            ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
            ->where('transactions.event_id', $event->id)
            ->whereIn('transactions.payment_status', $soldStatuses)
            ->where('participants.is_picked_up', false)
            ->whereNotNull('participants.jersey_size')
            ->where('participants.jersey_size', '!=', '')
            ->selectRaw('UPPER(participants.jersey_size) as jersey_size, COUNT(*) as total')
            ->groupBy('jersey_size')
            ->pluck('total', 'jersey_size')
            ->toArray();

        $normalized = [];
        foreach ($raw as $size => $total) {
            $key = strtoupper(trim((string) $size));
            if ($key === 'XXL') {
                $key = '2XL';
            } elseif ($key === 'XXXL') {
                $key = '3XL';
            }
            $normalized[$key] = (int) ($normalized[$key] ?? 0) + (int) $total;
        }

        return $normalized;
    }
}
