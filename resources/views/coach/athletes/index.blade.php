@extends('layouts.coach')

@section('title', 'My Athletes')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <p class="text-neon font-mono text-sm tracking-widest uppercase">Monitoring</p>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">My Athletes</h1>
            </div>
        </div>

        <div class="glass-panel rounded-2xl p-6">
            @if($enrollments->count() > 0)
                <div class="overflow-x-auto">
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
                                        <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold">
                                            {{ substr($enrollment->runner->name, 0, 1) }}
                                        </div>
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
                                            <span>Week {{ ceil($daysPassed/7) }}</span>
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
                <div class="mt-4">
                    {{ $enrollments->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-slate-500 mb-4">No athletes enrolled in your programs yet.</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
