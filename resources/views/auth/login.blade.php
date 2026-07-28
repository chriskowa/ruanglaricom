<!DOCTYPE html>
<html lang="id" class="h-full bg-[#09090b]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - RuangLari</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @php($recaptchaSiteKey = config('services.recaptcha.site_key') ?: (env('RECAPTCHA_SITE_KEY_v3') ?: env('RECAPTCHA_SITE_KEY')))
    @if($recaptchaSiteKey)
        <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
    @endif

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        .zoom-bg {
            transition: transform 6s ease-out, opacity 1.2s ease-in-out;
        }
    </style>
</head>

<body class="h-full bg-[#09090b] text-zinc-100 font-sans antialiased selection:bg-zinc-800 selection:text-white">
    
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">
        
        <!-- Left Showcase Column with Ken Burns Zoom Background Slideshow -->
        <div class="lg:col-span-5 hidden lg:flex flex-col justify-between p-12 relative overflow-hidden bg-zinc-950 border-r border-zinc-800/80"
             x-data="heroSlideshow()">
            
            <!-- Background Image Slideshow Container -->
            <div class="absolute inset-0 z-0">
                <template x-for="(slide, index) in slides" :key="index">
                    <div class="absolute inset-0 zoom-bg bg-cover bg-center"
                         :style="`background-image: url('${slide.image}');`"
                         :class="{
                             'opacity-100 scale-110': activeSlide === index,
                             'opacity-0 scale-100': activeSlide !== index
                         }">
                    </div>
                </template>
                <!-- Dark Gradient Overlay for Maximum Text Contrast & Readability -->
                <div class="absolute inset-0 bg-gradient-to-t from-[#09090b] via-[#09090b]/85 to-[#09090b]/65"></div>
                <div class="absolute inset-0 bg-black/30"></div>
            </div>

            <!-- Top Branding -->
            <div class="relative z-10">
                <a href="{{ route('home') }}" class="inline-block">
                    <span class="text-2xl font-black tracking-tight text-white italic">
                        RUANG<span class="text-zinc-400">LARI</span>
                    </span>
                </a>
            </div>

            <!-- Middle Content / Value Propositions (Dynamic Slide Text) -->
            <div class="my-auto py-12 max-w-md relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-zinc-900/80 border border-zinc-700/60 text-xs text-zinc-300 font-medium mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Ekosistem Lari Terintegrasi
                </div>
                
                <h2 class="text-3xl font-extrabold text-white tracking-tight leading-tight mb-4 min-h-[72px] transition-all duration-500"
                    x-text="slides[activeSlide].title">
                </h2>
                
                <p class="text-zinc-300 text-sm leading-relaxed mb-8 min-h-[48px] transition-all duration-500"
                   x-text="slides[activeSlide].subtitle">
                </p>

                <!-- Features List -->
                <div class="space-y-3.5 border-t border-zinc-800/80 pt-6">
                    <div class="flex items-start gap-3">
                        <div class="p-1.5 rounded-md bg-zinc-900/90 text-zinc-300 border border-zinc-800 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-zinc-200">Daniels VDOT Training Plans</h4>
                            <p class="text-xs text-zinc-400">Target pace terstruktur sesuai kapasitas fisik Anda.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="p-1.5 rounded-md bg-zinc-900/90 text-zinc-300 border border-zinc-800 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-zinc-200">Cari Teman Lari & Running Connect</h4>
                            <p class="text-xs text-zinc-400">Temukan pelari di sekitar Anda dengan lokasi GPS real-time.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="p-1.5 rounded-md bg-zinc-900/90 text-zinc-300 border border-zinc-800 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-zinc-200">Jadwal Event Lari Indonesia</h4>
                            <p class="text-xs text-zinc-400">Informasi lengkap event lari 5K, 10K, Half & Full Marathon.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Progress Dots & Footer -->
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-4">
                    <template x-for="(slide, index) in slides" :key="index">
                        <button type="button" @click="activeSlide = index"
                                class="h-1 rounded-full transition-all duration-300"
                                :class="activeSlide === index ? 'w-8 bg-white' : 'w-2 bg-zinc-700 hover:bg-zinc-500'">
                        </button>
                    </template>
                </div>
                <div class="text-xs text-zinc-500">
                    &copy; {{ date('Y') }} RuangLari. Hak cipta dilindungi undang-undang.
                </div>
            </div>
        </div>

        <!-- Right Login Form Column -->
        <div class="lg:col-span-7 flex flex-col justify-center items-center p-6 md:p-12 lg:p-16">
            
            <div class="w-full max-w-md mx-auto" x-data="loginPage()">
                
                <!-- Mobile Brand Header -->
                <div class="lg:hidden text-center mb-8">
                    <a href="{{ route('home') }}" class="inline-block">
                        <span class="text-2xl font-black tracking-tight text-white italic">
                            RUANG<span class="text-zinc-400">LARI</span>
                        </span>
                    </a>
                </div>

                <!-- Section Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-white tracking-tight">Selamat Datang Kembali</h1>
                    <p class="text-zinc-400 text-sm mt-1">Masuk ke akun RuangLari Anda untuk melanjutkan latihan.</p>
                </div>

                <!-- Tab Segmented Control -->
                <div class="grid grid-cols-2 p-1 bg-zinc-900 border border-zinc-800 rounded-xl mb-6">
                    <button type="button" @click="loginMethod = 'email'" :class="loginMethod === 'email' ? 'bg-zinc-800 text-white font-semibold shadow-sm' : 'text-zinc-400 hover:text-zinc-200'" class="py-2 text-xs rounded-lg transition-all text-center">
                        Email / Username
                    </button>
                    <button type="button" @click="loginMethod = 'phone'" :class="loginMethod === 'phone' ? 'bg-zinc-800 text-white font-semibold shadow-sm' : 'text-zinc-400 hover:text-zinc-200'" class="py-2 text-xs rounded-lg transition-all text-center">
                        WhatsApp OTP
                    </button>
                </div>

                <!-- Alert Messages -->
                <div x-show="errorMessage" x-text="errorMessage" class="mb-6 p-4 rounded-xl bg-red-950/40 border border-red-800/60 text-red-200 text-xs" x-cloak></div>
                <div x-show="successMessage" x-text="successMessage" class="mb-6 p-4 rounded-xl bg-emerald-950/40 border border-emerald-800/60 text-emerald-200 text-xs" x-cloak></div>

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-950/40 border border-red-800/60 text-red-200 text-xs">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-950/40 border border-emerald-800/60 text-emerald-200 text-xs">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-950/40 border border-red-800/60 text-red-200 text-xs" x-show="loginMethod === 'email'">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Email / Username Login Form -->
                <form id="email-login-form" method="POST" action="{{ route('login') }}" class="space-y-4" x-show="loginMethod === 'email'">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Email atau Username</label>
                        <input type="text" name="email" value="{{ request('email', old('email')) }}" class="w-full px-4 py-2.5 rounded-lg bg-zinc-900/80 border border-zinc-800 text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="nama@email.com atau username" required autofocus>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-medium text-zinc-300">Password</label>
                            <a href="{{ route('password.request') }}" class="text-xs text-zinc-400 hover:text-white transition-colors">Lupa Password?</a>
                        </div>
                        <input type="password" name="password" value="{{ request('password') }}" class="w-full px-4 py-2.5 rounded-lg bg-zinc-900/80 border border-zinc-800 text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="••••••••" required>
                    </div>

                    <div class="flex items-center pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-zinc-400 hover:text-zinc-200 transition-colors">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-zinc-100 focus:ring-zinc-700">
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                    <div class="pt-2">
                        <button type="submit" id="login-btn" class="w-full py-3 px-4 rounded-lg bg-white hover:bg-zinc-200 text-zinc-950 font-semibold text-sm transition-all flex items-center justify-center gap-2 shadow-sm">
                            <span id="btn-text">Masuk</span>
                            <span id="btn-loader" class="hidden w-4 h-4 border-2 border-zinc-950 border-t-transparent rounded-full animate-spin"></span>
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-zinc-800"></div>
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="px-3 bg-[#09090b] text-zinc-500 font-medium">Atau masuk dengan</span>
                        </div>
                    </div>

                    <!-- Social Login Buttons -->
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('auth.google') }}" class="py-2.5 px-4 rounded-lg border border-zinc-800 bg-zinc-900/60 hover:bg-zinc-800 text-zinc-200 font-medium text-xs transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            Google
                        </a>

                        <a href="{{ route('auth.strava') }}" class="py-2.5 px-4 rounded-lg border border-orange-500/30 bg-orange-500/10 hover:bg-orange-500/20 text-orange-400 font-medium text-xs transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 fill-current text-[#FC4C02]" viewBox="0 0 24 24">
                                <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7.92 15.6h4.172"/>
                            </svg>
                            Strava
                        </a>
                    </div>
                </form>

                <!-- Phone WhatsApp OTP Login Form -->
                <div class="space-y-4" x-show="loginMethod === 'phone'" x-cloak>
                    <!-- Step 1: Request OTP -->
                    <div x-show="!otpSent" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1.5">Nomor WhatsApp</label>
                            <input type="tel" x-model="phone" class="w-full px-4 py-2.5 rounded-lg bg-zinc-900/80 border border-zinc-800 text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="08123456789" required>
                        </div>
                        <button type="button" @click="requestOtp" :disabled="loading" class="w-full py-3 px-4 rounded-lg bg-white hover:bg-zinc-200 text-zinc-950 font-semibold text-sm transition-all flex items-center justify-center gap-2 shadow-sm">
                            <span x-show="!loading">Kirim OTP WhatsApp</span>
                            <span x-show="loading" class="w-4 h-4 border-2 border-zinc-950 border-t-transparent rounded-full animate-spin"></span>
                        </button>
                    </div>

                    <!-- Step 2: Verify OTP -->
                    <div x-show="otpSent" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1.5 text-center">Masukkan Kode OTP</label>
                            <input type="text" x-model="otpCode" maxlength="6" class="w-full px-4 py-3 rounded-lg bg-zinc-900 border border-zinc-800 text-white placeholder-zinc-500 text-center tracking-widest font-mono text-xl focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="••••••" required>
                        </div>
                        <button type="button" @click="verifyOtp" :disabled="loading" class="w-full py-3 px-4 rounded-lg bg-white hover:bg-zinc-200 text-zinc-950 font-semibold text-sm transition-all flex items-center justify-center gap-2 shadow-sm">
                            <span x-show="!loading">Verifikasi & Masuk</span>
                            <span x-show="loading" class="w-4 h-4 border-2 border-zinc-950 border-t-transparent rounded-full animate-spin"></span>
                        </button>
                        <div class="text-center pt-2">
                            <button type="button" @click="otpSent = false" class="text-xs text-zinc-400 hover:text-white underline transition-colors">Ubah Nomor WhatsApp</button>
                        </div>
                    </div>
                </div>

                <!-- Footer Join Link -->
                <div class="mt-8 pt-6 border-t border-zinc-800/80 text-center">
                    <p class="text-zinc-400 text-xs">
                        Belum memiliki akun RuangLari? 
                        <a href="{{ route('register') }}" class="text-white hover:underline font-semibold ml-1">Daftar Sekarang</a>
                    </p>
                </div>

            </div>

        </div>

    </div>

    <!-- Alpine.js Logic -->
    <script>
        function heroSlideshow() {
            return {
                activeSlide: 0,
                slides: [
                    {
                        image: '{{ asset("images/run_social_bg.png") }}',
                        title: 'Tingkatkan Performa & Pengalaman Lari Anda.',
                        subtitle: 'Akses program latihan terstruktur, analisis bentuk lari berbasis AI, & komunitas lari Indonesia.'
                    },
                    {
                        image: '{{ asset("images/hero/jadwal-lari.webp") }}',
                        title: 'Temukan Jadwal Race Event Lari Indonesia.',
                        subtitle: 'Informasi terlengkap 5K, 10K, Half & Full Marathon dari berbagai kota di Indonesia.'
                    },
                    {
                        image: '{{ asset("images/form-analyzer.webp") }}',
                        title: 'Running Connect & AI Form Analysis.',
                        subtitle: 'Temukan teman lari terdekat berdasarkan GPS real-time & tingkatkan efisiensi langkah lari Anda.'
                    }
                ],
                init() {
                    setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % this.slides.length;
                    }, 5000);
                }
            };
        }

        function loginPage() {
            return {
                loginMethod: 'email',
                phone: '',
                otpCode: '',
                otpSent: false,
                loading: false,
                errorMessage: '',
                successMessage: '',
                userId: null,

                async requestOtp() {
                    if (!this.phone) {
                        this.errorMessage = 'Nomor WhatsApp wajib diisi.';
                        return;
                    }
                    this.loading = true;
                    this.errorMessage = '';
                    this.successMessage = '';

                    try {
                        const response = await fetch('{{ route('login.phone.request') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ phone: this.phone })
                        });
                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.otpSent = true;
                            this.userId = data.user_id;
                            this.successMessage = data.message;
                        } else {
                            this.errorMessage = data.message || 'Gagal mengirim OTP.';
                        }
                    } catch (err) {
                        this.errorMessage = 'Terjadi kesalahan koneksi.';
                    } finally {
                        this.loading = false;
                    }
                },

                async verifyOtp() {
                    if (!this.otpCode) {
                        this.errorMessage = 'Kode OTP wajib diisi.';
                        return;
                    }
                    this.loading = true;
                    this.errorMessage = '';
                    this.successMessage = '';

                    try {
                        const response = await fetch('{{ route('login.phone.verify') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                user_id: this.userId,
                                code: this.otpCode
                            })
                        });
                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.successMessage = data.message;
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            this.errorMessage = data.message || 'Kode OTP salah.';
                        }
                    } catch (err) {
                        this.errorMessage = 'Terjadi kesalahan koneksi.';
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('email-login-form');
            const loginBtn = document.getElementById('login-btn');
            const btnText = document.getElementById('btn-text');
            const btnLoader = document.getElementById('btn-loader');

            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    @if($recaptchaSiteKey)
                        e.preventDefault();
                        
                        if (loginBtn.disabled) return;
                        loginBtn.disabled = true;
                        btnText.textContent = 'Memproses...';
                        btnLoader.classList.remove('hidden');

                        if (typeof grecaptcha === 'undefined') {
                            console.warn('reCAPTCHA tidak dapat dimuat.');
                            loginForm.submit();
                            return;
                        }

                        grecaptcha.ready(function() {
                            grecaptcha.execute('{{ $recaptchaSiteKey }}', {action: 'login'})
                            .then(function(token) {
                                const el = document.getElementById('g-recaptcha-response');
                                if (el) el.value = token;
                                loginForm.submit();
                            })
                            .catch(function(err) {
                                console.error('reCAPTCHA error:', err);
                                loginForm.submit();
                            });
                        });
                    @else
                        if (loginBtn.disabled) return;
                        loginBtn.disabled = true;
                        btnText.textContent = 'Memproses...';
                        btnLoader.classList.remove('hidden');
                    @endif
                });
            }
        });
    </script>
</body>
</html>
