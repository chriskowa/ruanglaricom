@extends('layouts.pacerhub')

@section('title', 'Report Event | Ruang Lari')

@push('styles')
<meta name="robots" content="noindex,nofollow,noarchive">
<style>
    @keyframes bounceShort {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    .animate-bounce-short {
        animation: bounceShort 0.6s ease-in-out 2;
    }
    .glow-blue {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
    }
    .glow-green {
        border-color: #22c55e !important;
        box-shadow: 0 0 25px rgba(34, 197, 94, 0.5);
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
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="text-xs text-slate-400 font-mono">/report/{{ $event->id }}</div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $event->name }}</h1>
            <div class="text-sm text-slate-300">
                <span class="font-mono">#{{ $event->id }}</span>
                @if($event->start_at)
                    <span class="mx-2 text-slate-600">•</span>
                    <span>{{ $event->start_at->format('d M Y H:i') }}</span>
                @endif
            </div>
        </div>
        <div class="text-xs text-slate-400">
            Halaman ini bersifat privat (tidak untuk diindeks).
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-card border border-slate-700 rounded-2xl p-4">
            <div class="text-xs text-slate-400">Total Slot</div>
            <div id="stat-total" class="text-2xl font-extrabold">
                {{ is_string($report['total_slots'] ?? null) ? $report['total_slots'] : number_format((int) ($report['total_slots'] ?? 0)) }}
            </div>
        </div>
        <div class="bg-card border border-slate-700 rounded-2xl p-4">
            <div class="text-xs text-slate-400">Sold (Paid)</div>
            <div id="stat-sold" class="text-2xl font-extrabold">{{ number_format((int) ($report['sold_slots'] ?? 0)) }}</div>
        </div>
        <div class="bg-card border border-slate-700 rounded-2xl p-4">
            <div class="text-xs text-slate-400">Pending</div>
            <div id="stat-pending" class="text-2xl font-extrabold">{{ number_format((int) ($report['pending_slots'] ?? 0)) }}</div>
        </div>
        <div class="bg-card border border-slate-700 rounded-2xl p-4">
            <div class="text-xs text-slate-400">Sisa Slot</div>
            <div id="stat-remaining" class="text-2xl font-extrabold">
                {{ is_string($report['remaining_slots'] ?? null) ? $report['remaining_slots'] : number_format((int) ($report['remaining_slots'] ?? 0)) }}
            </div>
            @if(($report['show_warning'] ?? false) === true)
                <div class="mt-2 text-xs text-yellow-300">Sisa slot &lt; 10%</div>
            @endif
        </div>
        <div class="bg-card border border-slate-700 rounded-2xl p-4">
            <div class="text-xs text-slate-400">Picked Up</div>
            <div id="stat-picked" class="text-2xl font-extrabold text-emerald-400">
                {{ number_format((int) ($report['pickup']['picked_up'] ?? 0)) }}
            </div>
        </div>
        <div class="bg-card border border-slate-700 rounded-2xl p-4">
            <div class="text-xs text-slate-400">Belum Diambil</div>
            <div id="stat-unpicked" class="text-2xl font-extrabold text-slate-400">
                {{ number_format((int) ($report['pickup']['not_picked_up'] ?? 0)) }}
            </div>
        </div>
    </div>

    <div class="mt-4 bg-card border border-slate-700 rounded-2xl p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-lg font-bold">Penjualan Slot</div>
                <div class="text-xs text-slate-400">Trend paid vs pending</div>
            </div>
            <form id="sales-filters" class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase">Periode</label>
                    <select id="sales_group" name="sales_group" class="mt-1 rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        <option value="day" @selected(($filters['sales_group'] ?? 'day') === 'day')>Harian</option>
                        <option value="month" @selected(($filters['sales_group'] ?? 'day') === 'month')>Bulanan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase">Mulai</label>
                    <input id="sales_start_date" type="date" name="sales_start_date" value="{{ $filters['sales_start_date'] ?? '' }}" class="mt-1 rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase">Akhir</label>
                    <input id="sales_end_date" type="date" name="sales_end_date" value="{{ $filters['sales_end_date'] ?? '' }}" class="mt-1 rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-neon text-dark font-bold hover:bg-lime-300 transition">Apply</button>
                <button type="button" id="sales-reset" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-200 hover:bg-slate-700 transition">Reset</button>
            </form>
        </div>
        <div class="mt-4 grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-3 border border-slate-700 rounded-2xl bg-slate-900/30 p-3">
                <canvas id="salesChart" height="110"></canvas>
            </div>
            <div class="border border-slate-700 rounded-2xl bg-slate-900/30 p-4">
                <div class="text-xs text-slate-400 font-bold uppercase">Insight</div>
                <div id="sales-insights" class="mt-2 space-y-2 text-sm text-slate-200"></div>
            </div>
        </div>
    </div>

    <div class="mt-4 bg-card border border-slate-700 rounded-2xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg font-bold">Jersey Breakdown</div>
                <div class="text-xs text-slate-400">Stok / Terpakai (paid only) / Sisa per ukuran</div>
            </div>
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
        {{-- Header --}}
        <div class="mt-3 hidden sm:grid grid-cols-4 gap-2 px-2 mb-1">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ukuran</span>
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Stok</span>
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Terpakai</span>
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Sisa</span>
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
                <div class="rounded-xl border {{ $sisa !== null && $sisa == 0 ? 'border-red-500/40 bg-red-900/10' : ($sisa !== null && $sisa <= 5 ? 'border-yellow-500/40 bg-yellow-900/10' : 'border-slate-700 bg-slate-900/30') }} px-3 py-2">
                    <div class="sm:hidden text-xs text-slate-400 font-bold mb-1">{{ $size }}</div>
                    <div class="sm:grid sm:grid-cols-4 sm:gap-2 sm:items-center flex items-center justify-between">
                        <div class="hidden sm:block text-sm font-bold text-slate-300">{{ $size }}</div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 sm:hidden">Stok</div>
                            <div class="text-sm font-mono text-slate-400" id="stat-jersey-quota-{{ $size }}">{{ $quota !== null ? number_format($quota) : '∞' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 sm:hidden">Terpakai</div>
                            <div class="text-sm font-mono font-bold text-white" id="stat-jersey-{{ $size }}">{{ number_format($used) }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 sm:hidden">Sisa</div>
                            <div class="text-sm font-mono font-bold {{ $sisa !== null && $sisa == 0 ? 'text-red-400' : ($sisa !== null && $sisa <= 5 ? 'text-yellow-400' : 'text-emerald-400') }}" id="stat-jersey-sisa-{{ $size }}">
                                {{ $sisa !== null ? $sisa : '∞' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{-- Total --}}
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
        <div class="mt-3 pt-3 border-t border-slate-700 grid grid-cols-4 gap-2 px-2 items-center">
            <span class="text-xs font-bold text-slate-400 uppercase">TOTAL</span>
            <span id="stat-jersey-total-quota" class="text-right text-sm font-mono font-bold text-slate-300">{{ $totalQuota !== null ? number_format($totalQuota) : '∞' }}</span>
            <span id="stat-jersey-total-used" class="text-right text-sm font-mono font-bold text-white">{{ number_format($totalUsed) }}</span>
            <span id="stat-jersey-total-sisa" class="text-right text-sm font-mono font-bold text-emerald-400">{{ $totalSisa !== null ? $totalSisa : '∞' }}</span>
        </div>
    </div>

    <div class="mt-6 space-y-6">
        <div class="bg-card border border-slate-700 rounded-2xl p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-lg font-bold">Data Peserta</div>
                    <div class="text-xs text-slate-400">Filter AJAX • Pagination server-side</div>
                </div>
                <div id="report-loading" class="hidden items-center gap-2 text-xs text-slate-300">
                    <span class="loader"></span>
                    <span>Memuat...</span>
                </div>
            </div>

            <form id="report-filters" class="mt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <div>
                    <label class="text-xs text-slate-300">Search</label>
                    <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, email, HP, BIB, Category, ID Card"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                </div>
                <div>
                    <label class="text-xs text-slate-300">Status Pembayaran</label>
                    <select name="payment_status"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        @php
                            $paymentStatus = $filters['payment_status'] ?? 'all';
                            $paymentOptions = ['all' => 'Semua', 'pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'expired' => 'Expired', 'cod' => 'COD'];
                        @endphp
                        @foreach($paymentOptions as $val => $label)
                            <option value="{{ $val }}" @selected($paymentStatus === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-300">Status Pengambilan</label>
                    <select name="is_picked_up"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        <option value="" @selected(($filters['is_picked_up'] ?? '') === '')>Semua Status</option>
                        <option value="0" @selected(($filters['is_picked_up'] ?? '') === '0')>Belum Diambil</option>
                        <option value="1" @selected(($filters['is_picked_up'] ?? '') === '1')>Sudah Diambil</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-300">Jenis Kelamin</label>
                    <select name="gender"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        <option value="" @selected(($filters['gender'] ?? '') === '')>Semua Gender</option>
                        <option value="male" @selected(($filters['gender'] ?? '') === 'male')>Laki-laki (Male)</option>
                        <option value="female" @selected(($filters['gender'] ?? '') === 'female')>Perempuan (Female)</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-300">Kategori</label>
                    <select name="category_id"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        <option value="">Semua</option>
                        @foreach($event->categories as $cat)
                            <option value="{{ $cat->id }}" @selected((int) ($filters['category_id'] ?? 0) === (int) $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="flex justify-between items-center">
                        <label class="text-xs text-slate-300">Kupon</label>
                        <button type="button" id="btn-show-coupon-report" class="text-[10px] text-neon hover:underline hidden" onclick="triggerManualCouponReport()">
                            Lihat Laporan
                        </button>
                    </div>
                    <select name="coupon_id"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        <option value="" @selected(($filters['coupon_id'] ?? '') === '')>Semua Kupon</option>
                        <option value="without" @selected(($filters['coupon_id'] ?? '') === 'without')>Tanpa Kupon</option>
                        <option value="with" @selected(($filters['coupon_id'] ?? '') === 'with')>Dengan Kupon (Apa Saja)</option>
                        @foreach($coupons as $coupon)
                            <option value="{{ $coupon->id }}" @selected((string)($filters['coupon_id'] ?? '') === (string)$coupon->id)>{{ $coupon->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-300">Add-on</label>
                    <select name="addon"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        <option value="" @selected(($filters['addon'] ?? '') === '')>Semua Add-on</option>
                        <option value="with" @selected(($filters['addon'] ?? '') === 'with')>Ada Add-on (Apa Saja)</option>
                        <option value="without" @selected(($filters['addon'] ?? '') === 'without')>Tanpa Add-on</option>
                        @if(!empty($event->addons) && (is_array($event->addons) || is_object($event->addons)))
                            @foreach($event->addons as $addon)
                                @php 
                                    $addonName = is_array($addon) ? ($addon['name'] ?? null) : (is_object($addon) ? ($addon->name ?? ($addon['name'] ?? null)) : $addon); 
                                @endphp
                                @if($addonName)
                                    <option value="{{ $addonName }}" @selected(($filters['addon'] ?? '') === $addonName)>Hanya: {{ $addonName }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-300">Ukuran Jersey</label>
                    <select name="jersey_size"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        <option value="" @selected(($filters['jersey_size'] ?? '') === '')>Semua Ukuran</option>
                        @foreach(['XXS','XS','S','M','L','XL','2XL','3XL','4XL','5XL'] as $jsz)
                            <option value="{{ $jsz }}" @selected(($filters['jersey_size'] ?? '') === $jsz)>{{ $jsz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-300">Kelompok Umur</label>
                    <select name="age_group"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        <option value="" @selected(($filters['age_group'] ?? '') === '')>Semua Kelompok</option>
                        <option value="Umum" @selected(($filters['age_group'] ?? '') === 'Umum')>Umum (&lt; 40)</option>
                        <option value="Master" @selected(($filters['age_group'] ?? '') === 'Master')>Master (40-44)</option>
                        <option value="Master 45+" @selected(($filters['age_group'] ?? '') === 'Master 45+')>Master 45+ (45-49)</option>
                        <option value="50+" @selected(($filters['age_group'] ?? '') === '50+')>50+ (&gt;= 50)</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-300">Umur Minimum</label>
                    <input type="number" name="min_age" value="{{ $filters['min_age'] ?? '' }}" placeholder="Min" min="1" max="150"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                </div>
                <div>
                    <label class="text-xs text-slate-300">Umur Maksimum</label>
                    <input type="number" name="max_age" value="{{ $filters['max_age'] ?? '' }}" placeholder="Max" min="1" max="150"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                </div>
                <div>
                    <label class="text-xs text-slate-300">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                </div>
                <div>
                    <label class="text-xs text-slate-300">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                </div>
                <div>
                    <label class="text-xs text-slate-300">Per Halaman</label>
                    <select name="per_page"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        @foreach([10,25,50,100] as $pp)
                            <option value="{{ $pp }}" @selected((int) ($filters['per_page'] ?? 25) === $pp)>{{ $pp }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 md:col-span-3 flex flex-wrap items-center gap-2 mt-2">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-neon text-dark font-bold hover:bg-lime-300 transition">
                        Terapkan
                    </button>
                    <button id="report-reset" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-200 hover:bg-slate-700 transition">
                        Reset
                    </button>
                    <button type="button" onclick="openQrScanModal()" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold flex items-center gap-2 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h3v3H7V7zm7 0h3v3h-3V7zM7 14h3v3H7v-3zm7 0h3v3h-3v-3z" /></svg>
                        Scan QR
                    </button>
                    <button type="button" onclick="openDoorprizeModal()" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-500 text-white font-bold flex items-center gap-2 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 0H4m8 0h8m-8 0a2 2 0 102 2h-2zm0 0a2 2 0 11-2 2h2z" /></svg>
                        Doorprize
                    </button>
                    <a id="export-csv-btn" href="#" onclick="window.location.href=getExportUrl('csv'); return false;" class="px-4 py-2 rounded-xl bg-green-600 text-white font-bold hover:bg-green-500 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export CSV
                    </a>
                    <a id="export-xlsx-btn" href="#" onclick="window.location.href=getExportUrl('xlsx'); return false;" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-500 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export XLSX
                    </a>
                </div>
            </form>

            <div class="mt-4 overflow-x-auto border border-slate-700 rounded-2xl">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-900/60 text-slate-300 hidden md:table-header-group">
                        <tr>
                            <th class="text-left font-semibold px-4 py-3">Nama</th>
                            <th class="text-left font-semibold px-4 py-3">Email</th>
                            <th class="text-left font-semibold px-4 py-3">No Telp</th>
                            <th class="text-left font-semibold px-4 py-3">Jersey</th>
                            <th class="text-left font-semibold px-4 py-3">No BIB</th>
                            <th class="text-left font-semibold px-4 py-3">Addons</th>
                            <th class="text-left font-semibold px-4 py-3">Tanggal Registrasi</th>
                            <th class="text-left font-semibold px-4 py-3">Status Pembayaran</th>
                            <th class="text-left font-semibold px-4 py-3 text-center">Picked Up</th>
                            <th class="text-left font-semibold px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="participants-tbody" class="divide-y divide-slate-800">
                        @foreach($participants as $p)
                            <tr class="hover:bg-slate-900/40 cursor-pointer block md:table-row border-b border-slate-800 md:border-none mb-4 md:mb-0 bg-slate-900/20 md:bg-transparent rounded-xl md:rounded-none p-4 md:p-0" onclick="if(!event.target.closest('button') && !event.target.closest('a') && !event.target.closest('.no-click')) openDetailModalFromRow(this)" data-json="{{ json_encode($p) }}">
                                <td class="px-4 py-2 md:py-3 font-semibold text-white block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Nama</span>
                                    <span class="text-right md:text-left">{{ $p->name }}</span>
                                </td>
                                <td class="px-4 py-2 md:py-3 text-slate-200 block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Email</span>
                                    <span class="text-right md:text-left break-all">{{ $p->email }}</span>
                                </td>
                                <td class="px-4 py-2 md:py-3 text-slate-300 block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">No Telp</span>
                                    <span class="text-right md:text-left font-mono">{{ $p->phone ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-2 md:py-3 text-slate-300 block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Jersey</span>
                                    <span class="text-right md:text-left font-mono">{{ $p->jersey_size ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-2 md:py-3 text-slate-300 block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">No BIB</span>
                                    @php
                                        $bib = $p->bib_number;
                                        if ($bib && strpos($bib, '-') !== false) {
                                            $parts = explode('-', $bib);
                                            $bib = end($parts);
                                        }
                                    @endphp
                                    <span class="text-right md:text-left font-mono">{{ $bib ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-2 md:py-3 text-slate-200 block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Addons</span>
                                    @php $addons = is_array($p->addons) ? $p->addons : []; @endphp
                                    <span class="text-right md:text-left">
                                        @if(count($addons) > 0)
                                            <span class="inline-flex flex-wrap gap-1 justify-end md:justify-start">
                                                @foreach($addons as $a)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-800 text-slate-200">
                                                        {{ is_array($a) ? ($a['name'] ?? '-') : ($a->name ?? '-') }}
                                                    </span>
                                                @endforeach
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-2 md:py-3 text-slate-300 block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Tgl Reg</span>
                                    <span class="text-right md:text-left">{{ \Illuminate\Support\Carbon::parse($p->created_at)->format('d M Y H:i') }}</span>
                                </td>
                                <td class="px-4 py-2 md:py-3 block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Status</span>
                                    <span class="text-right md:text-left">
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-800 text-slate-200">
                                            {{ $p->payment_status }}
                                        </span>
                                        @if($p->coupon_code)
                                            <div class="mt-1 text-[10px] text-yellow-400 font-mono" title="Kupon dipakai">
                                                🏷️ {{ $p->coupon_code }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">
                                                Net: Rp {{ number_format((float) $p->final_amount, 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-2 md:py-3 block md:table-cell flex justify-between items-center md:block no-click">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Picked Up</span>
                                    <span class="text-right md:text-left">
                                        <button type="button" 
                                            onclick="togglePickup(this, {{ $p->id }}, {{ $p->is_picked_up ? 'true' : 'false' }})"
                                            class="px-2 py-1 text-xs rounded-lg font-bold border transition duration-200 {{ $p->is_picked_up ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/60' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700' }}">
                                            {{ $p->is_picked_up ? 'Picked Up' : 'Not Picked' }}
                                        </button>
                                    </span>
                                </td>
                                <td class="px-4 py-2 md:py-3 block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Aksi</span>
                                    <span class="text-right md:text-left">
                                        <button type="button" 
                                            onclick="openEditModal({{ json_encode($p) }})"
                                            class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-white text-xs rounded-lg transition">
                                            Edit
                                        </button>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        @if($participants->isEmpty())
                            <tr>
                                <td colspan="10" class="px-4 py-6 text-center text-slate-400">Tidak ada data.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div id="participants-meta" class="text-xs text-slate-400">
                    Menampilkan <span class="font-mono">{{ $participants->count() }}</span> dari <span class="font-mono">{{ $participants->total() }}</span>
                </div>
                <div id="participants-pagination" class="flex flex-wrap gap-2 justify-start sm:justify-end"></div>
            </div>
        </div>

        <div class="bg-card border border-slate-700 rounded-2xl p-4">
            <div class="text-lg font-bold">Kupon Terpakai</div>
            <div class="text-xs text-slate-400">Berdasarkan transaksi paid/pending</div>

            <div class="mt-4 overflow-x-auto border border-slate-700 rounded-2xl">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-900/60 text-slate-300 hidden md:table-header-group">
                        <tr>
                            <th class="text-left font-semibold px-4 py-3">Kode</th>
                            <th class="text-right font-semibold px-4 py-3">Dipakai</th>
                            <th class="text-right font-semibold px-4 py-3">Total Diskon</th>
                        </tr>
                    </thead>
                    <tbody id="coupon-tbody" class="divide-y divide-slate-800">
                        @foreach($couponUsage as $c)
                            <tr class="hover:bg-slate-900/40 block md:table-row border-b border-slate-800 md:border-none mb-4 md:mb-0 bg-slate-900/20 md:bg-transparent rounded-xl md:rounded-none p-4 md:p-0">
                                <td class="px-4 py-2 md:py-3 font-mono font-bold text-white block md:table-cell flex justify-between items-center md:block">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Kode</span>
                                    <span class="text-right md:text-left">{{ $c->code }}</span>
                                </td>
                                <td class="px-4 py-2 md:py-3 text-slate-200 block md:table-cell flex justify-between items-center md:block text-right">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase text-left">Dipakai</span>
                                    <span>{{ number_format((int) $c->total_transactions) }}</span>
                                </td>
                                <td class="px-4 py-2 md:py-3 text-slate-200 block md:table-cell flex justify-between items-center md:block text-right">
                                    <span class="md:hidden text-slate-500 font-bold text-xs uppercase text-left">Total Diskon</span>
                                    <span>{{ number_format((float) $c->total_discount, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @endforeach
                        @if($couponUsage->isEmpty())
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-400">Belum ada kupon terpakai.</td>
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
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity duration-300" onclick="closeDoorprizeModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div id="doorprizeModalCard" class="relative transform overflow-hidden rounded-3xl bg-slate-900 border border-slate-700/80 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl p-6 sm:p-8">
                
                <!-- Action Controls -->
                <div class="absolute top-4 right-4 flex items-center gap-3 z-30">
                    <!-- Fullscreen Toggle Button -->
                    <button type="button" onclick="toggleDoorprizeFullscreen()" class="text-slate-400 hover:text-white transition-colors" title="Toggle Fullscreen">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="fullscreenIcon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4" />
                        </svg>
                    </button>
                    <!-- Close Button -->
                    <button type="button" onclick="closeDoorprizeModal()" class="text-slate-400 hover:text-white transition-colors" title="Close">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Header -->
                <div class="mb-6">
                    <h3 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-200 to-white flex items-center gap-2">
                        🎉 DOORPRIZE RANDOM DRAW
                    </h3>
                    <p class="text-sm text-slate-400 mt-1">Mengundi pemenang secara acak dari semua peserta yang berstatus lunas (Paid) untuk event <strong>{{ $event->name }}</strong>.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Draw Screen (2 Cols) -->
                    <div class="lg:col-span-2 flex flex-col justify-between bg-slate-950/50 rounded-2xl border border-slate-800 p-6 relative overflow-hidden">
                        
                        <!-- Decorative Neon Glows -->
                        <div class="absolute -top-12 -left-12 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                        <!-- Draw Name Input -->
                        <div class="mb-4 relative z-10">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Undian / Hadiah</label>
                            <input type="text" id="doorprizeDrawName" placeholder="Masukkan nama undian (misal: Sepeda Lipat, Helm, Voucher)" class="w-full px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white text-sm focus:outline-none focus:border-blue-500 placeholder-slate-500 transition-colors">
                        </div>

                        <!-- Draw Display Board -->
                        <div class="flex flex-col items-center justify-center min-h-[260px] text-center relative z-10">
                            <!-- Spinning Box -->
                            <div id="doorprizeDrawBoard" class="w-full flex flex-col items-center justify-center p-6 rounded-2xl border border-slate-800 transition-all duration-300">
                                
                                <!-- Placeholder / Init State -->
                                <div id="doorprizePlaceholder" class="text-slate-500 flex flex-col items-center gap-3">
                                    <span class="text-5xl">🎁</span>
                                    <p class="text-sm font-semibold tracking-wide uppercase">Siap untuk memutar doorprize</p>
                                </div>

                                <!-- Live Spin State -->
                                <div id="doorprizeLiveSpin" class="hidden w-full space-y-4">
                                    <div class="text-xs font-bold uppercase tracking-wider text-blue-400" id="liveDrawName"></div>
                                    <div class="text-7xl font-black text-white tracking-widest font-mono select-none" id="liveBib">0</div>
                                    <div class="text-sm text-slate-500 font-medium" id="liveStatus">Memutar data...</div>
                                </div>

                                <!-- Winner State -->
                                <div id="doorprizeWinner" class="hidden w-full space-y-6">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-500/20 border border-green-500/30 rounded-full text-xs font-bold text-green-400 uppercase tracking-widest animate-pulse">
                                        ✨ Pemenang Terpilih ✨
                                    </div>
                                    <div class="text-lg font-black text-yellow-400 uppercase tracking-wider" id="winnerDrawName"></div>
                                    <div class="space-y-2">
                                        <div class="text-8xl font-black text-white tracking-widest font-mono" id="winnerBib">0</div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Checkbox option and statistics info -->
                        <div class="mt-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 px-1">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-400 cursor-pointer hover:text-slate-200 transition">
                                <input type="checkbox" id="doorprizeExcludeWinners" checked class="rounded border-slate-700 bg-slate-900 text-blue-500 focus:ring-blue-500/50 cursor-pointer">
                                Saring pemenang yang sudah terpilih sebelumnya
                            </label>
                            <div class="text-xs text-slate-500 hidden">
                                Total Paid: <span id="doorprizeTotalPaid" class="font-bold text-slate-300">-</span>
                            </div>
                        </div>

                        <!-- Action Controls -->
                        <div class="mt-6 flex gap-3 relative z-10">
                            <button type="button" id="btnStartDoorprize" onclick="startDoorprizeDraw()" class="flex-1 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-xl font-black text-sm tracking-wider uppercase transition-all duration-300 shadow-lg shadow-blue-600/25 flex items-center justify-center gap-2">
                                <span class="text-base">▶</span> Start Draw
                            </button>
                            <button type="button" id="btnStopDoorprize" onclick="stopDoorprizeDraw()" disabled class="flex-1 py-4 bg-slate-800 text-slate-500 rounded-xl font-black text-sm tracking-wider uppercase transition-all duration-300 cursor-not-allowed flex items-center justify-center gap-2">
                                <span class="text-base">⏹</span> Stop Draw
                            </button>
                        </div>

                    </div>

                    <!-- Winner History Sidebar (1 Col) -->
                    <div id="doorprizeHistorySidebar" class="flex flex-col bg-slate-950/30 rounded-2xl border border-slate-800 p-4 overflow-hidden h-[380px] lg:h-auto">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-xs font-black uppercase text-slate-400 tracking-wider">🏆 History Pemenang</h4>
                            <button type="button" onclick="clearDoorprizeWinners()" class="text-[10px] text-red-400 hover:text-red-300 font-bold hover:underline transition">Reset</button>
                        </div>

                        <!-- Scrollable Winner List -->
                        <div id="doorprizeWinnerList" class="flex-1 overflow-y-auto space-y-2 pr-1 text-left">
                            <div class="text-xs text-slate-500 text-center py-8">Belum ada pemenang yang ditarik.</div>
                        </div>

                        <!-- Export Button -->
                        <button type="button" onclick="exportDoorprizeWinners()" class="mt-3 w-full py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 border border-slate-700">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Export Pemenang (CSV)
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div class="bg-card w-full max-w-lg rounded-2xl border border-slate-700 shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-slate-700 bg-slate-900/50">
            <h3 class="text-lg font-bold text-white">Edit Peserta</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="edit-form" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="participant_id" id="edit-participant-id">
            
            <div>
                <label class="block text-xs text-slate-400 mb-1">Nama</label>
                <div id="edit-name" class="text-sm font-semibold text-white"></div>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Status Approved</label>
                <select name="isApproved" id="edit-isApproved" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-neon">
                    <option value="1">Yes (Approved)</option>
                    <option value="0">No (Not Approved)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Target Time</label>
                <input type="text" name="target_time" id="edit-target-time" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-neon" placeholder="HH:MM:SS">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Photo</label>
                <div class="flex items-center gap-4">
                    <img id="edit-photo-preview" src="" class="w-16 h-16 object-cover rounded-lg bg-slate-800 hidden">
                    <input type="file" name="photo" id="edit-photo" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-sm text-slate-300 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-neon hover:file:bg-slate-700">
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 transition text-sm font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-neon text-dark hover:bg-lime-300 transition text-sm font-bold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Modal -->
<div id="detail-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div class="bg-card w-full max-w-2xl rounded-2xl border border-slate-700 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-slate-700 bg-slate-900/50">
            <h3 class="text-lg font-bold text-white">Detail Peserta</h3>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <!-- Content -->
        <div class="p-6 overflow-y-auto space-y-6 text-sm text-slate-300">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Info -->
                <div>
                    <h4 class="text-xs font-bold text-yellow-400 uppercase tracking-wider mb-3">Informasi Pribadi</h4>
                    <div class="space-y-3">
                        <div>
                            <div class="text-xs text-slate-500">Nama Lengkap</div>
                            <div class="text-white font-medium" id="dm-name">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">ID Card (KTP/SIM)</div>
                            <div class="text-white" id="dm-id-card">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Gender</div>
                            <div class="text-white capitalize" id="dm-gender">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Tanggal Lahir</div>
                            <div class="text-white" id="dm-dob">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Email</div>
                            <div class="text-white break-all" id="dm-email">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">No. Telp</div>
                            <div class="text-white font-mono" id="dm-phone">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Kontak Darurat</div>
                            <div class="text-white" id="dm-emergency">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Alamat Lengkap</div>
                            <div class="text-white" id="dm-address">-</div>
                        </div>
                    </div>
                </div>

                <!-- Race Info -->
                <div>
                    <h4 class="text-xs font-bold text-cyan-400 uppercase tracking-wider mb-3">Informasi Lomba</h4>
                    <div class="space-y-3">
                        <div>
                            <div class="text-xs text-slate-500">Kategori Lomba</div>
                            <div class="text-white font-bold" id="dm-category">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Nomor BIB</div>
                            <div class="text-yellow-400 font-mono text-lg font-bold" id="dm-bib">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Ukuran Jersey</div>
                            <div class="text-white font-bold" id="dm-jersey">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Golongan Darah</div>
                            <div class="text-white font-bold" id="dm-blood-type">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Kategori Umur</div>
                            <div class="text-white font-bold" id="dm-age-group">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Target Time</div>
                            <div class="text-white font-mono" id="dm-target-time">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Status Pengambilan Race Pack</div>
                            <div class="flex items-center gap-2 mt-1">
                                <div id="dm-pickup-status">-</div>
                                <button type="button" id="dm-pickup-toggle-btn" class="px-2 py-0.5 text-[10px] rounded-lg font-bold bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 transition">
                                    Toggle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PIC & Transaction Info -->
            <div class="pt-6 border-t border-slate-700 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- PIC Info -->
                <div>
                    <h4 class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-3">Informasi PIC Pemesan</h4>
                    <div class="space-y-3">
                        <div>
                            <div class="text-xs text-slate-500">Nama PIC</div>
                            <div class="text-white" id="dm-pic-name">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Email PIC</div>
                            <div class="text-white break-all" id="dm-pic-email">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">No. Telp PIC</div>
                            <div class="text-white font-mono" id="dm-pic-phone">-</div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Info -->
                <div>
                    <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-3">Detail Transaksi</h4>
                    <div class="space-y-3">
                        <div>
                            <div class="text-xs text-slate-500">Tanggal Transaksi</div>
                            <div class="text-white" id="dm-trx-date">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Metode Pembayaran</div>
                            <div class="text-white uppercase font-mono" id="dm-payment-method">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Kupon Diskon</div>
                            <div class="text-yellow-400 font-bold font-mono" id="dm-coupon">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Status Pembayaran</div>
                            <div class="inline-flex mt-1" id="dm-payment-status">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Addons Info -->
            <div class="pt-6 border-t border-slate-700">
                <h4 class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-3">Addons / Tambahan</h4>
                <div id="dm-addons" class="grid grid-cols-1 gap-2 bg-slate-900/40 p-3 rounded-xl border border-slate-800">
                    -
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-900/50 px-6 py-4 flex justify-end border-t border-slate-700">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 transition text-sm font-bold">Tutup</button>
        </div>
    </div>
</div>

<!-- QR Scan Modal -->
<div id="qrScanModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeQrScanModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-slate-800 border border-slate-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-white">Scan QR Pickup</h3>
                        <button type="button" onclick="closeQrScanModal()" class="text-slate-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="rounded-2xl overflow-hidden border border-slate-700 bg-black relative">
                        <video id="qrVideo" class="w-full h-72 object-cover" playsinline muted></video>
                        <div class="absolute inset-0 pointer-events-none">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-48 h-48 border-2 border-yellow-400/70 rounded-2xl"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <div id="qrScanMsg" class="text-sm text-slate-300"></div>
                        <div class="text-xs text-slate-500">Arahkan kamera ke QR dari email tiket (format: TICKET-...)</div>
                    </div>
                </div>
                <div class="bg-slate-900/50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" id="btnQrStart" onclick="startQrScan()" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-sm font-bold">Start</button>
                    <button type="button" id="btnQrStop" onclick="stopQrScan()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold">Stop</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Coupon Report Modal -->
<div id="coupon-report-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div class="bg-card w-full max-w-2xl rounded-2xl border border-slate-700 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-slate-700 bg-slate-900/50">
            <div>
                <h3 class="text-lg font-bold text-white">Laporan Filter Kupon</h3>
                <div class="text-xs text-slate-400" id="coupon-modal-subtitle"></div>
            </div>
            <button type="button" onclick="closeCouponReportModal()" class="text-slate-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <!-- Content -->
        <div class="p-6 overflow-y-auto space-y-6 text-sm text-slate-300">
            <!-- Jersey Size Summary (misal S = 3 XL = 4) -->
            <div>
                <h4 class="text-xs font-bold text-yellow-400 uppercase tracking-wider mb-3">Ringkasan Ukuran Jersey</h4>
                <div id="coupon-jersey-summary" class="flex flex-wrap gap-2">
                    <!-- Dynamic badges -->
                </div>
            </div>

            <!-- List of BIB & Jersey Sizes -->
            <div>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-3">
                    <h4 class="text-xs font-bold text-cyan-400 uppercase tracking-wider">Daftar Nomor BIB dan Jersey</h4>
                    <div class="flex items-center gap-2">
                        <label for="couponModalFilterPickup" class="text-xs text-slate-400 font-bold uppercase tracking-wider">Filter Picked:</label>
                        <select id="couponModalFilterPickup" onchange="filterCouponModalTable()" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="all">Semua Status</option>
                            <option value="1">Picked Up</option>
                            <option value="0">Not Picked</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto border border-slate-700 rounded-xl">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-900/60 text-slate-300">
                            <tr>
                                <th class="text-left font-semibold px-4 py-2">Nama</th>
                                <th class="text-left font-semibold px-4 py-2">Nomor BIB</th>
                                <th class="text-left font-semibold px-4 py-2">Ukuran Jersey</th>
                                <th class="text-left font-semibold px-4 py-2">Status Picked</th>
                            </tr>
                        </thead>
                        <tbody id="coupon-participants-tbody" class="divide-y divide-slate-800">
                            <!-- Dynamic rows -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-900/50 px-6 py-4 flex justify-end border-t border-slate-700">
            <button type="button" onclick="closeCouponReportModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 transition text-sm font-bold">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
        const updateUrlBase = "{{ route('report.participant.update', ['event' => $event->id, 'participant' => ':id']) }}";
        const csrfTokenVal = "{{ csrf_token() }}";

        var qrStream = null;
        var qrRunning = false;
        var qrBusy = false;
        var qrLastOkAt = 0;
        var qrLoopTimer = null;
        var qrCanvas = null;
        var qrCtx = null;
        var qrDetector = null;

        window.getExportUrl = function (format) {
            const baseUrl = format === 'xlsx' ? "{{ route('report.export.xlsx', $event->id) }}" : "{{ route('report.export', $event->id) }}";
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.delete('page');
            currentParams.delete('per_page');
            const qs = currentParams.toString();
            return baseUrl + (qs ? '?' + qs : '');
        };

        function setQrMsg(text, type) {
            var el = document.getElementById('qrScanMsg');
            if (!el) return;
            el.textContent = text || '';
            if (type === 'error') {
                el.className = 'text-sm text-red-300';
            } else if (type === 'success') {
                el.className = 'text-sm text-green-300';
            } else {
                el.className = 'text-sm text-slate-300';
            }
        }

        function parseParticipantIdFromQr(raw) {
            var s = String(raw || '').trim();
            if (!s) return null;

            try {
                if (/^https?:\/\//i.test(s)) {
                    var u = new URL(s);
                    var d = u.searchParams.get('data') || u.searchParams.get('ticket') || u.searchParams.get('q') || '';
                    if (d) s = String(d).trim();
                }
            } catch (e) {}

            var m = s.match(/^TICKET-(\d+)-(\d+)$/);
            if (m && m[1]) return parseInt(m[1], 10);

            m = s.match(/TICKET-(\d+)-/);
            if (m && m[1]) return parseInt(m[1], 10);

            return null;
        }

        function updatePickupByParticipantId(participantId) {
            var url = "{{ url('/reports/' . $event->id . '/participants') }}/" + participantId + "/status";
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfTokenVal,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    is_picked_up: true,
                    picked_up_by: 'Public Report Scanner'
                })
            }).then(function(r) { return r.json(); });
        }

        function ensureQrDetector() {
            if (qrDetector) return qrDetector;
            try {
                if (!('BarcodeDetector' in window)) return null;
                qrDetector = new BarcodeDetector({ formats: ['qr_code'] });
                return qrDetector;
            } catch (e) {
                return null;
            }
        }

        function drawVideoToCanvas(video) {
            var w = video.videoWidth;
            var h = video.videoHeight;
            if (!qrCanvas) qrCanvas = document.createElement('canvas');
            if (qrCanvas.width !== w) qrCanvas.width = w;
            if (qrCanvas.height !== h) qrCanvas.height = h;
            if (!qrCtx) qrCtx = qrCanvas.getContext('2d', { willReadFrequently: true });
            qrCtx.drawImage(video, 0, 0, w, h);
            return { w: w, h: h };
        }

        function decodeWithJsQR(video) {
            if (typeof jsQR !== 'function') return null;
            if (!video || !video.videoWidth || !video.videoHeight) return null;
            var dims = drawVideoToCanvas(video);
            try {
                var size = Math.floor(Math.min(dims.w, dims.h) * 0.75);
                var x = Math.floor((dims.w - size) / 2);
                var y = Math.floor((dims.h - size) / 2);
                var img = qrCtx.getImageData(x, y, size, size);
                var code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'attemptBoth' });
                if (code && code.data) return String(code.data).trim();
            } catch (e) {}

            try {
                var full = qrCtx.getImageData(0, 0, dims.w, dims.h);
                var code2 = jsQR(full.data, full.width, full.height, { inversionAttempts: 'attemptBoth' });
                if (code2 && code2.data) return String(code2.data).trim();
            } catch (e) {}

            return null;
        }

        function decodeWithBarcodeDetector(video) {
            var det = ensureQrDetector();
            if (!det) return Promise.resolve(null);
            if (!video || !video.videoWidth || !video.videoHeight) return Promise.resolve(null);
            drawVideoToCanvas(video);
            return det.detect(qrCanvas).then(function(dets){
                if (!dets || !dets.length) return null;
                var v = dets[0].rawValue || '';
                v = String(v).trim();
                return v || null;
            }).catch(function(){ return null; });
        }

        function processQrPayload(payload) {
            var participantId = parseParticipantIdFromQr(payload);
            if (!participantId) {
                setQrMsg('QR tidak dikenali. Pastikan QR dari email tiket.', 'error');
                return Promise.resolve();
            }

            setQrMsg('Memproses pickup…', null);
            return updatePickupByParticipantId(participantId).then(function(res){
                if (res && res.success) {
                    var p = res.participant || null;
                    var name = (p && p.name) ? String(p.name) : '';
                    var bib = (p && p.bib_number) ? String(p.bib_number) : '-';
                    var jersey = (p && p.jersey_size) ? String(p.jersey_size) : '-';
                    var payment = (p && p.payment_status) ? String(p.payment_status).toUpperCase() : 'UNKNOWN';
                    var ageGroup = (p && p.age_group) ? String(p.age_group) : '-';
                    var addonsList = (p && Array.isArray(p.addons)) ? p.addons.map(function(a) { return (a && (a.name || a['name'])) || a; }).filter(Boolean) : [];
                    var addonsText = addonsList.length ? addonsList.join(', ') : '-';
                    var msg = name ? (`Berhasil pickup: ${name} • BIB ${bib} • Jersey ${jersey} • Kategori Umur ${ageGroup} • Addons: ${addonsText} • Payment ${payment}`) : (res.message || ('Berhasil update pickup #' + participantId));
                    setQrMsg(msg, 'success');
                    
                    // Trigger AJAX reload to update the dashboard table and stats
                    const filterForm = document.getElementById('report-filters');
                    if (filterForm) {
                        const fd = new FormData(filterForm);
                        const payloadObj = {};
                        for (const [k, v] of fd.entries()) {
                            payloadObj[k] = typeof v === 'string' ? v.trim() : v;
                        }
                        payloadObj.page = 1;
                        if (typeof fetchReport === 'function') {
                            fetchReport(payloadObj);
                        }
                    }
                    qrLastOkAt = Date.now();
                } else {
                    setQrMsg((res && res.message) ? res.message : 'Gagal update pickup', 'error');
                }
            }).catch(function(err){
                setQrMsg((err && err.message) ? err.message : 'Gagal update pickup (network/server).', 'error');
            });
        }

        function qrLoop() {
            if (!qrRunning) return;
            if (qrBusy) return;
            var video = document.getElementById('qrVideo');
            if (!video || !video.videoWidth) return;

            var now = Date.now();
            if (now - qrLastOkAt < 900) return;

            qrBusy = true;
            decodeWithBarcodeDetector(video).then(function(v){
                if (v) return v;
                return decodeWithJsQR(video);
            }).then(function(val){
                if (!val) return null;
                return processQrPayload(val);
            }).finally(function(){
                qrBusy = false;
            });
        }

        window.openQrScanModal = function() {
            var modal = document.getElementById('qrScanModal');
            if (modal) modal.classList.remove('hidden');
            setQrMsg('', null);
            window.startQrScan();
        };

        window.closeQrScanModal = function() {
            window.stopQrScan();
            var modal = document.getElementById('qrScanModal');
            if (modal) modal.classList.add('hidden');
        };

        window.startQrScan = function() {
            if (qrRunning) return;
            var video = document.getElementById('qrVideo');
            if (!video) return;

            qrRunning = true;
            setQrMsg('Meminta akses kamera…', null);

            navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false })
                .then(function(stream){
                    qrStream = stream;
                    video.srcObject = stream;
                    return video.play();
                })
                .then(function(){
                    setQrMsg('Arahkan kamera ke QR.', null);
                    if (qrLoopTimer) clearInterval(qrLoopTimer);
                    qrLoopTimer = setInterval(qrLoop, 220);
                })
                .catch(function(err){
                    qrRunning = false;
                    setQrMsg('Kamera tidak bisa diakses. Pastikan izin kamera diaktifkan.', 'error');
                    if (err) console.error(err);
                });
        };

        window.stopQrScan = function() {
            qrRunning = false;
            qrBusy = false;
            if (qrLoopTimer) {
                clearInterval(qrLoopTimer);
                qrLoopTimer = null;
            }
            var video = document.getElementById('qrVideo');
            if (video) {
                try { video.pause(); } catch (e) {}
                video.srcObject = null;
            }
            if (qrStream) {
                try {
                    qrStream.getTracks().forEach(function(t){ t.stop(); });
                } catch (e) {}
                qrStream = null;
            }
        };

        async function updateStatus(participantId, value, checkboxEl) {
            try {
                const url = updateUrlBase.replace(':id', participantId);
                const formData = new FormData();
                formData.append('_token', csrfTokenVal);
                formData.append('isApproved', value);
                
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                const data = await res.json();
                
                if (!res.ok) {
                    throw new Error(data.message || 'Gagal update status');
                }
                
                // Success - Toast or console
            } catch (e) {
                alert(e.message || 'Terjadi kesalahan saat update status');
                // Revert checkbox state if failed
                if (checkboxEl) {
                    checkboxEl.checked = !checkboxEl.checked;
                    toggleLabel(participantId, checkboxEl.checked);
                }
            }
        }

        function toggleLabel(id, isChecked) {
            const label = document.getElementById('status-label-' + id);
            if (label) {
                label.innerText = isChecked ? 'Yes' : 'No';
                label.className = isChecked ? 'ml-2 text-xs font-bold text-neon' : 'ml-2 text-xs font-bold text-slate-400';
            }
        }

        function handleToggle(id, el) {
            const isChecked = el.checked;
            toggleLabel(id, isChecked);
            updateStatus(id, isChecked ? 1 : 0, el);
        }

        function openEditModal(p) {
        const modal = document.getElementById('edit-modal');
        const form = document.getElementById('edit-form');
        
        document.getElementById('edit-participant-id').value = p.id;
        document.getElementById('edit-name').textContent = p.name;
        document.getElementById('edit-isApproved').value = p.isApproved ? 1 : 0;
        document.getElementById('edit-target-time').value = p.target_time || '';
        
        const photoPreview = document.getElementById('edit-photo-preview');
        if (p.photo) {
            photoPreview.src = '/storage/' + p.photo;
            photoPreview.classList.remove('hidden');
        } else {
            photoPreview.classList.add('hidden');
        }

        form.action = updateUrlBase.replace(':id', p.id);
        modal.classList.remove('hidden');
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
        pStatusEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border';
        if (pStatus === 'paid' || pStatus === 'settlement' || pStatus === 'capture' || pStatus === 'cod') {
            pStatusEl.classList.add('bg-green-900/30', 'text-green-400', 'border-green-500/30');
        } else if (pStatus === 'pending') {
            pStatusEl.classList.add('bg-yellow-900/30', 'text-yellow-400', 'border-yellow-500/30');
        } else {
            pStatusEl.classList.add('bg-red-900/30', 'text-red-400', 'border-red-500/30');
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
                is_picked_up: nextStatus ? 1 : 0,
                picked_up_by: 'Public Report Dashboard'
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                callback(null, data.participant);
            } else {
                callback(data.message || 'Gagal mengubah status');
            }
        })
        .catch(error => {
            callback(error.message || 'Terjadi kesalahan sistem');
        });
    }

    function adjustPickupStats(isPickedUp) {
        const statPicked = document.getElementById('stat-picked');
        const statUnpicked = document.getElementById('stat-unpicked');
        if (statPicked && statUnpicked) {
            let pickedVal = parseInt(statPicked.textContent.replace(/[^0-9]/g, '')) || 0;
            let unpickedVal = parseInt(statUnpicked.textContent.replace(/[^0-9]/g, '')) || 0;
            if (isPickedUp) {
                statPicked.textContent = (pickedVal + 1).toLocaleString('id-ID');
                statUnpicked.textContent = Math.max(0, unpickedVal - 1).toLocaleString('id-ID');
            } else {
                statPicked.textContent = Math.max(0, pickedVal - 1).toLocaleString('id-ID');
                statUnpicked.textContent = (unpickedVal + 1).toLocaleString('id-ID');
            }
        }
    }

    function togglePickup(btn, participantId, isPickedUp) {
        btn.disabled = true;
        performPickupToggle(participantId, !isPickedUp, function(err, p) {
            btn.disabled = false;
            if (err) {
                alert(err);
                return;
            }
            adjustPickupStats(p.is_picked_up);
            
            // Update all toggle buttons for this participant on the page (main table, coupon modal, etc.)
            const allToggles = document.querySelectorAll(`button[onclick*="togglePickup"]`);
            allToggles.forEach(toggle => {
                const onclickStr = toggle.getAttribute('onclick') || '';
                const match = onclickStr.match(/togglePickup\s*\(\s*this\s*,\s*(\d+)/);
                if (match && parseInt(match[1]) === p.id) {
                    toggle.setAttribute('onclick', `togglePickup(this, ${p.id}, ${p.is_picked_up})`);
                    if (p.is_picked_up) {
                        toggle.className = toggle.classList.contains('text-[10px]')
                            ? "px-2 py-1 text-[10px] rounded-lg font-bold border transition duration-200 bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/60"
                            : "px-2 py-1 text-xs rounded-lg font-bold border transition duration-200 bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/60";
                        toggle.textContent = "Picked Up";
                    } else {
                        toggle.className = toggle.classList.contains('text-[10px]')
                            ? "px-2 py-1 text-[10px] rounded-lg font-bold border transition duration-200 bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700"
                            : "px-2 py-1 text-xs rounded-lg font-bold border transition duration-200 bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700";
                        toggle.textContent = "Not Picked";
                    }
                }
            });

            // Update in-memory coupon report cache
            if (window.currentCouponReport && window.currentCouponReport.participants) {
                const cp = window.currentCouponReport.participants.find(x => x.id === p.id);
                if (cp) {
                    cp.is_picked_up = p.is_picked_up;
                }
            }
            if (window.currentCouponReportData && window.currentCouponReportData.participants) {
                const cp = window.currentCouponReportData.participants.find(x => x.id === p.id);
                if (cp) {
                    cp.is_picked_up = p.is_picked_up;
                }
                if (typeof filterCouponModalTable === 'function') {
                    filterCouponModalTable();
                }
            }

            // Update row data-json attribute
            const tr = btn.closest('tr');
            if (tr) {
                try {
                    const currentJson = JSON.parse(tr.dataset.json || '{}');
                    currentJson.is_picked_up = p.is_picked_up;
                    tr.dataset.json = JSON.stringify(currentJson);
                } catch(e) {}
            }

            // Update detail modal if open for this participant
            const activeId = document.getElementById('dm-pickup-status')?.dataset?.participantId;
            if (activeId && parseInt(activeId) === p.id) {
                updateModalPickupUi(p);
            }
        });
    }

    function updateModalPickupUi(data) {
        const pickupEl = document.getElementById('dm-pickup-status');
        const toggleBtn = document.getElementById('dm-pickup-toggle-btn');
        
        if (data.is_picked_up) {
            pickupEl.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-900/30 text-emerald-400 border border-emerald-500/30">Already Picked Up</span>';
            toggleBtn.textContent = 'Mark as Not Picked';
        } else {
            pickupEl.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">Not Picked Up</span>';
            toggleBtn.textContent = 'Mark as Picked Up';
        }

        toggleBtn.onclick = function() {
            toggleBtn.disabled = true;
            performPickupToggle(data.id, !data.is_picked_up, function(err, p) {
                toggleBtn.disabled = false;
                if (err) {
                    alert(err);
                    return;
                }
                adjustPickupStats(p.is_picked_up);
                
                // Update modal UI
                updateModalPickupUi(p);
                
                // Update all toggle buttons for this participant on the page (main table, coupon modal, etc.)
                const allToggles = document.querySelectorAll(`button[onclick*="togglePickup"]`);
                allToggles.forEach(toggle => {
                    const onclickStr = toggle.getAttribute('onclick') || '';
                    const match = onclickStr.match(/togglePickup\s*\(\s*this\s*,\s*(\d+)/);
                    if (match && parseInt(match[1]) === p.id) {
                        toggle.setAttribute('onclick', `togglePickup(this, ${p.id}, ${p.is_picked_up})`);
                        if (p.is_picked_up) {
                            toggle.className = toggle.classList.contains('text-[10px]')
                                ? "px-2 py-1 text-[10px] rounded-lg font-bold border transition duration-200 bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/60"
                                : "px-2 py-1 text-xs rounded-lg font-bold border transition duration-200 bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/60";
                            toggle.textContent = "Picked Up";
                        } else {
                            toggle.className = toggle.classList.contains('text-[10px]')
                                ? "px-2 py-1 text-[10px] rounded-lg font-bold border transition duration-200 bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700"
                                : "px-2 py-1 text-xs rounded-lg font-bold border transition duration-200 bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700";
                            toggle.textContent = "Not Picked";
                        }
                    }
                });

                // Update in-memory coupon report cache
                if (window.currentCouponReport && window.currentCouponReport.participants) {
                    const cp = window.currentCouponReport.participants.find(x => x.id === p.id);
                    if (cp) {
                        cp.is_picked_up = p.is_picked_up;
                    }
                }
                
                // Update table row data-json attribute if visible in the table
                const rows = document.querySelectorAll('#participants-tbody tr[data-json]');
                rows.forEach(tr => {
                    try {
                        const rowData = JSON.parse(tr.dataset.json || '{}');
                        if (rowData.id === p.id) {
                            rowData.is_picked_up = p.is_picked_up;
                            tr.dataset.json = JSON.stringify(rowData);
                        }
                    } catch(e) {}
                });
            });
        };
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
        
        // Coupon Report Variables
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

            const labels = sales && Array.isArray(sales.labels) ? sales.labels : [];
            const paidSeries = sales && sales.series && Array.isArray(sales.series.paid) ? sales.series.paid : [];
            const days = labels.length || 0;
            const avgPaid = days > 0 ? paid / days : 0;

            let bestIdx = -1;
            let bestVal = 0;
            paidSeries.forEach((v, i) => {
                const n = Number(v || 0);
                if (n > bestVal) {
                    bestVal = n;
                    bestIdx = i;
                }
            });
            const bestLabel = bestIdx >= 0 && labels[bestIdx] ? labels[bestIdx] : '-';

            salesInsightsEl.innerHTML = [
                `<div class="flex items-center justify-between"><span class="text-slate-400">Paid</span><span class="font-mono font-bold">${formatNumber(paid)}</span></div>`,
                `<div class="flex items-center justify-between"><span class="text-slate-400">Pending</span><span class="font-mono font-bold">${formatNumber(pending)}</span></div>`,
                `<div class="flex items-center justify-between"><span class="text-slate-400">Conversion</span><span class="font-mono font-bold">${conversion.toFixed(1)}%</span></div>`,
                `<div class="flex items-center justify-between"><span class="text-slate-400">Avg/day</span><span class="font-mono font-bold">${avgPaid.toFixed(2)}</span></div>`,
                `<div class="flex items-center justify-between"><span class="text-slate-400">Best</span><span class="font-mono font-bold">${bestVal ? formatNumber(bestVal) : '-'}</span></div>`,
                `<div class="text-xs text-slate-500">${bestVal ? `Tanggal: ${bestLabel}` : ''}</div>`,
            ].join('');
        }

        function renderSalesChart(sales) {
            if (!salesChartCanvas || typeof Chart === 'undefined') return;

            const labels = sales && Array.isArray(sales.labels) ? sales.labels : [];
            const series = sales && sales.series ? sales.series : {};
            const paid = Array.isArray(series.paid) ? series.paid : [];
            const pending = Array.isArray(series.pending) ? series.pending : [];

            const datasetPaid = {
                label: 'Paid',
                data: paid,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.25)',
                tension: 0.25,
                fill: true,
                pointRadius: 2,
            };

            const datasetPending = {
                label: 'Pending',
                data: pending,
                borderColor: '#eab308',
                backgroundColor: 'rgba(234, 179, 8, 0.18)',
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
                                labels: { color: '#e2e8f0' }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#94a3b8', maxRotation: 0, autoSkip: true },
                                grid: { color: 'rgba(148, 163, 184, 0.15)' },
                            },
                            y: {
                                ticks: { color: '#94a3b8' },
                                grid: { color: 'rgba(148, 163, 184, 0.15)' },
                                beginAtZero: true,
                            }
                        }
                    }
                });
            } else {
                salesChart.data.labels = labels;
                salesChart.data.datasets = [datasetPaid, datasetPending];
                salesChart.update();
            }

            setSalesInsights(sales);
        }

        function paymentPill(status, couponCode, finalAmount) {
            const text = (status || '').toString();
            let html = `<span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-800 text-slate-200">${text}</span>`;
            if (couponCode) {
                html += `<div class="mt-1 text-[10px] text-yellow-400 font-mono" title="Kupon dipakai">🏷️ ${couponCode}</div>`;
                html += `<div class="text-[10px] text-slate-400 mt-0.5">Net: Rp ${formatCurrency(finalAmount)}</div>`;
            }
            return html;
        }

        function renderParticipants(paginator) {
            const rows = (paginator && paginator.data) ? paginator.data : [];
            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="10" class="px-4 py-6 text-center text-slate-400">Tidak ada data.</td></tr>`;
            } else {
                tbody.innerHTML = rows.map((p) => {
                    const addons = Array.isArray(p.addons) ? p.addons : [];
                    const addonsHtml = addons.length
                        ? `<span class="inline-flex flex-wrap gap-1 justify-end md:justify-start">${
                            addons.map((a) => {
                                const name = (a && (a.name || a['name'])) || '-';
                                return `<span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-800 text-slate-200">${name}</span>`;
                            }).join('')
                        }</span>`
                        : `<span class="text-slate-400">-</span>`;

                    // Escape JSON for onclick to avoid syntax errors with quotes
                    const jsonP = JSON.stringify(p).replace(/"/g, '&quot;');

                    return `
                        <tr class="hover:bg-slate-900/40 cursor-pointer block md:table-row border-b border-slate-800 md:border-none mb-4 md:mb-0 bg-slate-900/20 md:bg-transparent rounded-xl md:rounded-none p-4 md:p-0" onclick="if(!event.target.closest('button') && !event.target.closest('a') && !event.target.closest('.no-click')) openDetailModalFromRow(this)" data-json="${jsonP}">
                            <td class="px-4 py-2 md:py-3 font-semibold text-white block md:table-cell flex justify-between items-center md:block">
                                <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Nama</span>
                                <span class="text-right md:text-left">${(p.name || '-')}</span>
                            </td>
                            <td class="px-4 py-2 md:py-3 text-slate-200 block md:table-cell flex justify-between items-center md:block">
                                <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Email</span>
                                <span class="text-right md:text-left break-all">${(p.email || '-')}</span>
                            </td>
                            <td class="px-4 py-2 md:py-3 text-slate-300 block md:table-cell flex justify-between items-center md:block">
                                <span class="md:hidden text-slate-500 font-bold text-xs uppercase">No Telp</span>
                                <span class="text-right md:text-left font-mono">${(p.phone || '-')}</span>
                            </td>
                            <td class="px-4 py-2 md:py-3 text-slate-300 block md:table-cell flex justify-between items-center md:block">
                                <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Jersey</span>
                                <span class="text-right md:text-left font-mono">${(p.jersey_size || '-')}</span>
                            </td>
                            <td class="px-4 py-2 md:py-3 text-slate-300 block md:table-cell flex justify-between items-center md:block">
                                <span class="md:hidden text-slate-500 font-bold text-xs uppercase">No BIB</span>
                                <span class="text-right md:text-left font-mono">${
                                    (() => {
                                        let bib = p.bib_number;
                                        if (bib && bib.includes('-')) {
                                            const parts = bib.split('-');
                                            return parts[parts.length - 1];
                                        }
                                        return bib || '-';
                                    })()
                                }</span>
                            </td>
                            <td class="px-4 py-2 md:py-3 text-slate-200 block md:table-cell flex justify-between items-center md:block">
                                <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Addons</span>
                                <span class="text-right md:text-left">${addonsHtml}</span>
                            </td>
                            <td class="px-4 py-2 md:py-3 text-slate-300 block md:table-cell flex justify-between items-center md:block">
                                <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Tgl Reg</span>
                                <span class="text-right md:text-left">${formatDateTime(p.created_at)}</span>
                            </td>
                            <td class="px-4 py-2 md:py-3 block md:table-cell flex justify-between items-center md:block">
                                <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Status</span>
                                <span class="text-right md:text-left">${paymentPill(p.payment_status, p.coupon_code, p.final_amount)}</span>
                            </td>
                            <td class="px-4 py-2 md:py-3 block md:table-cell flex justify-between items-center md:block no-click">
                                <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Picked Up</span>
                                <span class="text-right md:text-left">
                                    <button type="button" 
                                        onclick="togglePickup(this, ${p.id}, ${p.is_picked_up ? 'true' : 'false'})"
                                        class="px-2 py-1 text-xs rounded-lg font-bold border transition duration-200 ${p.is_picked_up ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/60' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700'}">
                                        ${p.is_picked_up ? 'Picked Up' : 'Not Picked'}
                                    </button>
                                </span>
                            </td>
                            <td class="px-4 py-2 md:py-3 block md:table-cell flex justify-between items-center md:block">
                                <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Aksi</span>
                                <span class="text-right md:text-left">
                                    <button type="button" 
                                        onclick="openEditModal(${jsonP})"
                                        class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-white text-xs rounded-lg transition">
                                        Edit
                                    </button>
                                </span>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            metaEl.textContent = `Menampilkan ${formatNumber(paginator.to || 0)} dari ${formatNumber(paginator.total || 0)}`;
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
                if (p === '…') return `<span class="px-3 py-2 text-xs text-slate-500">…</span>`;
                const active = p === current;
                const cls = active
                    ? 'px-3 py-2 text-xs font-bold rounded-xl bg-neon text-dark'
                    : 'px-3 py-2 text-xs font-bold rounded-xl bg-slate-800 text-slate-200 hover:bg-slate-700';
                return `<button type="button" data-page="${p}" class="${cls}">${p}</button>`;
            }).join('');

            Array.from(paginationEl.querySelectorAll('button[data-page]')).forEach((btn) => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const page = Number(btn.getAttribute('data-page') || 1);
                    fetchReport({ ...currentPayload, page });
                });
            });
        }

        function renderCoupons(coupons) {
            const rows = Array.isArray(coupons) ? coupons : [];
            if (!rows.length) {
                couponTbody.innerHTML = `<tr><td colspan="3" class="px-4 py-6 text-center text-slate-400">Belum ada kupon terpakai.</td></tr>`;
                return;
            }

            couponTbody.innerHTML = rows.map((c) => {
                return `
                    <tr class="hover:bg-slate-900/40 block md:table-row border-b border-slate-800 md:border-none mb-4 md:mb-0 bg-slate-900/20 md:bg-transparent rounded-xl md:rounded-none p-4 md:p-0">
                        <td class="px-4 py-2 md:py-3 font-mono font-bold text-white block md:table-cell flex justify-between items-center md:block">
                            <span class="md:hidden text-slate-500 font-bold text-xs uppercase">Kode</span>
                            <span class="text-right md:text-left">${(c.code || '-')}</span>
                        </td>
                        <td class="px-4 py-2 md:py-3 text-slate-200 block md:table-cell flex justify-between items-center md:block text-right">
                            <span class="md:hidden text-slate-500 font-bold text-xs uppercase text-left">Dipakai</span>
                            <span>${formatNumber(c.total_transactions)}</span>
                        </td>
                        <td class="px-4 py-2 md:py-3 text-slate-200 block md:table-cell flex justify-between items-center md:block text-right">
                            <span class="md:hidden text-slate-500 font-bold text-xs uppercase text-left">Total Diskon</span>
                            <span>${formatCurrency(c.total_discount)}</span>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderStats(report) {
            if (!report) return;
            statTotal.textContent = (typeof report.total_slots === 'string') ? report.total_slots : formatNumber(report.total_slots);
            statSold.textContent = formatNumber(report.sold_slots);
            statPending.textContent = formatNumber(report.pending_slots);
            statRemaining.textContent = (typeof report.remaining_slots === 'string') ? report.remaining_slots : formatNumber(report.remaining_slots);
            if (statPicked && report.pickup) {
                statPicked.textContent = formatNumber(report.pickup.picked_up || 0);
            }
            if (statUnpicked && report.pickup) {
                statUnpicked.textContent = formatNumber(report.pickup.not_picked_up || 0);
            }

            const jersey = report.jersey_sizes || {};
            let totalUsed = 0;
            ['XXS','XS','S','M','L','XL','2XL','3XL','4XL','5XL'].forEach((size) => {
                const el = document.getElementById('stat-jersey-' + size);
                if (!el) return;
                let v = jersey[size];
                if (v === undefined || v === null) v = jersey[String(size).toLowerCase()];
                if (v === undefined || v === null) v = jersey[String(size).toUpperCase()];
                if ((v === undefined || v === null) && size === '2XL') v = (jersey['XXL'] || jersey['xxl']);
                if ((v === undefined || v === null) && size === '3XL') v = (jersey['XXXL'] || jersey['xxxl']);
                const val = Number(v || 0);
                el.textContent = formatNumber(val);
                totalUsed += val;

                // Recalculate remaining (sisa) dynamically
                const quotaEl = document.getElementById('stat-jersey-quota-' + size);
                const sisaEl = document.getElementById('stat-jersey-sisa-' + size);
                if (quotaEl && sisaEl) {
                    const quotaVal = quotaEl.textContent.trim();
                    if (quotaVal !== '∞' && quotaVal !== '') {
                        const quota = parseInt(quotaVal.replace(/,/g, '')) || 0;
                        const sisa = Math.max(0, quota - val);
                        sisaEl.textContent = formatNumber(sisa);

                        // Update status colors on wrapper and text
                        const parentEl = el.closest('.rounded-xl');
                        if (parentEl) {
                            parentEl.className = 'rounded-xl border px-3 py-2 ' + 
                                (sisa === 0 ? 'border-red-500/40 bg-red-900/10' : (sisa <= 5 ? 'border-yellow-500/40 bg-yellow-900/10' : 'border-slate-700 bg-slate-900/30'));
                            sisaEl.className = 'text-sm font-mono font-bold ' + 
                                (sisa === 0 ? 'text-red-400' : (sisa <= 5 ? 'text-yellow-400' : 'text-emerald-400'));
                        }
                    }
                }
            });

            // Update Totals row dynamically
            const totalUsedEl = document.getElementById('stat-jersey-total-used');
            const totalQuotaEl = document.getElementById('stat-jersey-total-quota');
            const totalSisaEl = document.getElementById('stat-jersey-total-sisa');
            if (totalUsedEl) {
                totalUsedEl.textContent = formatNumber(totalUsed);
            }
            if (totalQuotaEl && totalSisaEl) {
                const totalQuotaVal = totalQuotaEl.textContent.trim();
                if (totalQuotaVal !== '∞' && totalQuotaVal !== '') {
                    const totalQuota = parseInt(totalQuotaVal.replace(/,/g, '')) || 0;
                    const totalSisa = Math.max(0, totalQuota - totalUsed);
                    totalSisaEl.textContent = formatNumber(totalSisa);
                }
            }
        }

        async function fetchReport(payload) {
            setLoading(true);
            try {
                const url = new URL(window.location.href);
                Object.keys(payload).forEach(key => {
                    if (payload[key] !== null && payload[key] !== undefined && payload[key] !== '') {
                        url.searchParams.set(key, payload[key]);
                    } else {
                        url.searchParams.delete(key);
                    }
                });
                
                // Update history
                window.history.pushState({}, '', url);

                const res = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                });

                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Request gagal');
                }

                renderStats(data.report);
                renderCoupons(data.coupon_usage);
                renderParticipants(data.participants);
                renderPagination(data.participants, payload);
                renderSalesChart(data.sales);

                window.currentCouponReport = data.coupon_report || null;
                currentCouponText = couponSelect ? couponSelect.options[couponSelect.selectedIndex].text : '';

                const btnShowCouponReport = document.getElementById('btn-show-coupon-report');
                if (btnShowCouponReport) {
                    if (window.currentCouponReport) {
                        btnShowCouponReport.classList.remove('hidden');
                    } else {
                        btnShowCouponReport.classList.add('hidden');
                    }
                }

                if (shouldShowCouponModal && currentCouponReport) {
                    showCouponReportModal(currentCouponReport, currentCouponText);
                    shouldShowCouponModal = false;
                }
            } catch (e) {
                alert(e.message || 'Terjadi kesalahan');
            } finally {
                setLoading(false);
            }
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const payload = serializeAll();
                payload.page = 1;
                fetchReport(payload);
            });

            form.querySelectorAll('select').forEach((sel) => {
                sel.addEventListener('change', () => {
                    const payload = serializeAll();
                    payload.page = 1;
                    fetchReport(payload);
                });
            });

            form.querySelectorAll('input[type="date"], input[type="number"]').forEach((inp) => {
                inp.addEventListener('change', () => {
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
                }, 400));
            }
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                Array.from(form.elements).forEach((el) => {
                    if (!el.name) return;
                    if (el.tagName === 'SELECT') {
                        el.value = el.name === 'payment_status' ? 'all' : '';
                        return;
                    }
                    el.value = '';
                });
                const payload = serializeAll();
                payload.page = 1;
                fetchReport(payload);
            });
        }

        if (salesForm) {
            salesForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const payload = serializeAll();
                payload.page = 1;
                fetchReport(payload);
            });
        }

        if (salesResetBtn) {
            salesResetBtn.addEventListener('click', function () {
                if (salesGroupEl) salesGroupEl.value = 'day';
                if (salesStartEl) salesStartEl.value = '';
                if (salesEndEl) salesEndEl.value = '';
                const payload = serializeAll();
                payload.page = 1;
                fetchReport(payload);
            });
        }

        // Only initial render for pagination if needed, but the server already renders it.
        // However, we want to hook up the events.
        // The server renders #participants-pagination empty? 
        // Let's check the view again.
        // <div id="participants-pagination" ...></div> is empty in HTML.
        // So line 394 in original calls renderPagination.
        
        // Coupon Report Modal Helpers
        const couponSelect = document.querySelector('select[name="coupon_id"]');
        if (couponSelect) {
            couponSelect.addEventListener('change', function() {
                const val = this.value;
                if (val !== '' && val !== 'without') {
                    shouldShowCouponModal = true;
                }
            });
            currentCouponText = couponSelect.options[couponSelect.selectedIndex].text;
        }

        window.currentCouponReportData = null;

        window.renderCouponReportModalContent = function(participants) {
            const summaryEl = document.getElementById('coupon-jersey-summary');
            summaryEl.innerHTML = '';
            
            const totals = {};
            participants.forEach(p => {
                const size = (p.jersey_size || '').toUpperCase().trim();
                if (size) {
                    totals[size] = (totals[size] || 0) + 1;
                }
            });
            
            if (Object.keys(totals).length > 0) {
                const order = ['XXS','XS','S','M','L','XL','2XL','3XL','4XL','5XL'];
                const sortedSizes = Object.keys(totals).sort((a, b) => {
                    const idxA = order.indexOf(a);
                    const idxB = order.indexOf(b);
                    if (idxA !== -1 && idxB !== -1) return idxA - idxB;
                    if (idxA !== -1) return -1;
                    if (idxB !== -1) return 1;
                    return a.localeCompare(b);
                });
                
                sortedSizes.forEach(size => {
                    const count = totals[size];
                    const badge = document.createElement('span');
                    badge.className = 'inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-neon text-dark border border-neon/30';
                    badge.innerHTML = `${size} = <span class="font-mono ml-1">${count}</span>`;
                    summaryEl.appendChild(badge);
                });
            } else {
                summaryEl.innerHTML = '<span class="text-slate-500 italic text-xs">Tidak ada data ukuran jersey</span>';
            }
            
            const tbody = document.getElementById('coupon-participants-tbody');
            tbody.innerHTML = '';
            if (participants && participants.length > 0) {
                participants.forEach(p => {
                    const statusHtml = `
                        <button type="button" 
                            onclick="togglePickup(this, ${p.id}, ${p.is_picked_up ? 'true' : 'false'})"
                            class="px-2 py-1 text-[10px] rounded-lg font-bold border transition duration-200 ${p.is_picked_up ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/30 hover:bg-emerald-900/60' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700'}">
                            ${p.is_picked_up ? 'Picked Up' : 'Not Picked'}
                        </button>
                    `;

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-900/40';
                    tr.innerHTML = `
                        <td class="px-4 py-2 font-semibold text-white">${p.name}</td>
                        <td class="px-4 py-2 font-mono text-yellow-400">${p.bib}</td>
                        <td class="px-4 py-2 font-mono text-slate-300">${p.jersey_size || '-'}</td>
                        <td class="px-4 py-2">${statusHtml}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-4 text-center text-slate-500 italic">Tidak ada peserta untuk kupon ini</td></tr>`;
            }
        };

        window.filterCouponModalTable = function() {
            if (!window.currentCouponReportData) return;
            const filterVal = document.getElementById('couponModalFilterPickup').value;
            let participants = window.currentCouponReportData.participants || [];
            
            if (filterVal === '1') {
                participants = participants.filter(p => p.is_picked_up == 1 || p.is_picked_up === true);
            } else if (filterVal === '0') {
                participants = participants.filter(p => p.is_picked_up == 0 || p.is_picked_up === false || p.is_picked_up === null);
            }
            
            window.renderCouponReportModalContent(participants);
        };

        window.showCouponReportModal = function(report, couponCode) {
            const modal = document.getElementById('coupon-report-modal');
            if (!modal) return;
            
            window.currentCouponReportData = report;
            document.getElementById('coupon-modal-subtitle').textContent = 'Kupon: ' + couponCode;
            
            const filterSelect = document.getElementById('couponModalFilterPickup');
            if (filterSelect) filterSelect.value = 'all';
            
            window.renderCouponReportModalContent(report.participants || []);
            modal.classList.remove('hidden');
        };

        window.closeCouponReportModal = function() {
            const modal = document.getElementById('coupon-report-modal');
            if (modal) modal.classList.add('hidden');
        };

        window.triggerManualCouponReport = function() {
            if (window.currentCouponReport) {
                showCouponReportModal(window.currentCouponReport, currentCouponText);
            }
        };

        // Initialize coupon report button visibility
        const btnShowCouponReport = document.getElementById('btn-show-coupon-report');
        if (btnShowCouponReport && window.currentCouponReport) {
            btnShowCouponReport.classList.remove('hidden');
        }

        // DOORPRIZE DRAW SYSTEM
        // ==========================================
        window.doorprizeParticipants = [];
        window.doorprizeInterval = null;
        window.doorprizeIsSpinning = false;
        window.doorprizeWinners = [];
        const eventIdForStorage = "{{ $event->id }}";

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
                btnStart.innerHTML = `<span>⏳ Loading Data...</span>`;
            }
            if (totalPaidEl) totalPaidEl.textContent = 'Loading...';
            
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
                                btnStart.innerHTML = `<span class="text-base">▶</span> Start Draw`;
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
            
            // UI Adjustments
            document.getElementById('doorprizePlaceholder').classList.add('hidden');
            document.getElementById('doorprizeWinner').classList.add('hidden');
            document.getElementById('doorprizeLiveSpin').classList.remove('hidden');
            
            document.getElementById('liveDrawName').textContent = drawName;
            
            const board = document.getElementById('doorprizeDrawBoard');
            board.classList.add('glow-blue');
            board.classList.remove('glow-green');
            
            const btnStart = document.getElementById('btnStartDoorprize');
            const btnStop = document.getElementById('btnStopDoorprize');
            
            btnStart.disabled = true;
            btnStart.classList.add('opacity-50', 'cursor-not-allowed');
            
            btnStop.disabled = false;
            btnStop.classList.remove('bg-slate-800', 'text-slate-500', 'cursor-not-allowed');
            btnStop.classList.add('bg-red-600', 'hover:bg-red-500', 'text-white', 'shadow-lg', 'shadow-red-600/20');
            
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
            
            // Show Winner Detail
            document.getElementById('doorprizeLiveSpin').classList.add('hidden');
            document.getElementById('doorprizeWinner').classList.remove('hidden');
            
            const board = document.getElementById('doorprizeDrawBoard');
            board.classList.remove('glow-blue');
            board.classList.add('glow-green');
            
            document.getElementById('winnerDrawName').textContent = drawName;
            
            const bib = finalWinner.bib_number || '';
            const parts = bib.split('-');
            const lastPart = parts[parts.length - 1] || '';
            let processedBib = '-';
            if (lastPart) {
                processedBib = lastPart.startsWith('0') ? '5' + lastPart.substring(1) : lastPart;
            }
            document.getElementById('winnerBib').textContent = processedBib;
            
            // Action Controls Reset
            const btnStart = document.getElementById('btnStartDoorprize');
            const btnStop = document.getElementById('btnStopDoorprize');
            
            btnStart.disabled = false;
            btnStart.classList.remove('opacity-50', 'cursor-not-allowed');
            
            btnStop.disabled = true;
            btnStop.classList.remove('bg-red-600', 'hover:bg-red-500', 'text-white', 'shadow-lg', 'shadow-red-600/20');
            btnStop.classList.add('bg-slate-800', 'text-slate-500', 'cursor-not-allowed');
            
            // Save Winner to Local Storage
            saveWinnerToStorage(finalWinner, drawName);
            renderDoorprizeWinnersList();
            
            // Celebration visual pulse
            const winnerBlock = document.getElementById('doorprizeWinner');
            winnerBlock.classList.remove('animate-bounce-short');
            void winnerBlock.offsetWidth; // Trigger reflow
            winnerBlock.classList.add('animate-bounce-short');
        };

        function getDoorprizeWinnersFromStorage() {
            const key = 'doorprize_winners_' + eventIdForStorage;
            const stored = localStorage.getItem(key);
            return stored ? JSON.parse(stored) : [];
        }

        function saveWinnerToStorage(winner, drawName) {
            const key = 'doorprize_winners_' + eventIdForStorage;
            const list = getDoorprizeWinnersFromStorage();
            
            // Avoid duplicate entry if same ID somehow gets added
            if (!list.some(w => w.id === winner.id)) {
                list.push({
                    id: winner.id,
                    bib_number: winner.bib_number || '-',
                    name: winner.name || '-',
                    phone: winner.phone || '-',
                    address: [winner.address, winner.city, winner.province]
                        .filter(part => part && part.trim() !== '')
                        .join(', ') || '-',
                    draw_name: drawName,
                    drawn_at: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
                });
                localStorage.setItem(key, JSON.stringify(list));
            }
        }

        window.clearDoorprizeWinners = function() {
            if (confirm('Apakah Anda yakin ingin menghapus semua history pemenang doorprize untuk event ini?')) {
                const key = 'doorprize_winners_' + eventIdForStorage;
                localStorage.removeItem(key);
                renderDoorprizeWinnersList();
            }
        };

        function renderDoorprizeWinnersList() {
            const list = getDoorprizeWinnersFromStorage();
            const container = document.getElementById('doorprizeWinnerList');
            if (!container) return;
            
            if (list.length === 0) {
                container.innerHTML = `<div class="text-xs text-slate-500 text-center py-8">Belum ada pemenang yang ditarik.</div>`;
                return;
            }
            
            let html = '';
            list.slice().reverse().forEach((w, index) => {
                html += `
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3 flex flex-col gap-1.5 transition hover:border-slate-700">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] bg-blue-500/10 text-blue-400 border border-blue-500/20 px-1.5 py-0.5 rounded font-bold">BIB ${w.bib_number}</span>
                        <span class="text-[10px] text-slate-500 font-medium">${w.drawn_at}</span>
                    </div>
                    ${w.draw_name ? `<div class="text-[10px] text-yellow-400/90 font-semibold tracking-wide uppercase">${w.draw_name}</div>` : ''}
                    <div class="text-xs font-bold text-slate-200 truncate" title="${w.name}">${w.name}</div>
                    <div class="text-[10px] text-slate-400 flex flex-col gap-0.5 mt-0.5 border-t border-slate-800/60 pt-1.5">
                        <span class="truncate">📞 ${w.phone}</span>
                        <span class="truncate" title="${w.address}">📍 ${w.address}</span>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }

        window.exportDoorprizeWinners = function() {
            const list = getDoorprizeWinnersFromStorage();
            if (list.length === 0) {
                alert('Belum ada pemenang untuk di-export.');
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

        const initialParticipants = @json($participants);
        renderPagination(initialParticipants, serializeAll());
        renderSalesChart(initialSales);
    })();
</script>
@endpush
