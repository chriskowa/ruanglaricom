@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Buat Email Campaign - ' . $event->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Buat Campaign Baru</h1>
            <p class="text-sm text-slate-400 mt-1">Atur template, target, dan jadwal pengiriman email.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('eo.events.campaigns.index', $event) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">
                Batal
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Form Kolom Kiri -->
        <div>
            <form id="campaignForm" action="{{ route('eo.events.campaigns.store', $event) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6">
                @csrf

                <!-- Basic Info -->
                <div>
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Pengaturan Dasar</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Nama Campaign (Internal)</label>
                            <input type="text" name="name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required placeholder="Mis: Reminder H-7">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Tipe Pengiriman</label>
                                <select name="type" id="typeSelect" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                    <option value="instant">Kirim Sekarang (Instant)</option>
                                    <option value="absolute">Jadwalkan Waktu Tertentu</option>
                                </select>
                            </div>
                            <div id="scheduleContainer" class="hidden">
                                <label class="block text-sm font-medium text-slate-700">Jadwal Kirim</label>
                                <input type="datetime-local" name="send_at" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Targeting -->
                <div class="pt-6 border-t border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Target Peserta</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Status Pembayaran</label>
                            <div class="space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="filter_payment[]" value="paid" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" checked>
                                    <span class="ml-2 text-sm text-slate-700">Paid / Settlement</span>
                                </label><br>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="filter_payment[]" value="cod" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-slate-700">COD / Offline</span>
                                </label>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Catatan: Jika kosong, defaultnya akan mengirim ke semua peserta berstatus lunas (Paid/Settlement/Capture/COD).</p>
                        </div>
                    </div>
                </div>

                <!-- Email Content -->
                <div class="pt-6 border-t border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Konten Email</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Subjek Email</label>
                            <input type="text" name="subject" id="subjectInput" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required placeholder="Mis: Reminder Pengambilan Racepack - {{ $event->name }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Preset Layout</label>
                            <select name="preset_template" id="presetSelect" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                <option value="general">Umum (Header, Teks, Tombol)</option>
                                <option value="reminder">Reminder / Info (Tambah Kotak Info Peserta)</option>
                                <option value="info">Info Penting (Sama seperti Reminder)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Judul Utama (Headline)</label>
                            <input type="text" name="headline" id="headlineInput" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Mis: Halo {{name}}, bersiaplah!">
                            <p class="text-[10px] text-slate-500 mt-1">Gunakan <code>{{name}}</code> untuk nama peserta, <code>{{bib}}</code> untuk No. BIB.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Isi Pesan</label>
                            <textarea name="body_text" id="bodyInput" rows="5" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Tulis pesan lengkap di sini..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Teks Tombol (CTA)</label>
                                <input type="text" name="cta_text" id="ctaTextInput" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Mis: Unduh Panduan">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">URL Tombol</label>
                                <input type="url" name="cta_url" id="ctaUrlInput" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" id="btnPreview" class="px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Preview
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan & Proses Campaign
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Preview Kolom Kanan -->
        <div class="lg:sticky lg:top-8 self-start">
            <h3 class="text-lg font-bold text-white mb-4">Preview Email (Dummy)</h3>
            <div class="bg-slate-100 rounded-xl border border-slate-200 overflow-hidden shadow-inner h-[600px] flex flex-col relative">
                <div id="previewLoading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center hidden">
                    <div class="text-sm font-bold text-slate-600">Memuat preview...</div>
                </div>
                <iframe id="previewFrame" class="w-full h-full border-none bg-white" sandbox="allow-same-origin"></iframe>
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

        typeSelect.addEventListener('change', function() {
            if (this.value === 'absolute') {
                scheduleContainer.classList.remove('hidden');
                scheduleContainer.querySelector('input').required = true;
            } else {
                scheduleContainer.classList.add('hidden');
                scheduleContainer.querySelector('input').required = false;
            }
        });

        async function updatePreview() {
            previewLoading.classList.remove('hidden');
            const data = {
                preset_template: document.getElementById('presetSelect').value,
                subject: document.getElementById('subjectInput').value,
                headline: document.getElementById('headlineInput').value,
                body_text: document.getElementById('bodyInput').value,
                cta_text: document.getElementById('ctaTextInput').value,
                cta_url: document.getElementById('ctaUrlInput').value,
                _token: '{{ csrf_token() }}'
            };

            try {
                const response = await fetch('{{ route('eo.events.campaigns.preview', $event) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                
                // Write to iframe
                const doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
                doc.open();
                doc.write(result.html);
                doc.close();
            } catch (e) {
                console.error(e);
            } finally {
                previewLoading.classList.add('hidden');
            }
        }

        btnPreview.addEventListener('click', updatePreview);
        
        // Initial preview load
        updatePreview();
    });
</script>
@endpush
