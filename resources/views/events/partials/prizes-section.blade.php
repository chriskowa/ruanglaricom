@php
    $validCategories = $categories->filter(function ($cat) {
        if (empty($cat->prizes) || !is_array($cat->prizes)) {
            return false;
        }
        foreach ($cat->prizes as $rank => $amount) {
            $val = trim((string) $amount);
            if ($val !== '' && $val !== 'null') {
                return true;
            }
        }
        return false;
    })->values();

    $catCount = $validCategories->count();
    $template = $variant ?? ($event->template ?? null);
    $template = $template ?: 'modern-dark';
    $isDark = isset($isDark) ? (bool) $isDark : in_array($template, ['modern-dark', 'paolo-fest-dark', 'golden-run']);

    $sectionBg = $isDark ? 'bg-slate-950 text-white' : 'bg-white text-slate-900';
    $cardBg = $isDark ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200 shadow-sm';
    $borderColor = $isDark ? 'border-slate-800' : 'border-slate-200';
    $textMuted = $isDark ? 'text-slate-300' : 'text-slate-600';
    $textStrong = $isDark ? 'text-white' : 'text-slate-900';
    $headerBg = $isDark ? 'bg-slate-800' : 'bg-slate-50';
    $rowHover = $isDark ? 'hover:bg-slate-800/50' : 'hover:bg-slate-50';
    $divideColor = $isDark ? 'divide-slate-800' : 'divide-slate-100';
    $badgeMuted = $isDark ? 'bg-slate-800 text-slate-200 border-slate-700' : 'bg-slate-100 text-slate-700 border-slate-200';

    $containerClass = $catCount === 1
        ? 'max-w-2xl'
        : ($catCount === 2 ? 'max-w-5xl' : 'max-w-6xl');
    $gridClass = $catCount === 1
        ? 'grid-cols-1'
        : ($catCount === 2 ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3');
@endphp

@if($catCount > 0)
<section class="py-16 sm:py-20 relative {{ $sectionBg }} border-b {{ $borderColor }}" id="prizes-section">
    <div class="{{ $containerClass }} mx-auto px-4 sm:px-6">
        <div class="text-center mb-8 sm:mb-10 max-w-2xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Podium & Penghargaan</span>
            <h2 class="text-2xl sm:text-3xl font-heading font-extrabold {{ $textStrong }} mt-1.5">Hadiah Pemenang</h2>
            <p class="text-xs sm:text-sm {{ $textMuted }} mt-2">Daftar apresiasi resmi bagi para pelari terbaik di masing-masing kategori lomba.</p>
        </div>

        <div class="grid {{ $gridClass }} gap-6 items-start">
            @foreach($validCategories as $cat)
                <div class="rounded-lg border {{ $borderColor }} {{ $cardBg }} overflow-hidden">
                    <div class="px-5 py-4 {{ $headerBg }} border-b {{ $borderColor }} flex items-center justify-between gap-3">
                        <h3 class="font-heading font-bold text-base {{ $textStrong }}">{{ $cat->name }}</h3>
                        @if($cat->distance_km)
                            <span class="px-2.5 py-1 rounded text-xs font-mono font-bold border {{ $badgeMuted }} shrink-0">
                                {{ (float) $cat->distance_km }} KM
                            </span>
                        @endif
                    </div>

                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b {{ $borderColor }}">
                                <th class="py-3 px-5 text-xs font-bold uppercase tracking-wider {{ $textMuted }}">Peringkat</th>
                                <th class="py-3 px-5 text-xs font-bold uppercase tracking-wider {{ $textMuted }} text-right">Hadiah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y {{ $divideColor }}">
                            @foreach($cat->prizes as $rank => $amount)
                                @php
                                    $amountStr = trim((string) $amount);
                                    if ($amountStr === '' || $amountStr === 'null') {
                                        continue;
                                    }
                                    $displayAmount = preg_match('/^\d+$/', $amountStr)
                                        ? 'Rp ' . number_format((int) $amountStr, 0, ',', '.')
                                        : $amountStr;
                                @endphp
                                <tr class="{{ $rowHover }} transition-colors duration-150">
                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center gap-3">
                                            @if((int) $rank === 1)
                                                <div class="w-8 h-8 rounded-md bg-amber-500 text-slate-950 font-heading font-extrabold text-xs flex items-center justify-center shrink-0">
                                                    1
                                                </div>
                                                <div>
                                                    <span class="block font-heading font-bold text-sm {{ $textStrong }}">Juara 1</span>
                                                    <span class="text-xs {{ $isDark ? 'text-amber-400' : 'text-amber-700' }} font-semibold">Podium 1</span>
                                                </div>
                                            @elseif((int) $rank === 2)
                                                <div class="w-8 h-8 rounded-md {{ $isDark ? 'bg-slate-700 text-slate-100' : 'bg-slate-200 text-slate-800' }} font-heading font-bold text-xs flex items-center justify-center shrink-0">
                                                    2
                                                </div>
                                                <div>
                                                    <span class="block font-heading font-bold text-sm {{ $textStrong }}">Juara 2</span>
                                                    <span class="text-xs {{ $textMuted }}">Podium 2</span>
                                                </div>
                                            @elseif((int) $rank === 3)
                                                <div class="w-8 h-8 rounded-md {{ $isDark ? 'bg-amber-950 text-amber-300 border border-amber-800' : 'bg-amber-100 text-amber-900 border border-amber-300' }} font-heading font-bold text-xs flex items-center justify-center shrink-0">
                                                    3
                                                </div>
                                                <div>
                                                    <span class="block font-heading font-bold text-sm {{ $textStrong }}">Juara 3</span>
                                                    <span class="text-xs {{ $textMuted }}">Podium 3</span>
                                                </div>
                                            @else
                                                <div class="w-8 h-8 rounded-md {{ $isDark ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-700' }} font-heading font-bold text-xs flex items-center justify-center shrink-0">
                                                    {{ $rank }}
                                                </div>
                                                <div>
                                                    <span class="block font-heading font-bold text-sm {{ $textStrong }}">Peringkat {{ $rank }}</span>
                                                    <span class="text-xs {{ $textMuted }}">Top Finisher</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5 text-right">
                                        <span class="font-mono tabular-nums text-sm sm:text-base font-bold {{ (int) $rank === 1 ? ($isDark ? 'text-amber-400' : 'text-amber-700') : $textStrong }}">
                                            {{ $displayAmount }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
