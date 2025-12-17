        <footer class="bg-slate-900 border-t border-slate-800 pt-16 pb-8">
            <div class="max-w-7xl mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                    <div class="col-span-1 md:col-span-1">
                        <div class="flex items-center gap-2 mb-4">
                            <!-- Using the logo here too for consistency, or keep text -->
                            <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="RuangLari" class="h-6 w-auto">
                            <span class="text-xl font-bold">RUANG<span class="text-neon"> LARI</span></span>
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Platform komunitas lari terbesar untuk menemukan pacer profesional. Lari lebih cepat, lari lebih jauh, lari bersama kami.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Explore</h4>
                        <ul class="space-y-2 text-slate-400 text-sm">
                            <li><a href="#" class="hover:text-neon transition">Cari Pacer</a></li>
                            <li><a href="#" class="hover:text-neon transition">Event Kalender</a></li>
                            <li><a href="#" class="hover:text-neon transition">Leaderboard</a></li>
                            <li><a href="#" class="hover:text-neon transition">Running Calculator</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Community</h4>
                        <ul class="space-y-2 text-slate-400 text-sm">
                            <li><a href="#" class="hover:text-neon transition">Tentang Kami</a></li>
                            <li><a href="#" class="hover:text-neon transition">Menjadi Pacer</a></li>
                            <li><a href="#" class="hover:text-neon transition">Blog & Tips</a></li>
                            <li><a href="#" class="hover:text-neon transition">FaQ</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Stay Updated</h4>
                        <p class="text-slate-400 text-xs mb-4">Dapatkan tips lari dan info event terbaru.</p>
                        <div class="flex gap-2">
                            <input type="email" placeholder="Email Anda" class="bg-slate-800 text-white text-sm px-4 py-2 rounded-lg border border-slate-700 w-full focus:border-neon outline-none">
                            <button class="bg-neon text-dark font-bold px-4 py-2 rounded-lg hover:bg-white transition">→</button>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-slate-500">
                    <p>&copy; {{ date('Y') }} RuangLari Indonesia. All rights reserved.</p>
                    <div class="flex gap-6 mt-4 md:mt-0">
                        <a href="#" class="hover:text-white">Privacy Policy</a>
                        <a href="#" class="hover:text-white">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>
