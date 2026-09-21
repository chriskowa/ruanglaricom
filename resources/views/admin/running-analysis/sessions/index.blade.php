@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Running Analysis Sessions | Admin RuangLari')

@section('content')
<div class="min-h-screen pt-2 pb-12 px-4 md:px-8 relative overflow-hidden font-sans bg-[#060a17]">
    <div class="max-w-7xl mx-auto">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Running Analysis Sessions
                </h1>
                <p class="text-sm text-slate-300 mt-1">Kelola jadwal sesi analisis form lari dan peserta atlet/pelari.</p>
            </div>
            <button type="button" 
                    onclick="openCreateSessionModal()" 
                    class="px-5 py-2.5 bg-neon hover:bg-white text-slate-950 text-xs font-black uppercase tracking-wider rounded-md transition duration-150 flex items-center justify-center gap-1.5 shadow-sm">
                <span>+ Buat Sesi Baru</span>
            </button>
        </div>

        @if(session('success'))
        <div class="bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 px-4 py-3 rounded-md mb-6 flex items-center justify-between text-sm">
            <span>{{ session('success') }}</span>
        </div>
        @endif

        {{-- Table Container --}}
        <div class="bg-[#0F172A] rounded-lg border border-slate-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900/90 border-b border-slate-800 text-slate-300 text-xs uppercase tracking-wider">
                            <th class="px-6 py-3.5 font-semibold">Tanggal &amp; Nama Sesi</th>
                            <th class="px-6 py-3.5 font-semibold">Lokasi</th>
                            <th class="px-6 py-3.5 font-semibold">Pelari</th>
                            <th class="px-6 py-3.5 font-semibold">Status</th>
                            <th class="px-6 py-3.5 font-semibold">Dibuat Oleh</th>
                            <th class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-slate-300 text-sm">
                        @forelse($sessions as $session)
                        <tr class="hover:bg-slate-800/40 transition duration-150">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $session->name }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $session->session_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-300">{{ $session->location ?: '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-slate-800 text-slate-200 py-1 px-2.5 rounded text-xs font-semibold">
                                    {{ $session->runners_count }} Pelari
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($session->status === 'draft')
                                    <span class="bg-slate-800 text-slate-300 border border-slate-700 py-1 px-2 rounded text-xs font-semibold uppercase">Draft</span>
                                @elseif($session->status === 'active')
                                    <span class="bg-neon/20 text-neon border border-neon/50 py-1 px-2 rounded text-xs font-semibold uppercase">Active</span>
                                @elseif($session->status === 'completed')
                                    <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/50 py-1 px-2 rounded text-xs font-semibold uppercase">Completed</span>
                                @else
                                    <span class="bg-slate-800 text-slate-400 border border-slate-700 py-1 px-2 rounded text-xs font-semibold uppercase">{{ $session->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-300">{{ $session->creator->name ?? 'Admin' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.running-analysis.sessions.show', $session) }}" 
                                   class="text-neon hover:text-white font-semibold text-xs inline-flex items-center gap-1 transition duration-150">
                                    <span>Lihat Detail</span>
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <p class="text-sm">Belum ada sesi analisis lari.</p>
                                <button type="button" onclick="openCreateSessionModal()" class="mt-3 text-xs text-neon hover:underline">
                                    + Klik di sini untuk membuat sesi baru
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sessions->hasPages())
            <div class="px-6 py-4 border-t border-slate-800">
                {{ $sessions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Create Session Modal --}}
<div id="createSessionModal" 
     class="fixed inset-0 z-[1100] hidden overflow-y-auto" 
     aria-labelledby="modal-session-title" 
     role="dialog" 
     aria-modal="true">
     
    {{-- Backdrop Overlay (100% full screen background) --}}
    <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm transition-opacity" 
         onclick="closeCreateSessionModal()"></div>

    {{-- Centering Flex Container --}}
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6">
        {{-- Modal Card with Explicit relative z-10 so it renders on top of backdrop --}}
        <div class="relative z-10 w-full max-w-lg bg-[#0F172A] border border-slate-800 rounded-lg shadow-2xl text-left overflow-hidden transform transition-all">
            <form action="{{ route('admin.running-analysis.sessions.store') }}" method="POST">
                @csrf
                <div class="px-6 py-4 bg-slate-900 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-base font-bold text-white uppercase tracking-wider" id="modal-session-title">
                        Buat Sesi Analisis Baru
                    </h3>
                    <button type="button" 
                            class="text-slate-400 hover:text-white p-1 rounded transition duration-150" 
                            onclick="closeCreateSessionModal()"
                            aria-label="Tutup modal">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <label for="session_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Nama Sesi <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" 
                               id="session_name" 
                               name="name" 
                               required 
                               class="w-full bg-[#090D1A] border border-slate-700 rounded-md px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition duration-150" 
                               placeholder="Contoh: GBK Sunday Long Run Analysis">
                    </div>
                    <div>
                        <label for="session_location" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Lokasi Sesi
                        </label>
                        <input type="text" 
                               id="session_location" 
                               name="location" 
                               class="w-full bg-[#090D1A] border border-slate-700 rounded-md px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition duration-150" 
                               placeholder="Contoh: Stadion Madya Gelora Bung Karno">
                    </div>
                    <div>
                        <label for="session_date" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Tanggal Sesi <span class="text-rose-400">*</span>
                        </label>
                        <input type="date" 
                               id="session_date" 
                               name="session_date" 
                               required 
                               value="{{ date('Y-m-d') }}" 
                               class="w-full bg-[#090D1A] border border-slate-700 rounded-md px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition duration-150" 
                               style="color-scheme: dark;">
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-slate-900 border-t border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" 
                            class="px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white rounded-md transition duration-150" 
                            onclick="closeCreateSessionModal()">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 bg-[#CCFF00] hover:bg-white text-slate-950 text-xs font-black uppercase tracking-wider rounded-md transition duration-150 shadow-sm">
                        Simpan Sesi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateSessionModal() {
    const modal = document.getElementById('createSessionModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            const input = document.getElementById('session_name');
            if (input) input.focus();
        }, 50);
    }
}

function closeCreateSessionModal() {
    const modal = document.getElementById('createSessionModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateSessionModal();
    }
});
</script>
@endsection
