@if($event->template === 'light-clean')
    @include('events.themes.light-clean')
@elseif($event->template === 'simple-minimal')
    @include('events.themes.simple-minimal')
@else
    @include('events.themes.modern-dark')
@endif
