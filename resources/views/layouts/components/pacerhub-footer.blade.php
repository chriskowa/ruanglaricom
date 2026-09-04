@php
    $siteTitle = \App\Models\AppSettings::get('site_title', 'Ruang Lari Indonesia');
    $socialInsta = \App\Models\AppSettings::get('social_instagram', 'https://www.instagram.com/ruanglaricom/');
    $socialTiktok = \App\Models\AppSettings::get('social_tiktok', 'https://www.tiktok.com/@ruanglaricom');
    $socialStrava = \App\Models\AppSettings::get('social_strava', 'https://www.strava.com/clubs/ruanglari');
    $socialYt = \App\Models\AppSettings::get('social_youtube', 'https://www.youtube.com/@ruanglaricom');
    $lightMode = $lightMode ?? false;
@endphp

<footer class="relative bg-[#020617] border-t border-slate-900/90 text-slate-400 font-sans overflow-hidden" aria-label="Footer Ruang Lari">
    
    {{-- =========================================================
        ATMOSPHERIC SPORTS-TECH BACKGROUND GRAPHICS
    ========================================================== --}}
    {{-- 1. Ambient Lighting Highlights (Subtle & Clean) --}}
    <div class="absolute top-0 left-1/4 w-[600px] h-[300px] bg-[#C7FF00]/[0.02] blur-[140px] rounded-full pointer-events-none select-none" aria-hidden="true"></div>
    <div class="absolute bottom-0 right-10 w-[500px] h-[260px] bg-[#C7FF00]/[0.015] blur-[130px] rounded-full pointer-events-none select-none" aria-hidden="true"></div>

    {{-- 2. Athletic Running Track Lines Silhouette (Clean White Stadium Lanes) --}}
    <div class="absolute inset-0 pointer-events-none select-none overflow-hidden z-0" aria-hidden="true">
        <svg class="absolute -right-20 -bottom-20 w-[840px] h-[840px]" viewBox="0 0 840 840" fill="none" style="mask-image: linear-gradient(to top left, black 50%, transparent 95%); -webkit-mask-image: linear-gradient(to top left, black 50%, transparent 95%);">
            <defs>
                <linearGradient id="rlWhiteTrackGrad" x1="100%" y1="100%" x2="0%" y2="0%">
                    <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.28" />
                    <stop offset="50%" stop-color="#FFFFFF" stop-opacity="0.15" />
                    <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0.05" />
                </linearGradient>
            </defs>
            {{-- Olympic Running Track Lanes (White Lines) --}}
            <circle cx="840" cy="840" r="280" stroke="url(#rlWhiteTrackGrad)" stroke-width="1.8" stroke-dasharray="6 6"/>
            <circle cx="840" cy="840" r="350" stroke="url(#rlWhiteTrackGrad)" stroke-width="1.8"/>
            <circle cx="840" cy="840" r="420" stroke="url(#rlWhiteTrackGrad)" stroke-width="1.8" stroke-dasharray="14 8"/>
            <circle cx="840" cy="840" r="490" stroke="url(#rlWhiteTrackGrad)" stroke-width="1.8"/>
            <circle cx="840" cy="840" r="560" stroke="url(#rlWhiteTrackGrad)" stroke-width="1.8" stroke-dasharray="8 6"/>
            <circle cx="840" cy="840" r="630" stroke="url(#rlWhiteTrackGrad)" stroke-width="1.8"/>
            <circle cx="840" cy="840" r="700" stroke="url(#rlWhiteTrackGrad)" stroke-width="1.8"/>
            <circle cx="840" cy="840" r="770" stroke="url(#rlWhiteTrackGrad)" stroke-width="1.8"/>
            {{-- Lane Stagger Distance Tick Marks --}}
            <line x1="840" y1="560" x2="840" y2="535" stroke="#FFFFFF" stroke-opacity="0.25" stroke-width="2.5"/>
            <line x1="840" y1="490" x2="840" y2="465" stroke="#FFFFFF" stroke-opacity="0.25" stroke-width="2.5"/>
            <line x1="840" y1="420" x2="840" y2="395" stroke="#FFFFFF" stroke-opacity="0.25" stroke-width="2.5"/>
            <line x1="840" y1="350" x2="840" y2="325" stroke="#FFFFFF" stroke-opacity="0.25" stroke-width="2.5"/>
            {{-- Lane Numbers Along the Curve --}}
            <text x="548" y="832" fill="#FFFFFF" fill-opacity="0.22" font-size="12" font-weight="800" font-family="monospace">1</text>
            <text x="478" y="832" fill="#FFFFFF" fill-opacity="0.22" font-size="12" font-weight="800" font-family="monospace">2</text>
            <text x="408" y="832" fill="#FFFFFF" fill-opacity="0.22" font-size="12" font-weight="800" font-family="monospace">3</text>
            <text x="338" y="832" fill="#FFFFFF" fill-opacity="0.22" font-size="12" font-weight="800" font-family="monospace">4</text>
            <text x="268" y="832" fill="#FFFFFF" fill-opacity="0.22" font-size="12" font-weight="800" font-family="monospace">5</text>
            <text x="198" y="832" fill="#FFFFFF" fill-opacity="0.22" font-size="12" font-weight="800" font-family="monospace">6</text>
            <text x="128" y="832" fill="#FFFFFF" fill-opacity="0.22" font-size="12" font-weight="800" font-family="monospace">7</text>
        </svg>
    </div>

    {{-- =========================================================
        MAIN FOOTER CONTENT CONTAINER
    ========================================================== --}}
    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 pt-20 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-10 xl:gap-14 pb-16">
            
            {{-- ----------------------------------------------------
                LEFT SECTION: BRAND IDENTITY & INTERACTIVE SHORTCUTS
            ----------------------------------------------------- --}}
            <div class="lg:col-span-5 space-y-6">
                {{-- Brand Lockup --}}
                
                <a href="{{ url('/') }}" 
                        class="text-sm sm:text-lg md:text-xl font-black italic tracking-tighter flex items-center {{ $lightMode ? 'text-slate-900' : 'text-white' }}"
                        title="RuangLari Utama">
                        <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="{{ $siteTitle }}" class="h-10 w-auto object-contain transition-transform duration-200 group-hover:scale-105">
                        RUANG<span class="pl-1" style="{{ $lightMode ? 'color: #000000ff;' : 'color: #ccff00;' }}">LARI</span>
                    </a>

                {{-- Sports Headline --}}
                <div class="pt-1">
                    <h2 class="text-2xl sm:text-3xl lg:text-[2.1rem] font-black uppercase tracking-tight text-white leading-[1.08] font-sans">
                        Run Together.<br>
                        Grow Stronger.<br>
                        <span class="text-[#C7FF00]">Go Further.</span>
                    </h2>
                    <p class="mt-3.5 text-sm text-slate-300 leading-relaxed max-w-md">
                        Ekosistem lari Indonesia untuk berkembang, terhubung, dan mencapai personal best.
                    </p>
                </div>

                {{-- 4 Interactive Feature Shortcuts (Compact Sports-Tech Cards) --}}
                <div class="pt-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-2.5">
                        Alat &amp; Fitur Interaktif
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-w-lg">
                        
                        {{-- 1. VDOT Analyzer --}}
                        <a href="{{ url('/#vdot-section') }}" class="group relative flex flex-col p-3 rounded-lg bg-[#060D1F] border border-slate-800 hover:border-[#C7FF00]/70 hover:bg-[#0B152B] transition-all duration-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold tracking-wider text-[#C7FF00] uppercase">TRAINING LAB</span>
                                <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-[#C7FF00] group-hover:translate-x-0.5 transition-all" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-white group-hover:text-white transition-colors">VDOT Analyzer</span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Kalkulasi pace &amp; kapasitas aerobik</span>
                        </a>

                        {{-- 2. Find Running Buddy --}}
                        <a href="{{ route('run-connect.index') }}" class="group relative flex flex-col p-3 rounded-lg bg-[#060D1F] border border-slate-800 hover:border-[#C7FF00]/70 hover:bg-[#0B152B] transition-all duration-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold tracking-wider text-[#C7FF00] uppercase">RUN CONNECT</span>
                                <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-[#C7FF00] group-hover:translate-x-0.5 transition-all" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-white group-hover:text-white transition-colors">Find Running Buddy</span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Temukan teman lari &amp; komunitas</span>
                        </a>

                        {{-- 3. Race Calendar --}}
                        <a href="{{ route('events.index') }}" class="group relative flex flex-col p-3 rounded-lg bg-[#060D1F] border border-slate-800 hover:border-[#C7FF00]/70 hover:bg-[#0B152B] transition-all duration-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold tracking-wider text-[#C7FF00] uppercase">RACE AGENDA</span>
                                <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-[#C7FF00] group-hover:translate-x-0.5 transition-all" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-white group-hover:text-white transition-colors">Race Calendar</span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Jadwal event maraton terverifikasi</span>
                        </a>

                        {{-- 4. AI Running Coach --}}
                        <a href="{{ route('tools.form-analyzer') }}" class="group relative flex flex-col p-3 rounded-lg bg-[#060D1F] border border-slate-800 hover:border-[#C7FF00]/70 hover:bg-[#0B152B] transition-all duration-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold tracking-wider text-[#C7FF00] uppercase">BIOMECHANICS</span>
                                <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-[#C7FF00] group-hover:translate-x-0.5 transition-all" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-white group-hover:text-white transition-colors">AI Running Coach</span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Analisis form lari &amp; rekomendasi</span>
                        </a>

                    </div>
                </div>
            </div>

            {{-- ----------------------------------------------------
                CENTER SECTION: 3 ELEGANT NAVIGATION COLUMNS
            ----------------------------------------------------- --}}
            <div class="lg:col-span-4 grid grid-cols-3 gap-6 sm:gap-8 pt-2">
                
                {{-- Column 1: TRAINING --}}
                <div class="space-y-4">
                    <h3 class="text-xs font-bold tracking-widest text-[#C7FF00] uppercase">
                        Training
                    </h3>
                    <ul class="space-y-2.5 text-sm">
                        <li>
                            <a href="{{ route('programs.index') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Training Program
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('landing.program-lari-5k-pemula') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                5K Beginner Plan
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('landing.program-lari-10k') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                10K Speed Plan
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('landing.program-half-marathon') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Half Marathon
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('coaches.index') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Running Coach
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Column 2: COMMUNITY --}}
                <div class="space-y-4">
                    <h3 class="text-xs font-bold tracking-widest text-[#C7FF00] uppercase">
                        Community
                    </h3>
                    <ul class="space-y-2.5 text-sm">
                        <li>
                            <a href="{{ route('community.index') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Running Groups
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('events.index') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Race Calendar
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('events.index') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Event Directory
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('gpx.index') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                GPX Database
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('marketplace.index') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Marketplace Gear
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Column 3: RESOURCES --}}
                <div class="space-y-4">
                    <h3 class="text-xs font-bold tracking-widest text-[#C7FF00] uppercase">
                        Resources
                    </h3>
                    <ul class="space-y-2.5 text-sm">
                        <li>
                            <a href="{{ url('/blog') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Running Articles
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('calculator') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Pace Calculator
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/blog') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Training Tips
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/blog') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                Shoe Guide
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tools.form-analyzer') }}" class="text-slate-300 hover:text-white hover:translate-x-1 inline-block transition-all duration-150">
                                AI Coach
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            {{-- ----------------------------------------------------
                RIGHT SECTION: NEWSLETTER SUBSCRIPTION
            ----------------------------------------------------- --}}
            <div class="lg:col-span-3 space-y-4 pt-2">
                <div>
                    <h3 class="text-lg font-bold text-white tracking-tight">
                        Ready to run?
                    </h3>
                    <p class="mt-1 text-xs text-slate-400 leading-relaxed">
                        Event, training plan, dan tips lari langsung ke inbox kamu.
                    </p>
                </div>

                <form id="newsletter-form" class="space-y-2.5 pt-1" onsubmit="event.preventDefault(); subscribeNewsletter();">
                    <label for="newsletter-email" class="sr-only">Alamat Email</label>
                    <input
                        type="email"
                        id="newsletter-email"
                        name="email"
                        placeholder="Enter your email"
                        required
                        class="w-full bg-[#060D1F] text-white text-xs px-3.5 py-3 rounded-md border border-slate-800 placeholder-slate-500 focus:outline-none focus:border-[#C7FF00] focus:ring-1 focus:ring-[#C7FF00] transition duration-150"
                    >
                    <button
                        type="submit"
                        id="newsletter-btn"
                        class="w-full bg-[#C7FF00] hover:bg-white text-[#020617] font-black uppercase text-xs tracking-wider py-3 px-4 rounded-md transition duration-150 flex items-center justify-center gap-1 shadow-sm"
                    >
                        <span>JOIN RUN COMMUNITY &rarr;</span>
                    </button>
                    <p id="newsletter-message" class="text-xs mt-1.5 hidden"></p>
                </form>

                <p class="text-[11px] text-slate-400 pt-1">
                    Bebas spam. Berhenti berlangganan kapan saja.
                </p>
            </div>

        </div>

        {{-- ----------------------------------------------------
            BOTTOM SECTION: MINIMAL BOTTOM BAR
        ----------------------------------------------------- --}}
        <div class="border-t border-slate-900 pt-8 mt-2 flex flex-col md:flex-row items-center justify-between gap-6 text-xs text-slate-400">
            
            {{-- Social Media Links --}}
            <div class="flex items-center gap-3 order-2 md:order-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    FOLLOW RUANG LARI
                </span>
                <div class="flex items-center gap-2">
                    {{-- Instagram --}}
                    @if($socialInsta)
                    <a href="{{ $socialInsta }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram Ruang Lari" class="w-8 h-8 rounded-md bg-[#060D1F] border border-slate-800 flex items-center justify-center text-slate-400 hover:border-[#C7FF00] hover:text-[#C7FF00] transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
                        </svg>
                    </a>
                    @endif

                    {{-- Strava --}}
                    @if($socialStrava)
                    <a href="{{ $socialStrava }}" target="_blank" rel="noopener noreferrer" aria-label="Strava Club Ruang Lari" class="w-8 h-8 rounded-md bg-[#060D1F] border border-slate-800 flex items-center justify-center text-slate-400 hover:border-[#C7FF00] hover:text-[#C7FF00] transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7.925 15.586h4.172"/>
                        </svg>
                    </a>
                    @endif

                    {{-- YouTube --}}
                    @if($socialYt)
                    <a href="{{ $socialYt }}" target="_blank" rel="noopener noreferrer" aria-label="YouTube Ruang Lari" class="w-8 h-8 rounded-md bg-[#060D1F] border border-slate-800 flex items-center justify-center text-slate-400 hover:border-[#C7FF00] hover:text-[#C7FF00] transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </a>
                    @endif

                    {{-- TikTok --}}
                    @if($socialTiktok)
                    <a href="{{ $socialTiktok }}" target="_blank" rel="noopener noreferrer" aria-label="TikTok Ruang Lari" class="w-8 h-8 rounded-md bg-[#060D1F] border border-slate-800 flex items-center justify-center text-slate-400 hover:border-[#C7FF00] hover:text-[#C7FF00] transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 2.89 3.5 2.72 1.42-.08 2.65-.97 3.12-2.31.25-.66.31-1.38.31-2.09.01-4.73 0-9.46.01-14.19z"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Copyright --}}
            <div class="order-3 md:order-2 text-center">
                <p>&copy; 2026 RUANG LARI Indonesia. All rights reserved.</p>
            </div>

            {{-- Policy & Legal Links --}}
            <div class="flex items-center gap-5 order-1 md:order-3">
                <a href="{{ route('legal', ['tab' => 'privacy']) }}" class="hover:text-white transition-colors">
                    Privacy Policy
                </a>
                <span class="text-slate-700" aria-hidden="true">&bull;</span>
                <a href="{{ route('legal', ['tab' => 'terms']) }}" class="hover:text-white transition-colors">
                    Terms
                </a>
                <span class="text-slate-700" aria-hidden="true">&bull;</span>
                <a href="{{ route('vcard.index') }}" class="hover:text-white transition-colors">
                    Contact
                </a>
            </div>

        </div>
    </div>
</footer>

<script>
function subscribeNewsletter() {
    const emailInput = document.getElementById('newsletter-email');
    const email = emailInput ? emailInput.value : '';
    const btn = document.getElementById('newsletter-btn');
    const msg = document.getElementById('newsletter-message');
    
    if (!email) return;

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Memproses...';
    
    fetch('{{ route("newsletter.subscribe") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (msg) {
            msg.classList.remove('hidden');
            if (data.success) {
                msg.className = 'text-xs mt-2 text-[#C7FF00] font-medium';
                msg.textContent = data.message;
                emailInput.value = '';
            } else {
                msg.className = 'text-xs mt-2 text-rose-400 font-medium';
                msg.textContent = data.message;
            }
        }
    })
    .catch(error => {
        console.error(error);
        if (msg) {
            msg.classList.remove('hidden');
            msg.className = 'text-xs mt-2 text-rose-400 font-medium';
            msg.textContent = 'Terjadi kesalahan. Coba lagi nanti.';
        }
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
