@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Popup Analytics')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <div class="text-xs font-mono text-slate-400 uppercase tracking-widest">Popup Management</div>
            <h1 class="text-3xl font-black text-white">Analytics</h1>
        </div>
        <form method="GET" class="flex items-center gap-3">
            <select name="range" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                @foreach([7,14,30,60,90,180] as $range)
                    <option value="{{ $range }}" @selected($days==$range)>{{ $range }} days</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-xl bg-primary text-slate-900 font-bold">Apply</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5">
            <div class="text-xs text-slate-400 uppercase">Total Views</div>
            <div class="text-2xl font-black text-white">{{ $totalViews }}</div>
        </div>
        <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5">
            <div class="text-xs text-slate-400 uppercase">Clicks</div>
            <div class="text-2xl font-black text-white">{{ $totalClicks }}</div>
        </div>
        <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5">
            <div class="text-xs text-slate-400 uppercase">Conversions</div>
            <div class="text-2xl font-black text-white">{{ $totalConversions }}</div>
        </div>
        <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5">
            <div class="text-xs text-slate-400 uppercase">CTR / CR</div>
            <div class="text-2xl font-black text-white">{{ $totalCtr }}% / {{ $totalCr }}%</div>
        </div>
    </div>

    <div class="overflow-x-auto bg-slate-900/60 border border-slate-700 rounded-2xl">
        <table class="w-full text-sm text-slate-200">
            <thead class="text-xs uppercase text-slate-400 border-b border-slate-700">
                <tr>
                    <th class="p-3 text-left">Popup</th>
                    <th class="p-3 text-left">Views</th>
                    <th class="p-3 text-left">Clicks</th>
                    <th class="p-3 text-left">Conversions</th>
                    <th class="p-3 text-left">CTR</th>
                    <th class="p-3 text-left">CR</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary as $row)
                    <tr class="border-b border-slate-800 hover:bg-slate-800/40">
                        <td class="p-3">
                            <div class="font-bold text-white">{{ $row['popup']->name }}</div>
                            <div class="text-xs text-slate-400">{{ $row['popup']->slug }}</div>
                        </td>
                        <td class="p-3">{{ $row['views'] }}</td>
                        <td class="p-3">{{ $row['clicks'] }}</td>
                        <td class="p-3">{{ $row['conversions'] }}</td>
                        <td class="p-3">{{ $row['ctr'] }}%</td>
                        <td class="p-3">{{ $row['cr'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-4 text-center text-slate-400" colspan="6">No analytics data yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
