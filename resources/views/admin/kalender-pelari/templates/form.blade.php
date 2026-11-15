@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', ($template->id ? 'Edit Template: ' . $template->name : 'Tambah Template Idola Lari') . ' - Admin Ruang Lari')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<style>
/* CKEditor 5 Dark Athletic Theme */
.ck.ck-editor {
    width: 100% !important;
}
.ck.ck-editor__top .ck-sticky-panel .ck-toolbar {
    background-color: #0f172a !important;
    border: 1px solid #1e293b !important;
    border-bottom: 0 !important;
    border-top-left-radius: 6px !important;
    border-top-right-radius: 6px !important;
}
.ck.ck-toolbar .ck-button {
    color: #cbd5e1 !important;
    cursor: pointer;
}
.ck.ck-toolbar .ck-button:hover {
    background-color: #1e293b !important;
    color: #ffffff !important;
}
.ck.ck-toolbar .ck-button.ck-on {
    background-color: #dc2626 !important;
    color: #ffffff !important;
}
.ck.ck-editor__main > .ck-editor__editable {
    background-color: #020617 !important;
    color: #f8fafc !important;
    border: 1px solid #1e293b !important;
    min-height: 180px;
    border-bottom-left-radius: 6px !important;
    border-bottom-right-radius: 6px !important;
    padding: 14px !important;
    font-size: 13px !important;
    line-height: 1.6 !important;
}
.ck.ck-editor__main > .ck-editor__editable.ck-focused {
    border-color: #dc2626 !important;
    box-shadow: 0 0 0 1px #dc2626 !important;
}
.ck.ck-dropdown__panel {
    background-color: #0f172a !important;
    border: 1px solid #1e293b !important;
}
.ck.ck-list__item .ck-button {
    color: #e2e8f0 !important;
}
.ck.ck-list__item .ck-button:hover {
    background-color: #1e293b !important;
}

/* Dropzone Slot Styles */
.slot-dropzone {
    transition: all 0.2s ease;
}
.slot-dropzone.drag-active {
    border-color: #ef4444 !important;
    background-color: rgba(239, 68, 68, 0.08) !important;
    transform: scale(1.01);
}
.master-dropzone.drag-active {
    border-color: #ef4444 !important;
    background-color: rgba(239, 68, 68, 0.08) !important;
    transform: scale(1.005);
}
</style>
@endpush

@php
    $isEdit = !empty($template->id);
    $formTitle = $isEdit ? "Edit Template: {$template->name}" : 'Tambah Template Idola Lari';
    $action = $isEdit ? route('admin.kalender-pelari.templates.update', $template) : route('admin.kalender-pelari.templates.store');
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $photoUrls = $template->photo_urls ?? [];
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-10 pb-12">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.kalender-pelari.templates.index') }}" class="hover:text-white transition">Template Idola</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-200">{{ $isEdit ? 'Edit Template' : 'Buat Baru' }}</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                {{ $formTitle }}
            </h1>
            <p class="text-slate-300 text-sm mt-1">
                Atur profil atlet dengan CKEditor dan unggah paket 13 foto menggunakan drag-and-drop Dropzone interaktif.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.kalender-pelari.templates.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold rounded-md border border-slate-700 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <!-- Error Summary -->
    @if(isset($errors) && $errors->any())
        <div class="mb-6 p-4 rounded-md bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm">
            <div class="font-semibold flex items-center gap-2 mb-1">
                <i class="fas fa-exclamation-circle"></i>
                <span>Terdapat kesalahan pada formulir:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-300">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="templateForm" method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <!-- Card 1: Informasi Dasar Atlet & CKEditor Bio -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
            <h2 class="text-base font-bold text-white tracking-tight mb-4 flex items-center gap-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                <i class="fas fa-id-card text-red-500"></i>
                <span>Informasi Dasar Atlet</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Nama Atlet -->
                <div class="md:col-span-6">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Nama Idola / Atlet <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $template->name) }}" required placeholder="Misal: Agus Prayogo - Marathon Legend" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none">
                </div>

                <!-- Kategori -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Kategori Atlet
                    </label>
                    <select name="category" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-sm text-white focus:border-red-500 outline-none">
                        <option value="Nasional" {{ old('category', $template->category) === 'Nasional' ? 'selected' : '' }}>Nasional</option>
                        <option value="Dunia" {{ old('category', $template->category) === 'Dunia' ? 'selected' : '' }}>Dunia / Internasional</option>
                        <option value="Trail" {{ old('category', $template->category) === 'Trail' ? 'selected' : '' }}>Trail / Ultra Running</option>
                        <option value="Inspirasi" {{ old('category', $template->category) === 'Inspirasi' ? 'selected' : '' }}>Komunitas / Inspirasi</option>
                    </select>
                </div>

                <!-- Urutan & Aktif -->
                <div class="md:col-span-3 grid grid-cols-2 gap-3 items-center">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Urutan
                        </label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $template->sort_order ?? 0) }}" min="0" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-sm text-white focus:border-red-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Status
                        </label>
                        <label class="flex items-center gap-2 mt-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-800 bg-slate-950 text-red-600 focus:ring-red-500">
                            <span class="text-xs text-white font-medium">Tampil di Studio</span>
                        </label>
                    </div>
                </div>

                <!-- Bio dengan CKEditor -->
                <div class="md:col-span-12">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5 flex items-center justify-between">
                        <span>Biografi Ringkas &amp; Catatan Prestasi (CKEditor)</span>
                        <span class="text-[11px] text-slate-400 font-normal">Mendukung tebal, miring, list, link, dan kutipan</span>
                    </label>

                    <!-- CKEditor Mount Container -->
                    <div id="bio_editor" class="text-white"></div>

                    <!-- Hidden input to submit with form -->
                    <textarea name="bio" id="bio_input" class="hidden">{!! old('bio', $template->bio) !!}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Profil ini akan ditampilkan pada informasi template dan kartu inspirasi di Studio Kalender Pelari.
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 2: Pengaturan Desain & Palet Bawaan -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
            <h2 class="text-base font-bold text-white tracking-tight mb-4 flex items-center gap-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                <i class="fas fa-palette text-red-500"></i>
                <span>Pengaturan Desain &amp; Gaya Rekomendasi</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Warna Aksen -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Warna Aksen Bawaan
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="accent_color" id="accentColorPicker" value="{{ old('accent_color', $template->accent_color ?? '#E63946') }}" class="w-10 h-10 p-1 bg-slate-950 border border-slate-800 rounded-md cursor-pointer">
                        <input type="text" id="accentColorText" value="{{ old('accent_color', $template->accent_color ?? '#E63946') }}" oninput="document.getElementById('accentColorPicker').value = this.value" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-sm text-white font-mono focus:border-red-500 outline-none">
                    </div>
                </div>

                <!-- Warna Kertas -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Warna Kertas Rekomendasi
                    </label>
                    <select name="paper_color" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-sm text-white focus:border-red-500 outline-none">
                        <option value="white" {{ old('paper_color', $template->paper_color ?? 'white') === 'white' ? 'selected' : '' }}>Putih Bersih (White)</option>
                        <option value="cream" {{ old('paper_color', $template->paper_color) === 'cream' ? 'selected' : '' }}>Krem Alami (Cream)</option>
                        <option value="black" {{ old('paper_color', $template->paper_color) === 'black' ? 'selected' : '' }}>Hitam Pekat (Dark/Black)</option>
                    </select>
                </div>

                <!-- Font Pilihan -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Font Heading Rekomendasi
                    </label>
                    <select name="default_font" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-sm text-white focus:border-red-500 outline-none">
                        <option value="Inter Tight" {{ old('default_font', $template->default_font ?? 'Inter Tight') === 'Inter Tight' ? 'selected' : '' }}>Inter Tight (Resmi Ruang Lari)</option>
                        <option value="Sora" {{ old('default_font', $template->default_font) === 'Sora' ? 'selected' : '' }}>Sora</option>
                        <option value="Inter" {{ old('default_font', $template->default_font) === 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Montserrat" {{ old('default_font', $template->default_font) === 'Montserrat' ? 'selected' : '' }}>Montserrat</option>
                        <option value="Oswald" {{ old('default_font', $template->default_font) === 'Oswald' ? 'selected' : '' }}>Oswald</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 3: Multi-Photo Dropzone (Master Dropzone + 13 Slot Interaktif) -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-base font-bold text-white tracking-tight flex items-center gap-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                        <i class="fas fa-camera text-red-500"></i>
                        <span>Paket 13 Foto Atlet (Dropzone Upload)</span>
                    </h2>
                    <p class="text-slate-300 text-xs mt-0.5">
                        Tarik &amp; lepas foto langsung ke slot yang diinginkan, atau gunakan Master Dropzone untuk mengisi semua bulan sekaligus.
                    </p>
                </div>
                <div class="text-xs font-mono text-slate-400 bg-slate-950 px-2.5 py-1 rounded border border-slate-800">
                    <span id="filledCounter">{{ $template->filled_photos_count ?? 0 }}</span>/13 Foto Terpasang
                </div>
            </div>

            <!-- 1. Master Bulk Multi-Photo Dropzone -->
            <div id="masterDropzone" class="master-dropzone border-2 border-dashed border-slate-700 hover:border-red-500 rounded-lg p-6 bg-slate-950/80 hover:bg-slate-950 transition text-center cursor-pointer mb-6 group select-none">
                <input type="file" id="masterFileInput" multiple accept="image/*" class="hidden">
                <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                    <div class="w-12 h-12 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 group-hover:text-red-500 group-hover:border-red-500/50 transition">
                        <i class="fas fa-cloud-arrow-up text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white group-hover:text-red-400 transition">
                            Tarik &amp; lepaskan banyak foto atlet di sini, atau <span class="underline text-red-500">klik untuk pilih dari komputer</span>
                        </p>
                        <p class="text-xs text-slate-400 mt-1">
                            Mendukung hingga 13 foto sekaligus. File akan otomatis didistribusikan ke Sampul dan Bulan 1 s/d 12 secara berurutan.
                        </p>
                    </div>
                </div>
                <!-- Feedback notification for bulk drop -->
                <div id="masterFeedback" class="hidden mt-3 text-xs font-semibold text-emerald-400 flex items-center justify-center gap-1.5">
                    <i class="fas fa-check-circle"></i>
                    <span id="masterFeedbackText"></span>
                </div>
            </div>

            <!-- 2. Slot 0: Foto Sampul Depan (Cover) -->
            <div class="mb-6">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span>Slot Foto Sampul Depan (Cover Utama)</span>
                </div>

                <div id="dropzoneSlot0" class="slot-dropzone border-2 border-dashed border-slate-800 hover:border-red-500/80 rounded-lg p-4 bg-slate-950 transition cursor-pointer relative group flex flex-col sm:flex-row items-center gap-4" data-slot="0">
                    <input type="file" name="cover_file" id="slotInput0" accept="image/*" class="hidden">

                    <!-- Preview Thumbnail -->
                    <div class="w-36 h-24 rounded-md overflow-hidden bg-slate-900 border border-slate-800 flex-shrink-0 relative">
                        <img id="slotImg0" src="{{ $photoUrls[0] ?? $template->cover_url }}" alt="Cover" class="w-full h-full object-cover {{ empty($photoUrls[0]) && empty($template->cover_url) ? 'hidden' : '' }}">
                        <div id="slotPlaceholder0" class="w-full h-full flex flex-col items-center justify-center text-slate-600 gap-1 {{ !empty($photoUrls[0]) || !empty($template->cover_url) ? 'hidden' : '' }}">
                            <i class="fas fa-cloud-upload-alt text-2xl"></i>
                            <span class="text-[10px]">Tarik foto ke sini</span>
                        </div>
                        <span class="absolute bottom-1 left-1 px-1.5 py-0.5 bg-slate-950/90 rounded text-[9px] font-bold text-white uppercase">Sampul</span>
                    </div>

                    <!-- Meta info & Actions -->
                    <div class="flex-1 w-full text-center sm:text-left">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white uppercase">Foto Sampul Utama Kalender</span>
                            <span id="slotStatus0" class="w-2.5 h-2.5 rounded-full {{ !empty($photoUrls[0]) || !empty($template->cover_url) ? 'bg-emerald-400' : 'bg-slate-700' }}" title="Status foto"></span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5 mb-2">
                            Tarik file gambar ke kotak ini atau klik untuk memilih file cover resolusi tinggi.
                        </p>
                        <div class="flex items-center gap-2 justify-center sm:justify-start">
                            <button type="button" onclick="triggerSlotUpload(0)" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-md border border-slate-700 transition flex items-center gap-1.5">
                                <i class="fas fa-folder-open text-[10px]"></i>
                                <span>Pilih File</span>
                            </button>
                            <span id="slotFileName0" class="text-[11px] text-slate-400 font-mono truncate max-w-[200px]">
                                {{ !empty($photoUrls[0]) ? 'Foto sampul aktif' : 'Belum ada file baru dipilih' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Grid 12 Slot Foto Bulanan (Januari s/d Desember) -->
            <div class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                <span>Slot 12 Foto Bulanan (Januari s/d Desember)</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @for($m = 1; $m <= 12; $m++)
                    @php
                        $monthName = $months[$m - 1];
                        $currPhoto = $photoUrls[$m] ?? null;
                    @endphp
                    <div id="dropzoneSlot{{ $m }}" class="slot-dropzone border-2 border-dashed border-slate-800 hover:border-red-500/80 rounded-lg p-3 bg-slate-950 transition cursor-pointer relative group flex flex-col justify-between" data-slot="{{ $m }}">
                        <input type="file" name="month_photos[{{ $m }}]" id="slotInput{{ $m }}" accept="image/*" class="hidden">

                        <!-- Header Slot -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-slate-800 border border-slate-700/80 text-[10px] flex items-center justify-center font-mono text-red-400 font-bold">{{ $m }}</span>
                                <span>{{ $monthName }}</span>
                            </span>
                            <span id="slotStatus{{ $m }}" class="w-2 h-2 rounded-full {{ $currPhoto ? 'bg-emerald-400' : 'bg-slate-700' }}" title="{{ $currPhoto ? 'Foto tersedia' : 'Belum ada foto' }}"></span>
                        </div>

                        <!-- Thumbnail Preview Area -->
                        <div class="h-28 rounded bg-slate-900 border border-slate-800/80 overflow-hidden mb-2 relative flex items-center justify-center">
                            <img id="slotImg{{ $m }}" src="{{ $currPhoto }}" alt="{{ $monthName }}" class="w-full h-full object-cover {{ !$currPhoto ? 'hidden' : '' }}">
                            <div id="slotPlaceholder{{ $m }}" class="w-full h-full flex flex-col items-center justify-center text-slate-600 gap-1 {{ $currPhoto ? 'hidden' : '' }}">
                                <i class="fas fa-cloud-arrow-up text-lg group-hover:text-red-400 transition"></i>
                                <span class="text-[10px] group-hover:text-slate-300 transition">Tarik foto ke sini</span>
                            </div>
                        </div>

                        <!-- Info & Action -->
                        <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
                            <span id="slotFileName{{ $m }}" class="text-slate-400 truncate max-w-[120px]" title="{{ $currPhoto ? 'Foto bulan ' . $monthName : '' }}">
                                {{ $currPhoto ? 'Foto terpasang' : 'Kosong' }}
                            </span>
                            <button type="button" onclick="triggerSlotUpload({{ $m }})" class="px-2 py-0.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[10px] font-semibold rounded border border-slate-700 transition">
                                Ganti
                            </button>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Form Actions Sticky / Fixed Footer -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 flex items-center justify-between shadow-sm">
            <a href="{{ route('admin.kalender-pelari.templates.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold rounded-md transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-md shadow-sm transition flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span>{{ $isEdit ? 'Simpan Perubahan Template' : 'Buat Template Idola' }}</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<!-- CKEditor 5 Classic CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<!-- Dropzone JS CDN -->
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    /* ----------------------------------------------------
     * 1. CKEDITOR 5 INITIALIZATION FOR BIO
     * ---------------------------------------------------- */
    const bioTextarea = document.getElementById('bio_input');
    const bioEditorEl = document.getElementById('bio_editor');

    if (bioEditorEl) {
        ClassicEditor
            .create(bioEditorEl, {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                        'blockQuote', 'undo', 'redo'
                    ]
                },
                placeholder: 'Tuliskan biografi atlet, rekor prestasi, catatan maraton, atau kutipan motivasi...'
            })
            .then(editor => {
                window.athleteBioEditor = editor;
                if (bioTextarea && bioTextarea.value) {
                    editor.setData(bioTextarea.value);
                }

                editor.model.document.on('change:data', () => {
                    if (bioTextarea) {
                        bioTextarea.value = editor.getData();
                    }
                });

                const form = document.getElementById('templateForm');
                if (form) {
                    form.addEventListener('submit', () => {
                        if (bioTextarea) {
                            bioTextarea.value = editor.getData();
                        }
                    });
                }
            })
            .catch(error => {
                console.error('CKEditor Init Error:', error);
            });
    }

    /* ----------------------------------------------------
     * 2. ACCENT COLOR SYNC
     * ---------------------------------------------------- */
    const accentPicker = document.getElementById('accentColorPicker');
    const accentText = document.getElementById('accentColorText');
    if (accentPicker && accentText) {
        accentPicker.addEventListener('input', (e) => { accentText.value = e.target.value; });
        accentText.addEventListener('input', (e) => { accentPicker.value = e.target.value; });
    }

    /* ----------------------------------------------------
     * 3. DROPZONE SLOTS LOGIC (SLOT 0 = COVER, 1..12 = JAN..DEC)
     * ---------------------------------------------------- */
    // Helper to format file size
    function formatBytes(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024, sizes = ['B', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // Assign file to a slot (preview & file input)
    window.assignFileToSlot = function(slotIndex, file) {
        const input = document.getElementById(`slotInput${slotIndex}`);
        const img = document.getElementById(`slotImg${slotIndex}`);
        const placeholder = document.getElementById(`slotPlaceholder${slotIndex}`);
        const status = document.getElementById(`slotStatus${slotIndex}`);
        const fileName = document.getElementById(`slotFileName${slotIndex}`);

        if (!input || !file) return;

        // Set input.files via DataTransfer
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;

        // Render preview
        const reader = new FileReader();
        reader.onload = function(e) {
            if (img) {
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
            if (status) {
                status.className = 'w-2 h-2 rounded-full bg-emerald-400';
            }
            if (fileName) {
                fileName.textContent = `${file.name.substring(0, 16)} (${formatBytes(file.size)})`;
            }
            updateFilledCount();
        };
        reader.readAsDataURL(file);
    };

    window.triggerSlotUpload = function(slotIndex) {
        const input = document.getElementById(`slotInput${slotIndex}`);
        if (input) input.click();
    };

    function updateFilledCount() {
        let count = 0;
        for (let i = 0; i <= 12; i++) {
            const img = document.getElementById(`slotImg${i}`);
            if (img && !img.classList.contains('hidden') && img.src && !img.src.endsWith('/')) {
                count++;
            }
        }
        const counterEl = document.getElementById('filledCounter');
        if (counterEl) counterEl.textContent = count;
    }

    // Attach drag & drop listeners to each slot (0 to 12)
    for (let slot = 0; slot <= 12; slot++) {
        const slotEl = document.getElementById(`dropzoneSlot${slot}`);
        const slotInput = document.getElementById(`slotInput${slot}`);

        if (slotEl && slotInput) {
            // Click slot to trigger upload
            slotEl.addEventListener('click', function(e) {
                if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'INPUT') {
                    slotInput.click();
                }
            });

            // Input change listener
            slotInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    window.assignFileToSlot(slot, this.files[0]);
                }
            });

            // Drag and drop events
            ['dragenter', 'dragover'].forEach(evtName => {
                slotEl.addEventListener(evtName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    slotEl.classList.add('drag-active');
                });
            });

            ['dragleave', 'drop'].forEach(evtName => {
                slotEl.addEventListener(evtName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    slotEl.classList.remove('drag-active');
                });
            });

            slotEl.addEventListener('drop', function(e) {
                const files = e.dataTransfer.files;
                if (files && files.length > 0 && files[0].type.startsWith('image/')) {
                    window.assignFileToSlot(slot, files[0]);
                }
            });
        }
    }

    /* ----------------------------------------------------
     * 4. MASTER BULK DROPZONE LOGIC (AUTO-DISTRIBUTE MULTI FILES)
     * ---------------------------------------------------- */
    const masterDropzone = document.getElementById('masterDropzone');
    const masterFileInput = document.getElementById('masterFileInput');
    const masterFeedback = document.getElementById('masterFeedback');
    const masterFeedbackText = document.getElementById('masterFeedbackText');

    if (masterDropzone && masterFileInput) {
        masterDropzone.addEventListener('click', () => {
            masterFileInput.click();
        });

        masterFileInput.addEventListener('change', function() {
            handleBulkFiles(this.files);
        });

        ['dragenter', 'dragover'].forEach(evtName => {
            masterDropzone.addEventListener(evtName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                masterDropzone.classList.add('drag-active');
            });
        });

        ['dragleave', 'drop'].forEach(evtName => {
            masterDropzone.addEventListener(evtName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                masterDropzone.classList.remove('drag-active');
            });
        });

        masterDropzone.addEventListener('drop', function(e) {
            if (e.dataTransfer && e.dataTransfer.files) {
                handleBulkFiles(e.dataTransfer.files);
            }
        });

        function handleBulkFiles(fileList) {
            const files = Array.from(fileList).filter(f => f.type.startsWith('image/'));
            if (!files.length) return;

            // Distribute files sequentially:
            // 0 -> Sampul, 1 -> Jan, 2 -> Feb, ... up to 12
            files.slice(0, 13).forEach((file, index) => {
                window.assignFileToSlot(index, file);
            });

            if (masterFeedback && masterFeedbackText) {
                masterFeedbackText.textContent = `${files.length} foto berhasil didistribusikan ke slot kalender!`;
                masterFeedback.classList.remove('hidden');
                setTimeout(() => {
                    masterFeedback.classList.add('hidden');
                }, 6000);
            }
        }
    }
});
</script>
@endpush
