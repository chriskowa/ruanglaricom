@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Program Latihan Saya | Ruang Lari')

@section('content')
<style>
    /*
     * Ruang Lari — Training Programs
     * Direction: dark performance editorial.
     * High-contrast typography, restrained neon, flatter surfaces,
     * fewer pills/glows, and stronger information hierarchy.
     */
    .programs-page {
        --rl-bg: #07090b;
        --rl-panel: #0d1014;
        --rl-panel-2: #11151a;
        --rl-panel-3: #151a20;
        --rl-line: #293039;
        --rl-line-soft: #1d232a;
        --rl-text: #f8fafc;
        --rl-text-soft: #d7dee7;
        --rl-text-muted: #aeb9c7;
        --rl-neon: #c8ff3d;
        --rl-neon-ink: #0a0d08;
        --rl-danger: #fb7185;
        --rl-warning: #fbbf24;
        --rl-success: #4ade80;
        --rl-info: #60a5fa;

        color: var(--rl-text);
        font-family: "IBM Plex Sans", "Manrope", ui-sans-serif, system-ui, sans-serif;
    }

    .programs-page *,
    .programs-page *::before,
    .programs-page *::after {
        box-sizing: border-box;
    }

    .programs-page .rl-kicker {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        color: var(--rl-text-soft);
        font-size: .68rem;
        line-height: 1;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .programs-page .rl-kicker::before {
        content: "";
        width: 7px;
        height: 7px;
        background: var(--rl-neon);
        border-radius: 1px;
    }

    .programs-page .rl-title {
        color: var(--rl-text);
        font-size: clamp(1.8rem, 3vw, 2.65rem);
        line-height: 1.02;
        font-weight: 850;
        letter-spacing: -.045em;
    }

    .programs-page .rl-copy {
        color: var(--rl-text-muted);
        line-height: 1.65;
    }

    .programs-page .rl-section {
        border-top: 1px solid var(--rl-line);
        padding-top: 1.4rem;
    }

    .programs-page .rl-section-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.15rem;
    }

    .programs-page .rl-section-index {
        color: var(--rl-neon);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .08em;
    }

    .programs-page .rl-section-title {
        color: var(--rl-text);
        font-size: 1.05rem;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -.018em;
    }

    .programs-page .rl-card {
        position: relative;
        background:
            linear-gradient(180deg, rgba(255,255,255,.018), rgba(255,255,255,0)),
            var(--rl-panel);
        border: 1px solid var(--rl-line);
        border-radius: 12px;
        transition: border-color .2s ease, background-color .2s ease, transform .2s ease;
    }

    .programs-page .rl-card:hover {
        border-color: #48525e;
        background-color: var(--rl-panel-2);
    }

    .programs-page .rl-active-card {
        overflow: hidden;
    }

    .programs-page .rl-active-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--rl-neon);
    }

    .programs-page .rl-meta-label,
    .programs-page label {
        color: var(--rl-text-soft);
        font-size: .68rem;
        font-weight: 750;
        letter-spacing: .035em;
    }

    .programs-page .rl-meta-value {
        color: var(--rl-text);
        font-size: .8rem;
        font-weight: 750;
    }

    .programs-page .rl-muted {
        color: var(--rl-text-muted);
    }

    .programs-page .rl-tag {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        min-height: 24px;
        padding: 0 .5rem;
        border: 1px solid var(--rl-line);
        border-radius: 4px;
        background: #0a0d10;
        color: var(--rl-text-soft);
        font-size: .62rem;
        font-weight: 850;
        letter-spacing: .09em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .programs-page .rl-tag::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    .programs-page .rl-tag--beginner { color: #86efac; }
    .programs-page .rl-tag--intermediate { color: #fde68a; }
    .programs-page .rl-tag--advanced { color: #fda4af; }
    .programs-page .rl-tag--inactive { color: #fda4af; }
    .programs-page .rl-tag--finished { color: #93c5fd; }

    .programs-page .rl-avatar {
        border: 1px solid #3b4652;
        background: #161b21;
    }

    .programs-page .rl-coach-row {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .7rem 0;
        border-top: 1px solid var(--rl-line-soft);
        border-bottom: 1px solid var(--rl-line-soft);
    }

    .programs-page .rl-data-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        border-top: 1px solid var(--rl-line);
        border-bottom: 1px solid var(--rl-line);
    }

    .programs-page .rl-data-cell {
        padding: .8rem 0;
    }

    .programs-page .rl-data-cell + .rl-data-cell {
        padding-left: 1rem;
        border-left: 1px solid var(--rl-line);
    }

    .programs-page .rl-progress-track {
        height: 5px;
        overflow: hidden;
        border-radius: 2px;
        background: #252b32;
    }

    .programs-page .rl-progress-bar {
        height: 100%;
        border-radius: 2px;
        background: var(--rl-neon);
        transition: width .45s ease;
    }

    .programs-page .rl-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .55rem;
        min-height: 42px;
        padding: .65rem .9rem;
        border-radius: 7px;
        border: 1px solid transparent;
        font-size: .76rem;
        line-height: 1;
        font-weight: 820;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }

    .programs-page .rl-btn:hover {
        transform: translateY(-1px);
    }

    .programs-page .rl-btn-primary {
        background: var(--rl-neon);
        border-color: var(--rl-neon);
        color: var(--rl-neon-ink);
    }

    .programs-page .rl-btn-primary:hover {
        background: #d5ff6d;
        border-color: #d5ff6d;
    }

    .programs-page .rl-btn-secondary {
        background: #151a20;
        border-color: #39434e;
        color: var(--rl-text);
    }

    .programs-page .rl-btn-secondary:hover {
        background: #1a2027;
        border-color: #5d6875;
        color: #fff;
    }

    .programs-page .rl-btn-danger {
        background: #241115;
        border-color: #6b2733;
        color: #fecdd3;
    }

    .programs-page .rl-btn-danger:hover {
        background: #35171d;
        border-color: #a64052;
        color: #fff;
    }

    .programs-page .rl-icon-btn {
        width: 42px;
        min-width: 42px;
        padding: 0;
    }

    .programs-page .rl-empty {
        border: 1px dashed #3d4752;
        border-radius: 10px;
        background: #0a0d10;
    }

    .programs-page .rl-market-card .rl-cover::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(7,9,11,.74), rgba(7,9,11,0) 55%);
        pointer-events: none;
    }

    .programs-page .rl-market-card img {
        filter: saturate(.85) contrast(1.03);
        transition: transform .35s ease, filter .35s ease;
    }

    .programs-page .rl-market-card:hover img {
        transform: scale(1.025);
        filter: saturate(1) contrast(1.05);
    }

    .programs-page .rl-link {
        color: var(--rl-text-soft);
        font-size: .73rem;
        font-weight: 750;
        transition: color .18s ease;
    }

    .programs-page .rl-link:hover {
        color: var(--rl-neon);
    }

    .programs-page .rl-modal-backdrop {
        background: rgba(2, 4, 6, .86);
        backdrop-filter: blur(8px);
    }

    .programs-page .rl-modal {
        background: #0d1014;
        border: 1px solid #3b4652;
        border-radius: 12px;
        box-shadow: 0 24px 80px rgba(0,0,0,.52);
    }

    .programs-page .rl-input {
        width: 100%;
        min-height: 44px;
        padding: .7rem .85rem;
        border-radius: 7px;
        border: 1px solid #3a444f;
        background: #07090b;
        color: #fff;
        font-size: .82rem;
        font-weight: 700;
        outline: none;
        color-scheme: dark;
    }

    .programs-page .rl-input:focus {
        border-color: var(--rl-neon);
        box-shadow: 0 0 0 2px rgba(200,255,61,.09);
    }

    .programs-page .rl-price {
        color: var(--rl-neon);
        font-size: .8rem;
        font-weight: 900;
        letter-spacing: -.01em;
    }

    @media (max-width: 640px) {
        .programs-page .rl-section-head {
            align-items: flex-start;
        }
    }
</style>

<div class="programs-page py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto" x-data="programsManager()">
    <!-- Header -->
    <header class="mb-10">
        <div class="rl-kicker mb-4">Training desk</div>

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div class="max-w-2xl">
                <h1 class="rl-title">Program Latihan Saya</h1>
                <p class="rl-copy text-sm mt-3">
                    Kelola program yang sedang berjalan, siapkan program berikutnya, dan pantau riwayat latihan Anda.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('runner.calendar') }}"
                   class="rl-btn rl-btn-secondary rl-icon-btn"
                   aria-label="Buka kalender latihan"
                   title="Kalender latihan">
                    <i class="fas fa-calendar-alt"></i>
                </a>
                <a href="{{ route('marketplace.index') }}" class="rl-btn rl-btn-primary">
                    <i class="fas fa-shopping-bag"></i>
                    Marketplace
                </a>
            </div>
        </div>
    </header>

    <!-- Active Program -->
    <section class="rl-section mb-12">
        <div class="rl-section-head">
            <div class="flex items-center gap-3">
                <span class="rl-section-index">01</span>
                <h2 class="rl-section-title">Program aktif</h2>
            </div>
            @if($activePrograms->count() > 0)
                <span class="rl-meta-label">{{ $activePrograms->count() }} program berjalan</span>
            @endif
        </div>

        @if($activePrograms->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                @foreach($activePrograms as $active)
                    @php
                        $prog = $active->program;
                        $coach = $prog->coach;
                    @endphp

                    <article class="rl-card rl-active-card p-5 sm:p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <span class="rl-tag
                                        @if(($prog->difficulty ?? 'beginner') === 'beginner') rl-tag--beginner
                                        @elseif(($prog->difficulty ?? 'beginner') === 'intermediate') rl-tag--intermediate
                                        @else rl-tag--advanced @endif">
                                        {{ $prog->difficulty ?? 'Beginner' }}
                                    </span>
                                    <h3 class="text-xl sm:text-2xl font-extrabold text-white tracking-[-0.025em] mt-3 leading-tight">
                                        {{ $prog->title }}
                                    </h3>
                                </div>

                                <div class="text-right shrink-0">
                                    <div class="rl-meta-label">Durasi</div>
                                    <div class="rl-meta-value font-mono mt-1">{{ $prog->duration_weeks ?? 12 }} minggu</div>
                                </div>
                            </div>

                            @if($coach)
                                <div class="rl-coach-row mt-5">
                                    <img
                                        src="{{ $coach->avatar ? (str_starts_with($coach->avatar, 'http') ? $coach->avatar : (str_starts_with($coach->avatar, '/storage') ? asset(ltrim($coach->avatar, '/')) : asset('storage/' . $coach->avatar))) : asset('images/profile/17.jpg') }}"
                                        alt="{{ $coach->name }}"
                                        class="rl-avatar w-9 h-9 rounded-lg object-cover">
                                    <div class="min-w-0">
                                        <div class="rl-meta-label">Coach</div>
                                        <div class="text-sm font-bold text-white truncate mt-0.5">{{ $coach->name }}</div>
                                    </div>
                                </div>
                            @endif

                            <div class="rl-data-grid mt-5">
                                <div class="rl-data-cell">
                                    <div class="rl-meta-label">Tanggal mulai</div>
                                    <div class="rl-meta-value font-mono mt-1.5">
                                        {{ \Carbon\Carbon::parse($active->start_date)->format('d M Y') }}
                                    </div>
                                </div>
                                <div class="rl-data-cell">
                                    <div class="rl-meta-label">Target selesai</div>
                                    <div class="rl-meta-value font-mono mt-1.5">
                                        {{ \Carbon\Carbon::parse($active->end_date)->format('d M Y') }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5">
                                <div class="flex items-end justify-between gap-4 mb-2.5">
                                    <div>
                                        <div class="rl-meta-label">Progres latihan</div>
                                        <div class="text-xs text-white font-bold mt-1">
                                            {{ $active->completed_sessions }} / {{ $active->total_sessions }} sesi
                                        </div>
                                    </div>
                                    <div class="text-lg font-black text-white tracking-tight">
                                        {{ $active->progress_percent }}<span class="text-[11px] text-[var(--rl-neon)] ml-0.5">%</span>
                                    </div>
                                </div>

                                <div class="rl-progress-track" aria-label="Progres {{ $active->progress_percent }} persen">
                                    <div class="rl-progress-bar" style="width: {{ $active->progress_percent }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-6 pt-5 border-t border-[#252c34]">
                            <a href="{{ route('runner.calendar') }}" class="rl-btn rl-btn-primary flex-1">
                                <i class="fas fa-play"></i>
                                Buka latihan hari ini
                            </a>
                            <button
                                @click="confirmReset({{ $active->id }}, '{{ $prog->title }}')"
                                class="rl-btn rl-btn-danger rl-icon-btn"
                                title="Reset program"
                                aria-label="Reset program">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rl-empty p-7 sm:p-8 max-w-2xl">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 shrink-0 rounded-lg bg-[#151a20] border border-[#343e49] flex items-center justify-center text-white">
                        <i class="fas fa-running"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Belum ada program aktif</h3>
                        <p class="rl-copy text-xs mt-2 max-w-lg">
                            Aktifkan program dari kantong Anda atau pilih program baru dari marketplace untuk mulai menyusun jadwal latihan.
                        </p>
                        <div class="flex flex-wrap items-center gap-2 mt-5">
                            @if($programBag->count() > 0)
                                <a href="#kantong-program" class="rl-btn rl-btn-secondary">
                                    Lihat kantong program
                                </a>
                            @else
                                <a href="{{ route('programs.index') }}" class="rl-btn rl-btn-primary">
                                    Jelajahi program coach
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <!-- Program Bag -->
    <section class="rl-section mb-12" id="kantong-program">
        <div class="rl-section-head">
            <div class="flex items-center gap-3">
                <span class="rl-section-index">02</span>
                <h2 class="rl-section-title">Kantong program</h2>
            </div>
            <span class="rl-meta-label">Belum aktif</span>
        </div>

        @if($programBag->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($programBag as $bag)
                    @php
                        $prog = $bag->program;
                        $coach = $prog->coach;
                    @endphp

                    <article class="rl-card p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <span class="rl-tag
                                    @if(($prog->difficulty ?? 'beginner') === 'beginner') rl-tag--beginner
                                    @elseif(($prog->difficulty ?? 'beginner') === 'intermediate') rl-tag--intermediate
                                    @else rl-tag--advanced @endif">
                                    {{ $prog->difficulty ?? 'Beginner' }}
                                </span>
                                <span class="rl-meta-value font-mono text-[11px]">{{ $prog->duration_weeks ?? 12 }} minggu</span>
                            </div>

                            <h3 class="text-base font-extrabold text-white tracking-[-0.015em] mt-4 line-clamp-2 min-h-[2.8rem]">
                                {{ $prog->title }}
                            </h3>

                            @if($coach)
                                <div class="flex items-center gap-2.5 mt-4 pt-4 border-t border-[#222932]">
                                    <img
                                        src="{{ $coach->avatar ? (str_starts_with($coach->avatar, 'http') ? $coach->avatar : (str_starts_with($coach->avatar, '/storage') ? asset(ltrim($coach->avatar, '/')) : asset('storage/' . $coach->avatar))) : asset('images/profile/17.jpg') }}"
                                        alt="{{ $coach->name }}"
                                        class="rl-avatar w-7 h-7 rounded-md object-cover">
                                    <div class="min-w-0">
                                        <div class="rl-meta-label">Coach</div>
                                        <div class="text-xs font-bold text-white truncate">{{ $coach->name }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <button
                            @click="openStartModal({{ $bag->id }}, '{{ $prog->title }}')"
                            class="rl-btn rl-btn-secondary w-full mt-5">
                            <i class="fas fa-play"></i>
                            Mulai program
                        </button>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rl-empty p-6 max-w-2xl">
                <p class="rl-copy text-xs">
                    Kantong program Anda kosong. Program yang sudah dibeli atau diambil akan muncul di bagian ini sebelum diaktifkan.
                </p>
            </div>
        @endif
    </section>

    <!-- History -->
    <section class="rl-section mb-12" id="riwayat-program">
        <div class="rl-section-head">
            <div class="flex items-center gap-3">
                <span class="rl-section-index">03</span>
                <h2 class="rl-section-title">Riwayat program</h2>
            </div>
            <span class="rl-meta-label">Selesai / non-aktif</span>
        </div>

        @if($historyPrograms->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($historyPrograms as $hist)
                    @php
                        $prog = $hist->program;
                        $coach = $prog->coach;
                    @endphp

                    <article class="rl-card p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <span class="rl-tag {{ $hist->status === 'inactive' ? 'rl-tag--inactive' : 'rl-tag--finished' }}">
                                    {{ $hist->status === 'inactive' ? 'Non-aktif' : 'Selesai' }}
                                </span>
                                <span class="rl-meta-value font-mono text-[11px]">{{ $prog->duration_weeks ?? 12 }} minggu</span>
                            </div>

                            <h3 class="text-base font-extrabold text-white tracking-[-0.015em] mt-4 line-clamp-2 min-h-[2.8rem]">
                                {{ $prog->title }}
                            </h3>

                            @if($coach)
                                <div class="flex items-center gap-2.5 mt-4 pt-4 border-t border-[#222932]">
                                    <img
                                        src="{{ $coach->avatar ? (str_starts_with($coach->avatar, 'http') ? $coach->avatar : (str_starts_with($coach->avatar, '/storage') ? asset(ltrim($coach->avatar, '/')) : asset('storage/' . $coach->avatar))) : asset('images/profile/17.jpg') }}"
                                        alt="{{ $coach->name }}"
                                        class="rl-avatar w-7 h-7 rounded-md object-cover">
                                    <div class="min-w-0">
                                        <div class="rl-meta-label">Coach</div>
                                        <div class="text-xs font-bold text-white truncate">{{ $coach->name }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-5">
                            @if($prog->price == 0)
                                <form action="{{ route('runner.programs.enroll-free', $prog->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rl-btn rl-btn-secondary w-full">
                                        <i class="fas fa-redo"></i>
                                        Ambil gratis lagi
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('marketplace.cart.add', $prog->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rl-btn rl-btn-secondary w-full">
                                        <i class="fas fa-shopping-cart"></i>
                                        Daftar ulang
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rl-empty p-6 max-w-2xl">
                <p class="rl-copy text-xs">Belum ada riwayat program selesai atau program non-aktif.</p>
            </div>
        @endif
    </section>

    <!-- Marketplace Recommendations -->
    <section class="rl-section pb-4">
        <div class="rl-section-head">
            <div class="flex items-center gap-3">
                <span class="rl-section-index">04</span>
                <h2 class="rl-section-title">Pilihan program berikutnya</h2>
            </div>
            <a href="{{ route('marketplace.index') }}" class="rl-link flex items-center gap-2">
                Lihat semua
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        @if($marketPrograms->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($marketPrograms as $prog)
                    @php $coach = $prog->coach; @endphp

                    <article class="rl-card rl-market-card overflow-hidden flex flex-col justify-between group">
                        <a href="{{ route('programs.show', $prog->slug) }}" class="block">
                            <div class="rl-cover relative aspect-[16/10] w-full overflow-hidden bg-[#11151a]">
                                <img src="{{ $prog->image_url }}" alt="{{ $prog->title }}" class="w-full h-full object-cover">

                                <div class="absolute left-3 bottom-3 z-10">
                                    <span class="rl-tag
                                        @if(($prog->difficulty ?? 'beginner') === 'beginner') rl-tag--beginner
                                        @elseif(($prog->difficulty ?? 'beginner') === 'intermediate') rl-tag--intermediate
                                        @else rl-tag--advanced @endif">
                                        {{ $prog->difficulty ?? 'Beginner' }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-4">
                                <h3 class="text-sm font-extrabold text-white tracking-[-0.01em] line-clamp-2 min-h-[2.5rem]">
                                    {{ $prog->title }}
                                </h3>

                                <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-[#222932]">
                                    <div>
                                        <div class="rl-meta-label">Durasi</div>
                                        <div class="text-xs font-bold text-white mt-1">
                                            {{ $prog->duration_weeks ?? 12 }} minggu
                                        </div>
                                    </div>
                                    <div>
                                        <div class="rl-meta-label">Lokasi</div>
                                        <div class="text-xs font-bold text-white mt-1 truncate">
                                            {{ $prog->city->name ?? 'Online' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <div class="p-4 pt-0">
                            <div class="flex items-center justify-between gap-3 mb-4 pt-4 border-t border-[#222932]">
                                @if($coach)
                                    <div class="flex items-center gap-2 min-w-0">
                                        <img
                                            src="{{ $coach->avatar ? (str_starts_with($coach->avatar, 'http') ? $coach->avatar : (str_starts_with($coach->avatar, '/storage') ? asset(ltrim($coach->avatar, '/')) : asset('storage/' . $coach->avatar))) : asset('images/profile/17.jpg') }}"
                                            alt="{{ $coach->name }}"
                                            class="rl-avatar w-6 h-6 rounded-md object-cover">
                                        <span class="text-[11px] text-white font-bold truncate">{{ $coach->name }}</span>
                                    </div>
                                @endif

                                <div class="rl-price shrink-0">
                                    @if($prog->price == 0)
                                        GRATIS
                                    @else
                                        Rp{{ number_format($prog->price, 0, ',', '.') }}
                                    @endif
                                </div>
                            </div>

                            @php
                                $isEnrolled = false;
                                if (auth()->user()) {
                                    $isEnrolled = \App\Models\ProgramEnrollment::where('runner_id', auth()->id())
                                        ->where('program_id', $prog->id)
                                        ->whereIn('status', ['purchased', 'active'])
                                        ->exists();
                                }
                            @endphp

                            @if($isEnrolled)
                                <a href="{{ route('runner.calendar') }}" class="rl-btn rl-btn-secondary w-full">
                                    <i class="fas fa-check"></i>
                                    Sudah diambil
                                </a>
                            @else
                                @if($prog->price == 0)
                                    <form action="{{ route('runner.programs.enroll-free', $prog->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="rl-btn rl-btn-primary w-full">
                                            <i class="fas fa-plus"></i>
                                            Ambil gratis
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('marketplace.cart.add', $prog->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="rl-btn rl-btn-primary w-full">
                                            <i class="fas fa-shopping-cart"></i>
                                            Beli sekarang
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rl-empty p-6">
                <p class="rl-copy text-xs">Belum ada program rekomendasi saat ini.</p>
            </div>
        @endif
    </section>

    <!-- Start Program Modal -->
    <div
        x-show="showStartModal"
        class="rl-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition
        x-cloak
        style="display: none;">
        <div class="rl-modal relative w-full max-w-md p-6" @click.away="closeStartModal()">
            <button
                @click="closeStartModal()"
                class="absolute top-4 right-4 w-9 h-9 rounded-md border border-[#37414c] bg-[#151a20] text-white hover:bg-[#1d232a] transition"
                aria-label="Tutup modal">
                <i class="fas fa-times"></i>
            </button>

            <div class="rl-kicker mb-4">Aktivasi program</div>
            <h3 class="text-xl font-extrabold text-white tracking-[-0.025em] pr-10">Mulai program latihan</h3>
            <p class="rl-copy text-xs mt-2 mb-6">
                Anda akan mengaktifkan <strong class="text-white" x-text="selectedProgramTitle"></strong>.
                Tentukan tanggal mulai untuk membuat jadwal latihan.
            </p>

            <form @submit.prevent="submitStartProgram()">
                <div class="mb-6">
                    <label class="block mb-2">Tanggal mulai</label>
                    <input type="date" x-model="startDate" required class="rl-input">
                    <p class="rl-copy text-[11px] mt-2">
                        Memulai pada hari Senin membantu menjaga struktur minggu latihan tetap konsisten.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="closeStartModal()" class="rl-btn rl-btn-secondary flex-1">
                        Batal
                    </button>
                    <button type="submit" :disabled="loading" class="rl-btn rl-btn-primary flex-1 disabled:opacity-50">
                        <span x-show="!loading">Aktifkan program</span>
                        <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Program Modal -->
    <div
        x-show="showResetModal"
        class="rl-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition
        x-cloak
        style="display: none;">
        <div class="rl-modal relative w-full max-w-md p-6" @click.away="closeResetModal()">
            <button
                @click="closeResetModal()"
                class="absolute top-4 right-4 w-9 h-9 rounded-md border border-[#37414c] bg-[#151a20] text-white hover:bg-[#1d232a] transition"
                aria-label="Tutup modal">
                <i class="fas fa-times"></i>
            </button>

            <div class="w-10 h-10 rounded-md bg-[#2b1117] border border-[#71303d] flex items-center justify-center text-[#fecdd3] mb-5">
                <i class="fas fa-exclamation-triangle"></i>
            </div>

            <h3 class="text-xl font-extrabold text-white tracking-[-0.025em] pr-10">Reset program latihan?</h3>
            <p class="rl-copy text-xs mt-2 mb-6 leading-relaxed">
                Program <strong class="text-white" x-text="selectedProgramTitle"></strong> akan dikembalikan ke Kantong Program.
                Seluruh catatan tracking latihan untuk program ini akan
                <span class="text-[#fecdd3] font-bold">dihapus permanen</span>.
            </p>

            <div class="flex items-center gap-2">
                <button type="button" @click="closeResetModal()" class="rl-btn rl-btn-secondary flex-1">
                    Batal
                </button>
                <button
                    type="button"
                    @click="submitResetProgram()"
                    :disabled="loading"
                    class="rl-btn rl-btn-danger flex-1 disabled:opacity-50">
                    <span x-show="!loading">Reset program</span>
                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function programsManager() {
        return {
            showStartModal: false,
            showResetModal: false,
            loading: false,
            selectedEnrollmentId: null,
            selectedProgramTitle: '',
            startDate: new Date().toISOString().split('T')[0],

            openStartModal(enrollmentId, title) {
                this.selectedEnrollmentId = enrollmentId;
                this.selectedProgramTitle = title;
                this.showStartModal = true;
            },

            closeStartModal() {
                this.showStartModal = false;
                this.selectedEnrollmentId = null;
                this.selectedProgramTitle = '';
            },

            async submitStartProgram() {
                this.loading = true;
                try {
                    const response = await fetch("{{ route('runner.calendar.apply-program') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            enrollment_id: this.selectedEnrollmentId,
                            start_date: this.startDate
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        alert('Program berhasil diaktifkan! Mengalihkan ke Kalender...');
                        window.location.href = "{{ route('runner.calendar') }}";
                    } else {
                        alert(data.message || 'Gagal mengaktifkan program.');
                    }
                } catch (error) {
                    console.error('Error applying program:', error);
                    alert('Terjadi kesalahan sistem. Silakan coba lagi.');
                } finally {
                    this.loading = false;
                    this.closeStartModal();
                }
            },

            confirmReset(enrollmentId, title) {
                this.selectedEnrollmentId = enrollmentId;
                this.selectedProgramTitle = title;
                this.showResetModal = true;
            },

            closeResetModal() {
                this.showResetModal = false;
                this.selectedEnrollmentId = null;
                this.selectedProgramTitle = '';
            },

            async submitResetProgram() {
                this.loading = true;
                try {
                    const response = await fetch("{{ route('runner.calendar.reset-plan') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            enrollment_id: this.selectedEnrollmentId
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        alert('Program berhasil direset dan dipindahkan ke Kantong Program.');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal mereset program.');
                    }
                } catch (error) {
                    console.error('Error resetting program:', error);
                    alert('Terjadi kesalahan sistem. Silakan coba lagi.');
                } finally {
                    this.loading = false;
                    this.closeResetModal();
                }
            }
        }
    }
</script>
@endsection
