@guest
<div x-data="authModalComponent()" 
     x-show="open" 
     x-cloak
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
     @keydown.escape.window="open = false">
    
    <div class="relative w-full max-w-md max-h-[90vh] bg-[#09090b] border border-zinc-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden text-zinc-100 font-sans"
         @click.outside="open = false"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        
        <!-- Header -->
        <div class="p-6 text-center border-b border-zinc-800/80 flex-shrink-0 relative">
            <button @click="open = false" class="absolute top-5 right-5 text-zinc-500 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <a href="{{ route('home') }}" class="inline-block mb-1">
                <span class="text-2xl font-black italic tracking-tight text-white">
                    RUANG<span class="text-zinc-400">LARI</span>
                </span>
            </a>
            
            <p class="text-xs text-zinc-400 mt-1" x-text="tab === 'login' ? 'Masuk ke akun RuangLari Anda' : 'Daftar akun runner / coach baru'"></p>

            <div class="grid grid-cols-2 mt-5 p-1 bg-zinc-900 border border-zinc-800 rounded-xl">
                <button type="button" @click="tab = 'login'" :class="tab === 'login' ? 'bg-zinc-800 text-white font-semibold shadow-sm' : 'text-zinc-400 hover:text-zinc-200'" class="py-2 text-xs rounded-lg transition-all text-center">Masuk (Login)</button>
                <button type="button" @click="tab = 'register'" :class="tab === 'register' ? 'bg-zinc-800 text-white font-semibold shadow-sm' : 'text-zinc-400 hover:text-zinc-200'" class="py-2 text-xs rounded-lg transition-all text-center">Daftar (Register)</button>
            </div>
        </div>

        <div class="p-6 overflow-y-auto flex-grow">
            <!-- Error & Success Alert -->
            <div x-show="errorMessage" x-text="errorMessage" class="mb-4 p-3 rounded-xl bg-red-950/40 border border-red-800/60 text-red-200 text-xs" x-cloak></div>
            <div x-show="successMessage" x-text="successMessage" class="mb-4 p-3 rounded-xl bg-emerald-950/40 border border-emerald-800/60 text-emerald-200 text-xs" x-cloak></div>

            <!-- Login Form Tab -->
            <div x-show="tab === 'login'" class="space-y-4">
                <!-- Sub Tab Selection for Login Method -->
                <div class="grid grid-cols-2 p-1 bg-zinc-900/60 border border-zinc-800/80 rounded-lg">
                    <button type="button" @click="loginMethod = 'email'" :class="loginMethod === 'email' ? 'bg-zinc-800 text-white font-medium shadow-sm' : 'text-zinc-400 hover:text-zinc-200'" class="py-1.5 text-xs rounded-md transition-all text-center">Email / Username</button>
                    <button type="button" @click="loginMethod = 'phone'" :class="loginMethod === 'phone' ? 'bg-zinc-800 text-white font-medium shadow-sm' : 'text-zinc-400 hover:text-zinc-200'" class="py-1.5 text-xs rounded-md transition-all text-center">WhatsApp OTP</button>
                </div>

                <!-- Email Login Form -->
                <form x-show="loginMethod === 'email'" @submit.prevent="submitLogin" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1">Email atau Username</label>
                        <input type="text" name="email" required class="w-full px-3.5 py-2.5 bg-zinc-900 border border-zinc-800 rounded-lg text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="runner@example.com">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-medium text-zinc-300">Password</label>
                            <a href="{{ route('password.request') }}" class="text-xs text-zinc-400 hover:text-white transition-colors">Lupa Password?</a>
                        </div>
                        <input type="password" name="password" required class="w-full px-3.5 py-2.5 bg-zinc-900 border border-zinc-800 rounded-lg text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="••••••••">
                    </div>
                    
                    <div class="flex items-center pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-zinc-400 hover:text-zinc-200 transition-colors">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-zinc-100 focus:ring-zinc-700">
                            <span>Ingat saya</span>
                        </label>
                    </div>
                    
                    <input type="hidden" name="g-recaptcha-response" value="">

                    <button type="submit" :disabled="loading" class="w-full py-2.5 bg-white hover:bg-zinc-200 text-zinc-950 font-semibold rounded-lg transition-all disabled:opacity-50 flex items-center justify-center gap-2 text-sm shadow-sm">
                        <span x-show="!loading">Masuk</span>
                        <span x-show="loading" class="w-4 h-4 border-2 border-zinc-950 border-t-transparent rounded-full animate-spin"></span>
                    </button>
                </form>

                <!-- Phone Login Form -->
                <div x-show="loginMethod === 'phone'" class="space-y-3.5" x-cloak>
                    <!-- Step 1: Request OTP -->
                    <div x-show="!otpSent" class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Nomor WhatsApp</label>
                            <input type="tel" x-model="phone" class="w-full px-3.5 py-2.5 bg-zinc-900 border border-zinc-800 rounded-lg text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="08123456789">
                        </div>
                        <button type="button" @click="requestOtp" :disabled="loading" class="w-full py-2.5 bg-white hover:bg-zinc-200 text-zinc-950 font-semibold rounded-lg transition-all disabled:opacity-50 flex items-center justify-center gap-2 text-sm shadow-sm">
                            <span x-show="!loading">Kirim OTP WhatsApp</span>
                            <span x-show="loading" class="w-4 h-4 border-2 border-zinc-950 border-t-transparent rounded-full animate-spin"></span>
                        </button>
                    </div>

                    <!-- Step 2: Verify OTP -->
                    <div x-show="otpSent" class="space-y-3.5" x-cloak>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1 text-center">Masukkan Kode OTP</label>
                            <input type="text" x-model="otpCode" maxlength="6" class="w-full px-3.5 py-2.5 bg-zinc-900 border border-zinc-800 rounded-lg text-white placeholder-zinc-500 text-center tracking-widest font-mono text-xl focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="••••••">
                        </div>
                        <button type="button" @click="verifyOtp" :disabled="loading" class="w-full py-2.5 bg-white hover:bg-zinc-200 text-zinc-950 font-semibold rounded-lg transition-all disabled:opacity-50 flex items-center justify-center gap-2 text-sm shadow-sm">
                            <span x-show="!loading">Verifikasi & Masuk</span>
                            <span x-show="loading" class="w-4 h-4 border-2 border-zinc-950 border-t-transparent rounded-full animate-spin"></span>
                        </button>
                        <div class="text-center">
                            <button type="button" @click="otpSent = false" class="text-xs text-zinc-400 hover:text-white underline transition-colors">Ubah Nomor WhatsApp</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Register Form Tab -->
            <form x-show="tab === 'register'" @submit.prevent="submitRegister" class="space-y-3.5" x-cloak>
                <!-- Role Selection -->
                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-2">Saya mendaftar sebagai...</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="runner" x-model="role" class="hidden">
                            <div :class="role === 'runner' ? 'border-zinc-400 bg-zinc-800/80 text-white font-semibold' : 'border-zinc-800 text-zinc-400 hover:bg-zinc-900'" class="border rounded-lg p-2.5 text-center transition-all h-full flex flex-col items-center justify-center gap-1.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span class="text-xs">Runner</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="coach" x-model="role" class="hidden">
                            <div :class="role === 'coach' ? 'border-zinc-400 bg-zinc-800/80 text-white font-semibold' : 'border-zinc-800 text-zinc-400 hover:bg-zinc-900'" class="border rounded-lg p-2.5 text-center transition-all h-full flex flex-col items-center justify-center gap-1.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="text-xs">Coach</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-zinc-900 border border-zinc-800 rounded-lg text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="Nama Lengkap">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-3.5 py-2.5 bg-zinc-900 border border-zinc-800 rounded-lg text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="nama@example.com">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-300 mb-1">Nomor WhatsApp</label>
                    <input type="tel" name="phone" required class="w-full px-3.5 py-2.5 bg-zinc-900 border border-zinc-800 rounded-lg text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="08123456789">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-3.5 py-2.5 bg-zinc-900 border border-zinc-800 rounded-lg text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-300 mb-1">Konfirmasi</label>
                        <input type="password" name="password_confirmation" required class="w-full px-3.5 py-2.5 bg-zinc-900 border border-zinc-800 rounded-lg text-white placeholder-zinc-500 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all" placeholder="••••••••">
                    </div>
                </div>
                <input type="hidden" name="g-recaptcha-response" value="">

                <button type="submit" :disabled="loading" class="w-full py-2.5 bg-white hover:bg-zinc-200 text-zinc-950 font-semibold rounded-lg transition-all disabled:opacity-50 flex items-center justify-center gap-2 text-sm shadow-sm">
                    <span x-show="!loading">Daftar Akun Baru</span>
                    <span x-show="loading" class="w-4 h-4 border-2 border-zinc-950 border-t-transparent rounded-full animate-spin"></span>
                </button>
            </form>

            <!-- Social Login Options (Google & Strava) -->
            <div class="mt-6">
                <div class="relative flex items-center justify-center mb-5">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-zinc-800"></div></div>
                    <span class="relative px-3 bg-[#09090b] text-xs font-medium text-zinc-500">Atau lanjutkan dengan</span>
                </div>
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
            </div>
        </div>
    </div>
</div>
@endguest

<script>
    function authModalComponent() {
        return {
            open: false,
            tab: 'login',
            loginMethod: 'email',
            phone: '',
            otpCode: '',
            otpSent: false,
            loading: false,
            errorMessage: '',
            successMessage: '',
            userId: null,
            role: 'runner',
            
            init() {
                window.openLoginModal = () => { 
                    this.tab = 'login'; 
                    this.open = true; 
                    this.errorMessage = ''; 
                    this.successMessage = '';
                };
                window.openRegisterModal = () => { 
                    this.tab = 'register'; 
                    this.open = true; 
                    this.errorMessage = ''; 
                    this.successMessage = '';
                };
            },

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
            },

            async submitLogin(e) {
                this.loading = true;
                this.errorMessage = '';
                const form = e.target;

                const recaptchaKeyV3 = '{{ env('RECAPTCHA_SITE_KEY_v3') ?: env('RECAPTCHA_SITE_KEY') }}';
                if (recaptchaKeyV3 && typeof grecaptcha !== 'undefined' && typeof grecaptcha.execute === 'function') {
                    try {
                        const token = await new Promise((resolve, reject) => {
                            grecaptcha.ready(() => {
                                grecaptcha.execute(recaptchaKeyV3, {action: 'login'})
                                    .then(resolve)
                                    .catch(reject);
                            });
                        });
                        const input = form.querySelector('input[name="g-recaptcha-response"]');
                        if (input) input.value = token;
                    } catch (err) {
                        console.error('reCAPTCHA v3 error:', err);
                    }
                }

                const formData = new FormData(form);
                
                try {
                    const res = await fetch('{{ route('login') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    
                    const data = await res.json();
                    if (data.success) {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        this.errorMessage = data.message || 'Email atau password salah.';
                    }
                } catch (error) {
                    this.errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                } finally {
                    this.loading = false;
                }
            },

            async submitRegister(e) {
                this.loading = true;
                this.errorMessage = '';
                const form = e.target;

                const recaptchaKeyV3 = '{{ env('RECAPTCHA_SITE_KEY_v3') ?: env('RECAPTCHA_SITE_KEY') }}';
                if (recaptchaKeyV3 && typeof grecaptcha !== 'undefined' && typeof grecaptcha.execute === 'function') {
                    try {
                        const token = await new Promise((resolve, reject) => {
                            grecaptcha.ready(() => {
                                grecaptcha.execute(recaptchaKeyV3, {action: 'register'})
                                    .then(resolve)
                                    .catch(reject);
                            });
                        });
                        const input = form.querySelector('input[name="g-recaptcha-response"]');
                        if (input) input.value = token;
                    } catch (err) {
                        console.error('reCAPTCHA v3 error:', err);
                    }
                }

                const formData = new FormData(form);
                
                try {
                    const res = await fetch('{{ route('register') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    
                    const data = await res.json();
                    if (res.ok) {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        this.errorMessage = data.message || 'Gagal mendaftar. Periksa kembali data Anda.';
                        if (data.errors) {
                            const firstError = Object.values(data.errors)[0][0];
                            this.errorMessage = firstError;
                        }
                    }
                } catch (error) {
                    this.errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                } finally {
                    this.loading = false;
                }
            }
        };
    }
</script>
