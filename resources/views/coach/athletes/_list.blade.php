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
        <div class="space-y-6">
            <!-- Alert/Explanation -->
            <div class="bg-slate-950 border border-slate-800 rounded-lg p-4">
                <h4 class="text-xs font-semibold text-white mb-1">Pengelompokan Otomatis (VDOT Clusters)</h4>
                <p class="text-xs text-slate-300 leading-relaxed">Sistem mengelompokkan atlet berdasarkan kedekatan skor VDOT (toleransi selisih ±3.0 poin) agar intensitas dan target pace latihan setara.</p>
            </div>

            <!-- Clusters List -->
            @foreach($vdotClusters as $clusterIdx => $cluster)
                @php
                    $clusterVdots = collect($cluster)->map(fn($e) => $e->runner->vdot)->filter();
                    $minClusterVdot = $clusterVdots->min();
                    $maxClusterVdot = $clusterVdots->max();
                @endphp
                <div class="border border-slate-800 rounded-lg overflow-hidden bg-slate-950">
                    <div class="bg-slate-900 px-4 py-3 flex flex-wrap justify-between items-center gap-2 border-b border-slate-800">
                        <div>
                            <h3 class="font-semibold text-white text-xs sm:text-sm">Kelompok Latihan #{{ $clusterIdx + 1 }}</h3>
                            <p class="text-xs text-slate-400 font-mono">Rentang VDOT: {{ round($minClusterVdot, 1) }} - {{ round($maxClusterVdot, 1) }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded text-xs font-medium bg-slate-800 border border-slate-700 text-slate-200">
                            {{ count($cluster) }} Atlet
                        </span>
                    </div>
                    <div class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @foreach($cluster as $enrollment)
                            @php
                                $runner = $enrollment->runner;
                                $paces = $runner->training_paces;
                            @endphp
                            <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @if($runner->avatar)
                                            <img src="{{ $runner->avatar_url }}" alt="{{ $runner->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                                {{ substr($runner->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-semibold text-white text-xs truncate">{{ $runner->name }}</div>
                                            <div class="text-xs text-slate-400 truncate">{{ $runner->email }}</div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-200 font-mono font-medium text-xs shrink-0">
                                        VDOT {{ round($runner->vdot, 1) }}
                                    </span>
                                </div>

                                <div class="text-xs">
                                    <div class="text-xs text-slate-400 mb-0.5">Program Aktif:</div>
                                    <div class="font-medium text-slate-200 truncate flex items-center justify-between gap-2">
                                        <span class="truncate">{{ $enrollment->program->title }}</span>
                                        <span class="px-2 py-0.5 rounded text-xs font-medium border shrink-0
                                            @if($enrollment->status === 'active') bg-emerald-950 text-emerald-300 border-emerald-800
                                            @elseif($enrollment->status === 'inactive') bg-rose-950 text-rose-300 border-rose-800
                                            @elseif($enrollment->status === 'completed') bg-sky-950 text-sky-300 border-sky-800
                                            @else bg-amber-950 text-amber-300 border-amber-800 @endif">
                                            {{ $enrollment->status === 'inactive' ? 'Expired' : ($enrollment->status === 'purchased' ? 'Belum Aktif' : $enrollment->status) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Pace zones -->
                                @if($paces)
                                    <div>
                                        <div class="text-xs text-slate-400 mb-1">Target Pace (min/km):</div>
                                        <div class="grid grid-cols-5 gap-1 text-center bg-slate-950 p-2 rounded-md border border-slate-800 text-xs">
                                            <div>
                                                <div class="text-slate-400">Easy</div>
                                                <div class="font-mono font-medium text-emerald-300 mt-0.5">{{ $formatPace($paces['E'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-slate-400">Mara</div>
                                                <div class="font-mono font-medium text-sky-300 mt-0.5">{{ $formatPace($paces['M'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-slate-400">Thresh</div>
                                                <div class="font-mono font-medium text-amber-300 mt-0.5">{{ $formatPace($paces['T'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-slate-400">Int</div>
                                                <div class="font-mono font-medium text-rose-300 mt-0.5">{{ $formatPace($paces['I'] ?? null) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-slate-400">Rep</div>
                                                <div class="font-mono font-medium text-purple-300 mt-0.5">{{ $formatPace($paces['R'] ?? null) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="pt-2.5 border-t border-slate-800 flex flex-wrap items-center justify-end gap-1.5 text-xs">
                                    <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-2.5 py-1 rounded-md bg-neon text-dark font-semibold hover:bg-white transition">
                                        Monitor
                                    </a>
                                    <button onclick="openSendAccessModal('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->runner->email) }}', '{{ addslashes($enrollment->runner->phone ?? '') }}')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-medium transition" title="Kirim Akses Login">
                                        Kirim Akses
                                    </button>
                                    <button onclick="openReminderModal('{{ $enrollment->id }}')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-medium transition">
                                        Pengingat
                                    </button>
                                    <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-2 py-1 rounded-md bg-rose-950 hover:bg-rose-900 border border-rose-800 text-rose-300 font-medium transition" title="Hapus Atlet">
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
                <div class="border border-slate-800 rounded-lg overflow-hidden bg-slate-950">
                    <div class="bg-slate-900 px-4 py-3 flex flex-wrap justify-between items-center gap-2 border-b border-slate-800">
                        <div>
                            <h3 class="font-semibold text-white text-xs sm:text-sm">Belum Ada Data VDOT / PB</h3>
                            <p class="text-xs text-slate-400">Atlet belum memiliki catatan Personal Best (PB) atau hasil tes lari</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded text-xs font-medium bg-slate-800 border border-slate-700 text-slate-200">
                            {{ $noVdotAthletes->count() }} Atlet
                        </span>
                    </div>
                    <div class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @foreach($noVdotAthletes as $enrollment)
                            @php
                                $runner = $enrollment->runner;
                            @endphp
                            <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @if($runner->avatar)
                                            <img src="{{ $runner->avatar_url }}" alt="{{ $runner->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                                {{ substr($runner->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-semibold text-white text-xs truncate">{{ $runner->name }}</div>
                                            <div class="text-xs text-slate-400 truncate">{{ $runner->email }}</div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-mono text-slate-400">VDOT: -</span>
                                </div>
                                <div class="text-xs">
                                    <div class="text-xs text-slate-400 mb-0.5">Program Aktif:</div>
                                    <div class="font-medium text-slate-300 truncate">{{ $enrollment->program->title }}</div>
                                </div>
                                <div class="pt-2.5 border-t border-slate-800 flex flex-wrap items-center justify-end gap-1.5 text-xs">
                                    <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-2.5 py-1 rounded-md bg-neon text-dark font-semibold hover:bg-white transition">
                                        Monitor
                                    </a>
                                    <button onclick="openSendAccessModal('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->runner->email) }}', '{{ addslashes($enrollment->runner->phone ?? '') }}')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-medium transition" title="Kirim Akses Login">
                                        Kirim Akses
                                    </button>
                                    <button onclick="openReminderModal('{{ $enrollment->id }}')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-medium transition">
                                        Pengingat
                                    </button>
                                    <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-2 py-1 rounded-md bg-rose-950 hover:bg-rose-900 border border-rose-800 text-rose-300 font-medium transition" title="Hapus Atlet">
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
        <div class="text-center py-10">
            <div class="text-slate-400 text-xs">Tidak ada atlet yang cocok dengan kriteria filter.</div>
        </div>
    @endif
@else
    @if($enrollments->count() > 0)
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="text-slate-400 text-xs font-semibold border-b border-slate-800">
                        <th class="pb-3 px-3">Atlet</th>
                        <th class="pb-3 px-3">Program Latihan</th>
                        <th class="pb-3 px-3">Skor VDOT</th>
                        <th class="pb-3 px-3">Progres Program</th>
                        <th class="pb-3 px-3">Tanggal Mulai</th>
                        <th class="pb-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 divide-y divide-slate-800 font-sans">
                    @foreach($enrollments as $enrollment)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2.5">
                                @if($enrollment->runner->avatar)
                                    <img src="{{ $enrollment->runner->avatar_url }}" alt="{{ $enrollment->runner->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                        {{ substr($enrollment->runner->name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-semibold text-white truncate">{{ $enrollment->runner->name }}</div>
                                    <div class="text-xs text-slate-400 truncate">{{ $enrollment->runner->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-medium text-white">{{ $enrollment->program->title }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="px-1.5 py-0.2 rounded text-[11px] font-medium bg-slate-800 border border-slate-700 text-slate-300">
                                    {{ $enrollment->program->difficulty }}
                                </span>
                                <span class="px-1.5 py-0.2 rounded text-[11px] font-medium border
                                    @if($enrollment->status === 'active') bg-emerald-950 text-emerald-300 border-emerald-800
                                    @elseif($enrollment->status === 'inactive') bg-rose-950 text-rose-300 border-rose-800
                                    @elseif($enrollment->status === 'completed') bg-sky-950 text-sky-300 border-sky-800
                                    @else bg-amber-950 text-amber-300 border-amber-800 @endif">
                                    {{ $enrollment->status === 'inactive' ? 'Expired' : ($enrollment->status === 'purchased' ? 'Belum Aktif' : $enrollment->status) }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3 px-3 font-mono text-xs">
                            @if($enrollment->runner->vdot)
                                <span class="bg-slate-800 text-slate-200 px-2 py-0.5 rounded border border-slate-700 font-medium">
                                    {{ round($enrollment->runner->vdot, 1) }}
                                </span>
                            @else
                                <span class="text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-3">
                            @php
                                $totalDays = ($enrollment->program->duration_weeks ?? 12) * 7;
                                $daysPassed = $enrollment->start_date ? now()->diffInDays($enrollment->start_date) : 0;
                                $progress = $totalDays > 0 ? min(100, max(0, ($daysPassed / $totalDays) * 100)) : 0;
                            @endphp
                            <div class="w-28">
                                <div class="flex justify-between text-xs font-mono mb-1 text-slate-400">
                                    <span>Mg {{ ceil(($daysPassed + 1)/7) }}</span>
                                    <span class="font-medium text-white">{{ number_format($progress, 0) }}%</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-800 rounded-sm overflow-hidden">
                                    <div class="h-full bg-neon rounded-sm" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-xs font-mono text-slate-300">
                            {{ $enrollment->start_date ? $enrollment->start_date->format('d M Y') : 'Belum Mulai' }}
                        </td>
                        <td class="py-3 px-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-2.5 py-1 rounded-md bg-neon text-dark font-semibold text-xs hover:bg-white transition">
                                    Monitor
                                </a>
                                <button onclick="openSendAccessModal('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->runner->email) }}', '{{ addslashes($enrollment->runner->phone ?? '') }}')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-medium text-xs transition" title="Kirim Akses Login">
                                    Kirim Akses
                                </button>
                                <button onclick="openReminderModal('{{ $enrollment->id }}')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-medium text-xs transition">
                                    Pengingat
                                </button>
                                <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-2 py-1 rounded-md bg-rose-950 hover:bg-rose-900 border border-rose-800 text-rose-300 font-medium text-xs transition" title="Hapus Atlet">
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
        <div class="md:hidden space-y-3">
            @foreach($enrollments as $enrollment)
                <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            @if($enrollment->runner->avatar)
                                <img src="{{ $enrollment->runner->avatar_url }}" alt="{{ $enrollment->runner->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                            @else
                                <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                    {{ substr($enrollment->runner->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="font-semibold text-white text-xs truncate">{{ $enrollment->runner->name }}</div>
                                <div class="text-xs text-slate-400 truncate">{{ $enrollment->runner->email }}</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-200 font-medium font-mono text-xs shrink-0">
                            VDOT {{ $enrollment->runner->vdot ? round($enrollment->runner->vdot, 1) : '-' }}
                        </span>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-800 text-xs">
                        <div class="flex justify-between items-center">
                            <div class="min-w-0 pr-2">
                                <div class="text-xs text-slate-400">Program:</div>
                                <div class="font-medium text-white truncate">{{ $enrollment->program->title }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-xs font-medium border shrink-0
                                @if($enrollment->status === 'active') bg-emerald-950 text-emerald-300 border-emerald-800
                                @elseif($enrollment->status === 'inactive') bg-rose-950 text-rose-300 border-rose-800
                                @elseif($enrollment->status === 'completed') bg-sky-950 text-sky-300 border-sky-800
                                @else bg-amber-950 text-amber-300 border-amber-800 @endif">
                                {{ $enrollment->status === 'inactive' ? 'Expired' : ($enrollment->status === 'purchased' ? 'Belum Aktif' : $enrollment->status) }}
                            </span>
                        </div>

                        <div>
                            @php
                                $totalDays = ($enrollment->program->duration_weeks ?? 12) * 7;
                                $daysPassed = $enrollment->start_date ? now()->diffInDays($enrollment->start_date) : 0;
                                $progress = $totalDays > 0 ? min(100, max(0, ($daysPassed / $totalDays) * 100)) : 0;
                            @endphp
                            <div class="flex justify-between text-xs font-mono text-slate-400 mb-1">
                                <span>Minggu {{ ceil(($daysPassed + 1)/7) }}</span>
                                <span class="font-medium text-white">{{ number_format($progress, 0) }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-800 rounded-sm overflow-hidden">
                                <div class="h-full bg-neon rounded-sm" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center justify-end gap-1.5 pt-2 border-t border-slate-800 text-xs">
                        <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-2.5 py-1 rounded-md bg-neon text-dark font-semibold hover:bg-white transition">
                            Monitor
                        </a>
                        <button onclick="openSendAccessModal('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->runner->email) }}', '{{ addslashes($enrollment->runner->phone ?? '') }}')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-medium transition" title="Kirim Akses Login">
                            Kirim Akses
                        </button>
                        <button onclick="openReminderModal('{{ $enrollment->id }}')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white font-medium transition">
                            Pengingat
                        </button>
                        <button onclick="confirmDeleteAthlete('{{ $enrollment->id }}', '{{ addslashes($enrollment->runner->name) }}', '{{ addslashes($enrollment->program->title) }}')" class="px-2 py-1 rounded-md bg-rose-950 hover:bg-rose-900 border border-rose-800 text-rose-300 font-medium transition" title="Hapus Atlet">
                            Hapus
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4 ajax-pagination">
            {{ $enrollments->links() }}
        </div>
    @else
        <div class="text-center py-10">
            <div class="text-slate-400 text-xs">Belum ada atlet yang terdaftar pada program Anda.</div>
        </div>
    @endif
@endif
