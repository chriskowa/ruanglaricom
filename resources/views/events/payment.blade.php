@extends('layouts.pacerhub')

@section('title', 'Instruksi Pembayaran - ' . $event->name)

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <style>
        .font-brand-heading {
            font-family: 'Inter Tight', 'Sora', sans-serif;
            letter-spacing: -0.03em;
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100 py-6 sm:py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">

        <!-- Top Navigation -->
        <div class="mb-5 flex items-center justify-between">
            <a href="{{ route('events.show', $event->slug) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition">
                <i class="fa-solid fa-arrow-left text-[11px]"></i>
                <span>Kembali ke Halaman Event</span>
            </a>
            <div class="text-xs text-slate-400">
                Ref: <span class="font-mono text-slate-200 font-bold">{{ $transaction->public_ref ?? '#'.$transaction->id }}</span>
            </div>
        </div>

        <!-- Page Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 pb-5 border-b border-slate-800">
            <div>
                <h1 class="font-brand-heading text-2xl sm:text-3xl font-extrabold text-white">
                    Instruksi Pembayaran
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">
                    Event: <span class="font-semibold text-white">{{ $event->name }}</span>
                </p>
            </div>

            <!-- Transaction Status Badge -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Status:</span>
                @if($transaction->payment_status === 'paid')
                    <div id="payment-status-badge" class="px-2.5 py-1 rounded bg-emerald-500/10 text-emerald-400 text-xs font-bold uppercase tracking-wider border border-emerald-500/30">
                        LUNAS
                    </div>
                @elseif(in_array($transaction->payment_status, ['failed', 'expired', 'cancelled']))
                    <div id="payment-status-badge" class="px-2.5 py-1 rounded bg-rose-500/10 text-rose-400 text-xs font-bold uppercase tracking-wider border border-rose-500/30">
                        {{ strtoupper($transaction->payment_status) }}
                    </div>
                @else
                    <div id="payment-status-badge" class="px-2.5 py-1 rounded bg-amber-500/10 text-amber-300 text-xs font-bold uppercase tracking-wider border border-amber-500/30 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        MENUNGGU PEMBAYARAN
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Layout: 2 Columns on LG -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Column: Payment Amount, Bank Accounts, Proof Upload -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Card 1: Total Transfer Amount -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total yang Harus Ditransfer</span>
                        @if(($transaction->unique_code ?? 0) > 0)
                            <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-300 font-mono font-bold text-xs border border-amber-500/20">
                                Kode Unik: +{{ $transaction->unique_code }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-3 pt-2">
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-white tabular-nums tracking-tight">
                            Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}
                        </div>
                        <button type="button" onclick="copyAmount('{{ (int)$transaction->final_amount }}', this)" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition border border-slate-700 active:scale-95">
                            <i class="fa-regular fa-copy text-xs"></i>
                            <span class="copy-btn-label">Salin Nominal</span>
                        </button>
                    </div>

                    @if(($transaction->unique_code ?? 0) > 0)
                        <div class="mt-4 p-3 rounded-md bg-amber-950/30 border border-amber-500/20 text-xs text-amber-200 leading-relaxed">
                            <strong class="text-white">PENTING:</strong> Transfer tepat hingga 3 digit terakhir (<span class="font-mono font-bold text-white">{{ $transaction->unique_code }}</span>) agar memudahkan sistem dan panitia memverifikasi pesanan Anda secara cepat.
                        </div>
                    @else
                        <div class="mt-4 p-3 rounded-md bg-slate-950 border border-slate-800 text-xs text-slate-300 leading-relaxed">
                            Pastikan nominal transfer Anda persis sama dengan angka yang tertera di atas untuk mempercepat verifikasi.
                        </div>
                    @endif
                </div>

                <!-- Card 2: Bank Accounts -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                    <div class="mb-4">
                        <h2 class="font-brand-heading text-base font-bold text-white">Rekening Tujuan Transfer</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Silakan transfer ke salah satu rekening resmi berikut:</p>
                    </div>

                    <div class="space-y-3">
                        @forelse($bankAccounts as $bank)
                            <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:border-slate-700 transition">
                                <div class="flex items-center gap-3.5">
                                    <div class="px-2.5 py-1.5 rounded bg-slate-800 border border-slate-700 text-white font-mono font-bold text-xs uppercase tracking-wider shrink-0">
                                        {{ strtoupper($bank['bank_type'] ?? 'BANK') }}
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-400 font-medium">a.n. {{ $bank['name'] ?? 'Panitia Event' }}</div>
                                        <div class="text-base sm:text-lg font-bold text-white font-mono tracking-wider tabular-nums mt-0.5">
                                            {{ $bank['account_number'] ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                                <button type="button" onclick="copyAccount('{{ $bank['account_number'] ?? '' }}', this)" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition border border-slate-700 shrink-0 active:scale-95">
                                    <i class="fa-regular fa-copy text-xs"></i>
                                    <span class="copy-btn-label">Salin Rekening</span>
                                </button>
                            </div>
                        @empty
                            <div class="p-4 rounded-md bg-slate-950 border border-slate-800 text-xs text-slate-400">
                                Informasi rekening sedang disiapkan panitia. Silakan hubungi narahubung event.
                            </div>
                        @endforelse
                    </div>
                </div>

                @if(!empty($isManualTransfer))
                <!-- Card 3: Upload Proof (Manual Transfer EO) -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-brand-heading text-base font-bold text-white">Unggah Bukti Transfer</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Foto struk ATM atau tangkapan layar m-banking Anda</p>
                        </div>
                        <div id="proof-status-badge">
                            @if($transaction->payment_proof)
                                <span class="px-2.5 py-1 rounded bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/20">
                                    Bukti Terkirim
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded bg-slate-800 text-slate-400 text-xs font-medium border border-slate-700">
                                    Belum Diunggah
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Current Upload Status Preview -->
                    <div id="proof-current-info" class="{{ $transaction->payment_proof ? '' : 'hidden' }} mb-4 p-3.5 rounded-lg bg-slate-950 border border-slate-800">
                        <div class="flex items-center gap-3.5">
                            <img id="current-proof-img" src="{{ $transaction->payment_proof ? asset('storage/'.$transaction->payment_proof) : '' }}" alt="Bukti Transfer" class="w-14 h-14 rounded object-cover border border-slate-700 shrink-0 cursor-pointer hover:opacity-80 transition" onclick="window.open(this.src, '_blank')">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-emerald-400">Bukti pembayaran telah diterima sistem</p>
                                <p class="text-[11px] text-slate-400 mt-0.5" id="current-proof-time">
                                    Diunggah: {{ $transaction->payment_proof_uploaded_at ? $transaction->payment_proof_uploaded_at->format('d M Y H:i') : '' }}
                                </p>
                                <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">Panitia sedang memverifikasi data Anda. e-Tiket akan dikirim otomatis ke email Anda setelah disetujui.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form id="upload-proof-form" onsubmit="submitPaymentProof(event)" class="space-y-4">
                        @csrf
                        <div>
                            <div id="drop-area" class="border border-dashed border-slate-700 hover:border-slate-500 rounded-lg p-5 text-center cursor-pointer transition bg-slate-950" onclick="document.getElementById('proof-file-input').click()">
                                <input type="file" id="proof-file-input" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleProofFileSelect(this)">
                                <div id="drop-prompt">
                                    <div class="w-10 h-10 rounded-md bg-slate-800 text-slate-300 flex items-center justify-center mx-auto mb-2.5">
                                        <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-white">Klik untuk memilih file atau seret foto ke sini</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Format JPG, PNG, atau WebP (Otomatis dikompresi ringan di peramban)</p>
                                </div>
                                <div id="preview-wrapper" class="hidden">
                                    <img id="image-preview" src="" alt="Preview Bukti" class="max-h-48 mx-auto rounded border border-slate-700 object-contain">
                                    <p class="text-[11px] text-slate-300 mt-2 font-mono" id="file-size-info"></p>
                                    <button type="button" onclick="event.stopPropagation(); resetProofSelection()" class="text-xs text-rose-400 hover:underline mt-1.5 inline-block">Ganti File</button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="proof-notes" class="block text-xs font-medium text-slate-300 mb-1.5">Catatan Tambahan (Opsional)</label>
                            <input type="text" id="proof-notes" name="notes" value="{{ $transaction->proof_notes ?? '' }}" placeholder="Contoh: Transfer a.n. Budi Santoso" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-slate-600 focus:outline-none">
                        </div>

                        <button type="submit" id="btn-submit-proof" class="w-full py-2.5 px-4 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs transition flex items-center justify-center gap-2 disabled:opacity-50">
                            <span id="btn-proof-text">Kirim Bukti Transfer</span>
                            <span id="btn-proof-spinner" class="hidden"><i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Mengompresi & Mengunggah...</span>
                        </button>
                        <div id="upload-feedback" class="text-xs hidden p-3 rounded-md text-center"></div>
                    </form>
                </div>
                @endif

            </div>

            <!-- Right Column: Expiration Timer, Participant Summary, Transfer Guide -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Card 1: Batas Waktu Pembayaran -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Batas Waktu</span>
                        <span class="text-[11px] text-slate-400">24 Jam</span>
                    </div>

                    <div id="timer" class="mt-2 text-3xl font-extrabold font-mono text-white tracking-tight tabular-nums" data-expires="{{ $transaction->created_at->addHours(24)->toISOString() }}">
                        --:--:--
                    </div>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Selesaikan pembayaran sebelum <span class="text-slate-200 font-semibold">{{ $transaction->created_at->addHours(24)->translatedFormat('d M Y, H:i') }} WIB</span> agar pesanan tidak dibatalkan otomatis.
                    </p>
                </div>

                <!-- Card 2: Ringkasan Peserta -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-3">Peserta Terdaftar</span>

                    @if($transaction->participants && $transaction->participants->count() > 0)
                        <div class="space-y-2.5">
                            @foreach($transaction->participants as $participant)
                                <div class="p-3 rounded-md bg-slate-950 border border-slate-800 text-xs">
                                    <div class="font-semibold text-white">{{ $participant->name }}</div>
                                    <div class="text-slate-400 text-[11px] mt-0.5 flex items-center justify-between">
                                        <span>{{ $participant->category->name ?? 'Kategori Utama' }}</span>
                                        @if($participant->jersey_size)
                                            <span class="font-mono text-slate-300">Jersey {{ $participant->jersey_size }}</span>
                                        @endif
                                    </div>
                                    @if($participant->bib_number)
                                        <div class="mt-1.5 pt-1.5 border-t border-slate-800/80 font-mono text-[11px] text-slate-300">
                                            BIB: {{ $participant->bib_number }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @elseif(!empty($transaction->pic_data) && is_array($transaction->pic_data))
                        <div class="p-3 rounded-md bg-slate-950 border border-slate-800 text-xs">
                            <div class="font-semibold text-white">{{ $transaction->pic_data['name'] ?? 'Peserta' }}</div>
                            <div class="text-slate-400 text-[11px] mt-0.5">{{ $transaction->pic_data['email'] ?? '-' }}</div>
                        </div>
                    @else
                        <div class="text-xs text-slate-400">1 Tiket Registrasi</div>
                    @endif
                </div>

                <!-- Card 3: Petunjuk Transfer -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-3">Petunjuk Pembayaran</span>

                    @if(!empty($instructions))
                        <div class="text-xs text-slate-300 leading-relaxed space-y-2">
                            {!! nl2br(e($instructions)) !!}
                        </div>
                    @else
                        <ol class="space-y-3 text-xs text-slate-300 list-decimal list-inside leading-relaxed">
                            <li>Buka aplikasi m-Banking atau kunjungi ATM bank Anda.</li>
                            <li>Pilih menu <strong class="text-white">Transfer</strong> lalu masukkan nomor rekening tujuan yang tertera.</li>
                            <li>Masukkan nominal transfer <strong class="text-white">persis sama</strong> dengan total tagihan.</li>
                            <li>Simpan struk transaksi dan unggah bukti transfer pada formulir yang tersedia.</li>
                        </ol>
                    @endif
                </div>

            </div>

        </div>

    </div>
</div>

<!-- Inline Toast for Clipboard Copying -->
<div id="clipboard-toast" class="fixed bottom-6 right-6 z-50 px-3.5 py-2 rounded-md bg-slate-800 border border-slate-700 text-white text-xs font-medium shadow-lg transform translate-y-10 opacity-0 pointer-events-none transition-all duration-200">
    <span id="clipboard-toast-msg">Tersalin ke clipboard</span>
</div>

<script>
    // Toast helper
    function showToast(msg) {
        const toast = document.getElementById('clipboard-toast');
        const toastMsg = document.getElementById('clipboard-toast-msg');
        if (!toast || !toastMsg) return;
        toastMsg.textContent = msg;
        toast.classList.remove('translate-y-10', 'opacity-0', 'pointer-events-none');
        toast.classList.add('translate-y-0', 'opacity-100');
        clearTimeout(window._toastTimeout);
        window._toastTimeout = setTimeout(() => {
            toast.classList.add('translate-y-10', 'opacity-0', 'pointer-events-none');
            toast.classList.remove('translate-y-0', 'opacity-100');
        }, 2000);
    }

    // Copy Amount Button
    function copyAmount(amount, btn) {
        navigator.clipboard.writeText(amount).then(() => {
            showToast('Nominal Rp ' + Number(amount).toLocaleString('id-ID') + ' disalin');
            if (btn) {
                const label = btn.querySelector('.copy-btn-label');
                if (label) {
                    const original = label.textContent;
                    label.textContent = 'Disalin!';
                    setTimeout(() => { label.textContent = original; }, 1500);
                }
            }
        });
    }

    // Copy Bank Account Button
    function copyAccount(accNum, btn) {
        navigator.clipboard.writeText(accNum).then(() => {
            showToast('Nomor rekening ' + accNum + ' disalin');
            if (btn) {
                const label = btn.querySelector('.copy-btn-label');
                if (label) {
                    const original = label.textContent;
                    label.textContent = 'Disalin!';
                    setTimeout(() => { label.textContent = original; }, 1500);
                }
            }
        });
    }

    // Payment Status Polling
    (function() {
        const statusUrl = "{{ route('api.events.payments.status', ['slug' => $event->slug, 'transaction' => $transaction->id]) }}";
        const successUrl = "{{ route('events.show', $event->slug) }}?success=true";
        const badgeEl = document.getElementById('payment-status-badge');
        let pollInterval;

        function checkStatus() {
            fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    const status = data.transaction ? data.transaction.payment_status : 'pending';

                    if (status === 'paid') {
                        if (badgeEl) {
                            badgeEl.className = 'px-2.5 py-1 rounded bg-emerald-500/10 text-emerald-400 text-xs font-bold uppercase tracking-wider border border-emerald-500/30';
                            badgeEl.textContent = 'LUNAS';
                        }
                        clearInterval(pollInterval);
                        window.location.href = successUrl;
                    } else if (['failed', 'expired', 'cancelled'].includes(status)) {
                        if (badgeEl) {
                            badgeEl.className = 'px-2.5 py-1 rounded bg-rose-500/10 text-rose-400 text-xs font-bold uppercase tracking-wider border border-rose-500/30';
                            badgeEl.textContent = status.toUpperCase();
                        }
                        clearInterval(pollInterval);
                    }
                })
                .catch(e => console.error('Status check error', e));
        }

        pollInterval = setInterval(checkStatus, 5000);
        checkStatus();
    })();

    // Timer Countdown Logic
    const timerEl = document.getElementById('timer');
    if (timerEl && timerEl.dataset.expires) {
        const expiresAt = new Date(timerEl.dataset.expires).getTime();

        function updateTimer() {
            const now = new Date().getTime();
            const distance = expiresAt - now;

            if (distance < 0) {
                timerEl.innerHTML = "KADALUARSA";
                timerEl.classList.add('text-rose-400');
                timerEl.classList.remove('text-white');
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            timerEl.innerHTML = 
                String(hours).padStart(2, '0') + ":" + 
                String(minutes).padStart(2, '0') + ":" + 
                String(seconds).padStart(2, '0');
        }

        setInterval(updateTimer, 1000);
        updateTimer();
    }

    // Client-side Image Compression & Proof Upload
    let compressedProofBase64 = null;

    function formatBytes(bytes, decimals = 1) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    function processProofFile(file) {
        if (!file) return;

        if (!file.type.match(/image\/(jpeg|png|webp)/i)) {
            alert('Silakan pilih file gambar dengan format JPG, PNG, atau WebP.');
            return;
        }

        const dropPrompt = document.getElementById('drop-prompt');
        const previewWrapper = document.getElementById('preview-wrapper');
        const imagePreview = document.getElementById('image-preview');
        const fileSizeInfo = document.getElementById('file-size-info');
        const feedback = document.getElementById('upload-feedback');
        if (feedback) feedback.classList.add('hidden');

        const originalSize = file.size;
        const reader = new FileReader();

        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const maxDim = 1280;
                let width = img.width;
                let height = img.height;

                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                let compressedDataUrl = canvas.toDataURL('image/webp', 0.82);
                if (!compressedDataUrl.startsWith('data:image/webp')) {
                    compressedDataUrl = canvas.toDataURL('image/jpeg', 0.82);
                }

                compressedProofBase64 = compressedDataUrl;

                const base64Length = compressedDataUrl.length - (compressedDataUrl.indexOf(',') + 1);
                const compressedSize = Math.round((base64Length * 3) / 4);

                if (imagePreview) imagePreview.src = compressedDataUrl;
                if (fileSizeInfo) {
                    const savedPct = originalSize > compressedSize 
                        ? Math.round(((originalSize - compressedSize) / originalSize) * 100) 
                        : 0;
                    fileSizeInfo.innerHTML = `Ukuran: <strong class="text-white">${formatBytes(compressedSize)}</strong> ` +
                        `<span class="text-slate-400">(asli: ${formatBytes(originalSize)}${savedPct > 0 ? `, hemat ${savedPct}%` : ''})</span>`;
                }

                if (dropPrompt) dropPrompt.classList.add('hidden');
                if (previewWrapper) previewWrapper.classList.remove('hidden');
            };
            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    }

    function handleProofFileSelect(input) {
        if (input.files && input.files[0]) {
            processProofFile(input.files[0]);
        }
    }

    function resetProofSelection() {
        compressedProofBase64 = null;
        const input = document.getElementById('proof-file-input');
        if (input) input.value = '';

        const dropPrompt = document.getElementById('drop-prompt');
        const previewWrapper = document.getElementById('preview-wrapper');
        const imagePreview = document.getElementById('image-preview');

        if (imagePreview) imagePreview.src = '';
        if (previewWrapper) previewWrapper.classList.add('hidden');
        if (dropPrompt) dropPrompt.classList.remove('hidden');
    }

    // Drag and Drop
    const dropArea = document.getElementById('drop-area');
    if (dropArea) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropArea.classList.add('border-slate-500', 'bg-slate-900');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropArea.classList.remove('border-slate-500', 'bg-slate-900');
            }, false);
        });

        dropArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files[0]) {
                processProofFile(dt.files[0]);
            }
        }, false);
    }

    function submitPaymentProof(e) {
        e.preventDefault();

        const feedback = document.getElementById('upload-feedback');
        const btnSubmit = document.getElementById('btn-submit-proof');
        const btnText = document.getElementById('btn-proof-text');
        const btnSpinner = document.getElementById('btn-proof-spinner');
        const notesInput = document.getElementById('proof-notes');

        if (!compressedProofBase64) {
            if (feedback) {
                feedback.className = 'text-xs p-3 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-300 text-center block';
                feedback.textContent = 'Silakan pilih foto bukti transfer terlebih dahulu.';
            }
            return;
        }

        btnSubmit.disabled = true;
        btnText.classList.add('hidden');
        btnSpinner.classList.remove('hidden');
        if (feedback) feedback.classList.add('hidden');

        fetch("{{ route('events.payment.upload-proof', ['slug' => $event->slug, 'transaction' => $transaction->id]) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                payment_proof_base64: compressedProofBase64,
                notes: notesInput ? notesInput.value : ''
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
        .then(result => {
            btnSubmit.disabled = false;
            btnText.classList.remove('hidden');
            btnSpinner.classList.add('hidden');

            if (result.ok && result.data.success) {
                if (feedback) {
                    feedback.className = 'text-xs p-3 rounded-md bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-center block';
                    feedback.textContent = result.data.message || 'Bukti transfer berhasil diunggah.';
                }

                const statusBadge = document.getElementById('proof-status-badge');
                if (statusBadge) {
                    statusBadge.innerHTML = '<span class="px-2.5 py-1 rounded bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/20">Bukti Terkirim</span>';
                }

                const currentInfo = document.getElementById('proof-current-info');
                const currentImg = document.getElementById('current-proof-img');
                const currentTime = document.getElementById('current-proof-time');
                if (currentInfo && currentImg) {
                    currentImg.src = result.data.proof_url;
                    if (currentTime && result.data.uploaded_at) {
                        currentTime.textContent = 'Diunggah: ' + result.data.uploaded_at;
                    }
                    currentInfo.classList.remove('hidden');
                }
            } else {
                if (feedback) {
                    feedback.className = 'text-xs p-3 rounded-md bg-rose-500/10 border border-rose-500/30 text-rose-300 text-center block';
                    feedback.textContent = (result.data && result.data.message) ? result.data.message : 'Gagal mengunggah bukti transfer. Silakan coba kembali.';
                }
            }
        })
        .catch(err => {
            btnSubmit.disabled = false;
            btnText.classList.remove('hidden');
            btnSpinner.classList.add('hidden');

            if (feedback) {
                feedback.className = 'text-xs p-3 rounded-md bg-rose-500/10 border border-rose-500/30 text-rose-300 text-center block';
                feedback.textContent = 'Terjadi kesalahan jaringan saat mengunggah. Silakan periksa koneksi Anda.';
            }
        });
    }
</script>
@endsection
