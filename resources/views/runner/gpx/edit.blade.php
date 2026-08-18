@extends('layouts.pacerhub')

@section('title', 'Edit Rute GPX - ' . $item->title)

@section('content')
    @php($withSidebar = true)

    <div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4 flex-wrap mb-6">
                <div>
                    <h1 class="text-3xl font-black text-white">Edit Rute GPX</h1>
                    <p class="text-slate-400 mt-1 text-sm">{{ $item->title }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 text-xs transition flex items-center gap-2">
                        <i class="fas fa-eye text-xs"></i>
                        <span>Lihat Rute</span>
                    </a>
                    <a href="{{ route('runner.gpx.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 text-xs transition">
                        Kembali
                    </a>
                </div>
            </div>

            @if($errors->any())
                <div class="mb-4 bg-red-500/10 border border-red-500/40 text-red-300 px-4 py-3 rounded-xl text-sm font-semibold">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Summary Stats Badges -->
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4">
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Jarak Rute</div>
                    <div class="mt-1 text-xl font-black text-white">{{ $item->distance_km ? number_format((float) $item->distance_km, 2) . ' km' : '-' }}</div>
                </div>
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4">
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Elev Gain</div>
                    <div class="mt-1 text-xl font-black text-emerald-400">{{ $item->elevation_gain_m !== null ? '+' . $item->elevation_gain_m . 'm' : '-' }}</div>
                </div>
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4">
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Elev Loss</div>
                    <div class="mt-1 text-xl font-black text-rose-400">{{ $item->elevation_loss_m !== null ? '-' . $item->elevation_loss_m . 'm' : '-' }}</div>
                </div>
            </div>

            <!-- Form Edit -->
            <form method="POST" action="{{ route('runner.gpx.update', $item) }}" class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Rute</label>
                    <input name="title" value="{{ old('title', $item->title) }}" required class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:border-slate-600 text-sm">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kota / Lokasi</label>
                    <input name="city" value="{{ old('city', $item->city) }}" placeholder="Mis. Jakarta Pusat" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:border-slate-600 text-sm">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Tipe Rute</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="route-type-btn {{ (old('route_type', $item->route_type) === 'road' || !old('route_type', $item->route_type)) ? 'is-active' : '' }} flex items-center justify-center p-2.5 rounded-xl border border-slate-800 bg-slate-950 hover:border-slate-700 cursor-pointer text-xs font-semibold text-center select-none shadow-sm transition">
                            <input type="radio" name="route_type" value="road" class="hidden" {{ old('route_type', $item->route_type) === 'road' || !old('route_type', $item->route_type) ? 'checked' : '' }}>
                            <span>Road</span>
                        </label>
                        <label class="route-type-btn {{ old('route_type', $item->route_type) === 'trail' ? 'is-active' : '' }} flex items-center justify-center p-2.5 rounded-xl border border-slate-800 bg-slate-950 hover:border-slate-700 cursor-pointer text-xs font-semibold text-center select-none shadow-sm transition">
                            <input type="radio" name="route_type" value="trail" class="hidden" {{ old('route_type', $item->route_type) === 'trail' ? 'checked' : '' }}>
                            <span>Trail</span>
                        </label>
                        <label class="route-type-btn {{ old('route_type', $item->route_type) === 'track' ? 'is-active' : '' }} flex items-center justify-center p-2.5 rounded-xl border border-slate-800 bg-slate-950 hover:border-slate-700 cursor-pointer text-xs font-semibold text-center select-none shadow-sm transition">
                            <input type="radio" name="route_type" value="track" class="hidden" {{ old('route_type', $item->route_type) === 'track' ? 'checked' : '' }}>
                            <span>Track</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Deskripsi Rute (Pemandangan, Medan, Tips)</label>
                    <textarea name="description" rows="5" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:border-slate-600 text-xs leading-relaxed" placeholder="Jelaskan jenis permukaan rute (aspal/trail), rintangan, rekomendasi jam lari, water station, dll.">{{ old('description', $item->description) }}</textarea>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:border-slate-600 text-xs" placeholder="Catatan internal atau saran untuk runner lain...">{{ old('notes', $item->notes) }}</textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3">
                    <a href="{{ route('runner.gpx.index') }}" class="px-5 py-3 rounded-xl bg-slate-800 text-slate-300 font-bold hover:bg-slate-700 transition text-xs">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition text-xs shadow-lg shadow-neon/10 cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
    <style>
        .route-type-btn {
            background-color: #020617 !important;
            border-color: #1e293b !important;
            color: #cbd5e1 !important;
            transition: all 0.2s ease-in-out;
        }
        .route-type-btn.is-active,
        .route-type-btn:has(input:checked) {
            background-color: #ccff00 !important;
            background: #ccff00 !important;
            border-color: #ccff00 !important;
            color: #020617 !important;
            font-weight: 700 !important;
            box-shadow: 0 0 12px rgba(204, 255, 0, 0.25) !important;
        }
        .route-type-btn.is-active span,
        .route-type-btn:has(input:checked) span {
            color: #020617 !important;
            font-weight: 700 !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function syncRouteType() {
                document.querySelectorAll('input[name="route_type"]').forEach(r => {
                    const label = r.closest('.route-type-btn');
                    if (label) {
                        if (r.checked) {
                            label.classList.add('is-active');
                        } else {
                            label.classList.remove('is-active');
                        }
                    }
                });
            }

            document.querySelectorAll('input[name="route_type"]').forEach(radio => {
                radio.addEventListener('change', syncRouteType);
            });

            document.querySelectorAll('.route-type-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = this.querySelector('input[name="route_type"]');
                    if (input) {
                        input.checked = true;
                        syncRouteType();
                    }
                });
            });

            syncRouteType();
        });
    </script>
    @endpush
@endsection
