<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EoReportEmailDelivery;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filtersFromRequest($request);

        $events = Event::query()
            ->orderByDesc('id')
            ->get(['id', 'name']);

        $deliveries = $this->baseQuery($filters)
            ->paginate(25)
            ->withQueryString();

        return view('admin.email-reports.index', [
            'events' => $events,
            'filters' => $filters,
            'deliveries' => $deliveries,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->filtersFromRequest($request);
        $deliveries = $this->baseQuery($filters)->get();

        $filename = 'email-reports-'.now()->format('Ymd-His').'.csv';

        return response()->stream(function () use ($deliveries) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, [
                'created_at',
                'event',
                'eo',
                'to_email',
                'subject',
                'status',
                'attempts',
                'first_attempt_at',
                'last_attempt_at',
                'sent_at',
                'failure_code',
                'failure_message',
            ]);

            foreach ($deliveries as $d) {
                fputcsv($out, [
                    optional($d->created_at)->toDateTimeString(),
                    $d->event?->name,
                    $d->eoUser?->name,
                    $d->to_email,
                    $d->subject,
                    $d->status,
                    (int) $d->attempts,
                    optional($d->first_attempt_at)->toDateTimeString(),
                    optional($d->last_attempt_at)->toDateTimeString(),
                    optional($d->sent_at)->toDateTimeString(),
                    $d->failure_code,
                    $d->failure_message ? Str::replace(["\r", "\n"], ' ', $d->failure_message) : null,
                ]);
            }

            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function print(Request $request)
    {
        $filters = $this->filtersFromRequest($request);
        $deliveries = $this->baseQuery($filters)->limit(2000)->get();

        return view('admin.email-reports.print', [
            'filters' => $filters,
            'deliveries' => $deliveries,
        ]);
    }

    private function baseQuery(array $filters)
    {
        $query = EoReportEmailDelivery::query()
            ->with(['event', 'eoUser'])
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

