@extends('layouts.pacerhub')
@php($withSidebar = false)

@section('title', 'Pendaftaran Komunitas')

@section('content')
<div class="min-h-screen px-4 md:px-8 py-10">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <div class="text-neon font-mono text-xs tracking-widest uppercase">Community Registration</div>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Pendaftaran Komunitas</h1>
            <div class="text-slate-400 text-sm mt-1">Pilih event managed, isi PIC, lalu tambah peserta komunitas.</div>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-900/30 border border-red-500/30 text-red-200 rounded-2xl p-4 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('community.register.start') }}" method="POST" class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih Event</label>
                <select name="event_id" required class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon">
                    <option value="">-- Pilih event managed --</option>
                    @foreach($events as $e)
                        <option value="{{ $e->id }}">
                            {{ $e->name }}{{ $e->start_at ? ' • ' . $e->start_at->format('d M Y') : '' }}{{ $e->location_name ? ' • ' . $e->location_name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Komunitas</label>
                <input type="text" name="community_name" value="{{ old('community_name') }}" required class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="Contoh: Komunitas Lari Bandung">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama PIC</label>
                    <input type="text" name="pic_name" value="{{ old('pic_name') }}" required class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="Nama lengkap">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email PIC</label>
                    <input type="email" name="pic_email" value="{{ old('pic_email') }}" required class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="email@contoh.com">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">WhatsApp PIC</label>
                    <input type="text" name="pic_phone" value="{{ old('pic_phone') }}" required class="w-full px-4 py-3 rounded-2xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="08xxxxxxxxxx">
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end">
                <button type="submit" class="px-6 py-3 rounded-2xl bg-neon text-dark font-black hover:bg-neon/90 transition">
                    Lanjut ke Form Peserta
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

