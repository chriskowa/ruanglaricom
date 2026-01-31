<x-mail::message>
# Laporan Event: {{ $event->name ?? 'Event' }}

Halo {{ $delivery->eoUser->name ?? 'EO' }},

Laporan untuk event ini telah dibuat.

@if(!empty($delivery->filters))
**Filter**  
@foreach(($delivery->filters ?? []) as $k => $v)
- {{ $k }}: {{ is_array($v) ? json_encode($v) : $v }}
@endforeach
@endif

<x-mail::button :url="route('eo.events.participants', $event)">
Buka Dashboard Peserta
</x-mail::button>

Jika Anda tidak meminta laporan ini, abaikan email ini.

Terima kasih,  
RuangLari
</x-mail::message>

