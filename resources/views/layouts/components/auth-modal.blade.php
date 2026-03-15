@guest
<div x-data="authModalComponent()" 
     x-show="open" 
     x-cloak
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-dark/90 backdrop-blur-sm"
     @keydown.escape.window="open = false">
    
    <div class="relative w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden"
         @click.outside="open = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        
        <!-- Header -->
        <div class="p-6 text-center border-b border-slate-800">
            <h2 class="text-2xl font-black italic tracking-tighter text-white">
                RUANG<span class="text-primary">LARI</span>
            </h2>
            <div class="flex mt-6 p-1 bg-slate-800 rounded-xl">
                <button @click="tab = 'login'" :class="tab === 'login' ? 'bg-slate-700 text-white shadow-lg' : 'text-slate-400'" class="flex-1 py-2 text-sm font-bold rounded-lg transition-all">Login</button>
                <button @click="tab = 'register'" :class="tab === 'register' ? 'bg-slate-700 text-white shadow-lg' : 'text-slate-400'" class="flex-1 py-2 text-sm font-bold rounded-lg transition-all">Register</button>
            </div>
        </div>

        <div class="p-6">
            <!-- Error Alert -->
            <div x-show="errorMessage" x-text="errorMessage" class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-bold" x-cloak></div>

            <!-- Login Form -->
            <form x-show="tab === 'login'" @submit.prevent="submitLogin" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Email / Username</label>
                    <input type="text" name="email" required class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-primary transition-colors" placeholder="runner@example.com">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-primary transition-colors" placeholder="••••••••">
                </div>
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-800 text-primary focus:ring-primary">
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="text-primary hover:underline">Forgot Password?</a>
                </div>
                
                <input type="hidden" name="g-recaptcha-response" value="">

                <button type="submit" :disabled="loading" class="w-full py-3 bg-primary hover:bg-white text-dark font-black rounded-xl transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                    <span x-show="!loading">SIGN IN</span>
                    <span x-show="loading" class="w-4 h-4 border-2 border-dark border-t-transparent rounded-full animate-spin"></span>
                </button>
            </form>

            <!-- Register Form -->
            <form x-show="tab === 'register'" @submit.prevent="submitRegister" class="space-y-4" x-cloak>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Full Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-primary transition-colors" placeholder="John Doe">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-primary transition-colors" placeholder="runner@example.com">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">WhatsApp Number</label>
                    <input type="tel" name="phone" required class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-primary transition-colors" placeholder="08123456789">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-primary transition-colors" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Confirm</label>
                        <input type="password" name="password_confirmation" required class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-primary transition-colors" placeholder="••••••••">
                    </div>
                </div>
                <input type="hidden" name="role" value="runner">

                @php($recaptchaSiteKeyV2 = env('RECAPTCHA_SITE_KEY'))
                @if($recaptchaSiteKeyV2)
                    <div class="flex justify-center my-4 scale-90 origin-center">
                        <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKeyV2 }}" data-theme="dark"></div>
                    </div>
                @endif

                <button type="submit" :disabled="loading" class="w-full py-3 bg-primary hover:bg-white text-dark font-black rounded-xl transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                    <span x-show="!loading">CREATE ACCOUNT</span>
                    <span x-show="loading" class="w-4 h-4 border-2 border-dark border-t-transparent rounded-full animate-spin"></span>
                </button>
            </form>

            <!-- Social Login -->
            <div class="mt-6">
                <div class="relative flex items-center justify-center mb-6">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-800"></div></div>
                    <span class="relative px-4 bg-slate-900 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Or continue with</span>
                </div>
                <a href="{{ route('auth.google') }}" class="flex items-center justify-center gap-3 w-full py-3 bg-white text-dark font-bold rounded-xl hover:bg-slate-100 transition-all">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c1.68-1.54 2.64-3.81 2.64-6.39z"/><path fill="#34A853" d="M12 23c3.11 0 5.71-1.02 7.62-2.77l-3.57-2.77c-.99.66-2.25 1.06-3.62 1.06-2.79 0-5.14-1.88-5.99-4.41H2.82v2.86C4.72 20.56 8.13 23 12 23z"/><path fill="#FBBC05" d="M6.01 14.11c-.22-.66-.35-1.36-.35-2.11s.13-1.45.35-2.11V7.03H2.82C2.1 8.52 1.69 10.21 1.69 12s.41 3.48 1.13 4.97l3.19-2.86z"/><path fill="#EA4335" d="M12 5.38c1.69 0 3.06.58 4.26 1.72l3.19-3.19C17.53 2.1 14.91 1 12 1 8.13 1 4.72 3.44 2.82 6.14l3.19 2.86c.85-2.53 3.2-4.41 12-4.41z"/></svg>
                    Google
                </a>
            </div>
        </div>

        <button @click="open = false" class="absolute top-4 right-4 text-slate-500 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
</div>
@endguest

<script>
    function authModalComponent() {
        return {
            open: false,
            tab: 'login',
            loading: false,
            errorMessage: '',
            
            init() {
                window.openLoginModal = () => { 
                    this.tab = 'login'; 
                    this.open = true; 
                    this.errorMessage = ''; 
                };
                window.openRegisterModal = () => { 
                    this.tab = 'register'; 
                    this.open = true; 
                    this.errorMessage = ''; 
                };
            },

            async submitLogin(e) {
                this.loading = true;
                this.errorMessage = '';
                const form = e.target;

                const recaptchaKeyV3 = '{{ env('RECAPTCHA_SITE_KEY_v3') }}';
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
                        window.location.reload();
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
                const formData = new FormData(e.target);
                
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
                        window.location.reload();
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
                    if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.reset === 'function') {
                        try {
                            grecaptcha.reset();
                        } catch (e) {}
                    }
                }
            }
        };
    }
</script>
