@php
    $validCategories = $categories->filter(function($cat) {
        if (empty($cat->prizes) || !is_array($cat->prizes)) return false;
        foreach ($cat->prizes as $rank => $amount) {
            $val = trim((string)$amount);
            if (!empty($val) && $val !== 'null') {
                return true;
            }
        }
        return false;
    });

    $template = $variant ?? ($event->template ?? null);
    $template = $template ?: 'modern-dark';
    $isDark = isset($isDark) ? (bool)$isDark : in_array($template, ['modern-dark', 'paolo-fest', 'paolo-fest-dark']);
    
    // Theme Colors Configuration
    $sectionBg = $isDark ? 'bg-slate-950 text-white' : 'bg-white text-slate-900';
    $cardBg = $isDark ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200 shadow-sm';
    $borderColor = $isDark ? 'border-slate-800' : 'border-slate-200';
    $textMuted = $isDark ? 'text-slate-400' : 'text-slate-500';
    $textStrong = $isDark ? 'text-white' : 'text-slate-900';
    $headerBg = $isDark ? 'bg-slate-900/90' : 'bg-slate-50';
    $rowHover = $isDark ? 'hover:bg-slate-800/40' : 'hover:bg-slate-50/80';
    $divideColor = $isDark ? 'divide-slate-800' : 'divide-slate-100';

    $tabActive = $isDark 
        ? 'prize-tab-btn px-4 py-2 rounded-md text-xs font-bold border transition bg-white border-white text-slate-950 shadow-sm'
        : 'prize-tab-btn px-4 py-2 rounded-md text-xs font-bold border transition bg-slate-900 border-slate-900 text-white shadow-sm';
    $tabInactive = $isDark
        ? 'prize-tab-btn px-4 py-2 rounded-md text-xs font-bold border transition bg-slate-900 border-slate-800 text-slate-300 hover:border-slate-700 hover:text-white'
        : 'prize-tab-btn px-4 py-2 rounded-md text-xs font-bold border transition bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300';
@endphp

@if($validCategories->count() > 0)
<section class="py-16 sm:py-20 relative {{ $sectionBg }} border-b {{ $borderColor }}" id="prizes-section">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <!-- Section Header -->
        <div class="text-center mb-8 sm:mb-10 max-w-2xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Podium & Penghargaan</span>
            <h2 class="text-2xl sm:text-3xl font-heading font-extrabold text-slate-900 {{ $isDark ? '!text-white' : '' }} mt-1.5">Hadiah Pemenang</h2>
            <p class="text-xs sm:text-sm {{ $textMuted }} mt-2">Daftar apresiasi resmi bagi para pelari terbaik di masing-masing kategori lomba.</p>
        </div>

        @if($validCategories->count() > 1)
        <!-- Category Tabs -->
        <div class="flex flex-wrap justify-center gap-2 mb-8" role="tablist">
            @foreach($validCategories as $index => $cat)
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    onclick="switchPrizeTab('{{ $cat->id }}')"
                    class="{{ $loop->first ? $tabActive : $tabInactive }}"
                    data-target="{{ $cat->id }}"
                    data-active-class="{{ $tabActive }}"
                    data-inactive-class="{{ $tabInactive }}"
                >
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
        @endif

        <!-- Tables -->
        <div class="space-y-6">
            @foreach($validCategories as $index => $cat)
                <div id="prize-content-{{ $cat->id }}" class="prize-content {{ $loop->first ? '' : 'hidden' }}">
                    <div class="rounded-lg border {{ $borderColor }} {{ $cardBg }} overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="{{ $headerBg }} border-b {{ $borderColor }}">
                                    <th class="py-3.5 px-4 sm:px-6 text-xs font-bold uppercase tracking-wider {{ $textMuted }}">Peringkat</th>
                                    <th class="py-3.5 px-4 sm:px-6 text-xs font-bold uppercase tracking-wider {{ $textMuted }} text-right">Hadiah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y {{ $divideColor }}">
                                @foreach($cat->prizes as $rank => $amount)
                                @php
                                    $amountStr = trim((string)$amount);
                                    if (empty($amountStr) || $amountStr === 'null') continue;
                                @endphp
                                <tr class="group {{ $rowHover }} transition-colors">
                                    <td class="py-4 px-4 sm:px-6">
                                        <div class="flex items-center gap-3.5">
                                            @if($rank == 1)
                                                <div class="w-8 h-8 rounded-md bg-amber-500 text-slate-950 font-heading font-extrabold text-xs flex items-center justify-center shrink-0 shadow-sm">
                                                    1
                                                </div>
                                                <div>
                                                    <span class="block font-heading font-bold text-sm sm:text-base {{ $textStrong }}">Juara 1</span>
                                                    <span class="text-[11px] text-amber-600 font-semibold uppercase tracking-wider">Podium 1</span>
                                                </div>
                                            @elseif($rank == 2)
                                                <div class="w-8 h-8 rounded-md {{ $isDark ? 'bg-slate-700 text-slate-100' : 'bg-slate-200 text-slate-800' }} font-heading font-bold text-xs flex items-center justify-center shrink-0">
                                                    2
                                                </div>
                                                <div>
                                                    <span class="block font-heading font-bold text-sm sm:text-base {{ $textStrong }}">Juara 2</span>
                                                    <span class="text-[11px] {{ $textMuted }} font-medium uppercase tracking-wider">Podium 2</span>
                                                </div>
                                            @elseif($rank == 3)
                                                <div class="w-8 h-8 rounded-md {{ $isDark ? 'bg-amber-950/40 text-amber-300 border border-amber-800/40' : 'bg-amber-100 text-amber-900 border border-amber-300' }} font-heading font-bold text-xs flex items-center justify-center shrink-0">
                                                    3
                                                </div>
                                                <div>
                                                    <span class="block font-heading font-bold text-sm sm:text-base {{ $textStrong }}">Juara 3</span>
                                                    <span class="text-[11px] {{ $textMuted }} font-medium uppercase tracking-wider">Podium 3</span>
                                                </div>
                                            @else
                                                <div class="w-8 h-8 rounded-md {{ $isDark ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-600' }} font-heading font-bold text-xs flex items-center justify-center shrink-0">
                                                    {{ $rank }}
                                                </div>
                                                <div>
                                                    <span class="block font-heading font-bold text-sm sm:text-base {{ $textStrong }}">Peringkat {{ $rank }}</span>
                                                    <span class="text-[11px] {{ $textMuted }} font-medium uppercase tracking-wider">Top Finisher</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 sm:px-6 text-right">
                                        <span class="font-mono tabular-nums text-base sm:text-lg font-bold {{ $rank == 1 ? 'text-amber-600' : $textStrong }}">
                                            {{ $amountStr }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<script>
    function switchPrizeTab(targetId) {
        document.querySelectorAll('.prize-tab-btn').forEach(btn => {
            const isActive = btn.dataset.target === String(targetId);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            btn.className = isActive ? btn.dataset.activeClass : btn.dataset.inactiveClass;
        });

        document.querySelectorAll('.prize-content').forEach(content => {
            if (content.id === 'prize-content-' + targetId) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    }
</script>
@endif