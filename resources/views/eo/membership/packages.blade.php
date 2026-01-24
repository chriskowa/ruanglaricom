@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'EO Membership Packages')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="flex justify-between items-end mb-8">
        <div>
            <p class="text-neon font-mono text-sm tracking-widest uppercase mb-1">EO Membership</p>
            <h1 class="text-4xl font-black text-white italic tracking-tighter">
                MEMBERSHIP <span class="text-neon">PACKAGES</span>
            </h1>
        </div>
    </div>

    <!-- Package List -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($packages as $package)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 relative group overflow-hidden hover:border-neon/50 transition-all duration-300 flex flex-col">
             <!-- Glow Effect -->
             <div class="absolute -top-10 -right-10 w-32 h-32 bg-neon/5 rounded-full blur-3xl group-hover:bg-neon/10 transition-all duration-500"></div>

             <h3 class="text-2xl font-black text-white italic tracking-tighter mb-2 uppercase">{{ $package->name }}</h3>
             
             <div class="flex items-baseline gap-1 mb-4">
                 <span class="text-sm text-slate-400">Rp</span>
                 <span class="text-4xl font-black text-neon tracking-tighter">{{ number_format($package->price, 0, ',', '.') }}</span>
             </div>
             
             <div class="mb-6 space-y-4 flex-grow">
                <div class="flex items-center gap-3 text-slate-300">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-neon">
                         <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="font-mono text-sm">Active for <span class="text-white font-bold">{{ $package->duration_days }} Days</span></span>
                </div>
                
                @if($package->description)
                <div class="text-slate-400 text-sm leading-relaxed border-t border-slate-800 pt-4">
                    {{ $package->description }}
                </div>
                @endif

                @if($package->features)
                <ul class="space-y-2 pt-2">
                    @foreach($package->features as $feature)
                    <li class="flex items-start gap-2 text-sm text-slate-300">
                        <svg class="w-5 h-5 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
             </div>

             <form action="{{ route('eo.membership.select') }}" method="POST" class="mt-auto pt-6">
                 @csrf
                 <input type="hidden" name="package_id" value="{{ $package->id }}">
                 <button type="submit" class="w-full bg-slate-800 text-white font-bold py-3 rounded-xl border border-slate-700 hover:bg-neon hover:text-slate-900 hover:border-neon transition-all duration-300 flex items-center justify-center gap-2 group-hover:shadow-lg group-hover:shadow-neon/20">
                     <span>SELECT PLAN</span>
                     <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                 </button>
             </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
