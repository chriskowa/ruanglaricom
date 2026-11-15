@extends('layouts.pacerhub', [
    'hideFooter' => true,
    'hideChat' => true,
    'hideNav' => true,
])

@section('title', 'Calendar Studio — ' . ($project->name ?? 'Kalender Pelari'))
@section('meta_robots', 'noindex, nofollow')

@push('styles')
<script>document.documentElement.classList.add('kp-editor-active');</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/kalender-pelari/style.css') }}">
@endpush

@section('content')
<div class="kp-studio-wrapper kp-dark-theme w-screen h-screen flex flex-col bg-slate-950 text-slate-100 overflow-hidden">
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('kp_studio_theme') || 'dark';
                if (theme === 'light') {
                    document.body && document.body.classList.add('kp-light-theme');
                    var w = document.querySelector('.kp-studio-wrapper');
                    if (w) { w.classList.remove('kp-dark-theme'); w.classList.add('kp-light-theme'); }
                }
            } catch (e) {}
        })();
    </script>
    <header class="flex items-center justify-between border-b border-slate-800 bg-slate-900 px-4 sm:px-6 shrink-0 h-14 z-30 w-full gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('kalender-pelari.landing') }}" class="brand-title flex items-center gap-2 text-white hover:text-slate-200 whitespace-nowrap shrink-0">
                <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="Ruang Lari" class="w-5 h-5 object-contain shrink-0">
                <span class="font-bold tracking-tight text-sm font-['Inter_Tight','Sora',sans-serif]">RUANG LARI / STUDIO</span>
            </a>
            <span class="hidden md:inline text-slate-600">|</span>
            <div class="hidden sm:flex items-center gap-2 min-w-0">
                <span id="kpProjectNameDisplay" class="truncate text-xs font-semibold text-slate-300 max-w-[180px]">{{ $project->name ?? 'Kalender Pelari' }}</span>
                <span id="kpSaveIndicator" class="text-xs font-mono text-emerald-400 whitespace-nowrap">Tersimpan</span>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <!-- Studio Interface Theme Switcher -->
            <div class="studio-theme-toggle flex items-center rounded-md border border-slate-700 bg-slate-800 p-0.5 text-xs font-semibold shrink-0" role="group" aria-label="Tema Antarmuka Studio">
                <button type="button" class="btn-studio-dark rounded transition text-white active" title="Tampilan Studio Mode Gelap">
                    Studio Gelap
                </button>
                <button type="button" class="btn-studio-light rounded transition text-slate-400 hover:text-white" title="Tampilan Studio Mode Terang">
                    Studio Terang
                </button>
            </div>

            @if($project->is_anonymous)
                <a href="{{ $authLoginUrl }}?redirect_to={{ urlencode(request()->fullUrl()) }}"
                   class="btn-header-action rounded-md border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white transition">
                    Simpan
                </a>
            @else
                <a href="{{ route('kalender-pelari.my-projects') }}"
                   class="btn-header-action rounded-md border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white transition">
                    Proyek Saya
                </a>
            @endif
            <button type="button" onclick="document.getElementById('download')?.click()"
                    class="btn-header-action rounded-md border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white transition">
                Unduh
            </button>
            <button type="button" onclick="document.getElementById('print')?.click()"
                    class="btn-header-action rounded-md border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white transition">
                PDF
            </button>
        </div>
    </header>

    <div class="kp-workspace flex-1 w-full overflow-hidden">
        <!-- PREVIEW CANVAS STAGE (LEFT COLUMN) -->
        <section class="preview">
            <div class="toolbar">
                <button type="button" id="prev" aria-label="Halaman sebelumnya" class="p-2 rounded bg-slate-800 border border-slate-700 text-white">&larr;</button>
                <strong id="slideTitle" class="text-sm sm:text-base font-bold text-white px-3 font-['Inter_Tight','Sora',sans-serif]"></strong>
                <button type="button" id="next" aria-label="Halaman berikutnya" class="p-2 rounded bg-slate-800 border border-slate-700 text-white">&rarr;</button>
            </div>

            <div class="preview-tools flex items-center justify-between pb-2 shrink-0">
                <div class="tool-cluster flex items-center gap-1">
                    <button type="button" id="undo" aria-label="Urungkan" title="Urungkan (Ctrl+Z)" class="px-2.5 py-1 text-xs rounded bg-slate-800 border border-slate-700 text-slate-200 hover:text-white">Undo</button>
                    <button type="button" id="redo" aria-label="Ulangi" title="Ulangi (Ctrl+Shift+Z)" class="px-2.5 py-1 text-xs rounded bg-slate-800 border border-slate-700 text-slate-200 hover:text-white">Redo</button>
                    <button type="button" id="panMode" aria-label="Mode geser kanvas" title="Mode geser" class="px-2.5 py-1 text-xs rounded bg-slate-800 border border-slate-700 text-slate-200 hover:text-white">Geser</button>
                </div>
                <div class="tool-cluster flex items-center gap-1 bg-slate-900 border border-slate-800 rounded p-0.5" title="Pilih tema tampilan kanvas">
                    <button type="button" class="btn-theme-light px-2.5 py-1 text-xs rounded font-medium text-slate-300 hover:text-white transition" title="Mode Terang (Kertas Putih)">Terang</button>
                    <button type="button" class="btn-theme-dark px-2.5 py-1 text-xs rounded font-medium text-slate-300 hover:text-white transition" title="Mode Gelap (Kertas Gelap)">Gelap</button>
                </div>
                <div class="tool-cluster flex items-center gap-2">
                    <button type="button" id="zoomOut" class="w-7 h-7 flex items-center justify-center rounded bg-slate-800 border border-slate-700 text-white font-bold">&minus;</button>
                    <input type="range" id="previewZoom" min="25" max="300" step="5" value="100" class="w-20">
                    <output id="previewZoomValue" class="text-xs font-mono text-slate-400">100%</output>
                    <button type="button" id="zoomIn" class="w-7 h-7 flex items-center justify-center rounded bg-slate-800 border border-slate-700 text-white font-bold">&plus;</button>
                    <button type="button" id="zoomFit" class="px-2 py-1 text-xs rounded bg-slate-800 border border-slate-700 text-slate-200 hover:text-white">Pas Layar</button>
                </div>
            </div>

            <div class="stage" id="stage">
                <div id="canvasSpace" class="canvas-space">
                    <canvas tabindex="0" id="canvas" width="2382" height="1590" aria-label="Pratinjau kalender lari"></canvas>
                </div>
            </div>

            <p class="preview-note text-center text-xs text-slate-400 mt-2 shrink-0">
                Shift / Ctrl + Klik untuk memilih banyak elemen · Geser elemen langsung di atas kalender
            </p>

            <nav id="slides" aria-label="Navigasi halaman" class="mt-3 shrink-0"></nav>
            <p id="sizeInfo" class="text-center text-xs font-mono text-slate-400 mt-1 shrink-0"></p>
        </section>

        <!-- ASIDE INSPECTOR (RIGHT COLUMN) -->
        <aside aria-label="Panel pengaturan kalender">
            <div class="inspector-head">
                <div class="eyebrow">Studio Desain Kalender</div>
                <h1>{{ $project->name ?? 'Kalender Pelari' }}</h1>
                <p class="muted">Sesuaikan template, foto, rute lari, dan ekspor kalender resolusi tinggi.</p>
            </div>

            <nav class="inspector-nav" aria-label="Navigasi pengaturan">
                <a href="#designSection">Desain</a>
                <a href="#contentSection">Konten</a>
                <a href="#assetsSection">Stiker</a>
                <a href="#positionSection">Posisi</a>
                <a href="#ruangLariSection">Ruang Lari</a>
                <a href="#exportSection">Ekspor</a>
            </nav>

            <!-- 01 / DESAIN KALENDER -->
            <section class="inspector-section" id="designSection">
                <h2 class="section-title">01 / Desain kalender <small>Gaya visual</small></h2>

                <!-- Tema Antarmuka Studio -->
                <fieldset class="mb-3">
                    <legend>Tema antarmuka studio</legend>
                    <div class="studio-theme-toggle grid-toggle grid grid-cols-2 gap-1.5 p-1 bg-slate-950 border border-slate-800 rounded-md" role="group" aria-label="Tema Antarmuka Studio">
                        <button type="button" class="btn-studio-dark py-1.5 px-3 rounded text-xs font-semibold text-white transition active" title="Studio Editor Mode Gelap">
                            Studio Gelap
                        </button>
                        <button type="button" class="btn-studio-light py-1.5 px-3 rounded text-xs font-semibold text-slate-400 transition hover:text-white" title="Studio Editor Mode Terang">
                            Studio Terang
                        </button>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Gaya siap pakai</legend>
                    <div class="preset-grid">
                        <button type="button" class="preset-card" data-preset="race" aria-pressed="false">
                            <b>Race Day</b><small>Sporty, kontras, penuh energi</small>
                        </button>
                        <button type="button" class="preset-card" data-preset="editorial" aria-pressed="false">
                            <b>Editorial</b><small>Serif, hangat, elegan</small>
                        </button>
                        <button type="button" class="preset-card" data-preset="trail" aria-pressed="false">
                            <b>Trail Club</b><small>Hijau alam, tenang, bersih</small>
                        </button>
                        <button type="button" class="preset-card" data-preset="night" aria-pressed="false">
                            <b>Night Miles</b><small>Gelap, biru es, modern</small>
                        </button>
                    </div>
                </fieldset>

                <input type="hidden" id="layout" value="{{ strtolower($project->format_type ?? '') === 'a3_landscape' ? 'wall' : 'landscape' }}">
                <fieldset>
                    <legend>Ukuran & tata letak</legend>
                    <div id="layoutChoices" class="choice-grid"></div>
                </fieldset>

                <input type="hidden" id="monthsPerPage" value="1">
                <fieldset>
                    <legend>Format halaman</legend>
                    <div id="monthsPerPageChoices" class="choice-grid"></div>
                </fieldset>

                <!-- Mode Tema & Warna Kertas/Aksen Cetak -->
                <fieldset class="mt-3">
                    <legend>Warna kertas kalender (cetak)</legend>
                    <div class="theme-mode-toggle grid grid-cols-2 gap-1.5 p-1 bg-slate-950 border border-slate-800 rounded-md">
                        <button type="button" id="btnThemeLight" class="btn-theme-light theme-btn py-1.5 px-3 rounded text-xs font-semibold text-slate-300 transition hover:text-white" title="Gunakan tema terang kertas putih bersih">
                            Kertas Terang (Putih)
                        </button>
                        <button type="button" id="btnThemeDark" class="btn-theme-dark theme-btn py-1.5 px-3 rounded text-xs font-semibold text-slate-300 transition hover:text-white" title="Gunakan tema gelap atletik stealth">
                            Kertas Gelap (Hitam)
                        </button>
                    </div>
                </fieldset>

                <div class="two-inputs grid grid-cols-2 gap-2 mt-2">
                    <label class="text-xs text-slate-300">Warna kertas
                        <input id="paper" type="color" value="#ffffff" class="w-full h-9 rounded border border-slate-700 bg-slate-900 cursor-pointer">
                    </label>
                    <label class="text-xs text-slate-300">Warna aksen
                        <input id="accent" type="color" value="#f45123" class="w-full h-9 rounded border border-slate-700 bg-slate-900 cursor-pointer">
                    </label>
                </div>

                <fieldset>
                    <legend>Fitur pelari</legend>
                    <label class="choice flex items-center gap-2 cursor-pointer p-2.5 rounded-md border border-slate-800 bg-slate-900">
                        <input id="runningMode" type="checkbox" checked class="w-4 h-4 accent-red-600">
                        <span class="text-xs text-slate-200">
                            <b class="text-white block">Mode Kalender Lari</b>
                            Log KM harian, target mingguan, & catatan training
                        </span>
                    </label>
                </fieldset>

                <input type="hidden" id="template" value="classic">
                <fieldset>
                    <legend>Pilihan Template <span class="eyebrow" id="templateCount">/ 37 desain</span></legend>
                    <div id="templateChoices" class="choice-strip" tabindex="0" aria-label="Pilihan template dengan pratinjau"></div>
                    <p class="strip-hint text-xs text-slate-400 mt-1">Geser horizontal untuk memilih 37 desain layout atletik</p>
                </fieldset>

                <!-- Paket Foto Idola / Atlet Lari -->
                <fieldset class="mt-3">
                    <legend>Paket Foto Idola Lari <span class="eyebrow" id="athleteTemplateCount">/ Atlet Nasional & Dunia</span></legend>
                    <div id="athleteTemplateChoices" class="choice-strip" tabindex="0" aria-label="Pilihan template foto idola lari">
                        <div class="text-xs text-slate-400 p-2">Memuat template atlet...</div>
                    </div>
                    <p class="strip-hint text-xs text-slate-400 mt-1">Pilih idola lari untuk menerapkan 13 foto terkurasi ke seluruh bulan</p>
                </fieldset>

                <input type="hidden" id="font" value="Plus Jakarta Sans">
                <fieldset>
                    <legend>Tipografi <span id="fontCount"></span></legend>
                    <input id="fontSearch" class="font-filter w-full p-2 rounded-md bg-slate-950 border border-slate-800 text-xs text-white"
                           type="search" placeholder="Cari font: serif, bold, athletic, mono..." aria-label="Cari font">
                    <div id="fontChoices" class="choice-strip mt-2" tabindex="0" aria-label="Pilihan font"></div>
                </fieldset>
            </section>

            <!-- 02 / KONTEN HALAMAN -->
            <section class="inspector-section" id="contentSection">
                <h2 class="section-title">02 / Konten halaman <small>Foto & teks</small></h2>
                <input type="hidden" id="page" value="-1">
                <fieldset>
                    <legend>Halaman aktif</legend>
                    <div id="pageChoices" class="choice-grid"></div>
                </fieldset>

                <div class="two-inputs grid grid-cols-2 gap-2">
                    <label class="text-xs text-slate-300">Nama Pelari
                        <input id="name" value="{{ auth()->user()?->name ?? $project->name ?? 'Runner' }}" maxlength="40"
                               class="w-full p-2 rounded-md bg-slate-950 border border-slate-800 text-xs text-white">
                    </label>
                    <label class="text-xs text-slate-300">Tahun
                        <input id="year" type="number" value="{{ $project->year ?? 2026 }}" min="2020" max="2040"
                               class="w-full p-2 rounded-md bg-slate-950 border border-slate-800 text-xs text-white tabular-nums">
                    </label>
                </div>

                <label class="flex items-center gap-2 mt-3 cursor-pointer text-xs text-slate-300">
                    <input id="showHolidays" type="checkbox" checked class="accent-red-600">
                    Tampilkan hari libur nasional Indonesia
                </label>

                <fieldset class="mt-4">
                    <legend>Tata letak foto halaman ini <span class="eyebrow" id="collageModeLabel">/ 1 Foto</span></legend>
                    <div id="collageChoices" class="choice-grid grid-cols-2 gap-1.5 mb-2"></div>
                    <div id="collageGapRow" style="display:none;" class="flex items-center justify-between gap-2 mb-2">
                        <label class="m-0 text-xs text-slate-300 flex-1">Jarak foto (gap)
                            <input id="collageGap" type="range" min="0" max="20" value="4" class="w-full mt-1">
                            <output id="collageGapValue" class="text-xs text-slate-400 font-mono">4px</output>
                        </label>
                    </div>
                    <div id="slotSelectorRow" style="display:none;" class="flex flex-col gap-1 mb-2">
                        <span class="text-xs font-semibold text-slate-200">Pilih slot foto:</span>
                        <div id="slotTabs" class="slot-tabs flex gap-1.5 flex-wrap"></div>
                    </div>
                </fieldset>

                <label id="photoLabel" class="text-xs text-slate-300 block mt-2">Unggah foto pribadi
                    <input id="photo" type="file" accept="image/*" class="w-full mt-1 text-xs text-slate-400">
                </label>
                <div class="two-inputs grid grid-cols-2 gap-2 mt-2">
                    <button type="button" id="remove" class="p-2 text-xs rounded-md border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200">Kosongkan foto</button>
                    <button type="button" id="reset" class="p-2 text-xs rounded-md border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200">Foto contoh</button>
                </div>

                <div class="space-y-2 mt-3">
                    <label class="text-xs text-slate-300">Posisi horizontal (crop X)
                        <input id="px" type="range" min="0" max="100" value="50" class="w-full">
                    </label>
                    <label class="text-xs text-slate-300">Posisi vertikal (crop Y)
                        <input id="py" type="range" min="0" max="100" value="50" class="w-full">
                    </label>
                    <label class="text-xs text-slate-300">Perbesar (zoom)
                        <input id="zoom" type="range" min="1" max="3" step=".05" value="1" class="w-full">
                    </label>
                </div>

                <!-- Custom Text Block -->
                <div class="text-editor mt-4 p-3 rounded-lg border border-slate-800 bg-slate-900">
                    <div class="flex justify-between items-center mb-2">
                        <strong class="text-xs font-bold text-white">Teks Tambahan Bebas</strong>
                        <button type="button" id="addText" class="px-2.5 py-1 text-xs rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white">+ Tambah Teks</button>
                    </div>
                    <div id="textControls" style="display:none;" class="flex flex-col gap-2 mt-2">
                        <label class="text-xs text-slate-300">Isi Teks
                            <input id="customTextContent" type="text" placeholder="Contoh: PB 10K 45:12 / Tokyo Marathon"
                                   class="w-full p-2 rounded-md bg-slate-950 border border-slate-800 text-xs text-white">
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="text-xs text-slate-300">Ukuran
                                <input id="customTextSize" type="range" min="10" max="80" value="24" class="w-full">
                            </label>
                            <label class="text-xs text-slate-300">Warna
                                <input id="customTextColor" type="color" value="#ffffff" class="w-full h-8 rounded border border-slate-700 bg-slate-950">
                            </label>
                        </div>
                        <div class="flex items-center gap-3 mt-1">
                            <label class="flex items-center gap-1.5 text-xs text-slate-300 cursor-pointer">
                                <input id="customTextBold" type="checkbox" class="accent-red-600"> Tebal
                            </label>
                            <label class="flex items-center gap-1.5 text-xs text-slate-300 cursor-pointer">
                                <input id="customTextItalic" type="checkbox" class="accent-red-600"> Miring
                            </label>
                            <button type="button" id="deleteCustomText" class="ml-auto px-2 py-0.5 text-xs text-red-400 hover:text-red-300">Hapus</button>
                        </div>
                    </div>
                    <small id="noTextHint" class="text-[11px] text-slate-400 block mt-1">Klik "+ Tambah Teks" untuk menambahkan catatan atau target pribadi.</small>
                </div>
            </section>

            <!-- 03 / ELEMEN & STIKER -->
            <section class="inspector-section" id="assetsSection">
                <h2 class="section-title">03 / Elemen & stiker <small>Aksen visual</small></h2>
                <fieldset>
                    <legend>Stiker atletik siap pakai</legend>
                    <div class="preset-badges">
                        <button type="button" class="preset-badge-btn" data-preset="race"><b>Race Day</b><small>Lari kontras</small></button>
                        <button type="button" class="preset-badge-btn" data-preset="editorial"><b>Editorial</b><small>Bintang klasik</small></button>
                        <button type="button" class="preset-badge-btn" data-preset="trail"><b>Trail</b><small>Gunung alam</small></button>
                        <button type="button" class="preset-badge-btn" data-preset="night"><b>Night</b><small>Bulan malam</small></button>
                    </div>
                </fieldset>

                <span class="asset-subtitle text-xs font-semibold text-slate-200 mt-2 block">Ikon Atletik FontAwesome</span>
                <input type="search" id="iconSearch" class="font-filter w-full p-2 rounded-md bg-slate-950 border border-slate-800 text-xs text-white mt-1 mb-2"
                       placeholder="Cari ikon: lari, medali, piala, api..." aria-label="Cari ikon">
                <div id="iconPicker" class="asset-picker max-h-40 overflow-y-auto" aria-label="Ikon Font Awesome"></div>

                <span class="asset-subtitle text-xs font-semibold text-slate-200 mt-3 block">Simbol & Karakter Cepat</span>
                <div id="emojiPicker" class="asset-picker max-h-24 overflow-y-auto mb-2" aria-label="Pilihan emoji"></div>
                <div class="emoji-entry flex gap-1 mb-3">
                    <input id="emojiInput" type="text" maxlength="24" placeholder="Ketik simbol..." aria-label="Simbol teks"
                           class="flex-1 p-1.5 rounded-md bg-slate-950 border border-slate-800 text-xs text-white">
                    <button type="button" id="addEmoji" class="px-3 py-1 text-xs rounded-md border border-slate-700 bg-slate-800 text-white">+ Tambah</button>
                </div>

                <label class="text-xs text-slate-300 block">Unggah PNG transparan
                    <input id="addPng" type="file" accept="image/png" multiple class="w-full mt-1 text-xs text-slate-400">
                </label>

                <div id="assetControls" class="asset-controls mt-3 space-y-2" aria-label="Pengaturan elemen visual">
                    <label class="text-xs text-slate-300">Ukuran elemen
                        <input id="assetSize" type="range" min="16" max="360" value="64" class="w-full">
                        <output id="assetSizeValue" class="text-xs font-mono text-slate-400">64 × 64</output>
                    </label>
                    <label id="assetColorLabel" class="text-xs text-slate-300">Warna elemen
                        <input id="assetColor" type="color" value="#f45123" class="w-full h-8 rounded border border-slate-700 bg-slate-950">
                    </label>
                    <label class="text-xs text-slate-300">Rotasi
                        <input id="assetRotation" type="range" min="-180" max="180" value="0" class="w-full">
                        <output id="assetRotationValue" class="text-xs font-mono text-slate-400">0°</output>
                    </label>
                    <label class="text-xs text-slate-300">Transparansi
                        <input id="assetOpacity" type="range" min="10" max="100" value="100" class="w-full">
                        <output id="assetOpacityValue" class="text-xs font-mono text-slate-400">100%</output>
                    </label>
                </div>

                <div class="two-inputs grid grid-cols-2 gap-2 mt-3">
                    <button type="button" id="duplicateAsset" class="p-2 text-xs rounded-md border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200">Duplikat elemen</button>
                    <button type="button" id="deleteAssets" class="p-2 text-xs rounded-md border border-slate-700 bg-slate-800 hover:bg-slate-700 text-red-300">Hapus elemen</button>
                </div>
                <button type="button" id="copyDecorations" class="w-full mt-2 p-2 text-xs rounded-md border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200">Salin stiker ke semua halaman</button>
            </section>

            <!-- 04 / POSISI ELEMEN -->
            <section class="inspector-section" id="positionSection">
                <h2 class="section-title">04 / Posisi elemen <small>Geser & kunci</small></h2>
                <div class="selection-status-bar flex items-center justify-between p-2 rounded-md border border-slate-800 bg-slate-900 mb-2">
                    <p id="selectionInfo" class="text-xs text-slate-300 m-0">Belum ada elemen terpilih</p>
                    <div class="selection-actions flex gap-1">
                        <button type="button" id="selectAllElements" class="ghost-btn px-2 py-1 text-xs rounded border border-slate-700 text-slate-200">Semua</button>
                        <button type="button" id="clearSelection" class="ghost-btn px-2 py-1 text-xs rounded border border-slate-700 text-slate-200">Lepas</button>
                        <button type="button" id="lockElement" class="ghost-btn px-2 py-1 text-xs rounded border border-slate-700 text-slate-200">Kunci</button>
                        <button type="button" id="unlockAll" class="ghost-btn px-2 py-1 text-xs rounded border border-slate-700 text-slate-200">Buka</button>
                    </div>
                </div>

                <div class="two-inputs grid grid-cols-2 gap-2 mb-2">
                    <button type="button" id="duplicateSelection" class="p-2 text-xs rounded-md border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200">Duplikat Terpilih</button>
                    <button type="button" id="deleteSelection" class="p-2 text-xs rounded-md border border-slate-700 bg-slate-800 hover:bg-slate-700 text-red-300">Hapus Terpilih</button>
                </div>

                <div class="nudge-cluster grid grid-cols-4 gap-1 mb-2" aria-label="Geser posisi">
                    <button type="button" id="moveUp" class="p-2 text-xs rounded bg-slate-800 border border-slate-700 text-white">▲</button>
                    <button type="button" id="moveLeft" class="p-2 text-xs rounded bg-slate-800 border border-slate-700 text-white">◀</button>
                    <button type="button" id="moveDown" class="p-2 text-xs rounded bg-slate-800 border border-slate-700 text-white">▼</button>
                    <button type="button" id="moveRight" class="p-2 text-xs rounded bg-slate-800 border border-slate-700 text-white">▶</button>
                </div>

                <div class="quick-align-bar flex gap-1 flex-wrap mb-3" aria-label="Ratakan elemen">
                    <button type="button" id="alignLeft" class="ghost-btn px-2 py-1 text-xs rounded border border-slate-700 text-slate-200">Rata kiri</button>
                    <button type="button" id="alignTop" class="ghost-btn px-2 py-1 text-xs rounded border border-slate-700 text-slate-200">Rata atas</button>
                    <button type="button" id="alignCenterX" class="ghost-btn px-2 py-1 text-xs rounded border border-slate-700 text-slate-200">Tengah X</button>
                    <button type="button" id="alignCenterY" class="ghost-btn px-2 py-1 text-xs rounded border border-slate-700 text-slate-200">Tengah Y</button>
                    <button type="button" id="alignSafe" class="ghost-btn px-2 py-1 text-xs rounded border border-slate-700 text-slate-200">Batas aman</button>
                </div>

                <div class="two-inputs grid grid-cols-2 gap-2">
                    <label class="text-xs text-slate-300"><span id="positionXLabel">Geser X</span>
                        <input id="elementX" type="number" value="0" class="w-full p-1.5 rounded-md bg-slate-950 border border-slate-800 text-xs text-white tabular-nums">
                    </label>
                    <label class="text-xs text-slate-300"><span id="positionYLabel">Geser Y</span>
                        <input id="elementY" type="number" value="0" class="w-full p-1.5 rounded-md bg-slate-950 border border-slate-800 text-xs text-white tabular-nums">
                    </label>
                </div>

                <fieldset class="mt-3">
                    <legend>Daftar elemen halaman ini</legend>
                    <div id="elementChoices" class="element-list max-h-36 overflow-y-auto space-y-1"></div>
                </fieldset>
                <button type="button" id="resetPositions" class="w-full mt-2 p-2 text-xs rounded-md border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200">Reset semua posisi halaman ini</button>
            </section>

            <!-- 05 / INTEGRASI RUANG LARI -->
            <section class="inspector-section" id="ruangLariSection">
                <h2 class="section-title">05 / Ekosistem Ruang Lari <small>QR & Event</small></h2>
                <div class="space-y-3">
                    <label class="choice flex items-center gap-2 cursor-pointer p-2.5 rounded-md border border-slate-800 bg-slate-900">
                        <input id="ruangLariQr" type="checkbox" checked class="w-4 h-4 accent-red-600">
                        <span class="text-xs text-slate-200">
                            <b class="text-white block">Tampilkan QR Code Profil Resmi</b>
                            Scan untuk membuka profil pelari & kalender latihan online
                        </span>
                    </label>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="text-xs text-slate-300">Runner Username
                            <input id="ruangLariUser" value="{{ auth()->user()?->username ?? 'runner' }}"
                                   class="w-full p-1.5 rounded-md bg-slate-950 border border-slate-800 text-xs text-white">
                        </label>
                        <label class="text-xs text-slate-300">Access Token
                            <input id="ruangLariToken" value="{{ auth()->user() ? substr(md5(auth()->id() . 'ruanglari'), 0, 8) : 'RL2026' }}"
                                   class="w-full p-1.5 rounded-md bg-slate-950 border border-slate-800 text-xs text-white font-mono">
                        </label>
                    </div>

                    <div class="p-2 rounded bg-slate-950 border border-slate-800 text-[11px] text-slate-400 break-all">
                        <span class="text-slate-500 block mb-0.5">Target URL Scan:</span>
                        <span id="ruangLariUrlPreview" class="text-sky-400 font-mono">https://ruanglari.com/runner/runner/2026-01?token=RL2026</span>
                    </div>

                    <div class="pt-2 border-t border-slate-800 space-y-2">
                        <button type="button" id="btnSyncEvents" class="w-full p-2 text-xs rounded-md bg-sky-900/40 border border-sky-700/60 hover:bg-sky-900/70 text-sky-200 font-semibold transition">
                            Tandai Agenda Lomba Nasional Ruang Lari
                        </button>
                        <button type="button" id="btnClearStravaLog" class="w-full p-2 text-xs rounded-md bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-300 transition">
                            Bersihkan Agenda & Catatan Lari
                        </button>
                    </div>
                </div>
            </section>

            <!-- 06 / EKSPOR & SIMPAN -->
            <section class="inspector-section" id="exportSection">
                <h2 class="section-title">06 / Ekspor & simpan <small>Cetak & backup</small></h2>
                <div class="space-y-2">
                    <button type="button" id="download" class="primary w-full p-2.5 text-xs font-bold rounded-md bg-red-600 hover:bg-red-700 text-white">
                        Unduh Halaman Ini (PNG 300 DPI)
                    </button>
                    <button type="button" id="print" class="w-full p-2.5 text-xs font-bold rounded-md border border-slate-700 bg-slate-800 hover:bg-slate-700 text-white">
                        Cetak / Simpan PDF (13 Halaman Lengkap)
                    </button>
                    <div class="pt-2 border-t border-slate-800 flex gap-2">
                        <button type="button" id="saveProject" class="flex-1 p-2 text-xs rounded-md border border-slate-700 bg-slate-900 hover:bg-slate-800 text-slate-200">
                            Unduh File Proyek (.json)
                        </button>
                        <label class="flex-1 cursor-pointer p-2 text-xs text-center rounded-md border border-slate-700 bg-slate-900 hover:bg-slate-800 text-slate-200">
                            Buka File Proyek
                            <input id="loadProject" type="file" accept=".json" class="hidden">
                        </label>
                    </div>
                </div>
                <p id="status" class="mt-3 text-xs font-mono text-sky-400" role="status" aria-live="polite"></p>
            </section>
        </aside>
    </div>
</div>

<!-- SCRIPTS CONFIGURATION -->
<script>
    window.KP_BG_BASE_URL = "{{ asset('images/kalender-pelari/bg') }}/";
    window.KP_QR_BASE_URL = "{{ url('/runner/{user}/{month}') }}?token={token}";
    window.KP_CONFIG = {
        projectId: {{ $project->is_anonymous ? 'null' : (int) $project->id }},
        anonymousUuid: "{{ $project->anonymous_uuid ?? '' }}",
        isTrial: {{ $project->is_anonymous ? 'true' : 'false' }},
        csrfToken: "{{ csrf_token() }}",
        autosaveUrl: "{{ $autosaveApiUrl }}",
        eventsSearchUrl: "{{ route('kalender-pelari.api.events.search') }}",
        versionCounter: {{ (int) ($project->version_counter ?? 1) }}
    };
    @if(!empty($project->state_draft))
    window.KP_INITIAL_DRAFT = {!! json_encode($project->state_draft, JSON_UNESCAPED_UNICODE) !!};
    @else
    window.KP_INITIAL_DRAFT = null;
    @endif
</script>

<script src="{{ asset('js/kalender-pelari/qrcode.min.js') }}"></script>
<script src="{{ asset('js/kalender-pelari/data.js') }}"></script>
<script src="{{ asset('js/kalender-pelari/state.js') }}"></script>
<script src="{{ asset('js/kalender-pelari/canvas.js') }}"></script>
<script src="{{ asset('js/kalender-pelari/interaction.js') }}"></script>
<script src="{{ asset('js/kalender-pelari/export.js') }}"></script>
<script src="{{ asset('js/kalender-pelari/app.js') }}"></script>
@endsection
