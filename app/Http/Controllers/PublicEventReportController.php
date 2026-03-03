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
        ]);

        $filters = [
            'search' => trim((string) ($validated['search'] ?? '')),
            'payment_status' => (string) ($validated['payment_status'] ?? 'all'),
            'start_date' => (string) ($validated['start_date'] ?? ''),
            'end_date' => (string) ($validated['end_date'] ?? ''),
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'per_page' => (int) ($validated['per_page'] ?? 25),
            'page' => (int) ($validated['page'] ?? 1),
        ];

        if ($filters['category_id'] && ! $eventModel->categories->contains('id', $filters['category_id'])) {
            $filters['category_id'] = null;
        }

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
                    'participants.created_at',
                    'participants.target_time',
                    'participants.isApproved',
                    'participants.photo',
                    'transactions.payment_status',
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
                    'filters' => [
                        'payment_status' => $filters['payment_status'],
                        'start_date' => $filters['start_date'] ?: null,
                        'end_date' => $filters['end_date'] ?: null,
                        'category_id' => $filters['category_id'],
                        'search' => $filters['search'] ?: null,
                        'per_page' => $filters['per_page'],
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
}
