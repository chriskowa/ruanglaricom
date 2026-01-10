@props([
    'gradientFrom' => 'from-white',
    'titleColor' => 'text-slate-500',
    'containerClass' => 'bg-white shadow-sm border border-gray-100',
    'sectionClass' => 'py-12 bg-transparent'
])

@if(isset($event->sponsors) && is_array($event->sponsors) && count($event->sponsors) > 0)
<section class="{{ $sectionClass }} overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 mb-8 text-center">
        <h3 class="text-sm font-bold uppercase tracking-widest {{ $titleColor }}">Official Partners</h3>
    </div>
    
    <div class="relative w-full overflow-hidden group">
        <!-- Fade Edges -->
        <div class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r {{ $gradientFrom }} to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l {{ $gradientFrom }} to-transparent z-10 pointer-events-none"></div>

        <div class="flex animate-scroll hover:pause-scroll gap-6 w-max px-4">
            <!-- Repeat enough times to fill screen and loop smoothly -->
            @for ($i = 0; $i < 6; $i++) 
                @foreach($event->sponsors as $sponsor)
                    <div class="flex-shrink-0 w-36 h-24 {{ $containerClass }} rounded-xl flex items-center justify-center p-4 hover:scale-105 transition duration-300">
                        <img src="{{ asset('storage/' . $sponsor) }}" alt="Sponsor" class="max-w-full max-h-full object-contain">
                    </div>
                @endforeach
            @endfor
        </div>
    </div>
</section>

<style>
    @keyframes scroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-scroll {
        animation: scroll 60s linear infinite;
    }
    .hover\:pause-scroll:hover {
        animation-play-state: paused;
    }
</style>
@endif
