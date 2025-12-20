        <nav class="border-b border-slate-800 backdrop-blur-md fixed w-full z-40 bg-dark/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <div class="flex items-center gap-2">
                        <!-- Logo PacerHub replaced with RuangLari Green -->
                        <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="RuangLari" class="h-8 w-auto">
                        
                        <a href="https://app.ruanglari.com" class="text-2xl font-black italic tracking-tighter flex items-center gap-2">                            
                            RUANG<span class="text-primary">LARI</span>
                        </a>
                    </div>
                    
                    <div class="hidden md:flex items-center gap-2">
                        <a href="{{ route('pacer.index') }}" class="px-3 py-2 text-sm font-medium hover:text-neon transition">Pacers</a>
                        <a href="{{ route('pacer.index') }}#register" class="px-3 py-2 text-sm font-medium hover:text-neon transition">Registrasi</a>
                        <a href="{{ route('pacer.register') }}" class="px-3 py-2 text-sm font-bold text-neon border border-neon rounded hover:bg-neon hover:text-dark transition">Daftar Pacer</a>
                    </div>
                </div>
            </div>
        </nav>
