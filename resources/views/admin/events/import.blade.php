@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Import Events')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-end gap-4 relative z-10">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('admin.events.index') }}" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    IMPORT EVENTS
                </h1>
            </div>
            <p class="text-slate-400 mt-1">Bulk upload running events via CSV.</p>
        </div>
    </div>

    <!-- Import Form -->
    <div class="max-w-2xl mx-auto">
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 relative z-10">
            
            <div class="mb-6 p-4 bg-slate-800/50 rounded-xl border border-slate-700/50 text-sm text-slate-300">
                <h3 class="font-bold text-white mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    CSV Format Instructions
                </h3>
                <p class="mb-2">The CSV file should have a header row and the following columns:</p>
                <ol class="list-decimal list-inside space-y-1 text-slate-400 pl-2">
                    <li>Event Name (Required)</li>
                    <li>Date (YYYY-MM-DD) (Required)</li>
                    <li>Start Time (HH:MM)</li>
                    <li>Location Name</li>
                    <li>City Name (e.g. "Jakarta Pusat")</li>
                    <li>Race Type (e.g. "Road Run")</li>
                    <li>Distances (comma separated, e.g. "5K, 10K, HM")</li>
                    <li>Registration Link</li>
                    <li>Organizer Name</li>
                </ol>
                <div class="mt-3 p-2 bg-black/30 rounded font-mono text-xs text-slate-400 overflow-x-auto">
                    Name,Date,Time,Location,City,Type,Distances,Link,Organizer<br>
                    Jakarta Marathon,2024-10-20,04:30,Monas,Jakarta Pusat,Road Run,"5K, 10K, FM",https://example.com,Jkt Organizer
                </div>
            </div>

            <form action="{{ route('admin.events.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-300 uppercase tracking-wider">CSV File</label>
                    <div class="relative group">
                        <input type="file" name="file" accept=".csv,.txt" required
                            class="block w-full text-sm text-slate-400
                            file:mr-4 file:py-2.5 file:px-4
                            file:rounded-xl file:border-0
                            file:text-sm file:font-bold
                            file:bg-slate-700 file:text-white
                            hover:file:bg-slate-600
                            cursor-pointer border border-slate-700 rounded-xl bg-slate-800/50 focus:outline-none focus:border-neon transition-all">
                    </div>
                    @error('file')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-slate-700/50 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-neon text-dark hover:bg-neon/90 transition-all font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
