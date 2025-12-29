        <footer class="bg-slate-950 border-t border-slate-900 pt-20 pb-10">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                    <div class="col-span-1 md:col-span-1">
                        <a href="https://ruanglari.com/" class="flex items-center gap-2 mb-6 group">
                            <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="RuangLari" class="h-8 w-auto group-hover:scale-110 transition duration-300">
                            <span class="text-2xl font-black italic tracking-tighter text-white">RUANG<span class="text-neon">LARI</span></span>
                        </a>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            Platform komunitas lari terbesar di Indonesia. Temukan pacer, pantau progres, dan raih personal best Anda bersama kami.
                        </p>
                        <div class="flex gap-4">
                            <a href="https://www.instagram.com/ruanglaricom/" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-900 transition"><i class="fab fa-instagram"></i></a>
                            <a href="https://www.tiktok.com/@ruanglaricom" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-900 transition"><i class="fab fa-tiktok"></i></a>
                            <a href="https://www.facebook.com/ruanglari" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-neon hover:text-slate-900 transition"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-6 uppercase text-xs tracking-[0.2em] text-neon">Explore</h4>
                        <ul class="space-y-3 text-slate-400 text-sm font-medium">
                            <li><a href="#" class="hover:text-white hover:translate-x-1 inline-block transition">Cari Pacer</a></li>
                            <li><a href="#" class="hover:text-white hover:translate-x-1 inline-block transition">Event Kalender</a></li>
                            <li><a href="#" class="hover:text-white hover:translate-x-1 inline-block transition">Leaderboard</a></li>
                            <li><a href="#" class="hover:text-white hover:translate-x-1 inline-block transition">Running Calculator</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-6 uppercase text-xs tracking-[0.2em] text-neon">Community</h4>
                        <ul class="space-y-3 text-slate-400 text-sm font-medium">
                            <li><a href="#" class="hover:text-white hover:translate-x-1 inline-block transition">Tentang Kami</a></li>
                            <li><a href="#" class="hover:text-white hover:translate-x-1 inline-block transition">Menjadi Pacer</a></li>
                            <li><a href="https://ruanglari.com/blog" class="hover:text-white hover:translate-x-1 inline-block transition">Blog & Tips</a></li>
                            <li><a href="#" class="hover:text-white hover:translate-x-1 inline-block transition">FaQ</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-6 uppercase text-xs tracking-[0.2em] text-neon">Newsletter</h4>
                        <p class="text-slate-400 text-sm mb-4">Dapatkan tips lari eksklusif dan info event terbaru.</p>
                        <form class="flex flex-col gap-3">
                            <input type="email" placeholder="Email Anda" class="bg-slate-900 text-white text-sm px-4 py-3 rounded-xl border border-slate-800 w-full focus:border-neon focus:ring-1 focus:ring-neon outline-none transition placeholder-slate-600">
                            <button class="bg-neon text-slate-900 font-black uppercase text-xs tracking-wider px-4 py-3 rounded-xl hover:bg-white transition hover:scale-[1.02] active:scale-95">Subscribe</button>
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
