@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Buat Email Campaign - ' . $event->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
<div class="max-w-7xl mx-auto">
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('eo.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('eo.events.index') }}" class="ml-1 text-sm font-medium text-slate-400 hover:text-white md:ml-2">Master Events</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('eo.events.campaigns.index', $event) }}" class="ml-1 text-sm font-medium text-slate-400 hover:text-white md:ml-2">Email Campaigns</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-white md:ml-2">Create</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <div class="text-neon font-mono text-xs tracking-widest uppercase">Event</div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    CREATE <span class="text-yellow-400">EMAIL CAMPAIGN</span>
                </h1>
                <div class="text-slate-400 text-sm mt-1">{{ $event->name }}</div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('eo.events.campaigns.index', $event) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-black uppercase tracking-widest border border-slate-700 transition-colors">
                    Batal
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-5 rounded-2xl bg-red-900/30 border border-red-500/30 text-red-200">
            <div class="text-xs font-black uppercase tracking-widest mb-2">Validation Error</div>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
            <form id="campaignForm" action="{{ route('eo.events.campaigns.store', $event) }}" method="POST" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 md:p-8 space-y-8">
                @csrf

                <!-- Basic Info -->
                <div class="border-b border-slate-700 pb-8">
                    <h3 class="text-xl font-bold text-white mb-6">Pengaturan Dasar</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nama Campaign (Internal)</label>
                            <input type="text" name="name" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-yellow-400 focus:outline-none" required placeholder="Mis: Reminder H-7">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tipe Pengiriman</label>
                                <select name="type" id="typeSelect" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white focus:border-yellow-400 focus:outline-none" required>
                                    <option value="instant">Kirim Sekarang (Instant)</option>
                                    <option value="absolute">Jadwalkan Waktu Tertentu</option>
                                </select>
                            </div>
                            <div id="scheduleContainer" class="hidden">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Jadwal Kirim</label>
                                <input type="datetime-local" name="send_at" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white focus:border-yellow-400 focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-b border-slate-700 pb-8">
                    <h3 class="text-xl font-bold text-white mb-6">Target Peserta</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status Pembayaran</label>
                            <div class="space-y-2">
                                <label class="flex items-start gap-3 text-sm text-slate-200">
                                    <input type="checkbox" name="filter_payment[]" value="paid" class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-yellow-400 focus:ring-yellow-400" checked>
                                    <span>Paid / Settlement</span>
                                </label>
                                <label class="flex items-start gap-3 text-sm text-slate-200">
                                    <input type="checkbox" name="filter_payment[]" value="cod" class="mt-1 w-4 h-4 rounded bg-slate-900 border-slate-700 text-yellow-400 focus:ring-yellow-400">
                                    <span>COD / Offline</span>
                                </label>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Catatan: Jika kosong, defaultnya akan mengirim ke semua peserta berstatus lunas (Paid/Settlement/Capture/COD).</p>
                        </div>
                    </div>
                </div>

                <div class="pb-2">
                    <h3 class="text-xl font-bold text-white mb-6">Konten Email</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Subjek Email</label>
                            <input type="text" name="subject" id="subjectInput" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-yellow-400 focus:outline-none" required placeholder="Mis: Reminder Pengambilan Racepack - {{ $event->name }}">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Preset Layout</label>
                            <select name="preset_template" id="presetSelect" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white focus:border-yellow-400 focus:outline-none" required>
                                <option value="general">Umum (Header, Teks, Tombol)</option>
                                <option value="reminder">Reminder / Info (Tambah Kotak Info Peserta)</option>
                                <option value="info">Info Penting (Sama seperti Reminder)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Judul Utama (Headline)</label>
                            <input type="text" name="headline" id="headlineInput" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-yellow-400 focus:outline-none" placeholder="Mis: Halo @{{name}}, bersiaplah!">
                            <p class="text-[11px] text-slate-500 mt-2">Gunakan <span class="px-1.5 py-0.5 rounded bg-slate-900 border border-slate-700 text-slate-200 font-mono">@{{name}}</span> dan <span class="px-1.5 py-0.5 rounded bg-slate-900 border border-slate-700 text-slate-200 font-mono">@{{bib}}</span>.</p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Isi Pesan</label>
                            <textarea name="body_text" id="bodyInput" rows="6" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-yellow-400 focus:outline-none" placeholder="Tulis pesan lengkap di sini..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Teks Tombol (CTA)</label>
                                <input type="text" name="cta_text" id="ctaTextInput" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-yellow-400 focus:outline-none" placeholder="Mis: Unduh Panduan">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">URL Tombol</label>
                                <input type="url" name="cta_url" id="ctaUrlInput" class="w-full px-4 py-3 rounded-xl bg-slate-900/60 border border-slate-700 text-white placeholder-slate-500 focus:border-yellow-400 focus:outline-none" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
                    <button type="button" id="btnPreview" class="px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-black uppercase tracking-widest border border-slate-700 transition-colors">
                        Update Preview
                    </button>
                    <button type="submit" class="px-4 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-dark text-sm font-black uppercase tracking-widest transition-colors">
                        Simpan & Proses Campaign
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:sticky lg:top-8 self-start">
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <div class="text-white font-black uppercase tracking-widest text-sm">Preview Email</div>
                    <div class="text-xs text-slate-400 font-mono">Dummy</div>
                </div>
                <div class="relative h-[600px] bg-white">
                    <div id="previewLoading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center hidden">
                        <div class="text-sm font-black text-slate-700">Memuat preview...</div>
                    </div>
                    <iframe id="previewFrame" class="w-full h-full border-none bg-white" sandbox="allow-same-origin"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('typeSelect');
        const scheduleContainer = document.getElementById('scheduleContainer');
        const btnPreview = document.getElementById('btnPreview');
        const previewFrame = document.getElementById('previewFrame');
        const previewLoading = document.getElementById('previewLoading');
        const presetSelect = document.getElementById('presetSelect');
        const subjectInput = document.getElementById('subjectInput');
        const headlineInput = document.getElementById('headlineInput');
        const bodyInput = document.getElementById('bodyInput');
        const ctaTextInput = document.getElementById('ctaTextInput');
        const ctaUrlInput = document.getElementById('ctaUrlInput');

        typeSelect.addEventListener('change', function() {
            if (this.value === 'absolute') {
                scheduleContainer.classList.remove('hidden');
                scheduleContainer.querySelector('input').required = true;
            } else {
                scheduleContainer.classList.add('hidden');
                scheduleContainer.querySelector('input').required = false;
            }
        });

        let previewTimer = null;
        function schedulePreview() {
            if (previewTimer) clearTimeout(previewTimer);
            previewTimer = setTimeout(updatePreview, 350);
        }

        async function updatePreview() {
            previewLoading.classList.remove('hidden');
            const data = {
                preset_template: presetSelect ? presetSelect.value : '',
                subject: subjectInput ? subjectInput.value : '',
                headline: headlineInput ? headlineInput.value : '',
                body_text: bodyInput ? bodyInput.value : '',
                cta_text: ctaTextInput ? ctaTextInput.value : '',
                cta_url: ctaUrlInput ? ctaUrlInput.value : '',
                _token: '{{ csrf_token() }}'
            };

            try {
                const response = await fetch('{{ route('eo.events.campaigns.preview', $event) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });
                let result = null;
                try {
                    result = await response.json();
                } catch (e) {
                    result = null;
                }

                if (!response.ok || !result || typeof result.html !== 'string') {
                    const msg = (result && (result.message || result.error)) ? (result.message || result.error) : 'Preview gagal dimuat.';
                    throw new Error(msg);
                }
                
                if ('srcdoc' in previewFrame) {
                    previewFrame.srcdoc = result.html;
                } else {
                    const doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
                    doc.open();
                    doc.write(result.html);
                    doc.close();
                }
            } catch (e) {
                console.error(e);
                const errorHtml = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Preview Error</title></head><body style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;padding:20px;background:#fff;"><div style="max-width:560px;margin:0 auto;border:1px solid #fee2e2;background:#fef2f2;color:#991b1b;border-radius:12px;padding:16px;"><div style="font-weight:800;margin-bottom:6px;">Preview Error</div><div style="font-size:14px;line-height:1.4;">'+String(e && e.message ? e.message : 'Terjadi kesalahan.')+'</div></div></body></html>';
                if ('srcdoc' in previewFrame) {
                    previewFrame.srcdoc = errorHtml;
                } else {
                    const doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
                    doc.open();
                    doc.write(errorHtml);
                    doc.close();
                }
            } finally {
                previewLoading.classList.add('hidden');
            }
        }

        btnPreview.addEventListener('click', function () {
            if (previewTimer) clearTimeout(previewTimer);
            updatePreview();
        });

        if (presetSelect) presetSelect.addEventListener('change', schedulePreview);
        if (typeSelect) typeSelect.addEventListener('change', schedulePreview);
        if (subjectInput) subjectInput.addEventListener('input', schedulePreview);
        if (headlineInput) headlineInput.addEventListener('input', schedulePreview);
        if (bodyInput) bodyInput.addEventListener('input', schedulePreview);
        if (ctaTextInput) ctaTextInput.addEventListener('input', schedulePreview);
        if (ctaUrlInput) ctaUrlInput.addEventListener('input', schedulePreview);
        
        // Initial preview load
        updatePreview();
    });
</script>
@endpush
