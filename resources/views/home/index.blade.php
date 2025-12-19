@extends('layouts.pacerhub')

@section('content')
    <div id="app">
        <header class="relative min-h-screen flex items-center justify-center overflow-hidden pt-20">
            <div class="absolute inset-0 bg-hero-glow"></div>
            <div class="absolute top-1/4 -left-20 w-72 h-72 bg-primary/10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary/10 rounded-full blur-[100px]"></div>
            <div class="max-w-7xl mx-auto px-4 relative z-10 grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-primary/30 bg-primary/10 text-primary text-xs font-bold mb-6">
                        <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span></span>
                        Komunitas Lari #1 Indonesia
                    </div>
                    <h1 class="text-6xl md:text-8xl font-black leading-tight mb-6">
                        LARI TANPA <br>
                        <span class="text-transparent bg-clip-text text-gradient-primary">BATAS.</span>
                    </h1>
                    <p class="text-slate-400 text-lg mb-8 max-w-md leading-relaxed">
                        Platform all-in-one untuk pelari, pacer, dan pelatih. Temukan event, pantau progres, dan raih personal best Anda bersama Ruang Lari.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/register" class="px-8 py-4 btn-neon rounded-full font-black text-dark">Mulai Sekarang</a>
                        <a href="#events" class="px-8 py-4 border border-slate-700 rounded-full font-bold hover:bg-white/5 hover:border-primary/50 hover:text-primary transition flex items-center justify-center gap-2">Lihat Event</a>
                    </div>
                    <div class="mt-12 flex items-center gap-4">
                        <div class="flex -space-x-3">
                            <img class="w-10 h-10 rounded-full border-2 border-dark" src="https://i.pravatar.cc/100?img=11" alt="">
                            <img class="w-10 h-10 rounded-full border-2 border-dark" src="https://i.pravatar.cc/100?img=12" alt="">
                            <img class="w-10 h-10 rounded-full border-2 border-dark" src="https://i.pravatar.cc/100?img=33" alt="">
                            <div class="w-10 h-10 rounded-full border-2 border-dark bg-card flex items-center justify-center text-xs font-bold text-primary">+2k</div>
                        </div>
                        <p class="text-sm text-slate-500">Pelari telah bergabung</p>
                    </div>
                </div>
                <div class="relative">
                    <div class="relative z-10 rounded-[3rem] overflow-hidden border-8 border-card shadow-2xl rotate-3">
                        <img src="https://res.cloudinary.com/dslfarxct/images/v1766050868/542301374_18517775974013478_1186867397282832240_n/542301374_18517775974013478_1186867397282832240_n.jpg" alt="Runner" class="w-auto h-50 object-cover">
                    </div>
                    
                    <div class="absolute top-1/2 -translate-y-1/2 -right-10 w-28 h-28 bg-primary rounded-full flex items-center justify-center text-dark font-black text-center text-xs p-2 rotate-12 z-20 animate-bounce shadow-[0_0_30px_#ccff00]">
                        <a href="{{ url('/register') }}">
                            JOIN<br>NOW
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <section class="py-10 border-y border-white/5 bg-card/30">
            <div class="max-w-7xl mx-auto px-4 flex flex-wrap justify-center md:justify-between items-center gap-8 opacity-40 grayscale">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ea/New_Balance_logo.svg/2560px-New_Balance_logo.svg.png" class="h-6 md:h-8 invert">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Logo_NIKE.svg/1200px-Logo_NIKE.svg.png" class="h-6 md:h-8 invert">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/20/Adidas_Logo.svg/2560px-Adidas_Logo.svg.png" class="h-8 md:h-10 invert">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Under_armour_logo.svg/2560px-Under_armour_logo.svg.png" class="h-6 md:h-8 invert">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/33/Reebok_logo19.svg/2560px-Reebok_logo19.svg.png" class="h-6 md:h-8 invert">
            </div>
        </section>

        <section id="events" class="py-24 bg-card clip-path-slant pb-40 border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12">
                    <div>
                        <h2 class="text-4xl font-black text-white">RACE CALENDAR</h2>
                        <p class="text-slate-400 mt-2">Jangan lewatkan event lari terbesar tahun ini.</p>
                    </div>
                    <div class="flex gap-2 mt-4 md:mt-0 bg-dark p-1 rounded-lg border border-slate-700">
                        <a href="/events" class="px-6 py-2 rounded-md text-sm font-bold transition bg-primary text-dark">Lihat Semua</a>
                    </div>
                </div>
                <div id="homeEvents" class="grid grid-cols-1 lg:grid-cols-2 gap-6"></div>
                <div class="text-center mt-12"><a href="{{ url('/events') }}" class="inline-block border-b-2 border-primary text-primary font-bold hover:text-white hover:border-white transition pb-1">Lihat Semua Event →</a></div>
            </div>
        </section>

        <section id="community" class="py-24 relative">
            <div class="max-w-7xl mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-neon font-mono font-bold tracking-widest uppercase mb-2">Community Elite</h2>
                    <h3 class="text-4xl md:text-5xl font-black text-white">HALL OF <span class="text-stroke">FAME</span></h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="group bg-card border border-slate-700 rounded-3xl p-2 relative overflow-hidden card-hover transition duration-300">
                        <div class="absolute top-0 left-0 bg-neon text-dark text-xs font-bold px-4 py-2 rounded-br-2xl z-20">TOP RUNNER</div>
                        <div class="h-64 rounded-2xl overflow-hidden mb-4 relative">
                            <img src="https://images.unsplash.com/photo-1596727147705-0043c7576566?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover">
                            <div class="absolute bottom-4 left-4">
                                <h4 class="text-2xl font-black uppercase italic drop-shadow-md">Sarah <br>Jenner</h4>
                            </div>
                        </div>
                        <div class="px-4 pb-4">
                            <div class="flex justify-between items-end border-b border-white/10 pb-4 mb-4">
                                <div>
                                    <p class="text-slate-400 text-xs uppercase">Total Distance</p>
                                    <p class="text-xl font-mono font-bold text-white">2,450 KM</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-slate-400 text-xs uppercase">Avg Pace</p>
                                    <p class="text-xl font-mono font-bold text-neon">4:15</p>
                                </div>
                            </div>
                            <a href="{{ route('pacer.index') }}" class="w-full block text-center py-3 rounded-xl border border-slate-700 hover:bg-neon hover:text-dark hover:border-neon transition font-bold text-sm">Lihat Profil</a>
                        </div>
                    </div>

                    <div class="group bg-card border border-slate-700 rounded-3xl p-2 relative overflow-hidden card-hover transition duration-300">
                        <div class="absolute top-0 left-0 bg-blue-500 text-white text-xs font-bold px-4 py-2 rounded-br-2xl z-20">TOP PACER</div>
                        <div class="h-64 rounded-2xl overflow-hidden mb-4 relative">
                            <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover">
                            <div class="absolute bottom-4 left-4">
                                <h4 class="text-2xl font-black uppercase italic drop-shadow-md">Budi <br>Santoso</h4>
                            </div>
                        </div>
                        <div class="px-4 pb-4">
                            <div class="flex justify-between items-end border-b border-white/10 pb-4 mb-4">
                                <div>
                                    <p class="text-slate-400 text-xs uppercase">Events Paced</p>
                                    <p class="text-xl font-mono font-bold text-white">15 Races</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-slate-400 text-xs uppercase">Precision</p>
                                    <p class="text-xl font-mono font-bold text-blue-400">99.8%</p>
                                </div>
                            </div>
                            <a href="{{ route('pacer.index') }}" class="w-full block text-center py-3 rounded-xl border border-slate-700 hover:bg-neon hover:text-dark hover:border-neon transition font-bold text-sm">Book Pacer</a>
                        </div>
                    </div>

                    <div class="group bg-card border border-slate-700 rounded-3xl p-2 relative overflow-hidden card-hover transition duration-300">
                        <div class="absolute top-0 left-0 bg-white text-dark text-xs font-bold px-4 py-2 rounded-br-2xl z-20">TOP COACH</div>
                        <div class="h-64 rounded-2xl overflow-hidden mb-4 relative">
                            <img src="https://images.unsplash.com/photo-1571008887538-b36bb32f4571?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover">
                            <div class="absolute bottom-4 left-4">
                                <h4 class="text-2xl font-black uppercase italic drop-shadow-md">Coach <br>Indra</h4>
                            </div>
                        </div>
                        <div class="px-4 pb-4">
                            <div class="flex justify-between items-end border-b border-white/10 pb-4 mb-4">
                                <div>
                                    <p class="text-slate-400 text-xs uppercase">Students</p>
                                    <p class="text-xl font-mono font-bold text-white">120+</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-slate-400 text-xs uppercase">PB Broken</p>
                                    <p class="text-xl font-mono font-bold text-white">50+</p>
                                </div>
                            </div>
                            <a href="{{ url('/users?role=coach') }}" class="w-full block text-center py-3 rounded-xl border border-slate-700 hover:bg-neon hover:text-dark hover:border-neon transition font-bold text-sm">Join Class</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="pricing" class="py-24 relative -mt-20">
            <div class="max-w-7xl mx-auto px-4">
                <div class="bg-gradient-to-br from-neon via-green-500 to-emerald-600 rounded-[3rem] p-12 md:p-24 text-center relative overflow-hidden shadow-[0_20px_60px_-15px_rgba(204,255,0,0.3)]">
                    <h2 class="text-3xl md:text-5xl font-black text-dark mb-6">UNLOCK PREMIUM FEATURES</h2>
                    <p class="text-dark/80 text-lg mb-10 max-w-2xl mx-auto font-medium">Dapatkan akses ke rencana latihan eksklusif, analisis performa mendalam, dan diskon pendaftaran event.</p>
                    <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto text-left">
                        <div class="bg-dark/90 backdrop-blur-sm p-8 rounded-3xl border border-white/10 hover:border-neon transition">
                            <h3 class="text-xl font-bold text-white mb-2">Starter</h3>
                            <p class="text-3xl font-black text-white mb-6">Rp 0</p>
                            <ul class="space-y-3 text-sm text-slate-300 mb-8">
                                <li class="flex gap-2"><span class="text-neon">✓</span> Basic Tracking</li>
                                <li class="flex gap-2"><span class="text-neon">✓</span> Community Access</li>
                            </ul>
                            <a href="{{ url('/register') }}" class="w-full block text-center py-3 rounded-xl bg-slate-800 hover:bg-white hover:text-dark font-bold transition text-white">Current Plan</a>
                        </div>
                        <div class="bg-white text-dark p-8 rounded-3xl transform scale-105 shadow-2xl relative">
                            <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-dark text-neon border border-neon text-xs font-bold px-4 py-1 rounded-full uppercase tracking-wider">Most Popular</div>
                            <h3 class="text-xl font-bold mb-2">Pro Runner</h3>
                            <p class="text-3xl font-black mb-6">Rp 49k<span class="text-sm font-medium text-slate-500">/bln</span></p>
                            <ul class="space-y-3 text-sm font-medium mb-8">
                                <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> Advanced Analytics</li>
                                <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> Training Plans</li>
                                <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> Event Discounts</li>
                            </ul>
                            <a href="{{ url('/membership') }}" class="w-full block text-center py-3 rounded-xl bg-neon text-dark hover:brightness-90 font-black transition shadow-lg">Upgrade Now</a>
                        </div>
                        <div class="bg-dark/90 backdrop-blur-sm p-8 rounded-3xl border border-white/10 hover:border-neon transition">
                            <h3 class="text-xl font-bold text-white mb-2">Elite Coach</h3>
                            <p class="text-3xl font-black text-white mb-6">Rp 199k<span class="text-sm text-slate-400">/bln</span></p>
                            <ul class="space-y-3 text-sm text-slate-300 mb-8">
                                <li class="flex gap-2"><span class="text-neon">✓</span> All Pro Features</li>
                                <li class="flex gap-2"><span class="text-neon">✓</span> Manage Students</li>
                                <li class="flex gap-2"><span class="text-neon">✓</span> Verified Badge</li>
                            </ul>
                            <a href="{{ url('/contact') }}" class="w-full block text-center py-3 rounded-xl bg-slate-800 hover:bg-white hover:text-dark font-bold transition text-white">Contact Sales</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="blog" class="py-24 bg-dark">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-end mb-12"><h2 class="text-4xl font-black text-white">LATEST <span class="text-stroke">NEWS</span></h2><a href="https://ruanglari.com/blog/" target="_blank" rel="noopener" class="text-sm font-bold text-primary hover:text-white transition">Read Blog →</a></div>
                <div id="blogCards" class="grid grid-cols-1 md:grid-cols-3 gap-8"></div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
<style>
.text-stroke{ -webkit-text-stroke: 1px rgba(255,255,255,0.2); color: transparent; }
.btn-neon{ background-color:#ccff00; color:#0f172a; }
.bg-hero-glow{ background-image: radial-gradient(circle at center, rgba(204,255,0,0.15) 0%, transparent 70%); }
.text-gradient-primary{ background-image: linear-gradient(90deg,#ccff00,#34d399); }
@keyframes floaty { 0% { transform: translateY(0) rotate(12deg); } 50% { transform: translateY(-8px) rotate(12deg); } 100% { transform: translateY(0) rotate(12deg); } }
.animate-float { animation: floaty 2.8s ease-in-out infinite; }
</style>
@endpush

@push('scripts')
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
AOS.init({duration:800,once:true,offset:100});
async function loadLatestBlogs(){const c=document.getElementById('blogCards');if(!c)return;c.innerHTML='<div class="col-span-3 text-center text-gray-500">Memuat artikel...</div>';try{const r=await fetch('https://ruanglari.com/wp-json/wp/v2/posts?per_page=3&_embed');const p=await r.json();c.innerHTML='';p.forEach(post=>{const l=post.link;const t=post.title?.rendered||'Tanpa judul';const m=post._embedded?.['wp:featuredmedia']?.[0];const i=m?.source_url||'ruanglari.png';const a=document.createElement('a');a.href=l;a.target='_blank';a.rel='noopener';a.className='group block bg-white/5 border border-white/10 rounded-2xl overflow-hidden shadow hover:shadow-lg transition-shadow';a.innerHTML='<div class="overflow-hidden"><img src="'+i+'" alt="'+t.replace(/<[^>]*>/g,'')+'" class="w-full h-56 object-cover transform transition-transform duration-300 group-hover:scale-105" /></div><div class="p-4"><div class="font-semibold text-white line-clamp-2 group-hover:text-primary transition">'+t+'</div></div>';c.appendChild(a);});}catch(e){c.innerHTML='<div class="col-span-3 text-center text-red-500">Gagal memuat artikel.</div>';}}
async function loadUpcomingEvents(){const c=document.getElementById('homeEvents');if(!c)return;c.innerHTML='<div class="col-span-2 text-center text-slate-400">Memuat event...</div>';try{const base=(window.location.pathname.indexOf('/ruanglari/public')!==-1)?'/ruanglari/public':'';const r=await fetch(base+'/api/events/upcoming');const events=await r.json();c.innerHTML='';if(!events||events.length===0){c.innerHTML='<div class="col-span-2 text-center text-slate-500">Belum ada event mendatang.</div>';return;}events.forEach(ev=>{const d=new Date(ev.date+'T'+(ev.time||'00:00'));const m=d.toLocaleString('id-ID',{month:'short'}).toUpperCase();const day=String(d.getDate()).padStart(2,'0');const card=document.createElement('a');card.href='/events/'+ev.slug;card.className='flex bg-dark border border-slate-800 rounded-2xl overflow-hidden hover:border-primary/50 transition group';card.innerHTML='<div class="w-32 bg-slate-900 flex flex-col items-center justify-center text-center p-4 border-r border-slate-800"><span class="text-sm font-bold text-primary uppercase">'+m+'</span><span class="text-3xl font-black text-white">'+day+'</span></div><div class="p-6 flex-grow flex flex-col justify-center"><h3 class="text-xl font-bold text-white mb-1 group-hover:text-primary transition">'+ev.name+'</h3><p class="text-sm text-slate-500">'+(ev.location||'')+' • '+(ev.time||'')+' WIB</p></div><div class="w-16 flex items-center justify-center border-l border-slate-800 bg-slate-900/50"><span class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-white group-hover:bg-primary group-hover:text-dark transition">→</span></div>';c.appendChild(card);});}catch(e){c.innerHTML='<div class="col-span-2 text-center text-red-500">Gagal memuat event.</div>';}}
document.addEventListener('DOMContentLoaded',()=>{loadLatestBlogs();loadUpcomingEvents();});
</script>
@endpush
