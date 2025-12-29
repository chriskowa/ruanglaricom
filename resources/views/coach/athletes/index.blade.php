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

        <!-- Filter Section -->
        <div class="mb-8 bg-slate-900/50 backdrop-blur-md rounded-2xl p-6 border border-slate-800 shadow-lg">
            <form action="{{ route('coach.athletes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5">
                    <label for="search" class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-wider">Search Runner</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none transition-colors group-focus-within:text-neon">
                            <svg class="w-4 h-4 text-slate-500 group-focus-within:text-neon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..." 
                            class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl focus:ring-neon focus:border-neon block p-3 pl-10 placeholder-slate-500 transition-all focus:bg-slate-800/80 focus:shadow-neon-cyan">
                    </div>
                </div>
                <div class="md:col-span-4">
                    <label for="program_id" class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-wider">Filter Program</label>
                    <div class="relative">
                        <select name="program_id" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl focus:ring-neon focus:border-neon block p-3 appearance-none cursor-pointer hover:bg-slate-700/50 transition-colors">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                                    {{ $program->title }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="flex-1 px-5 py-3 text-sm font-black text-dark bg-neon rounded-xl hover:bg-white transition-all shadow-lg hover:shadow-neon-cyan transform hover:-translate-y-0.5">
                        FILTER
                    </button>
                    @if($search || $programId)
                        <a href="{{ route('coach.athletes.index') }}" class="px-5 py-3 text-sm font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700 hover:text-white transition-all border border-slate-700 hover:border-slate-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                    @endif
                </div>
            </form>
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
