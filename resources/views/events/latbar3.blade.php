<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->name }} - Registrasi Latbar</title>
    <meta name="description" content="{{ $event->location_name ? $event->name.' di '.$event->location_name.' | Latihan terstruktur, slot terbatas.' : $event->name.' | Latihan terstruktur, slot terbatas.' }}">
    <meta name="keywords" content="ruang lari, latbar, komunitas lari, event lari, {{ strtolower($event->name) }}, {{ strtolower($event->location_name) }}">
    <link rel="canonical" href="{{ route('events.show', $event->slug) }}">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $event->name }} - Registrasi Latbar">
    <meta property="og:description" content="{{ $event->location_name ? $event->name.' di '.$event->location_name.' | Latihan terstruktur, slot terbatas.' : $event->name.' | Latihan terstruktur, slot terbatas.' }}">
    <meta property="og:url" content="{{ route('events.show', $event->slug) }}">
    <meta property="og:image" content="{{ $event->getHeroImageUrl() ?? asset('images/ruanglari_green.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $event->name }} - Registrasi Latbar">
    <meta name="twitter:description" content="{{ $event->location_name ? $event->name.' di '.$event->location_name.' | Latihan terstruktur, slot terbatas.' : $event->name.' | Latihan terstruktur, slot terbatas.' }}">
    <meta name="twitter:image" content="{{ $event->getHeroImageUrl() ?? asset('images/ruanglari_green.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/green/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('images/green/site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ config('midtrans.base_url', 'https://app.sandbox.midtrans.com') }}/snap/snap.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                        display: ['Bebas Neue', 'sans-serif'], // Font Headline Sporty
                    },
                    colors: {
                        sport: {
                            volt: '#CCFF00',   /* Hijau Stabilo / Nike Volt */
                            blue: '#0044FF',   /* Electric Blue */
                            dark: '#0a0a0a',
                            surface: '#121212'
                        }
                    },
                    backgroundImage: {
                        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                    }
                }
            }
        }
    </script>
    <style>
        :root{
            --dark:#0a0a0a;
            --card:#111315;
            --input:#0f1112;
            --neon:#ccff00;
            --accent:#7dd3fc;
        }
        [v-cloak]{display:none;}
        .glass-dark{background:rgba(18,18,18,0.8);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.08)}
        body{background:var(--dark);color:#fff;font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, \"Apple Color Emoji\", \"Segoe UI Emoji\"}
        .font-display{font-weight:800;letter-spacing:0.02em}
        .text-sport-volt{color:var(--neon)}
        .bg-sport-volt{background-color:var(--neon)}
        
        /* Modern Inputs */
        .form-input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            padding: 1rem 1rem;
            border-radius: 0.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
            outline: none;
        }
        .form-input:focus {
            background: rgba(255,255,255,0.05);
            border-color: var(--neon);
            box-shadow: 0 0 0 4px rgba(204, 255, 0, 0.1);
            transform: translateY(-2px);
        }
        .form-label {
            position: absolute;
            left: 1rem;
            top: 1rem;
            color: #9ca3af;
            font-size: 0.95rem;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        .form-input:focus ~ .form-label,
        .form-input:not(:placeholder-shown) ~ .form-label {
            transform: translateY(-1.4rem) translateX(-0.2rem) scale(0.85);
            color: var(--neon);
            font-weight: 600;
            background: var(--dark);
            padding: 0 0.4rem;
        }

        /* Payment Method Cards */
        .payment-card {
            position: relative;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.03);
            border-radius: 1rem;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .payment-card:hover {
            border-color: rgba(204, 255, 0, 0.5);
            background: rgba(255,255,255,0.05);
        }
        .payment-card.active {
            border-color: var(--neon);
            background: rgba(204, 255, 0, 0.05);
            box-shadow: 0 4px 20px rgba(204, 255, 0, 0.1);
        }
        .payment-card .check-circle {
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 50%;
            border: 2px solid #6b7280;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .payment-card.active .check-circle {
            border-color: var(--neon);
            background: var(--neon);
        }
        .payment-card.active .check-circle::after {
            content: '';
            width: 0.5rem;
            height: 0.5rem;
            background: #000;
            border-radius: 50%;
        }

        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
    </style>
</head>
<body>
    @include('layouts.components.pacerhub-nav')
    <div id="ph-sidebar-backdrop" class="fixed inset-0 bg-black/40 z-40 hidden"></div>
    @include('layouts.components.pacerhub-sidebar')
    <div id="app" class="relative min-h-screen flex flex-col pt-20" v-cloak>
        <header class="relative h-[70vh] lg:h-[90vh] flex items-center justify-center overflow-hidden">
            <img src="{{ $event->getHeroImageUrl() ?? 'https://images.unsplash.com/photo-1532444458054-01a7dd3e9fca?q=80&w=1600&auto=format&fit=crop' }}"
                 alt="{{ $event->name }}"
                 class="absolute inset-0 w-full h-full object-cover"
                 loading="eager"
                 fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-t from-[var(--dark)] via-[var(--dark)]/70 to-transparent"></div>
            <div class="relative z-10 text-center px-4 w-full max-w-5xl mx-auto mt-12 pb-20 mb:pt-[120px]">
                <div class="inline-flex items-center space-x-2 bg-sport-volt text-black px-3 py-1 font-bold text-sm tracking-widest uppercase mb-4 transform -skew-x-12">
                    <span>Next Session</span>
                </div>
               
                <h1 class="text-6xl md:text-8xl font-display uppercase tracking-wider mb-2 leading-none text-white">
                    {{ strtoupper($event->name) }} <span class="text-sport-volt">Track Series</span>
                </h1>
                <p class="text-gray-300 text-lg md:text-xl font-light mb-8 max-w-2xl mx-auto">
                    Dorong batas kemampuanmu. Bergabunglah dengan sesi latihan di stadion kebanggaan Malang.
                </p>
               
                <div class="flex flex-wrap justify-center gap-6 mb-8">
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 min-w-[80px]">
                        <span class="block text-3xl font-display text-sport-volt">@{{ countdown.days }}</span>
                        <span class="text-[10px] uppercase tracking-widest text-gray-400">Hari</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 min-w-[80px]">
                        <span class="block text-3xl font-display text-sport-volt">@{{ countdown.hours }}</span>
                        <span class="text-[10px] uppercase tracking-widest text-gray-400">Jam</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 min-w-[80px]">
                        <span class="block text-3xl font-display text-sport-volt">@{{ countdown.minutes }}</span>
                        <span class="text-[10px] uppercase tracking-widest text-gray-400">Menit</span>
                    </div>
                </div>
                <button v-on:click="scrollToForm" class="bg-sport-volt hover:bg-white text-black font-bold py-3 px-8 text-sm md:text-base uppercase tracking-wider transition-all rounded-lg shadow-[0_0_20px_rgba(204,255,0,0.4)]">
                    Daftar Sekarang
                </button>
            </div>
        </header>
        <main id="registration-area" class="flex-grow max-w-7xl mx-auto px-4 md:px-8 -mt-20 pt-20 relative z-20 pb-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8">
                <div class="lg:col-span-7">
                    <div class="glass-dark p-6 md:p-8 shadow-2xl rounded-2xl animate-fade-in">
                        <div class="flex items-center justify-between mb-8 border-b border-white/10 pb-4">
                            <div>
                                <h2 class="text-3xl font-display uppercase text-white tracking-wide">Formulir Pendaftaran</h2>
                                <p class="text-xs text-gray-400 mt-1 font-mono">ISI DATA DIRI ANDA DENGAN BENAR</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center border border-white/10">
                                <i class="fas fa-running text-sport-volt animate-pulse"></i>
                            </div>
                        </div>
                        <form v-on:submit.prevent="processPayment" class="space-y-6">
                            
                            <!-- Personal Info -->
                            <div class="space-y-2">
                                <div class="form-input-group animate-fade-in delay-100">
                                    <input type="text" id="name" v-model="form.name" required class="form-input" placeholder=" ">
                                    <label for="name" class="form-label">NAMA LENGKAP</label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="form-input-group animate-fade-in delay-200">
                                        <input type="tel" id="phone" v-model="form.phone" required class="form-input" placeholder=" ">
                                        <label for="phone" class="form-label">WHATSAPP</label>
                                    </div>
                                    <div class="form-input-group animate-fade-in delay-200">
                                        <input type="email" id="email" v-model="form.email" required class="form-input" placeholder=" ">
                                        <label for="email" class="form-label">EMAIL</label>
                                    </div>
                                </div>
                                
                                <div class="form-input-group animate-fade-in delay-300">
                                    <input type="number" id="ticket" v-model="form.ticket_quantity" min="1" required class="form-input" placeholder=" ">
                                    <label for="ticket" class="form-label">JUMLAH TIKET</label>
                                </div>
                            </div>

                            <!-- Addons Section -->
                            <div v-if="availableAddons.length > 0" class="animate-fade-in delay-300">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-1 h-4 bg-sport-volt rounded-full"></div>
                                    <h3 class="text-sm font-bold text-gray-300 uppercase tracking-wider">Add-ons (Optional)</h3>
                                </div>
                                <div class="grid grid-cols-1 gap-3">
                                    <div v-for="(addon, index) in availableAddons" :key="index"
                                         class="relative group bg-white/5 border border-white/10 rounded-xl p-4 cursor-pointer hover:bg-white/10 transition-all duration-300"
                                         :class="{'border-sport-volt bg-sport-volt/5': isAddonSelected(addon)}"
                                         @click="toggleAddon(addon)">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="w-6 h-6 rounded border flex items-center justify-center transition-all duration-300"
                                                     :class="isAddonSelected(addon) ? 'bg-sport-volt border-sport-volt' : 'border-gray-500 group-hover:border-sport-volt'">
                                                    <i class="fas fa-check text-black text-xs transform scale-0 transition-transform duration-200"
                                                       :class="{'scale-100': isAddonSelected(addon)}"></i>
                                                </div>
                                                <div>
                                                    <span class="block text-sm font-bold text-white group-hover:text-sport-volt transition-colors">@{{ addon.name }}</span>
                                                    <span class="text-xs text-gray-500">Tambahan opsional</span>
                                                </div>
                                            </div>
                                            <span class="text-sm font-display text-sport-volt bg-sport-volt/10 px-3 py-1 rounded-lg border border-sport-volt/20">
                                                @{{ formatCurrency(addon.price) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="animate-fade-in delay-300">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-1 h-4 bg-sport-volt rounded-full"></div>
                                    <h3 class="text-sm font-bold text-gray-300 uppercase tracking-wider">Metode Pembayaran</h3>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="payment-card" :class="{'active': form.payment_method === 'midtrans'}" @click="form.payment_method = 'midtrans'">
                                        <div class="flex items-center justify-between mb-2">
                                            <i class="fas fa-credit-card text-xl text-gray-400" :class="{'text-sport-volt': form.payment_method === 'midtrans'}"></i>
                                            <div class="check-circle"></div>
                                        </div>
                                        <div class="font-bold text-sm text-white">Online Payment</div>
                                        <div class="text-[10px] text-gray-500 mt-1">QRIS, E-Wallet, Virtual Account</div>
                                    </div>
                                    
                                    <div class="payment-card" :class="{'active': form.payment_method === 'cod'}" @click="form.payment_method = 'cod'">
                                        <div class="flex items-center justify-between mb-2">
                                            <i class="fas fa-hand-holding-usd text-xl text-gray-400" :class="{'text-sport-volt': form.payment_method === 'cod'}"></i>
                                            <div class="check-circle"></div>
                                        </div>
                                        <div class="font-bold text-sm text-white">Bayar di Lokasi</div>
                                        <div class="text-[10px] text-gray-500 mt-1">Cash On Delivery (COD)</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total & Action -->
                            <div class="border-t border-white/10 pt-6 mt-6 animate-fade-in delay-300">
                                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                                    <div class="text-center md:text-left w-full md:w-auto">
                                        <span class="block text-gray-500 text-[10px] font-mono uppercase tracking-widest mb-1">Total Pembayaran</span>
                                        <div class="font-display text-4xl text-white tracking-wide">
                                            @{{ formattedTotal }}
                                        </div>
                                    </div>
                                    <button type="submit" :disabled="isLoading" 
                                            class="group relative w-full md:w-auto bg-white hover:bg-sport-volt text-black font-black py-4 px-10 text-base uppercase tracking-widest transition-all duration-300 rounded-xl overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-1 hover:shadow-[0_10px_30px_rgba(204,255,0,0.3)]">
                                        <div class="relative z-10 flex items-center justify-center gap-3">
                                            <span v-if="isLoading">Processing...</span>
                                            <span v-else>Bayar Sekarang</span>
                                            <i v-if="!isLoading" class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="lg:col-span-5">
                    <div class="glass-dark p-6 md:p-8 h-full relative rounded-2xl">
                        <div class="flex items-center justify-between mb-5 md:mb-6">
                            <div>
                                <h3 class="text-lg md:text-xl font-display uppercase text-white">Daftar Peserta</h3>
                                <p class="text-xs text-gray-400">Bergabung dengan @{{ participants.length }} pelari lainnya</p>
                            </div>
                            <div class="bg-gray-800 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-sport-volt" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                        </div>
                        @php
                            $codCount = \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status','cod'); })->count();
                            $paidCount = \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status','paid'); })->count();
                            $codNames = \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status','cod'); })->orderBy('created_at','desc')->limit(10)->get(['name']);
                            $paidNames = \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status','paid'); })->orderBy('created_at','desc')->limit(10)->get(['name']);
                        @endphp
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <div class="p-3 bg-white/5 border border-white/10 rounded-xl">
                                <div class="text-xs text-gray-400 uppercase">COD Terdaftar</div>
                                <div class="text-2xl font-display text-sport-volt">{{ $codCount ?? 0 }}</div>
                                <ul class="mt-2 text-xs text-gray-400 space-y-1">
                                    @foreach(($codNames ?? []) as $n)
                                        <li>{{ $n->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="p-3 bg-white/5 border border-white/10 rounded-xl">
                                <div class="text-xs text-gray-400 uppercase">Paid</div>
                                <div class="text-2xl font-display text-sport-volt">{{ $paidCount ?? 0 }}</div>
                                <ul class="mt-2 text-xs text-gray-400 space-y-1">
                                    @foreach(($paidNames ?? []) as $n)
                                        <li>{{ $n->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="overflow-y-auto max-h-[500px] space-y-3 pr-1">
                            <div v-for="(p, index) in participants" :key="p.id || index" class="flex items-center p-3 bg-white/5 border border-white/5 rounded-xl hover:bg-white/10 transition">
                                <div class="w-10 h-10 flex-shrink-0 bg-gray-800 text-sport-volt font-bold font-display text-lg flex items-center justify-center border border-gray-600 rounded-lg">
                                    @{{ getInitials(p.name) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-200">@{{ p.name }}</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-wider">Ready to Run</div>
                                </div>
                                @php($canManage = auth()->check() && auth()->user()->isEventOrganizer() && $event->user_id === auth()->id())
                                @if($canManage)
                                <div class="ml-auto">
                                    <button @click="deleteParticipant(p.id)" class="text-xs px-3 py-1 rounded bg-red-600 text-white hover:bg-red-500">Delete</button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        @include('layouts.components.pacerhub-footer')
    </div>

    <script type="text/javascript" src="{{ config('midtrans.base_url', 'https://app.sandbox.midtrans.com') }}/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
    const { createApp, ref, computed, onMounted } = Vue;
    createApp({
        setup() {
            const form = ref({ name: '', email: '', phone: '', ticket_quantity: 1, addons: [] });
            const isLoading = ref(false);
            const prices = { base: 15000 };
            
            // Defensives for array initialization
            const participantsRaw = @json($participants->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
            const participants = ref(Array.isArray(participantsRaw) ? participantsRaw : []);
            
            const categoriesRaw = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]));
            const categories = Array.isArray(categoriesRaw) ? categoriesRaw : [];
            
            const addonsRaw = @json($event->addons ?? []);
            const availableAddons = ref(Array.isArray(addonsRaw) ? addonsRaw : []);
            
            const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
            const isAddonSelected = (addon) => Array.isArray(form.value.addons) && form.value.addons.some(a => a.name === addon.name);
            const toggleAddon = (addon) => {
                if (!Array.isArray(form.value.addons)) form.value.addons = [];
                const index = form.value.addons.findIndex(a => a.name === addon.name);
                if (index === -1) form.value.addons.push(addon);
                else form.value.addons.splice(index, 1);
            };
            const formattedTotal = computed(() => {
                const addonsList = Array.isArray(form.value.addons) ? form.value.addons : [];
                const addonsTotal = addonsList.reduce((sum, addon) => sum + (parseInt(addon.price) || 0), 0);
                const total = (prices.base * (form.value.ticket_quantity || 1)) + (addonsTotal * (form.value.ticket_quantity || 1));
                return formatCurrency(total);
            });
            const scrollToForm = () => document.getElementById('registration-area').scrollIntoView({ behavior: 'smooth' });
            const getInitials = (name) => name ? name.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase() : '??';
            const defaultCategoryId = categories.length > 0 ? categories[0].id : null;
            const countdown = ref({ days: 0, hours: 0, minutes: 0 });
            const startIso = "{{ optional($event->start_at)->format('c') }}";
            const startDate = startIso ? new Date(startIso) : null;
            const canManage = {{ (auth()->check() && auth()->user()->isEventOrganizer() && $event->user_id === auth()->id()) ? 'true' : 'false' }};
            const deleteBaseUrl = "{{ route('eo.events.participants.destroy', [$event, 0]) }}";
            const tick = () => {
                if (!startDate) return;
                const now = new Date();
                const dist = startDate.getTime() - now.getTime();
                if (dist <= 0) { countdown.value = { days: 0, hours: 0, minutes: 0 }; return; }
                countdown.value.days = Math.floor(dist / (1000 * 60 * 60 * 24));
                countdown.value.hours = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                countdown.value.minutes = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
            };
            const deleteParticipant = async (id) => {
                if (!canManage) return;
                if (!id) return;
                if (!confirm('Hapus peserta ini?')) return;
                const csrf = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
                try {
                    const lastSlash = deleteBaseUrl.lastIndexOf('/');
                    const base = lastSlash >= 0 ? deleteBaseUrl.slice(0, lastSlash) : deleteBaseUrl;
                    const url = base + '/' + id;
                    const res = await fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' } });
                    const data = await res.json();
                    if (data && data.success) {
                        const idx = participants.value.findIndex(p => String(p.id) === String(id));
                        if (idx >= 0) participants.value.splice(idx, 1);
                    } else {
                        alert((data && data.message) || 'Gagal menghapus peserta');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan');
                }
            };
            const processPayment = async () => {
                isLoading.value = true;
                try {
                    const participantsList = [];
                    for (let i = 0; i < form.value.ticket_quantity; i++) {
                        participantsList.push({
                            name: form.value.name,
                            email: form.value.email,
                            phone: form.value.phone,
                            id_card: form.value.phone,
                            category_id: defaultCategoryId,
                        });
                    }

                    const payload = {
                        pic_name: form.value.name,
                        pic_email: form.value.email,
                        pic_phone: form.value.phone,
                        payment_method: form.value.payment_method || 'midtrans',
                        addons: form.value.addons,
                        participants: participantsList
                    };
                    const res = await fetch("{{ route('events.register.store', $event->slug) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        alert(data.message || 'Registrasi gagal');
                        return;
                    }
                    if ((form.value.payment_method || 'midtrans') === 'midtrans' && data.snap_token) {
                        window.snap.pay(data.snap_token);
                    } else {
                        alert('Registrasi COD berhasil. Silakan lakukan pembayaran di lokasi.');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan: ' + e.message);
                } finally {
                    isLoading.value = false;
                }
            };
            onMounted(() => {
                form.value.payment_method = 'midtrans';
                tick();
                setInterval(tick, 1000);
            });
            return { form, isLoading, formattedTotal, processPayment, participants, scrollToForm, getInitials, countdown, deleteParticipant, availableAddons, formatCurrency, isAddonSelected, toggleAddon };
        }
    }).mount('#app');
    </script>
    <script>
    (function(){
        var btn = document.getElementById('ph-sidebar-toggle');
        var sidebar = document.getElementById('ph-sidebar');
        var backdrop = document.getElementById('ph-sidebar-backdrop');
        function openSidebar(){
            if(!sidebar) return;
            sidebar.classList.remove('-translate-x-full');
            if(backdrop){ backdrop.classList.remove('hidden'); }
        }
        function closeSidebar(){
            if(!sidebar) return;
            sidebar.classList.add('-translate-x-full');
            if(backdrop){ backdrop.classList.add('hidden'); }
        }
        if(btn){
            btn.addEventListener('click', function(){
                if(sidebar && sidebar.classList.contains('-translate-x-full')){ openSidebar(); } else { closeSidebar(); }
            });
        }
        if(backdrop){
            backdrop.addEventListener('click', closeSidebar);
        }
        document.addEventListener('keydown', function(e){
            if(e.key === 'Escape'){ closeSidebar(); }
        });
    })();
    </script>
</body>
</html>
