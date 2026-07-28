<!DOCTYPE html>
<html lang="id" class="h-full bg-[#09090b]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun Baru - RuangLari</title>
    
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
    
    @php($recaptchaSiteKey = env('RECAPTCHA_SITE_KEY_v3') ?: env('RECAPTCHA_SITE_KEY'))
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
        /* Custom Radio Card Active state */
        .role-radio:checked + .role-card {
            border-color: #f4f4f5;
            background-color: #27272a;
            color: #ffffff;
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
                <!-- Dark Gradient Overlay for Maximum Text Contrast -->
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

            <!-- Middle Content / Value Propositions -->
            <div class="my-auto py-12 max-w-md relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-zinc-900/80 border border-zinc-700/60 text-xs text-zinc-300 font-medium mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Bergabung Dengan Komunitas Pelari
                </div>
                
                <h2 class="text-3xl font-extrabold text-white tracking-tight leading-tight mb-4 min-h-[72px] transition-all duration-500"
                    x-text="slides[activeSlide].title">
                </h2>
                
                <p class="text-zinc-300 text-sm leading-relaxed mb-8 min-h-[48px] transition-all duration-500"
                   x-text="slides[activeSlide].subtitle">
                </p>

                <!-- Benefits List -->
                <div class="space-y-3.5 border-t border-zinc-800/80 pt-6">
                    <div class="flex items-start gap-3">
                        <div class="p-1.5 rounded-md bg-zinc-900/90 text-zinc-300 border border-zinc-800 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-zinc-200">Akses Gratis Program Latihan</h4>
                            <p class="text-xs text-zinc-400">Program latihan Daniels VDOT dari 5K hingga Marathon.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="p-1.5 rounded-md bg-zinc-900/90 text-zinc-300 border border-zinc-800 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-zinc-200">Koneksi Otomatis Strava</h4>
                            <p class="text-xs text-zinc-400">Sinkronkan hasil lari & statistik latihan Anda secara real-time.</p>
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

        <!-- Right Register Form Column -->
        <div class="lg:col-span-7 flex flex-col justify-center items-center p-6 md:p-12 lg:p-16">
            
            <div class="w-full max-w-md mx-auto">
                
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
                    <h1 class="text-2xl font-bold text-white tracking-tight">Buat Akun RuangLari</h1>
                    <p class="text-zinc-400 text-sm mt-1">Lengkapi formulir di bawah untuk memulai perjalanan lari Anda.</p>
                </div>

                <!-- Errors Alert -->
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-950/40 border border-red-800/60 text-red-200 text-xs">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    
                    <!-- Role Selection -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-2">Saya mendaftar sebagai...</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="runner" class="role-radio hidden" {{ old('role', $role ?? 'runner') == 'runner' ? 'checked' : '' }}>
                                <div class="role-card border border-zinc-800 rounded-lg p-2.5 text-center hover:bg-zinc-900 transition-all h-full flex flex-col items-center justify-center gap-1.5">
                                    <svg class="w-5 h-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span class="text-xs font-medium">Runner</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="coach" class="role-radio hidden" {{ old('role', $role ?? '') == 'coach' ? 'checked' : '' }}>
                                <div class="role-card border border-zinc-800 rounded-lg p-2.5 text-center hover:bg-zinc-900 transition-all h-full flex flex-col items-center justify-center gap-1.5">
                                    <svg class="w-5 h-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span class="text-xs font-medium">Coach</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="eo" class="role-radio hidden" {{ old('role', $role ?? '') == 'eo' ? 'checked' : '' }}>
                                <div class="role-card border border-zinc-800 rounded-lg p-2.5 text-center hover:bg-zinc-900 transition-all h-full flex flex-col items-center justify-center gap-1.5">
                                    <svg class="w-5 h-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs font-medium">Organizer</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-2.5 rounded-lg bg-zinc-900/80 border border-zinc-800 text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="Nama Lengkap" required autofocus>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2.5 rounded-lg bg-zinc-900/80 border border-zinc-800 text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="nama@example.com" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1.5">Nomor WhatsApp</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2.5 rounded-lg bg-zinc-900/80 border border-zinc-800 text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="08123456789" required>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1.5">Password</label>
                            <input type="password" name="password" class="w-full px-4 py-2.5 rounded-lg bg-zinc-900/80 border border-zinc-800 text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="••••••••" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1.5">Konfirmasi</label>
                            <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 rounded-lg bg-zinc-900/80 border border-zinc-800 text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="••••••••" required>
                        </div>
                    </div>

                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                    <div class="pt-2">
                        <button type="submit" id="register-btn" class="w-full py-3 px-4 rounded-lg bg-white hover:bg-zinc-200 text-zinc-950 font-semibold text-sm transition-all flex items-center justify-center gap-2 shadow-sm">
                            <span id="btn-text">Daftar Akun Baru</span>
                            <span id="btn-loader" class="hidden w-4 h-4 border-2 border-zinc-950 border-t-transparent rounded-full animate-spin"></span>
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-zinc-800"></div>
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="px-3 bg-[#09090b] text-zinc-500 font-medium">Atau mendaftar dengan</span>
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

                <!-- Footer Sign in Link -->
                <div class="mt-8 pt-6 border-t border-zinc-800/80 text-center">
                    <p class="text-zinc-400 text-xs">
                        Sudah memiliki akun? 
                        <a href="{{ route('login') }}" class="text-white hover:underline font-semibold ml-1">Masuk Sekarang</a>
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

        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.querySelector('form');
            const registerBtn = document.getElementById('register-btn');
            const btnText = document.getElementById('btn-text');
            const btnLoader = document.getElementById('btn-loader');
            const recaptchaInput = document.getElementById('g-recaptcha-response');
            const recaptchaSiteKey = '{{ $recaptchaSiteKey }}';

            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    if (registerBtn.disabled) return;

                    registerBtn.disabled = true;
                    btnText.textContent = 'Memproses...';
                    btnLoader.classList.remove('hidden');

                    if (recaptchaSiteKey && typeof grecaptcha !== 'undefined') {
                        e.preventDefault();
                        grecaptcha.ready(function() {
                            grecaptcha.execute(recaptchaSiteKey, {action: 'register'}).then(function(token) {
                                if (recaptchaInput) recaptchaInput.value = token;
                                registerForm.submit();
                            }).catch(function(error) {
                                console.error('reCAPTCHA error:', error);
                                registerForm.submit();
                            });
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>
