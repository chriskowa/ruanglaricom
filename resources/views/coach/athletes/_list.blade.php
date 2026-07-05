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
        <div class="space-y-10">
            <!-- Alert/Explanation -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-4 flex items-start gap-3">
                <div class="text-neon p-1.5 bg-slate-800 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white mb-1">Pengelompokan Otomatis (VDOT Clusters)</h4>
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
                <div class="border border-slate-800/80 rounded-2xl overflow-hidden bg-slate-900/20 backdrop-blur shadow-sm">
                    <div class="bg-slate-800/30 px-6 py-4 flex flex-wrap justify-between items-center gap-2 border-b border-slate-800/80">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-neon/10 flex items-center justify-center text-neon font-black text-sm">
                                #{{ $clusterIdx + 1 }}
                            </div>
                            <div>
                                <h3 class="font-black text-white italic tracking-tight">Kelompok Latihan #{{ $clusterIdx + 1 }}</h3>
                                <p class="text-xs text-slate-500 font-mono">VDOT Range: {{ round($minClusterVdot, 1) }} - {{ round($maxClusterVdot, 1) }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-neon text-dark">
                            {{ count($cluster) }} Atlet
                        </span>
                    </div>
                    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        @foreach($cluster as $enrollment)
                            @php
                                $runner = $enrollment->runner;
                                $paces = $runner->training_paces;
                            @endphp
                            <div class="bg-slate-800/30 border border-slate-700/40 rounded-xl p-5 hover:border-neon/40 transition duration-300">
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3">
                                        @if($runner->avatar)
                                            <img src="{{ $runner->avatar_url }}" alt="{{ $runner->name }}" class="w-11 h-11 rounded-full object-cover border border-slate-700">
                                        @else
                                            <div class="w-11 h-11 rounded-full bg-slate-700 flex items-center justify-center text-white font-black text-base">
                                                {{ substr($runner->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-bold text-white">{{ $runner->name }}</div>
                                            <div class="text-xs text-slate-500 truncate max-w-[200px]">{{ $runner->email }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-2.5 py-1 rounded bg-neon/10 border border-neon/30 text-neon font-black text-xs font-mono">
                                            VDOT {{ round($runner->vdot, 1) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="text-[10px] font-mono text-slate-500 uppercase tracking-wider mb-1">Active Program</div>
                                    <div class="font-medium text-slate-300 text-sm truncate">{{ $enrollment->program->title }}</div>
                                </div>

                                <!-- Pace zones -->
                                @if($paces)
                                    <div>
                                        <div class="text-[10px] font-mono text-slate-500 uppercase tracking-wider mb-2">Training Paces (min/km)</div>
                                        <div class="grid grid-cols-5 gap-1 text-center bg-slate-900/60 p-2 rounded-lg border border-slate-800">
                                            <div>
                                                <div class="text-[10px] font-bold text-slate-400" title="Easy Run">Easy</div>
                                                <div class="text-xs font-mono font-bold text-emerald-400 mt-0.5">{{ $formatPace($paces['E'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-[10px] font-bold text-slate-400" title="Marathon Pace">Mara</div>
                                                <div class="text-xs font-mono font-bold text-blue-400 mt-0.5">{{ $formatPace($paces['M'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-[10px] font-bold text-slate-400" title="Threshold Pace">Thresh</div>
                                                <div class="text-xs font-mono font-bold text-orange-400 mt-0.5">{{ $formatPace($paces['T'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-[10px] font-bold text-slate-400" title="Interval Pace">Int</div>
                                                <div class="text-xs font-mono font-bold text-red-400 mt-0.5">{{ $formatPace($paces['I'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-[10px] font-bold text-slate-400" title="Repetition Pace">Rep</div>
                                                <div class="text-xs font-mono font-bold text-violet-400 mt-0.5">{{ $formatPace($paces['R'] ?? null) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-4 pt-3 border-t border-slate-800 flex justify-end gap-2">
                                    <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white font-black text-xs hover:bg-slate-700 hover:text-neon transition">
                                        Monitor & Grade
                                    </a>
                                    <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-3 py-2 rounded-lg bg-red-600/10 text-red-500 border border-red-500/20 hover:bg-red-600 hover:text-white font-bold text-xs transition">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Athletes without VDOT -->
            @if($noVdotAthletes->count() > 0)
                <div class="border border-slate-850 rounded-2xl overflow-hidden bg-slate-900/10">
                    <div class="bg-slate-800/20 px-6 py-4 flex flex-wrap justify-between items-center gap-2 border-b border-slate-800/80">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 font-bold text-sm">
                                ?
                            </div>
                            <div>
                                <h3 class="font-black text-white italic tracking-tight">Belum Ada Data VDOT / PB</h3>
                                <p class="text-xs text-slate-500">Atlet belum mengisi data Personal Best (PB) atau tes lari lainnya</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-400">
                            {{ $noVdotAthletes->count() }} Atlet
                        </span>
                    </div>
                    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        @foreach($noVdotAthletes as $enrollment)
                            @php
                                $runner = $enrollment->runner;
                            @endphp
                            <div class="bg-slate-800/20 border border-slate-800/60 rounded-xl p-5">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3">
                                        @if($runner->avatar)
                                            <img src="{{ $runner->avatar_url }}" alt="{{ $runner->name }}" class="w-11 h-11 rounded-full object-cover border border-slate-700">
                                        @else
                                            <div class="w-11 h-11 rounded-full bg-slate-700 flex items-center justify-center text-white font-black text-base">
                                                {{ substr($runner->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-bold text-slate-300">{{ $runner->name }}</div>
                                            <div class="text-xs text-slate-500 truncate max-w-[200px]">{{ $runner->email }}</div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500">VDOT: -</span>
                                </div>
                                <div class="mb-4">
                                    <div class="text-[10px] font-mono text-slate-500 uppercase tracking-wider mb-1">Active Program</div>
                                    <div class="font-medium text-slate-400 text-sm truncate">{{ $enrollment->program->title }}</div>
                                </div>
                                <div class="flex justify-end pt-3 border-t border-slate-800 gap-2">
                                    <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 font-bold text-xs hover:bg-slate-700 hover:text-white transition">
                                        Monitor & Grade
                                    </a>
                                    <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-3 py-2 rounded-lg bg-red-600/10 text-red-500 border border-red-500/20 hover:bg-red-600 hover:text-white font-bold text-xs transition">
                                        Hapus
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
            <div class="text-slate-500 mb-4">No athletes found matching the VDOT filters.</div>
        </div>
    @endif
@else
    @if($enrollments->count() > 0)
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-500 text-xs uppercase border-b border-slate-700">
                        <th class="pb-3 pl-4">Runner</th>
                        <th class="pb-3">Program</th>
                        <th class="pb-3">VDOT</th>
                        <th class="pb-3">Progress</th>
                        <th class="pb-3">Start Date</th>
                        <th class="pb-3 text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300">
                    @foreach($enrollments as $enrollment)
                    <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition">
                        <td class="py-4 pl-4">
                            <div class="flex items-center gap-3">
                                @if($enrollment->runner->avatar)
                                    <img src="{{ $enrollment->runner->avatar_url }}" alt="{{ $enrollment->runner->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-700">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold">
                                        {{ substr($enrollment->runner->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-white">{{ $enrollment->runner->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $enrollment->runner->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4">
                            <div class="font-bold text-white">{{ $enrollment->program->title }}</div>
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-slate-700 text-slate-300">
                                {{ $enrollment->program->difficulty }}
                            </span>
                        </td>
                        <td class="py-4 font-mono text-sm font-bold">
                            @if($enrollment->runner->vdot)
                                <span class="text-neon bg-neon/10 px-2 py-0.5 rounded border border-neon/20">
                                    {{ round($enrollment->runner->vdot, 1) }}
                                </span>
                            @else
                                <span class="text-slate-550">-</span>
                            @endif
                        </td>
                        <td class="py-4">
                            @php
                                $totalDays = ($enrollment->program->duration_weeks ?? 12) * 7;
                                $daysPassed = $enrollment->start_date ? now()->diffInDays($enrollment->start_date) : 0;
                                $progress = $totalDays > 0 ? min(100, max(0, ($daysPassed / $totalDays) * 100)) : 0;
                            @endphp
                            <div class="w-32">
                                <div class="flex justify-between text-xs mb-1">
                                    <span>Week {{ ceil(($daysPassed + 1)/7) }}</span>
                                    <span>{{ number_format($progress, 0) }}%</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-neon" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 text-sm font-mono">
                            {{ $enrollment->start_date ? $enrollment->start_date->format('d M Y') : 'Not Started' }}
                        </td>
                        <td class="py-4 text-right pr-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-4 py-2 rounded-lg bg-neon text-dark font-black text-xs hover:bg-neon/90 transition inline-block">
                                    Monitor & Grade
                                </a>
                                <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-3 py-2 rounded-lg bg-red-600/10 text-red-500 border border-red-550/20 hover:bg-red-600 hover:text-white font-bold text-xs transition">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Card View -->
        <div class="md:hidden space-y-4">
            @foreach($enrollments as $enrollment)
                <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4">
                    <div class="flex items-center gap-3 mb-4">
                        @if($enrollment->runner->avatar)
                            <img src="{{ $enrollment->runner->avatar_url }}" alt="{{ $enrollment->runner->name }}" class="w-12 h-12 rounded-full object-cover border border-slate-700">
                        @else
                            <div class="w-12 h-12 rounded-full bg-slate-700 flex items-center justify-center text-white font-black text-lg">
                                {{ substr($enrollment->runner->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="font-black text-white truncate italic">{{ $enrollment->runner->name }}</div>
                            <div class="text-xs text-slate-500 truncate">{{ $enrollment->runner->email }}</div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="w-10 h-10 rounded-xl bg-neon flex items-center justify-center text-dark">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7-7"></path></svg>
                            </a>
                            <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="w-10 h-10 rounded-xl bg-red-600 flex items-center justify-center text-white hover:bg-red-500 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-[10px] font-mono text-slate-500 uppercase tracking-widest mb-1">Active Program</div>
                                <div class="font-bold text-white text-sm">{{ $enrollment->program->title }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[9px] uppercase font-black bg-slate-700 text-slate-300">
                                {{ $enrollment->program->difficulty }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-t border-slate-700/30">
                            <div class="text-[10px] font-mono text-slate-500 uppercase tracking-widest">VDOT Score</div>
                            <div class="text-xs font-mono font-bold text-neon bg-neon/10 px-2 py-0.5 rounded">
                                {{ $enrollment->runner->vdot ? round($enrollment->runner->vdot, 1) : '-' }}
                            </div>
                        </div>

                        <div>
                            @php
                                $totalDays = ($enrollment->program->duration_weeks ?? 12) * 7;
                                $daysPassed = $enrollment->start_date ? now()->diffInDays($enrollment->start_date) : 0;
                                $progress = $totalDays > 0 ? min(100, max(0, ($daysPassed / $totalDays) * 100)) : 0;
                            @endphp
                            <div class="flex justify-between text-[10px] font-mono text-slate-500 uppercase tracking-widest mb-1">
                                <span>Progress: Week {{ ceil(($daysPassed + 1)/7) }}</span>
                                <span>{{ number_format($progress, 0) }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-neon shadow-[0_0_10px_rgba(204,255,0,0.5)]" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t border-slate-700/50">
                            <div class="text-[10px] font-mono text-slate-500 uppercase tracking-widest">Started</div>
                            <div class="text-xs font-bold text-slate-300">
                                {{ $enrollment->start_date ? $enrollment->start_date->format('d M Y') : 'Not Started' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4 ajax-pagination">
            {{ $enrollments->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-slate-500 mb-4">No athletes enrolled in your programs yet.</div>
        </div>
    @endif
@endif
