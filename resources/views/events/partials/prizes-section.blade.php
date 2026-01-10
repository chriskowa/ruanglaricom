@if($categories->where('prizes', '!=', null)->count() > 0)
@php
    $template = $variant ?? ($event->template ?? null);
    $template = $template ?: 'modern-dark';
    $isDark = $template === 'modern-dark';

    $sectionBg = $isDark ? 'bg-slate-900 text-white' : 'bg-white text-slate-900';
    $subText = $isDark ? 'text-slate-400' : 'text-slate-600';
    $tabBase = $isDark ? 'border-slate-700' : 'border-slate-200';

    $accentFrom = 'from-amber-400';
    $accentTo = 'to-orange-500';
    $badgeText = $isDark ? 'text-black' : 'text-white';
    $badgeShadow = $isDark ? 'shadow-lg shadow-orange-500/20' : 'shadow-lg shadow-slate-900/5';
    $blob1 = $isDark ? 'bg-purple-600/20' : 'bg-slate-200/60';
    $blob2 = $isDark ? 'bg-blue-600/20' : 'bg-slate-100/60';

    $tabActive = 'bg-amber-500 text-black border-amber-500';
    $tabInactive = ($isDark ? 'bg-slate-800 text-slate-400' : 'bg-white text-slate-600').' '.$tabBase;
    $tabHover = $isDark ? 'hover:border-amber-500 hover:text-amber-400' : 'hover:border-slate-300 hover:text-slate-900';

    $goldBorder = 'border-amber-400';
    $goldText = 'text-amber-500';
    $goldLine = 'bg-amber-400';
    $goldShadow = $isDark ? 'shadow-[0_0_50px_rgba(251,191,36,0.5)]' : 'shadow-[0_20px_60px_-35px_rgba(251,191,36,0.6)]';

    $silverBorder = $isDark ? 'border-slate-300' : 'border-slate-300';
    $silverText = $isDark ? 'text-slate-300' : 'text-slate-500';
    $silverLine = 'bg-slate-300';
    $silverShadow = $isDark ? 'shadow-[0_0_30px_rgba(203,213,225,0.3)]' : 'shadow-[0_20px_60px_-40px_rgba(148,163,184,0.55)]';

    $bronzeBorder = $isDark ? 'border-orange-700' : 'border-orange-500';
    $bronzeText = $isDark ? 'text-orange-700' : 'text-orange-600';
    $bronzeLine = $isDark ? 'bg-orange-700' : 'bg-orange-500';
    $bronzeShadow = $isDark ? 'shadow-[0_0_30px_rgba(194,65,12,0.3)]' : 'shadow-[0_20px_60px_-40px_rgba(249,115,22,0.45)]';

    if ($template === 'light-clean') {
        $accentFrom = 'from-brand-600';
        $accentTo = 'to-brand-500';
        $badgeShadow = 'shadow-lg shadow-brand-600/15';
        $blob1 = 'bg-brand-600/10';
        $blob2 = 'bg-sky-400/10';
        $tabActive = 'bg-brand-600 text-white border-brand-600';
        $tabHover = 'hover:border-brand-600 hover:text-brand-600';
    } elseif ($template === 'paolo-fest') {
        $accentFrom = 'from-brand-600';
        $accentTo = 'to-accent-500';
        $badgeShadow = 'shadow-lg shadow-brand-600/15';
        $blob1 = 'bg-brand-600/10';
        $blob2 = 'bg-accent-500/10';
        $tabActive = 'bg-brand-600 text-white border-brand-600';
        $tabHover = 'hover:border-brand-600 hover:text-brand-600';
    } elseif ($template === 'professional-city-run' || $template === 'profesional-city-run') {
        $accentFrom = 'from-brand-600';
        $accentTo = 'to-action-500';
        $badgeShadow = 'shadow-lg shadow-brand-600/15';
        $blob1 = 'bg-brand-600/10';
        $blob2 = 'bg-action-500/10';
        $tabActive = 'bg-brand-900 text-white border-brand-900';
        $tabHover = 'hover:border-brand-600 hover:text-brand-600';
    } elseif ($template === 'simple-minimal') {
        $accentFrom = 'from-slate-900';
        $accentTo = 'to-slate-700';
        $badgeShadow = 'shadow-lg shadow-slate-900/10';
        $blob1 = 'bg-slate-200/70';
        $blob2 = 'bg-slate-100/70';
        $tabActive = 'bg-slate-900 text-white border-slate-900';
        $tabHover = 'hover:border-slate-900 hover:text-slate-900';
    }

    $badgeBg = "bg-gradient-to-r {$accentFrom} {$accentTo}";
    $circleBg = $isDark ? 'bg-slate-800' : 'bg-white';
    $cardBg = $isDark ? 'bg-gradient-to-b from-white/10 to-slate-900/40' : 'bg-gradient-to-b from-white to-slate-50';
    $cardBorder = $isDark ? 'border border-white/10' : 'border border-slate-200';
    $cardSub = $isDark ? 'text-slate-400' : 'text-slate-500';
    $amountText = $isDark ? 'text-white' : 'text-slate-900';
@endphp

<section class="py-20 relative overflow-hidden {{ $sectionBg }}" id="prizes-section">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] {{ $blob1 }} blur-[100px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] {{ $blob2 }} blur-[100px] rounded-full"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="text-center mb-12">
            <span class="inline-block py-1 px-3 rounded-full {{ $badgeBg }} {{ $badgeText }} text-xs font-bold uppercase tracking-wider mb-4 {{ $badgeShadow }}">
                Rewards
            </span>
            <h2 class="text-3xl md:text-5xl font-black tracking-tight mb-4">Podium Juara</h2>
            <p class="{{ $subText }} max-w-2xl mx-auto">Total hadiah menarik menanti para pemenang di setiap kategori. Siapkan performa terbaikmu!</p>
        </div>

        <div class="flex flex-wrap justify-center gap-2 mb-16" role="tablist">
            @foreach($categories as $index => $cat)
                @if(!empty($cat->prizes))
                <button
                    onclick="switchPrizeTab('{{ $cat->id }}')"
                    class="prize-tab-btn px-6 py-2 rounded-full text-sm font-bold transition-all duration-300 border {{ $tabHover }} {{ $index === 0 ? $tabActive : $tabInactive }}"
                    data-target="{{ $cat->id }}"
                    data-active-class="{{ $tabActive }}"
                    data-inactive-class="{{ $tabInactive }}"
                >{{ $cat->name }}</button>
                @endif
            @endforeach
        </div>

        <div class="relative min-h-[400px]">
            @foreach($categories as $index => $cat)
                @if(!empty($cat->prizes))
                <div id="prize-content-{{ $cat->id }}" class="prize-content transition-all duration-500 absolute w-full {{ $index === 0 ? 'opacity-100 z-10 scale-100' : 'opacity-0 z-0 scale-95 pointer-events-none' }}">
                    <div class="flex flex-col md:flex-row items-end justify-center gap-4 md:gap-8 pb-10">
                        <div class="order-2 md:order-1 flex flex-col items-center w-full md:w-1/3 group">
                            <div class="mb-4 transform transition-all duration-500 group-hover:-translate-y-2">
                                <div class="w-24 h-24 md:w-32 md:h-32 rounded-full border-4 {{ $silverBorder }} {{ $circleBg }} flex items-center justify-center {{ $silverShadow }} relative overflow-hidden">
                                    <span class="text-4xl md:text-5xl font-black {{ $silverText }}">2</span>
                                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                </div>
                            </div>
                            <div class="w-full {{ $cardBg }} {{ $cardBorder }} rounded-2xl p-6 text-center transform transition-all duration-700 translate-y-[20px] opacity-0 reveal-podium delay-100 h-[180px] md:h-[220px] flex flex-col justify-start relative overflow-hidden">
                                <div class="absolute top-0 left-0 w-full h-1 {{ $silverLine }}"></div>
                                <h4 class="text-xl font-bold {{ $silverText }} mb-2">Runner Up</h4>
                                <p class="text-lg md:text-2xl font-black {{ $amountText }} mb-1">{{ $cat->prizes['2'] ?? '-' }}</p>
                                <p class="text-xs {{ $cardSub }}">Hadiah + Medali Perak</p>
                            </div>
                        </div>

                        <div class="order-1 md:order-2 flex flex-col items-center w-full md:w-1/3 group z-10">
                            <div class="mb-4 transform transition-all duration-500 group-hover:-translate-y-4 relative">
                                <div class="absolute -top-10 left-1/2 -translate-x-1/2 text-5xl {{ $isDark ? 'animate-bounce' : '' }}">👑</div>
                                <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 {{ $goldBorder }} {{ $circleBg }} flex items-center justify-center {{ $goldShadow }} relative overflow-hidden">
                                    <span class="text-5xl md:text-6xl font-black {{ $goldText }}">1</span>
                                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                </div>
                            </div>
                            <div class="w-full {{ $cardBg }} {{ $cardBorder }} rounded-2xl p-8 text-center transform transition-all duration-700 translate-y-[20px] opacity-0 reveal-podium h-[220px] md:h-[280px] flex flex-col justify-start relative overflow-hidden">
                                <div class="absolute top-0 left-0 w-full h-1 {{ $goldLine }}"></div>
                                <h4 class="text-2xl font-bold {{ $goldText }} mb-2">Champion</h4>
                                <p class="text-xl md:text-3xl font-black {{ $amountText }} mb-1">{{ $cat->prizes['1'] ?? '-' }}</p>
                                <p class="text-sm {{ $cardSub }}">Hadiah Utama + Medali Emas</p>
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -skew-x-12 translate-x-[-200%] group-hover:translate-x-[200%] transition-transform duration-1000"></div>
                            </div>
                        </div>

                        <div class="order-3 md:order-3 flex flex-col items-center w-full md:w-1/3 group">
                            <div class="mb-4 transform transition-all duration-500 group-hover:-translate-y-2">
                                <div class="w-24 h-24 md:w-32 md:h-32 rounded-full border-4 {{ $bronzeBorder }} {{ $circleBg }} flex items-center justify-center {{ $bronzeShadow }} relative overflow-hidden">
                                    <span class="text-4xl md:text-5xl font-black {{ $bronzeText }}">3</span>
                                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                </div>
                            </div>
                            <div class="w-full {{ $cardBg }} {{ $cardBorder }} rounded-2xl p-6 text-center transform transition-all duration-700 translate-y-[20px] opacity-0 reveal-podium delay-200 h-[160px] md:h-[180px] flex flex-col justify-start relative overflow-hidden">
                                <div class="absolute top-0 left-0 w-full h-1 {{ $bronzeLine }}"></div>
                                <h4 class="text-xl font-bold {{ $bronzeText }} mb-2">3rd Place</h4>
                                <p class="text-lg md:text-2xl font-black {{ $amountText }} mb-1">{{ $cat->prizes['3'] ?? '-' }}</p>
                                <p class="text-xs {{ $cardSub }}">Hadiah + Medali Perunggu</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</section>

<script>
    function switchPrizeTab(targetId) {
        const setClasses = (el, addStr, removeStr) => {
            (removeStr || '').split(' ').filter(Boolean).forEach(c => el.classList.remove(c));
            (addStr || '').split(' ').filter(Boolean).forEach(c => el.classList.add(c));
        };

        document.querySelectorAll('.prize-tab-btn').forEach(btn => {
            if (btn.dataset.target == targetId) {
                setClasses(btn, btn.dataset.activeClass, btn.dataset.inactiveClass);
            } else {
                setClasses(btn, btn.dataset.inactiveClass, btn.dataset.activeClass);
            }
        });

        document.querySelectorAll('.prize-content').forEach(content => {
            if(content.id === 'prize-content-' + targetId) {
                content.classList.remove('opacity-0', 'z-0', 'scale-95', 'pointer-events-none');
                content.classList.add('opacity-100', 'z-10', 'scale-100');
                content.querySelectorAll('.reveal-podium').forEach(el => {
                    el.classList.remove('opacity-0', 'translate-y-[20px]');
                });
            } else {
                content.classList.add('opacity-0', 'z-0', 'scale-95', 'pointer-events-none');
                content.classList.remove('opacity-100', 'z-10', 'scale-100');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.querySelectorAll('.reveal-podium').forEach(el => {
                        el.classList.remove('opacity-0', 'translate-y-[20px]');
                    });
                }
            });
        }, { threshold: 0.2 });

        const section = document.getElementById('prizes-section');
        if(section) observer.observe(section);
    });
</script>
@endif
