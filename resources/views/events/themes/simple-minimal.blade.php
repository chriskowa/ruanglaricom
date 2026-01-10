<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>{{ $event->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    @php $midtransUrl = config('midtrans.base_url', 'https://app.sandbox.midtrans.com'); @endphp
    <link rel="stylesheet" href="{{ $midtransUrl }}/snap/snap.css" />
    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script>
        tailwind.config = {
            theme: {
                fontFamily: {
                    sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                },
                extend: {
                    colors: {
                        primary: '#111827', // Gray 900 (Black-ish)
                        secondary: '#4B5563', // Gray 600
                        accent: '#2563eb', // Blue 600
                    }
                }
            }
        }
    </script>
    <style>
        /* Optimasi Form Elements */
        .form-radio:checked { background-color: #111827; border-color: #111827; }
        .form-input:focus, .form-select:focus { border-color: #111827; ring: 0; box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.1); }
        /* Hide scrollbar for category selector on mobile */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased min-h-screen flex flex-col">

    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($event->logo_image)
                    <img src="{{ asset('storage/' . $event->logo_image) }}" class="h-8 w-auto">
                @else
                    <div class="h-8 w-8 bg-black rounded text-white flex items-center justify-center font-bold">E</div>
                @endif
                <span class="font-bold tracking-tight text-sm md:text-base truncate max-w-[200px]">{{ $event->name }}</span>
            </div>
            
            @if(!($event->registration_open_at && now() < $event->registration_open_at) && !($event->registration_close_at && now() > $event->registration_close_at))
            <a href="#register" class="bg-black text-white px-5 py-2 rounded-full text-xs font-bold hover:bg-gray-800 transition">
                Daftar
            </a>
            @endif
        </div>
    </nav>

    <main class="flex-grow">
        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 px-4 py-10">
            
            <div class="lg:col-span-7 space-y-10">
                
                <div>
                    <span class="inline-block py-1 px-3 rounded-full bg-gray-100 text-gray-600 text-xs font-bold uppercase tracking-wider mb-4">Official Event</span>
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 leading-tight">{{ $event->name }}</h1>
                    
                    <div class="flex flex-wrap gap-4 text-sm font-medium text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            {{ $event->start_at->format('d F Y, H:i') }} WIB
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ $event->location_name }}
                        </div>
                    </div>
                </div>

                <div class="prose prose-slate prose-lg text-gray-600 leading-relaxed">
                    {!! $event->full_description ?? $event->short_description !!}
                </div>

                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-l-4 border-black pl-3">Kategori & Harga</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($categories as $cat)
                        <div class="border border-gray-200 p-5 rounded-xl hover:border-black transition duration-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-lg">{{ $cat->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">Jarak: {{ $cat->distance_km }}KM</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold">Rp {{ number_format($cat->price_regular, 0, ',', '.') }}</p>
                                    @if($cat->quota > 0)
                                        <span class="inline-block mt-1 w-2 h-2 bg-green-500 rounded-full"></span>
                                    @else
                                        <span class="text-xs text-red-500 font-bold">Habis</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="lg:col-span-5">
                <div class="sticky top-24" id="register">
                    
                    @php
                        $now = now();
                        $isRegOpen = !($event->registration_open_at && $now < $event->registration_open_at) && !($event->registration_close_at && $now > $event->registration_close_at);
                    @endphp

                    @if(!$isRegOpen)
                        <div class="bg-gray-100 rounded-2xl p-8 text-center border border-gray-200">
                            <h3 class="font-bold text-xl text-gray-400">Registrasi Ditutup</h3>
                            <p class="text-sm text-gray-500 mt-2">Maaf, event ini belum dibuka atau sudah berakhir.</p>
                        </div>
                    @else
                        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                                <h3 class="font-bold text-lg text-gray-900">Formulir Pendaftaran</h3>
                                <p class="text-xs text-gray-500">Isi data dengan benar.</p>
                            </div>

                            <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="registrationForm" class="p-6 space-y-5">
                                @csrf
                                
                                <div class="space-y-3">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Penanggung Jawab</label>
                                    <input type="text" name="pic_name" placeholder="Nama Lengkap" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="email" name="pic_email" placeholder="Email" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                        <input type="text" name="pic_phone" placeholder="WhatsApp" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                    </div>
                                </div>

                                <div class="h-px bg-gray-100 w-full"></div>

                                <div id="participantsWrapper" class="space-y-8">
                                    <!-- Participant Item Template -->
                                    <div class="participant-item space-y-3" data-index="0">
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider participant-title">Peserta #1</label>
                                            <button type="button" class="text-xs text-red-500 hover:text-red-700 font-medium remove-participant hidden">Hapus</button>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 gap-2">
                                            <p class="text-xs text-gray-500 mb-1">Pilih Kategori:</p>
                                            @foreach($categories as $cat)
                                            <label class="cursor-pointer relative">
                                                <input type="radio" name="participants[0][category_id]" value="{{ $cat->id }}" class="peer sr-only category-radio" data-price="{{ $cat->price_regular }}" required>
                                                <div class="p-3 border border-gray-200 rounded-lg peer-checked:border-black peer-checked:bg-gray-50 peer-checked:ring-1 peer-checked:ring-black transition flex justify-between items-center">
                                                    <span class="text-sm font-medium">{{ $cat->name }}</span>
                                                    <span class="text-sm font-bold">Rp {{ number_format($cat->price_regular/1000, 0) }}k</span>
                                                </div>
                                            </label>
                                            @endforeach
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 pt-2">
                                            <input type="text" name="participants[0][name]" placeholder="Nama Peserta" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                            <select name="participants[0][gender]" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                                <option value="">Gender</option>
                                                <option value="male">Laki-laki</option>
                                                <option value="female">Perempuan</option>
                                            </select>
                                        </div>
                                        <input type="email" name="participants[0][email]" placeholder="Email Peserta" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                        
                                        <div class="grid grid-cols-2 gap-3">
                                             <input type="text" name="participants[0][phone]" placeholder="No. HP" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                             <select name="participants[0][jersey_size]" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                                <option value="">Jersey</option>
                                                @foreach(['S','M','L','XL'] as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                                            </select>
                                        </div>
                                        <input type="text" name="participants[0][id_card]" placeholder="No. KTP/SIM" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                        <input type="text" name="participants[0][target_time]" placeholder="Target Waktu (Optional)" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition">
                                    </div>
                                </div>

                                <button type="button" id="addParticipantBtn" class="w-full py-3 border border-dashed border-gray-300 rounded-xl text-sm font-bold text-gray-500 hover:text-black hover:border-black transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Tambah Peserta Lain
                                </button>

                                <div class="bg-gray-50 p-4 rounded-xl space-y-3">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500">Subtotal</span>
                                        <span class="font-bold text-black" id="subtotalDisplay">Rp 0</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm {{ $event->platform_fee > 0 ? '' : 'hidden' }}" id="feeRow">
                                        <span class="text-gray-500">Biaya Admin</span>
                                        <span class="font-bold text-black" id="feeDisplay">Rp 0</span>
                                    </div>
                                    <div class="h-px bg-gray-200 w-full"></div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500 font-bold">Total Bayar</span>
                                        <span class="font-bold text-lg text-black" id="totalDisplay">Rp 0</span>
                                    </div>
                                    
                                    @if($event->terms_and_conditions)
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input type="checkbox" name="terms_agreed" required class="mt-1 w-3.5 h-3.5 rounded border-gray-300 text-black focus:ring-black">
                                        <span class="text-[10px] text-gray-500 leading-tight">
                                            Setuju dengan <button type="button" onclick="document.getElementById('termsModal').classList.remove('hidden')" class="underline hover:text-black">Syarat & Ketentuan</button>.
                                        </span>
                                    </label>
                                    @endif
                                </div>

                                <button type="submit" id="submitBtn" class="w-full bg-black text-white font-bold py-4 rounded-xl hover:bg-gray-800 hover:shadow-lg transform transition active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                                    Bayar Sekarang
                                </button>
                                <p class="text-[10px] text-center text-gray-400 mt-2 flex items-center justify-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Pembayaran Aman via Midtrans
                                </p>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <footer class="border-t border-gray-100 py-8 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} {{ $event->name }}. All rights reserved.
    </footer>

    @if($event->terms_and_conditions)
    <div id="termsModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('termsModal').classList.add('hidden')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[80vh]">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Syarat & Ketentuan</h3>
                    <button type="button" onclick="document.getElementById('termsModal').classList.add('hidden')" class="text-gray-400 hover:text-black">✕</button>
                </div>
                <div class="p-5 overflow-y-auto prose prose-sm max-w-none text-gray-600">
                    {!! $event->terms_and_conditions !!}
                </div>
                <div class="p-5 border-t border-gray-100">
                    <button onclick="document.getElementById('termsModal').classList.add('hidden')" class="w-full bg-black text-white py-3 rounded-lg font-bold text-sm">Saya Mengerti</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        // Currency Formatter
        const formatRupiah = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
        const platformFee = {{ $event->platform_fee ?? 0 }};

        // --- Multi Participant Logic ---
        let participantIndex = 1;
        const wrapper = document.getElementById('participantsWrapper');
        const addBtn = document.getElementById('addParticipantBtn');
        const totalDisplay = document.getElementById('totalDisplay');
        
        // Template for cloning (deep clone the first item)
        const template = wrapper.querySelector('.participant-item').cloneNode(true);

        // Function to update total price
        function updateTotal() {
            let subtotal = 0;
            let count = 0;
            const checkedRadios = wrapper.querySelectorAll('input.category-radio:checked');
            checkedRadios.forEach(radio => {
                subtotal += parseFloat(radio.getAttribute('data-price') || 0);
                count++;
            });
            
            const fee = count * platformFee;
            const total = subtotal + fee;

            document.getElementById('subtotalDisplay').textContent = formatRupiah(subtotal);
            if(platformFee > 0) {
                document.getElementById('feeDisplay').textContent = formatRupiah(fee);
            }
            totalDisplay.textContent = formatRupiah(total);
        }

        // Event Delegation for Radio Changes (to update price)
        wrapper.addEventListener('change', (e) => {
            if(e.target.classList.contains('category-radio')) {
                updateTotal();
            }
        });

        // Add Participant
        addBtn.addEventListener('click', () => {
            const newItem = template.cloneNode(true);
            const idx = participantIndex++;
            
            newItem.setAttribute('data-index', idx);
            newItem.querySelector('.participant-title').textContent = `Peserta #${wrapper.children.length + 1}`;
            
            // Update Inputs
            newItem.querySelectorAll('input, select').forEach(el => {
                const name = el.getAttribute('name');
                if(name) {
                    // Replace index in name: participants[0][field] -> participants[idx][field]
                    el.setAttribute('name', name.replace(/participants\[\d+\]/, `participants[${idx}]`));
                    
                    // Clear values but keep radio values (value attribute)
                    if(el.type !== 'radio' && el.type !== 'hidden') {
                        el.value = '';
                    }
                    if(el.type === 'radio') {
                        el.checked = false;
                    }
                }
            });

            // Show Remove Button
            const removeBtn = newItem.querySelector('.remove-participant');
            removeBtn.classList.remove('hidden');
            
            wrapper.appendChild(newItem);
        });

        // Remove Participant
        wrapper.addEventListener('click', (e) => {
            if(e.target.classList.contains('remove-participant')) {
                e.target.closest('.participant-item').remove();
                
                // Renumber Titles
                wrapper.querySelectorAll('.participant-item').forEach((item, i) => {
                    item.querySelector('.participant-title').textContent = `Peserta #${i + 1}`;
                });
                
                updateTotal();
            }
        });

        // Form Submit
        const form = document.getElementById('registrationForm');
        if(form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const btn = document.getElementById('submitBtn');
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<span class="animate-pulse">Memproses...</span>';
                btn.disabled = true;
                
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success && data.snap_token) {
                        snap.pay(data.snap_token, {
                            onSuccess: function(result){ window.location.href = `{{ route("events.show", $event->slug) }}?payment=success`; },
                            onPending: function(result){ window.location.href = `{{ route("events.show", $event->slug) }}?payment=pending`; },
                            onError: function(result){ alert("Pembayaran gagal"); btn.disabled=false; btn.innerHTML=originalText; },
                            onClose: function(){ btn.disabled=false; btn.innerHTML=originalText; }
                        });
                    } else if(data.success) {
                         window.location.href = `{{ route("events.show", $event->slug) }}?success=true`;
                    } else {
                        alert(data.message || 'Error occurred');
                        btn.disabled=false; btn.innerHTML=originalText;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal terhubung ke server');
                    btn.disabled=false; btn.innerHTML=originalText;
                });
            });
        }
    </script>
</body>
</html>