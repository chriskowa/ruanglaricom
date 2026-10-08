@extends('layouts.pacerhub')

@section('title', 'Report Event | ' . $event->name)

@push('styles')
<meta name="robots" content="noindex,nofollow,noarchive">
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

    /* Report Theme Variables */
    :root {
        --rep-canvas: #020617;
        --rep-card: #0f172a;
        --rep-card-subtle: #0b1120;
        --rep-border: #1e293b;
        --rep-border-hover: #334155;
        --rep-head: #ffffff;
        --rep-body: #cbd5e1;
        --rep-muted: #94a3b8;
        --rep-input-bg: #020617;
        --rep-input-border: #1e293b;
        --rep-input-text: #ffffff;
        --rep-th-bg: #0f172a;
        --rep-th-text: #94a3b8;
        --rep-tr-hover: rgba(30, 41, 59, 0.45);
        --rep-divider: #1e293b;
        --rep-btn-bg: #1e293b;
        --rep-btn-hover: #334155;
        --rep-btn-text: #f1f5f9;
        --rep-btn-border: #334155;
    }

    body.theme-light,
    .theme-light {
        --rep-canvas: #f8fafc;
        --rep-card: #ffffff;
        --rep-card-subtle: #f1f5f9;
        --rep-border: #e2e8f0;
        --rep-border-hover: #cbd5e1;
        --rep-head: #0f172a;
        --rep-body: #334155;
        --rep-muted: #64748b;
        --rep-input-bg: #ffffff;
        --rep-input-border: #cbd5e1;
        --rep-input-text: #0f172a;
        --rep-th-bg: #f8fafc;
        --rep-th-text: #475569;
        --rep-tr-hover: #f1f5f9;
        --rep-divider: #e2e8f0;
        --rep-btn-bg: #f8fafc;
        --rep-btn-hover: #f1f5f9;
        --rep-btn-text: #0f172a;
        --rep-btn-border: #cbd5e1;
    }

    /* Container Theming */
    #report-page-container {
        background-color: var(--rep-canvas);
        color: var(--rep-body);
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    body.theme-light {
        background-color: #f8fafc !important;
        color: #0f172a !important;
    }
    body.theme-light #report-page-container {
        background-color: #f8fafc !important;
        color: #334155 !important;
    }
    body.theme-light .bg-card,
    body.theme-light .rep-card {
        background-color: #ffffff !important;
        border-color: #e2e8f0 !important;
        color: #334155 !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
    }
    body.theme-light .rep-card-subtle,
    body.theme-light .bg-slate-900\/30,
    body.theme-light .bg-slate-900\/40,
    body.theme-light .bg-slate-900\/50,
    body.theme-light .bg-slate-950\/50,
    body.theme-light .bg-slate-950\/60 {
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
    }
    body.theme-light input,
    body.theme-light select,
    body.theme-light textarea {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }
    body.theme-light input::placeholder {
        color: #94a3b8 !important;
    }
    body.theme-light .border-slate-700,
    body.theme-light .border-slate-800,
    body.theme-light .border-slate-700\/80,
    body.theme-light .border-slate-700\/50,
    body.theme-light .border-slate-800\/60 {
        border-color: #e2e8f0 !important;
    }
    body.theme-light .divide-slate-800 > :not([hidden]) ~ :not([hidden]) {
        border-color: #e2e8f0 !important;
    }
    body.theme-light .text-white {
        color: #0f172a !important;
    }
    body.theme-light .text-slate-200,
    body.theme-light .text-slate-300 {
        color: #334155 !important;
    }
    body.theme-light .text-slate-400 {
        color: #64748b !important;
    }
    body.theme-light thead {
        background-color: #f8fafc !important;
        color: #475569 !important;
    }
    body.theme-light tr:hover {
        background-color: #f1f5f9 !important;
    }
    body.theme-light .bg-slate-800 {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }
    body.theme-light .bg-slate-800:hover {
        background-color: #e2e8f0 !important;
    }
    body.theme-light .bg-slate-900 {
        background-color: #ffffff !important;
        border-color: #e2e8f0 !important;
    }
    body.theme-light .bg-slate-950 {
        background-color: #ffffff !important;
    }

    /* Modal surfaces in light mode */
    body.theme-light #doorprizeModalCard,
    body.theme-light #edit-modal > div,
    body.theme-light #detail-modal > div,
    body.theme-light #qrScanModal .relative,
    body.theme-light #coupon-report-modal > div,
    body.theme-light #activityLogModal .pointer-events-auto {
        background-color: #ffffff !important;
        border-color: #e2e8f0 !important;
        color: #0f172a !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    }
    body.theme-light #doorprizeModalCard .border-b,
    body.theme-light #edit-modal .border-b,
    body.theme-light #detail-modal .border-b,
    body.theme-light #qrScanModal .border-b,
    body.theme-light #coupon-report-modal .border-b,
    body.theme-light #activityLogModal .border-b {
        border-color: #e2e8f0 !important;
    }

    #doorprizeModalCard:fullscreen {
        background-color: #020617 !important;
        padding: 2.5rem !important;
        width: 100vw !important;
        height: 100vh !important;
        max-width: none !important;
        max-height: none !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        box-sizing: border-box !important;
    }
    #doorprizeModalCard:fullscreen .grid {
        height: calc(100vh - 8rem);
    }
    #doorprizeModalCard:fullscreen #doorprizeHistorySidebar {
        display: none !important;
    }
    #doorprizeModalCard:fullscreen .grid > div.lg\:col-span-2 {
        grid-column: span 3 / span 3 !important;
    }
    #doorprizeModalCard:fullscreen #doorprizeDrawBoard {
        flex: 1;
        justify-content: center;
        min-height: 380px;
    }

    /* Table View Modes (List Compact vs Stacked Cards) */
    .view-mode-list thead {
        display: table-header-group !important;
    }
    .view-mode-list tr.participant-row {
        display: table-row !important;
        background-color: transparent !important;
        margin-bottom: 0 !important;
        padding: 0 !important;
        border-radius: 0 !important;
        border-bottom: 1px solid var(--rep-divider) !important;
    }
    .view-mode-list tr.participant-row td {
        display: table-cell !important;
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        white-space: nowrap !important;
    }
    .view-mode-list tr.participant-row td .mobile-label {
        display: none !important;
    }
    .view-mode-list tr.participant-row td .cell-value {
        text-align: left !important;
    }

    .view-mode-stacked thead {
        display: none !important;
    }
    .view-mode-stacked tr.participant-row {
        display: block !important;
        background-color: var(--rep-card-subtle) !important;
        margin-bottom: 0.75rem !important;
        padding: 0.875rem !important;
        border-radius: 0.5rem !important;
        border: 1px solid var(--rep-border) !important;
    }
    .view-mode-stacked tr.participant-row td {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        padding: 0.375rem 0 !important;
        border: none !important;
        white-space: normal !important;
    }
    .view-mode-stacked tr.participant-row td .mobile-label {
        display: inline-block !important;
    }
    .view-mode-stacked tr.participant-row td .cell-value {
        text-align: right !important;
    }
</style>
@endpush

@section('content')
<div id="report-page-container" class="min-h-screen py-6 sm:py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between pb-5 border-b border-slate-800">
            <div>
                <div class="text-xs text-slate-400 font-mono tracking-wider">/report/{{ $event->id }}</div>
                <h1 class="font-brand-heading text-2xl sm:text-3xl font-extrabold text-white mt-1">
                    {{ $event->name }}
                </h1>
                <div class="text-xs sm:text-sm text-slate-300 mt-1 flex items-center gap-2">
                    <span class="font-mono text-slate-400">#{{ $event->id }}</span>
                    @if($event->start_at)
                        <span class="text-slate-600">•</span>
                        <span>{{ $event->start_at->translatedFormat('d M Y, H:i') }} WIB</span>
                    @endif
                    <span class="text-slate-600">•</span>
                    <span class="text-slate-400">Laporan Internal Panitia</span>
                </div>
            </div>

            <!-- Header Actions: Theme Toggle -->
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <button type="button" id="theme-toggle-btn" onclick="toggleReportTheme()" class="px-3 py-1.5 rounded-md bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs font-semibold text-slate-200 transition flex items-center gap-1.5 shadow-sm" title="Ganti Mode Tampilan (Dark / Light)">
                    <i id="theme-toggle-icon" class="fa-solid fa-sun text-amber-400"></i>
                    <span id="theme-toggle-label">Mode Terang</span>
                </button>
            </div>
        </div>

        <!-- 6 Key Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
            <div class="bg-card border border-slate-800 rounded-lg p-4">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Slot</div>
                <div id="stat-total" class="text-2xl font-extrabold font-mono text-white tabular-nums mt-1">
                    {{ is_string($report['total_slots'] ?? null) ? $report['total_slots'] : number_format((int) ($report['total_slots'] ?? 0)) }}
                </div>
            </div>
            <div class="bg-card border border-slate-800 rounded-lg p-4">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sold (Paid)</div>
                <div id="stat-sold" class="text-2xl font-extrabold font-mono text-white tabular-nums mt-1">
                    {{ number_format((int) ($report['sold_slots'] ?? 0)) }}
                </div>
            </div>
            <div class="bg-card border border-slate-800 rounded-lg p-4">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending</div>
                <div id="stat-pending" class="text-2xl font-extrabold font-mono text-white tabular-nums mt-1">
                    {{ number_format((int) ($report['pending_slots'] ?? 0)) }}
                </div>
            </div>
            <div class="bg-card border border-slate-800 rounded-lg p-4">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Slot</div>
                <div id="stat-remaining" class="text-2xl font-extrabold font-mono text-white tabular-nums mt-1">
                    {{ is_string($report['remaining_slots'] ?? null) ? $report['remaining_slots'] : number_format((int) ($report['remaining_slots'] ?? 0)) }}
                </div>
                @if(($report['show_warning'] ?? false) === true)
                    <div class="mt-1 text-[11px] text-amber-400 font-semibold">Sisa slot &lt; 10%</div>
                @endif
            </div>
            <div class="bg-card border border-slate-800 rounded-lg p-4">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Picked Up</div>
                <div id="stat-picked" class="text-2xl font-extrabold font-mono text-emerald-400 tabular-nums mt-1">
                    {{ number_format((int) ($report['pickup']['picked_up'] ?? 0)) }}
                </div>
            </div>
            <div class="bg-card border border-slate-800 rounded-lg p-4">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Belum Diambil</div>
                <div id="stat-unpicked" class="text-2xl font-extrabold font-mono text-slate-400 tabular-nums mt-1">
                    {{ number_format((int) ($report['pickup']['not_picked_up'] ?? 0)) }}
                </div>
            </div>
        </div>

        <!-- Sales Chart Card -->
        <div class="bg-card border border-slate-800 rounded-lg p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-slate-800">
                <div>
                    <h2 class="font-brand-heading text-base font-bold text-white">Penjualan Slot</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Tren transaksi paid vs pending</p>
                </div>
                <form id="sales-filters" class="flex flex-wrap items-end gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Periode</label>
                        <select id="sales_group" name="sales_group" class="mt-1 rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                            <option value="day" @selected(($filters['sales_group'] ?? 'day') === 'day')>Harian</option>
                            <option value="month" @selected(($filters['sales_group'] ?? 'day') === 'month')>Bulanan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mulai</label>
                        <input id="sales_start_date" type="date" name="sales_start_date" value="{{ $filters['sales_start_date'] ?? '' }}" class="mt-1 rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Akhir</label>
                        <input id="sales_end_date" type="date" name="sales_end_date" value="{{ $filters['sales_end_date'] ?? '' }}" class="mt-1 rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                    </div>
                    <button type="submit" class="px-3.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs border border-slate-700 transition">Terapkan</button>
                    <button type="button" id="sales-reset" class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white font-semibold text-xs border border-slate-800 transition">Reset</button>
                </form>
            </div>
            <div class="mt-4 grid grid-cols-1 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-3 border border-slate-800 rounded-lg bg-slate-950/40 p-3 min-h-[200px]">
                    <canvas id="salesChart" height="110"></canvas>
                </div>
                <div class="border border-slate-800 rounded-lg bg-slate-950/40 p-4">
                    <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Insight Penjualan</div>
                    <div id="sales-insights" class="mt-3 space-y-2 text-xs text-slate-300"></div>
                </div>
            </div>
        </div>

        <!-- Jersey Breakdown Card -->
        <div class="bg-card border border-slate-800 rounded-lg p-5">
            <div class="pb-3 border-b border-slate-800">
                <h2 class="font-brand-heading text-base font-bold text-white">Jersey Breakdown</h2>
                <p class="text-xs text-slate-400 mt-0.5">Stok, terpakai (paid only), dan sisa per ukuran</p>
            </div>
            @php
                $jerseyCounts = $report['jersey_sizes'] ?? [];
                $jerseyStockQuotas = $report['jersey_stock_quotas'] ?? [];
                $jerseySizes = ['XXS','XS','S','M','L','XL','2XL','3XL','4XL','5XL'];
                $jerseyActiveSizes = array_filter($jerseySizes, function($s) use ($jerseyCounts, $jerseyStockQuotas) {
                    $used = (int) ($jerseyCounts[$s] ?? $jerseyCounts[strtolower($s)] ?? $jerseyCounts[strtoupper($s)] ?? 0);
                    if ($s === '2XL') {
                        $used += (int) ($jerseyCounts['XXL'] ?? $jerseyCounts['xxl'] ?? 0);
                    } elseif ($s === '3XL') {
                        $used += (int) ($jerseyCounts['XXXL'] ?? $jerseyCounts['xxxl'] ?? 0);
                    }
                    return $used > 0 || isset($jerseyStockQuotas[$s]);
                });
                if (empty($jerseyActiveSizes)) $jerseyActiveSizes = ['XS','S','M','L','XL','2XL','3XL'];
            @endphp

            <div class="mt-3 hidden sm:grid grid-cols-4 gap-2 px-3 mb-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ukuran</span>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Stok</span>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Terpakai</span>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-right">Sisa</span>
            </div>
            <div class="mt-1 grid grid-cols-2 sm:grid-cols-1 gap-2">
                @foreach($jerseyActiveSizes as $size)
                    @php
                        $used  = (int) ($jerseyCounts[$size] ?? $jerseyCounts[strtolower($size)] ?? $jerseyCounts[strtoupper($size)] ?? 0);
                        if ($size === '2XL') {
                            $used += (int) ($jerseyCounts['XXL'] ?? $jerseyCounts['xxl'] ?? 0);
                        } elseif ($size === '3XL') {
                            $used += (int) ($jerseyCounts['XXXL'] ?? $jerseyCounts['xxxl'] ?? 0);
                        }
                        $quota = isset($jerseyStockQuotas[$size]) ? (int) $jerseyStockQuotas[$size] : null;
                        $sisa  = $quota !== null ? max(0, $quota - $used) : null;
                    @endphp
                    <div class="rounded-md border {{ $sisa !== null && $sisa == 0 ? 'border-rose-500/30 bg-rose-950/20' : ($sisa !== null && $sisa <= 5 ? 'border-amber-500/30 bg-amber-950/20' : 'border-slate-800 bg-slate-950/30') }} px-3 py-2">
                        <div class="sm:hidden text-xs text-slate-400 font-bold mb-1">{{ $size }}</div>
                        <div class="sm:grid sm:grid-cols-4 sm:gap-2 sm:items-center flex items-center justify-between">
                            <div class="hidden sm:block text-xs font-bold text-slate-200">{{ $size }}</div>
                            <div class="text-right">
                                <div class="text-[10px] text-slate-400 sm:hidden">Stok</div>
                                <div class="text-xs font-mono text-slate-400" id="stat-jersey-quota-{{ $size }}">{{ $quota !== null ? number_format($quota) : '∞' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] text-slate-400 sm:hidden">Terpakai</div>
                                <div class="text-xs font-mono font-bold text-white" id="stat-jersey-{{ $size }}">{{ number_format($used) }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] text-slate-400 sm:hidden">Sisa</div>
                                <div class="text-xs font-mono font-bold {{ $sisa !== null && $sisa == 0 ? 'text-rose-400' : ($sisa !== null && $sisa <= 5 ? 'text-amber-400' : 'text-emerald-400') }}" id="stat-jersey-sisa-{{ $size }}">
                                    {{ $sisa !== null ? $sisa : '∞' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @php
                $totalUsed = 0;
                foreach ($jerseyActiveSizes as $s) {
                    $cnt = (int) ($jerseyCounts[$s] ?? $jerseyCounts[strtolower($s)] ?? $jerseyCounts[strtoupper($s)] ?? 0);
                    if ($s === '2XL') {
                        $cnt += (int) ($jerseyCounts['XXL'] ?? $jerseyCounts['xxl'] ?? 0);
                    } elseif ($s === '3XL') {
                        $cnt += (int) ($jerseyCounts['XXXL'] ?? $jerseyCounts['xxxl'] ?? 0);
                    }
                    $totalUsed += $cnt;
                }
                $totalQuota = !empty($jerseyStockQuotas) ? array_sum($jerseyStockQuotas) : null;
                $totalSisa  = $totalQuota !== null ? max(0, $totalQuota - $totalUsed) : null;
            @endphp
            <div class="mt-3 pt-3 border-t border-slate-800 grid grid-cols-4 gap-2 px-3 items-center">
                <span class="text-xs font-bold text-slate-400 uppercase">TOTAL</span>
                <span id="stat-jersey-total-quota" class="text-right text-xs font-mono font-bold text-slate-300">{{ $totalQuota !== null ? number_format($totalQuota) : '∞' }}</span>
                <span id="stat-jersey-total-used" class="text-right text-xs font-mono font-bold text-white">{{ number_format($totalUsed) }}</span>
                <span id="stat-jersey-total-sisa" class="text-right text-xs font-mono font-bold text-emerald-400">{{ $totalSisa !== null ? $totalSisa : '∞' }}</span>
            </div>
        </div>

        <!-- Participants Section -->
        <div class="bg-card border border-slate-800 rounded-lg p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div>
                        <h2 class="font-brand-heading text-base font-bold text-white">Data Peserta</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Filter dinamis • Pagination server-side</p>
                    </div>
                    <button type="button" id="toggle-filters-btn" onclick="toggleReportFilters()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold transition border border-slate-800">
                        <i id="toggle-filters-icon" class="fa-solid fa-chevron-up text-xs"></i>
                        <span id="toggle-filters-text">Sembunyikan Filter</span>
                    </button>
                </div>
                <div id="report-loading" class="hidden items-center gap-2 text-xs text-slate-400">
                    <span class="loader"></span>
                    <span>Memuat data...</span>
                </div>
            </div>

            <!-- Filter Form -->
            <form id="report-filters" class="mt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-300">Pencarian</label>
                    <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, email, HP, BIB, ID..."
                        class="mt-1 w-full rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300">Status Pembayaran</label>
                    <select name="payment_status" class="mt-1 w-full rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        @php
                            $paymentStatus = $filters['payment_status'] ?? 'all';
                            $paymentOptions = ['all' => 'Semua Status', 'pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'expired' => 'Expired', 'cod' => 'COD'];
                        @endphp
                        @foreach($paymentOptions as $val => $label)
                            <option value="{{ $val }}" @selected($paymentStatus === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300">Status Pengambilan</label>
                    <select name="is_picked_up" class="mt-1 w-full rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        <option value="" @selected(($filters['is_picked_up'] ?? '') === '')>Semua Pickup</option>
                        <option value="0" @selected(($filters['is_picked_up'] ?? '') === '0')>Belum Diambil</option>
                        <option value="1" @selected(($filters['is_picked_up'] ?? '') === '1')>Sudah Diambil</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300">Jenis Kelamin</label>
                    <select name="gender" class="mt-1 w-full rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        <option value="" @selected(($filters['gender'] ?? '') === '')>Semua Gender</option>
                        <option value="male" @selected(($filters['gender'] ?? '') === 'male')>Laki-laki (Male)</option>
                        <option value="female" @selected(($filters['gender'] ?? '') === 'female')>Perempuan (Female)</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300">Kategori</label>
                    <select name="category_id" class="mt-1 w-full rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        <option value="">Semua Kategori</option>
                        @foreach($event->categories as $cat)
                            <option value="{{ $cat->id }}" @selected((int) ($filters['category_id'] ?? 0) === (int) $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="flex justify-between items-center">
                        <label class="text-xs font-semibold text-slate-300">Kupon</label>
                        <button type="button" id="btn-show-coupon-report" class="text-[10px] text-sky-400 hover:underline hidden" onclick="triggerManualCouponReport()">
                            Lihat Laporan
                        </button>
                    </div>
                    <select name="coupon_id" class="mt-1 w-full rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        <option value="" @selected(($filters['coupon_id'] ?? '') === '')>Semua Kupon</option>
                        <option value="without" @selected(($filters['coupon_id'] ?? '') === 'without')>Tanpa Kupon</option>
                        <option value="with" @selected(($filters['coupon_id'] ?? '') === 'with')>Dengan Kupon (Apa Saja)</option>
                        @foreach($coupons as $coupon)
                            <option value="{{ $coupon->id }}" @selected((string)($filters['coupon_id'] ?? '') === (string)$coupon->id)>{{ $coupon->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300">Add-on</label>
                    <select name="addon" class="mt-1 w-full rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        <option value="" @selected(($filters['addon'] ?? '') === '')>Semua Add-on</option>
                        <option value="with" @selected(($filters['addon'] ?? '') === 'with')>Ada Add-on</option>
                        <option value="without" @selected(($filters['addon'] ?? '') === 'without')>Tanpa Add-on</option>
                        @if(!empty($event->addons) && (is_array($event->addons) || is_object($event->addons)))
                            @foreach($event->addons as $addon)
                                @php 
                                    $addonName = is_array($addon) ? ($addon['name'] ?? null) : (is_object($addon) ? ($addon->name ?? ($addon['name'] ?? null)) : $addon); 
                                @endphp
                                @if($addonName)
                                    <option value="{{ $addonName }}" @selected(($filters['addon'] ?? '') === $addonName)>{{ $addonName }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300">Ukuran Jersey</label>
                    <select name="jersey_size" class="mt-1 w-full rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        <option value="" @selected(($filters['jersey_size'] ?? '') === '')>Semua Ukuran</option>
                        @foreach(['XXS','XS','S','M','L','XL','2XL','3XL','4XL','5XL'] as $jsz)
                            <option value="{{ $jsz }}" @selected(($filters['jersey_size'] ?? '') === $jsz)>{{ $jsz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300">Kelompok Umur</label>
                    <select name="age_group" class="mt-1 w-full rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        <option value="" @selected(($filters['age_group'] ?? '') === '')>Semua Kelompok</option>
                        <option value="Umum" @selected(($filters['age_group'] ?? '') === 'Umum')>Umum (&lt; 40)</option>
                        <option value="Master" @selected(($filters['age_group'] ?? '') === 'Master')>Master (40-44)</option>
                        <option value="Master 45+" @selected(($filters['age_group'] ?? '') === 'Master 45+')>Master 45+ (45-49)</option>
                        <option value="50+" @selected(($filters['age_group'] ?? '') === '50+')>50+ (&gt;= 50)</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300">Umur Min / Max</label>
                    <div class="flex gap-2">
                        <input type="number" name="min_age" value="{{ $filters['min_age'] ?? '' }}" placeholder="Min" min="1" max="150" class="mt-1 w-1/2 rounded-md bg-slate-950 border border-slate-800 px-2 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        <input type="number" name="max_age" value="{{ $filters['max_age'] ?? '' }}" placeholder="Max" min="1" max="150" class="mt-1 w-1/2 rounded-md bg-slate-950 border border-slate-800 px-2 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300">Rentang Tanggal</label>
                    <div class="flex gap-2">
                        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="mt-1 w-1/2 rounded-md bg-slate-950 border border-slate-800 px-2 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="mt-1 w-1/2 rounded-md bg-slate-950 border border-slate-800 px-2 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300">Per Halaman</label>
                    <select name="per_page" class="mt-1 w-full rounded-md bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-white outline-none focus:border-slate-600">
                        @foreach([10,25,50,100] as $pp)
                            <option value="{{ $pp }}" @selected((int) ($filters['per_page'] ?? 25) === $pp)>{{ $pp }} data</option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons Row -->
                <div class="sm:col-span-2 md:col-span-3 lg:col-span-4 flex flex-wrap items-center justify-between gap-2 mt-3 pt-3 border-t border-slate-800">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" class="px-3.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs border border-slate-700 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-filter text-xs"></i>
                            <span>Terapkan Filter</span>
                        </button>
                        <button id="report-reset" type="button" class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-slate-300 font-semibold text-xs transition border border-slate-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                            <span>Reset</span>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="openQrScanModal()" class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800 text-xs font-semibold flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-qrcode text-xs text-slate-300"></i>
                            <span>Scan QR</span>
                        </button>
                        <button type="button" onclick="openActivityLogModal()" class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800 text-xs font-semibold flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-clock-rotate-left text-xs text-slate-300"></i>
                            <span>Log Aktivitas</span>
                            <span id="activity-log-badge" class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-200 text-[10px] font-mono font-bold">0</span>
                        </button>
                        <button type="button" onclick="openDoorprizeModal()" class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800 text-xs font-semibold flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-gift text-xs text-slate-300"></i>
                            <span>Doorprize</span>
                        </button>

                        <div class="h-4 w-px bg-slate-800 mx-1 hidden sm:block"></div>

                        <a id="export-csv-btn" href="#" onclick="window.location.href=getExportUrl('csv'); return false;" class="px-3 py-1.5 rounded-md bg-emerald-700 hover:bg-emerald-600 text-white font-semibold text-xs transition flex items-center gap-1.5 border border-emerald-600">
                            <i class="fa-solid fa-file-csv text-xs"></i>
                            <span>CSV</span>
                        </a>
                        <a id="export-xlsx-btn" href="#" onclick="window.location.href=getExportUrl('xlsx'); return false;" class="px-3 py-1.5 rounded-md bg-emerald-700 hover:bg-emerald-600 text-white font-semibold text-xs transition flex items-center gap-1.5 border border-emerald-600">
                            <i class="fa-solid fa-file-excel text-xs"></i>
                            <span>XLSX</span>
                        </a>
                    </div>
                </div>
            </form>

            <!-- Sticky Quick Search & View Mode Switcher -->
            <div class="mt-4 sticky top-2 z-20 bg-slate-900 border border-slate-800 p-2.5 rounded-lg shadow-md flex flex-wrap sm:flex-nowrap items-center gap-2">
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" id="quick-search-input" value="{{ $filters['search'] ?? '' }}" placeholder="Cari cepat (Nama, BIB, Email, Telp, ID)..." 
                        class="w-full bg-slate-950 border border-slate-800 rounded-md pl-9 pr-8 py-2 text-xs font-medium text-white placeholder-slate-500 outline-none focus:border-slate-600 transition-colors">
                    <button type="button" id="quick-search-clear" onclick="clearQuickSearch()" class="{{ !empty($filters['search']) ? '' : 'hidden' }} absolute right-2.5 top-2.5 text-slate-400 hover:text-white text-[10px] font-bold bg-slate-800 rounded w-4 h-4 flex items-center justify-center transition">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                    </button>
                </div>

                <!-- View Mode Switcher -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-md border border-slate-800 shrink-0">
                    <button type="button" id="btn-view-mode-list" onclick="setTableViewMode('list')" class="px-2.5 py-1 rounded-md text-xs font-semibold transition flex items-center gap-1.5 bg-slate-800 text-white" title="Tampilan List Ringkas">
                        <i class="fa-solid fa-list-ul text-xs"></i>
                        <span class="text-[11px] uppercase tracking-wider">List</span>
                    </button>
                    <button type="button" id="btn-view-mode-stacked" onclick="setTableViewMode('stacked')" class="px-2.5 py-1 rounded-md text-xs font-semibold transition flex items-center gap-1.5 text-slate-400 hover:text-white" title="Tampilan Kartu">
                        <i class="fa-solid fa-table-cells-large text-xs"></i>
                        <span class="text-[11px] uppercase tracking-wider">Kartu</span>
                    </button>
                </div>
            </div>

            <!-- Participants Table -->
            <div id="participants-table-wrapper" class="mt-4 overflow-x-auto border border-slate-800 rounded-lg view-mode-list">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-900 text-slate-300 border-b border-slate-800">
                        <tr>
                            <th class="text-left font-semibold px-3 py-2.5">Nama</th>
                            <th class="text-left font-semibold px-3 py-2.5">Email</th>
                            <th class="text-left font-semibold px-3 py-2.5">No Telp</th>
                            <th class="text-left font-semibold px-3 py-2.5">Jersey</th>
                            <th class="text-left font-semibold px-3 py-2.5">No BIB</th>
                            <th class="text-left font-semibold px-3 py-2.5">Addons</th>
                            <th class="text-left font-semibold px-3 py-2.5">Tanggal Registrasi</th>
                            <th class="text-left font-semibold px-3 py-2.5">Status Pembayaran</th>
                            <th class="text-left font-semibold px-3 py-2.5 text-center">Picked Up</th>
                            <th class="text-left font-semibold px-3 py-2.5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="participants-tbody" class="divide-y divide-slate-800">
                        @foreach($participants as $p)
                            <tr class="participant-row hover:bg-slate-900/40 cursor-pointer" onclick="if(!event.target.closest('button') && !event.target.closest('a') && !event.target.closest('select') && !event.target.closest('.no-click')) openDetailModalFromRow(this)" data-json="{{ json_encode($p) }}">
                                <td class="px-3 py-2 font-semibold text-white">
                                    <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Nama</span>
                                    <span class="cell-value text-right md:text-left font-bold text-white text-xs sm:text-sm">{{ $p->name }}</span>
                                </td>
                                <td class="px-3 py-2 text-slate-200">
                                    <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Email</span>
                                    <span class="cell-value text-right md:text-left break-all text-xs text-slate-300">{{ $p->email }}</span>
                                </td>
                                <td class="px-3 py-2 text-slate-300">
                                    <span class="mobile-label text-slate-400 font-bold text-xs uppercase">No Telp</span>
                                    <span class="cell-value text-right md:text-left font-mono text-xs">{{ $p->phone ?? '-' }}</span>
                                </td>
                                <td class="px-3 py-2 text-slate-300">
                                    <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Jersey</span>
                                    <span class="cell-value text-right md:text-left font-mono font-bold text-xs text-slate-200">{{ $p->jersey_size ?? '-' }}</span>
                                </td>
                                <td class="px-3 py-2 text-slate-300">
                                    <span class="mobile-label text-slate-400 font-bold text-xs uppercase">No BIB</span>
                                    @php
                                        $bib = $p->bib_number;
                                        if ($bib && strpos($bib, '-') !== false) {
                                            $parts = explode('-', $bib);
                                            $bib = end($parts);
                                        }
                                    @endphp
                                    <span class="cell-value text-right md:text-left font-mono font-bold text-xs text-white">#{{ $bib ?? '-' }}</span>
                                </td>
                                <td class="px-3 py-2 text-slate-200">
                                    <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Addons</span>
                                    @php $addons = is_array($p->addons) ? $p->addons : []; @endphp
                                    <span class="cell-value text-right md:text-left">
                                        @if(count($addons) > 0)
                                            <span class="inline-flex flex-wrap gap-1 justify-end md:justify-start">
                                                @foreach($addons as $a)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-200">
                                                        {{ is_array($a) ? ($a['name'] ?? '-') : ($a->name ?? '-') }}
                                                    </span>
                                                @endforeach
                                            </span>
                                        @else
                                            <span class="text-slate-500">-</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-slate-300">
                                    <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Tgl Reg</span>
                                    <span class="cell-value text-right md:text-left text-xs font-mono text-slate-400">{{ \Illuminate\Support\Carbon::parse($p->created_at)->format('d/m/y H:i') }}</span>
                                </td>
                                <td class="px-3 py-2 no-click">
                                    <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Status</span>
                                    <div class="cell-value text-right md:text-left">
                                        <select onchange="updatePaymentStatus(this, {{ $p->id }}, this.value)" class="bg-slate-950 border border-slate-800 text-xs font-semibold rounded-md px-2 py-1 text-white focus:outline-none focus:border-slate-600 cursor-pointer">
                                            <option value="paid" @selected($p->payment_status === 'paid')>PAID</option>
                                            <option value="pending" @selected($p->payment_status === 'pending')>PENDING</option>
                                            <option value="cod" @selected($p->payment_status === 'cod')>COD</option>
                                            <option value="failed" @selected($p->payment_status === 'failed')>FAILED</option>
                                            <option value="expired" @selected($p->payment_status === 'expired')>EXPIRED</option>
                                            <option value="cancelled" @selected($p->payment_status === 'cancelled')>CANCELLED</option>
                                        </select>
                                        @if($p->coupon_code)
                                            <div class="mt-0.5 text-[10px] font-mono text-amber-400" title="Kupon dipakai">
                                                Kupon: {{ $p->coupon_code }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-center no-click">
                                    <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Picked Up</span>
                                    <div class="cell-value text-right md:text-center">
                                        <button type="button" 
                                            onclick="togglePickup(this, {{ $p->id }}, {{ $p->is_picked_up ? 'true' : 'false' }})"
                                            class="px-2 py-1 text-xs rounded-md font-semibold border transition duration-150 {{ $p->is_picked_up ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/50' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700' }}">
                                            {{ $p->is_picked_up ? 'Picked Up' : 'Not Picked' }}
                                        </button>
                                    </div>
                                </td>
                                <td class="px-3 py-2 no-click">
                                    <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Aksi</span>
                                    <div class="cell-value text-right md:text-left">
                                        <button type="button" 
                                            onclick="openDetailModalFromRow(this.closest('tr'))"
                                            class="px-2.5 py-1 text-xs rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold transition">
                                            Detail
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if($participants->isEmpty())
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-slate-400">Tidak ada data peserta yang cocok.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination & Meta -->
            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-800">
                <div id="participants-meta" class="text-xs text-slate-400">
                    Menampilkan <span class="font-mono">{{ $participants->count() }}</span> dari <span class="font-mono">{{ $participants->total() }}</span> peserta
                </div>
                <div id="participants-pagination" class="flex flex-wrap gap-1.5 justify-start sm:justify-end"></div>
            </div>
        </div>

        <!-- Coupon Usage Card -->
        <div class="bg-card border border-slate-800 rounded-lg p-5">
            <div class="pb-3 border-b border-slate-800">
                <h2 class="font-brand-heading text-base font-bold text-white">Kupon Terpakai</h2>
                <p class="text-xs text-slate-400 mt-0.5">Berdasarkan transaksi paid dan pending</p>
            </div>

            <div class="mt-4 overflow-x-auto border border-slate-800 rounded-lg">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-900 text-slate-300 border-b border-slate-800 hidden md:table-header-group">
                        <tr>
                            <th class="text-left font-semibold px-4 py-2.5">Kode Kupon</th>
                            <th class="text-right font-semibold px-4 py-2.5">Jumlah Digunakan</th>
                            <th class="text-right font-semibold px-4 py-2.5">Total Diskon</th>
                        </tr>
                    </thead>
                    <tbody id="coupon-tbody" class="divide-y divide-slate-800">
                        @foreach($couponUsage as $c)
                            <tr class="hover:bg-slate-900/40 block md:table-row border-b border-slate-800 md:border-none mb-3 md:mb-0 bg-slate-950/20 md:bg-transparent rounded-md md:rounded-none p-3 md:p-0">
                                <td class="px-4 py-2 md:py-2.5 font-mono font-bold text-white block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-400 font-bold text-xs uppercase">Kode</span>
                                    <span class="text-right md:text-left">{{ $c->code }}</span>
                                </td>
                                <td class="px-4 py-2 md:py-2.5 text-slate-200 block md:table-cell flex justify-between items-center md:block text-right">
                                    <span class="md:hidden text-slate-400 font-bold text-xs uppercase text-left">Dipakai</span>
                                    <span>{{ number_format((int) $c->total_transactions) }} kali</span>
                                </td>
                                <td class="px-4 py-2 md:py-2.5 text-slate-200 block md:table-cell flex justify-between items-center md:block text-right">
                                    <span class="md:hidden text-slate-400 font-bold text-xs uppercase text-left">Total Diskon</span>
                                    <span>Rp {{ number_format((float) $c->total_discount, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @endforeach
                        @if($couponUsage->isEmpty())
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-400">Belum ada kupon yang terpakai.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Doorprize Modal -->
<div id="doorprizeModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/80 transition-opacity" onclick="closeDoorprizeModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div id="doorprizeModalCard" class="relative transform overflow-hidden rounded-lg bg-slate-900 border border-slate-800 text-left shadow-2xl transition-all w-full max-w-4xl p-6">
                
                <!-- Action Controls -->
                <div class="absolute top-4 right-4 flex items-center gap-2 z-30">
                    <button type="button" onclick="toggleDoorprizeFullscreen()" class="p-1.5 rounded-md hover:bg-slate-800 text-slate-400 hover:text-white transition" title="Toggle Fullscreen">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="fullscreenIcon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4" />
                        </svg>
                    </button>
                    <button type="button" onclick="closeDoorprizeModal()" class="p-1.5 rounded-md hover:bg-slate-800 text-slate-400 hover:text-white transition" title="Tutup">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Header -->
                <div class="mb-5 pb-3 border-b border-slate-800">
                    <h3 class="font-brand-heading text-xl font-bold text-white flex items-center gap-2">
                        <span>Undian Doorprize Peserta</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Mengundi pemenang secara acak dari peserta berstatus lunas (Paid) untuk event <strong>{{ $event->name }}</strong>.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Main Draw Screen (2 Cols) -->
                    <div class="lg:col-span-2 flex flex-col justify-between bg-slate-950 border border-slate-800 rounded-lg p-5">
                        
                        <!-- Draw Name Input -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nama Undian / Hadiah</label>
                            <input type="text" id="doorprizeDrawName" placeholder="Masukkan nama undian (misal: Sepeda Lipat, Smartwatch, Voucher)" class="w-full px-3 py-2 rounded-md border border-slate-800 bg-slate-900 text-white text-xs focus:outline-none focus:border-slate-600 placeholder-slate-500 transition">
                        </div>

                        <!-- Draw Display Board -->
                        <div class="flex flex-col items-center justify-center min-h-[220px] text-center">
                            <div id="doorprizeDrawBoard" class="w-full flex flex-col items-center justify-center p-6 rounded-lg border border-slate-800 transition-colors">
                                
                                <!-- Placeholder -->
                                <div id="doorprizePlaceholder" class="text-slate-500 flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-gift text-4xl text-slate-600 mb-1"></i>
                                    <p class="text-xs font-semibold tracking-wide uppercase">Siap untuk memutar undian doorprize</p>
                                </div>

                                <!-- Live Spin State -->
                                <div id="doorprizeLiveSpin" class="hidden w-full space-y-3">
                                    <div class="text-xs font-bold uppercase tracking-wider text-sky-400" id="liveDrawName"></div>
                                    <div class="text-6xl font-extrabold text-white tracking-widest font-mono select-none" id="liveBib">0</div>
                                    <div class="text-xs text-slate-400 font-medium" id="liveStatus">Memutar acak nomor peserta...</div>
                                </div>

                                <!-- Winner State -->
                                <div id="doorprizeWinner" class="hidden w-full space-y-4">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/10 border border-emerald-500/30 rounded text-xs font-bold text-emerald-400 uppercase tracking-wider">
                                        Pemenang Terpilih
                                    </div>
                                    <div class="text-sm font-bold text-amber-400 uppercase tracking-wider" id="winnerDrawName"></div>
                                    <div>
                                        <div class="text-7xl font-extrabold text-white tracking-widest font-mono" id="winnerBib">0</div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Exclude winners checkbox -->
                        <div class="mt-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 px-1">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-400 cursor-pointer hover:text-slate-200 transition">
                                <input type="checkbox" id="doorprizeExcludeWinners" checked class="rounded border-slate-700 bg-slate-900 text-sky-500 focus:ring-0 cursor-pointer">
                                Saring pemenang yang sudah terpilih sebelumnya
                            </label>
                            <div class="text-xs text-slate-500">
                                Total Peserta Paid: <span id="doorprizeTotalPaid" class="font-bold text-slate-300">-</span>
                            </div>
                        </div>

                        <!-- Action Controls -->
                        <div class="mt-5 flex gap-2.5">
                            <button type="button" id="btnStartDoorprize" onclick="startDoorprizeDraw()" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-md font-bold text-xs tracking-wider uppercase transition flex items-center justify-center gap-1.5">
                                <span>Mulai Undian</span>
                            </button>
                            <button type="button" id="btnStopDoorprize" onclick="stopDoorprizeDraw()" disabled class="flex-1 py-3 bg-slate-800 text-slate-500 rounded-md font-bold text-xs tracking-wider uppercase transition cursor-not-allowed flex items-center justify-center gap-1.5">
                                <span>Hentikan Undian</span>
                            </button>
                        </div>

                    </div>

                    <!-- Winner History Sidebar -->
                    <div id="doorprizeHistorySidebar" class="flex flex-col bg-slate-950 border border-slate-800 rounded-lg p-4 h-[380px] lg:h-auto">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-slate-800">
                            <h4 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Daftar Pemenang Terpilih</h4>
                            <button type="button" onclick="clearDoorprizeWinners()" class="text-[10px] text-rose-400 hover:underline font-bold transition">Reset</button>
                        </div>

                        <!-- Scrollable Winner List -->
                        <div id="doorprizeWinnerList" class="flex-1 overflow-y-auto space-y-2 pr-1 text-left text-xs">
                            <div class="text-xs text-slate-500 text-center py-8">Belum ada pemenang yang ditarik.</div>
                        </div>

                        <!-- Export Button -->
                        <button type="button" onclick="exportDoorprizeWinners()" class="mt-3 w-full py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-md text-xs font-semibold transition flex items-center justify-center gap-1.5 border border-slate-700">
                            <i class="fa-solid fa-download text-xs"></i>
                            <span>Unduh Pemenang (CSV)</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/80 p-4">
    <div class="bg-card w-full max-w-lg rounded-lg border border-slate-800 shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-slate-800 bg-slate-900/50">
            <h3 class="text-base font-bold text-white">Edit Peserta</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="edit-form" method="POST" enctype="multipart/form-data" class="p-5 space-y-4 text-xs">
            @csrf
            <input type="hidden" name="participant_id" id="edit-participant-id">
            
            <div>
                <label class="block text-slate-400 mb-1 font-semibold">Nama Peserta</label>
                <div id="edit-name" class="text-sm font-bold text-white"></div>
            </div>

            <div>
                <label class="block text-slate-400 mb-1 font-semibold">Status Approved</label>
                <select name="isApproved" id="edit-isApproved" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                    <option value="1">Yes (Approved)</option>
                    <option value="0">No (Not Approved)</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-400 mb-1 font-semibold">Target Time</label>
                <input type="text" name="target_time" id="edit-target-time" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600" placeholder="HH:MM:SS">
            </div>

            <div>
                <label class="block text-slate-400 mb-1 font-semibold">Foto</label>
                <div class="flex items-center gap-3">
                    <img id="edit-photo-preview" src="" class="w-14 h-14 object-cover rounded-md bg-slate-800 border border-slate-700 hidden">
                    <input type="file" name="photo" id="edit-photo" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-1.5 text-xs text-slate-300">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-3.5 py-2 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 transition font-semibold">Batal</button>
                <button type="submit" class="px-3.5 py-2 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white transition font-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Modal -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/80 p-4">
    <div class="bg-card w-full max-w-2xl rounded-lg border border-slate-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-slate-800 bg-slate-900/50">
            <h3 class="text-base font-bold text-white">Detail Lengkap Peserta</h3>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <!-- Content -->
        <div class="p-5 overflow-y-auto space-y-5 text-xs text-slate-300">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Personal Info -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 pb-1.5 border-b border-slate-800">Informasi Pribadi</h4>
                    <div>
                        <div class="text-[11px] text-slate-400">Nama Lengkap</div>
                        <div class="text-white font-semibold text-sm mt-0.5" id="dm-name">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">ID Card (KTP/SIM)</div>
                        <div class="text-white font-mono mt-0.5" id="dm-id-card">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Gender & Tanggal Lahir</div>
                        <div class="text-white capitalize mt-0.5" id="dm-gender">-</div>
                        <div class="text-slate-400 text-[11px]" id="dm-dob">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Email & No. Telp</div>
                        <div class="text-white break-all mt-0.5" id="dm-email">-</div>
                        <div class="text-white font-mono" id="dm-phone">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Kontak Darurat</div>
                        <div class="text-white mt-0.5" id="dm-emergency">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Alamat Lengkap</div>
                        <div class="text-white mt-0.5" id="dm-address">-</div>
                    </div>
                </div>

                <!-- Race Info -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 pb-1.5 border-b border-slate-800">Informasi Lomba</h4>
                    <div>
                        <div class="text-[11px] text-slate-400">Kategori Lomba</div>
                        <div class="text-white font-bold mt-0.5" id="dm-category">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Nomor BIB</div>
                        <div class="text-white font-mono text-base font-bold mt-0.5" id="dm-bib">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Ukuran Jersey & Gol. Darah</div>
                        <div class="text-white font-bold mt-0.5" id="dm-jersey">-</div>
                        <div class="text-slate-400 text-[11px]" id="dm-blood-type">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Kelompok Umur</div>
                        <div class="text-white font-medium mt-0.5" id="dm-age-group">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Target Waktu</div>
                        <div class="text-white font-mono mt-0.5" id="dm-target-time">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Status Pengambilan Race Pack</div>
                        <div class="flex items-center gap-2 mt-1">
                            <div id="dm-pickup-status">-</div>
                            <button type="button" id="dm-pickup-toggle-btn" class="px-2 py-0.5 text-[10px] rounded-md font-bold bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 transition">
                                Ubah Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PIC & Transaction Info -->
            <div class="pt-4 border-t border-slate-800 grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- PIC Info -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 pb-1.5 border-b border-slate-800">Informasi Pemesan (PIC)</h4>
                    <div>
                        <div class="text-[11px] text-slate-400">Nama PIC</div>
                        <div class="text-white font-medium mt-0.5" id="dm-pic-name">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Email & Telepon PIC</div>
                        <div class="text-white break-all mt-0.5" id="dm-pic-email">-</div>
                        <div class="text-white font-mono" id="dm-pic-phone">-</div>
                    </div>
                </div>

                <!-- Transaction Info -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 pb-1.5 border-b border-slate-800">Detail Transaksi</h4>
                    <div>
                        <div class="text-[11px] text-slate-400">Tanggal Transaksi</div>
                        <div class="text-white font-mono mt-0.5" id="dm-trx-date">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Metode & Kupon</div>
                        <div class="text-white uppercase font-mono mt-0.5" id="dm-payment-method">-</div>
                        <div class="text-amber-400 font-mono text-[11px]" id="dm-coupon">-</div>
                    </div>
                    <div>
                        <div class="text-[11px] text-slate-400">Status Pembayaran</div>
                        <div class="inline-flex mt-1" id="dm-payment-status">-</div>
                    </div>
                </div>
            </div>

            <!-- Addons Info -->
            <div class="pt-4 border-t border-slate-800">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Add-on Terpilih</h4>
                <div id="dm-addons" class="bg-slate-950/60 p-3 rounded-md border border-slate-800 space-y-1">
                    -
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-900/50 px-5 py-3 flex justify-end border-t border-slate-800">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-1.5 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 transition text-xs font-semibold">Tutup</button>
        </div>
    </div>
</div>

<!-- QR Scan Modal -->
<div id="qrScanModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/80 transition-opacity" onclick="closeQrScanModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-lg bg-slate-900 border border-slate-800 text-left shadow-2xl transition-all w-full max-w-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-white">Verifikasi & Scan QR Pickup</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Scan tiket atau masukkan nomor BIB/ID peserta untuk mencatat pengambilan race pack.</p>
                        </div>
                        <button type="button" onclick="closeQrScanModal()" class="text-slate-400 hover:text-white p-1 rounded-md hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- Mode Tabs -->
                    <div class="flex gap-1.5 mt-4 p-1 bg-slate-950 rounded-md border border-slate-800">
                        <button type="button" id="tab-qr-camera" onclick="switchQrMode('camera')" class="flex-1 py-1.5 text-xs font-semibold rounded-md bg-slate-800 text-white transition">Kamera Langsung</button>
                        <button type="button" id="tab-qr-file" onclick="switchQrMode('file')" class="flex-1 py-1.5 text-xs font-semibold rounded-md text-slate-400 hover:text-white transition">Upload Foto QR</button>
                        <button type="button" id="tab-qr-manual" onclick="switchQrMode('manual')" class="flex-1 py-1.5 text-xs font-semibold rounded-md text-slate-400 hover:text-white transition">Input Manual</button>
                    </div>

                    <!-- Mode 1: Camera Scanner -->
                    <div id="panel-qr-camera" class="mt-4">
                        <div class="rounded-lg overflow-hidden border border-slate-800 bg-black relative">
                            <div id="html5-qr-reader" class="w-full"></div>
                            <video id="qrVideo" class="w-full h-64 object-cover hidden" playsinline muted></video>
                        </div>
                        <div class="flex items-center justify-between mt-2.5">
                            <span class="text-[11px] text-slate-400">Arahkan kamera ke QR tiket dari email pelari</span>
                            <div class="flex items-center gap-2">
                                <button type="button" id="btnQrStart" onclick="startQrScan()" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 border border-slate-700">Mulai Ulang</button>
                                <button type="button" id="btnQrStop" onclick="stopQrScan()" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 border border-slate-700">Hentikan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Mode 2: File Upload Scanner -->
                    <div id="panel-qr-file" class="mt-4 hidden">
                        <div class="border-2 border-dashed border-slate-800 hover:border-slate-600 rounded-lg p-6 text-center cursor-pointer bg-slate-950/60 transition" onclick="document.getElementById('qr-file-input').click()">
                            <input type="file" id="qr-file-input" accept="image/*" class="hidden" onchange="handleQrFileScan(this)">
                            <div class="w-10 h-10 rounded-md bg-slate-800 text-slate-300 flex items-center justify-center mx-auto mb-2">
                                <i class="fa-solid fa-image text-sm"></i>
                            </div>
                            <p class="text-xs font-semibold text-white">Klik untuk memilih gambar atau screenshot QR</p>
                            <p class="text-[11px] text-slate-400 mt-1">Mendukung format JPG, PNG, atau WebP</p>
                        </div>
                    </div>

                    <!-- Mode 3: Manual Input -->
                    <div id="panel-qr-manual" class="mt-4 hidden">
                        <form onsubmit="handleQrManualSubmit(event)" class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1.5">Nomor BIB / ID Tiket / Nama / No. HP</label>
                                <div class="flex gap-2">
                                    <input type="text" id="qr-manual-input" placeholder="Contoh: 1001, TICKET-12, atau Budi" class="flex-1 bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-600">
                                    <button type="submit" class="px-4 py-2 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs transition">Verifikasi</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Feedback & Verification Result Card -->
                    <div id="qrScanResultCard" class="mt-4 hidden p-3 rounded-lg border text-xs"></div>
                    <div id="qrScanMsg" class="mt-3 text-xs text-slate-400"></div>
                </div>

                <div class="bg-slate-950 px-6 py-3 border-t border-slate-800 flex justify-end">
                    <button type="button" onclick="closeQrScanModal()" class="px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700 transition">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Coupon Report Modal -->
<div id="coupon-report-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/80 p-4">
    <div class="bg-card w-full max-w-2xl rounded-lg border border-slate-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between p-4 border-b border-slate-800 bg-slate-900/50">
            <div>
                <h3 class="text-base font-bold text-white">Laporan Filter Kupon</h3>
                <div class="text-xs text-slate-400" id="coupon-modal-subtitle"></div>
            </div>
            <button type="button" onclick="closeCouponReportModal()" class="text-slate-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="p-5 overflow-y-auto space-y-5 text-xs text-slate-300">
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Ringkasan Ukuran Jersey</h4>
                <div id="coupon-jersey-summary" class="flex flex-wrap gap-2"></div>
            </div>

            <div>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Daftar Nomor BIB dan Jersey</h4>
                    <div class="flex items-center gap-2">
                        <label for="couponModalFilterPickup" class="text-xs text-slate-400 font-semibold uppercase">Filter Picked:</label>
                        <select id="couponModalFilterPickup" onchange="filterCouponModalTable()" class="bg-slate-950 border border-slate-800 rounded-md px-2.5 py-1 text-xs text-white focus:outline-none">
                            <option value="all">Semua Status</option>
                            <option value="1">Picked Up</option>
                            <option value="0">Not Picked</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto border border-slate-800 rounded-lg">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-900 text-slate-300 border-b border-slate-800">
                            <tr>
                                <th class="text-left font-semibold px-3 py-2">Nama</th>
                                <th class="text-left font-semibold px-3 py-2">Nomor BIB</th>
                                <th class="text-left font-semibold px-3 py-2">Ukuran Jersey</th>
                                <th class="text-left font-semibold px-3 py-2">Status Picked</th>
                            </tr>
                        </thead>
                        <tbody id="coupon-participants-tbody" class="divide-y divide-slate-800"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-slate-900/50 px-5 py-3 flex justify-end border-t border-slate-800">
            <button type="button" onclick="closeCouponReportModal()" class="px-4 py-1.5 rounded-md bg-slate-800 text-slate-300 hover:bg-slate-700 transition text-xs font-semibold">Tutup</button>
        </div>
    </div>
</div>

<!-- Activity Log Modal -->
<div id="activityLogModal" class="fixed inset-0 z-[100] hidden overflow-y-auto p-3 sm:p-4">
    <div class="fixed inset-0 bg-slate-950/80" onclick="closeActivityLogModal()"></div>
    <div class="flex min-h-full items-center justify-center relative pointer-events-none">
        <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] text-slate-200">
            <!-- Header -->
            <div class="p-4 border-b border-slate-800 bg-slate-900 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-md bg-slate-800 text-slate-300 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-sm sm:text-base">
                            Log Aktivitas Operator
                        </h3>
                        <p class="text-[11px] text-slate-400">Histori scan QR, pengubahan status & approval sesi ini</p>
                    </div>
                </div>
                <button type="button" onclick="closeActivityLogModal()" class="w-7 h-7 rounded-md bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Sub Header -->
            <div class="p-3 bg-slate-950/50 border-b border-slate-800 flex flex-col sm:flex-row gap-2 justify-between items-center shrink-0">
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-500 text-xs"></i>
                    <input type="text" id="activity-log-search" oninput="renderActivityLogs(this.value)" placeholder="Cari BIB / Nama di log..." class="w-full bg-slate-950 border border-slate-800 rounded-md pl-8 pr-3 py-1.5 text-xs text-white placeholder-slate-500 outline-none focus:border-slate-600 transition">
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
                    <span id="activity-log-count-summary" class="text-xs text-slate-400 font-mono">0 Log tercatat</span>
                    <button type="button" onclick="clearActivityLogs()" class="px-2.5 py-1 text-xs rounded-md bg-rose-950/40 hover:bg-rose-900/60 text-rose-400 border border-rose-500/30 font-semibold transition flex items-center gap-1">
                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                        <span>Hapus Log</span>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2" id="activity-log-list-container"></div>

            <!-- Footer -->
            <div class="p-3 border-t border-slate-800 bg-slate-900 flex justify-end shrink-0">
                <button type="button" onclick="closeActivityLogModal()" class="px-4 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
    // Theme Management (Dark / Light)
    function applyReportTheme(theme) {
        const isLight = theme === 'light';
        const body = document.body;
        const icon = document.getElementById('theme-toggle-icon');
        const label = document.getElementById('theme-toggle-label');

        if (isLight) {
            body.classList.add('theme-light');
            if (icon) icon.className = 'fa-solid fa-moon text-slate-600';
            if (label) label.textContent = 'Mode Gelap';
            try { localStorage.setItem('ruanglari_report_theme', 'light'); } catch(e) {}
        } else {
            body.classList.remove('theme-light');
            if (icon) icon.className = 'fa-solid fa-sun text-amber-400';
            if (label) label.textContent = 'Mode Terang';
            try { localStorage.setItem('ruanglari_report_theme', 'dark'); } catch(e) {}
        }

        if (typeof salesChart !== 'undefined' && salesChart) {
            updateSalesChartTheme(isLight);
        }
    }

    function toggleReportTheme() {
        const isLight = document.body.classList.contains('theme-light');
        applyReportTheme(isLight ? 'dark' : 'light');
    }

    function updateSalesChartTheme(isLight) {
        if (!salesChart) return;
        const textColor = isLight ? '#475569' : '#94a3b8';
        const gridColor = isLight ? 'rgba(0,0,0,0.06)' : 'rgba(148, 163, 184, 0.15)';

        if (salesChart.options && salesChart.options.scales) {
            if (salesChart.options.scales.x) {
                salesChart.options.scales.x.ticks.color = textColor;
                salesChart.options.scales.x.grid.color = gridColor;
            }
            if (salesChart.options.scales.y) {
                salesChart.options.scales.y.ticks.color = textColor;
                salesChart.options.scales.y.grid.color = gridColor;
            }
        }
        if (salesChart.options && salesChart.options.plugins && salesChart.options.plugins.legend) {
            salesChart.options.plugins.legend.labels.color = isLight ? '#0f172a' : '#e2e8f0';
        }
        salesChart.update();
    }

    // Auto-init theme preference
    (function() {
        let savedTheme = 'dark';
        try {
            const urlParam = new URLSearchParams(window.location.search).get('theme');
            if (urlParam === 'light' || urlParam === 'dark') {
                savedTheme = urlParam;
            } else {
                savedTheme = localStorage.getItem('ruanglari_report_theme') || 'dark';
            }
        } catch(e) {}

        if (savedTheme === 'light') {
            applyReportTheme('light');
        }
    })();

    // Activity Log System
    const activityLogStorageKey = 'report_activity_logs_' + '{{ $event->id }}';

    function getActivityLogs() {
        try {
            const data = localStorage.getItem(activityLogStorageKey);
            return data ? JSON.parse(data) : [];
        } catch(e) {
            return [];
        }
    }

    function updateActivityLogBadge() {
        const logs = getActivityLogs();
        const badge = document.getElementById('activity-log-badge');
        const summary = document.getElementById('activity-log-count-summary');
        if (badge) badge.textContent = logs.length;
        if (summary) summary.textContent = `${logs.length} Log tercatat`;
    }

    function addActivityLog(type, participant, extra = '') {
        if (!participant) return;
        const logs = getActivityLogs();
        
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const dateStr = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });

        const newLog = {
            id: Date.now() + '_' + Math.random().toString(36).substr(2, 4),
            timestamp: timeStr,
            date: dateStr,
            type: type,
            participant_id: participant.id || participant.participant_id || '-',
            name: participant.name || participant.participant_name || '-',
            bib: participant.bib_number || participant.bib || '-',
            category: participant.category_name || participant.category?.name || '-',
            email: participant.email || '-',
            phone: participant.phone || '-',
            extra: extra
        };

        logs.unshift(newLog);
        if (logs.length > 100) logs.splice(100);

        try {
            localStorage.setItem(activityLogStorageKey, JSON.stringify(logs));
        } catch(e) {}

        updateActivityLogBadge();
    }

    function clearActivityLogs() {
        if (!confirm('Apakah Anda yakin ingin menghapus seluruh riwayat log aktivitas scan/pickup?')) return;
        localStorage.removeItem(activityLogStorageKey);
        updateActivityLogBadge();
        renderActivityLogs();
    }

    function openActivityLogModal() {
        renderActivityLogs();
        const modal = document.getElementById('activityLogModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeActivityLogModal() {
        const modal = document.getElementById('activityLogModal');
        if (modal) modal.classList.add('hidden');
    }

    function renderActivityLogs(searchQuery = '') {
        const container = document.getElementById('activity-log-list-container');
        if (!container) return;

        const logs = getActivityLogs();
        const query = (searchQuery || '').toLowerCase().trim();

        const filtered = logs.filter(log => {
            if (!query) return true;
            return (log.name || '').toLowerCase().includes(query) ||
                   (log.bib || '').toLowerCase().includes(query) ||
                   (log.type || '').toLowerCase().includes(query) ||
                   (log.category || '').toLowerCase().includes(query);
        });

        if (filtered.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10 text-slate-500">
                    <i class="fa-solid fa-list-check text-2xl mb-2 text-slate-600 block"></i>
                    <p class="text-xs font-semibold">Belum ada log aktivitas ${query ? 'yang cocok' : 'tercatat'}.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = filtered.map(log => {
            let badgeClass = 'bg-slate-800 text-slate-300 border-slate-700';
            let badgeText = 'ACTIVITY';
            let iconClass = 'fa-info-circle';

            if (log.type === 'pickup_on') {
                badgeClass = 'bg-emerald-950/60 text-emerald-400 border-emerald-500/30';
                badgeText = 'PICKED UP (Sudah Diambil)';
                iconClass = 'fa-circle-check';
            } else if (log.type === 'pickup_off') {
                badgeClass = 'bg-amber-950/60 text-amber-400 border-amber-500/30';
                badgeText = 'NOT PICKED (Batal Picked)';
                iconClass = 'fa-rotate-left';
            } else if (log.type === 'qr_scan') {
                badgeClass = 'bg-sky-950/60 text-sky-300 border-sky-500/30';
                badgeText = 'SCAN QR SUCCESS';
                iconClass = 'fa-qrcode';
            } else if (log.type === 'status_update') {
                badgeClass = 'bg-slate-800 text-slate-200 border-slate-700';
                badgeText = 'STATUS UPDATE';
                iconClass = 'fa-pen-to-square';
            }

            return `
                <div class="bg-slate-950 border border-slate-800 rounded-md p-3 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 hover:border-slate-700 transition">
                    <div class="flex items-start gap-2.5">
                        <div class="px-2 py-0.5 rounded border text-[10px] font-mono font-bold uppercase ${badgeClass} shrink-0 mt-0.5 sm:mt-0 flex items-center gap-1">
                            <i class="fa-solid ${iconClass} text-[9px]"></i>
                            <span>${badgeText}</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-white flex items-center gap-2">
                                <span>${log.name}</span>
                                <span class="px-1.5 py-0.2 bg-slate-800 text-slate-200 rounded text-[10px] font-mono font-bold">BIB #${log.bib}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5">
                                <span>${log.category}</span>
                                <span>•</span>
                                <span class="text-slate-500">${log.email !== '-' ? log.email : log.phone}</span>
                                ${log.extra ? `<span class="text-slate-500">• ${log.extra}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="text-[10px] font-mono text-slate-500 self-end sm:self-center shrink-0">
                        ${log.date} ${log.timestamp}
                    </div>
                </div>
            `;
        }).join('');
    }

    // View Mode Switcher
    const viewModeStorageKey = 'report_table_view_mode';

    function setTableViewMode(mode) {
        const wrapper = document.getElementById('participants-table-wrapper');
        const btnList = document.getElementById('btn-view-mode-list');
        const btnStacked = document.getElementById('btn-view-mode-stacked');

        if (!wrapper) return;

        if (mode === 'stacked') {
            wrapper.classList.remove('view-mode-list');
            wrapper.classList.add('view-mode-stacked');

            if (btnList) {
                btnList.className = 'px-2.5 py-1 rounded-md text-xs font-semibold transition flex items-center gap-1.5 text-slate-400 hover:text-white';
            }
            if (btnStacked) {
                btnStacked.className = 'px-2.5 py-1 rounded-md text-xs font-semibold transition flex items-center gap-1.5 bg-slate-800 text-white';
            }
        } else {
            mode = 'list';
            wrapper.classList.remove('view-mode-stacked');
            wrapper.classList.add('view-mode-list');

            if (btnList) {
                btnList.className = 'px-2.5 py-1 rounded-md text-xs font-semibold transition flex items-center gap-1.5 bg-slate-800 text-white';
            }
            if (btnStacked) {
                btnStacked.className = 'px-2.5 py-1 rounded-md text-xs font-semibold transition flex items-center gap-1.5 text-slate-400 hover:text-white';
            }
        }

        try {
            localStorage.setItem(viewModeStorageKey, mode);
        } catch(e) {}
    }

    function initTableViewMode() {
        let savedMode = 'list';
        try {
            savedMode = localStorage.getItem(viewModeStorageKey) || 'list';
        } catch(e) {}

        setTableViewMode(savedMode);
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateActivityLogBadge();
        initTableViewMode();
    });

    const updateUrlBase = "{{ route('report.participant.update', ['event' => $event->id, 'participant' => ':id']) }}";
    const csrfTokenVal = "{{ csrf_token() }}";

    var html5QrCode = null;
    var qrRunning = false;
    var qrBusy = false;
    var qrLastOkAt = 0;
    var activeQrMode = 'camera';

    const scanQrEndpoint = "{{ route('report.scan-qr', $event->id) }}" + window.location.search;

    window.switchQrMode = function(mode) {
        activeQrMode = mode;
        ['camera', 'file', 'manual'].forEach(m => {
            const btn = document.getElementById('tab-qr-' + m);
            const panel = document.getElementById('panel-qr-' + m);
            if (btn) {
                if (m === mode) {
                    btn.className = 'flex-1 py-1.5 text-xs font-semibold rounded-md bg-slate-800 text-white transition';
                } else {
                    btn.className = 'flex-1 py-1.5 text-xs font-semibold rounded-md text-slate-400 hover:text-white transition';
                }
            }
            if (panel) {
                if (m === mode) panel.classList.remove('hidden');
                else panel.classList.add('hidden');
            }
        });

        if (mode === 'camera') {
            window.startQrScan();
        } else {
            window.stopQrScan();
            if (mode === 'manual') {
                const inp = document.getElementById('qr-manual-input');
                if (inp) inp.focus();
            }
        }
    };

    function setQrResult(data, isError, message) {
        const card = document.getElementById('qrScanResultCard');
        const msgEl = document.getElementById('qrScanMsg');
        if (msgEl) msgEl.textContent = '';

        if (!card) return;

        if (isError) {
            card.className = 'mt-4 p-3 rounded-md bg-rose-950/60 border border-rose-800/80 text-rose-200 text-xs block';
            card.innerHTML = `<div class="font-bold mb-1">Gagal Verifikasi</div><p class="text-slate-300 leading-relaxed">${message || 'Kode QR tidak dikenali atau belum lunas.'}</p>`;
            return;
        }

        const p = data.participant || {};
        const isAlready = data.already_picked_up;

        card.className = isAlready 
            ? 'mt-4 p-3.5 rounded-md bg-amber-950/70 border border-amber-600/60 text-amber-200 text-xs block'
            : 'mt-4 p-3.5 rounded-md bg-emerald-950/70 border border-emerald-600/60 text-emerald-200 text-xs block';

        card.innerHTML = `
            <div class="flex items-center justify-between mb-2 pb-2 border-b border-white/10">
                <span class="font-bold text-sm text-white">${isAlready ? 'SUDAH PERNAH DIAMBIL' : 'VERIFIKASI SUKSES'}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold ${isAlready ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300'}">BIB: ${p.bib_number || '-'}</span>
            </div>
            <div class="space-y-1 text-slate-300">
                <div class="flex justify-between"><span class="text-slate-400">Nama:</span> <strong class="text-white">${p.name || '-'}</strong></div>
                <div class="flex justify-between"><span class="text-slate-400">Jersey:</span> <span class="font-bold text-white">${p.jersey_size || '-'}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Kategori / Umur:</span> <span>${p.age_group || '-'}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Pembayaran:</span> <span class="text-emerald-400 font-bold uppercase">${p.payment_status || 'PAID'}</span></div>
                ${p.addons && p.addons.length ? `<div class="flex justify-between"><span class="text-slate-400">Addons:</span> <span>${p.addons.join(', ')}</span></div>` : ''}
                ${isAlready && p.picked_up_at ? `<div class="text-[11px] text-amber-300/90 pt-1">Diambil pada: ${p.picked_up_at} (${p.picked_up_by || 'Staff'})</div>` : ''}
            </div>
        `;
    }

    function verifyQrCodePayload(code) {
        if (!code || qrBusy) return;
        const now = Date.now();
        if (now - qrLastOkAt < 1200) return;

        qrBusy = true;
        const msgEl = document.getElementById('qrScanMsg');
        if (msgEl) msgEl.textContent = 'Memverifikasi data tiket #' + code + '…';

        fetch(scanQrEndpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfTokenVal,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                code: String(code).trim(),
                picked_up_by: 'Public Report Scanner'
            })
        })
        .then(r => r.json())
        .then(res => {
            qrBusy = false;
            if (res.success) {
                qrLastOkAt = Date.now();
                setQrResult(res, false, res.message);
                if (typeof addActivityLog === 'function' && res.participant) {
                    addActivityLog('qr_scan', res.participant, 'Scan QR Verified');
                }
                const filterForm = document.getElementById('report-filters');
                if (filterForm && typeof fetchReport === 'function') {
                    const fd = new FormData(filterForm);
                    const payloadObj = {};
                    for (const [k, v] of fd.entries()) {
                        payloadObj[k] = typeof v === 'string' ? v.trim() : v;
                    }
                    payloadObj.page = 1;
                    fetchReport(payloadObj);
                }
            } else {
                setQrResult(res, true, res.message || 'Verifikasi gagal');
            }
        })
        .catch(err => {
            qrBusy = false;
            setQrResult(null, true, err.message || 'Terjadi kesalahan jaringan atau sesi scanner kedaluwarsa.');
        });
    }

    window.openQrScanModal = function() {
        var modal = document.getElementById('qrScanModal');
        if (modal) modal.classList.remove('hidden');
        const card = document.getElementById('qrScanResultCard');
        if (card) card.classList.add('hidden');
        const msg = document.getElementById('qrScanMsg');
        if (msg) msg.textContent = '';
        window.switchQrMode('camera');
    };

    window.closeQrScanModal = function() {
        window.stopQrScan();
        var modal = document.getElementById('qrScanModal');
        if (modal) modal.classList.add('hidden');
    };

    window.startQrScan = function() {
        if (qrRunning) return;
        const readerEl = document.getElementById('html5-qr-reader');
        if (!readerEl) return;

        const msgEl = document.getElementById('qrScanMsg');
        if (msgEl) msgEl.textContent = 'Menyiapkan scanner kamera…';

        if (typeof Html5Qrcode !== 'undefined') {
            try {
                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode("html5-qr-reader");
                }
                qrRunning = true;
                html5QrCode.start(
                    { facingMode: "environment" },
                    { fps: 12, qrbox: { width: 220, height: 220 } },
                    (decodedText) => {
                        verifyQrCodePayload(decodedText);
                    },
                    () => {}
                ).then(() => {
                    if (msgEl) msgEl.textContent = 'Kamera aktif. Arahkan ke barcode atau QR tiket.';
                }).catch(err => {
                    qrRunning = false;
                    console.warn('html5QrCode start failed, fallback to file/manual', err);
                    if (msgEl) msgEl.textContent = 'Tidak dapat membuka kamera (' + (err.message || 'Izin kamera ditolak') + '). Gunakan Upload Foto atau Input Manual.';
                });
                return;
            } catch(e) {
                console.error(e);
            }
        }
        if (msgEl) msgEl.textContent = 'Pustaka scanner belum dimuat. Silakan gunakan Input Manual.';
    };

    window.stopQrScan = function() {
        if (html5QrCode && qrRunning) {
            html5QrCode.stop().then(() => {
                qrRunning = false;
            }).catch(() => {
                qrRunning = false;
            });
        }
    };

    window.handleQrFileScan = function(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const msgEl = document.getElementById('qrScanMsg');
        if (msgEl) msgEl.textContent = 'Membaca gambar QR…';

        if (typeof Html5Qrcode !== 'undefined') {
            const scanner = new Html5Qrcode("html5-qr-reader");
            scanner.scanFile(file, true)
                .then(decodedText => {
                    verifyQrCodePayload(decodedText);
                })
                .catch(err => {
                    if (msgEl) msgEl.textContent = 'QR tidak ditemukan dalam foto tersebut. Coba foto lain atau gunakan Input Manual.';
                });
        }
    };

    window.handleQrManualSubmit = function(e) {
        e.preventDefault();
        const inp = document.getElementById('qr-manual-input');
        if (!inp || !inp.value.trim()) return;
        verifyQrCodePayload(inp.value.trim());
    };

    async function updateStatus(participantId, value, checkboxEl) {
        const url = updateUrlBase.replace(':id', participantId);
        const original = !value;
        checkboxEl.disabled = true;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfTokenVal,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ isApproved: value })
            });

            if (!res.ok) {
                throw new Error('Gagal update status');
            }

            const data = await res.json();
            if (!data.success) {
                throw new Error(data.message || 'Gagal update status');
            }

            checkboxEl.checked = value;
            toggleLabel(participantId, value);
        } catch (err) {
            checkboxEl.checked = original;
            toggleLabel(participantId, original);
            alert(err.message || 'Terjadi kesalahan sistem');
        } finally {
            checkboxEl.disabled = false;
        }
    }

    function toggleLabel(id, isChecked) {
        const textEl = document.getElementById('status-text-' + id);
        if (textEl) {
            textEl.textContent = isChecked ? 'Approved' : 'Pending';
        }
    }

    function handleToggle(id, el) {
        const val = el.checked ? 1 : 0;
        toggleLabel(id, el.checked);
        updateStatus(id, val, el);
    }

    function openEditModal(p) {
        document.getElementById('edit-participant-id').value = p.id;
        document.getElementById('edit-name').textContent = p.name;
        document.getElementById('edit-isApproved').value = p.isApproved ? '1' : '0';
        document.getElementById('edit-target-time').value = p.target_time || '';
        
        const preview = document.getElementById('edit-photo-preview');
        if (p.photo_url) {
            preview.src = p.photo_url;
            preview.classList.remove('hidden');
        } else {
            preview.src = '';
            preview.classList.add('hidden');
        }

        const editForm = document.getElementById('edit-form');
        editForm.action = updateUrlBase.replace(':id', p.id);

        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    function openDetailModalFromRow(tr) {
        var data = JSON.parse(tr.dataset.json || '{}');
        
        document.getElementById('dm-name').textContent = data.name || '-';
        document.getElementById('dm-id-card').textContent = data.id_card || '-';
        document.getElementById('dm-gender').textContent = data.gender || '-';
        
        var dob = data.date_of_birth;
        if (dob) {
            try {
                var dateObj = new Date(dob);
                if (!isNaN(dateObj.getTime())) {
                    var options = { day: 'numeric', month: 'short', year: 'numeric' };
                    dob = dateObj.toLocaleDateString('id-ID', options);
                }
            } catch(e) {}
        }
        document.getElementById('dm-dob').textContent = dob || '-';
        
        document.getElementById('dm-email').textContent = data.email || '-';
        document.getElementById('dm-phone').textContent = data.phone || '-';
        
        var emergency = '-';
        if (data.emergency_contact_name || data.emergency_contact_number) {
            emergency = (data.emergency_contact_name || '') + ' (' + (data.emergency_contact_number || '') + ')';
        }
        document.getElementById('dm-emergency').textContent = emergency;
        
        var fullAddress = data.address || '';
        var addrParts = [];
        if (data.city) addrParts.push(data.city);
        if (data.province) addrParts.push(data.province);
        if (data.postal_code) addrParts.push(data.postal_code);
        if (addrParts.length > 0) {
            fullAddress += (fullAddress ? ', ' : '') + addrParts.join(', ');
        }
        document.getElementById('dm-address').textContent = fullAddress || '-';
        
        document.getElementById('dm-category').textContent = (data.category && data.category.name) ? data.category.name : (data.category_name || '-');
        document.getElementById('dm-bib').textContent = data.bib_number || '-';
        document.getElementById('dm-jersey').textContent = data.jersey_size || '-';
        document.getElementById('dm-blood-type').textContent = data.blood_type || '-';
        document.getElementById('dm-age-group').textContent = data.age_group || '-';
        document.getElementById('dm-target-time').textContent = data.target_time || '-';
        
        document.getElementById('dm-pickup-status').dataset.participantId = data.id;
        updateModalPickupUi(data);
        
        document.getElementById('dm-pic-name').textContent = data.pic_name || '-';
        document.getElementById('dm-pic-email').textContent = data.pic_email || '-';
        document.getElementById('dm-pic-phone').textContent = data.pic_phone || '-';
        document.getElementById('dm-trx-date').textContent = data.transaction_date || '-';
        document.getElementById('dm-payment-method').textContent = data.payment_method || '-';
        document.getElementById('dm-coupon').textContent = data.coupon_code || '-';
        
        var pStatus = (data.payment_status || '').toLowerCase();
        var pStatusEl = document.getElementById('dm-payment-status');
        pStatusEl.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border';
        if (pStatus === 'paid' || pStatus === 'settlement' || pStatus === 'capture' || pStatus === 'cod') {
            pStatusEl.classList.add('bg-emerald-950/40', 'text-emerald-400', 'border-emerald-500/30');
        } else if (pStatus === 'pending') {
            pStatusEl.classList.add('bg-amber-950/40', 'text-amber-400', 'border-amber-500/30');
        } else {
            pStatusEl.classList.add('bg-rose-950/40', 'text-rose-400', 'border-rose-500/30');
        }
        pStatusEl.textContent = pStatus.toUpperCase();
        
        var addonsEl = document.getElementById('dm-addons');
        if (data.addons && Array.isArray(data.addons) && data.addons.length > 0) {
            addonsEl.innerHTML = data.addons.map(function(a) {
                var name = a.name || a['name'] || '-';
                var val = a.value || a['value'] || '-';
                return '<div class="flex justify-between text-xs py-1 border-b border-slate-800 last:border-0"><span class="text-slate-400 font-medium">' + name + '</span><span class="text-white font-bold">' + val + '</span></div>';
            }).join('');
        } else {
            addonsEl.innerHTML = '<div class="text-slate-500 italic text-xs text-center py-2">Tidak ada addons</div>';
        }
        
        document.getElementById('detail-modal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }

    function performPickupToggle(participantId, nextStatus, callback) {
        const eventId = "{{ $event->id }}";
        const url = `/reports/${eventId}/participants/${participantId}/status`;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                is_picked_up: nextStatus ? 1 : 0
            })
        })
        .then(r => {
            if (!r.ok) return r.json().then(err => { throw err; });
            return r.json();
        })
        .then(data => {
            if (data.success) {
                adjustPickupStats(nextStatus);
                if (typeof addActivityLog === 'function') {
                    const row = document.querySelector(`tr[data-json*='"id":${participantId}']`) ||
                                document.querySelector(`tr[data-json*='"id":"${participantId}"']`);
                    let pObj = { id: participantId, name: 'Participant #' + participantId, bib: '-' };
                    if (row && row.dataset.json) {
                        try { pObj = JSON.parse(row.dataset.json); } catch(e){}
                    }
                    addActivityLog(nextStatus ? 'pickup_on' : 'pickup_off', pObj, nextStatus ? 'Racepack Picked Up' : 'Pickup Cancelled');
                }
                if (callback) callback(null, data);
            } else {
                if (callback) callback(new Error(data.message || 'Gagal update status pickup'));
            }
        })
        .catch(err => {
            if (callback) callback(err);
        });
    }

    function adjustPickupStats(isPickedUp) {
        const pickedEl = document.getElementById('stat-picked');
        const unpickedEl = document.getElementById('stat-unpicked');
        
        if (pickedEl) {
            let current = parseInt(pickedEl.textContent.replace(/\D/g, '')) || 0;
            pickedEl.textContent = isPickedUp ? (current + 1).toLocaleString('id-ID') : Math.max(0, current - 1).toLocaleString('id-ID');
        }
        if (unpickedEl) {
            let current = parseInt(unpickedEl.textContent.replace(/\D/g, '')) || 0;
            unpickedEl.textContent = isPickedUp ? Math.max(0, current - 1).toLocaleString('id-ID') : (current + 1).toLocaleString('id-ID');
        }
    }

    function togglePickup(btn, participantId, isPickedUp) {
        const nextStatus = !isPickedUp;
        btn.disabled = true;

        performPickupToggle(participantId, nextStatus, function(err, data) {
            btn.disabled = false;
            if (err) {
                alert(err.message || 'Gagal mengubah status pengambilan.');
                return;
            }

            if (nextStatus) {
                btn.className = "px-2 py-1 text-xs rounded-md font-semibold border transition duration-150 bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/50";
                btn.textContent = 'Picked Up';
                btn.setAttribute('onclick', `togglePickup(this, ${participantId}, true)`);
            } else {
                btn.className = "px-2 py-1 text-xs rounded-md font-semibold border transition duration-150 bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700";
                btn.textContent = 'Not Picked';
                btn.setAttribute('onclick', `togglePickup(this, ${participantId}, false)`);
            }

            const row = btn.closest('tr');
            if (row && row.dataset.json) {
                try {
                    let obj = JSON.parse(row.dataset.json);
                    obj.is_picked_up = nextStatus;
                    row.dataset.json = JSON.stringify(obj);
                } catch(e) {}
            }
        });
    }

    const dmPickupToggleBtn = document.getElementById('dm-pickup-toggle-btn');
    if (dmPickupToggleBtn) {
        dmPickupToggleBtn.addEventListener('click', function() {
            const statusContainer = document.getElementById('dm-pickup-status');
            const participantId = statusContainer.dataset.participantId;
            if (!participantId) return;

            const isCurrentlyPicked = statusContainer.dataset.isPickedUp === 'true';
            const nextStatus = !isCurrentlyPicked;

            dmPickupToggleBtn.disabled = true;
            performPickupToggle(participantId, nextStatus, function(err, data) {
                dmPickupToggleBtn.disabled = false;
                if (err) {
                    alert(err.message || 'Gagal mengubah status.');
                    return;
                }

                updateModalPickupUi({ id: participantId, is_picked_up: nextStatus });

                const row = document.querySelector(`tr[data-json*='"id":${participantId}']`) ||
                            document.querySelector(`tr[data-json*='"id":"${participantId}"']`);
                if (row) {
                    const btnInRow = row.querySelector('button[onclick*="togglePickup"]');
                    if (btnInRow) {
                        if (nextStatus) {
                            btnInRow.className = "px-2 py-1 text-xs rounded-md font-semibold border transition duration-150 bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/50";
                            btnInRow.textContent = 'Picked Up';
                            btnInRow.setAttribute('onclick', `togglePickup(this, ${participantId}, true)`);
                        } else {
                            btnInRow.className = "px-2 py-1 text-xs rounded-md font-semibold border transition duration-150 bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700";
                            btnInRow.textContent = 'Not Picked';
                            btnInRow.setAttribute('onclick', `togglePickup(this, ${participantId}, false)`);
                        }
                    }
                    try {
                        let obj = JSON.parse(row.dataset.json);
                        obj.is_picked_up = nextStatus;
                        row.dataset.json = JSON.stringify(obj);
                    } catch(e) {}
                }
            });
        });
    }

    function updateModalPickupUi(data) {
        const container = document.getElementById('dm-pickup-status');
        const isPicked = !!data.is_picked_up;
        container.dataset.isPickedUp = isPicked ? 'true' : 'false';

        if (isPicked) {
            container.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-950/40 text-emerald-400 border border-emerald-500/30">Sudah Diambil (Picked Up)</span>`;
        } else {
            container.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-800 text-slate-400 border border-slate-700">Belum Diambil (Not Picked)</span>`;
        }
    }

    function getExportUrl(format) {
        const form = document.getElementById('report-filters');
        if (!form) return '#';
        const fd = new FormData(form);
        const params = new URLSearchParams();
        for (const [k, v] of fd.entries()) {
            if (typeof v === 'string' && v.trim() !== '') {
                params.set(k, v.trim());
            }
        }
        params.set('format', format);
        return "{{ route('report.export', $event->id) }}?" + params.toString();
    }

    (function () {
        const form = document.getElementById('report-filters');
        const resetBtn = document.getElementById('report-reset');
        const salesForm = document.getElementById('sales-filters');
        const salesResetBtn = document.getElementById('sales-reset');
        const salesGroupEl = document.getElementById('sales_group');
        const salesStartEl = document.getElementById('sales_start_date');
        const salesEndEl = document.getElementById('sales_end_date');
        const salesInsightsEl = document.getElementById('sales-insights');
        const salesChartCanvas = document.getElementById('salesChart');
        const loadingEl = document.getElementById('report-loading');
        const tbody = document.getElementById('participants-tbody');
        const metaEl = document.getElementById('participants-meta');
        const paginationEl = document.getElementById('participants-pagination');
        const couponTbody = document.getElementById('coupon-tbody');

        const statTotal = document.getElementById('stat-total');
        const statSold = document.getElementById('stat-sold');
        const statPending = document.getElementById('stat-pending');
        const statRemaining = document.getElementById('stat-remaining');
        const statPicked = document.getElementById('stat-picked');
        const statUnpicked = document.getElementById('stat-unpicked');
        
        window.currentCouponReport = @json($couponReport ?? null);
        let currentCouponText = '';
        let shouldShowCouponModal = false;
        const initialSales = @json($sales ?? null);
        let salesChart = null;

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        function csrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        function setLoading(isLoading) {
            if (!loadingEl) return;
            loadingEl.classList.toggle('hidden', !isLoading);
            loadingEl.classList.toggle('flex', isLoading);
        }

        function formatNumber(n) {
            const num = Number(n || 0);
            return num.toLocaleString('id-ID');
        }

        function formatCurrency(n) {
            const num = Number(n || 0);
            return num.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        }

        function formatDateTime(value) {
            if (!value) return '-';
            try {
                const d = new Date(value);
                return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return value;
            }
        }

        function serializeForm() {
            const fd = new FormData(form);
            const obj = {};
            for (const [k, v] of fd.entries()) {
                obj[k] = typeof v === 'string' ? v.trim() : v;
            }
            return obj;
        }

        function serializeSales() {
            return {
                sales_group: salesGroupEl ? salesGroupEl.value : '',
                sales_start_date: salesStartEl ? salesStartEl.value : '',
                sales_end_date: salesEndEl ? salesEndEl.value : '',
            };
        }

        function serializeAll() {
            return Object.assign({}, serializeForm(), serializeSales());
        }

        function setSalesInsights(sales) {
            if (!salesInsightsEl) return;
            const totals = sales && sales.totals ? sales.totals : { paid: 0, pending: 0, total: 0 };
            const paid = Number(totals.paid || 0);
            const pending = Number(totals.pending || 0);
            const total = Number(totals.total || 0);
            const conversion = total > 0 ? (paid / total) * 100 : 0;

            const days = (sales && Array.isArray(sales.labels)) ? sales.labels.length : 0;
            const avgPaid = days > 0 ? (paid / days) : 0;

            let bestVal = 0;
            let bestLabel = '-';
            if (sales && sales.series && Array.isArray(sales.series.paid) && Array.isArray(sales.labels)) {
                sales.series.paid.forEach((v, idx) => {
                    const num = Number(v || 0);
                    if (num > bestVal) {
                        bestVal = num;
                        bestLabel = sales.labels[idx] || '-';
                    }
                });
            }

            salesInsightsEl.innerHTML = [
                `<div class="flex items-center justify-between"><span class="text-slate-400">Paid</span><span class="font-mono font-bold text-emerald-400">${formatNumber(paid)}</span></div>`,
                `<div class="flex items-center justify-between"><span class="text-slate-400">Pending</span><span class="font-mono font-bold text-amber-400">${formatNumber(pending)}</span></div>`,
                `<div class="flex items-center justify-between"><span class="text-slate-400">Konversi</span><span class="font-mono font-bold text-white">${conversion.toFixed(1)}%</span></div>`,
                `<div class="flex items-center justify-between"><span class="text-slate-400">Rata-rata/hari</span><span class="font-mono font-bold text-white">${avgPaid.toFixed(1)}</span></div>`,
                bestVal ? `<div class="pt-2 border-t border-slate-800 text-[11px] text-slate-400">Puncak: <strong class="text-white">${formatNumber(bestVal)}</strong> (${bestLabel})</div>` : '',
            ].filter(Boolean).join('');
        }

        function renderSalesChart(sales) {
            if (!salesChartCanvas || typeof Chart === 'undefined') return;

            const isLight = document.body.classList.contains('theme-light');
            const labels = sales && Array.isArray(sales.labels) ? sales.labels : [];
            const series = sales && sales.series ? sales.series : {};
            const paid = Array.isArray(series.paid) ? series.paid : [];
            const pending = Array.isArray(series.pending) ? series.pending : [];

            const datasetPaid = {
                label: 'Paid',
                data: paid,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.15)',
                tension: 0.25,
                fill: true,
                pointRadius: 2,
            };

            const datasetPending = {
                label: 'Pending',
                data: pending,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.12)',
                tension: 0.25,
                fill: true,
                pointRadius: 2,
            };

            if (!salesChart) {
                salesChart = new Chart(salesChartCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [datasetPaid, datasetPending],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: isLight ? '#0f172a' : '#e2e8f0' }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: isLight ? '#475569' : '#94a3b8', maxRotation: 0, autoSkip: true },
                                grid: { color: isLight ? 'rgba(0,0,0,0.06)' : 'rgba(148, 163, 184, 0.15)' },
                            },
                            y: {
                                ticks: { color: isLight ? '#475569' : '#94a3b8' },
                                grid: { color: isLight ? 'rgba(0,0,0,0.06)' : 'rgba(148, 163, 184, 0.15)' },
                                beginAtZero: true,
                            }
                        }
                    }
                });
            } else {
                salesChart.data.labels = labels;
                salesChart.data.datasets = [datasetPaid, datasetPending];
                updateSalesChartTheme(isLight);
            }

            setSalesInsights(sales);
        }

        function paymentPill(status, couponCode, finalAmount, participantId) {
            const text = (status || '').toString().toLowerCase();
            const statuses = ['paid', 'pending', 'cod', 'failed', 'expired', 'cancelled'];
            let options = statuses.map(s => `<option value="${s}" ${s === text ? 'selected' : ''}>${s.toUpperCase()}</option>`).join('');
            
            let html = `<select onchange="updatePaymentStatus(this, ${participantId}, this.value)" class="bg-slate-950 border border-slate-800 text-xs font-semibold rounded-md px-2 py-1 text-white focus:outline-none focus:border-slate-600 cursor-pointer">${options}</select>`;
            if (couponCode) {
                html += `<div class="mt-0.5 text-[10px] text-amber-400 font-mono" title="Kupon dipakai">Kupon: ${couponCode}</div>`;
                html += `<div class="text-[10px] text-slate-400">Rp ${formatCurrency(finalAmount)}</div>`;
            }
            return html;
        }

        function renderParticipants(paginator) {
            const rows = (paginator && paginator.data) ? paginator.data : [];
            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="10" class="px-4 py-8 text-center text-slate-400">Tidak ada data peserta yang cocok.</td></tr>`;
            } else {
                tbody.innerHTML = rows.map((p) => {
                    const addons = Array.isArray(p.addons) ? p.addons : [];
                    const addonsHtml = addons.length
                        ? `<span class="inline-flex flex-wrap gap-1 justify-end md:justify-start">${
                            addons.map((a) => {
                                const name = (a && (a.name || a['name'])) || '-';
                                return `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-200">${name}</span>`;
                            }).join('')
                        }</span>`
                        : `<span class="text-slate-500">-</span>`;

                    const jsonP = JSON.stringify(p).replace(/"/g, '&quot;');
                    let bib = p.bib_number || '-';
                    if (bib && bib.includes('-')) {
                        const parts = bib.split('-');
                        bib = parts[parts.length - 1];
                    }

                    return `
                        <tr class="participant-row hover:bg-slate-900/40 cursor-pointer" onclick="if(!event.target.closest('button') && !event.target.closest('a') && !event.target.closest('select') && !event.target.closest('.no-click')) openDetailModalFromRow(this)" data-json="${jsonP}">
                            <td class="px-3 py-2 font-semibold text-white">
                                <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Nama</span>
                                <span class="cell-value text-right md:text-left font-bold text-white text-xs sm:text-sm">${(p.name || '-')}</span>
                            </td>
                            <td class="px-3 py-2 text-slate-200">
                                <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Email</span>
                                <span class="cell-value text-right md:text-left break-all text-xs text-slate-300">${(p.email || '-')}</span>
                            </td>
                            <td class="px-3 py-2 text-slate-300">
                                <span class="mobile-label text-slate-400 font-bold text-xs uppercase">No Telp</span>
                                <span class="cell-value text-right md:text-left font-mono text-xs">${(p.phone || '-')}</span>
                            </td>
                            <td class="px-3 py-2 text-slate-300">
                                <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Jersey</span>
                                <span class="cell-value text-right md:text-left font-mono font-bold text-xs text-slate-200">${(p.jersey_size || '-')}</span>
                            </td>
                            <td class="px-3 py-2 text-slate-300">
                                <span class="mobile-label text-slate-400 font-bold text-xs uppercase">No BIB</span>
                                <span class="cell-value text-right md:text-left font-mono font-bold text-xs text-white">#${bib}</span>
                            </td>
                            <td class="px-3 py-2 text-slate-200">
                                <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Addons</span>
                                <span class="cell-value text-right md:text-left">${addonsHtml}</span>
                            </td>
                            <td class="px-3 py-2 text-slate-300">
                                <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Tgl Reg</span>
                                <span class="cell-value text-right md:text-left text-xs font-mono text-slate-400">${formatDateTime(p.created_at)}</span>
                            </td>
                            <td class="px-3 py-2 no-click">
                                <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Status</span>
                                <div class="cell-value text-right md:text-left">${paymentPill(p.payment_status, p.coupon_code, p.final_amount, p.id)}</div>
                            </td>
                            <td class="px-3 py-2 text-center no-click">
                                <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Picked Up</span>
                                <div class="cell-value text-right md:text-center">
                                    <button type="button" 
                                        onclick="togglePickup(this, ${p.id}, ${p.is_picked_up ? 'true' : 'false'})"
                                        class="px-2 py-1 text-xs rounded-md font-semibold border transition duration-150 ${p.is_picked_up ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/50' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700'}">
                                        ${p.is_picked_up ? 'Picked Up' : 'Not Picked'}
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-2 no-click">
                                <span class="mobile-label text-slate-400 font-bold text-xs uppercase">Aksi</span>
                                <div class="cell-value text-right md:text-left">
                                    <button type="button" 
                                        onclick="openDetailModalFromRow(this.closest('tr'))"
                                        class="px-2.5 py-1 text-xs rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold transition">
                                        Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            metaEl.textContent = `Menampilkan ${formatNumber(paginator.to || 0)} dari ${formatNumber(paginator.total || 0)} peserta`;
        }

        function renderPagination(paginator, currentPayload) {
            const current = Number(paginator.current_page || 1);
            const last = Number(paginator.last_page || 1);
            if (last <= 1) {
                paginationEl.innerHTML = '';
                return;
            }

            const pages = [];
            const pushPage = (p) => pages.push(p);

            pushPage(1);
            if (current - 2 > 2) pushPage('…');
            for (let p = Math.max(2, current - 2); p <= Math.min(last - 1, current + 2); p++) pushPage(p);
            if (current + 2 < last - 1) pushPage('…');
            pushPage(last);

            paginationEl.innerHTML = pages.map((p) => {
                if (p === '…') return `<span class="px-2.5 py-1 text-xs text-slate-500">…</span>`;
                const active = p === current;
                const cls = active
                    ? 'px-2.5 py-1 text-xs font-bold rounded-md bg-slate-800 text-white border border-slate-700'
                    : 'px-2.5 py-1 text-xs font-medium rounded-md bg-slate-900 text-slate-300 hover:bg-slate-800 border border-slate-800';
                return `<button type="button" class="${cls}" data-page="${p}">${p}</button>`;
            }).join('');

            paginationEl.querySelectorAll('button[data-page]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const page = Number(btn.getAttribute('data-page'));
                    if (!page || page === current) return;
                    const payload = Object.assign({}, currentPayload, { page });
                    fetchReport(payload);
                });
            });
        }

        function renderCoupons(coupons) {
            if (!couponTbody) return;
            const list = Array.isArray(coupons) ? coupons : [];
            if (!list.length) {
                couponTbody.innerHTML = `<tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">Belum ada kupon yang terpakai.</td></tr>`;
                return;
            }

            couponTbody.innerHTML = list.map((c) => {
                return `
                    <tr class="hover:bg-slate-900/40 block md:table-row border-b border-slate-800 md:border-none mb-3 md:mb-0 bg-slate-950/20 md:bg-transparent rounded-md md:rounded-none p-3 md:p-0">
                        <td class="px-4 py-2 md:py-2.5 font-mono font-bold text-white block md:table-cell flex justify-between items-center md:block">
                            <span class="md:hidden text-slate-400 font-bold text-xs uppercase">Kode</span>
                            <span class="text-right md:text-left">${c.code}</span>
                        </td>
                        <td class="px-4 py-2 md:py-2.5 text-slate-200 block md:table-cell flex justify-between items-center md:block text-right">
                            <span class="md:hidden text-slate-400 font-bold text-xs uppercase text-left">Dipakai</span>
                            <span>${formatNumber(c.total_transactions)} kali</span>
                        </td>
                        <td class="px-4 py-2 md:py-2.5 text-slate-200 block md:table-cell flex justify-between items-center md:block text-right">
                            <span class="md:hidden text-slate-400 font-bold text-xs uppercase text-left">Total Diskon</span>
                            <span>Rp ${formatCurrency(c.total_discount)}</span>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderStats(report) {
            if (!report) return;
            if (statTotal && report.total_slots !== undefined) {
                statTotal.textContent = (typeof report.total_slots === 'string') ? report.total_slots : formatNumber(report.total_slots);
            }
            if (statSold && report.sold_slots !== undefined) {
                statSold.textContent = formatNumber(report.sold_slots);
            }
            if (statPending && report.pending_slots !== undefined) {
                statPending.textContent = formatNumber(report.pending_slots);
            }
            if (statRemaining && report.remaining_slots !== undefined) {
                statRemaining.textContent = (typeof report.remaining_slots === 'string') ? report.remaining_slots : formatNumber(report.remaining_slots);
            }
            if (report.pickup) {
                if (statPicked && report.pickup.picked_up !== undefined) {
                    statPicked.textContent = formatNumber(report.pickup.picked_up);
                }
                if (statUnpicked && report.pickup.not_picked_up !== undefined) {
                    statUnpicked.textContent = formatNumber(report.pickup.not_picked_up);
                }
            }

            if (report.jersey_sizes) {
                const sizes = ['XXS','XS','S','M','L','XL','2XL','3XL','4XL','5XL'];
                let totUsed = 0;
                sizes.forEach(sz => {
                    let u = Number(report.jersey_sizes[sz] || report.jersey_sizes[sz.toLowerCase()] || 0);
                    if (sz === '2XL') u += Number(report.jersey_sizes['XXL'] || report.jersey_sizes['xxl'] || 0);
                    if (sz === '3XL') u += Number(report.jersey_sizes['XXXL'] || report.jersey_sizes['xxxl'] || 0);
                    totUsed += u;

                    const uEl = document.getElementById('stat-jersey-' + sz);
                    if (uEl) uEl.textContent = formatNumber(u);

                    const qEl = document.getElementById('stat-jersey-quota-' + sz);
                    const sEl = document.getElementById('stat-jersey-sisa-' + sz);
                    if (qEl && sEl && report.jersey_stock_quotas && report.jersey_stock_quotas[sz] !== undefined) {
                        const q = Number(report.jersey_stock_quotas[sz]);
                        const s = Math.max(0, q - u);
                        sEl.textContent = s;
                        if (s === 0) {
                            sEl.className = 'text-xs font-mono font-bold text-rose-400';
                        } else if (s <= 5) {
                            sEl.className = 'text-xs font-mono font-bold text-amber-400';
                        } else {
                            sEl.className = 'text-xs font-mono font-bold text-emerald-400';
                        }
                    }
                });
                const totUsedEl = document.getElementById('stat-jersey-total-used');
                if (totUsedEl) totUsedEl.textContent = formatNumber(totUsed);
            }
        }

        async function fetchReport(payload) {
            setLoading(true);
            const params = new URLSearchParams();
            Object.keys(payload || {}).forEach((k) => {
                const v = payload[k];
                if (v !== null && v !== undefined && String(v) !== '') {
                    params.set(k, String(v));
                }
            });

            try {
                const res = await fetch("{{ route('report.show', $event) }}?" + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                if (!res.ok) throw new Error('Gagal memuat laporan');
                const data = await res.json();
                renderStats(data.report);
                renderParticipants(data.participants);
                renderPagination(data.participants, payload);
                renderCoupons(data.coupon_usage);
                renderSalesChart(data.sales);

                if (data.coupon_report) {
                    window.currentCouponReport = data.coupon_report;
                    if (shouldShowCouponModal) {
                        openCouponReportModal(currentCouponText, data.coupon_report);
                        shouldShowCouponModal = false;
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                setLoading(false);
            }
        }

        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const payload = serializeAll();
                payload.page = 1;
                fetchReport(payload);
            });

            form.querySelectorAll('select, input[type="date"]').forEach((el) => {
                el.addEventListener('change', () => {
                    const couponSelect = form.querySelector('select[name="coupon_id"]');
                    const btnReport = document.getElementById('btn-show-coupon-report');
                    if (couponSelect && btnReport) {
                        const val = couponSelect.value;
                        if (val && val !== 'without') {
                            btnReport.classList.remove('hidden');
                        } else {
                            btnReport.classList.add('hidden');
                        }
                    }

                    if (el.name === 'coupon_id') {
                        const val = el.value;
                        if (val && val !== 'without') {
                            shouldShowCouponModal = true;
                            currentCouponText = el.options[el.selectedIndex]?.text || val;
                        }
                    }

                    const payload = serializeAll();
                    payload.page = 1;
                    fetchReport(payload);
                });
            });

            const searchInp = form.querySelector('input[name="search"]');
            if (searchInp) {
                searchInp.addEventListener('input', debounce(() => {
                    const payload = serializeAll();
                    payload.page = 1;
                    fetchReport(payload);
                }, 350));
            }
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                if (form) {
                    form.reset();
                    const btnReport = document.getElementById('btn-show-coupon-report');
                    if (btnReport) btnReport.classList.add('hidden');
                }
                const payload = serializeAll();
                payload.page = 1;
                fetchReport(payload);
            });
        }

        if (salesForm) {
            salesForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const payload = serializeAll();
                fetchReport(payload);
            });
        }

        if (salesResetBtn) {
            salesResetBtn.addEventListener('click', () => {
                if (salesGroupEl) salesGroupEl.value = 'day';
                if (salesStartEl) salesStartEl.value = '';
                if (salesEndEl) salesEndEl.value = '';
                const payload = serializeAll();
                fetchReport(payload);
            });
        }

        // Coupon Report Modal Logic
        window.triggerManualCouponReport = function() {
            const couponSelect = form ? form.querySelector('select[name="coupon_id"]') : null;
            if (couponSelect && couponSelect.value && couponSelect.value !== 'without') {
                const text = couponSelect.options[couponSelect.selectedIndex]?.text || couponSelect.value;
                if (window.currentCouponReport) {
                    openCouponReportModal(text, window.currentCouponReport);
                } else {
                    shouldShowCouponModal = true;
                    currentCouponText = text;
                    const payload = serializeAll();
                    payload.page = 1;
                    fetchReport(payload);
                }
            }
        };

        window.openCouponReportModal = function(couponName, reportData) {
            const modal = document.getElementById('coupon-report-modal');
            const subtitle = document.getElementById('coupon-modal-subtitle');
            const summaryContainer = document.getElementById('coupon-jersey-summary');
            const tbody = document.getElementById('coupon-participants-tbody');
            if (!modal) return;

            if (subtitle) subtitle.textContent = 'Kupon: ' + couponName;

            if (summaryContainer && reportData.jersey_totals) {
                summaryContainer.innerHTML = Object.entries(reportData.jersey_totals).map(([size, count]) => {
                    return `<span class="px-2 py-1 bg-slate-800 text-slate-200 border border-slate-700 rounded-md font-mono font-bold text-xs">${size}: ${count}</span>`;
                }).join('');
            }

            renderCouponModalTable(reportData.participants || []);
            modal.classList.remove('hidden');
        };

        window.closeCouponReportModal = function() {
            const modal = document.getElementById('coupon-report-modal');
            if (modal) modal.classList.add('hidden');
        };

        window.filterCouponModalTable = function() {
            if (!window.currentCouponReport || !window.currentCouponReport.participants) return;
            const val = document.getElementById('couponModalFilterPickup')?.value || 'all';
            let list = window.currentCouponReport.participants;
            if (val === '1') {
                list = list.filter(p => p.is_picked_up);
            } else if (val === '0') {
                list = list.filter(p => !p.is_picked_up);
            }
            renderCouponModalTable(list);
        };

        function renderCouponModalTable(participants) {
            const tbody = document.getElementById('coupon-participants-tbody');
            if (!tbody) return;

            if (!participants.length) {
                tbody.innerHTML = `<tr><td colspan="4" class="px-3 py-4 text-center text-slate-500">Tidak ada data.</td></tr>`;
                return;
            }

            tbody.innerHTML = participants.map(p => {
                const pickedBadge = p.is_picked_up 
                    ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-950/40 text-emerald-400 border border-emerald-500/30">Picked Up</span>`
                    : `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">Not Picked</span>`;

                return `
                    <tr class="hover:bg-slate-900/30">
                        <td class="px-3 py-2 font-medium text-white">${p.name}</td>
                        <td class="px-3 py-2 font-mono text-white font-bold">#${p.bib}</td>
                        <td class="px-3 py-2 font-mono">${p.jersey_size}</td>
                        <td class="px-3 py-2">${pickedBadge}</td>
                    </tr>
                `;
            }).join('');
        }

        // Doorprize Draw System
        window.doorprizeParticipants = [];
        window.doorprizeInterval = null;
        window.doorprizeIsSpinning = false;
        window.doorprizeWinners = [];

        window.toggleDoorprizeFullscreen = function() {
            const card = document.getElementById('doorprizeModalCard');
            if (!card) return;
            
            if (!document.fullscreenElement) {
                card.requestFullscreen().catch(err => {
                    alert(`Gagal mengaktifkan mode Fullscreen: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        };

        document.addEventListener('fullscreenchange', () => {
            const icon = document.getElementById('fullscreenIcon');
            if (!icon) return;
            if (document.fullscreenElement) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h4v4M20 4h-4v4M4 20h4v-4M20 20h-4v-4" />';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4" />';
            }
        });

        window.openDoorprizeModal = function() {
            document.getElementById('doorprizeModal').classList.remove('hidden');
            renderDoorprizeWinnersList();
            
            const btnStart = document.getElementById('btnStartDoorprize');
            const totalPaidEl = document.getElementById('doorprizeTotalPaid');
            if (btnStart) {
                btnStart.disabled = true;
                btnStart.classList.add('opacity-50', 'cursor-not-allowed');
                btnStart.innerHTML = `<span>Memuat Data...</span>`;
            }
            if (totalPaidEl) totalPaidEl.textContent = 'Memuat...';
            
            const filters = typeof serializeAll === 'function' ? serializeAll() : {};
            const url = new URL("{{ route('report.doorprize-list', $event) }}", window.location.origin);
            Object.keys(filters).forEach(key => {
                if (filters[key] !== null && filters[key] !== undefined && filters[key] !== '') {
                    url.searchParams.set(key, filters[key]);
                }
            });
            
            fetch(url.toString())
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        window.doorprizeParticipants = res.data || [];
                        if (totalPaidEl) totalPaidEl.textContent = window.doorprizeParticipants.length;
                        
                        if (window.doorprizeParticipants.length > 0) {
                            if (btnStart) {
                                btnStart.disabled = false;
                                btnStart.classList.remove('opacity-50', 'cursor-not-allowed');
                                btnStart.innerHTML = `<span>Mulai Undian</span>`;
                            }
                        } else {
                            if (btnStart) {
                                btnStart.innerHTML = `<span>Tidak ada data Paid</span>`;
                            }
                        }
                    } else {
                        alert('Gagal mengambil data peserta: ' + (res.message || 'Error'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal mengambil data peserta.');
                });
        };

        window.closeDoorprizeModal = function() {
            if (window.doorprizeIsSpinning) {
                window.stopDoorprizeDraw();
            }
            document.getElementById('doorprizeModal').classList.add('hidden');
        };

        window.startDoorprizeDraw = function() {
            if (window.doorprizeIsSpinning) return;
            
            let pool = [...window.doorprizeParticipants];
            const excludeWinners = document.getElementById('doorprizeExcludeWinners').checked;
            
            if (excludeWinners) {
                const winnersKeys = getDoorprizeWinnersFromStorage().map(w => w.id);
                pool = pool.filter(p => !winnersKeys.includes(p.id));
            }
            
            if (pool.length === 0) {
                alert('Semua peserta paid sudah terpilih atau kolam undian kosong!');
                return;
            }
            
            const drawNameInput = document.getElementById('doorprizeDrawName');
            const drawName = drawNameInput ? drawNameInput.value.trim() : '';
            if (!drawName) {
                alert('Silakan masukkan nama undian terlebih dahulu.');
                if (drawNameInput) drawNameInput.focus();
                return;
            }
            
            window.doorprizeIsSpinning = true;
            
            document.getElementById('doorprizePlaceholder').classList.add('hidden');
            document.getElementById('doorprizeWinner').classList.add('hidden');
            document.getElementById('doorprizeLiveSpin').classList.remove('hidden');
            
            document.getElementById('liveDrawName').textContent = drawName;
            
            const board = document.getElementById('doorprizeDrawBoard');
            board.classList.add('border-sky-500');
            board.classList.remove('border-emerald-500');
            
            const btnStart = document.getElementById('btnStartDoorprize');
            const btnStop = document.getElementById('btnStopDoorprize');
            
            btnStart.disabled = true;
            btnStart.classList.add('opacity-50', 'cursor-not-allowed');
            
            btnStop.disabled = false;
            btnStop.classList.remove('bg-slate-800', 'text-slate-500', 'cursor-not-allowed');
            btnStop.classList.add('bg-rose-600', 'hover:bg-rose-500', 'text-white');
            
            const liveBib = document.getElementById('liveBib');
            
            window.doorprizeInterval = setInterval(() => {
                const randIdx = Math.floor(Math.random() * pool.length);
                const candidate = pool[randIdx];
                if (candidate) {
                    const bib = candidate.bib_number || '';
                    const parts = bib.split('-');
                    const lastPart = parts[parts.length - 1] || '';
                    let processedBib = '-';
                    if (lastPart) {
                        processedBib = lastPart.startsWith('0') ? '5' + lastPart.substring(1) : lastPart;
                    }
                    liveBib.textContent = processedBib;
                }
            }, 50);
        };

        window.stopDoorprizeDraw = function() {
            if (!window.doorprizeIsSpinning) return;
            
            clearInterval(window.doorprizeInterval);
            window.doorprizeIsSpinning = false;
            
            let pool = [...window.doorprizeParticipants];
            const excludeWinners = document.getElementById('doorprizeExcludeWinners').checked;
            if (excludeWinners) {
                const winnersKeys = getDoorprizeWinnersFromStorage().map(w => w.id);
                pool = pool.filter(p => !winnersKeys.includes(p.id));
            }
            
            if (pool.length === 0) {
                alert('Undian tidak valid.');
                return;
            }
            
            const finalWinner = pool[Math.floor(Math.random() * pool.length)];
            const drawName = (document.getElementById('doorprizeDrawName')?.value || 'Undian').trim();
            
            document.getElementById('doorprizeLiveSpin').classList.add('hidden');
            document.getElementById('doorprizeWinner').classList.remove('hidden');
            
            const board = document.getElementById('doorprizeDrawBoard');
            board.classList.remove('border-sky-500');
            board.classList.add('border-emerald-500');
            
            document.getElementById('winnerDrawName').textContent = drawName;
            
            const bib = finalWinner.bib_number || '';
            const parts = bib.split('-');
            const lastPart = parts[parts.length - 1] || '';
            let processedBib = '-';
            if (lastPart) {
                processedBib = lastPart.startsWith('0') ? '5' + lastPart.substring(1) : lastPart;
            }
            document.getElementById('winnerBib').textContent = processedBib;
            
            const btnStart = document.getElementById('btnStartDoorprize');
            const btnStop = document.getElementById('btnStopDoorprize');
            
            btnStart.disabled = false;
            btnStart.classList.remove('opacity-50', 'cursor-not-allowed');
            btnStart.innerHTML = `<span>Mulai Undian</span>`;
            
            btnStop.disabled = true;
            btnStop.classList.remove('bg-rose-600', 'hover:bg-rose-500', 'text-white');
            btnStop.classList.add('bg-slate-800', 'text-slate-500', 'cursor-not-allowed');
            
            saveWinnerToStorage(finalWinner, drawName);
            renderDoorprizeWinnersList();
        };

        function getDoorprizeWinnersFromStorage() {
            try {
                return JSON.parse(localStorage.getItem('doorprize_winners_' + '{{ $event->id }}') || '[]');
            } catch(e) {
                return [];
            }
        }

        function saveWinnerToStorage(winner, drawName) {
            const list = getDoorprizeWinnersFromStorage();
            list.push({
                id: winner.id,
                bib_number: winner.bib_number,
                name: winner.name,
                phone: winner.phone || '-',
                address: winner.address || '-',
                draw_name: drawName,
                drawn_at: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            });
            try {
                localStorage.setItem('doorprize_winners_' + '{{ $event->id }}', JSON.stringify(list));
            } catch(e) {}
        }

        window.clearDoorprizeWinners = function() {
            if (!confirm('Apakah Anda yakin ingin menghapus seluruh daftar pemenang doorprize?')) return;
            localStorage.removeItem('doorprize_winners_' + '{{ $event->id }}');
            renderDoorprizeWinnersList();
        };

        function renderDoorprizeWinnersList() {
            const container = document.getElementById('doorprizeWinnerList');
            if (!container) return;
            const list = getDoorprizeWinnersFromStorage();
            
            if (list.length === 0) {
                container.innerHTML = '<div class="text-xs text-slate-500 text-center py-8">Belum ada pemenang yang ditarik.</div>';
                return;
            }
            
            let html = '';
            list.slice().reverse().forEach((w) => {
                html += `
                <div class="bg-slate-900 border border-slate-800 rounded-md p-2.5 flex flex-col gap-1 transition hover:border-slate-700">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] bg-slate-800 text-slate-200 border border-slate-700 px-1.5 py-0.5 rounded font-bold font-mono">BIB ${w.bib_number}</span>
                        <span class="text-[10px] text-slate-500 font-mono">${w.drawn_at}</span>
                    </div>
                    ${w.draw_name ? `<div class="text-[10px] text-amber-400 font-semibold tracking-wide uppercase">${w.draw_name}</div>` : ''}
                    <div class="text-xs font-bold text-white truncate" title="${w.name}">${w.name}</div>
                    <div class="text-[10px] text-slate-400 flex flex-col gap-0.5 border-t border-slate-800 pt-1">
                        <span class="truncate">Telp: ${w.phone}</span>
                        <span class="truncate" title="${w.address}">Alamat: ${w.address}</span>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }

        window.exportDoorprizeWinners = function() {
            const list = getDoorprizeWinnersFromStorage();
            if (list.length === 0) {
                alert('Belum ada pemenang untuk diunduh.');
                return;
            }
            
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "No,Draw Name,BIB Number,Name,Phone,Address,Drawn At\n";
            
            list.forEach((w, idx) => {
                const row = [
                    idx + 1,
                    `"${(w.draw_name || '').replace(/"/g, '""')}"`,
                    `"${w.bib_number}"`,
                    `"${w.name.replace(/"/g, '""')}"`,
                    `"${w.phone}"`,
                    `"${w.address.replace(/"/g, '""')}"`,
                    `"${w.drawn_at}"`
                ].join(",");
                csvContent += row + "\n";
            });
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "Doorprize_Winners_" + "{{ Str::slug($event->name) }}" + "_" + new Date().toISOString().slice(0,10) + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

        window.toggleReportFilters = function() {
            const form = document.getElementById('report-filters');
            const icon = document.getElementById('toggle-filters-icon');
            const text = document.getElementById('toggle-filters-text');
            if (!form) return;
            const isHidden = form.classList.contains('hidden');
            if (isHidden) {
                form.classList.remove('hidden');
                if (icon) icon.className = 'fa-solid fa-chevron-up text-xs';
                if (text) text.textContent = 'Sembunyikan Filter';
                localStorage.setItem('reportFiltersCollapsed', '0');
            } else {
                form.classList.add('hidden');
                if (icon) icon.className = 'fa-solid fa-chevron-down text-xs';
                if (text) text.textContent = 'Tampilkan Filter';
                localStorage.setItem('reportFiltersCollapsed', '1');
            }
        };

        window.updatePaymentStatus = function(selectEl, participantId, newStatus) {
            if (selectEl) selectEl.disabled = true;
            const eventId = "{{ $event->id }}";
            const url = `/reports/${eventId}/participants/${participantId}/status`;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    payment_status: newStatus
                })
            })
            .then(r => {
                if (!r.ok) return r.json().then(err => { throw err; });
                return r.json();
            })
            .then(data => {
                if (selectEl) selectEl.disabled = false;
                if (data.success) {
                    const filterForm = document.getElementById('report-filters');
                    if (filterForm) {
                        filterForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                    }
                } else {
                    alert(data.message || 'Gagal mengubah status pembayaran.');
                }
            })
            .catch(err => {
                if (selectEl) selectEl.disabled = false;
                alert(err.message || 'Terjadi kesalahan sistem');
            });
        };

        if (localStorage.getItem('reportFiltersCollapsed') === '1') {
            window.toggleReportFilters();
        }

        // Quick Search Synchronization
        const quickSearchInp = document.getElementById('quick-search-input');
        const mainSearchInp = form ? form.querySelector('input[name="search"]') : null;
        const quickClearBtn = document.getElementById('quick-search-clear');

        if (quickSearchInp && mainSearchInp) {
            quickSearchInp.addEventListener('input', debounce(() => {
                mainSearchInp.value = quickSearchInp.value;
                if (quickClearBtn) {
                    if (quickSearchInp.value.trim().length > 0) {
                        quickClearBtn.classList.remove('hidden');
                    } else {
                        quickClearBtn.classList.add('hidden');
                    }
                }
                const payload = serializeAll();
                payload.page = 1;
                fetchReport(payload);
            }, 350));

            mainSearchInp.addEventListener('input', () => {
                quickSearchInp.value = mainSearchInp.value;
                if (quickClearBtn) {
                    if (mainSearchInp.value.trim().length > 0) {
                        quickClearBtn.classList.remove('hidden');
                    } else {
                        quickClearBtn.classList.add('hidden');
                    }
                }
            });
        }

        window.clearQuickSearch = function() {
            if (quickSearchInp) quickSearchInp.value = '';
            if (mainSearchInp) mainSearchInp.value = '';
            if (quickClearBtn) quickClearBtn.classList.add('hidden');
            const payload = serializeAll();
            payload.page = 1;
            fetchReport(payload);
        };

        const initialParticipants = @json($participants);
        renderPagination(initialParticipants, serializeAll());
        renderSalesChart(initialSales);
    })();
</script>
@endpush