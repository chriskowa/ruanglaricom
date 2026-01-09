<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $event->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    @php $midtransUrl = config('midtrans.base_url', 'https://app.sandbox.midtrans.com'); @endphp
    <link rel="stylesheet" href="{{ $midtransUrl }}/snap/snap.css" />
    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <style>
        body { background-color: #ffffff; color: #111827; font-family: system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="antialiased">

    <div class="max-w-3xl mx-auto px-6 py-12">
        
        <!-- Header -->
        <header class="mb-12 text-center border-b border-gray-100 pb-12">
            @if($event->logo_image)
                <img src="{{ asset('storage/' . $event->logo_image) }}" class="h-16 mx-auto mb-6">
            @endif
            
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 mb-4">{{ $event->name }}</h1>
            <div class="flex justify-center gap-6 text-sm text-gray-500 font-medium">
                <span>{{ $event->start_at->format('d F Y') }}</span>
                <span>&bull;</span>
                <span>{{ $event->location_name }}</span>
            </div>
        </header>

        <!-- Description -->
        <section class="prose prose-slate mx-auto mb-16">
            <div class="text-lg text-gray-600 leading-relaxed">
                {!! $event->full_description ?? $event->short_description !!}
            </div>
        </section>

        <!-- Categories -->
        <section class="mb-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Kategori Lomba</h2>
            <div class="grid gap-4">
                @foreach($categories as $cat)
                <div class="border border-gray-200 rounded-lg p-6 flex justify-between items-center hover:border-gray-400 transition">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $cat->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $cat->distance_km }} KM &bull; COT {{ $cat->cot_hours }} Jam</p>
                    </div>
                    <div class="text-right">
                        <span class="block text-lg font-bold text-gray-900">Rp {{ number_format($cat->price_regular, 0, ',', '.') }}</span>
                        @if($cat->quota > 0)
                            <span class="text-xs text-green-600 font-medium">Tersedia</span>
                        @else
                            <span class="text-xs text-red-600 font-medium">Habis</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Registration -->
        @php
            $now = now();
            $isRegOpen = !($event->registration_open_at && $now < $event->registration_open_at) && !($event->registration_close_at && $now > $event->registration_close_at);
        @endphp

        <section id="register" class="mb-16">
            <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Formulir Pendaftaran</h2>
                
                @if(!$isRegOpen)
                    <div class="text-center py-8 text-gray-500">Pendaftaran ditutup.</div>
                @else
                    <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="registrationForm" class="space-y-6">
                        @csrf
                        
                        <!-- PIC -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold uppercase text-gray-400 tracking-wider">Data Penanggung Jawab</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="text" name="pic_name" placeholder="Nama Lengkap" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-black focus:ring-black" required>
                                <input type="email" name="pic_email" placeholder="Email" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-black focus:ring-black" required>
                                <input type="text" name="pic_phone" placeholder="WhatsApp" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-black focus:ring-black md:col-span-2" required>
                            </div>
                        </div>

                        <!-- Participant -->
                        <div class="space-y-4 pt-6 border-t border-gray-200">
                            <h3 class="text-sm font-bold uppercase text-gray-400 tracking-wider">Data Peserta</h3>
                            <div class="bg-white p-4 rounded-lg border border-gray-200 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input type="text" name="participants[0][name]" placeholder="Nama Peserta" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-black focus:ring-black" required>
                                    <input type="email" name="participants[0][email]" placeholder="Email Peserta" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-black focus:ring-black" required>
                                    <input type="text" name="participants[0][phone]" placeholder="No. HP" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-black focus:ring-black" required>
                                    <input type="text" name="participants[0][id_card]" placeholder="No. ID (KTP/SIM)" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-black focus:ring-black" required>
                                    
                                    <select name="participants[0][category_id]" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-black focus:ring-black md:col-span-2" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }} - Rp {{ number_format($cat->price_regular, 0, ',', '.') }}</option>
                                        @endforeach
                                    </select>

                                    <select name="participants[0][jersey_size]" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-black focus:ring-black" required>
                                        <option value="">Ukuran Jersey</option>
                                        @foreach(['S','M','L','XL'] as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                                    </select>
                                    
                                    <input type="text" name="participants[0][target_time]" placeholder="Target Waktu (Optional)" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-black focus:ring-black">
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" class="w-full bg-black text-white font-bold py-4 rounded-lg hover:bg-gray-800 transition">
                            Daftar Sekarang
                        </button>
                    </form>
                @endif
            </div>
        </section>

        <!-- Footer -->
        <footer class="text-center text-sm text-gray-400 pt-12 border-t border-gray-100">
            &copy; {{ date('Y') }} {{ $event->name }}. All rights reserved.
        </footer>
    </div>

    <script>
        // Simple script for form handling
        const form = document.getElementById('registrationForm');
        if(form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const btn = document.getElementById('submitBtn');
                btn.innerHTML = 'Memproses...';
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
                            onError: function(result){ alert("Pembayaran gagal"); btn.disabled=false; btn.innerHTML='Daftar Sekarang'; },
                            onClose: function(){ btn.disabled=false; btn.innerHTML='Daftar Sekarang'; }
                        });
                    } else if(data.success) {
                         window.location.href = `{{ route("events.show", $event->slug) }}?success=true`;
                    } else {
                        alert(data.message || 'Error');
                        btn.disabled=false; btn.innerHTML='Daftar Sekarang';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal menghubungi server');
                    btn.disabled=false; btn.innerHTML='Daftar Sekarang';
                });
            });
        }
    </script>
</body>
</html>
