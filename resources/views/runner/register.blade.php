@extends('layouts.pacerhub')
@php($withSidebar = false)

@section('title', 'Runner Registration')

@section('content')
<div id="runner-register-app" class="min-h-screen pt-20 pb-20 px-4 md:px-8 font-sans bg-dark text-slate-200" v-cloak>
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <p class="text-neon font-mono text-sm tracking-widest uppercase mb-2">Runner Onboarding</p>
            <h1 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter">Build Your Profile</h1>
            <p class="text-slate-400 mt-2">Isi data profil, personal best, dan tes awal untuk rekomendasi program.</p>
        </div>

        <form method="POST" action="{{ route('runner.register.store') }}" class="space-y-8">
            @csrf

            <!-- Personal Info -->
            <div class="glass-panel rounded-2xl p-6">
                <h3 class="text-white font-bold text-lg mb-4">Data Pribadi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Nama</label>
                        <input name="name" type="text" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white" required>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Email</label>
                        <input name="email" type="email" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white" required>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Password</label>
                        <input name="password" type="password" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white" required>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Confirm Password</label>
                        <input name="password_confirmation" type="password" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white" required>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Gender</label>
                        <select name="gender" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="">-</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Tanggal Lahir</label>
                        <input name="birthdate" type="date" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-white">
                    </div>
                </div>
            </div>

            <!-- Health Basics -->
            <div class="glass-panel rounded-2xl p-6">
                <h3 class="text-white font-bold text-lg mb-4">Data Kesehatan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Tinggi (cm)</label>
                        <input name="height_cm" type="number" step="0.1" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Berat (kg)</label>
                        <input name="weight_kg" type="number" step="0.1" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white">
                    </div>
                </div>
            </div>

            <!-- Personal Best -->
            <div class="glass-panel rounded-2xl p-6">
                <h3 class="text-white font-bold text-lg mb-4">Personal Best</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">5K (H:i:s)</label>
                        <input name="pb_5k_time" type="text" placeholder="00:25:30" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">10K (H:i:s)</label>
                        <input name="pb_10k_time" type="text" placeholder="00:55:00" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">21K (H:i:s)</label>
                        <input name="pb_21k_time" type="text" placeholder="02:10:00" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">42K (H:i:s)</label>
                        <input name="pb_42k_time" type="text" placeholder="04:30:00" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white">
                    </div>
                </div>
            </div>

            <!-- Initial Tests -->
            <div class="glass-panel rounded-2xl p-6">
                <h3 class="text-white font-bold text-lg mb-4">Tes Awal</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Cooper (m)</label>
                        <input name="cooper_distance" type="number" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1 block">Resting HR</label>
                        <input name="resting_hr" type="number" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full md:w-auto px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition shadow-lg shadow-neon/20">
                            Register \u0026 Choose Challenge
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
