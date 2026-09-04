@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Homepage Hero & Content')

@section('content')
<div class="max-w-5xl mx-auto p-6 md:p-8 bg-slate-900 rounded-xl overflow-hidden shadow-2xl border border-slate-700">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-800">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Homepage Hero & Athlete Management</h2>
            <p class="text-sm text-slate-400 mt-1">Kelola gambar background parallax, visual athlete (PNG transparan), dan teks hero RuangLari.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-md text-xs font-semibold tracking-wider uppercase transition flex items-center gap-1.5 border border-slate-700">
                <i class="fas fa-external-link-alt text-[10px]"></i> Preview Homepage
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-900/40 border border-emerald-500 text-emerald-100 px-4 py-3 rounded-md mb-6 flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-400 text-lg"></i>
            <div>
                <p class="font-medium text-sm">{{ session('success') }}</p>
                <p class="text-xs text-emerald-300/80">Cache telah diperbarui, perubahan langsung aktif di homepage.</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-900/40 border border-red-500 text-red-100 px-4 py-3 rounded-md mb-6">
            <p class="font-medium text-sm mb-1">Terdapat kesalahan pengisian:</p>
            <ul class="list-disc list-inside text-xs text-red-300">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.homepage.content.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        
        {{-- Section 1: Headline & Subheadline --}}
        <div class="bg-slate-800/40 border border-slate-700/80 rounded-lg p-6">
            <h3 class="text-base font-bold text-white mb-1 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-neon"></span> 01. Teks Headline & Subheadline
            </h3>
            <p class="text-xs text-slate-400 mb-4">Biarkan kosong jika ingin memakai teks standar resmi RuangLari.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2" for="headline">Headline Utama</label>
                    <input type="text" name="headline" id="headline" value="{{ old('headline', $content->headline) }}" placeholder="Ekosistem Lari Indonesia." class="w-full bg-slate-900 border border-slate-700 rounded-md p-3 text-white text-sm focus:outline-none focus:border-neon transition">
                    <span class="text-[11px] text-slate-500 mt-1 block">Contoh default: "Ekosistem Lari Indonesia."</span>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2" for="subheadline">Subheadline / Deskripsi</label>
                    <textarea name="subheadline" id="subheadline" rows="3" placeholder="Temukan berita lari, event race, komunitas, program latihan..." class="w-full bg-slate-900 border border-slate-700 rounded-md p-3 text-white text-sm focus:outline-none focus:border-neon transition">{{ old('subheadline', $content->subheadline) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 2: 3 Ambient Background Parallax Slides --}}
        <div class="bg-slate-800/40 border border-slate-700/80 rounded-lg p-6">
            <div class="mb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-neon"></span> 02. Ambient Background Parallax Slider (3 Slide Bergantian)
                </h3>
                <p class="text-xs text-slate-400 mt-1">Sistem akan memutar 3 slide ini secara halus di latar hero. Anda bisa meng-upload file foto baru atau memasukkan URL gambar.</p>
            </div>

            @php
                $fallbackSlideList = [
                    'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp',
                    'https://ruanglari.com/storage/blog/media/305fd042-17e8-4e89-86d7-8666d4e2229f.webp',
                    'https://ruanglari.com/storage/blog/media/21bb785d-d104-4129-99ce-d84ae98afd3a.webp',
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @for($i = 1; $i <= 3; $i++)
                    @php
                        $idx = $i - 1;
                        $curVal = $currentSlides[$idx] ?? '';
                        $defaultSlideUrl = (isset($defaultSlides) && isset($defaultSlides[$idx])) 
                            ? $defaultSlides[$idx] 
                            : ($fallbackSlideList[$idx] ?? '');
                        $previewUrl = !empty($curVal) 
                            ? (str_starts_with($curVal, 'http') ? $curVal : asset($curVal))
                            : $defaultSlideUrl;
                        $isCustom = !empty($curVal);
                    @endphp
                    <div class="bg-slate-900/90 border border-slate-700 rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold font-mono text-neon uppercase tracking-wider">SLIDE 0{{ $i }} {{ $i === 1 ? '(Utama)' : '' }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded {{ $isCustom ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $isCustom ? 'Custom' : 'Default' }}
                                </span>
                            </div>

                            {{-- Image Preview --}}
                            <div class="relative aspect-video rounded overflow-hidden bg-slate-950 border border-slate-800 mb-3 group">
                                <img src="{{ $previewUrl }}" alt="Slide {{ $i }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-2 text-center text-[10px] text-white">
                                    Preview Tampilan Slide {{ $i }}
                                </div>
                            </div>

                            {{-- File Upload --}}
                            <label class="block text-xs text-slate-300 font-semibold mb-1">Upload File Foto</label>
                            <input type="file" name="hero_slide_{{ $i }}" accept="image/*" class="block w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-neon file:text-dark hover:file:bg-neon/90 mb-3 cursor-pointer">

                            {{-- Or URL --}}
                            <label class="block text-xs text-slate-300 font-semibold mb-1">Atau Gunakan URL Gambar</label>
                            <input type="text" name="hero_slide_{{ $i }}_url" value="{{ old("hero_slide_{$i}_url", $isCustom ? $curVal : '') }}" placeholder="https://... atau storage/..." class="w-full bg-slate-800 border border-slate-700 rounded p-2 text-white text-xs focus:outline-none focus:border-neon transition">
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Section 3: Athlete Visual (Multi-Photo Dropzone) --}}
        <div class="bg-slate-800/40 border border-slate-700/80 rounded-lg p-6"
             x-data="athleteDropzoneManager({{ json_encode($existingAthleteImages ?? []) }})">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-4 border-b border-slate-700/60">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-neon"></span> 03. Visual Atlet Pelari (Multi-Foto dengan Dropzone)
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Unggah beberapa foto atlet (PNG transparan atau WebP). Urutan foto menentukan alur cerita hero:
                        <span class="text-neon font-semibold">Pose 1 = Utama (Idle)</span>, 
                        <span class="text-emerald-400 font-semibold">Pose 2 = Hover & Siap</span>, 
                        <span class="text-amber-400 font-semibold">Pose 3+ = Sprint & Akselerasi</span>.
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-xs font-mono px-2.5 py-1 rounded bg-slate-800 text-slate-300 border border-slate-700"
                          x-text="(existingPhotos.length + newFiles.length) + ' Foto Terpasang'">
                    </span>
                </div>
            </div>

            {{-- 1. Existing Athlete Photos Gallery --}}
            <div class="mb-6" x-show="existingPhotos.length > 0">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-300">
                        Foto Atlet Yang Sedang Aktif (Urutan Dapat Ditukar)
                    </span>
                    <span class="text-[11px] text-slate-400">
                        Gunakan tombol panah untuk memindahkan urutan pose
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="(photo, idx) in existingPhotos" :key="photo.id">
                        <div class="relative bg-slate-950 border border-slate-700 rounded-lg p-3 flex flex-col items-center justify-between group overflow-hidden transition hover:border-slate-500">
                            
                            {{-- Role Badge & Controls --}}
                            <div class="w-full flex items-center justify-between mb-2">
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded uppercase"
                                      :class="{
                                          'bg-neon text-dark font-black': idx === 0,
                                          'bg-emerald-900/80 text-emerald-300 border border-emerald-700': idx === 1,
                                          'bg-amber-900/80 text-amber-300 border border-amber-700': idx === 2,
                                          'bg-slate-800 text-slate-400 border border-slate-700': idx > 2
                                      }"
                                      x-text="getPoseBadge(idx)">
                                </span>

                                {{-- Reorder Controls --}}
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="moveExisting(idx, -1)" :disabled="idx === 0"
                                            class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-25 disabled:cursor-not-allowed text-slate-300 hover:text-white flex items-center justify-center text-xs transition"
                                            title="Geser ke urutan sebelumnya">
                                        <i class="fas fa-chevron-left text-[10px]"></i>
                                    </button>
                                    <button type="button" @click="moveExisting(idx, 1)" :disabled="idx === existingPhotos.length - 1"
                                            class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-25 disabled:cursor-not-allowed text-slate-300 hover:text-white flex items-center justify-center text-xs transition"
                                            title="Geser ke urutan berikutnya">
                                        <i class="fas fa-chevron-right text-[10px]"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Image Preview with checkered transparency indicator --}}
                            <div class="w-full h-44 rounded flex items-center justify-center relative p-2 my-1"
                                 style="background-color: #0b0f17; background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 10px 10px;">
                                <img :src="photo.url" :alt="'Pose ' + (idx + 1)" class="h-full max-w-full object-contain filter drop-shadow-[0_10px_15px_rgba(0,0,0,0.8)]">
                            </div>

                            {{-- Footer Info & Delete Button --}}
                            <div class="w-full flex items-center justify-between pt-2 border-t border-slate-800/80 mt-2 text-xs">
                                <span class="text-[11px] text-slate-400 truncate max-w-[170px]" :title="photo.filename" x-text="photo.filename"></span>
                                <button type="button" @click="removeExisting(idx)"
                                        class="text-rose-400 hover:text-rose-300 text-xs flex items-center gap-1 hover:underline transition">
                                    <i class="fas fa-trash-alt text-[10px]"></i> Hapus
                                </button>
                            </div>

                            {{-- Hidden Input for form submission --}}
                            <input type="hidden" name="existing_athlete_images[]" :value="photo.path">
                        </div>
                    </template>
                </div>
            </div>

            {{-- 2. New Uploads Preview (Before Saving) --}}
            <div class="mb-6" x-show="newFiles.length > 0" x-cloak>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-neon flex items-center gap-2">
                        <i class="fas fa-upload text-xs"></i> Foto Baru Yang Akan Diunggah (<span x-text="newFiles.length"></span> foto)
                    </span>
                    <button type="button" @click="clearAllNewFiles()" class="text-xs text-rose-400 hover:text-rose-300">
                        Batalkan Semua Foto Baru
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="(item, idx) in newFiles" :key="idx">
                        <div class="relative bg-slate-950 border-2 border-dashed border-neon/60 rounded-lg p-3 flex flex-col items-center justify-between overflow-hidden">
                            <div class="w-full flex items-center justify-between mb-2">
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-neon/20 text-neon border border-neon/40 uppercase">
                                    BARU &bull; POSE <span x-text="existingPhotos.length + idx + 1"></span>
                                </span>
                                <button type="button" @click="removeNewFile(idx)"
                                        class="w-5 h-5 rounded-full bg-rose-600 hover:bg-rose-500 text-white flex items-center justify-center text-xs transition"
                                        title="Batal unggah file ini">
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                            </div>

                            <div class="w-full h-36 rounded flex items-center justify-center p-2"
                                 style="background-color: #0b0f17; background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 10px 10px;">
                                <img :src="item.previewUrl" class="h-full max-w-full object-contain filter drop-shadow-[0_10px_15px_rgba(0,0,0,0.8)]">
                            </div>

                            <div class="w-full pt-2 border-t border-slate-800 mt-2 flex items-center justify-between text-[11px] text-slate-400">
                                <span class="truncate max-w-[150px]" x-text="item.name"></span>
                                <span class="font-mono text-slate-500" x-text="item.sizeFormatted"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 3. Drag & Drop Dropzone Box --}}
            <div class="mb-4">
                <input type="file" id="athlete-dropzone-input" name="athlete_images[]" multiple accept="image/png,image/webp" class="hidden" @change="handleFileInput($event)">
                
                <div 
                    class="relative border-2 border-dashed rounded-lg p-8 text-center transition-all cursor-pointer bg-slate-950/80 group select-none"
                    :class="isDragging ? 'border-neon bg-neon/5 scale-[1.005]' : 'border-slate-700 hover:border-neon/60 hover:bg-slate-900/60'"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop($event)"
                    @click="triggerFileInput()"
                >
                    <div class="flex flex-col items-center justify-center space-y-3 pointer-events-none">
                        <div class="w-14 h-14 rounded-lg bg-slate-800/90 border border-slate-700 flex items-center justify-center text-slate-300 group-hover:text-neon group-hover:border-neon/50 transition">
                            <i class="fas fa-cloud-arrow-up text-2xl text-neon"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white">
                                Tarik &amp; letakkan foto atlet PNG/WebP di sini, atau <span class="text-neon underline">pilih dari komputer</span>
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                Mendukung pemilihan banyak file sekaligus (PNG transparan atau WebP hingga 5MB per file)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Option: Add photo from URL --}}
            <div class="pt-3 border-t border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <button type="button" @click="showUrlBox = !showUrlBox" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition">
                    <i class="fas" :class="showUrlBox ? 'fa-chevron-up' : 'fa-link'"></i>
                    <span x-text="showUrlBox ? 'Tutup Input URL' : 'Atau Tambah Foto Lewat URL Gambar'"></span>
                </button>

                <div x-show="existingPhotos.length === 0 && newFiles.length === 0" class="text-xs text-amber-400/90 flex items-center gap-1.5">
                    <i class="fas fa-info-circle"></i>
                    <span>Belum ada foto custom. Hero akan menggunakan visual atlet default RuangLari.</span>
                </div>
            </div>

            <div x-show="showUrlBox" x-cloak class="mt-3 p-3 bg-slate-900 border border-slate-700 rounded-md">
                <label class="block text-slate-300 text-xs font-semibold mb-1">URL Gambar Atlet (PNG/WebP)</label>
                <div class="flex gap-2">
                    <input type="text" x-model="urlInput" placeholder="https://ruanglari.com/storage/... atau URL lainnya" 
                           class="flex-1 bg-slate-950 border border-slate-700 rounded-md p-2 text-white text-xs focus:outline-none focus:border-neon transition">
                    <button type="button" @click="addFromUrl()" 
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-md text-xs font-semibold transition shrink-0">
                        + Tambahkan ke Daftar
                    </button>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
            <a href="{{ route('admin.pages.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-md text-xs font-bold tracking-wider uppercase transition">
                Kembali ke Pages
            </a>
            <button type="submit" class="bg-neon text-dark font-black py-2.5 px-8 rounded-md hover:bg-neon/90 transition flex items-center gap-2 text-xs uppercase tracking-wider shadow-lg shadow-neon/20">
                <i class="fas fa-save"></i> Simpan Perubahan Hero
            </button>
        </div>
    </form>
</div>

<script>
function athleteDropzoneManager(initialPhotos) {
    return {
        existingPhotos: Array.isArray(initialPhotos) ? initialPhotos : [],
        newFiles: [],
        isDragging: false,
        showUrlBox: false,
        urlInput: '',

        getPoseBadge(idx) {
            if (idx === 0) return 'Pose 1 // Utama (Idle)';
            if (idx === 1) return 'Pose 2 // Hover & Ready';
            if (idx === 2) return 'Pose 3 // Sprint';
            return 'Pose ' + (idx + 1);
        },

        triggerFileInput() {
            document.getElementById('athlete-dropzone-input').click();
        },

        handleDrop(e) {
            this.isDragging = false;
            const droppedFiles = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            if (droppedFiles.length) {
                this.addFiles(droppedFiles);
            }
        },

        handleFileInput(e) {
            const selectedFiles = Array.from(e.target.files).filter(f => f.type.startsWith('image/'));
            if (selectedFiles.length) {
                this.addFiles(selectedFiles);
            }
        },

        addFiles(files) {
            files.forEach(file => {
                this.newFiles.push({
                    file: file,
                    name: file.name,
                    previewUrl: URL.createObjectURL(file),
                    sizeFormatted: (file.size / (1024 * 1024)).toFixed(2) + ' MB'
                });
            });
            this.syncDataTransfer();
        },

        removeNewFile(index) {
            if (this.newFiles[index] && this.newFiles[index].previewUrl) {
                URL.revokeObjectURL(this.newFiles[index].previewUrl);
            }
            this.newFiles.splice(index, 1);
            this.syncDataTransfer();
        },

        clearAllNewFiles() {
            this.newFiles.forEach(item => {
                if (item.previewUrl) URL.revokeObjectURL(item.previewUrl);
            });
            this.newFiles = [];
            this.syncDataTransfer();
        },

        syncDataTransfer() {
            const input = document.getElementById('athlete-dropzone-input');
            if (!input) return;
            try {
                const dt = new DataTransfer();
                this.newFiles.forEach(item => {
                    dt.items.add(item.file);
                });
                input.files = dt.files;
            } catch (err) {
                console.warn('DataTransfer sync warning:', err);
            }
        },

        removeExisting(index) {
            this.existingPhotos.splice(index, 1);
        },

        moveExisting(index, direction) {
            const targetIdx = index + direction;
            if (targetIdx < 0 || targetIdx >= this.existingPhotos.length) return;
            const item = this.existingPhotos.splice(index, 1)[0];
            this.existingPhotos.splice(targetIdx, 0, item);
        },

        addFromUrl() {
            const val = this.urlInput.trim();
            if (!val) return;
            this.existingPhotos.push({
                id: Date.now(),
                path: val,
                url: val,
                filename: val.split('/').pop() || 'image.png'
            });
            this.urlInput = '';
            this.showUrlBox = false;
        }
    };
}
</script>
@endsection

