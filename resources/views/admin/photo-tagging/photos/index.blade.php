@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Manage Photos: ' . $event->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative font-sans">
    <div class="mb-8 flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.photo-tagging.events.index') }}" class="p-2 bg-slate-800 text-slate-400 hover:text-white rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-white italic tracking-tighter">Manage Photos</h1>
                <p class="text-slate-400">{{ $event->name }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 text-green-400 rounded-xl">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 text-red-400 rounded-xl">
            {{ session('error') }}
        </div>
    @endif
    
    @if($errors->any())
        <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 text-red-400 rounded-xl">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Upload Section -->
    <div class="bg-card/50 border border-slate-700 rounded-2xl p-6 md:p-8 mb-8">
        <h2 class="text-xl font-bold text-white mb-4">Upload Foto Baru</h2>
        <form action="{{ route('admin.photo-tagging.photos.upload', $event->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-4 items-start md:items-center">
            @csrf
            <div class="flex-1 w-full">
                <input type="file" name="photos[]" multiple required accept="image/jpeg,image/png,image/webp" class="w-full bg-slate-800 border border-slate-700 text-slate-300 rounded-xl px-4 py-3 focus:outline-none focus:border-red-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-red-500/20 file:text-red-400 hover:file:bg-red-500/30">
                <p class="text-xs text-slate-500 mt-2">Bisa pilih banyak foto sekaligus. Format: JPG, PNG, WEBP. Max per file: 20MB</p>
            </div>
            <button type="submit" class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-red-600/30 hover:scale-105 shrink-0 whitespace-nowrap">
                Upload Foto
            </button>
        </form>
    </div>

    <!-- Photos Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($photos as $photo)
            <div class="bg-card/50 border border-slate-700 rounded-2xl overflow-hidden group">
                <div class="relative h-48 bg-slate-800">
                    <img src="{{ $photo->image_url }}" alt="Photo" class="w-full h-full object-cover">
                    <div class="absolute top-2 right-2 flex flex-col gap-1">
                        @if($photo->status === 'published')
                            <span class="px-2 py-1 bg-green-500/90 text-white text-[10px] font-bold rounded shadow uppercase tracking-wider backdrop-blur-sm border border-green-400/50">Published</span>
                        @elseif($photo->status === 'tagged')
                            <span class="px-2 py-1 bg-blue-500/90 text-white text-[10px] font-bold rounded shadow uppercase tracking-wider backdrop-blur-sm border border-blue-400/50">Tagged</span>
                        @else
                            <span class="px-2 py-1 bg-yellow-500/90 text-yellow-900 text-[10px] font-bold rounded shadow uppercase tracking-wider backdrop-blur-sm border border-yellow-400/50">Uploaded</span>
                        @endif
                    </div>
                </div>
                
                <div class="p-4">
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Tag Nomor BIB</label>
                        @php
                            $currentTags = $photo->tags->pluck('bib_number')->implode(', ');
                        @endphp
                        <form action="{{ route('admin.photo-tagging.photos.tags', $photo->id) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="bib_numbers" value="{{ $currentTags }}" placeholder="Contoh: 1025, 2031" class="w-full bg-slate-900 border border-slate-700 text-white rounded text-sm px-3 py-2 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-colors">
                            <button type="submit" class="p-2 bg-blue-600 hover:bg-blue-500 text-white rounded transition-colors" title="Simpan Tag">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </form>
                    </div>

                    <div class="flex justify-between items-center pt-3 border-t border-slate-700/50">
                        @if($photo->status !== 'published')
                        <form action="{{ route('admin.photo-tagging.photos.publish', $photo->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-green-400 hover:text-green-300 transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Publish
                            </button>
                        </form>
                        @else
                            <span class="text-xs font-bold text-slate-500 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Live
                            </span>
                        @endif

                        <form action="{{ route('admin.photo-tagging.photos.destroy', $photo->id) }}" method="POST" onsubmit="return confirm('Yakin hapus foto ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-400 transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center border border-dashed border-slate-700 rounded-2xl bg-card/20">
                <svg class="w-16 h-16 text-slate-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-slate-400 font-bold text-lg mb-2">Belum Ada Foto</p>
                <p class="text-slate-500 text-sm">Gunakan form di atas untuk mulai mengupload foto event.</p>
            </div>
        @endforelse
    </div>

    @if($photos->hasPages())
        <div class="mt-8">
            {{ $photos->links() }}
        </div>
    @endif
</div>
@endsection
