<?php
    $siteTitle = \App\Models\AppSettings::get('site_title', 'RuangLari Indonesia');
    $siteTagline = \App\Models\AppSettings::get('site_tagline', 'RuangLari adalah portal lari & media race event No.1 di Indonesia. Kami menyajikan berita lari terkini, program latihan lari terstruktur (Daniels VDOT), kalender event marathon terlengkap, serta marketplace gear lari.');
    $socialInsta = \App\Models\AppSettings::get('social_instagram', 'https://www.instagram.com/ruanglaricom/');
    $socialTiktok = \App\Models\AppSettings::get('social_tiktok', 'https://www.tiktok.com/@ruanglaricom');
    $socialFb = \App\Models\AppSettings::get('social_facebook', 'https://www.facebook.com/ruanglari');
    $socialYt = \App\Models\AppSettings::get('social_youtube');
?>

<footer class="bg-slate-950 border-t border-slate-900 pt-16 pb-10 text-slate-400 font-sans" aria-label="Footer RuangLari">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-16">
            
            <!-- Column 1: Brand & Purpose Statement (2 Cols width on desktop) -->
            <div class="lg:col-span-2 space-y-4">
                <a href="<?php echo e(url('/')); ?>" class="inline-flex items-center gap-2.5 group">
                    <img src="<?php echo e(asset('images/logo saja ruang lari.png')); ?>" alt="<?php echo e($siteTitle); ?> Logo" class="h-8 w-auto group-hover:scale-105 transition duration-300">
                    <span class="text-2xl font-black italic tracking-tighter text-white whitespace-nowrap">RUANG<span class="pl-1 text-neon">LARI</span></span>
                </a>
                
                <p class="text-slate-400 text-sm leading-relaxed max-w-md">
                    <?php echo e($siteTagline); ?>

                </p>

                <!-- Key SEO Badges -->
                <div class="flex flex-wrap gap-2 pt-2">
                    <span class="text-[11px] font-medium px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-slate-300 flex items-center gap-1.5"><i class="fas fa-bolt text-neon text-[10px]"></i> Daniels VDOT</span>
                    <span class="text-[11px] font-medium px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-slate-300 flex items-center gap-1.5"><i class="fas fa-running text-neon text-[10px]"></i> Cari Teman Lari</span>
                    <span class="text-[11px] font-medium px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-slate-300 flex items-center gap-1.5"><i class="far fa-calendar-alt text-neon text-[10px]"></i> Race Calendar</span>
                    <span class="text-[11px] font-medium px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-slate-300 flex items-center gap-1.5"><i class="fas fa-robot text-neon text-[10px]"></i> AI Biomechanics</span>
                </div>

                <!-- Social Media -->
                <div class="flex gap-3 pt-3">
                    <?php if($socialInsta): ?>
                    <a href="<?php echo e($socialInsta); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram RuangLari" class="w-9 h-9 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-950 transition"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if($socialTiktok): ?>
                    <a href="<?php echo e($socialTiktok); ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok RuangLari" class="w-9 h-9 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-950 transition"><i class="fab fa-tiktok"></i></a>
                    <?php endif; ?>
                    <?php if($socialFb): ?>
                    <a href="<?php echo e($socialFb); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook RuangLari" class="w-9 h-9 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-950 transition"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if($socialYt): ?>
                    <a href="<?php echo e($socialYt); ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube RuangLari" class="w-9 h-9 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-950 transition"><i class="fab fa-youtube"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Column 2: Program & Fitur Lari (SEO Keyword Rich Links) -->
            <div>
                <h3 class="text-white font-bold mb-5 uppercase text-xs tracking-widest text-neon">Program & Fitur Lari</h3>
                <ul class="space-y-2.5 text-sm font-medium">
                    <li><a href="<?php echo e(route('programs.index')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">Katalog Program VDOT</a></li>
                    <li><a href="<?php echo e(route('landing.program-lari-5k-pemula')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">Program Lari 5K Pemula</a></li>
                    <li><a href="<?php echo e(route('landing.program-lari-10k')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">Program Lari 10K Speed</a></li>
                    <li><a href="<?php echo e(route('landing.program-half-marathon')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">Program Half Marathon</a></li>
                    <li><a href="<?php echo e(route('run-connect.index')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">Cari Teman Lari (Connect)</a></li>
                    <li><a href="<?php echo e(route('coaches.index')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">Pelatih & Coach Lari</a></li>
                </ul>
            </div>

            <!-- Column 3: Event & Komunitas Lari -->
            <div>
                <h3 class="text-white font-bold mb-5 uppercase text-xs tracking-widest text-neon">Event & Komunitas</h3>
                <ul class="space-y-2.5 text-sm font-medium">
                    <li><a href="<?php echo e(route('events.index')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">Jadwal Event Lari 2026</a></li>
                    <li><a href="<?php echo e(route('community.register.index')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">Direktori Komunitas Lari</a></li>
                    <li><a href="<?php echo e(route('marketplace.index')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">Marketplace Shoes & Gear</a></li>
                    <li><a href="<?php echo e(route('gpx.index')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">Database GPX</a></li>
                    <li><a href="<?php echo e(route('register')); ?>?role=eo" class="hover:text-white hover:translate-x-1 inline-block transition">Registrasi Organizer (EO)</a></li>
                    <li><a href="<?php echo e(route('vcard.index')); ?>" class="hover:text-white hover:translate-x-1 inline-block transition">RuangLari Digital V-Card</a></li>
                </ul>
            </div>

            <!-- Column 4: Newsletter & Edukasi SEO -->
            <div>
                <h3 class="text-white font-bold mb-5 uppercase text-xs tracking-widest text-neon">Tips Lari & Updates</h3>
                <p class="text-xs text-slate-400 mb-4 leading-relaxed">
                    Dapatkan update event lari terbaru, tips pace VDOT, dan artikel latihan marathon langsung di inbox email Anda.
                </p>
                <form id="newsletter-form" class="flex flex-col gap-2.5" onsubmit="event.preventDefault(); subscribeNewsletter();">
                    <input type="email" id="newsletter-email" name="email" placeholder="Email Anda..." class="bg-slate-900 text-white text-xs px-3.5 py-2.5 rounded-lg border border-slate-800 w-full focus:border-neon focus:ring-1 focus:ring-neon outline-none transition placeholder-slate-600" required>
                    <button type="submit" id="newsletter-btn" class="bg-neon text-slate-950 font-bold uppercase text-xs tracking-wider px-3.5 py-2.5 rounded-lg hover:bg-white transition">Subscribe Newsletter</button>
                    <p id="newsletter-message" class="text-xs mt-1 hidden"></p>
                </form>

                <div class="pt-4 border-t border-slate-900 mt-4 flex items-center justify-between text-xs text-slate-400">
                    <a href="<?php echo e(url('/blog')); ?>" class="hover:text-white transition font-medium">Blog & Artikel Lari</a>
                    <span>•</span>
                    <a href="<?php echo e(route('about')); ?>" class="hover:text-white transition font-medium">Tentang RuangLari</a>
                </div>
            </div>

        </div>

        <!-- Footer Bottom Copyright & Policy -->
        <div class="border-t border-slate-900 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500 font-medium">
            <p>&copy; <?php echo e(date('Y')); ?> RuangLari Indonesia. Ekosistem Pelari & Event Lari No.1 Indonesia. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-6">
                <a href="<?php echo e(route('home')); ?>#privacy" class="hover:text-slate-300 transition">Kebijakan Privasi</a>
                <a href="<?php echo e(route('home')); ?>#terms" class="hover:text-slate-300 transition">Syarat & Ketentuan</a>
                <a href="<?php echo e(route('home')); ?>#faq" class="hover:text-slate-300 transition">FaQ / Bantuan</a>
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
    
    fetch('<?php echo e(route("newsletter.subscribe")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (msg) {
            msg.classList.remove('hidden');
            if (data.success) {
                msg.className = 'text-xs mt-1 text-emerald-400';
                msg.textContent = data.message;
                emailInput.value = '';
            } else {
                msg.className = 'text-xs mt-1 text-red-400';
                msg.textContent = data.message;
            }
        }
    })
    .catch(error => {
        console.error(error);
        if (msg) {
            msg.classList.remove('hidden');
            msg.className = 'text-xs mt-1 text-red-400';
            msg.textContent = 'Terjadi kesalahan. Coba lagi nanti.';
        }
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
<?php /**PATH C:\laragon\www\ruanglari\resources\views/layouts/components/pacerhub-footer.blade.php ENDPATH**/ ?>