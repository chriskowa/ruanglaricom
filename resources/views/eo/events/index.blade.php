@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Master Events')

@push('styles')
    <script>
        tailwind.config.theme.extend = {
            ...tailwind.config.theme.extend,
            colors: {
                ...tailwind.config.theme.extend.colors,
                neon: {
                    cyan: '#06b6d4',
                    purple: '#a855f7',
                    green: '#22c55e',
                    yellow: '#eab308',
                }
            }
        }
    </script>
@endpush

@section('content')
<div id="eo-events-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans" x-data="{ showModal: false, activeEvent: null }" x-cloak>
    
    <!-- Header Section -->
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('eo.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 text-sm font-medium text-white md:ml-2">Master Events</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    MASTER <span class="text-yellow-400">EVENTS</span>
                </h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('eo.events.create') }}" class="px-6 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-black font-black transition-all shadow-lg shadow-yellow-500/20 flex items-center gap-2 transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Create New Event
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/30 rounded-xl p-4 flex items-center justify-between text-green-400 relative z-10" role="alert">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" class="hover:text-white transition-colors" onclick="this.parentElement.remove()">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    <!-- Events List -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900/50 text-xs uppercase font-bold text-slate-300">
                    <tr>
                        <th scope="col" class="px-6 py-4">Event Name</th>
                        <th scope="col" class="px-6 py-4">Date & Time</th>
                        <th scope="col" class="px-6 py-4">Location</th>
                        <th scope="col" class="px-6 py-4">Categories</th>
                        <th scope="col" class="px-6 py-4">Status</th>
                        <th scope="col" class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($events as $event)
                    <tr class="hover:bg-slate-800/50 transition-colors group">
                        <td class="px-6 py-4 font-medium text-white group-hover:text-yellow-400 transition-colors">
                            {{ $event->name }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-white">{{ $event->start_at->format('d M Y') }}</span>
                                <span class="text-xs">{{ $event->start_at->format('H:i') }} WIB</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span>{{ Str::limit($event->location_name, 20) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-900/30 text-cyan-400 border border-cyan-500/30">
                                {{ $event->categories->count() }} Cats
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900/30 text-green-400 border border-green-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5 animate-pulse"></span>
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" 
                                    @click="activeEvent = {{ json_encode([
                                        'name' => $event->name,
                                        'date' => $event->start_at->format('d M Y'),
                                        'time' => $event->start_at->format('H:i') . ' WIB',
                                        'location_name' => $event->location_name,
                                        'location_address' => $event->location_address ?? $event->location_name,
                                        'description' => Str::limit($event->short_description ?? strip_tags($event->full_description) ?? '', 200),
                                        'total_registered' => $event->categories->sum('total_participants'),
                                        'total_paid' => $event->categories->sum('paid_participants'),
                                        'categories' => $event->categories->map(function($cat) {
                                            return [
                                                'name' => $cat->name,
                                                'price_regular' => number_format($cat->price_regular, 0, ',', '.'),
                                                'quota' => $cat->quota ?? 0,
                                                'total_participants' => $cat->total_participants ?? 0,
                                                'paid_participants' => $cat->paid_participants ?? 0,
                                            ];
                                        })
                                    ]) }}; showModal = true"
                                    class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors" title="Detail">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </button>
                                <a href="{{ route('eo.events.participants', $event) }}" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors" title="Participants">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </a>
                                <a href="{{ route('eo.events.blast', $event) }}" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors" title="Blast Email">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </a>
                                <a href="{{ route('eo.events.edit', $event) }}" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <button type="button" class="p-2 rounded-lg bg-slate-800 text-red-400 hover:bg-red-500/20 hover:text-red-300 transition-colors" title="Delete" onclick="if(confirm('Delete this event?')) document.getElementById('delete-form-{{ $event->id }}').submit()">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                                <form id="delete-form-{{ $event->id }}" action="{{ route('eo.events.destroy', $event) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <h3 class="text-lg font-medium text-white mb-1">No Events Found</h3>
                                <p class="text-slate-500 text-sm mb-4">You haven't created any events yet.</p>
                                <a href="{{ route('eo.events.create') }}" class="text-yellow-400 hover:text-yellow-300 font-bold text-sm">Create your first event &rarr;</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($events->hasPages())
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/30">
            {{ $events->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Detail Event -->
    <div x-show="showModal" 
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto" 
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="showModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" 
            @click="showModal = false"></div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="showModal" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-slate-900 border border-slate-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">
                
                <!-- Modal Header -->
                <div class="bg-slate-800/50 px-4 py-3 sm:px-6 border-b border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-black italic text-white" id="modal-title">
                        EVENT <span class="text-yellow-400">DETAIL</span>
                    </h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-4 py-5 sm:p-6" x-if="activeEvent">
                    <!-- Event Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h4 class="text-xl font-bold text-white mb-2" x-text="activeEvent.name"></h4>
                            <div class="space-y-2 text-sm text-slate-300">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span x-text="activeEvent.date + ', ' + activeEvent.time"></span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-yellow-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <div>
                                        <div x-text="activeEvent.location_name"></div>
                                        <div class="text-xs text-slate-500" x-text="activeEvent.location_address"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-slate-400" x-text="activeEvent.description"></div>
                        </div>

                        <!-- Summary Stats -->
                        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700">
                            <h5 class="text-xs font-bold text-slate-400 uppercase mb-4 tracking-wider">Registration Summary</h5>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-900 rounded-lg p-3 border border-slate-700">
                                    <div class="text-xs text-slate-500">Total Registered</div>
                                    <div class="text-2xl font-black text-white" x-text="activeEvent.total_registered"></div>
                                </div>
                                <div class="bg-slate-900 rounded-lg p-3 border border-slate-700">
                                    <div class="text-xs text-slate-500">Total Paid (Confirmed)</div>
                                    <div class="text-2xl font-black text-neon-green text-green-400" x-text="activeEvent.total_paid"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <h5 class="text-sm font-bold text-white uppercase mb-3 border-l-4 border-yellow-500 pl-3">Participant Details</h5>
                    <div class="overflow-x-auto rounded-xl border border-slate-700">
                        <table class="w-full text-left text-sm text-slate-400">
                            <thead class="bg-slate-800 text-xs uppercase font-bold text-slate-300">
                                <tr>
                                    <th class="px-4 py-3">Category</th>
                                    <th class="px-4 py-3 text-right">Price</th>
                                    <th class="px-4 py-3 text-center">Quota</th>
                                    <th class="px-4 py-3 text-center">Registered</th>
                                    <th class="px-4 py-3 text-center">Paid</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 bg-slate-900">
                                <template x-for="cat in activeEvent.categories" :key="cat.name">
                                    <tr class="hover:bg-slate-800/50">
                                        <td class="px-4 py-3 font-medium text-white" x-text="cat.name"></td>
                                        <td class="px-4 py-3 text-right font-mono" x-text="'Rp ' + cat.price_regular"></td>
                                        <td class="px-4 py-3 text-center">
                                            <span x-text="cat.quota > 0 ? cat.quota : '∞'"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center text-white" x-text="cat.total_participants"></td>
                                        <td class="px-4 py-3 text-center text-green-400 font-bold" x-text="cat.paid_participants"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-slate-800/50 px-4 py-3 sm:px-6 border-t border-slate-700 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                        class="mt-3 inline-flex w-full justify-center rounded-lg bg-slate-700 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-slate-600 sm:mt-0 sm:w-auto transition-colors"
                        @click="showModal = false">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
