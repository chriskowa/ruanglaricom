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
            'payment_status' => ['nullable', 'string', 'max:50'],
            'is_picked_up' => ['nullable', 'string', 'max:10'],
            'gender' => ['nullable', 'string', 'max:20'],
            'coupon_id' => ['nullable', 'string', 'max:50'],
            'addon' => ['nullable', 'string', 'max:100'],
            'jersey_size' => ['nullable', 'string', 'max:50'],
            'age_group' => ['nullable', 'string', 'max:100'],
            'min_age' => ['nullable', 'integer'],
            'max_age' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'category_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sales_group' => ['nullable', 'in:day,month'],
            'sales_start_date' => ['nullable', 'date'],
            'sales_end_date' => ['nullable', 'date'],
        ]);

        $filters = [
            'search' => trim((string) ($validated['search'] ?? '')),
            'payment_status' => (string) ($validated['payment_status'] ?? 'all'),
            'is_picked_up' => isset($validated['is_picked_up']) ? (string) $validated['is_picked_up'] : '',
            'gender' => (string) ($validated['gender'] ?? ''),
            'coupon_id' => (string) ($validated['coupon_id'] ?? ''),
            'addon' => (string) ($validated['addon'] ?? ''),
            'jersey_size' => (string) ($validated['jersey_size'] ?? ''),
            'age_group' => (string) ($validated['age_group'] ?? ''),
            'min_age' => isset($validated['min_age']) ? (int) $validated['min_age'] : null,
            'max_age' => isset($validated['max_age']) ? (int) $validated['max_age'] : null,
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
                ->with(['category', 'transaction.user'])
                ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
                ->leftJoin('coupons', 'coupons.id', '=', 'transactions.coupon_id')
                ->where('transactions.event_id', $eventModel->id)
                ->select([
                    'participants.*',
                    'transactions.payment_status',
                    'transactions.final_amount',
                    'transactions.discount_amount',
                    'transactions.payment_gateway',
                    'transactions.created_at as transaction_created_at',
                    'transactions.pic_data',
                    DB::raw('COALESCE(coupons.code, "") as coupon_code'),
                ]);

            if (! empty($filters['category_id'])) {
                $participantsQuery->where('participants.race_category_id', $filters['category_id']);
            }

            if (isset($filters['is_picked_up']) && $filters['is_picked_up'] !== '') {
                $participantsQuery->where('participants.is_picked_up', $filters['is_picked_up'] == '1');
            }

            if (! empty($filters['gender'])) {
                $participantsQuery->where('participants.gender', $filters['gender']);
            }

            if (! empty($filters['coupon_id'])) {
                $couponFilter = $filters['coupon_id'];
                if ($couponFilter === 'with') {
                    $participantsQuery->whereNotNull('transactions.coupon_id');
                } elseif ($couponFilter === 'without') {
                    $participantsQuery->whereNull('transactions.coupon_id');
                } else {
                    $participantsQuery->where('transactions.coupon_id', $couponFilter);
                }
            }

            if (! empty($filters['addon'])) {
                $addonFilter = $filters['addon'];
                if ($addonFilter === 'with') {
                    $participantsQuery->whereNotNull('participants.addons')->whereJsonLength('participants.addons', '>', 0);
                } elseif ($addonFilter === 'without') {
                    $participantsQuery->where(function ($q) {
                        $q->whereNull('participants.addons')->orWhereJsonLength('participants.addons', 0);
                    });
                } else {
                    $participantsQuery->whereJsonContains('participants.addons', ['name' => $addonFilter]);
                }
            }

            if (! empty($filters['jersey_size'])) {
                $jerseySizeFilter = $filters['jersey_size'];
                $matchSizes = [strtoupper($jerseySizeFilter)];
                if (in_array(strtoupper($jerseySizeFilter), ['2XL', 'XXL'])) {
                    $matchSizes = ['2XL', 'XXL'];
                } elseif (in_array(strtoupper($jerseySizeFilter), ['3XL', 'XXXL'])) {
                    $matchSizes = ['3XL', 'XXXL'];
                }
                $participantsQuery->whereNotNull('participants.jersey_size')
                    ->whereIn(\DB::raw('UPPER(TRIM(participants.jersey_size))'), $matchSizes);
            }

            if (! empty($filters['age_group'])) {
                $group = $filters['age_group'];
                $eventDate = $eventModel->start_at ?: now();
                if ($group === '50+') {
                    $participantsQuery->whereDate('participants.date_of_birth', '<=', $eventDate->copy()->subYears(50));
                } elseif ($group === 'Master 45+') {
                    $participantsQuery->whereDate('participants.date_of_birth', '<=', $eventDate->copy()->subYears(45))
                        ->whereDate('participants.date_of_birth', '>', $eventDate->copy()->subYears(50));
                } elseif ($group === 'Master') {
                    $participantsQuery->whereDate('participants.date_of_birth', '<=', $eventDate->copy()->subYears(40))
                        ->whereDate('participants.date_of_birth', '>', $eventDate->copy()->subYears(45));
                } elseif ($group === 'Umum') {
                    $participantsQuery->whereDate('participants.date_of_birth', '>', $eventDate->copy()->subYears(40));
                }
            }

            if ($filters['min_age'] !== null) {
                $minAge = (int) $filters['min_age'];
                $eventDate = $eventModel->start_at ?: now();
                $participantsQuery->whereDate('participants.date_of_birth', '<=', $eventDate->copy()->subYears($minAge));
            }

            if ($filters['max_age'] !== null) {
                $maxAge = (int) $filters['max_age'];
                $eventDate = $eventModel->start_at ?: now();
                $participantsQuery->whereDate('participants.date_of_birth', '>', $eventDate->copy()->subYears($maxAge + 1));
            }

            if (! empty($filters['search'])) {
                $search = $filters['search'];
                $participantsQuery->where(function ($qq) use ($search) {
                    $qq->where('participants.name', 'like', "%{$search}%")
                        ->orWhere('participants.email', 'like', "%{$search}%")
                        ->orWhere('participants.phone', 'like', "%{$search}%")
                        ->orWhere('participants.bib_number', 'like', "%{$search}%")
                        ->orWhere('participants.id_card', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
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

            $participants->getCollection()->each(function ($p) use ($eventModel) {
                $p->setAttribute('age_group', $p->getAgeGroup($eventModel->start_at));
                $p->setAttribute('category_name', $p->category ? $p->category->name : '-');
                $p->setAttribute('pic_name', $p->transaction->pic_data['name'] ?? ($p->transaction->user->name ?? '-'));
                $p->setAttribute('pic_phone', $p->transaction->pic_data['phone'] ?? ($p->transaction->user->phone ?? '-'));
                $p->setAttribute('pic_email', $p->transaction->pic_data['email'] ?? ($p->transaction->user->email ?? '-'));
                $p->setAttribute('transaction_date', $p->transaction_created_at ? \Carbon\Carbon::parse($p->transaction_created_at)->format('d M Y H:i') : '-');
                $p->setAttribute('payment_method', $p->payment_gateway ?? '-');
            });

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

            $couponReport = null;
            if (! empty($filters['coupon_id']) && $filters['coupon_id'] !== 'without') {
                $couponReportQuery = \App\Models\Participant::query()
                    ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
                    ->where('transactions.event_id', $eventModel->id)
                    ->whereIn('transactions.payment_status', ['paid', 'settlement', 'capture', 'cod']);

                $couponFilter = $filters['coupon_id'];
                if ($couponFilter === 'with') {
                    $couponReportQuery->whereNotNull('transactions.coupon_id');
                } else {
                    $couponReportQuery->where('transactions.coupon_id', $couponFilter);
                }

                if (! empty($filters['category_id'])) {
                    $couponReportQuery->where('participants.race_category_id', $filters['category_id']);
                }
                if (isset($filters['is_picked_up']) && $filters['is_picked_up'] !== '') {
                    $couponReportQuery->where('participants.is_picked_up', $filters['is_picked_up'] == '1');
                }
                if (! empty($filters['jersey_size'])) {
                    $jerseySizeFilter = $filters['jersey_size'];
                    $matchSizes = [strtoupper($jerseySizeFilter)];
                    if (in_array(strtoupper($jerseySizeFilter), ['2XL', 'XXL'])) {
                        $matchSizes = ['2XL', 'XXL'];
                    } elseif (in_array(strtoupper($jerseySizeFilter), ['3XL', 'XXXL'])) {
                        $matchSizes = ['3XL', 'XXXL'];
                    }
                    $couponReportQuery->whereNotNull('participants.jersey_size')
                        ->whereIn(\DB::raw('UPPER(TRIM(participants.jersey_size))'), $matchSizes);
                }
                if (! empty($filters['gender'])) {
                    $couponReportQuery->where('participants.gender', $filters['gender']);
                }
                if (! empty($filters['search'])) {
                    $search = $filters['search'];
                    $couponReportQuery->where(function ($qq) use ($search) {
                        $qq->where('participants.name', 'like', "%{$search}%")
                            ->orWhere('participants.email', 'like', "%{$search}%")
                            ->orWhere('participants.phone', 'like', "%{$search}%")
                            ->orWhere('participants.bib_number', 'like', "%{$search}%")
                            ->orWhere('participants.id_card', 'like', "%{$search}%");
                    });
                }

                $couponParticipants = $couponReportQuery
                    ->select([
                        'participants.id',
                        'participants.name',
                        'participants.bib_number',
                        'participants.jersey_size',
                        'participants.is_picked_up',
                    ])
                    ->orderBy('participants.name', 'asc')
                    ->get();

                $jerseyCounts = [];
                foreach ($couponParticipants as $cp) {
                    $size = strtoupper(trim((string) $cp->jersey_size));
                    if ($size === '') {
                        $size = 'NO SIZE';
                    }
                    if ($size === 'XXL') {
                        $size = '2XL';
                    } elseif ($size === 'XXXL') {
                        $size = '3XL';
                    }
                    $jerseyCounts[$size] = ($jerseyCounts[$size] ?? 0) + 1;
                }

                $standardOrder = ['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', 'NO SIZE'];
                uksort($jerseyCounts, function ($a, $b) use ($standardOrder) {
                    $posA = array_search($a, $standardOrder);
                    $posB = array_search($b, $standardOrder);
                    $posA = $posA === false ? 999 : $posA;
                    $posB = $posB === false ? 999 : $posB;
                    return $posA <=> $posB;
                });

                $couponReport = [
                    'jersey_totals' => $jerseyCounts,
                    'participants' => $couponParticipants->map(function ($cp) {
                        $bib = $cp->bib_number;
                        if ($bib && strpos($bib, '-') !== false) {
                            $parts = explode('-', $bib);
                            $bib = end($parts);
                        }
                        return [
                            'id' => $cp->id,
                            'name' => $cp->name,
                            'bib' => $bib ?: '-',
                            'jersey_size' => $cp->jersey_size ?: '-',
                            'is_picked_up' => (bool) $cp->is_picked_up,
                        ];
                    })->toArray(),
                ];
            }

            return [
                'report' => $report,
                'coupon_usage' => $couponUsage,
                'participants' => $participants,
                'sales' => $this->buildSalesSeries($eventModel->id, $filters),
                'coupon_report' => $couponReport,
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
                    'coupon_report' => $payload['coupon_report'] ?? null,
                    'filters' => [
                        'payment_status' => $filters['payment_status'],
                        'is_picked_up' => $filters['is_picked_up'],
                        'gender' => $filters['gender'] ?: null,
                        'coupon_id' => $filters['coupon_id'] ?: null,
                        'addon' => $filters['addon'] ?: null,
                        'jersey_size' => $filters['jersey_size'] ?: null,
                        'age_group' => $filters['age_group'] ?: null,
                        'min_age' => $filters['min_age'],
                        'max_age' => $filters['max_age'],
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

        $coupons = \App\Models\Coupon::where('event_id', $eventModel->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return response()
            ->view('reports.event', [
                'event' => $eventModel,
                'report' => $payload['report'],
                'couponUsage' => $payload['coupon_usage'],
                'participants' => $payload['participants'],
                'sales' => $payload['sales'],
                'filters' => $filters,
                'coupons' => $coupons,
                'couponReport' => $payload['coupon_report'] ?? null,
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

        $query = Participant::whereHas('transaction', function ($q) use ($eventModel) {
            $q->where('event_id', $eventModel->id);
        })
        ->with(['transaction.coupon', 'transaction.participants', 'category']);

        $this->applyParticipantFilters($query, $request, $eventModel);

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
            $queryForStream->join('transactions', 'participants.transaction_id', '=', 'transactions.id')
                ->leftJoin('coupons', 'transactions.coupon_id', '=', 'coupons.id')
                ->select('participants.*')
                ->orderBy('coupons.code', 'asc')
                ->orderBy('participants.created_at', 'asc')
                ->chunk(1000, function ($participants) use ($file, &$rowNumber) {
                foreach ($participants as $participant) {
                    $rowNumber++;

                    $priceType = $participant->price_type ?? 'regular';
                    $ticketPrice = 0;
                    if ($participant->category) {
                        if ($priceType === 'early' && isset($participant->category->price_early) && $participant->category->price_early > 0) {
                            $ticketPrice = (float) $participant->category->price_early;
                        } elseif ($priceType === 'late' && isset($participant->category->price_late) && $participant->category->price_late > 0) {
                            $ticketPrice = (float) $participant->category->price_late;
                        } else {
                            $ticketPrice = (float) ($participant->category->price_regular ?? 0);
                        }
                    }

                    $txParticipantsCount = $participant->transaction && $participant->transaction->participants ? $participant->transaction->participants->count() : 1;
                    $txParticipantsCount = max(1, $txParticipantsCount);

                    $couponDiscount = $participant->transaction ? ((float) $participant->transaction->discount_amount / $txParticipantsCount) : 0.0;
                    $platformFee = $participant->transaction ? ((float) $participant->transaction->admin_fee / $txParticipantsCount) : 0.0;

                    fputcsv($file, [
                        $rowNumber,
                        $participant->name,
                        ucfirst($participant->gender ?? '-'),
                        $participant->date_of_birth ? $participant->date_of_birth->format('Y-m-d') : '-',
                        $participant->email,
                        $participant->phone,
                        $participant->id_card,
                        $participant->address ?? '-',
                        $participant->category ? $participant->category->name : '-',
                        $participant->bib_number ?? '-',
                        $participant->jersey_size ?? '-',
                        $participant->blood_type ?? '-',
                        (! empty($participant->addons) && is_array($participant->addons)) ? collect($participant->addons)->pluck('name')->filter()->implode(', ') : '-',
                        $participant->target_time ?? '-',
                        $participant->transaction && $participant->transaction->coupon ? $participant->transaction->coupon->code : '-',
                        $couponDiscount,
                        $ticketPrice,
                        $platformFee,
                        $participant->emergency_contact_name ?? '-',
                        $participant->emergency_contact_number ?? '-',
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

        $query = Participant::whereHas('transaction', function ($q) use ($eventModel) {
            $q->where('event_id', $eventModel->id);
        })
        ->with(['transaction.coupon', 'transaction.participants', 'category']);

        $this->applyParticipantFilters($query, $request, $eventModel);

        $filename = 'participants_'.$eventModel->slug.'_'.date('Y-m-d').'.xlsx';

        $queryForStream = clone $query;

        return response()->streamDownload(function () use ($queryForStream) {
            $writer = new \OpenSpout\Writer\XLSX\Writer;
            $writer->openToFile('php://output');

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(\App\Services\GoogleSheetsParticipantExporter::OUTPUT_COLUMNS));

            $rowNumber = 0;
            $queryForStream->join('transactions', 'participants.transaction_id', '=', 'transactions.id')
                ->leftJoin('coupons', 'transactions.coupon_id', '=', 'coupons.id')
                ->select('participants.*')
                ->orderBy('coupons.code', 'asc')
                ->orderBy('participants.created_at', 'asc')
                ->chunk(1000, function ($participants) use (&$rowNumber, $writer) {
                $rows = [];
                foreach ($participants as $participant) {
                    $rowNumber++;

                    $priceType = $participant->price_type ?? 'regular';
                    $ticketPrice = 0;
                    if ($participant->category) {
                        if ($priceType === 'early' && isset($participant->category->price_early) && $participant->category->price_early > 0) {
                            $ticketPrice = (float) $participant->category->price_early;
                        } elseif ($priceType === 'late' && isset($participant->category->price_late) && $participant->category->price_late > 0) {
                            $ticketPrice = (float) $participant->category->price_late;
                        } else {
                            $ticketPrice = (float) ($participant->category->price_regular ?? 0);
                        }
                    }

                    $txParticipantsCount = $participant->transaction && $participant->transaction->participants ? $participant->transaction->participants->count() : 1;
                    $txParticipantsCount = max(1, $txParticipantsCount);

                    $couponDiscount = $participant->transaction ? ((float) $participant->transaction->discount_amount / $txParticipantsCount) : 0.0;
                    $platformFee = $participant->transaction ? ((float) $participant->transaction->admin_fee / $txParticipantsCount) : 0.0;

                    $rows[] = \OpenSpout\Common\Entity\Row::fromValues([
                        $rowNumber,
                        $participant->name,
                        ucfirst($participant->gender ?? '-'),
                        $participant->date_of_birth ? $participant->date_of_birth->format('Y-m-d') : '-',
                        $participant->email,
                        $participant->phone,
                        $participant->id_card,
                        $participant->address ?? '-',
                        $participant->category ? $participant->category->name : '-',
                        $participant->bib_number ?? '-',
                        $participant->jersey_size ?? '-',
                        $participant->blood_type ?? '-',
                        (! empty($participant->addons) && is_array($participant->addons)) ? collect($participant->addons)->pluck('name')->filter()->implode(', ') : '-',
                        $participant->target_time ?? '-',
                        $participant->transaction && $participant->transaction->coupon ? $participant->transaction->coupon->code : '-',
                        $couponDiscount,
                        $ticketPrice,
                        $platformFee,
                        $participant->emergency_contact_name ?? '-',
                        $participant->emergency_contact_number ?? '-',
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
                    'age_group' => $participant->getAgeGroup($eventModel->start_at),
                    'addons' => $participant->addons ?? [],
                ],
                'jersey_sizes_pending_pickup' => $this->getJerseyPendingPickupCounts($eventModel),
            ]);
        }

        return back()->with('success', 'Status pengambilan berhasil diperbarui');
    }

    private function getJerseyPendingPickupCounts(Event $event): array
    {
        $soldStatuses = ['paid'];

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

    private function applyParticipantFilters($query, Request $request, Event $eventModel)
    {
        if ($request->has('payment_status') && $request->payment_status && $request->payment_status !== 'all') {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->where('payment_status', $request->payment_status);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('race_category_id', $request->category_id);
        }

        if ($request->has('is_picked_up') && $request->is_picked_up !== '') {
            $query->where('is_picked_up', $request->is_picked_up == '1');
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('coupon_id')) {
            $couponFilter = $request->coupon_id;
            if ($couponFilter === 'with') {
                $query->whereHas('transaction', function ($q) {
                    $q->whereNotNull('coupon_id');
                });
            } elseif ($couponFilter === 'without') {
                $query->whereHas('transaction', function ($q) {
                    $q->whereNull('coupon_id');
                });
            } else {
                $query->whereHas('transaction', function ($q) use ($couponFilter) {
                    $q->where('coupon_id', $couponFilter);
                });
            }
        }

        if ($request->filled('addon')) {
            $addonFilter = $request->addon;
            if ($addonFilter === 'with') {
                $query->whereNotNull('addons')->whereJsonLength('addons', '>', 0);
            } elseif ($addonFilter === 'without') {
                $query->where(function ($q) {
                    $q->whereNull('addons')->orWhereJsonLength('addons', 0);
                });
            } else {
                $query->whereJsonContains('addons', ['name' => $addonFilter]);
            }
        }

        if ($request->filled('jersey_size')) {
            $jerseySizeFilter = $request->jersey_size;
            $matchSizes = [strtoupper($jerseySizeFilter)];
            if (in_array(strtoupper($jerseySizeFilter), ['2XL', 'XXL'])) {
                $matchSizes = ['2XL', 'XXL'];
            } elseif (in_array(strtoupper($jerseySizeFilter), ['3XL', 'XXXL'])) {
                $matchSizes = ['3XL', 'XXXL'];
            }
            $query->whereNotNull('jersey_size')->whereIn(\DB::raw('UPPER(TRIM(jersey_size))'), $matchSizes);
        }

        if ($request->filled('age_group')) {
            $group = $request->age_group;
            $eventDate = $eventModel->start_at ?: now();
            if ($group === '50+') {
                $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears(50));
            } elseif ($group === 'Master 45+') {
                $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears(45))
                    ->whereDate('date_of_birth', '>', $eventDate->copy()->subYears(50));
            } elseif ($group === 'Master') {
                $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears(40))
                    ->whereDate('date_of_birth', '>', $eventDate->copy()->subYears(45));
            } elseif ($group === 'Umum') {
                $query->whereDate('date_of_birth', '>', $eventDate->copy()->subYears(40));
            }
        }

        if ($request->filled('min_age')) {
            $minAge = (int) $request->min_age;
            $eventDate = $eventModel->start_at ?: now();
            $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears($minAge));
        }

        if ($request->filled('max_age')) {
            $maxAge = (int) $request->max_age;
            $eventDate = $eventModel->start_at ?: now();
            $query->whereDate('date_of_birth', '>', $eventDate->copy()->subYears($maxAge + 1));
        }

        if ($request->has('search') && trim($request->search) !== '') {
            $search = trim($request->search);
            $query->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('bib_number', 'like', "%{$search}%")
                    ->orWhere('id_card', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transaction', function ($t) use ($search) {
                        $t->where(function ($jt) use ($search) {
                            $jt->where('pic_data->name', 'like', "%{$search}%")
                                ->orWhere('pic_data->email', 'like', "%{$search}%")
                                ->orWhere('pic_data->phone', 'like', "%{$search}%")
                                ->orWhere('pic_data', 'like', "%{$search}%");
                        })->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return $query;
    }

    public function doorprizeList(Request $request, $event)
    {
        $sessionKey = 'report_access_' . $event;
        if (! session($sessionKey)) {
            abort(403, 'Unauthorized');
        }

        $eventModel = Event::query()->whereKey($event)->firstOrFail();
        
        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($eventModel) {
            $q->where('event_id', $eventModel->id)
              ->where('payment_status', 'paid');
        });

        $this->applyParticipantFilters($query, $request, $eventModel);

        $participants = $query->select(
            'participants.id',
            'participants.bib_number',
            'participants.name',
            'participants.phone',
            'participants.address',
            'participants.city',
            'participants.province'
        )
        ->orderBy('participants.bib_number')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $participants
        ]);
    }
}

