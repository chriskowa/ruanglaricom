@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Detail Event - ' . $event->name)

@section('content')
<div id="eo-event-detail-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Breadcrumb & Header Navigation -->
    <div class="mb-6 relative z-10" data-aos="fade-up">
        <nav class="flex mb-3" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-medium text-slate-400">
                <li class="inline-flex items-center">
                    <a href="{{ route('eo.dashboard') }}" class="hover:text-white transition-colors">
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3.5 h-3.5 mx-1 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('eo.events.index') }}" class="hover:text-white transition-colors">
                            Master Events
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3.5 h-3.5 mx-1 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="text-white truncate max-w-xs">{{ $event->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    @php
                        $statusColors = [
                            'published' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
                            'draft' => 'bg-slate-700/50 text-slate-300 border border-slate-600/30',
                            'completed' => 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
                            'cancelled' => 'bg-rose-500/20 text-rose-400 border border-rose-500/30',
                        ];
                        $badgeClass = $statusColors[$event->status] ?? 'bg-slate-700/50 text-slate-300 border border-slate-600/30';
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold uppercase tracking-wider {{ $badgeClass }}">
                        {{ $event->status }}
                    </span>
                    <span class="text-xs text-slate-400">ID Event: #{{ $event->id }}</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">
                    {{ $event->name }}
                </h1>
            </div>

            <!-- Quick Action Top Bar -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('eo.events.edit', $event) }}" class="px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold border border-slate-700 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Edit Event
                </a>
                <a href="{{ route('eo.events.participants', $event) }}" class="px-4 py-2 rounded-md bg-cyan-950/60 hover:bg-cyan-900/60 text-cyan-300 hover:text-cyan-200 text-xs font-semibold border border-cyan-800/50 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Kelola Peserta
                </a>
                <a href="{{ $event->public_url }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-md bg-yellow-500 hover:bg-yellow-400 text-black text-xs font-bold transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    Halaman Publik
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 relative z-10">
        
        <!-- Total Registered -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
            <span class="text-xs font-mono text-slate-400 uppercase">Peserta Terdaftar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-white">{{ number_format($totalRegistered) }}</span>
                <span class="text-xs text-slate-400">Total runners</span>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
            <span class="text-xs font-mono text-slate-400 uppercase">Peserta Lunas</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-emerald-400">{{ number_format($totalPaid) }}</span>
                <span class="text-xs text-slate-400">Telah bayar</span>
            </div>
        </div>

        <!-- Total Categories -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
            <span class="text-xs font-mono text-slate-400 uppercase">Kategori Lomba</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-cyan-400">{{ $event->categories->count() }}</span>
                <span class="text-xs text-slate-400">Kategori aktif</span>
            </div>
        </div>

        <!-- Estimated Earnings -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
            <span class="text-xs font-mono text-slate-400 uppercase">Estimasi Penjualan Tiket</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-white">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</span>
                <span class="text-xs text-emerald-400 font-semibold">Lunas</span>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Categories & Event Overview (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Categories Table Panel -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-white">Kategori Lomba & Kuota</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Daftar kategori race yang dibuka pada event ini</p>
                    </div>
                    <a href="{{ route('eo.events.edit', $event) }}" class="text-xs text-yellow-400 hover:text-yellow-300 font-semibold transition-colors">
                        Atur Kategori &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-800/80 text-slate-400 uppercase font-mono border-b border-slate-700">
                            <tr>
                                <th scope="col" class="px-4 py-3">Kategori</th>
                                <th scope="col" class="px-4 py-3">Jarak</th>
                                <th scope="col" class="px-4 py-3">Harga</th>
                                <th scope="col" class="px-4 py-3">Kuota</th>
                                <th scope="col" class="px-4 py-3">Terdaftar</th>
                                <th scope="col" class="px-4 py-3">Lunas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($event->categories as $cat)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3 font-semibold text-white">
                                    {{ $cat->name }}
                                </td>
                                <td class="px-4 py-3 text-slate-400 font-mono">
                                    {{ $cat->distance_km ? $cat->distance_km . ' KM' : '-' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-white">
                                    Rp {{ number_format($cat->price_regular ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-300">
                                    {{ $cat->quota ? number_format($cat->quota) : 'Unlimited' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-cyan-400 font-medium">
                                    {{ number_format($cat->total_participants ?? 0) }}
                                </td>
                                <td class="px-4 py-3 font-mono text-emerald-400 font-semibold">
                                    {{ number_format($cat->paid_participants ?? 0) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    Belum ada kategori lomba yang ditambahkan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Event Details Panel -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                <h2 class="text-base font-bold text-white mb-4">Informasi & Jadwal Pelaksanaan</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-md bg-slate-800/50 border border-slate-800">
                        <span class="text-slate-400 uppercase font-mono block mb-1">Waktu Start Pelaksanaan</span>
                        <p class="text-white font-semibold text-sm">
                            {{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->translatedFormat('l, d F Y - H:i') . ' WIB' : 'Belum diatur' }}
                        </p>
                    </div>

                    <div class="p-4 rounded-md bg-slate-800/50 border border-slate-800">
                        <span class="text-slate-400 uppercase font-mono block mb-1">Periode Registrasi</span>
                        <p class="text-white font-semibold text-sm">
                            {{ $event->registration_open_at ? \Carbon\Carbon::parse($event->registration_open_at)->translatedFormat('d M Y') : 'Dibuka' }}
                            s/d
                            {{ $event->registration_close_at ? \Carbon\Carbon::parse($event->registration_close_at)->translatedFormat('d M Y') : 'Selesai' }}
                        </p>
                    </div>

                    <div class="md:col-span-2 p-4 rounded-md bg-slate-800/50 border border-slate-800">
                        <span class="text-slate-400 uppercase font-mono block mb-1">Lokasi Lomba</span>
                        <p class="text-white font-semibold text-sm">
                            {{ $event->location_name ?: '-' }}
                        </p>
                        @if($event->location_address)
                        <p class="text-slate-400 mt-1">
                            {{ $event->location_address }}
                        </p>
                        @endif
                    </div>
                </div>

                @if(!empty($event->short_description))
                <div class="mt-4 pt-4 border-t border-slate-800">
                    <span class="text-slate-400 uppercase font-mono text-xs block mb-1">Deskripsi Singkat</span>
                    <p class="text-slate-300 text-xs leading-relaxed">
                        {{ $event->short_description }}
                    </p>
                </div>
                @endif
            </div>

        </div>

        <!-- Right: Actions & Integrations (1 col) -->
        <div class="space-y-6">
            
            <!-- Quick Management Shortcuts -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 mb-4 border-b border-slate-800 pb-2">Manajemen Event</h3>
                <div class="space-y-2">
                    <a href="{{ route('eo.events.participants', $event) }}" class="flex items-center justify-between p-3 rounded-md bg-slate-800/60 hover:bg-slate-800 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <span class="text-slate-200 group-hover:text-white text-xs font-semibold">Tabel Peserta & Check-in</span>
                        </div>
                        <span class="font-mono text-xs text-cyan-400">{{ number_format($totalRegistered) }}</span>
                    </a>

                    <a href="{{ route('eo.events.edit', $event) }}" class="flex items-center justify-between p-3 rounded-md bg-slate-800/60 hover:bg-slate-800 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            <span class="text-slate-200 group-hover:text-white text-xs font-semibold">Edit Konfigurasi Event</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>

                    <a href="{{ route('eo.events.blast', $event) }}" class="flex items-center justify-between p-3 rounded-md bg-slate-800/60 hover:bg-slate-800 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18V8H3v8z" /></svg>
                            <span class="text-slate-200 group-hover:text-white text-xs font-semibold">Broadcast / Blast Email</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>

                    <a href="{{ route('eo.events.results', $event) }}" class="flex items-center justify-between p-3 rounded-md bg-slate-800/60 hover:bg-slate-800 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="text-slate-200 group-hover:text-white text-xs font-semibold">Hasil Lomba (Race Results)</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>

            <!-- Public Link & Information Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                <h3 class="text-xs font-mono uppercase tracking-wider text-slate-400 mb-3 border-b border-slate-800 pb-2">Tautan Registrasi</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="text-[11px] text-slate-400 mb-1 block">URL Landing & Registrasi:</label>
                        <div class="flex items-center gap-1.5">
                            <input type="text" readonly value="{{ $event->public_url }}" id="publicUrlInput" class="w-full text-xs font-mono bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-slate-200 focus:outline-none">
                            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('publicUrlInput').value); alert('URL berhasil disalin ke clipboard!');" class="px-3 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition-colors shrink-0">
                                Salin
                            </button>
                        </div>
                    </div>

                    <a href="{{ $event->public_url }}" target="_blank" rel="noopener noreferrer" class="block w-full py-2.5 bg-yellow-500 hover:bg-yellow-400 text-black text-xs font-bold text-center rounded-md transition-colors shadow-sm">
                        Buka Halaman Registrasi &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
