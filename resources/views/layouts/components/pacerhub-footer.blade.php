    @php
        $siteTitle = \App\Models\AppSettings::get('site_title', 'Ruang Lari');
        $siteTagline = \App\Models\AppSettings::get('site_tagline', 'Platform komunitas lari terbesar di Indonesia. Temukan pacer, pantau progres, dan raih personal best Anda bersama kami.');
        $socialInsta = \App\Models\AppSettings::get('social_instagram', 'https://www.instagram.com/ruanglaricom/');
        $socialTiktok = \App\Models\AppSettings::get('social_tiktok', 'https://www.tiktok.com/@ruanglaricom');
        $socialFb = \App\Models\AppSettings::get('social_facebook', 'https://www.facebook.com/ruanglari');
        $socialYt = \App\Models\AppSettings::get('social_youtube');
    @endphp
        <footer class="bg-slate-950 border-t border-slate-900 pt-20 pb-10">
            <div class="max-w-7xl mx-auto p-5">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                    <div class="col-span-1 md:col-span-1">
                        <a href="{{ url('/') }}" class="flex items-center gap-2 mb-6 group">
                            <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="{{ $siteTitle }}" class="h-8 w-auto group-hover:scale-110 transition duration-300">
                            <span class="text-2xl font-black italic tracking-tighter text-white">RUANG<span class="pl-1 text-neon">LARI</span></span>
                        </a>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            {{ $siteTagline }}
                        </p>
                        <div class="flex gap-4">
                            @if($socialInsta)
                            <a href="{{ $socialInsta }}" target="_blank" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-900 transition"><i class="fab fa-instagram"></i></a>
                            @endif
                            @if($socialTiktok)
                            <a href="{{ $socialTiktok }}" target="_blank" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-900 transition"><i class="fab fa-tiktok"></i></a>
                            @endif
                            @if($socialFb)
                            <a href="{{ $socialFb }}" target="_blank" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-900 transition"><i class="fab fa-facebook-f"></i></a>
                            @endif
                            @if($socialYt)
                            <a href="{{ $socialYt }}" target="_blank" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-900 transition"><i class="fab fa-youtube"></i></a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-6 uppercase text-xs tracking-[0.2em] text-neon">Explore</h4>
                        <ul class="space-y-3 text-slate-400 text-sm font-medium">
                            <li><a href="{{ route('pacer.index') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Cari Pacer</a></li>
                            <li><a href="{{ route('events.index') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Event Kalender</a></li>
                            <li><a href="{{ route('leaderboard.cyberpunk') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Leaderboard</a></li>
                            <li><a href="{{ route('programs.realistic') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Running Calculator</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-6 uppercase text-xs tracking-[0.2em] text-neon">Community</h4>
                        <ul class="space-y-3 text-slate-400 text-sm font-medium">
                            <li><a href="{{ route('home') }}#about" class="hover:text-white hover:translate-x-1 inline-block transition">Tentang Kami</a></li>
                            <li><a href="{{ route('pacer.register') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Menjadi Pacer</a></li>
                            <li><a href="https://ruanglari.com/blog" class="hover:text-white hover:translate-x-1 inline-block transition">Blog & Tips</a></li>
                            <li><a href="{{ route('home') }}#faq" class="hover:text-white hover:translate-x-1 inline-block transition">FaQ</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-6 uppercase text-xs tracking-[0.2em] text-neon">Newsletter</h4>
                        <p class="text-slate-400 text-sm mb-4">Dapatkan tips lari eksklusif dan info event terbaru.</p>
                        <form id="newsletter-form" class="flex flex-col gap-3" onsubmit="event.preventDefault(); subscribeNewsletter();">
                            <input type="email" id="newsletter-email" name="email" placeholder="Email Anda" class="bg-slate-900 text-white text-sm px-4 py-3 rounded-xl border border-slate-800 w-full focus:border-neon focus:ring-1 focus:ring-neon outline-none transition placeholder-slate-600" required>
                            <button type="submit" id="newsletter-btn" class="bg-neon text-slate-900 font-black uppercase text-xs tracking-wider px-4 py-3 rounded-xl hover:bg-white transition hover:scale-[1.02] active:scale-95">Subscribe</button>
                            <p id="newsletter-message" class="text-xs mt-1 hidden"></p>
                        </form>
                    </div>
                </div>

                <div class="border-t border-slate-900 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-600 font-medium">
                    <p>&copy; {{ date('Y') }} RuangLari Indonesia. All rights reserved.</p>
                    <div class="flex gap-8">
                        <a href="#" class="hover:text-white transition">Privacy Policy</a>
                        <a href="#" class="hover:text-white transition">Terms of Service</a>
                        <a href="#" class="hover:text-white transition">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </footer>

<script>
function subscribeNewsletter() {
    const email = document.getElementById('newsletter-email').value;
    const btn = document.getElementById('newsletter-btn');
    const msg = document.getElementById('newsletter-message');
    
    // Disable button
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Loading...';
    
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
        msg.classList.remove('hidden');
        if (data.success) {
            msg.className = 'text-xs mt-1 text-green-400';
            msg.textContent = data.message;
            document.getElementById('newsletter-email').value = '';
        } else {
            msg.className = 'text-xs mt-1 text-red-400';
            msg.textContent = data.message;
        }
    })
    .catch(error => {
        console.error(error);
        msg.classList.remove('hidden');
        msg.className = 'text-xs mt-1 text-red-400';
        msg.textContent = 'Terjadi kesalahan. Coba lagi nanti.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
