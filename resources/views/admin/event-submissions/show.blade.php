@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Review Submission')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Review Submission</div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">{{ $submission->event_name }}</h1>
                <p class="text-slate-400 mt-1">{{ $submission->location_name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.event-submissions.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold hover:bg-slate-700 transition">Kembali</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-900/30 border border-green-500/30 text-green-300 rounded-2xl p-4 font-bold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-900/30 border border-red-500/30 text-red-300 rounded-2xl p-4 font-bold">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-card/80 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    @if($submission->banner)
                        <div class="mb-6 rounded-xl overflow-hidden border border-slate-700 relative group">
                            <img src="{{ Storage::url($submission->banner) }}" alt="Banner Event" class="w-full h-auto object-cover max-h-[400px]">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <a href="{{ Storage::url($submission->banner) }}" target="_blank" class="px-4 py-2 bg-white text-black rounded-full font-bold text-sm">Lihat Full Size</a>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-slate-500">Tanggal</div>
                            <div class="text-white font-bold">{{ optional($submission->event_date)->format('d M Y') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Jam</div>
                            <div class="text-white font-bold">{{ $submission->start_time ?: '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Kota</div>
                            <div class="text-white font-bold">{{ $submission->city?->name ?? ($submission->city_text ?: '-') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Jenis Lomba</div>
                            <div class="text-white font-bold">{{ $submission->raceType?->name ?? '-' }}</div>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs text-slate-500">Alamat</div>
                            <div class="text-white font-bold">{{ $submission->location_address ?: '-' }}</div>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs text-slate-500">Link Pendaftaran</div>
                            @if($submission->registration_link)
                                <a href="{{ $submission->registration_link }}" target="_blank" class="text-neon font-bold hover:text-white break-all">{{ $submission->registration_link }}</a>
                            @else
                                <div class="text-white font-bold">-</div>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-xs text-slate-500">Link Media Sosial</div>
                            @if($submission->social_media_link)
                                <a href="{{ $submission->social_media_link }}" target="_blank" class="text-neon font-bold hover:text-white break-all">{{ $submission->social_media_link }}</a>
                            @else
                                <div class="text-white font-bold">-</div>
                            @endif
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Penyelenggara</div>
                            <div class="text-white font-bold">{{ $submission->organizer_name ?: '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Kontak Penyelenggara</div>
                            <div class="text-white font-bold">{{ $submission->organizer_contact ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="mt-5 border-t border-slate-800 pt-5">
                        <div class="text-xs text-slate-500 mb-2">Catatan Pengaju</div>
                        <div class="text-slate-200 whitespace-pre-line">{{ $submission->notes ?: '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-card/80 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status</div>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black border {{ $submission->badge_class }}">{{ strtoupper($submission->status) }}</span>
                    </div>

                    <div class="mt-4 space-y-1">
                        <div class="text-xs text-slate-500">Pengaju</div>
                        <div class="text-white font-bold">{{ $submission->contributor_name ?: '-' }}</div>
                        <div class="text-slate-300 text-sm break-all">{{ $submission->contributor_email }}</div>
                    </div>

                    @if($submission->reviewed_at)
                        <div class="mt-4 border-t border-slate-800 pt-4 space-y-1">
                            <div class="text-xs text-slate-500">Direview</div>
                            <div class="text-white font-bold">{{ $submission->reviewer?->name ?? '-' }}</div>
                            <div class="text-slate-400 text-sm font-mono">{{ $submission->reviewed_at->format('d/m/Y H.i.s') }}</div>
                            <div class="text-slate-200 whitespace-pre-line mt-2">{{ $submission->review_note ?: '-' }}</div>
                        </div>
                    @endif
                </div>

                @if($submission->status === 'pending')
                    <div class="bg-card/80 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 space-y-4">
                        <form action="{{ route('admin.event-submissions.approve', $submission) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Approve</div>
                                <textarea name="review_note" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon" placeholder="Catatan admin (opsional)"></textarea>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                                <input type="checkbox" name="publish" value="1" class="rounded bg-slate-900 border-slate-700" checked>
                                Publish langsung
                            </label>
                            <button type="submit" class="w-full px-4 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition">Approve & Buat Event</button>
                        </form>

                        <form action="{{ route('admin.event-submissions.reject', $submission) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Reject</div>
                                <textarea name="review_note" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-red-400" placeholder="Wajib isi alasan penolakan" required></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-3 rounded-xl bg-red-600 text-white font-black hover:bg-red-500 transition">Reject</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

