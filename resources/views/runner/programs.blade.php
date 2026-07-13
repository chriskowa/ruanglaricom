@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Program Latihan Saya | Ruang Lari')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto text-slate-100" x-data="programsManager()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-white italic tracking-tight uppercase">Program Latihan Saya</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola program latihan aktif Anda dan temukan program baru dari Coach terbaik kami.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('runner.calendar') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold text-sm hover:border-neon hover:text-neon transition-all flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i>
                Lihat Kalender Lari
            </a>
            <a href="{{ route('marketplace.index') }}" class="px-4 py-2.5 rounded-xl bg-neon text-dark font-black text-sm hover:bg-neon/90 hover:shadow-neon/20 transition-all flex items-center gap-2">
                <i class="fas fa-shopping-bag"></i>
                Jelajahi Market
            </a>
        </div>
    </div>

    <!-- Active Program Section -->
    <div class="mb-10">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-1.5 h-6 bg-neon rounded-full"></span>
            Program Aktif Saat Ini
        </h2>

        @if($activePrograms->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($activePrograms as $active)
                    @php
                        $prog = $active->program;
                        $coach = $prog->coach;
                    @endphp
                    <div class="relative overflow-hidden rounded-2xl bg-slate-900/60 border border-slate-800/80 p-6 flex flex-col justify-between hover:border-neon/30 transition-all duration-300">
                        <div class="absolute -right-16 -top-16 w-36 h-36 bg-neon/10 rounded-full blur-3xl pointer-events-none"></div>
                        
                        <div>
                            <!-- Header Info -->
                            <div class="flex justify-between items-start gap-4 mb-4">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider 
                                        @if(($prog->difficulty ?? 'beginner') === 'beginner') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                        @elseif(($prog->difficulty ?? 'beginner') === 'intermediate') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                        @else bg-rose-500/10 text-rose-400 border border-rose-500/20 @endif">
                                        {{ $prog->difficulty ?? 'Beginner' }}
                                    </span>
                                    <h3 class="text-xl font-black text-white uppercase italic tracking-tight mt-2">{{ $prog->title }}</h3>
                                </div>
                                <span class="text-xs text-slate-500 font-mono font-bold">{{ $prog->duration_weeks ?? 12 }} Minggu</span>
                            </div>

                            <!-- Coach Profile -->
                            @if($coach)
                            <div class="flex items-center gap-3 mb-6 p-3 rounded-xl bg-slate-950/40 border border-slate-800/40">
                                <img src="{{ $coach->avatar ? (str_starts_with($coach->avatar, 'http') ? $coach->avatar : (str_starts_with($coach->avatar, '/storage') ? asset(ltrim($coach->avatar, '/')) : asset('storage/' . $coach->avatar))) : asset('images/profile/17.jpg') }}" alt="{{ $coach->name }}" class="w-8 h-8 rounded-full object-cover">
                                <div>
                                    <div class="text-xs text-slate-400">Coach</div>
                                    <div class="text-xs font-bold text-white">{{ $coach->name }}</div>
                                </div>
                            </div>
                            @endif

                            <!-- Paces info / schedule dates -->
                            <div class="grid grid-cols-2 gap-4 mb-6 text-xs border-t border-b border-slate-800/60 py-3">
                                <div>
                                    <span class="text-slate-500 block mb-1">Tanggal Mulai:</span>
                                    <span class="text-slate-300 font-bold font-mono">{{ \Carbon\Carbon::parse($active->start_date)->format('d M Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block mb-1">Target Selesai:</span>
                                    <span class="text-slate-300 font-bold font-mono">{{ \Carbon\Carbon::parse($active->end_date)->format('d M Y') }}</span>
                                </div>
                            </div>

                            <!-- Progress Section -->
                            <div class="mb-6">
                                <div class="flex justify-between items-center text-xs font-bold mb-2">
                                    <span class="text-slate-400">Progres Latihan</span>
                                    <span class="text-neon">{{ $active->progress_percent }}% Selesai</span>
                                </div>
                                <div class="h-2 w-full bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-neon to-lime-400 rounded-full transition-all duration-500" style="width: {{ $active->progress_percent }}%"></div>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-1 font-semibold text-right">
                                    {{ $active->completed_sessions }} dari {{ $active->total_sessions }} Sesi Selesai
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-3 mt-4">
                            <a href="{{ route('runner.calendar') }}" class="flex-grow py-3 rounded-xl bg-neon text-dark font-black text-center text-sm hover:bg-neon/90 hover:shadow-neon/20 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-play-circle text-base"></i>
                                Buka Latihan Hari Ini
                            </a>
                            <button @click="confirmReset({{ $active->id }}, '{{ $prog->title }}')" class="px-4 py-3 rounded-xl bg-slate-800 hover:bg-red-950/20 border border-slate-700 hover:border-red-900/50 text-slate-400 hover:text-red-400 transition-all" title="Reset Program">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty state active program -->
            <div class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/20 p-8 text-center max-w-xl">
                <div class="w-12 h-12 rounded-full bg-slate-900/60 border border-slate-850 flex items-center justify-center mx-auto mb-4 text-slate-500">
                    <i class="fas fa-running text-lg"></i>
                </div>
                <h3 class="text-base font-bold text-white">Belum Ada Program Aktif</h3>
                <p class="text-xs text-slate-400 mt-2 mb-6">Anda saat ini tidak memiliki program latihan berjalan. Aktifkan salah satu program di kantong program Anda, atau jelajahi marketplace.</p>
                <div class="flex items-center justify-center gap-3">
                    @if($programBag->count() > 0)
                        <a href="#kantong-program" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold text-xs hover:border-neon hover:text-neon transition">
                            Lihat Kantong Program
                        </a>
                    @else
                        <a href="{{ route('marketplace.index') }}" class="px-4 py-2 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 transition">
                            Jelajahi Program Coach
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Program Bag Section -->
    <div class="mb-10" id="kantong-program">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-1.5 h-6 bg-neon rounded-full"></span>
            Kantong Program (Belum Aktif)
        </h2>

        @if($programBag->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($programBag as $bag)
                    @php
                        $prog = $bag->program;
                        $coach = $prog->coach;
                    @endphp
                    <div class="rounded-2xl bg-slate-900/60 border border-slate-800/80 p-5 flex flex-col justify-between hover:border-slate-700 transition">
                        <div>
                            <!-- Header Info -->
                            <div class="flex justify-between items-start gap-3 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider 
                                    @if(($prog->difficulty ?? 'beginner') === 'beginner') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif(($prog->difficulty ?? 'beginner') === 'intermediate') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @else bg-rose-500/10 text-rose-400 border border-rose-500/20 @endif">
                                    {{ $prog->difficulty ?? 'Beginner' }}
                                </span>
                                <span class="text-xs text-slate-500 font-mono font-bold">{{ $prog->duration_weeks ?? 12 }} Minggu</span>
                            </div>

                            <h3 class="text-base font-bold text-white uppercase tracking-tight line-clamp-2 min-h-[3rem]">{{ $prog->title }}</h3>

                            <!-- Coach Profile -->
                            @if($coach)
                            <div class="flex items-center gap-2.5 my-4">
                                <img src="{{ $coach->avatar ? (str_starts_with($coach->avatar, 'http') ? $coach->avatar : (str_starts_with($coach->avatar, '/storage') ? asset(ltrim($coach->avatar, '/')) : asset('storage/' . $coach->avatar))) : asset('images/profile/17.jpg') }}" alt="{{ $coach->name }}" class="w-6 h-6 rounded-full object-cover">
                                <span class="text-xs font-bold text-slate-350">{{ $coach->name }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Start Button -->
                        <div class="mt-4">
                            <button @click="openStartModal({{ $bag->id }}, '{{ $prog->title }}')" class="w-full py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white hover:border-neon hover:text-neon font-bold text-xs transition flex items-center justify-center gap-1.5">
                                <i class="fas fa-play-circle"></i>
                                Mulai Latihan Ini
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty state program bag -->
            <div class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/20 p-6 text-center max-w-xl">
                <p class="text-xs text-slate-400">Kantong program Anda kosong. Beli program latihan Coach dari marketplace untuk memulainya di sini.</p>
            </div>
        @endif
    </div>

    <!-- History & Expired Programs Section -->
    <div class="mb-10" id="riwayat-program">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-1.5 h-6 bg-slate-500 rounded-full"></span>
            Riwayat & Program Non-aktif (Expired / Selesai)
        </h2>

        @if($historyPrograms->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($historyPrograms as $hist)
                    @php
                        $prog = $hist->program;
                        $coach = $prog->coach;
                    @endphp
                    <div class="rounded-2xl bg-slate-900/40 border border-slate-800/60 p-5 flex flex-col justify-between hover:border-slate-700 transition">
                        <div>
                            <!-- Header Info -->
                            <div class="flex justify-between items-start gap-3 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider 
                                    @if($hist->status === 'inactive') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                    @else bg-blue-500/10 text-blue-400 border border-blue-500/20 @endif">
                                    {{ $hist->status === 'inactive' ? 'Non-aktif (Expired)' : 'Selesai' }}
                                </span>
                                <span class="text-xs text-slate-500 font-mono font-bold">{{ $prog->duration_weeks ?? 12 }} Minggu</span>
                            </div>

                            <h3 class="text-base font-bold text-slate-300 uppercase tracking-tight line-clamp-2 min-h-[3rem]">{{ $prog->title }}</h3>

                            <!-- Coach Profile -->
                            @if($coach)
                            <div class="flex items-center gap-2.5 my-4">
                                <img src="{{ $coach->avatar ? (str_starts_with($coach->avatar, 'http') ? $coach->avatar : (str_starts_with($coach->avatar, '/storage') ? asset(ltrim($coach->avatar, '/')) : asset('storage/' . $coach->avatar))) : asset('images/profile/17.jpg') }}" alt="{{ $coach->name }}" class="w-6 h-6 rounded-full object-cover">
                                <span class="text-xs font-bold text-slate-400">{{ $coach->name }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Buy Again / Renew Button -->
                        <div class="mt-4">
                            @if($prog->price == 0)
                                <form action="{{ route('runner.programs.enroll-free', $prog->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 transition flex items-center justify-center gap-1.5">
                                        <i class="fas fa-redo"></i>
                                        Ambil Gratis Lagi
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('marketplace.cart.add', $prog->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 transition flex items-center justify-center gap-1.5">
                                        <i class="fas fa-shopping-cart"></i>
                                        Beli / Daftar Ulang
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty state history -->
            <div class="rounded-2xl border border-dashed border-slate-800 bg-slate-900/10 p-6 text-center max-w-xl">
                <p class="text-xs text-slate-500">Belum ada riwayat program selesai atau program kedaluwarsa.</p>
            </div>
        @endif
    </div>

    <!-- Recommended / Market Section -->
    <div class="border-t border-slate-800/80 pt-10">
        <div class="flex items-center justify-between gap-4 mb-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="w-1.5 h-6 bg-neon rounded-full"></span>
                Rekomendasi Program Latihan Terbaik
            </h2>
            <a href="{{ route('marketplace.index') }}" class="text-xs font-bold text-neon hover:text-lime-400 transition flex items-center gap-1">
                Lihat Semua
                <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
        </div>

        @if($marketPrograms->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($marketPrograms as $prog)
                    @php $coach = $prog->coach; @endphp
                    <div class="group rounded-2xl bg-slate-900/40 border border-slate-800/60 overflow-hidden hover:border-neon/40 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <a href="{{ route('programs.show', $prog->slug) }}" class="block">
                            <!-- Cover Image -->
                            <div class="relative aspect-video w-full bg-slate-900 overflow-hidden">
                                @if($prog->thumbnail)
                                    <img src="{{ $prog->thumbnail_url }}" alt="{{ $prog->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @elseif($prog->banner)
                                    <img src="{{ $prog->banner_url }}" alt="{{ $prog->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-slate-900 to-slate-950 flex items-center justify-center text-slate-700">
                                        <i class="fas fa-running text-3xl"></i>
                                    </div>
                                @endif
                                <span class="absolute top-3 right-3 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider
                                    @if(($prog->difficulty ?? 'beginner') === 'beginner') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                    @elseif(($prog->difficulty ?? 'beginner') === 'intermediate') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                    @else bg-rose-500/10 text-rose-400 border border-rose-500/20 @endif">
                                    {{ $prog->difficulty ?? 'Beginner' }}
                                </span>
                            </div>

                            <!-- Details -->
                            <div class="p-5">
                                <h3 class="text-sm font-bold text-white uppercase tracking-tight group-hover:text-neon transition line-clamp-2 min-h-[2.5rem]">{{ $prog->title }}</h3>
                                
                                <div class="flex items-center gap-2 mt-4 text-[11px] text-slate-500">
                                    <span><i class="fas fa-calendar-alt mr-1"></i>{{ $prog->duration_weeks ?? 12 }} Minggu</span>
                                    <span>•</span>
                                    <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $prog->city->name ?? 'Online' }}</span>
                                </div>
                            </div>
                        </a>

                        <!-- Footer / Pricing & Actions -->
                        <div class="p-5 pt-0 border-t border-slate-800/40 mt-3 flex flex-col gap-4">
                            <div class="flex items-center justify-between mt-3">
                                @if($coach)
                                <div class="flex items-center gap-2">
                                    <img src="{{ $coach->avatar ? (str_starts_with($coach->avatar, 'http') ? $coach->avatar : (str_starts_with($coach->avatar, '/storage') ? asset(ltrim($coach->avatar, '/')) : asset('storage/' . $coach->avatar))) : asset('images/profile/17.jpg') }}" alt="{{ $coach->name }}" class="w-5 h-5 rounded-full object-cover">
                                    <span class="text-[10px] text-slate-400 font-bold truncate max-w-[80px]">{{ $coach->name }}</span>
                                </div>
                                @endif

                                <div class="text-xs font-black text-neon">
                                    @if($prog->price == 0)
                                        GRATIS
                                    @else
                                        Rp{{ number_format($prog->price, 0, ',', '.') }}
                                    @endif
                                </div>
                            </div>

                            @php
                                $isEnrolled = false;
                                if (auth()->user()) {
                                    $isEnrolled = \App\Models\ProgramEnrollment::where('runner_id', auth()->id())
                                        ->where('program_id', $prog->id)
                                        ->whereIn('status', ['purchased', 'active'])
                                        ->exists();
                                }
                            @endphp

                            <div class="w-full">
                                @if($isEnrolled)
                                    <a href="{{ route('runner.calendar') }}" class="w-full py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-350 hover:border-neon hover:text-neon font-bold text-xs transition flex items-center justify-center gap-1.5">
                                        <i class="fas fa-calendar-alt"></i>
                                        Sudah Diambil
                                    </a>
                                @else
                                    @if($prog->price == 0)
                                        <form action="{{ route('runner.programs.enroll-free', $prog->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full py-2.5 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 hover:shadow-neon/20 transition flex items-center justify-center gap-1.5">
                                                <i class="fas fa-plus-circle"></i>
                                                Ambil Gratis
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('marketplace.cart.add', $prog->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full py-2.5 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 hover:shadow-neon/20 transition flex items-center justify-center gap-1.5">
                                                <i class="fas fa-shopping-cart"></i>
                                                Beli Sekarang
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-500">Tidak ada program rekomendasi saat ini.</p>
        @endif
    </div>

    <!-- Apply/Start Program Modal -->
    <div x-show="showStartModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-transition x-cloak style="display: none;">
        <div class="relative w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6" @click.away="closeStartModal()">
            <button @click="closeStartModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>

            <h3 class="text-lg font-black text-white uppercase italic tracking-tight mb-2">Mulai Program Latihan</h3>
            <p class="text-xs text-slate-400 mb-6">Anda akan mengaktifkan program <strong class="text-white" x-text="selectedProgramTitle"></strong>. Silakan tentukan tanggal mulai latihan Anda.</p>

            <form @submit.prevent="submitStartProgram()">
                <div class="mb-6">
                    <label class="block text-xs font-mono uppercase tracking-wider text-slate-400 mb-2">Tanggal Mulai:</label>
                    <input type="date" x-model="startDate" required class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white font-bold text-sm focus:border-neon focus:outline-none transition">
                    <p class="text-[10px] text-slate-500 mt-2">Program akan dimulai pada tanggal yang Anda pilih. Kami menyarankan untuk memulai pada hari Senin agar jadwal latihan mingguan Anda teratur.</p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click="closeStartModal()" class="flex-1 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="loading" class="flex-1 py-3 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 disabled:opacity-50 transition flex items-center justify-center gap-2">
                        <span x-show="!loading">Aktifkan Program</span>
                        <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Program Modal -->
    <div x-show="showResetModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" x-transition x-cloak style="display: none;">
        <div class="relative w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6" @click.away="closeResetModal()">
            <button @click="closeResetModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>

            <div class="w-12 h-12 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-500 mb-4">
                <i class="fas fa-exclamation-triangle text-lg"></i>
            </div>

            <h3 class="text-lg font-black text-white uppercase italic tracking-tight mb-2">Reset Program Latihan?</h3>
            <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                Apakah Anda yakin ingin mereset program <strong class="text-white" x-text="selectedProgramTitle"></strong>? 
                Seluruh catatan tracking latihan untuk program ini akan <span class="text-red-400 font-bold">dihapus permanen</span> dan program akan dikembalikan ke Kantong Program.
            </p>

            <div class="flex items-center gap-3">
                <button type="button" @click="closeResetModal()" class="flex-1 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition">
                    Batal
                </button>
                <button type="button" @click="submitResetProgram()" :disabled="loading" class="flex-1 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs disabled:opacity-50 transition flex items-center justify-center gap-2">
                    <span x-show="!loading">Ya, Reset Program</span>
                    <i x-show="loading" class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function programsManager() {
        return {
            showStartModal: false,
            showResetModal: false,
            loading: false,
            selectedEnrollmentId: null,
            selectedProgramTitle: '',
            startDate: new Date().toISOString().split('T')[0],

            openStartModal(enrollmentId, title) {
                this.selectedEnrollmentId = enrollmentId;
                this.selectedProgramTitle = title;
                this.showStartModal = true;
            },

            closeStartModal() {
                this.showStartModal = false;
                this.selectedEnrollmentId = null;
                this.selectedProgramTitle = '';
            },

            async submitStartProgram() {
                this.loading = true;
                try {
                    const response = await fetch("{{ route('runner.calendar.apply-program') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            enrollment_id: this.selectedEnrollmentId,
                            start_date: this.startDate
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        alert('Program berhasil diaktifkan! Mengalihkan ke Kalender...');
                        window.location.href = "{{ route('runner.calendar') }}";
                    } else {
                        alert(data.message || 'Gagal mengaktifkan program.');
                    }
                } catch (error) {
                    console.error('Error applying program:', error);
                    alert('Terjadi kesalahan sistem. Silakan coba lagi.');
                } finally {
                    this.loading = false;
                    this.closeStartModal();
                }
            },

            confirmReset(enrollmentId, title) {
                this.selectedEnrollmentId = enrollmentId;
                this.selectedProgramTitle = title;
                this.showResetModal = true;
            },

            closeResetModal() {
                this.showResetModal = false;
                this.selectedEnrollmentId = null;
                this.selectedProgramTitle = '';
            },

            async submitResetProgram() {
                this.loading = true;
                try {
                    const response = await fetch("{{ route('runner.calendar.reset-plan') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            enrollment_id: this.selectedEnrollmentId
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        alert('Program berhasil direset dan dipindahkan ke Kantong Program.');
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal mereset program.');
                    }
                } catch (error) {
                    console.error('Error resetting program:', error);
                    alert('Terjadi kesalahan sistem. Silakan coba lagi.');
                } finally {
                    this.loading = false;
                    this.closeResetModal();
                }
            }
        }
    }
</script>
@endsection
