@if($categories->where('prizes', '!=', null)->count() > 0)
@php
    $template = $variant ?? ($event->template ?? null);
    $template = $template ?: 'modern-dark';
    $isDark = in_array($template, ['modern-dark', 'paolo-fest', 'paolo-fest-dark']);
    
    // Theme Colors Configuration
    $sectionBg = $isDark ? 'bg-slate-900 text-white' : 'bg-white text-slate-900';
    $cardBg = $isDark ? 'bg-slate-800/50 backdrop-blur-sm border-white/10' : 'bg-white border-slate-100 shadow-xl';
    $borderColor = $isDark ? 'border-white/10' : 'border-slate-100';
    $textMuted = $isDark ? 'text-slate-400' : 'text-slate-500';
    $textStrong = $isDark ? 'text-white' : 'text-slate-900';
    $headerBg = $isDark ? 'bg-white/5' : 'bg-slate-50';
    $rowHover = $isDark ? 'hover:bg-white/5' : 'hover:bg-slate-50';
@endphp

<section class="py-20 relative {{ $sectionBg }}" id="prizes-section">
    <div class="max-w-4xl mx-auto px-4 relative z-10">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black tracking-tight mb-4">Hadiah & Apresiasi</h2>
            <p class="{{ $textMuted }} max-w-2xl mx-auto">Rewards untuk para pemenang di setiap kategori. Berikan performa terbaikmu!</p>
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap justify-center gap-3 mb-10" role="tablist">
            @foreach($categories as $index => $cat)
                @if(!empty($cat->prizes))
                <button
                    onclick="switchPrizeTab('{{ $cat->id }}')"
                    class="prize-tab-btn px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300 border {{ $index === 0 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/25' : ($isDark ? 'bg-slate-800 border-slate-700 text-slate-400 hover:border-slate-500' : 'bg-slate-100 border-slate-200 text-slate-600 hover:bg-slate-200') }}"
                    data-target="{{ $cat->id }}"
                    data-active-class="bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/25 transform scale-105"
                    data-inactive-class="{{ $isDark ? 'bg-slate-800 border-slate-700 text-slate-400 hover:border-slate-500' : 'bg-slate-100 border-slate-200 text-slate-600 hover:bg-slate-200' }}"
                >
                    {{ $cat->name }}
                </button>
                @endif
            @endforeach
        </div>

        <!-- Table Content -->
        <div class="relative min-h-[300px]">
            @foreach($categories as $index => $cat)
                @if(!empty($cat->prizes))
                <div id="prize-content-{{ $cat->id }}" class="prize-content transition-all duration-500 absolute w-full {{ $index === 0 ? 'opacity-100 z-10 translate-y-0 relative' : 'opacity-0 z-0 translate-y-4 absolute top-0 pointer-events-none' }}">
                    
                    <div class="overflow-hidden rounded-2xl border {{ $borderColor }} {{ $cardBg }}">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="{{ $headerBg }} border-b {{ $borderColor }}">
                                    <th class="py-5 px-6 text-xs font-bold uppercase tracking-widest {{ $textMuted }}">Peringkat</th>
                                    <th class="py-5 px-6 text-xs font-bold uppercase tracking-widest {{ $textMuted }} text-right">Hadiah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y {{ $borderColor }}">
                                @foreach($cat->prizes as $rank => $amount)
                                <tr class="group {{ $rowHover }} transition-colors">
                                    <td class="py-5 px-6">
                                        <div class="flex items-center gap-4">
                                            @if($rank == 1)
                                                <div class="w-12 h-12 rounded-xl bg-amber-400/10 flex items-center justify-center text-amber-400 border border-amber-400/20 shadow-[0_0_15px_rgba(251,191,36,0.2)]">
                                                    <i class="fas fa-trophy text-xl"></i>
                                                </div>
                                                <div>
                                                    <span class="block font-bold text-lg {{ $textStrong }}">Juara 1</span>
                                                    <span class="text-xs text-amber-500/80 font-medium uppercase tracking-wider">Champion</span>
                                                </div>
                                            @elseif($rank == 2)
                                                <div class="w-12 h-12 rounded-xl bg-slate-300/10 flex items-center justify-center text-slate-400 border border-slate-300/20">
                                                    <i class="fas fa-medal text-xl"></i>
                                                </div>
                                                <div>
                                                    <span class="block font-bold text-lg {{ $textStrong }}">Juara 2</span>
                                                    <span class="text-xs {{ $textMuted }} font-medium uppercase tracking-wider">Runner Up</span>
                                                </div>
                                            @elseif($rank == 3)
                                                <div class="w-12 h-12 rounded-xl bg-orange-700/10 flex items-center justify-center text-orange-700 border border-orange-700/20">
                                                    <i class="fas fa-medal text-xl"></i>
                                                </div>
                                                <div>
                                                    <span class="block font-bold text-lg {{ $textStrong }}">Juara 3</span>
                                                    <span class="text-xs {{ $textMuted }} font-medium uppercase tracking-wider">3rd Place</span>
                                                </div>
                                            @else
                                                <div class="w-12 h-12 rounded-xl {{ $isDark ? 'bg-slate-800' : 'bg-slate-200' }} flex items-center justify-center {{ $textMuted }} font-bold text-lg">
                                                    {{ $rank }}
                                                </div>
                                                <div>
                                                    <span class="block font-bold text-lg {{ $textStrong }}">Peringkat {{ $rank }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-5 px-6 text-right">
                                        <span class="text-xl md:text-2xl font-black {{ $rank == 1 ? 'text-amber-400' : ($rank == 2 ? 'text-slate-400' : ($rank == 3 ? 'text-orange-600' : $textStrong)) }}">
                                            {{ $amount }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
                @endif
            @endforeach
        </div>
    </div>
</section>

<script>
    function switchPrizeTab(targetId) {
        // Reset all buttons
        document.querySelectorAll('.prize-tab-btn').forEach(btn => {
            const isActive = btn.dataset.target == targetId;
            btn.className = `prize-tab-btn px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300 border ${isActive ? btn.dataset.activeClass : btn.dataset.inactiveClass}`;
        });

        // Show/Hide Content
        document.querySelectorAll('.prize-content').forEach(content => {
            if(content.id === 'prize-content-' + targetId) {
                content.classList.remove('opacity-0', 'pointer-events-none', 'absolute', 'translate-y-4');
                content.classList.add('opacity-100', 'relative', 'z-10', 'translate-y-0');
            } else {
                content.classList.add('opacity-0', 'pointer-events-none', 'absolute', 'translate-y-4');
                content.classList.remove('opacity-100', 'relative', 'z-10', 'translate-y-0');
            }
        });
    }
</script>
@endif