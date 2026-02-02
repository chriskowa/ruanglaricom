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

            <form id="report-filters" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-slate-300">Search</label>
                    <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama atau email"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                </div>
                <div>
                    <label class="text-xs text-slate-300">Status Pembayaran</label>
                    <select name="payment_status"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        @php
                            $paymentStatus = $filters['payment_status'] ?? 'all';
                            $paymentOptions = ['all' => 'Semua', 'paid' => 'paid', 'settlement' => 'settlement', 'capture' => 'capture', 'pending' => 'pending', 'failed' => 'failed', 'cancel' => 'cancel', 'expire' => 'expire', 'deny' => 'deny'];
                        @endphp
                        @foreach($paymentOptions as $val => $label)
                            <option value="{{ $val }}" @selected($paymentStatus === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
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
                    <label class="text-xs text-slate-300">Per Halaman</label>
                    <select name="per_page"
                        class="mt-1 w-full rounded-xl bg-slate-900 border border-slate-700 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-neon/40">
                        @foreach([10,25,50,100] as $pp)
                            <option value="{{ $pp }}" @selected((int) ($filters['per_page'] ?? 25) === $pp)>{{ $pp }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-neon text-dark font-bold hover:bg-lime-300 transition">
                        Terapkan
                    </button>
                    <button id="report-reset" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-200 hover:bg-slate-700 transition">
                        Reset
                    </button>
                </div>
            </form>

            <div class="mt-4 overflow-x-auto border border-slate-700 rounded-2xl">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-900/60 text-slate-300">
                        <tr>
                            <th class="text-left font-semibold px-4 py-3">Nama</th>
                            <th class="text-left font-semibold px-4 py-3">Email</th>
                            <th class="text-left font-semibold px-4 py-3">Tanggal Registrasi</th>
                            <th class="text-left font-semibold px-4 py-3">Status Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody id="participants-tbody" class="divide-y divide-slate-800">
                        @foreach($participants as $p)
                            <tr class="hover:bg-slate-900/40">
                                <td class="px-4 py-3 font-semibold text-white">{{ $p->name }}</td>
                                <td class="px-4 py-3 text-slate-200">{{ $p->email }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ \Illuminate\Support\Carbon::parse($p->created_at)->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-800 text-slate-200">
                                        {{ $p->payment_status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        @if($participants->isEmpty())
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-400">Tidak ada data.</td>
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
                    <thead class="bg-slate-900/60 text-slate-300">
                        <tr>
                            <th class="text-left font-semibold px-4 py-3">Kode</th>
                            <th class="text-right font-semibold px-4 py-3">Dipakai</th>
                            <th class="text-right font-semibold px-4 py-3">Total Diskon</th>
                        </tr>
                    </thead>
                    <tbody id="coupon-tbody" class="divide-y divide-slate-800">
                        @foreach($couponUsage as $c)
                            <tr class="hover:bg-slate-900/40">
                                <td class="px-4 py-3 font-mono font-bold text-white">{{ $c->code }}</td>
                                <td class="px-4 py-3 text-right text-slate-200">{{ number_format((int) $c->total_transactions) }}</td>
                                <td class="px-4 py-3 text-right text-slate-200">{{ number_format((float) $c->total_discount, 0, ',', '.') }}</td>
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
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('report-filters');
        const resetBtn = document.getElementById('report-reset');
        const loadingEl = document.getElementById('report-loading');
        const tbody = document.getElementById('participants-tbody');
        const metaEl = document.getElementById('participants-meta');
        const paginationEl = document.getElementById('participants-pagination');
        const couponTbody = document.getElementById('coupon-tbody');

        const statTotal = document.getElementById('stat-total');
        const statSold = document.getElementById('stat-sold');
        const statPending = document.getElementById('stat-pending');
        const statRemaining = document.getElementById('stat-remaining');

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

        function paymentPill(status) {
            const text = (status || '').toString();
            return `<span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-slate-800 text-slate-200">${text}</span>`;
        }

        function renderParticipants(paginator) {
            const rows = (paginator && paginator.data) ? paginator.data : [];
            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Tidak ada data.</td></tr>`;
            } else {
                tbody.innerHTML = rows.map((p) => {
                    return `
                        <tr class="hover:bg-slate-900/40">
                            <td class="px-4 py-3 font-semibold text-white">${(p.name || '-')}</td>
                            <td class="px-4 py-3 text-slate-200">${(p.email || '-')}</td>
                            <td class="px-4 py-3 text-slate-300">${formatDateTime(p.created_at)}</td>
                            <td class="px-4 py-3">${paymentPill(p.payment_status)}</td>
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
                btn.addEventListener('click', function () {
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
                    <tr class="hover:bg-slate-900/40">
                        <td class="px-4 py-3 font-mono font-bold text-white">${(c.code || '-')}</td>
                        <td class="px-4 py-3 text-right text-slate-200">${formatNumber(c.total_transactions)}</td>
                        <td class="px-4 py-3 text-right text-slate-200">${formatCurrency(c.total_discount)}</td>
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
        }

        async function fetchReport(payload) {
            setLoading(true);
            try {
                const res = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Request gagal');
                }

                renderStats(data.report);
                renderCoupons(data.coupon_usage);
                renderParticipants(data.participants);
                renderPagination(data.participants, payload);
            } catch (e) {
                alert(e.message || 'Terjadi kesalahan');
            } finally {
                setLoading(false);
            }
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const payload = serializeForm();
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
                const payload = serializeForm();
                payload.page = 1;
                fetchReport(payload);
            });
        }

        renderPagination(@json($participants->toArray()), serializeForm());
    })();
</script>
@endpush

