@extends('layouts.pacerhub')

@section('title', 'Admin - Edit Master GPX')

@section('content')
    @php($withSidebar = true)

    <div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-start justify-between gap-4 flex-wrap mb-6">
                <div>
                    <h1 class="text-3xl font-black text-white">Edit Master GPX</h1>
                    <p class="text-slate-400 mt-1">{{ $item->title }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('tools.pace-pro.gpx', $item) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 transition">
                        View GPX
                    </a>
                    <a href="{{ route('admin.master-gpx.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 transition">
                        Kembali
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-500/10 border border-red-500/40 text-red-300 px-4 py-3 rounded-xl text-sm font-semibold">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4">
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Jarak</div>
                    <div class="mt-1 text-xl font-black text-white">{{ $item->distance_km ? number_format((float) $item->distance_km, 2) . ' km' : '-' }}</div>
                </div>
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4">
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Elev Gain</div>
                    <div class="mt-1 text-xl font-black text-white">{{ $item->elevation_gain_m !== null ? '+' . $item->elevation_gain_m . 'm' : '-' }}</div>
                </div>
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4">
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Elev Loss</div>
                    <div class="mt-1 text-xl font-black text-white">{{ $item->elevation_loss_m !== null ? '-' . $item->elevation_loss_m . 'm' : '-' }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.master-gpx.update', $item) }}" enctype="multipart/form-data" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Judul</label>
                    <input name="title" value="{{ old('title', $item->title) }}" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-neon/30 focus:border-neon/50">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Event (opsional)</label>
                    <select name="event_id" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-neon/30 focus:border-neon/50">
                        <option value="">-</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev->id }}" @selected(old('event_id', $item->event_id) == $ev->id)>{{ $ev->name }} ({{ optional($ev->start_at)->format('Y-m-d') }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ganti File GPX (opsional)</label>
                    <input name="gpx_file" type="file" accept=".gpx,.xml" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold">
                    <div class="mt-2 text-xs text-slate-500 font-semibold">Jika diganti, jarak/elevasi akan dihitung ulang.</div>
                </div>

                <div class="flex items-center gap-2">
                    <input id="is_published" name="is_published" type="checkbox" value="1" class="accent-neon" @checked(old('is_published', $item->is_published))>
                    <label for="is_published" class="text-sm font-bold text-slate-200">Published</label>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catatan</label>
                    <textarea name="notes" rows="4" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-neon/30 focus:border-neon/50">{{ old('notes', $item->notes) }}</textarea>
                </div>

                <div class="pt-2 flex items-center justify-end">
                    <button type="submit" class="px-5 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.master-gpx.destroy', $item) }}" onsubmit="return confirm('Hapus Master GPX ini?')" class="mt-3 flex justify-start">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 font-black hover:bg-red-500/20 transition">
                    Hapus
                </button>
            </form>
        </div>
    </div>
@endsection
