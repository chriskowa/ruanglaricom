@php
    $template = $event->ticket_email_template ?? 'modern';
    if (! in_array($template, ['modern', 'minimalist', 'sporty'])) {
        $template = 'modern';
    }
@endphp
@include('emails.events.templates.' . $template)
