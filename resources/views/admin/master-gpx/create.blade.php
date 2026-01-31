@extends('layouts.pacerhub')

@section('title', 'Admin - Tambah Master GPX')

@section('content')
    @php($withSidebar = true)

    <div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-start justify-between gap-4 flex-wrap mb-6">
                <div>
                    <h1 class="text-3xl font-black text-white">Tambah Master GPX</h1>
                    <p class="text-slate-400 mt-1">Upload GPX dan kaitkan ke Event.</p>
                </div>
                <a href="{{ route('admin.master-gpx.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 transition">
                    Kembali
                </a>
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

            <form method="POST" action="{{ route('admin.master-gpx.store') }}" enctype="multipart/form-data" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 space-y-4">
                @csrf

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Judul</label>
                    <input name="title" value="{{ old('title') }}" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-neon/30 focus:border-neon/50" placeholder="Mis. Rute 10K">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Event (opsional)</label>
                    <select name="running_event_id" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-neon/30 focus:border-neon/50">
                        <option value="">-</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev->id }}" @selected(old('running_event_id') == $ev->id)>{{ $ev->name }} ({{ optional($ev->event_date)->format('Y-m-d') }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">File GPX</label>
                    <input name="gpx_file" type="file" accept=".gpx,.xml" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold">
                </div>

                <div class="flex items-center gap-2">
                    <input id="is_published" name="is_published" type="checkbox" value="1" class="accent-neon" @checked(old('is_published', true))>
                    <label for="is_published" class="text-sm font-bold text-slate-200">Published</label>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catatan</label>
                    <textarea name="notes" rows="4" class="mt-1 w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white font-semibold focus:outline-none focus:ring-2 focus:ring-neon/30 focus:border-neon/50" placeholder="Optional">{{ old('notes') }}</textarea>
                </div>

                <div class="pt-2 flex items-center justify-end">
                    <button type="submit" class="px-5 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
