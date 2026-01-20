@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Konfigurasi Strava')

@section('content')
<div class="p-8 bg-slate-900 rounded-xl overflow-hidden shadow-xl border border-slate-700 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white">Konfigurasi Strava Club & API</h2>
        <div class="text-sm text-slate-400">
            Mengelola kredensial untuk Strava Club API
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-600/20 border border-green-500 text-green-100 px-4 py-3 rounded mb-6 flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-600/20 border border-red-500 text-red-100 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.strava.update') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
            <h3 class="text-lg font-bold text-neon mb-4 border-b border-slate-700 pb-2">Credentials</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client ID -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Client ID</label>
                    <input type="text" name="client_id" value="{{ old('client_id', $config->client_id ?? '') }}" 
                           class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:outline-none focus:border-neon transition-colors"
                           placeholder="Ex: 123456">
                </div>

                <!-- Client Secret -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Client Secret</label>
                    <input type="password" name="client_secret" value="{{ old('client_secret', $config->client_secret ?? '') }}" 
                           class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:outline-none focus:border-neon transition-colors"
                           placeholder="Ex: a1b2c3d4...">
                </div>
            </div>

            <!-- Club ID -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-slate-400 mb-2">Strava Club ID</label>
                <input type="text" name="club_id" value="{{ old('club_id', $config->club_id ?? '1859982') }}" 
                       class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:outline-none focus:border-neon transition-colors"
                       placeholder="Ex: 1859982">
                <p class="text-xs text-slate-500 mt-1">ID Club Strava yang akan diambil datanya (default Ruang Lari: 1859982)</p>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
            <h3 class="text-lg font-bold text-neon mb-4 border-b border-slate-700 pb-2">Authentication</h3>
            
            <!-- Refresh Token -->
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">Refresh Token</label>
                <textarea name="refresh_token" rows="3"
                          class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white font-mono text-sm focus:outline-none focus:border-neon transition-colors"
                          placeholder="Paste Refresh Token from Strava API Settings">{{ old('refresh_token', $config->refresh_token ?? '') }}</textarea>
                <p class="text-xs text-slate-500 mt-1">
                    Refresh Token diperlukan untuk generate Access Token baru secara otomatis. 
                    <a href="https://www.strava.com/settings/api" target="_blank" class="text-blue-400 hover:underline">Check Strava API Settings</a>
                </p>
            </div>

            <!-- Current Access Token (Read Only) -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-slate-400 mb-2">Current Access Token (Auto-generated)</label>
                <div class="relative">
                    <input type="text" value="{{ $config->access_token ? Str::limit($config->access_token, 20) . '...' : 'Not Available' }}" disabled
                           class="w-full bg-slate-900/50 border border-slate-800 rounded-lg p-3 text-slate-500 font-mono text-sm cursor-not-allowed">
                    @if($config->expires_at)
                        <div class="absolute right-3 top-3 text-xs {{ $config->expires_at->isPast() ? 'text-red-500' : 'text-green-500' }}">
                            Expires: {{ $config->expires_at->diffForHumans() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-neon text-dark font-black px-8 py-3 rounded-xl hover:bg-white hover:scale-105 transition transform shadow-[0_0_20px_rgba(204,255,0,0.3)]">
                SIMPAN KONFIGURASI
            </button>
        </div>
    </form>
</div>
@endsection
