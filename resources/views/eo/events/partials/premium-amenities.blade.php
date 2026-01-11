@php
    $oldAmenities = old('premium_amenities', []);
    $dbAmenities = $event->premium_amenities ?? [];
    
    $isEnabled = function($key) use ($oldAmenities, $dbAmenities) {
        if (!empty($oldAmenities)) {
            return isset($oldAmenities[$key]['enabled']);
        }
        return isset($dbAmenities[$key]['enabled']) && $dbAmenities[$key]['enabled'];
    };
@endphp

<div x-data="{
    amenities: {
        jersey: {{ $isEnabled('jersey') ? 'true' : 'false' }},
        medal: {{ $isEnabled('medal') ? 'true' : 'false' }},
        faq: {{ $isEnabled('faq') ? 'true' : 'false' }},
        rpc: {{ $isEnabled('rpc') ? 'true' : 'false' }},
        venue: {{ $isEnabled('venue') ? 'true' : 'false' }},
        what_to_bring: {{ $isEnabled('what_to_bring') ? 'true' : 'false' }}
    }
}" class="border-b border-slate-700 pb-8">
    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">5</span>
        Premium Amenities
    </h3>

    <div class="space-y-4">
        @include('eo.events.partials.amenities.jersey')
        @include('eo.events.partials.amenities.medal')
        @include('eo.events.partials.amenities.faq')
        @include('eo.events.partials.amenities.rpc')
        @include('eo.events.partials.amenities.venue')
        @include('eo.events.partials.amenities.what-to-bring')
    </div>
</div>
