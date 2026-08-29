<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- Primary SEO Meta Tags -->
    <title>{{ $metaTitle ?? 'Hasil Lomba & Leaderboard - Ruang Lari' }}</title>
    <meta name="title" content="{{ $metaTitle ?? 'Hasil Lomba & Leaderboard - Ruang Lari' }}">
    <meta name="description" content="{{ $metaDesc ?? 'Lihat hasil resmi, leaderboard, peringkat, waktu finish, dan unduh Kartu Finisher 4:5 resmi lomba di RuangLari.com.' }}">
    <meta name="keywords" content="hasil lomba lari, leaderboard {{ $raceName ?? 'lari' }}, race results, kartu finisher lari, timing gate, ruang lari race master">
    <link rel="canonical" href="{{ route('tools.race-master.results', ['slug' => $slug]) }}">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('tools.race-master.results', ['slug' => $slug]) }}">
    <meta property="og:title" content="{{ $metaTitle ?? 'Hasil Lomba & Leaderboard - Ruang Lari' }}">
    <meta property="og:description" content="{{ $metaDesc ?? 'Lihat hasil resmi dan unduh Kartu Finisher resmi lomba di RuangLari.com.' }}">
    <meta property="og:image" content="https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp">
    <meta property="og:site_name" content="RuangLari.com">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ route('tools.race-master.results', ['slug' => $slug]) }}">
    <meta name="twitter:title" content="{{ $metaTitle ?? 'Hasil Lomba & Leaderboard - Ruang Lari' }}">
    <meta name="twitter:description" content="{{ $metaDesc ?? 'Lihat hasil resmi dan unduh Kartu Finisher resmi lomba di RuangLari.com.' }}">
    <meta name="twitter:image" content="https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp">

    <!-- Schema.org SportsEvent JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SportsEvent",
      "name": "{{ $raceName ?? 'Ruang Lari Race' }}",
      "description": "{{ $metaDesc ?? 'Hasil resmi perlombaan lari di RuangLari.com' }}",
      "url": "{{ route('tools.race-master.results', ['slug' => $slug]) }}",
      "eventStatus": "https://schema.org/EventCompleted",
      "organizer": {
        "@type": "Organization",
        "name": "Ruang Lari Race Master",
        "url": "https://ruanglari.com"
      },
      "image": "https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp"
    }
    </script>

    <!-- PageSpeed: Resource Preconnects -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">

    <!-- Google Analytics (gtag.js) - Asynchronous -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-562MDGQ3RZ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-562MDGQ3RZ', { 'anonymize_ip': true });
    </script>

    <!-- Vue 3 and Assets -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"></noscript>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Orbitron:wght@700;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-orbitron { font-family: 'Orbitron', monospace !important; }
        .font-mono-nums { font-feature-settings: "tnum"; font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen antialiased">
<div id="app" class="min-h-screen flex flex-col justify-between">

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-50 bg-slate-950/90 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-5xl mx-auto px-4 py-3.5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="https://ruanglari.com" class="shrink-0">
                    <img src="https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp" alt="Ruang Lari" class="h-8 sm:h-9 object-contain" width="108" height="36">
                </a>
                <div class="min-w-0 border-l border-slate-800 pl-3">
                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Official Race Leaderboard</div>
                    <div class="font-bold text-sm sm:text-base text-white truncate max-w-xs sm:max-w-md">
                        {{ $raceName ?? 'Ruang Lari Race Championship' }}
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="copyLink" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-white font-bold text-xs flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-share-nodes text-indigo-400"></i>
                    <span class="hidden sm:inline">Bagikan</span>
                </button>
                <a href="{{ route('tools.race-master') }}" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-sm">
                    <i class="fa-solid fa-stopwatch"></i>
                    <span class="hidden sm:inline">Race Master</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-5xl mx-auto px-4 py-6 sm:py-8 space-y-5 flex-1 w-full">

        <!-- Race Hero Card -->
        <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl relative overflow-hidden">
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-950 text-indigo-300 border border-indigo-700/60 text-xs font-bold uppercase">
                        <i class="fa-solid fa-flag-checkered"></i>
                        <span>@{{ session.category || '{{ $category ?? "OFFICIAL EVENT" }}' }}</span>
                    </div>
                    <h1 class="text-xl sm:text-3xl font-black text-white uppercase tracking-tight">
                        {{ $raceName ?? 'Ruang Lari Race' }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400">
                        Hasil resmi catatan waktu digital yang diverifikasi oleh sistem Ruang Lari Race Master.
                    </p>
                </div>

                <!-- Fast Race Summary Box -->
                <div class="grid grid-cols-2 sm:grid-cols-2 gap-2 shrink-0 bg-slate-950 p-3 rounded-xl border border-slate-800 text-center min-w-[200px]">
                    <div class="p-2">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Total Finisher</div>
                        <div class="font-mono text-xl sm:text-2xl font-black text-white">@{{ results.length }}</div>
                    </div>
                    <div class="p-2 border-l border-slate-800">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Jarak Tempuh</div>
                        <div class="font-mono text-xl sm:text-2xl font-black text-indigo-400">
                            @{{ session.distance_km ? session.distance_km + ' KM' : '{{ $distanceKm ? $distanceKm . " KM" : "Road Race" }}' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar & Filters -->
        <div class="p-3 sm:p-4 bg-slate-900 rounded-2xl border border-slate-800 flex flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input 
                    v-model="query" 
                    type="text" 
                    placeholder="Cari nomor BIB atau nama pelari..." 
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-950 text-white font-medium placeholder:text-slate-400 border border-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-xs sm:text-sm transition">
            </div>
            <div class="flex items-center gap-1.5 w-full sm:w-auto overflow-x-auto no-scrollbar shrink-0 text-xs font-bold">
                <button type="button" @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-indigo-600 text-white' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'" class="px-3 py-2 rounded-xl transition shrink-0">
                    Semua (@{{ results.length }})
                </button>
                <button type="button" @click="statusFilter = 'top3'" :class="statusFilter === 'top3' ? 'bg-indigo-600 text-white' : 'bg-slate-950 text-slate-400 hover:text-white border border-slate-800'" class="px-3 py-2 rounded-xl transition shrink-0">
                    Podium Top 3
                </button>
            </div>
        </div>

        <!-- Results Table Container -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden shadow-lg">
            
            <!-- Mobile Stack View -->
            <div class="md:hidden divide-y divide-slate-800">
                <div v-for="r in filteredResults" :key="r.participant_id" class="p-4 space-y-3" :class="{'bg-amber-950/20': r.rank === 1}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Rank Badge -->
                            <div class="w-8 h-8 rounded-xl font-bold flex items-center justify-center shrink-0 text-sm shadow-sm"
                                :class="{
                                    'bg-amber-500 text-slate-950 font-black': r.rank === 1,
                                    'bg-slate-300 text-slate-900 font-bold': r.rank === 2,
                                    'bg-amber-700 text-white font-bold': r.rank === 3,
                                    'bg-slate-800 text-slate-400 text-xs': !r.rank || r.rank > 3
                                }">
                                @{{ r.rank ? r.rank : '-' }}
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-sm text-indigo-400">#@{{ r.bib }}</span>
                                    <span class="font-bold text-base text-white truncate">@{{ r.name }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    Putaran: @{{ r.laps || 1 }} Laps
                                </div>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-950 text-emerald-300 border border-emerald-800 shrink-0">
                            FINISH
                        </span>
                    </div>

                    <!-- Performance Stats -->
                    <div class="grid grid-cols-2 gap-2 p-3 bg-slate-950 rounded-xl border border-slate-800">
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400">Waktu Finish (HH:MM:SS)</div>
                            <div class="font-mono font-black text-sm text-indigo-400">
                                @{{ formatTimeHms(r.total_time_ms) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400">Average Pace</div>
                            <div class="font-mono font-bold text-sm text-slate-200">
                                @{{ paceFor(r) }}
                            </div>
                        </div>
                    </div>

                    <!-- Action Button for 4:5 Finisher Card -->
                    <div class="pt-1">
                        <button type="button" @click="openFinisherCard(r)" class="w-full py-2.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-sm">
                            <i class="fa-solid fa-award text-amber-300"></i>
                            <span>Buka Kartu Finisher 4:5</span>
                        </button>
                    </div>
                </div>

                <div v-if="!loading && filteredResults.length === 0" class="p-8 text-center text-slate-400 text-xs">
                    Tidak ada data peserta yang cocok.
                </div>
                <div v-if="loading" class="p-8 text-center text-slate-400 text-xs flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-notch fa-spin text-indigo-400"></i>
                    <span>Memuat leaderboard...</span>
                </div>
            </div>

            <!-- Desktop Table View -->
            <table class="w-full text-left hidden md:table">
                <thead class="bg-slate-950 border-b border-slate-800">
                    <tr>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Rank</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">BIB</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Peserta</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Waktu Finish</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Avg Pace</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Status</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Kartu Finisher</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-sm">
                    <tr v-for="r in filteredResults" :key="r.participant_id" class="hover:bg-slate-800/40 transition-colors" :class="{'bg-amber-950/20': r.rank === 1}">
                        <td class="p-4 font-bold text-slate-400">
                            <span v-if="r.rank === 1" class="text-amber-400 font-black text-lg">#1</span>
                            <span v-else-if="r.rank === 2" class="text-slate-200 font-black text-base">#2</span>
                            <span v-else-if="r.rank === 3" class="text-amber-600 font-black text-base">#3</span>
                            <span v-else-if="r.rank">#@{{ r.rank }}</span>
                            <span v-else>-</span>
                        </td>
                        <td class="p-4 font-mono font-bold text-base text-white">@{{ r.bib }}</td>
                        <td class="p-4 font-bold text-slate-200">@{{ r.name }}</td>
                        <td class="p-4 text-right font-mono font-bold text-indigo-400">
                            @{{ formatTimeHms(r.total_time_ms) }}
                        </td>
                        <td class="p-4 text-right font-mono text-slate-300">@{{ paceFor(r) }}</td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-950 text-emerald-300 border border-emerald-800">
                                FINISH
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <button type="button" @click="openFinisherCard(r)" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs inline-flex items-center gap-1.5 transition shadow-sm" title="Buka Kartu Finisher 4:5">
                                <i class="fa-solid fa-award text-amber-300"></i>
                                <span>Kartu 4:5</span>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!loading && filteredResults.length === 0">
                        <td colspan="7" class="p-8 text-center text-slate-400 text-xs">Tidak ada data hasil perlombaan yang cocok.</td>
                    </tr>
                    <tr v-if="loading">
                        <td colspan="7" class="p-8 text-center text-slate-400 text-xs">
                            <div class="flex items-center justify-center gap-2">
                                <i class="fa-solid fa-circle-notch fa-spin text-indigo-400"></i>
                                <span>Memuat data leaderboard...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <!-- 4:5 HTML5 Canvas Sports Finisher Card Modal -->
    <div v-if="cardModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-sm" @click.self="closeCardModal">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-4 sm:p-6 w-full max-w-lg shadow-2xl relative text-white space-y-4 max-h-[95vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                        <i class="fa-solid fa-award text-amber-300"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Kartu Finisher & E-Poster 4:5</h3>
                        <p class="text-xs text-slate-400">Format Instagram Portrait (1080 x 1350 px)</p>
                    </div>
                </div>
                <button type="button" @click="closeCardModal" class="text-slate-400 hover:text-white p-1 text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="space-y-3 text-xs">
                <!-- Photo Background Uploader -->
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">
                        Foto Lari Pelari (Opsional)
                    </label>
                    <input @change="onBgChange" type="file" accept="image/png,image/jpeg,image/webp" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                    <div class="text-[10px] text-slate-400">
                        Jika tidak ada foto, sistem otomatis merender desain <i>Energetic Sports Dark Theme</i> dengan logo resmi Ruang Lari.
                    </div>
                </div>

                <!-- Canvas Preview Box (4:5 Ratio) -->
                <div class="relative w-full max-w-[340px] mx-auto aspect-[4/5] bg-slate-950 rounded-xl overflow-hidden border border-slate-800 flex items-center justify-center shadow-lg">
                    <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-contain" alt="Finisher Card Preview">
                    <div v-else-if="generating" class="flex flex-col items-center gap-2 text-indigo-400">
                        <i class="fa-solid fa-circle-notch fa-spin text-2xl"></i>
                        <span class="text-xs font-bold text-slate-300">Merender Kartu Finisher...</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="downloadCard" :disabled="!previewUrl || generating" class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 disabled:opacity-50 text-white font-bold flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh PNG HD</span>
                    </button>
                    <button type="button" @click="shareCard" :disabled="!cardFile || generating" class="py-2.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold flex items-center justify-center gap-1.5 transition shadow-sm">
                        <i class="fa-solid fa-share-nodes"></i>
                        <span>Bagikan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Site Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500">
        <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                © {{ date('Y') }} <a href="https://ruanglari.com" class="text-indigo-400 hover:underline font-bold">RuangLari.com</a>. Official Race Timing & Results.
            </div>
            <div class="text-slate-400">
                Verified Digital Timing Gate by Ruang Lari Race Master Pro
            </div>
        </div>
    </footer>

</div>

<script>
const { createApp, ref, computed, nextTick } = Vue;

createApp({
    setup() {
        const slug = @json($slug);
        const initialRaceName = @json($raceName ?? 'Ruang Lari Race');
        const apiBase = @json(url('api/tools/race-master'));

        const loading = ref(true);
        const query = ref('');
        const statusFilter = ref('all');
        const session = ref({});
        const race = ref({});
        const results = ref([]);

        // Card Modal State
        const cardModalOpen = ref(false);
        const activeParticipant = ref(null);
        const previewUrl = ref('');
        const cardFile = ref(null);
        const bgImage = ref(null);
        const generating = ref(false);

        // Fetch Official Results Data
        const fetchResults = async () => {
            loading.value = true;
            try {
                const res = await fetch(`${apiBase}/public/${encodeURIComponent(slug)}/results`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    session.value = data.session || {};
                    race.value = data.race || {};
                    results.value = Array.isArray(data.results) ? data.results : [];
                }
            } catch (e) {
                console.error('Error loading race results:', e);
            } finally {
                loading.value = false;
            }
        };

        fetchResults();

        const filteredResults = computed(() => {
            let list = results.value;
            if (statusFilter.value === 'top3') {
                list = list.filter(r => r.rank && r.rank <= 3);
            }
            const q = query.value.trim().toLowerCase();
            if (q) {
                list = list.filter(r => String(r.bib || '').toLowerCase().includes(q) || String(r.name || '').toLowerCase().includes(q));
            }
            return list;
        });

        // Time Formatter without Milliseconds (hh:mm:ss)
        const formatTimeHms = (ms) => {
            if (!ms || ms < 0 || isNaN(ms)) return '00:00:00';
            const totalSeconds = Math.floor(ms / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        };

        const paceFor = (r) => {
            const dist = parseFloat(session.value.distance_km || '{{ $distanceKm ?? 0 }}');
            if (!dist || !r.total_time_ms) return '-';
            const sec = Math.max(1, Math.floor(r.total_time_ms / 1000));
            const paceSec = Math.round(sec / dist);
            const m = Math.floor(paceSec / 60);
            const s = paceSec % 60;
            return `${m}'${String(s).padStart(2, '0')}" /km`;
        };

        const copyLink = async () => {
            const url = window.location.href;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try {
                    await navigator.clipboard.writeText(url);
                    alert('Link hasil perlombaan berhasil disalin!');
                } catch (e) {
                    prompt('Salin link hasil lomba:', url);
                }
            } else {
                prompt('Salin link hasil lomba:', url);
            }
        };

        // Render 4:5 HTML5 Canvas Sports Finisher Card
        const renderCanvasCard = async () => {
            const p = activeParticipant.value;
            if (!p) return;

            generating.value = true;

            try {
                const canvas = document.createElement('canvas');
                canvas.width = 1080;
                canvas.height = 1350;
                const ctx = canvas.getContext('2d');

                // 1. Background
                if (bgImage.value) {
                    const bg = bgImage.value;
                    const imgRatio = bg.width / bg.height;
                    const canvasRatio = 1080 / 1350;
                    let drawW = 1080, drawH = 1350, drawX = 0, drawY = 0;
                    if (imgRatio > canvasRatio) {
                        drawH = 1350;
                        drawW = 1350 * imgRatio;
                        drawX = (1080 - drawW) / 2;
                    } else {
                        drawW = 1080;
                        drawH = 1080 / imgRatio;
                        drawY = (1350 - drawH) / 2;
                    }
                    ctx.drawImage(bg, drawX, drawY, drawW, drawH);

                    const bgGrad = ctx.createLinearGradient(0, 0, 0, 1350);
                    bgGrad.addColorStop(0, 'rgba(15, 23, 42, 0.85)');
                    bgGrad.addColorStop(0.4, 'rgba(15, 23, 42, 0.72)');
                    bgGrad.addColorStop(1, 'rgba(2, 6, 23, 0.96)');
                    ctx.fillStyle = bgGrad;
                    ctx.fillRect(0, 0, 1080, 1350);
                } else {
                    // Default Energetic Sports Dark Solid Palette
                    const bgGrad = ctx.createLinearGradient(0, 0, 1080, 1350);
                    bgGrad.addColorStop(0, '#090d16');
                    bgGrad.addColorStop(0.5, '#0f172a');
                    bgGrad.addColorStop(1, '#020617');
                    ctx.fillStyle = bgGrad;
                    ctx.fillRect(0, 0, 1080, 1350);

                    // Sport angle geometry speed lines
                    ctx.save();
                    ctx.strokeStyle = 'rgba(79, 70, 229, 0.16)';
                    ctx.lineWidth = 2;
                    for (let x = -500; x < 1500; x += 60) {
                        ctx.beginPath();
                        ctx.moveTo(x, 0);
                        ctx.lineTo(x + 500, 1350);
                        ctx.stroke();
                    }
                    ctx.restore();

                    // Sports ambient lights
                    ctx.save();
                    const glow1 = ctx.createRadialGradient(200, 250, 20, 200, 250, 450);
                    glow1.addColorStop(0, 'rgba(99, 102, 241, 0.28)');
                    glow1.addColorStop(1, 'rgba(99, 102, 241, 0)');
                    ctx.fillStyle = glow1;
                    ctx.fillRect(0, 0, 1080, 700);

                    const glow2 = ctx.createRadialGradient(880, 1100, 20, 880, 1100, 400);
                    glow2.addColorStop(0, 'rgba(16, 185, 129, 0.20)');
                    glow2.addColorStop(1, 'rgba(16, 185, 129, 0)');
                    ctx.fillStyle = glow2;
                    ctx.fillRect(0, 600, 1080, 750);
                    ctx.restore();
                }

                // 2. Official Ruang Lari Logo (Top Left)
                try {
                    const logoImg = new Image();
                    logoImg.crossOrigin = 'anonymous';
                    await new Promise((resolve) => {
                        logoImg.onload = resolve;
                        logoImg.onerror = resolve;
                        logoImg.src = 'https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp';
                    });
                    if (logoImg.width > 0) {
                        const logoH = 75;
                        const logoW = (logoImg.width / logoImg.height) * logoH;
                        ctx.drawImage(logoImg, 60, 60, logoW, logoH);
                    }
                } catch (err) {}

                // Top Category Pill (Top Right)
                const catText = `${session.value.category || '{{ $category ?? "RACE" }}'} • OFFICIAL FINISHER`.toUpperCase();
                ctx.save();
                ctx.font = 'bold 22px Inter, sans-serif';
                const catWidth = ctx.measureText(catText).width + 48;
                const catX = 1080 - 60 - catWidth;
                ctx.fillStyle = 'rgba(30, 41, 59, 0.9)';
                ctx.strokeStyle = '#4f46e5';
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.roundRect(catX, 68, catWidth, 52, 14);
                ctx.fill();
                ctx.stroke();

                ctx.fillStyle = '#818cf8';
                ctx.fillText(catText, catX + 24, 102);
                ctx.restore();

                // 3. Race Event Title
                ctx.fillStyle = '#94a3b8';
                ctx.font = 'bold 20px Inter, sans-serif';
                ctx.fillText('OFFICIAL RACE RESULT', 60, 190);

                ctx.fillStyle = '#ffffff';
                ctx.font = '900 42px Inter, sans-serif';
                const displayRaceTitle = race.value?.name || initialRaceName;
                ctx.fillText(displayRaceTitle.toUpperCase().slice(0, 32), 60, 245);

                // Divider
                const divGrad = ctx.createLinearGradient(60, 0, 1020, 0);
                divGrad.addColorStop(0, '#4f46e5');
                divGrad.addColorStop(0.5, '#10b981');
                divGrad.addColorStop(1, 'transparent');
                ctx.fillStyle = divGrad;
                ctx.fillRect(60, 270, 960, 4);

                // 4. Hero Finisher Card
                ctx.save();
                ctx.fillStyle = 'rgba(15, 23, 42, 0.82)';
                ctx.strokeStyle = 'rgba(71, 85, 105, 0.8)';
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.roundRect(60, 310, 960, 425, 24);
                ctx.fill();
                ctx.stroke();

                // BIB Tag
                ctx.fillStyle = '#4f46e5';
                ctx.beginPath();
                ctx.roundRect(100, 350, 200, 56, 12);
                ctx.fill();
                ctx.fillStyle = '#ffffff';
                ctx.font = '900 28px Orbitron, monospace';
                ctx.textAlign = 'center';
                ctx.fillText(`BIB #${p.bib}`, 200, 388);

                // Runner Full Name
                ctx.textAlign = 'left';
                ctx.fillStyle = '#ffffff';
                ctx.font = '900 46px Inter, sans-serif';
                ctx.fillText(p.name.toUpperCase().slice(0, 26), 100, 465);

                // Subtitle Official Time
                ctx.fillStyle = '#94a3b8';
                ctx.font = 'bold 22px Inter, sans-serif';
                ctx.fillText('OFFICIAL FINISH TIME (HH:MM:SS)', 100, 520);

                // TIME ONLY HH:MM:SS (NO MILLISECONDS)
                const timeHms = formatTimeHms(p.total_time_ms);
                ctx.fillStyle = '#f59e0b';
                ctx.font = '900 84px Orbitron, monospace';
                ctx.fillText(timeHms, 100, 620);
                ctx.restore();

                // 5. 3-Stat Metric Cards Grid
                const cardY = 765;
                const cardW = 300;
                const cardH = 220;
                const rankStr = p.rank ? `#${p.rank}` : '-';
                const paceStr = paceFor(p);
                const lapsCount = p.laps || 1;
                const distKm = session.value.distance_km || '{{ $distanceKm ?? 0 }}';
                const distStr = distKm ? `${distKm} KM` : `${lapsCount} Laps`;

                const metrics = [
                    { label: 'PERINGKAT', val: rankStr, color: '#f59e0b', sub: `DARI ${results.value.length} FINISHER` },
                    { label: 'AVERAGE PACE', val: paceStr, color: '#10b981', sub: 'MENIT / KM' },
                    { label: 'TOTAL JARAK', val: distStr, color: '#6366f1', sub: `${lapsCount} Putaran Laps` },
                ];

                metrics.forEach((m, i) => {
                    const cX = 60 + i * (cardW + 30);
                    ctx.save();
                    ctx.fillStyle = 'rgba(15, 23, 42, 0.82)';
                    ctx.strokeStyle = 'rgba(71, 85, 105, 0.8)';
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.roundRect(cX, cardY, cardW, cardH, 20);
                    ctx.fill();
                    ctx.stroke();

                    ctx.fillStyle = '#94a3b8';
                    ctx.font = 'bold 20px Inter, sans-serif';
                    ctx.fillText(m.label, cX + 24, cardY + 46);

                    ctx.fillStyle = m.color;
                    ctx.font = '900 48px Orbitron, monospace';
                    ctx.fillText(m.val, cX + 24, cardY + 118);

                    ctx.fillStyle = '#64748b';
                    ctx.font = 'bold 18px Inter, sans-serif';
                    ctx.fillText(m.sub, cX + 24, cardY + 172);
                    ctx.restore();
                });

                // 6. Footer Verified Badge
                const footY = 1025;
                ctx.save();
                ctx.fillStyle = 'rgba(15, 23, 42, 0.65)';
                ctx.strokeStyle = 'rgba(51, 65, 85, 0.6)';
                ctx.beginPath();
                ctx.roundRect(60, footY, 960, 250, 20);
                ctx.fill();
                ctx.stroke();

                ctx.fillStyle = '#e2e8f0';
                ctx.font = 'bold 26px Inter, sans-serif';
                ctx.fillText('RUANG LARI RACE TIMING SYSTEM', 100, footY + 55);

                ctx.fillStyle = '#94a3b8';
                ctx.font = '19px Inter, sans-serif';
                ctx.fillText('Hasil ini diverifikasi secara resmi oleh sistem AI & Digital Timing Gate Ruang Lari.', 100, footY + 95);
                ctx.fillText(`ID Sesi: ${slug} • Tanggal: ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`, 100, footY + 130);

                ctx.fillStyle = '#10b981';
                ctx.font = 'bold 22px Inter, sans-serif';
                ctx.fillText('✓ VERIFIED OFFICIAL FINISHER RESULT • RUANGLARI.COM', 100, footY + 185);
                ctx.restore();

                canvas.toBlob((blob) => {
                    if (!blob) return;
                    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
                        try { URL.revokeObjectURL(previewUrl.value); } catch (_) {}
                    }
                    const url = URL.createObjectURL(blob);
                    previewUrl.value = url;
                    cardFile.value = new File([blob], `finisher-card-BIB-${p.bib}.png`, { type: 'image/png' });
                }, 'image/png');
            } catch (e) {
                console.error('Render card error:', e);
            } finally {
                generating.value = false;
            }
        };

        const openFinisherCard = (participant) => {
            activeParticipant.value = participant;
            bgImage.value = null;
            if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
                try { URL.revokeObjectURL(previewUrl.value); } catch (_) {}
            }
            previewUrl.value = '';
            cardFile.value = null;
            cardModalOpen.value = true;
            nextTick(renderCanvasCard);
        };

        const closeCardModal = () => {
            cardModalOpen.value = false;
            if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
                try { URL.revokeObjectURL(previewUrl.value); } catch (_) {}
            }
            previewUrl.value = '';
            bgImage.value = null;
        };

        const onBgChange = (e) => {
            const file = e?.target?.files?.[0];
            if (!file) {
                bgImage.value = null;
                renderCanvasCard();
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    bgImage.value = img;
                    renderCanvasCard();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        };

        const downloadCard = () => {
            if (!previewUrl.value) return;
            const p = activeParticipant.value;
            const a = document.createElement('a');
            a.href = previewUrl.value;
            a.download = `finisher-card-BIB-${p?.bib || 'runner'}.png`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };

        const shareCard = async () => {
            if (!cardFile.value) return;
            if (!navigator.share || !navigator.canShare || !navigator.canShare({ files: [cardFile.value] })) {
                downloadCard();
                return;
            }
            try {
                await navigator.share({
                    title: `${race.value?.name || initialRaceName} - Kartu Finisher Resmi`,
                    text: `Hasil lari resmi ${activeParticipant.value?.name || ''} (BIB #${activeParticipant.value?.bib || ''})`,
                    files: [cardFile.value],
                });
            } catch (e) {}
        };

        return {
            slug, session, race, results, filteredResults, loading, query, statusFilter,
            formatTimeHms, paceFor, copyLink,
            cardModalOpen, activeParticipant, previewUrl, cardFile, generating,
            openFinisherCard, closeCardModal, onBgChange, downloadCard, shareCard
        };
    }
}).mount('#app');
</script>
</body>
</html>
