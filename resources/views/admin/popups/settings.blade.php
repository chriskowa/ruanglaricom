@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Popup Settings')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="mb-6">
        <div class="text-xs font-mono text-slate-400 uppercase tracking-widest">Popup Management</div>
        <h1 class="text-3xl font-black text-white">Settings</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.popups.settings.update') }}" class="max-w-3xl space-y-4">
        @csrf
        <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5 space-y-4">
            <label class="flex items-center gap-3 text-slate-200">
                <input type="checkbox" name="enabled" value="1" class="rounded bg-slate-800 border-slate-600" @checked($settings['enabled'] ?? false)>
                <span>Enable popups globally</span>
            </label>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-slate-400 mb-1">Default Frequency</div>
                    <select name="default_frequency" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                        @foreach(['session','day','interval'] as $opt)
                            <option value="{{ $opt }}" @selected(($settings['default_frequency'] ?? 'session') === $opt)>{{ ucfirst($opt) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="text-xs text-slate-400 mb-1">Default Interval Hours</div>
                    <input type="number" name="default_interval_hours" value="{{ $settings['default_interval_hours'] ?? 24 }}" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-slate-400 mb-1">Global Overlay Color</div>
                    <input type="text" name="global_overlay" value="{{ $settings['global_overlay'] ?? 'rgba(15, 23, 42, 0.7)' }}" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                </div>
                <div>
                    <div class="text-xs text-slate-400 mb-1">Global Z-Index</div>
                    <input type="number" name="z_index" value="{{ $settings['z_index'] ?? 1000 }}" class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                </div>
            </div>
        </div>
        <button class="px-6 py-2 rounded-xl bg-primary text-slate-900 font-bold">Save Settings</button>
    </form>
</div>
@endsection
