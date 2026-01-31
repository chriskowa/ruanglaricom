@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Email Laporan')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('eo.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-white md:ml-2">Email Laporan</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    EMAIL <span class="text-yellow-400">LAPORAN</span>
                </h1>
                <p class="text-slate-400 mt-2">Monitor status pengiriman email laporan (pending/terkirim/gagal).</p>
                <p class="text-slate-500 text-xs mt-1" id="lastUpdatedText"></p>
            </div>
        </div>
    </div>

    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 md:p-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <form method="GET" action="{{ route('eo.email-reports.index') }}" class="flex flex-col md:flex-row gap-3 items-end">
                    <div class="w-full md:w-64">
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Event</label>
                        <select name="event_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors">
                            <option value="">Semua Event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ (string)($filters['event_id'] ?? '') === (string)$event->id ? 'selected' : '' }}>
                                    {{ $event->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-48">
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Status</label>
                        <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors">
                            <option value="">Semua</option>
                            @foreach(['pending' => 'Pending', 'processing' => 'Processing', 'sent' => 'Terkirim', 'failed' => 'Gagal'] as $key => $label)
                                <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-40">
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Dari</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" />
                    </div>
                    <div class="w-full md:w-40">
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Sampai</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" />
                    </div>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition font-bold w-full md:w-auto">
                        Filter
                    </button>
                </form>
            </div>

            <div class="lg:col-span-1">
                <div class="border border-slate-700 rounded-2xl p-4 bg-slate-900/40">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-white font-extrabold">Kirim Email Laporan</h2>
                    </div>
                    <form id="sendReportForm" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Event</label>
                            <select name="event_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" required>
                                <option value="" disabled selected>Pilih event</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}">{{ $event->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Email Tujuan</label>
                            <input type="email" name="to_email" value="{{ auth()->user()->email }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Subject (Opsional)</label>
                            <input type="text" name="subject" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Dari</label>
                                <input type="date" name="date_from" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-1">Sampai</label>
                                <input type="date" name="date_to" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Status Data (Opsional)</label>
                            <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors">
                                <option value="">Semua</option>
                                @foreach(['paid' => 'Paid', 'pending' => 'Pending', 'failed' => 'Failed'] as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p id="sendReportError" class="text-red-400 text-xs hidden"></p>
                        <button type="submit" class="w-full px-6 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-black font-black shadow-lg shadow-yellow-500/20 transition-all">
                            Kirim
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-8 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-700">
                        <th class="text-left py-3 pr-4">Waktu</th>
                        <th class="text-left py-3 pr-4">Event</th>
                        <th class="text-left py-3 pr-4">Tujuan</th>
                        <th class="text-left py-3 pr-4">Subject</th>
                        <th class="text-left py-3 pr-4">Status</th>
                        <th class="text-left py-3 pr-4">Attempt</th>
                        <th class="text-left py-3 pr-4">Terakhir</th>
                        <th class="text-left py-3 pr-4">Error</th>
                        <th class="text-left py-3 pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody id="deliveriesBody" class="text-slate-200">
                    @foreach($deliveries as $d)
                        <tr class="border-b border-slate-800/60">
                            <td class="py-3 pr-4 whitespace-nowrap">{{ $d->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="py-3 pr-4">{{ $d->event?->name }}</td>
                            <td class="py-3 pr-4">{{ $d->to_email }}</td>
                            <td class="py-3 pr-4">{{ $d->subject }}</td>
                            <td class="py-3 pr-4">
                                @php($s = (string) $d->status)
                                @if($s === 'sent')
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">TERKIRIM</span>
                                @elseif($s === 'failed')
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-300 border border-red-500/30">GAGAL</span>
                                @elseif($s === 'processing')
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">PROCESSING</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">PENDING</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4">{{ (int) $d->attempts }}</td>
                            <td class="py-3 pr-4 whitespace-nowrap">{{ $d->last_attempt_at?->format('Y-m-d H:i') }}</td>
                            <td class="py-3 pr-4 text-slate-400">
                                @if($d->status === 'failed')
                                    <div class="text-red-400">{{ $d->failure_code }}</div>
                                    <div class="text-xs">{{ \Illuminate\Support\Str::limit($d->failure_message, 80) }}</div>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                <button type="button" data-resend-id="{{ $d->id }}" class="px-3 py-1 rounded-lg border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs resendBtn">
                                    Kirim Ulang
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    @if($deliveries->count() === 0)
                        <tr>
                            <td colspan="9" class="py-6 text-center text-slate-500">Belum ada riwayat pengiriman.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $deliveries->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const qs = new URLSearchParams(window.location.search);
        const dataUrl = @json(route('eo.email-reports.data')) + (window.location.search || '');
        const sendUrl = @json(route('eo.email-reports.send'));
        const resendUrlTemplate = @json(route('eo.email-reports.resend', ['delivery' => 0]));

        function escapeHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function statusBadge(status) {
            const s = String(status || 'pending');
            if (s === 'sent') return '<span class="px-2 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">TERKIRIM</span>';
            if (s === 'failed') return '<span class="px-2 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-300 border border-red-500/30">GAGAL</span>';
            if (s === 'processing') return '<span class="px-2 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">PROCESSING</span>';
            return '<span class="px-2 py-1 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">PENDING</span>';
        }

        function fmt(dtIso) {
            if (!dtIso) return '';
            const d = new Date(dtIso);
            if (Number.isNaN(d.getTime())) return '';
            const pad = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
        }

        async function refreshData() {
            try {
                const res = await fetch(dataUrl, { headers: { 'Accept': 'application/json' } });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) return;

                const body = document.getElementById('deliveriesBody');
                if (!body) return;

                let html = '';
                (data.deliveries || []).forEach((d) => {
                    const err = (String(d.status) === 'failed')
                        ? `<div class="text-red-400">${escapeHtml(d.failure_code || '')}</div><div class="text-xs">${escapeHtml((d.failure_message || '').slice(0, 80))}</div>`
                        : '';
                    html += `
                        <tr class="border-b border-slate-800/60">
                            <td class="py-3 pr-4 whitespace-nowrap">${escapeHtml(fmt(d.created_at))}</td>
                            <td class="py-3 pr-4">${escapeHtml(d.event_name || '')}</td>
                            <td class="py-3 pr-4">${escapeHtml(d.to_email || '')}</td>
                            <td class="py-3 pr-4">${escapeHtml(d.subject || '')}</td>
                            <td class="py-3 pr-4">${statusBadge(d.status)}</td>
                            <td class="py-3 pr-4">${escapeHtml(d.attempts || 0)}</td>
                            <td class="py-3 pr-4 whitespace-nowrap">${escapeHtml(fmt(d.last_attempt_at))}</td>
                            <td class="py-3 pr-4 text-slate-400">${err}</td>
                            <td class="py-3 pr-4">
                                <button type="button" data-resend-id="${escapeHtml(d.id)}" class="px-3 py-1 rounded-lg border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs resendBtn">
                                    Kirim Ulang
                                </button>
                            </td>
                        </tr>
                    `;
                });

                if (html.trim() === '') {
                    html = '<tr><td colspan="9" class="py-6 text-center text-slate-500">Belum ada riwayat pengiriman.</td></tr>';
                }

                body.innerHTML = html;
                const lastUpdatedText = document.getElementById('lastUpdatedText');
                if (lastUpdatedText) lastUpdatedText.textContent = 'Update terakhir: ' + fmt(new Date().toISOString());
            } catch (e) {
            }
        }

        const form = document.getElementById('sendReportForm');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const errEl = document.getElementById('sendReportError');
                if (errEl) {
                    errEl.classList.add('hidden');
                    errEl.textContent = '';
                }

                const fd = new FormData(form);
                try {
                    const res = await fetch(sendUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: fd,
                        credentials: 'same-origin',
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.ok) {
                        const msg = (data && data.message) ? data.message : 'Gagal enqueue email laporan.';
                        if (errEl) {
                            errEl.textContent = msg;
                            errEl.classList.remove('hidden');
                        }
                        return;
                    }
                    await refreshData();
                } catch (e2) {
                    if (errEl) {
                        errEl.textContent = 'Gagal enqueue email laporan. Coba lagi.';
                        errEl.classList.remove('hidden');
                    }
                }
            });
        }

        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.resendBtn');
            if (!btn) return;

            const id = btn.getAttribute('data-resend-id');
            if (!id) return;

            if (!confirm('Kirim ulang email laporan ini?')) return;

            const url = resendUrlTemplate.replace(/\/0\/resend$/, `/${id}/resend`);

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) {
                    alert((data && data.message) ? data.message : 'Gagal kirim ulang email laporan.');
                    return;
                }
                await refreshData();
            } catch (e2) {
                alert('Gagal kirim ulang email laporan. Coba lagi.');
            }
        });

        if (!qs.get('page')) {
            refreshData();
            setInterval(refreshData, 10000);
        }
    })();
</script>
@endpush
