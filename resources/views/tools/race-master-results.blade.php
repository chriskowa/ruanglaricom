<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Race Results - {{ $raceName ?? 'Ruang Lari' }}</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Great+Vibes&family=Oswald:wght@300;400;500;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .font-cinzel { font-family: 'Cinzel', serif; }
        .font-great-vibes { font-family: 'Great Vibes', cursive; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen dark:bg-slate-900 dark:text-slate-100 transition-colors">
<div id="app" class="min-h-screen">
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-slate-200 dark:bg-slate-900/80 dark:border-slate-800">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400">Race Results</div>
                <div class="font-bold text-lg truncate">{{ $raceName ?? 'Ruang Lari' }}</div>
            </div>
            <div class="flex items-center gap-3">
                <button @click="toggleDark" class="w-10 h-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:hover:bg-slate-700 transition-colors">
                    <i class="fa-solid" :class="isDark ? 'fa-sun' : 'fa-moon'"></i>
                </button>
                <button @click="copyLink" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition-colors">
                    <i class="fa-solid fa-link mr-2"></i> Share Link
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6 space-y-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 dark:bg-slate-800 dark:border-slate-700">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <div class="text-xs text-slate-500 dark:text-slate-400">Cari peserta</div>
                    <input v-model="query" placeholder="Ketik BIB atau nama..." class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700 outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Ringkasan</div>
                    <div class="mt-1 px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 dark:bg-slate-900 dark:border-slate-700">
                        <div class="text-sm font-bold">@{{ summaryText }}</div>
                        <div v-if="session.category || session.distance_km" class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            @{{ session.category || (session.distance_km ? (session.distance_km + ' km') : '') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden dark:bg-slate-800 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200 dark:bg-slate-900/40 dark:border-slate-700">
                        <tr>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400">Rank</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400">BIB</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400">Nama</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400 text-right">Time</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400 text-right">Pace</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-slate-400 text-center">Media</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="r in filteredResults" :key="r.participant_id" class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="p-4 font-bold text-slate-400 dark:text-slate-500">
                                <span v-if="r.rank" :class="r.rank === 1 ? 'text-yellow-500 text-lg' : ''">#@{{ r.rank }}</span>
                                <span v-else>-</span>
                            </td>
                            <td class="p-4 font-bold font-mono text-slate-900 dark:text-white">@{{ r.bib }}</td>
                            <td class="p-4 font-medium dark:text-slate-200">@{{ r.name }}</td>
                            <td class="p-4 text-right font-mono font-bold text-indigo-600 dark:text-indigo-400">@{{ r.total_time || '-' }}</td>
                            <td class="p-4 text-right font-mono text-slate-600 dark:text-slate-400">@{{ paceFor(r) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button @click="openMedia('certificate', r)" class="w-9 h-9 rounded-full bg-slate-900 hover:bg-black text-white dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors" title="E-Certificate">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </button>
                                    <button @click="openMedia('poster', r)" class="w-9 h-9 rounded-full bg-pink-600 hover:bg-pink-700 text-white transition-colors" title="Poster IG Story">
                                        <i class="fa-brands fa-instagram"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!loading && filteredResults.length === 0">
                            <td colspan="6" class="p-8 text-center text-slate-400">Tidak ada hasil.</td>
                        </tr>
                        <tr v-if="loading">
                            <td colspan="6" class="p-8 text-center text-slate-400">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div v-if="mediaModal.open" class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm p-4 flex items-center justify-center overflow-y-auto" @click.self="closeMedia">
        <div :class="['w-full transition-all duration-300', mediaModal.type === 'certificate' ? 'max-w-4xl' : 'max-w-md']" class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xl p-6">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div class="min-w-0">
                    <div class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-widest">@{{ mediaModal.type === 'poster' ? 'Poster IG Story' : 'E-Certificate' }}</div>
                    <div class="font-bold truncate">@{{ mediaModal.participant?.name }} (#@{{ mediaModal.participant?.bib }})</div>
                </div>
                <button @click="closeMedia" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>

            <!-- Certificate View -->
            <div v-if="mediaModal.type === 'certificate'" class="space-y-4">
                <div id="certificate-node" class="relative bg-slate-900 text-white p-0 overflow-hidden shadow-2xl mx-auto aspect-[1.414/1] flex flex-col" style="min-height: 600px;">
                    <!-- Decorative Background -->
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900"></div>
                    <!-- Sporty diagonal lines -->
                    <div class="absolute top-0 right-0 w-1/2 h-full bg-white/5 -skew-x-12 transform origin-top-right"></div>
                    <div class="absolute bottom-0 left-0 w-1/2 h-full bg-white/5 -skew-x-12 transform origin-bottom-left"></div>
                    
                    <!-- Content Container -->
                    <div class="relative z-10 flex flex-col h-full p-12">
                        <!-- Header -->
                        <div class="flex justify-between items-start">
                             <img v-if="race.logo_url" :src="race.logo_url" class="h-20 object-contain grayscale brightness-200">
                             <div class="text-right">
                                 <h1 class="font-oswald text-5xl font-bold tracking-widest text-white uppercase italic">Certificate</h1>
                                 <div class="text-slate-400 tracking-[0.3em] uppercase text-sm mt-1 font-bold">Of Achievement</div>
                             </div>
                        </div>

                        <!-- Main Content (Centered) -->
                        <div class="flex-1 flex flex-col justify-center items-center text-center space-y-8">
                            <div class="w-24 h-1.5 bg-yellow-500 rounded-full skew-x-12"></div>
                            
                            <div class="space-y-4">
                                <p class="text-slate-400 uppercase tracking-widest text-sm font-medium">This certifies that</p>
                                <h2 class="font-oswald text-6xl md:text-7xl font-bold text-white uppercase tracking-wide leading-tight">
                                    @{{ mediaModal.participant?.name }}
                                </h2>
                            </div>

                            <div class="text-slate-300 text-xl font-light">
                                Has successfully completed the <span class="text-yellow-400 font-bold italic text-2xl">@{{ session.category || (session.distance_km + ' KM') }}</span> category
                                <br>at <span class="font-bold uppercase text-white tracking-wide">@{{ race.name }}</span>
                            </div>
                        </div>

                        <!-- Stats Grid (Dashboard style) -->
                        <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10 mt-auto">
                            <div class="grid grid-cols-4 divide-x divide-white/10">
                                <div class="px-4 text-center">
                                    <div class="text-[10px] text-slate-400 uppercase tracking-[0.2em] mb-2">Rank</div>
                                    <div class="font-oswald text-4xl font-bold text-white italic">#@{{ mediaModal.participant?.rank || '-' }}</div>
                                </div>
                                <div class="px-4 text-center">
                                    <div class="text-[10px] text-slate-400 uppercase tracking-[0.2em] mb-2">Time</div>
                                    <div class="font-mono text-3xl font-bold text-white">@{{ mediaModal.participant?.total_time }}</div>
                                </div>
                                 <div class="px-4 text-center">
                                    <div class="text-[10px] text-slate-400 uppercase tracking-[0.2em] mb-2">Pace</div>
                                    <div class="font-mono text-3xl font-bold text-white">@{{ paceFor(mediaModal.participant) }}</div>
                                </div>
                                <div class="px-4 text-center">
                                    <div class="text-[10px] text-slate-400 uppercase tracking-[0.2em] mb-2">Date</div>
                                    <div class="font-mono text-xl font-bold text-white pt-2">@{{ formatDate(session.ended_at) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer ID -->
                        <div class="mt-8 flex justify-between items-end">
                            <div class="text-[10px] text-slate-600 font-mono uppercase">
                                ID: @{{ session.slug }}-@{{ mediaModal.participant?.bib }}
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-500 uppercase tracking-widest">Organized by</span>
                                <span class="font-oswald text-xl text-white italic font-bold">Ruang Lari Race Master</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 justify-end pt-4">
                     <button @click="downloadCertificateImage" :disabled="mediaModal.loading" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition-colors disabled:opacity-50">
                        <i v-if="mediaModal.loading" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                        Download Image
                    </button>
                </div>
            </div>

            <!-- Poster View -->
            <div v-else class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-300">Background Image (opsional)</label>
                    <input @change="onBgChange" type="file" accept="image/png,image/jpeg" class="mt-1 w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 dark:bg-slate-900 dark:border-slate-700">
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jika kosong, akan pakai background default.</div>
                </div>

                <button @click="generateMedia" :disabled="mediaModal.loading" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-bold transition-colors">
                    <i v-if="mediaModal.loading" class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    Generate
                </button>

                <div v-if="mediaModal.error" class="text-sm text-red-600 dark:text-red-400">@{{ mediaModal.error }}</div>

                <div v-if="mediaModal.previewUrl" class="mt-2">
                    <img :src="mediaModal.previewUrl" class="w-full rounded-xl border border-slate-200 dark:border-slate-700">
                </div>

                <div v-if="mediaModal.downloadUrl" class="flex gap-2">
                    <a :href="mediaModal.downloadUrl" target="_blank" class="flex-1 text-center py-3 rounded-xl bg-slate-900 hover:bg-black text-white font-bold transition-colors dark:bg-slate-700 dark:hover:bg-slate-600">
                        Download
                    </a>
                    <button v-if="mediaModal.file && canShareFiles" @click="shareFile" class="flex-1 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-colors">
                        Share
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const { createApp, ref, computed } = Vue;

createApp({
    setup() {
        const slug = @json($slug);
        const apiBase = @json(url('api/tools/race-master'));

        const isDark = ref(localStorage.getItem('race-master-results-theme') === 'dark');
        if (isDark.value) document.documentElement.classList.add('dark');
        const toggleDark = () => {
            isDark.value = !isDark.value;
            localStorage.setItem('race-master-results-theme', isDark.value ? 'dark' : 'light');
            if (isDark.value) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        };

        const loading = ref(true);
        const query = ref('');
        const session = ref({});
        const race = ref({});
        const results = ref([]);

        const canShareFiles = !!(navigator && navigator.share && navigator.canShare);

        const fetchResults = async () => {
            loading.value = true;
            try {
                const res = await fetch(`${apiBase}/public/${encodeURIComponent(slug)}/results`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                session.value = data.session || {};
                race.value = data.race || {};
                results.value = data.results || [];
            } finally {
                loading.value = false;
            }
        };

        fetchResults();

        const filteredResults = computed(() => {
            const q = query.value.trim().toLowerCase();
            if (!q) return results.value;
            return results.value.filter(r => String(r.bib).toLowerCase().includes(q) || String(r.name).toLowerCase().includes(q));
        });

        const summaryText = computed(() => {
            const count = results.value.length;
            return count ? `${count} peserta` : '—';
        });

        const paceFor = (r) => {
            const dist = session.value.distance_km;
            if (!dist || !r.total_time_ms) return '-';
            const sec = Math.max(1, Math.floor(r.total_time_ms / 1000));
            const paceSec = Math.round(sec / dist);
            const m = Math.floor(paceSec / 60);
            const s = paceSec % 60;
            return `${m}:${String(s).padStart(2,'0')}/km`;
        };
        
        const formatDate = (isoString) => {
            if (!isoString) return '-';
            return new Date(isoString).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        };

        const copyLink = async () => {
            const url = window.location.href;
            try {
                await navigator.clipboard.writeText(url);
                alert('Link berhasil disalin.');
            } catch (e) {
                alert(url);
            }
        };

        const mediaModal = ref({ open: false, type: 'poster', participant: null, loading: false, error: '', bgFile: null, previewUrl: '', downloadUrl: '', file: null });

        const openMedia = (type, participant) => {
            mediaModal.value = { open: true, type, participant, loading: false, error: '', bgFile: null, previewUrl: '', downloadUrl: '', file: null };
        };

        const closeMedia = () => {
            if (mediaModal.value.previewUrl && mediaModal.value.previewUrl.startsWith('blob:')) {
                try { URL.revokeObjectURL(mediaModal.value.previewUrl); } catch (e) {}
            }
            mediaModal.value.open = false;
        };

        const onBgChange = (e) => {
            mediaModal.value.bgFile = e?.target?.files?.[0] || null;
        };

        const downloadCertificateImage = async () => {
            const node = document.getElementById('certificate-node');
            if (!node) return;
            mediaModal.value.loading = true;
            try {
                const dataUrl = await htmlToImage.toPng(node, { pixelRatio: 2 });
                const link = document.createElement('a');
                link.download = `Certificate-${mediaModal.value.participant.bib}.png`;
                link.href = dataUrl;
                link.click();
            } catch (error) {
                console.error('oops, something went wrong!', error);
                alert('Gagal generate gambar certificate.');
            } finally {
                mediaModal.value.loading = false;
            }
        };

        const generateMedia = async () => {
            const p = mediaModal.value.participant;
            if (!p) return;
            mediaModal.value.loading = true;
            mediaModal.value.error = '';
            mediaModal.value.downloadUrl = '';
            mediaModal.value.file = null;

            try {
                if (mediaModal.value.type === 'certificate') {
                    // Deprecated: API call removed, using frontend generation
                } else {
                    const form = new FormData();
                    if (mediaModal.value.bgFile) form.append('background', mediaModal.value.bgFile);
                    const res = await fetch(`${apiBase}/public/${encodeURIComponent(slug)}/participants/${encodeURIComponent(p.bib)}/poster`, { method: 'POST', body: form });
                    if (!res.ok) {
                        const data = await res.json().catch(() => null);
                        throw new Error(data?.message || 'Gagal generate poster');
                    }
                    const blob = await res.blob();
                    const url = URL.createObjectURL(blob);
                    mediaModal.value.previewUrl = url;
                    mediaModal.value.file = new File([blob], `poster-${p.bib}.png`, { type: 'image/png' });
                    mediaModal.value.downloadUrl = url;
                }
            } catch (e) {
                mediaModal.value.error = e?.message || 'Error';
            } finally {
                mediaModal.value.loading = false;
            }
        };

        const shareFile = async () => {
            if (!mediaModal.value.file) return;
            if (!navigator.share || !navigator.canShare({ files: [mediaModal.value.file] })) return;
            await navigator.share({ files: [mediaModal.value.file], title: 'Race Result', text: 'Hasil race' });
        };

        return {
            isDark, toggleDark,
            loading, query, session, race, results, filteredResults, summaryText,
            paceFor, formatDate, copyLink,
            mediaModal, openMedia, closeMedia, onBgChange, generateMedia, downloadCertificateImage,
            canShareFiles, shareFile
        };
    }
}).mount('#app');
</script>
</body>
</html>

