@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Event Management')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                EVENT MANAGEMENT
            </h1>
            <p class="text-slate-400 mt-1">Kelola semua event dari seluruh EO.</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-end w-full md:w-auto">
            <form action="{{ route('admin.events.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari event, lokasi, atau EO..." class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 w-64 focus:outline-none focus:border-neon">
                <select name="status" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:outline-none focus:border-neon">
                    <option value="">Semua status</option>
                    <option value="draft" {{ ($status ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ ($status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="archived" {{ ($status ?? '') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
                <select name="featured" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:outline-none focus:border-neon">
                    <option value="">Featured: semua</option>
                    <option value="1" {{ ($featured ?? '') === '1' ? 'selected' : '' }}>Featured</option>
                    <option value="0" {{ ($featured ?? '') === '0' ? 'selected' : '' }}>Unfeatured</option>
                </select>
                <select name="active" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:outline-none focus:border-neon">
                    <option value="">Aktif: semua</option>
                    <option value="1" {{ ($active ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ ($active ?? '') === '0' ? 'selected' : '' }}>Non-aktif</option>
                </select>
                <select name="eo_id" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:outline-none focus:border-neon">
                    <option value="">Semua EO</option>
                    @foreach(($eventOrganizers ?? []) as $eo)
                        <option value="{{ $eo->id }}" {{ (string)($eoId ?? '') === (string)$eo->id ? 'selected' : '' }}>{{ $eo->name }}</option>
                    @endforeach
                </select>
                <select name="sort" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm focus:outline-none focus:border-neon">
                    <option value="created_at_desc" {{ ($sort ?? 'created_at_desc') === 'created_at_desc' ? 'selected' : '' }}>Terbaru ditambahkan</option>
                    <option value="created_at_asc" {{ ($sort ?? '') === 'created_at_asc' ? 'selected' : '' }}>Terlama ditambahkan</option>
                    <option value="start_at_desc" {{ ($sort ?? '') === 'start_at_desc' ? 'selected' : '' }}>Tanggal event terbaru</option>
                    <option value="start_at_asc" {{ ($sort ?? '') === 'start_at_asc' ? 'selected' : '' }}>Tanggal event terlama</option>
                    <option value="name_asc" {{ ($sort ?? '') === 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="name_desc" {{ ($sort ?? '') === 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                </select>
                <button type="submit" class="px-3 py-2 rounded-xl bg-slate-700 text-white hover:bg-slate-600 transition-all text-sm">Terapkan</button>
            </form>
            <form action="{{ route('admin.events.sync') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-500 transition-all font-bold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Sync Events
                </button>
            </form>
            <a href="{{ route('admin.events.import') }}" class="px-4 py-2 rounded-xl bg-slate-700 text-white hover:bg-slate-600 transition-all font-bold text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Import CSV
            </a>
            <a href="{{ route('admin.events.create') }}" class="px-4 py-2 rounded-xl bg-neon text-dark hover:bg-neon/90 transition-all font-bold text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                New Event
            </a>
        </div>
    </div>

    <!-- Events Table -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10" id="events-table-container">
        @include('admin.events.partials.table')
    </div>

    <div id="ph-toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[60] hidden">
        <div id="ph-toast-inner" class="px-4 py-3 rounded-xl border border-slate-700 bg-slate-900 text-white shadow-2xl"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        const statusSelect = document.querySelector('select[name="status"]');
        const featuredSelect = document.querySelector('select[name="featured"]');
        const activeSelect = document.querySelector('select[name="active"]');
        const eoSelect = document.querySelector('select[name="eo_id"]');
        const sortSelect = document.querySelector('select[name="sort"]');
        const tableContainer = document.getElementById('events-table-container');
        const form = document.querySelector('form[action="{{ route('admin.events.index') }}"]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const toast = document.getElementById('ph-toast');
        const toastInner = document.getElementById('ph-toast-inner');

        let timeout = null;

        // Prevent default form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchEvents();
        });

        // Search with debounce
        searchInput.addEventListener('keyup', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                fetchEvents();
            }, 500);
        });

        // Sort change
        sortSelect.addEventListener('change', function() {
            fetchEvents();
        });

        statusSelect.addEventListener('change', function() {
            fetchEvents();
        });

        featuredSelect.addEventListener('change', function() {
            fetchEvents();
        });

        activeSelect.addEventListener('change', function() {
            fetchEvents();
        });

        eoSelect.addEventListener('change', function() {
            fetchEvents();
        });

        // Pagination clicks
        tableContainer.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                const link = e.target.tagName === 'A' ? e.target : e.target.closest('a');
                if (link && link.href && !link.href.includes('javascript')) {
                    // Check if it's an action button (edit/view) or pagination
                    if (link.closest('.pagination') || link.rel === 'next' || link.rel === 'prev') {
                        e.preventDefault();
                        fetchEvents(link.href);
                    }
                }
            }
        });

        tableContainer.addEventListener('click', function(e) {
            const button = e.target.closest('button[data-action]');
            if (!button) return;
            e.preventDefault();

            const action = button.dataset.action;
            const url = button.dataset.url;
            const lockVersion = button.dataset.lockVersion;
            const statusValue = button.dataset.status;

            if (action === 'delete') {
                const ok = confirm('Hapus event ini?');
                if (!ok) return;
            }

            const body = new URLSearchParams();
            if (lockVersion !== undefined) body.set('lock_version', lockVersion);
            if (statusValue) body.set('status', statusValue);

            let method = 'POST';
            if (action === 'delete') {
                method = 'DELETE';
            }

            button.disabled = true;

            fetch(url, {
                method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: method === 'DELETE' ? null : body.toString(),
            })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw { status: response.status, data };
                }
                return data;
            })
            .then((data) => {
                showToast(data.message || 'Berhasil', true);
                fetchEvents();
            })
            .catch((err) => {
                const msg = err?.data?.message || 'Terjadi kesalahan.';
                showToast(msg, false);
                fetchEvents();
            })
            .finally(() => {
                button.disabled = false;
            });
        });

        function fetchEvents(url = null) {
            // Show loading state (optional)
            tableContainer.style.opacity = '0.5';

            const currentUrl = url || "{{ route('admin.events.index') }}";
            const params = new URLSearchParams(url ? new URL(url).search : '');
            
            // If not a direct pagination link, update params from inputs
            if (!url) {
                if (searchInput.value) params.set('search', searchInput.value);
                if (sortSelect.value) params.set('sort', sortSelect.value);
                if (statusSelect.value) params.set('status', statusSelect.value);
                if (featuredSelect.value !== '') params.set('featured', featuredSelect.value);
                if (activeSelect.value !== '') params.set('active', activeSelect.value);
                if (eoSelect.value) params.set('eo_id', eoSelect.value);
            } else {
                // Ensure search/sort are persisted if paginating
                if (searchInput.value && !params.has('search')) params.set('search', searchInput.value);
                if (sortSelect.value && !params.has('sort')) params.set('sort', sortSelect.value);
                if (statusSelect.value && !params.has('status')) params.set('status', statusSelect.value);
                if (featuredSelect.value !== '' && !params.has('featured')) params.set('featured', featuredSelect.value);
                if (activeSelect.value !== '' && !params.has('active')) params.set('active', activeSelect.value);
                if (eoSelect.value && !params.has('eo_id')) params.set('eo_id', eoSelect.value);
            }

            const fetchUrl = `${currentUrl.split('?')[0]}?${params.toString()}`;

            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                tableContainer.innerHTML = html;
                tableContainer.style.opacity = '1';
                
                // Update URL browser history
                window.history.pushState({}, '', fetchUrl);
            })
            .catch(error => {
                console.error('Error:', error);
                tableContainer.style.opacity = '1';
            });
        }

        function showToast(message, ok) {
            toastInner.textContent = message;
            toastInner.className = ok
                ? 'px-4 py-3 rounded-xl border border-green-500/20 bg-green-500/10 text-green-200 shadow-2xl'
                : 'px-4 py-3 rounded-xl border border-red-500/20 bg-red-500/10 text-red-200 shadow-2xl';
            toast.classList.remove('hidden');
            clearTimeout(toast._t);
            toast._t = setTimeout(() => toast.classList.add('hidden'), 2500);
        }
    });
</script>
@endsection
