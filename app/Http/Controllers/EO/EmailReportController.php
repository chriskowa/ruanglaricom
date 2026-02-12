<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Jobs\SendEoReportEmail;
use App\Models\EoReportEmailDelivery;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $eo = $request->user();

            $events = Event::query()
                ->where('user_id', $eo->id)
                ->orderByDesc('id')
                ->get(['id', 'name']);

            $query = $this->baseQuery($eo->id, $request);

            $deliveries = $query->paginate(20)->withQueryString();

            return view('eo.email-reports.index', [
                'events' => $events,
                'deliveries' => $deliveries,
                'filters' => $this->filtersFromRequest($request),
            ]);
        } catch (\Exception $e) {
            Log::error('EmailReportController::index error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Terjadi kesalahan saat memuat laporan email.');
        }
    }

    public function data(Request $request)
    {
        try {
            $eo = $request->user();

            $query = $this->baseQuery($eo->id, $request);

            $count = $query->count();

            // Debug logging
            Log::info('EmailReportController::data', [
                'eo_user_id' => $eo->id,
                'filters' => $this->filtersFromRequest($request),
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'count_before_limit' => $count,
            ]);

            $deliveries = $query
                ->limit(50)
                ->get()
                ->map(function (EoReportEmailDelivery $d) {
                    return [
                        'id' => $d->id,
                        'event_id' => $d->event_id,
                        'event_name' => $d->event ? $d->event->name : 'Event Deleted',
                        'to_email' => $d->to_email,
                        'subject' => $d->subject,
                        'status' => $d->status,
                        'attempts' => (int) $d->attempts,
                        'created_at' => $d->created_at?->toISOString(),
                        'first_attempt_at' => $d->first_attempt_at?->toISOString(),
                        'last_attempt_at' => $d->last_attempt_at?->toISOString(),
                        'sent_at' => $d->sent_at?->toISOString(),
                        'failure_code' => $d->failure_code,
                        'failure_message' => mb_convert_encoding($d->failure_message ?? '', 'UTF-8', 'UTF-8'),
                    ];
                });

            return response()->json([
                'ok' => true,
                'filters' => $this->filtersFromRequest($request),
                'deliveries' => $deliveries,
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Exception $e) {
            Log::error('EmailReportController::data error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Terjadi kesalahan saat memuat data laporan.',
            ], 500);
        }
    }

    public function send(Request $request)
    {
        $eo = $request->user();

        $validated = $request->validate([
            'event_id' => ['required', 'integer'],
            'to_email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $event = Event::query()
            ->where('id', $validated['event_id'])
            ->where('user_id', $eo->id)
            ->firstOrFail();

        $filters = array_filter([
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'status' => $validated['status'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        $subject = trim((string) ($validated['subject'] ?? ''));
        if ($subject === '') {
            $subject = 'Laporan Event: '.$event->name;
        }

        $delivery = EoReportEmailDelivery::create([
            'event_id' => $event->id,
            'eo_user_id' => $eo->id,
            'triggered_by_user_id' => $eo->id,
            'to_email' => $validated['to_email'],
            'to_name' => $eo->name,
            'subject' => $subject,
            'report_type' => 'event_report',
            'filters' => $filters,
            'queue' => 'emails-reports',
            'status' => 'pending',
        ]);

        SendEoReportEmail::dispatch($delivery->id)->onQueue('emails-reports');

        Log::info('EO report email enqueued', [
            'delivery_id' => $delivery->id,
            'event_id' => $event->id,
            'eo_user_id' => $eo->id,
            'to_email' => $delivery->to_email,
            'queue' => $delivery->queue,
        ]);

        return response()->json([
            'ok' => true,
            'delivery_id' => $delivery->id,
        ]);
    }

    public function resend(Request $request, EoReportEmailDelivery $delivery)
    {
        $eo = $request->user();

        if ((int) $delivery->eo_user_id !== (int) $eo->id) {
            abort(404);
        }

        $validated = $request->validate([
            'to_email' => ['nullable', 'email', 'max:255'],
        ]);

        $toEmail = $validated['to_email'] ?? $delivery->to_email;
        if (! filter_var((string) $toEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'ok' => false,
                'message' => 'Alamat email tidak valid.',
            ], 422);
        }

        $newDelivery = EoReportEmailDelivery::create([
            'event_id' => $delivery->event_id,
            'eo_user_id' => $delivery->eo_user_id,
            'triggered_by_user_id' => $eo->id,
            'to_email' => $toEmail,
            'to_name' => $delivery->to_name,
            'subject' => $delivery->subject,
            'report_type' => $delivery->report_type,
            'filters' => $delivery->filters,
            'queue' => 'emails-reports',
            'status' => 'pending',
        ]);

        SendEoReportEmail::dispatch($newDelivery->id)->onQueue('emails-reports');

        Log::info('EO report email resend enqueued', [
            'original_delivery_id' => $delivery->id,
            'new_delivery_id' => $newDelivery->id,
            'event_id' => $delivery->event_id,
            'eo_user_id' => $eo->id,
            'to_email' => $toEmail,
            'queue' => $newDelivery->queue,
        ]);

        return response()->json([
            'ok' => true,
            'delivery_id' => $newDelivery->id,
        ]);
    }

    private function baseQuery(int $eoUserId, Request $request)
    {
        $filters = $this->filtersFromRequest($request);

        $query = EoReportEmailDelivery::query()
            ->with('event')
            ->where('eo_user_id', $eoUserId)
            ->orderByDesc('id');

        if ($filters['event_id'] !== null) {
            $query->where('event_id', $filters['event_id']);
        }

        if ($filters['status'] !== null) {
            $query->where('status', $filters['status']);
        }

        if ($filters['date_from'] !== null) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== null) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    private function filtersFromRequest(Request $request): array
    {
        $eventId = $request->integer('event_id') ?: null;
        $status = trim((string) $request->query('status', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        return [
            'event_id' => $eventId,
            'status' => $status !== '' ? $status : null,
            'date_from' => $dateFrom !== '' ? $dateFrom : null,
            'date_to' => $dateTo !== '' ? $dateTo : null,
        ];
    }
}
