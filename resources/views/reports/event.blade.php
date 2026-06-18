@extends('layouts.pacerhub')

@section('title', 'Report Event | Ruang Lari')

@push('styles')
<meta name="robots" content="noindex,nofollow,noarchive">
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

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-card border border-slate-700 rounded-2xl p-4">
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
                    <label class="text-xs text-slate-300">Kupon</label>
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
                    <a id="export-csv-btn" href="#" onclick="this.href=getExportUrl('csv')" class="px-4 py-2 rounded-xl bg-green-600 text-white font-bold hover:bg-green-500 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export CSV
                    </a>
                    <a id="export-xlsx-btn" href="#" onclick="this.href=getExportUrl('xlsx')" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-500 transition flex items-center gap-2">
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
                            <th class="text-left font-semibold px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="participants-tbody" class="divide-y divide-slate-800">
                        @foreach($participants as $p)
                            <tr class="hover:bg-slate-900/40 block md:table-row border-b border-slate-800 md:border-none mb-4 md:mb-0 bg-slate-900/20 md:bg-transparent rounded-xl md:rounded-none p-4 md:p-0">
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
                                <td colspan="8" class="px-4 py-6 text-center text-slate-400">Tidak ada data.</td>
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
                    var msg = name ? (`Berhasil pickup: ${name} • BIB ${bib} • Jersey ${jersey} • Payment ${payment}`) : (res.message || ('Berhasil update pickup #' + participantId));
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
        const initialSales = @json($sales ?? null);
        let salesChart = null;

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
                tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-6 text-center text-slate-400">Tidak ada data.</td></tr>`;
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
                        <tr class="hover:bg-slate-900/40 block md:table-row border-b border-slate-800 md:border-none mb-4 md:mb-0 bg-slate-900/20 md:bg-transparent rounded-xl md:rounded-none p-4 md:p-0">
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
        
        const initialParticipants = @json($participants);
        renderPagination(initialParticipants, serializeAll());
        renderSalesChart(initialSales);
    })();
</script>
@endpush
