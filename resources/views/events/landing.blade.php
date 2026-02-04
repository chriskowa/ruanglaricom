@extends('layouts.pacerhub')

@section('title', 'Jadwal Lari & Kalender Event Lari Indonesia ' . date('Y'))
@section('description', 'Temukan jadwal event lari terbaru di Indonesia tahun ' . date('Y') . '. Kalender lari lengkap dengan filter kota, kategori jarak (5K, 10K, HM, FM), dan jenis lomba.')

@section('content')
<div class="min-h-screen pt-24 pb-16 px-4 md:px-8 bg-dark relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto mb-12 text-center relative z-10" data-aos="fade-down">
        <h1 class="text-4xl md:text-6xl font-black text-white italic tracking-tighter mb-4">
            KALENDER <span class="text-neon">LARI</span>
        </h1>
        <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto">
            Jadwal event lari terlengkap di Indonesia. Temukan race impianmu berikutnya, dari Fun Run hingga Ultra Marathon.
        </p>
        
        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3" data-aos="fade-up" data-aos-delay="50">
            <a href="{{ url('/calendar') }}" class="px-8 py-3 rounded-full bg-slate-800 border border-slate-700 text-white font-bold hover:bg-neon hover:text-dark hover:border-neon transition-all inline-flex items-center justify-center gap-2 shadow-lg hover:shadow-neon/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Kelola Kalender Lari Saya
            </a>
            <button type="button" id="btn-open-submit-event" class="px-8 py-3 rounded-full bg-neon text-dark font-extrabold hover:bg-lime-300 transition-all inline-flex items-center justify-center gap-2 shadow-lg shadow-neon/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Submit Event Lari
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="max-w-7xl mx-auto mb-8 relative z-10" data-aos="fade-up" data-aos-delay="100">
        <div class="bg-card/80 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 md:p-6 shadow-xl">
            <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="lg:col-span-1">
                    <div class="flex justify-between items-end mb-1">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Cari Event</label>
                        <button type="button" id="mobile-filter-toggle" class="md:hidden text-neon text-xs font-bold flex items-center gap-1 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                            Filter
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" name="search" placeholder="Nama event atau lokasi..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 pl-10 text-white focus:outline-none focus:border-neon transition-colors">
                        <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

                <!-- Month & Year -->
                <div class="mobile-filter-item hidden md:block">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Waktu</label>
                    <div class="flex gap-2">
                        <select name="month" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                            <option value="">Bulan</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                        <select name="year" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                            <option value="">Tahun</option>
                            @foreach(range(date('Y'), date('Y') + 1) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- City -->
                <div class="mobile-filter-item hidden md:block">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi</label>
                    <select name="city_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">Semua Kota</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Race Type -->
                <div class="mobile-filter-item hidden md:block">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Lomba</label>
                    <select name="race_type_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">Semua Jenis</option>
                        @foreach($raceTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Distance -->
                <div class="mobile-filter-item hidden md:block">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori Jarak</label>
                    <select name="race_distance_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">Semua Jarak</option>
                        @foreach($raceDistances as $distance)
                            <option value="{{ $distance->id }}">{{ $distance->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Event List -->
    <div class="max-w-7xl mx-auto relative z-10">
        <div id="events-container" class="space-y-4">
            @include('events.partials.list', ['events' => $events])
        </div>
        
        <div id="pagination-container" class="mt-8">
            {{ $events->links() }}
        </div>

        <!-- Loading State -->
        <div id="loading-indicator" class="hidden py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-neon"></div>
            <p class="mt-2 text-slate-400 text-sm">Memuat jadwal...</p>
        </div>
    </div>
</div>

<div id="submit-event-modal" class="fixed inset-0 z-[9999] hidden overflow-auto">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
    <div class="relative h-full w-full flex items-center justify-center p-4">
        <div class="w-full max-w-2xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl overflow-auto h-screen">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Submit Event</div>
                    <div class="text-lg font-black text-white italic tracking-tighter">AJUKAN EVENT LARI</div>
                </div>
                <button type="button" id="btn-close-submit-event" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div id="submit-event-alert" class="hidden px-6 pt-4"></div>

            <form id="submit-event-form" class="px-6 py-5 space-y-5">
                <input type="text" name="website" id="submit_event_website" class="hidden" tabindex="-1" autocomplete="off">
                <input type="hidden" name="started_at" id="submit_event_started_at" value="0">
                <input type="hidden" name="otp_id" id="submit_event_otp_id" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Event</label>
                        <input type="text" name="event_name" id="submit_event_name" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Contoh: Jakarta City Run 2026" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Event</label>
                        <input type="date" name="event_date" id="submit_event_date" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" required>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Banner Event (Opsional)</label>
                        <input type="file" name="banner" id="submit_event_banner" accept="image/png, image/jpeg, image/jpg, image/webp" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-neon hover:file:bg-slate-700">
                        <div class="text-[11px] text-slate-500">Maksimal 2MB. Disarankan landscape.</div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jam Mulai (Opsional)</label>
                        <input type="time" name="start_time" id="submit_event_time" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kota (Opsional)</label>
                        <select name="city_id" id="submit_event_city_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon">
                            <option value="">Pilih Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Lokasi</label>
                        <input type="text" name="location_name" id="submit_event_location" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Contoh: Gelora Bung Karno" required>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Alamat (Opsional)</label>
                        <input type="text" name="location_address" id="submit_event_address" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Alamat lengkap / titik kumpul">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Lomba (Opsional)</label>
                        <select name="race_type_id" id="submit_event_race_type_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon">
                            <option value="">Pilih Jenis</option>
                            @foreach($raceTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kategori Jarak (Opsional)</label>
                        <select name="race_distance_ids" id="submit_event_race_distance_ids" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" multiple>
                            @foreach($raceDistances as $distance)
                                <option value="{{ $distance->id }}">{{ $distance->name }}</option>
                            @endforeach
                        </select>
                        <div class="text-[11px] text-slate-500">Bisa pilih lebih dari satu.</div>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Link Pendaftaran (Opsional)</label>
                        <input type="url" name="registration_link" id="submit_event_registration_link" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="https://...">
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Link Media Sosial (Opsional)</label>
                        <input type="url" name="social_media_link" id="submit_event_social_media_link" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="https://instagram.com/...">
                    </div>
                </div>

                <div class="border-t border-slate-800 pt-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Penyelenggara (Opsional)</label>
                            <input type="text" name="organizer_name" id="submit_event_organizer_name" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Nama EO / komunitas">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kontak Penyelenggara (Opsional)</label>
                            <input type="text" name="organizer_contact" id="submit_event_organizer_contact" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="WA / Email">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Kamu (Opsional)</label>
                            <input type="text" name="contributor_name" id="submit_event_contributor_name" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Nama pengaju">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email Kamu</label>
                            <input type="email" name="contributor_email" id="submit_event_contributor_email" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="email@kamu.com" required>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catatan (Opsional)</label>
                            <textarea name="notes" id="submit_event_notes" rows="3" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Info tambahan (mis: kuota, kategori, syarat, dll)"></textarea>
                        </div>
                    </div>

                    @if(env('RECAPTCHA_SITE_KEY'))
                    <div class="flex justify-center">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-theme="dark"></div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                        <div class="md:col-span-2 space-y-1">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode OTP</label>
                            <input type="text" inputmode="numeric" maxlength="6" name="otp_code" id="submit_event_otp_code" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="6 digit">
                        </div>
                        <button type="button" id="btn-submit-event-send-otp" class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">
                            Kirim OTP
                        </button>
                    </div>
                </div>
            </form>

            <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/40 flex flex-col sm:flex-row justify-end gap-2">
                <button type="button" id="btn-submit-event-cancel" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-200 font-bold hover:bg-slate-700 transition">Batal</button>
                <button type="button" id="btn-submit-event-submit" class="px-5 py-2.5 rounded-xl bg-neon text-dark font-extrabold hover:bg-lime-300 transition">Submit Event</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if(env('RECAPTCHA_SITE_KEY'))
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filter-form');
        const container = document.getElementById('events-container');
        const paginationContainer = document.getElementById('pagination-container');
        const loading = document.getElementById('loading-indicator');
        let timeout = null;

        function fetchEvents(url = "{{ route('events.index') }}") {
            // Show loading
            container.classList.add('opacity-50');
            loading.classList.remove('hidden');

            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            // If url already has params (pagination), append filter params
            if (url.includes('?')) {
                // Extract base url and existing params
                const [baseUrl, existingQuery] = url.split('?');
                const existingParams = new URLSearchParams(existingQuery);
                
                // Merge params (filter params override pagination params if needed, but usually we want to keep page if only paginating, reset page if filtering)
                // Actually, usually when filtering, we want to reset to page 1.
                // But if clicking pagination link, we want to keep filter params.
                
                // Case 1: Filter changed -> url is base route -> use params
                // Case 2: Pagination clicked -> url has ?page=X -> merge params
                
                for(let [key, value] of existingParams) {
                    if(key === 'page') params.set('page', value);
                }
            }

            fetch(`${url.split('?')[0]}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                container.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination;
                
                // Re-attach pagination listeners
                attachPaginationListeners();
            })
            .finally(() => {
                container.classList.remove('opacity-50');
                loading.classList.add('hidden');
            });
        }

        function attachPaginationListeners() {
            document.querySelectorAll('#pagination-container a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchEvents(this.href);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        }

        // Filter change events
        form.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', () => fetchEvents());
        });

        // Search debounce
        form.querySelector('input[name="search"]').addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fetchEvents(), 500);
        });

        // Mobile Filter Toggle
        const mobileToggle = document.getElementById('mobile-filter-toggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                document.querySelectorAll('.mobile-filter-item').forEach(el => {
                    el.classList.toggle('hidden');
                });
                
                // Toggle active state styling on button
                this.classList.toggle('text-neon');
                this.classList.toggle('text-white');
            });
        }

        // Initial listeners
        attachPaginationListeners();
    });
</script>

<script>
    (function () {
        var modal = document.getElementById('submit-event-modal');
        var openBtn = document.getElementById('btn-open-submit-event');
        var closeBtn = document.getElementById('btn-close-submit-event');
        var cancelBtn = document.getElementById('btn-submit-event-cancel');
        var sendOtpBtn = document.getElementById('btn-submit-event-send-otp');
        var submitBtn = document.getElementById('btn-submit-event-submit');
        var alertBox = document.getElementById('submit-event-alert');
        var form = document.getElementById('submit-event-form');
        var startedAtEl = document.getElementById('submit_event_started_at');
        var otpIdEl = document.getElementById('submit_event_otp_id');
        var otpCodeEl = document.getElementById('submit_event_otp_code');
        var emailEl = document.getElementById('submit_event_contributor_email');

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

        function showAlert(type, msg) {
            if (!alertBox) return;
            var cls = type === 'success'
                ? 'bg-green-900/30 border border-green-500/30 text-green-300'
                : 'bg-red-900/30 border border-red-500/30 text-red-300';
            alertBox.className = 'px-6 pt-4';
            alertBox.innerHTML = '<div class="'+cls+' rounded-xl p-3 text-sm font-bold">'+String(msg || '')+'</div>';
            alertBox.classList.remove('hidden');
        }

        function clearAlert() {
            if (!alertBox) return;
            alertBox.classList.add('hidden');
            alertBox.innerHTML = '';
        }

        function openModal() {
            if (!modal) return;
            clearAlert();
            otpIdEl.value = '';
            otpCodeEl.value = '';
            startedAtEl.value = String(Date.now());
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function getCaptchaResponse() {
            try {
                if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse) {
                    return grecaptcha.getResponse();
                }
            } catch (e) {}
            return '';
        }

        function resetCaptcha() {
            try {
                if (typeof grecaptcha !== 'undefined' && grecaptcha.reset) {
                    grecaptcha.reset();
                }
            } catch (e) {}
        }

        function getPayload() {
            var formData = new FormData();
            var els = form.querySelectorAll('input, select, textarea');
            
            els.forEach(function (el) {
                if (!el.name) return;

                if (el.type === 'file') {
                    if (el.files && el.files[0]) {
                        formData.append(el.name, el.files[0]);
                    }
                    return;
                }

                if (el.multiple) {
                    Array.prototype.forEach.call(el.selectedOptions || [], function (opt) {
                        formData.append(el.name + '[]', opt.value);
                    });
                    return;
                }

                if (el.type === 'radio' || el.type === 'checkbox') {
                    if (el.checked) {
                        formData.append(el.name, el.value);
                    }
                    return;
                }

                formData.append(el.name, el.value);
            });

            var captcha = getCaptchaResponse();
            if (captcha) {
                formData.append('g-recaptcha-response', captcha);
            }

            return formData;
        }

        function setBusy(btn, busy, text) {
            if (!btn) return;
            btn.disabled = !!busy;
            if (text) btn.textContent = text;
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });
        }

        if (sendOtpBtn) {
            sendOtpBtn.addEventListener('click', function () {
                clearAlert();
                var email = String(emailEl.value || '').trim();
                if (!email) {
                    showAlert('error', 'Email wajib diisi untuk kirim OTP.');
                    return;
                }

                var captcha = getCaptchaResponse();
                if ({{ env('RECAPTCHA_SITE_KEY') ? 'true' : 'false' }} && !captcha) {
                    showAlert('error', 'Mohon selesaikan verifikasi reCAPTCHA.');
                    return;
                }

                setBusy(sendOtpBtn, true, 'Mengirim...');
                fetch(@json(route('events.submissions.request-otp')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        website: '',
                        'g-recaptcha-response': captcha
                    })
                })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, status: r.status, data: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.data || !res.data.success) {
                        showAlert('error', (res.data && res.data.message) ? res.data.message : 'Gagal mengirim OTP.');
                        return;
                    }
                    otpIdEl.value = res.data.otp_id || '';
                    showAlert('success', res.data.message || 'OTP terkirim. Cek email kamu.');
                    resetCaptcha();
                })
                .catch(function () {
                    showAlert('error', 'Terjadi kesalahan saat mengirim OTP.');
                })
                .finally(function () {
                    setBusy(sendOtpBtn, false, 'Kirim OTP');
                });
            });
        }

        if (submitBtn) {
            submitBtn.addEventListener('click', function () {
                clearAlert();
                var payload = getPayload();

                if (!payload.get('otp_id')) {
                    showAlert('error', 'Klik “Kirim OTP” dulu sebelum submit.');
                    return;
                }
                
                var otpCode = payload.get('otp_code');
                if (!otpCode || String(otpCode).length !== 6) {
                    showAlert('error', 'Masukkan OTP 6 digit.');
                    return;
                }

                if ({{ env('RECAPTCHA_SITE_KEY') ? 'true' : 'false' }} && !payload.get('g-recaptcha-response')) {
                    showAlert('error', 'Mohon selesaikan verifikasi reCAPTCHA.');
                    return;
                }

                setBusy(submitBtn, true, 'Memproses...');
                fetch(@json(route('events.submissions.store')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: payload
                })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, status: r.status, data: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.data || !res.data.success) {
                        showAlert('error', (res.data && res.data.message) ? res.data.message : 'Submit gagal.');
                        if (res.status === 422) resetCaptcha();
                        return;
                    }
                    showAlert('success', res.data.message || 'Submit berhasil.');
                    form.reset();
                    otpIdEl.value = '';
                    otpCodeEl.value = '';
                    startedAtEl.value = String(Date.now());
                    resetCaptcha();
                    setTimeout(closeModal, 800);
                })
                .catch(function () {
                    showAlert('error', 'Terjadi kesalahan saat submit.');
                })
                .finally(function () {
                    setBusy(submitBtn, false, 'Submit Event');
                });
            });
        }
    })();
</script>
@endpush
@endsection
