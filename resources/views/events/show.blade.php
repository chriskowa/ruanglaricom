@if($event->template === 'light-clean')
    @include('events.themes.light-clean')
@elseif($event->template === 'simple-minimal')
    @include('events.themes.simple-minimal')
@elseif($event->template === 'professional-city-run' || $event->template === 'profesional-city-run')
    @include('events.themes.professional-city-run')
@elseif($event->template === 'paolo-fest')
    @include('events.themes.paolo-fest')
@elseif($event->template === 'paolo-fest-dark')
    @include('events.themes.paolo-fest-dark')
@else
    @include('events.themes.modern-dark')
@endif
