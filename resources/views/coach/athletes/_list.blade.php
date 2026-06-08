@if($enrollments->count() > 0)
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-slate-500 text-xs uppercase border-b border-slate-700">
                    <th class="pb-3 pl-4">Runner</th>
                    <th class="pb-3">Program</th>
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
                        <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="px-4 py-2 rounded-lg bg-neon text-dark font-black text-xs hover:bg-neon/90 transition inline-block">
                            Monitor & Grade
                        </a>
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
                    <a href="{{ route('coach.athletes.show', $enrollment->id) }}" class="w-10 h-10 rounded-xl bg-neon flex items-center justify-center text-dark">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                <div class="space-y-3">
                    <div>
                        <div class="text-[10px] font-mono text-slate-500 uppercase tracking-widest mb-1">Active Program</div>
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-white text-sm">{{ $enrollment->program->title }}</div>
                            <span class="px-2 py-0.5 rounded text-[9px] uppercase font-black bg-slate-700 text-slate-300">
                                {{ $enrollment->program->difficulty }}
                            </span>
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
