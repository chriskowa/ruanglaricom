@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Edit Race')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('admin.races.show', $race) }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Back to Race Detail
            </a>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">EDIT RACE</h1>
            <p class="text-slate-400 mt-1">Update konfigurasi race.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/40 text-red-200 p-4 rounded-xl">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.races.update', $race) }}" enctype="multipart/form-data" class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">Nama Race</label>
                <input type="text" name="name" value="{{ old('name', $race->name) }}" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Slug (untuk landing)</label>
                    <input type="text" name="slug" value="{{ old('slug', $race->slug) }}" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none" placeholder="contoh: track-day-2026">
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                        <input type="checkbox" name="is_published" value="1" class="rounded border-slate-600 bg-slate-900 text-red-500 focus:ring-red-500" @checked(old('is_published', $race->is_published))>
                        Publish (aktifkan landing publik)
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Lokasi (opsional)</label>
                    <input type="text" name="location_name" value="{{ old('location_name', $race->location_name) }}" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Contoh: GBK Senayan">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Tanggal Mulai (opsional)</label>
                    <input type="datetime-local" name="start_at" value="{{ old('start_at', $race->start_at?->format('Y-m-d\\TH:i')) }}" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Tanggal Selesai (opsional)</label>
                    <input type="datetime-local" name="end_at" value="{{ old('end_at', $race->end_at?->format('Y-m-d\\TH:i')) }}" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-200 mb-2">Deskripsi (opsional)</label>
                    <textarea name="description" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Info race, aturan, dll...">{{ old('description', $race->description) }}</textarea>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">Owner (opsional)</label>
                <select name="created_by" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white focus:ring-2 focus:ring-red-500 outline-none">
                    <option value="">Tidak diubah</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(old('created_by', $race->created_by) == $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Logo saat ini</label>
                    <div class="w-full aspect-square rounded-2xl bg-slate-950 border border-slate-700 overflow-hidden flex items-center justify-center">
                        @if ($race->logo_path)
                            <img src="{{ Storage::disk('public')->url($race->logo_path) }}" class="w-full h-full object-contain" />
                        @else
                            <div class="text-slate-500 font-bold">Tidak ada logo</div>
                        @endif
                    </div>
                    @if ($race->logo_path)
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-300">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-600 bg-slate-900 text-red-500 focus:ring-red-500">
                            Hapus logo
                        </label>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Upload logo baru (opsional)</label>
                    <input type="file" name="logo" accept="image/png,image/jpeg" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-slate-200 focus:ring-2 focus:ring-red-500 outline-none">
                    <div class="text-xs text-slate-500 mt-2">PNG/JPG max 2MB, minimal 200x200.</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Banner saat ini</label>
                    <div class="w-full aspect-[16/9] rounded-2xl bg-slate-950 border border-slate-700 overflow-hidden flex items-center justify-center">
                        @if ($race->banner_path)
                            <img src="{{ Storage::disk('public')->url($race->banner_path) }}" class="w-full h-full object-cover" />
                        @else
                            <div class="text-slate-500 font-bold">Tidak ada banner</div>
                        @endif
                    </div>
                    @if ($race->banner_path)
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-300">
                            <input type="checkbox" name="remove_banner" value="1" class="rounded border-slate-600 bg-slate-900 text-red-500 focus:ring-red-500">
                            Hapus banner
                        </label>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-2">Upload banner baru (opsional)</label>
                    <input type="file" name="banner" accept="image/png,image/jpeg" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-slate-200 focus:ring-2 focus:ring-red-500 outline-none">
                    <div class="text-xs text-slate-500 mt-2">PNG/JPG max 4MB, rekomendasi minimal 900x400.</div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">Gallery saat ini</label>
                @php($gallery = is_array($race->gallery_paths) ? $race->gallery_paths : [])
                @if (count($gallery))
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach ($gallery as $p)
                            <div class="bg-slate-950 border border-slate-700 rounded-xl overflow-hidden">
                                <div class="aspect-square">
                                    <img src="{{ Storage::disk('public')->url($p) }}" class="w-full h-full object-cover" loading="lazy" />
                                </div>
                                <label class="flex items-center gap-2 p-3 text-xs text-slate-300">
                                    <input type="checkbox" name="remove_gallery[]" value="{{ $p }}" class="rounded border-slate-600 bg-slate-900 text-red-500 focus:ring-red-500">
                                    Hapus
                                </label>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-slate-500 font-bold">Belum ada foto.</div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">Tambah foto gallery (opsional)</label>
                <input type="file" name="gallery[]" multiple accept="image/png,image/jpeg" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-slate-200 focus:ring-2 focus:ring-red-500 outline-none">
                <div class="text-xs text-slate-500 mt-2">Bisa pilih beberapa foto. PNG/JPG max 4MB per file.</div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-200 mb-2">Prize (opsional)</label>
                <textarea name="prize_info" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Contoh: Juara 1: ...&#10;Juara 2: ...">{{ old('prize_info', $race->prize_info) }}</textarea>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="px-5 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black">Save</button>
                <a href="{{ route('admin.races.show', $race) }}" class="px-5 py-3 rounded-xl bg-slate-950 hover:bg-slate-900 border border-slate-700 text-slate-200 font-bold text-center">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
