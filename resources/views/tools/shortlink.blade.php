@extends('layouts.pacerhub')

@section('title', 'URL Shortener - Ruang Lari Tools')

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="relative z-10 max-w-4xl mx-auto text-center mb-12">
        <h1 class="text-3xl md:text-5xl font-black text-white italic tracking-tighter mb-4">
            URL <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-400 to-orange-400">SHORTENER</span>
        </h1>
        <p class="text-slate-400 text-lg">
            Persingkat tautan panjang Anda menjadi URL pendek yang rapi dan mudah dibagikan.
        </p>
    </div>

    <!-- Generator & History Card -->
    <div class="max-w-4xl mx-auto relative z-10 space-y-8">
        
        <!-- Generator Card -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-3xl p-6 md:p-8">
            <form id="shortenForm" class="space-y-6">
                @csrf
                <div>
                    <label for="urlInput" class="block text-sm font-bold text-slate-300 mb-2">Long URL / Tautan Panjang</label>
                    <div class="flex flex-col md:flex-row gap-3">
                        <div class="relative flex-grow">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <input type="url" id="urlInput" name="url" required
                                class="w-full bg-slate-900 border border-slate-600 rounded-2xl pl-12 pr-4 py-4 text-white focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none transition-all placeholder-slate-500 text-sm md:text-base"
                                placeholder="Masukkan URL panjang (misal: https://strava.com/activities/...)">
                        </div>
                        <button type="submit" id="submitBtn"
                            class="px-8 py-4 rounded-2xl bg-gradient-to-r from-rose-600 to-orange-600 hover:from-rose-500 hover:to-orange-500 text-white font-black shadow-lg shadow-rose-500/20 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 shrink-0">
                            <span id="btnText">Shorten URL</span>
                            <svg id="loadingSpinner" class="animate-spin h-5 w-5 text-white hidden" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                    <p id="errorMessage" class="text-rose-400 text-xs font-bold mt-2 hidden"></p>
                </div>
            </form>

            <!-- Result Area -->
            <div id="resultArea" class="mt-8 pt-8 border-t border-slate-700/60 hidden">
                <div class="bg-slate-900/60 rounded-2xl p-6 border border-slate-700/50 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="space-y-1 w-full md:w-auto text-left">
                        <span class="text-xs font-black text-rose-400 tracking-wider uppercase">Tautan Pendek Berhasil Dibuat</span>
                        <div class="text-lg md:text-xl font-bold text-white break-all select-all font-mono" id="shortenedUrl"></div>
                        <div class="text-xs text-slate-500 truncate max-w-md" id="originalUrlPreview"></div>
                    </div>
                    <div class="flex gap-2 w-full md:w-auto">
                        <button onclick="copyToClipboard()" id="copyBtn"
                            class="flex-grow md:flex-grow-0 flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold transition-all border border-slate-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span id="copyBtnText">Salin</span>
                        </button>
                        <a href="" id="visitBtn" target="_blank"
                            class="flex-grow md:flex-grow-0 flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold transition-all">
                            <span>Buka</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Card -->
        <div id="historyCard" class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-3xl p-6 md:p-8 hidden">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-black text-white italic tracking-tighter uppercase">Riwayat Singkat Tautan</h3>
                <button onclick="clearHistory()" class="text-xs font-bold text-slate-400 hover:text-rose-400 transition-colors uppercase tracking-wider">
                    Hapus Semua
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase font-bold">
                            <th class="pb-3 pr-4">Original URL</th>
                            <th class="pb-3 px-4">Short URL</th>
                            <th class="pb-3 pl-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody" class="text-sm divide-y divide-slate-700/40">
                        <!-- History rows injected here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[20%] left-[-10%] w-[40%] h-[40%] bg-rose-600/10 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-orange-600/10 rounded-full blur-[100px]"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadHistory();
        
        const form = document.getElementById('shortenForm');
        const urlInput = document.getElementById('urlInput');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const errorMessage = document.getElementById('errorMessage');
        const resultArea = document.getElementById('resultArea');
        const shortenedUrl = document.getElementById('shortenedUrl');
        const originalUrlPreview = document.getElementById('originalUrlPreview');
        const visitBtn = document.getElementById('visitBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Reset states
            errorMessage.classList.add('hidden');
            resultArea.classList.add('hidden');
            submitBtn.disabled = true;
            btnText.textContent = 'Memproses...';
            loadingSpinner.classList.remove('hidden');

            const url = urlInput.value.trim();

            try {
                const response = await fetch('{{ route("tools.shortlink.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ url })
                });

                const data = await response.json();

                if (response.ok) {
                    // Show result
                    shortenedUrl.textContent = data.short_url;
                    originalUrlPreview.textContent = url;
                    visitBtn.href = data.short_url;
                    resultArea.classList.remove('hidden');
                    
                    // Add to history
                    saveToHistory(url, data.short_url, data.code);
                    
                    // Clear input
                    urlInput.value = '';
                } else {
                    errorMessage.textContent = data.message || 'Gagal menyingkat URL. Silakan periksa kembali.';
                    errorMessage.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
                errorMessage.textContent = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
                errorMessage.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                btnText.textContent = 'Shorten URL';
                loadingSpinner.classList.add('hidden');
            }
        });
    });

    // History management
    function getHistory() {
        return JSON.parse(localStorage.getItem('rl_shortlink_history') || '[]');
    }

    function saveToHistory(originalUrl, shortUrl, code) {
        let history = getHistory();
        
        // Remove duplicate of same original url if exists
        history = history.filter(item => item.originalUrl !== originalUrl);
        
        // Prepend new item
        history.unshift({ originalUrl, shortUrl, code, timestamp: Date.now() });
        
        // Limit to 10 items
        if(history.length > 10) {
            history.pop();
        }
        
        localStorage.setItem('rl_shortlink_history', JSON.stringify(history));
        loadHistory();
    }

    function loadHistory() {
        const history = getHistory();
        const historyCard = document.getElementById('historyCard');
        const tbody = document.getElementById('historyTableBody');
        
        if (history.length === 0) {
            historyCard.classList.add('hidden');
            return;
        }

        historyCard.classList.remove('hidden');
        tbody.innerHTML = '';

        history.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-slate-700/40 text-slate-300 hover:bg-slate-700/20 transition-colors';
            
            // Format original URL with tooltip
            let shortOriginal = item.originalUrl;
            if(shortOriginal.length > 60) {
                shortOriginal = shortOriginal.substring(0, 57) + '...';
            }

            tr.innerHTML = `
                <td class="py-4 pr-4 truncate max-w-[200px] md:max-w-md font-medium text-slate-400" title="${item.originalUrl}">
                    ${shortOriginal}
                </td>
                <td class="py-4 px-4 font-mono font-bold text-rose-300">
                    <a href="${item.shortUrl}" target="_blank" class="hover:underline">${item.shortUrl}</a>
                </td>
                <td class="py-4 pl-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="copyLink('${item.shortUrl}', this)" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors border border-slate-700" title="Salin Tautan">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </button>
                        <button onclick="deleteHistoryItem(${index})" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-900/40 text-slate-400 hover:text-rose-400 transition-colors border border-slate-700" title="Hapus">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function deleteHistoryItem(index) {
        let history = getHistory();
        history.splice(index, 1);
        localStorage.setItem('rl_shortlink_history', JSON.stringify(history));
        loadHistory();
    }

    function clearHistory() {
        if(confirm('Apakah Anda yakin ingin menghapus semua riwayat short link di browser ini?')) {
            localStorage.removeItem('rl_shortlink_history');
            loadHistory();
        }
    }

    // Copy to clipboard helpers
    function copyToClipboard() {
        const text = document.getElementById('shortenedUrl').textContent;
        navigator.clipboard.writeText(text).then(() => {
            const copyBtnText = document.getElementById('copyBtnText');
            const originalText = copyBtnText.textContent;
            copyBtnText.textContent = 'Tersalin!';
            setTimeout(() => {
                copyBtnText.textContent = originalText;
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }

    function copyLink(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = `
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            `;
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
</script>
@endpush
