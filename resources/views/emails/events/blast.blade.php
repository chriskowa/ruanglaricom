<x-mail::message>
# Halo, {{ $participantName }}

{!! $content !!}

<x-mail::button :url="route('events.show', $event->slug)">
Lihat Detail Event
</x-mail::button>

Terima kasih,<br>
Tim {{ $event->name }}
</x-mail::message>
