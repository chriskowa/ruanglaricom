<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Email Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        .meta { font-size: 12px; color: #374151; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-weight: 700; font-size: 11px; }
        .b-sent { background: #d1fae5; color: #065f46; }
        .b-failed { background: #fee2e2; color: #991b1b; }
        .b-pending { background: #fef3c7; color: #92400e; }
        .b-processing { background: #dbeafe; color: #1e40af; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px;">
        <button onclick="window.print()">Print / Save PDF</button>
    </div>

    <h1>Email Report</h1>
    <div class="meta">
        Generated: {{ now()->format('Y-m-d H:i') }}
        @if(($filters['event_id'] ?? null))
            | Event ID: {{ $filters['event_id'] }}
        @endif
        @if(($filters['status'] ?? null))
            | Status: {{ $filters['status'] }}
        @endif
        @if(($filters['date_from'] ?? null))
            | Dari: {{ $filters['date_from'] }}
        @endif
        @if(($filters['date_to'] ?? null))
            | Sampai: {{ $filters['date_to'] }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Event</th>
                <th>EO</th>
                <th>Tujuan</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Attempt</th>
                <th>Sent</th>
                <th>Error</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveries as $d)
                <tr>
                    <td>{{ $d->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $d->event?->name }}</td>
                    <td>{{ $d->eoUser?->name }}</td>
                    <td>{{ $d->to_email }}</td>
                    <td>{{ $d->subject }}</td>
                    <td>
                        @php($s = (string) $d->status)
                        @if($s === 'sent')
                            <span class="badge b-sent">TERKIRIM</span>
                        @elseif($s === 'failed')
                            <span class="badge b-failed">GAGAL</span>
                        @elseif($s === 'processing')
                            <span class="badge b-processing">PROCESSING</span>
                        @else
                            <span class="badge b-pending">PENDING</span>
                        @endif
                    </td>
                    <td>{{ (int) $d->attempts }}</td>
                    <td>{{ $d->sent_at?->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($d->status === 'failed')
                            <div><strong>{{ $d->failure_code }}</strong></div>
                            <div>{{ $d->failure_message }}</div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        setTimeout(() => window.print(), 200);
    </script>
</body>
</html>

