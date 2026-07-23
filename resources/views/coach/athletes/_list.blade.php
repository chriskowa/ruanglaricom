@php
    $formatPace = function($decimalMin) {
        if (!$decimalMin) return '-';
        $min = floor($decimalMin);
        $sec = round(($decimalMin - $min) * 60);
        if ($sec == 60) {
            $min++;
            $sec = 0;
        }
        return sprintf('%d:%02d', $min, $sec);
    };
@endphp

@if($tab === 'clusters')
    @if(count($vdotClusters) > 0 || $noVdotAthletes->count() > 0)
        <div class="space-y-8">
            <!-- Alert/Explanation -->
            <div class="bg-slate-900/60 border border-slate-800/80 rounded-xl p-4 flex items-start gap-3 shadow-sm">
                <div class="text-neon p-2 bg-slate-800/80 rounded-lg flex-shrink-0">
                    <i class="fa-solid fa-circle-info text-base"></i>
                </div>
                <div>
                    <h4 class="text-xs font-extrabold text-white mb-1 uppercase tracking-wider">Pengelompokan Otomatis (VDOT Clusters)</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Sistem secara otomatis mengelompokkan atlet Anda ke dalam kelompok latihan berdasarkan kedekatan skor VDOT mereka (toleransi selisih ±3.0 poin). Atlet dalam satu kelompok memiliki intensitas & target pace latihan yang setara.</p>
                </div>
            </div>

            <!-- Clusters List -->
            @foreach($vdotClusters as $clusterIdx => $cluster)
                @php
                    $clusterVdots = collect($cluster)->map(fn($e) => $e->runner->vdot)->filter();
                    $minClusterVdot = $clusterVdots->min();
                    $maxClusterVdot = $clusterVdots->max();
                @endphp
                <div class="border border-slate-800/80 rounded-2xl overflow-hidden bg-slate-900/30 backdrop-blur shadow-sm">
                    <div class="bg-slate-800/40 px-5 py-3.5 flex flex-wrap justify-between items-center gap-2 border-b border-slate-800/80">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-neon/10 border border-neon/30 flex items-center justify-center text-neon font-black text-xs">
                                #{{ $clusterIdx + 1 }}
                            </div>
                            <div>
                                <h3 class="font-extrabold text-white uppercase tracking-tight text-xs sm:text-sm">Kelompok Latihan #{{ $clusterIdx + 1 }}</h3>
                                <p class="text-[11px] text-slate-400 font-mono">VDOT Range: {{ round($minClusterVdot, 1) }} - {{ round($maxClusterVdot, 1) }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-neon text-dark">
                            {{ count($cluster) }} Atlet
                        </span>
                    </div>
                    <div class="p-4 sm:p-5 grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
                        @foreach($cluster as $enrollment)
                            @php
                                $runner = $enrollment->runner;
                                $paces = $runner->training_paces;
                            @endphp
                            <div class="bg-slate-800/30 border border-slate-700/50 rounded-xl p-4 sm:p-5 hover:border-neon/40 transition duration-300">
                                <div class="flex items-start justify-between gap-3 mb-3.5">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if($runner->avatar)
                                            <img src="{{ $runner->avatar_url }}" alt="{{ $runner->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-700 flex-shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                                {{ substr($runner->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-bold text-white text-sm truncate">{{ $runner->name }}</div>
                                            <div class="text-xs text-slate-400 truncate">{{ $runner->email }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="inline-block px-2.5 py-1 rounded bg-neon/10 border border-neon/30 text-neon font-extrabold text-xs font-mono">
                                            VDOT {{ round($runner->vdot, 1) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-3.5">
                                    <div class="text-[10px] font-mono text-slate-500 uppercase tracking-wider mb-1">Active Program</div>
                                    <div class="font-medium text-slate-300 text-xs sm:text-sm truncate flex items-center justify-between gap-2">
                                        <span class="truncate">{{ $enrollment->program->title }}</span>
                                        <span class="px-2 py-0.5 rounded text-[9px] uppercase font-bold border flex-shrink-0
                                            @if($enrollment->status === 'active') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                            @elseif($enrollment->status === 'inactive') bg-rose-500/10 text-rose-400 border-rose-500/20
                                            @elseif($enrollment->status === 'completed') bg-blue-500/10 text-blue-400 border-blue-500/20
                                            @else bg-amber-500/10 text-amber-400 border-amber-500/20 @endif">
                                            {{ $enrollment->status === 'inactive' ? 'Expired' : ($enrollment->status === 'purchased' ? 'Program Bag' : $enrollment->status) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Pace zones -->
                                @if($paces)
                                    <div>
                                        <div class="text-[10px] font-mono text-slate-500 uppercase tracking-wider mb-1.5">Training Paces (min/km)</div>
                                        <div class="grid grid-cols-5 gap-1 text-center bg-slate-900/70 p-2 rounded-lg border border-slate-800/80">
                                            <div>
                                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider" title="Easy Run">Easy</div>
                                                <div class="text-[11px] font-mono font-bold text-emerald-400 mt-0.5">{{ $formatPace($paces['E'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider" title="Marathon Pace">Mara</div>
                                                <div class="text-[11px] font-mono font-bold text-blue-400 mt-0.5">{{ $formatPace($paces['M'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider" title="Threshold Pace">Thresh</div>
                                                <div class="text-[11px] font-mono font-bold text-orange-400 mt-0.5">{{ $formatPace($paces['T'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider" title="Interval Pace">Int</div>
                                                <div class="text-[11px] font-mono font-bold text-red-400 mt-0.5">{{ $formatPace($paces['I'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider" title="Repetition Pace">Rep</div>
                                                <div class="text-[11px] font-mono font-bold text-violet-400 mt-0.5">{{ $formatPace($paces['R'] ?? null) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-3.5 pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-end gap-1.5 sm:gap-2">
                                    <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-2.5 py-1.5 rounded-lg bg-neon text-dark font-extrabold text-[11px] hover:bg-neon/90 transition flex items-center gap-1">
                                        <i class="fa-solid fa-chart-line text-[10px]"></i>
                                        <span>Monitor</span>
                                    </a>
                                    <button onclick="openReminderModal('{{ $enrollment->id }}')" class="px-2.5 py-1.5 rounded-lg bg-neon/10 text-neon border border-neon/20 hover:bg-neon hover:text-dark font-bold text-[11px] transition flex items-center gap-1">
                                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                        <span>Reminder</span>
                                    </button>
                                    <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-2 py-1.5 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20 hover:bg-rose-500 hover:text-white font-bold text-[11px] transition flex items-center gap-1" title="Hapus Atlet">
                                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Athletes without VDOT -->
            @if($noVdotAthletes->count() > 0)
                <div class="border border-slate-800/80 rounded-2xl overflow-hidden bg-slate-900/20">
                    <div class="bg-slate-800/30 px-5 py-3.5 flex flex-wrap justify-between items-center gap-2 border-b border-slate-800/80">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 font-bold text-xs">
                                <i class="fa-solid fa-circle-question"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-white uppercase tracking-tight text-xs sm:text-sm">Belum Ada Data VDOT / PB</h3>
                                <p class="text-[11px] text-slate-400">Atlet belum mengisi data Personal Best (PB) atau tes lari lainnya</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-400">
                            {{ $noVdotAthletes->count() }} Atlet
                        </span>
                    </div>
                    <div class="p-4 sm:p-5 grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
                        @foreach($noVdotAthletes as $enrollment)
                            @php
                                $runner = $enrollment->runner;
                            @endphp
                            <div class="bg-slate-800/20 border border-slate-800/60 rounded-xl p-4 sm:p-5">
                                <div class="flex items-center justify-between gap-3 mb-3.5">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if($runner->avatar)
                                            <img src="{{ $runner->avatar_url }}" alt="{{ $runner->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-700 flex-shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                                {{ substr($runner->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-300 text-sm truncate">{{ $runner->name }}</div>
                                            <div class="text-xs text-slate-500 truncate">{{ $runner->email }}</div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500 flex-shrink-0">VDOT: -</span>
                                </div>
                                <div class="mb-3.5">
                                    <div class="text-[10px] font-mono text-slate-500 uppercase tracking-wider mb-1">Active Program</div>
                                    <div class="font-medium text-slate-400 text-xs sm:text-sm truncate">{{ $enrollment->program->title }}</div>
                                </div>
                                <div class="mt-3.5 pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-end gap-1.5 sm:gap-2">
                                    <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-2.5 py-1.5 rounded-lg bg-neon text-dark font-extrabold text-[11px] hover:bg-neon/90 transition flex items-center gap-1">
                                        <i class="fa-solid fa-chart-line text-[10px]"></i>
                                        <span>Monitor</span>
                                    </a>
                                    <button onclick="openReminderModal('{{ $enrollment->id }}')" class="px-2.5 py-1.5 rounded-lg bg-neon/10 text-neon border border-neon/20 hover:bg-neon hover:text-dark font-bold text-[11px] transition flex items-center gap-1">
                                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                        <span>Reminder</span>
                                    </button>
                                    <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-2 py-1.5 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20 hover:bg-rose-500 hover:text-white font-bold text-[11px] transition flex items-center gap-1" title="Hapus Atlet">
                                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-slate-400 text-xs sm:text-sm">Tidak ada atlet yang cocok dengan filter VDOT.</div>
        </div>
    @endif
@else
    @if($enrollments->count() > 0)
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-500 text-xs uppercase border-b border-slate-700/80 font-mono">
                        <th class="pb-3 pl-4">Runner</th>
                        <th class="pb-3">Program</th>
                        <th class="pb-3">VDOT</th>
                        <th class="pb-3">Progress</th>
                        <th class="pb-3">Tanggal Mulai</th>
                        <th class="pb-3 text-right pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-xs">
                    @foreach($enrollments as $enrollment)
                    <tr class="border-b border-slate-800/80 hover:bg-slate-800/30 transition">
                        <td class="py-3.5 pl-4">
                            <div class="flex items-center gap-3">
                                @if($enrollment->runner->avatar)
                                    <img src="{{ $enrollment->runner->avatar_url }}" alt="{{ $enrollment->runner->name }}" class="w-9 h-9 rounded-full object-cover border border-slate-700">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold text-xs">
                                        {{ substr($enrollment->runner->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-white">{{ $enrollment->runner->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $enrollment->runner->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5">
                            <div class="font-bold text-white">{{ $enrollment->program->title }}</div>
                            <div class="flex items-center gap-1 mt-1">
                                <span class="px-2 py-0.5 rounded text-[9px] uppercase font-bold bg-slate-700 text-slate-300">
                                    {{ $enrollment->program->difficulty }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[9px] uppercase font-bold border
                                    @if($enrollment->status === 'active') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                    @elseif($enrollment->status === 'inactive') bg-rose-500/10 text-rose-400 border-rose-500/20
                                    @elseif($enrollment->status === 'completed') bg-blue-500/10 text-blue-400 border-blue-500/20
                                    @else bg-amber-500/10 text-amber-400 border-amber-500/20 @endif">
                                    {{ $enrollment->status === 'inactive' ? 'Expired' : ($enrollment->status === 'purchased' ? 'Program Bag' : $enrollment->status) }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3.5 font-mono text-xs font-bold">
                            @if($enrollment->runner->vdot)
                                <span class="text-neon bg-neon/10 px-2 py-0.5 rounded border border-neon/20">
                                    {{ round($enrollment->runner->vdot, 1) }}
                                </span>
                            @else
                                <span class="text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="py-3.5">
                            @php
                                $totalDays = ($enrollment->program->duration_weeks ?? 12) * 7;
                                $daysPassed = $enrollment->start_date ? now()->diffInDays($enrollment->start_date) : 0;
                                $progress = $totalDays > 0 ? min(100, max(0, ($daysPassed / $totalDays) * 100)) : 0;
                            @endphp
                            <div class="w-32">
                                <div class="flex justify-between text-[11px] mb-1">
                                    <span class="text-slate-400">Minggu {{ ceil(($daysPassed + 1)/7) }}</span>
                                    <span class="font-bold text-white">{{ number_format($progress, 0) }}%</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-700/80 rounded-full overflow-hidden">
                                    <div class="h-full bg-neon" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 text-xs font-mono text-slate-300">
                            {{ $enrollment->start_date ? $enrollment->start_date->format('d M Y') : 'Belum Mulai' }}
                        </td>
                        <td class="py-3.5 text-right pr-4">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-2.5 py-1.5 rounded-lg bg-neon text-dark font-extrabold text-[11px] hover:bg-neon/90 transition flex items-center gap-1">
                                    <i class="fa-solid fa-chart-line text-[10px]"></i>
                                    <span>Monitor</span>
                                </a>
                                <button onclick="openReminderModal('{{ $enrollment->id }}')" class="px-2.5 py-1.5 rounded-lg bg-neon/10 text-neon border border-neon/20 hover:bg-neon hover:text-dark font-bold text-[11px] transition flex items-center gap-1">
                                    <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                    <span>Reminder</span>
                                </button>
                                <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-2 py-1.5 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20 hover:bg-rose-500 hover:text-white font-bold text-[11px] transition flex items-center gap-1" title="Hapus Atlet">
                                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Card View -->
        <div class="md:hidden space-y-3.5">
            @foreach($enrollments as $enrollment)
                <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($enrollment->runner->avatar)
                                <img src="{{ $enrollment->runner->avatar_url }}" alt="{{ $enrollment->runner->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-700 flex-shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    {{ substr($enrollment->runner->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="font-extrabold text-white text-sm truncate">{{ $enrollment->runner->name }}</div>
                                <div class="text-xs text-slate-400 truncate">{{ $enrollment->runner->email }}</div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="inline-block px-2 py-0.5 rounded bg-neon/10 border border-neon/30 text-neon font-bold font-mono text-xs">
                                VDOT {{ $enrollment->runner->vdot ? round($enrollment->runner->vdot, 1) : '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2.5 pt-2 border-t border-slate-700/40">
                        <div class="flex justify-between items-center text-xs">
                            <div class="min-w-0 pr-2">
                                <div class="text-[10px] font-mono text-slate-500 uppercase tracking-wider mb-0.5">Active Program</div>
                                <div class="font-bold text-white truncate">{{ $enrollment->program->title }}</div>
                            </div>
                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                <span class="px-2 py-0.5 rounded text-[9px] uppercase font-bold bg-slate-700/80 text-slate-300">
                                    {{ $enrollment->program->difficulty }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[9px] uppercase font-bold border
                                    @if($enrollment->status === 'active') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                    @elseif($enrollment->status === 'inactive') bg-rose-500/10 text-rose-400 border-rose-500/20
                                    @elseif($enrollment->status === 'completed') bg-blue-500/10 text-blue-400 border-blue-500/20
                                    @else bg-amber-500/10 text-amber-400 border-amber-500/20 @endif">
                                    {{ $enrollment->status === 'inactive' ? 'Expired' : ($enrollment->status === 'purchased' ? 'Program Bag' : $enrollment->status) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            @php
                                $totalDays = ($enrollment->program->duration_weeks ?? 12) * 7;
                                $daysPassed = $enrollment->start_date ? now()->diffInDays($enrollment->start_date) : 0;
                                $progress = $totalDays > 0 ? min(100, max(0, ($daysPassed / $totalDays) * 100)) : 0;
                            @endphp
                            <div class="flex justify-between text-[10px] font-mono text-slate-400 uppercase tracking-wider mb-1">
                                <span>Progress: Minggu {{ ceil(($daysPassed + 1)/7) }}</span>
                                <span class="font-bold text-white">{{ number_format($progress, 0) }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-700/80 rounded-full overflow-hidden">
                                <div class="h-full bg-neon shadow-[0_0_8px_rgba(204,255,0,0.4)]" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Compact Action Buttons -->
                    <div class="flex flex-wrap items-center justify-end gap-1.5 pt-2.5 border-t border-slate-700/40">
                        <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-3 py-1.5 rounded-lg bg-neon text-dark font-extrabold text-[11px] hover:bg-neon/90 transition flex items-center gap-1">
                            <i class="fa-solid fa-chart-line text-[10px]"></i>
                            <span>Monitor</span>
                        </a>
                        <button onclick="openReminderModal('{{ $enrollment->id }}')" class="px-2.5 py-1.5 rounded-lg bg-neon/10 text-neon border border-neon/20 hover:bg-neon hover:text-dark font-bold text-[11px] transition flex items-center gap-1">
                            <i class="fa-solid fa-paper-plane text-[10px]"></i>
                            <span>Reminder</span>
                        </button>
                        <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-2 py-1.5 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20 hover:bg-rose-500 hover:text-white font-bold text-[11px] transition flex items-center gap-1" title="Hapus Atlet">
                            <i class="fa-solid fa-trash-can text-[10px]"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4 ajax-pagination">
            {{ $enrollments->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-slate-400 text-xs sm:text-sm">Belum ada atlet yang terdaftar pada program Anda.</div>
        </div>
    @endif
@endif
