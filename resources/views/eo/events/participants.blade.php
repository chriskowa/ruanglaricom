@extends('layouts.pacerhub')

@section('title', 'Participants - ' . $event->name)

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
<div id="eo-participants-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 relative z-10" data-aos="fade-up">
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
                        <a href="{{ route('eo.events.index') }}" class="ml-1 text-sm font-medium text-slate-400 hover:text-white md:ml-2">Master Events</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-white md:ml-2">Participants</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    EVENT <span class="text-yellow-400">PARTICIPANTS</span>
                </h1>
                <p class="text-slate-400 text-lg mt-1">{{ $event->name }}</p>
            </div>
            <a id="exportLink" href="{{ route('eo.events.participants.export', $event) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-500 text-white font-bold flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 relative z-10">
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-xl p-4">
            <p class="text-slate-400 text-xs font-bold uppercase mb-1">Total Registered</p>
            <h3 id="statTotalRegistered" class="text-2xl font-black text-white">{{ $participants->total() }}</h3>
        </div>
        <div class="bg-green-900/20 backdrop-blur border border-green-500/30 rounded-xl p-4">
            <p class="text-green-400 text-xs font-bold uppercase mb-1">Paid & Confirmed</p>
            <h3 id="statPaidConfirmed" class="text-2xl font-black text-white">{{ \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status', 'paid'); })->count() }}</h3>
        </div>
        <div class="bg-blue-900/20 backdrop-blur border border-blue-500/30 rounded-xl p-4">
            <p class="text-blue-400 text-xs font-bold uppercase mb-1">Race Pack Picked Up</p>
            <h3 id="statPickedUp" class="text-2xl font-black text-white">{{ \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id); })->where('is_picked_up', true)->count() }}</h3>
        </div>
        <div class="bg-yellow-900/20 backdrop-blur border border-yellow-500/30 rounded-xl p-4">
            <p class="text-yellow-400 text-xs font-bold uppercase mb-1">Pending Pickup</p>
            <h3 id="statPendingPickup" class="text-2xl font-black text-white">{{ \App\Models\Participant::whereHas('transaction', function($q) use ($event) { $q->where('event_id', $event->id)->where('payment_status', 'paid'); })->where('is_picked_up', false)->count() }}</h3>
        </div>
    </div>

    <!-- Filter & Table -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10">
        
        <!-- Filters -->
        <div class="p-4 border-b border-slate-700 bg-slate-900/30">
            <form id="filtersForm" method="GET" action="{{ route('eo.events.participants', $event) }}" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Payment Status</label>
                    <select name="payment_status" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:border-yellow-400 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="expired" {{ request('payment_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="cod" {{ request('payment_status') == 'cod' ? 'selected' : '' }}>COD</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Pickup Status</label>
                    <select name="is_picked_up" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:border-yellow-400 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="0" {{ request('is_picked_up') === '0' ? 'selected' : '' }}>Not Picked Up</option>
                        <option value="1" {{ request('is_picked_up') === '1' ? 'selected' : '' }}>Picked Up</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-slate-400 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, email, atau nomor WA" class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg px-3 py-2 focus:border-yellow-400 focus:outline-none">
                </div>
                <div>
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-400 text-black font-bold py-2 px-4 rounded-lg transition-colors text-sm">
                        Filter
                    </button>
                    <a href="{{ route('eo.events.participants', $event) }}" class="ml-2 text-slate-400 hover:text-white text-sm">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900/50 text-xs uppercase font-bold text-slate-300">
                    <tr>
                        <th class="px-6 py-4">Participant</th>
                        <th class="px-6 py-4">Contact</th>
                        <th class="px-6 py-4">Category & BIB</th>
                        <th class="px-6 py-4">Payment</th>
                        <th class="px-6 py-4">Pickup Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="participantsTableBody" class="divide-y divide-slate-800">
                    @forelse($participants as $participant)
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-white">{{ $participant->name }}</div>
                            <div class="text-xs text-slate-500">Reg: {{ $participant->created_at->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-white">{{ $participant->email }}</div>
                            <div class="text-xs">{{ $participant->phone }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-200 w-fit">
                                    {{ $participant->category->name ?? '-' }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-900/30 text-yellow-400 border border-yellow-500/30 w-fit">
                                    BIB: {{ $participant->bib_number ?? 'N/A' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php $status = $participant->transaction->payment_status ?? 'pending'; @endphp
                            <div class="relative inline-block">
                                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                        data-dropdown="payment-{{ $participant->transaction->id }}"
                                        data-status="{{ $status }}"
                                        data-id="{{ $participant->transaction->id }}"
                                        onclick="togglePaymentDropdown(this)">
                                    <span class="status-label">{{ ucfirst($status) }}</span>
                                    <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <div id="payment-{{ $participant->transaction->id }}" class="absolute mt-2 w-36 bg-slate-900 border border-slate-700 rounded-lg shadow-xl hidden z-20">
                                    <button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus('{{ route('eo.events.transactions.payment-status', [$event, $participant->transaction->id]) }}', 'pending', this)">Pending</button>
                                    <button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus('{{ route('eo.events.transactions.payment-status', [$event, $participant->transaction->id]) }}', 'paid', this)">Paid</button>
                                    <button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus('{{ route('eo.events.transactions.payment-status', [$event, $participant->transaction->id]) }}', 'failed', this)">Failed</button>
                                    <button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus('{{ route('eo.events.transactions.payment-status', [$event, $participant->transaction->id]) }}', 'expired', this)">Expired</button>
                                    <button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus('{{ route('eo.events.transactions.payment-status', [$event, $participant->transaction->id]) }}', 'cod', this)">COD</button>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($status == 'paid')
                                <button onclick="openPickupModal({{ $participant->id }}, '{{ addslashes($participant->name) }}', {{ $participant->is_picked_up ? 'true' : 'false' }}, '{{ addslashes($participant->picked_up_by ?? '') }}')" class="hover:opacity-80 transition-opacity text-left">
                                    @if($participant->is_picked_up)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-400 border border-blue-500/30">
                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            Picked Up
                                        </span>
                                        <div class="text-xs text-slate-500 mt-1">By: {{ Str::limit($participant->picked_up_by, 15) }}</div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">
                                            Not Picked Up
                                        </span>
                                    @endif
                                </button>
                            @else
                                <span class="text-xs text-slate-500 italic">Payment required</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="mailto:{{ $participant->email }}" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors" title="Email">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $participant->phone)) }}" target="_blank" class="p-2 rounded-lg bg-slate-800 text-green-400 hover:bg-slate-700 hover:text-green-300 transition-colors" title="WhatsApp">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.536 0 1.52 1.115 2.988 1.264 3.186.149.198 2.19 3.361 5.27 4.69 2.151.928 2.988.94 3.518.865.592-.084 1.758-.717 2.006-1.41.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.381a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                </a>
                                @if($status != 'paid')
                                <button onclick="deleteParticipant({{ $participant->id }})" class="p-2 rounded-lg bg-slate-800 text-red-400 hover:bg-red-900/50 hover:text-red-300 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <p class="text-slate-500">No participants found matching your criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($participants->hasPages())
        <div id="paginationContainer" class="px-6 py-4 border-t border-slate-800 bg-slate-900/30">
            {{ $participants->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Pickup Modal -->
<div id="pickupModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-slate-800 border border-slate-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <form id="pickupForm" method="POST">
                    @csrf
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-white mb-4">Race Pack Pickup</h3>
                        <input type="hidden" name="participant_id" id="participant_id">
                        
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-slate-400 mb-1">Participant Name</label>
                            <input type="text" id="participant_name_display" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm" readonly>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-medium text-slate-400 mb-1">Status</label>
                            <select name="is_picked_up" id="pickup_status" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none">
                                <option value="0">Not Picked Up</option>
                                <option value="1">Picked Up</option>
                            </select>
                        </div>

                        <div id="picked_by_container" class="mb-4 hidden">
                            <label class="block text-xs font-medium text-slate-400 mb-1">Picked Up By (Name)</label>
                            <input type="text" name="picked_up_by" id="picked_up_by" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-yellow-400 focus:outline-none" placeholder="e.g. Self or Delegate Name">
                        </div>
                    </div>
                    <div class="bg-slate-900/50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" onclick="closePickupModal()" class="px-4 py-2 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700 text-sm font-bold">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-400 text-black text-sm font-bold">Save Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        var form = document.getElementById('filtersForm');
        var tbody = document.getElementById('participantsTableBody');
        var statTotal = document.getElementById('statTotalRegistered');
        var statPaid = document.getElementById('statPaidConfirmed');
        var statPicked = document.getElementById('statPickedUp');
        var statPending = document.getElementById('statPendingPickup');
        var exportLink = document.getElementById('exportLink');
        var pagination = document.getElementById('paginationContainer');
        var baseUrl = form.getAttribute('action');
        var eventId = {{ $event->id }};

        function qs(obj) {
            var params = new URLSearchParams();
            Object.keys(obj).forEach(function(k){
                if (obj[k] !== undefined && obj[k] !== null && obj[k] !== '') params.append(k, obj[k]);
            });
            return params.toString();
        }

        function serializeForm() {
            var data = {};
            var els = form.querySelectorAll('input, select');
            els.forEach(function(el){
                if (el.name) data[el.name] = el.value;
            });
            return data;
        }

        function phoneToWa(phone) {
            var digits = String(phone || '').replace(/[^0-9]/g, '');
            return digits.replace(/^0/, '62');
        }

        function renderRows(items) {
            var html = '';
            items.forEach(function(p){
                var status = p.payment_status || 'pending';
                var pickedBadge = '';
                if (status === 'paid') {
                    if (p.is_picked_up) {
                        pickedBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-400 border border-blue-500/30"><svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Picked Up</span><div class="text-xs text-slate-500 mt-1">By: ' + (p.picked_up_by ? p.picked_up_by.substring(0, 15) : '') + '</div>';
                    } else {
                        pickedBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Not Picked Up</span>';
                    }
                } else {
                    pickedBadge = '<span class="text-xs text-slate-500 italic">Payment required</span>';
                }
                var paymentBtn = '<button type="button" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border" data-dropdown="payment-'+ p.transaction_id +'" data-status="'+ status +'" data-id="'+ p.transaction_id +'" onclick="togglePaymentDropdown(this)"><span class="status-label">'+ (status.charAt(0).toUpperCase()+status.slice(1)) +'</span><svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></button>';
                var paymentDd = '<div id="payment-'+ p.transaction_id +'" class="absolute mt-2 w-36 bg-slate-900 border border-slate-700 rounded-lg shadow-xl hidden z-20">'+
                    '<button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus(\''+ p.payment_update_url +'\', \'pending\', this)">Pending</button>'+
                    '<button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus(\''+ p.payment_update_url +'\', \'paid\', this)">Paid</button>'+
                    '<button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus(\''+ p.payment_update_url +'\', \'failed\', this)">Failed</button>'+
                    '<button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus(\''+ p.payment_update_url +'\', \'expired\', this)">Expired</button>'+
                    '<button class="w-full text-left px-3 py-2 text-xs hover:bg-slate-800" onclick="updatePaymentStatus(\''+ p.payment_update_url +'\', \'cod\', this)">COD</button>'+
                '</div>';
                html += '<tr class="hover:bg-slate-800/50 transition-colors">'+
                    '<td class="px-6 py-4"><div class="font-medium text-white">'+ p.name +'</div><div class="text-xs text-slate-500">Reg: '+ p.created_at +'</div></td>'+
                    '<td class="px-6 py-4"><div class="text-white">'+ (p.email || '') +'</div><div class="text-xs">'+ (p.phone || '') +'</div></td>'+
                    '<td class="px-6 py-4"><div class="flex flex-col gap-1"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-200 w-fit">'+ (p.category || '-') +'</span><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-900/30 text-yellow-400 border border-yellow-500/30 w-fit">BIB: '+ (p.bib_number || 'N/A') +'</span></div></td>'+
                    '<td class="px-6 py-4"><div class="relative inline-block">'+ paymentBtn + paymentDd +'</div></td>'+
                    '<td class="px-6 py-4">'+ pickedBadge +'</td>'+
                    '<td class="px-6 py-4 text-right"><div class="flex items-center justify-end gap-2">'+
                        '<a href="mailto:'+ (p.email || '') +'" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors" title="Email"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2 2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></a>'+
                        '<a href="https://wa.me/'+ phoneToWa(p.phone) +'" target="_blank" class="p-2 rounded-lg bg-slate-800 text-green-400 hover:bg-slate-700 hover:text-green-300 transition-colors" title="WhatsApp"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.536 0 1.52 1.115 2.988 1.264 3.186.149.198 2.19 3.361 5.27 4.69 2.151.928 2.988.94 3.518.865.592-.084 1.758-.717 2.006-1.41.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.381a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg></a>'+
                        (status !== 'paid' ? '<button onclick="deleteParticipant('+ p.id +')" class="p-2 rounded-lg bg-slate-800 text-red-400 hover:bg-red-900/50 hover:text-red-300 transition-colors" title="Delete"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>' : '') +
                    '</div></td>'+
                '</tr>';
            });
            return html;
        }

        function updateStats(stats) {
            if (statTotal) statTotal.textContent = stats.total_registered;
            if (statPaid) statPaid.textContent = stats.paid_confirmed;
            if (statPicked) statPicked.textContent = stats.race_pack_picked_up;
            if (statPending) statPending.textContent = stats.pending_pickup;
        }

        function updateExportLink(data) {
            var q = qs(serializeForm());
            if (exportLink) exportLink.href = '{{ route('eo.events.participants.export', $event) }}' + (q ? ('?' + q) : '');
        }

        var debounceTimer;
        function debounce(fn, delay) {
            return function(){
                var args = arguments, ctx = this;
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function(){ fn.apply(ctx, args); }, delay || 300);
            }
        }

        function fetchParticipants() {
            var q = qs(serializeForm());
            var url = baseUrl + (q ? ('?' + q) : '');
            fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (!res || !res.success) { alert('Gagal memuat data'); return; }
                tbody.innerHTML = renderRows(res.data || []);
                document.querySelectorAll('button[data-dropdown]').forEach(function(btn){
                    setStatusButtonStyle(btn, btn.dataset.status || 'pending');
                });
                updateStats(res.stats || {});
                updateExportLink();
            })
            .catch(function(){ alert('Terjadi kesalahan'); });
        }

        form.addEventListener('submit', function(e){ e.preventDefault(); fetchParticipants(); });
        form.querySelectorAll('select').forEach(function(sel){
            sel.addEventListener('change', fetchParticipants);
        });
        var searchInput = form.querySelector('input[name="search"]');
        if (searchInput) searchInput.addEventListener('input', debounce(fetchParticipants, 400));

        if (pagination) {
            pagination.addEventListener('click', function(e){
                var a = e.target.closest('a');
                if (!a) return;
                e.preventDefault();
                var url = a.getAttribute('href');
                if (!url) return;
                fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    if (!res || !res.success) return;
                    tbody.innerHTML = renderRows(res.data || []);
                    document.querySelectorAll('button[data-dropdown]').forEach(function(btn){
                        setStatusButtonStyle(btn, btn.dataset.status || 'pending');
                    });
                    updateStats(res.stats || {});
                    updateExportLink();
                });
            });
        }
    })();
    function setStatusButtonStyle(btn, status) {
        var map = {
            paid: {bg:'bg-green-900/30', text:'text-green-400', border:'border-green-500/30'},
            pending: {bg:'bg-yellow-900/30', text:'text-yellow-400', border:'border-yellow-500/30'},
            failed: {bg:'bg-red-900/30', text:'text-red-400', border:'border-red-500/30'},
            expired: {bg:'bg-red-900/30', text:'text-red-400', border:'border-red-500/30'},
            cod: {bg:'bg-blue-900/30', text:'text-blue-400', border:'border-blue-500/30'}
        };
        var cls = map[status] || map['pending'];
        btn.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border ' + cls.bg + ' ' + cls.text + ' ' + cls.border;
        var label = btn.querySelector('.status-label');
        if (label) label.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        btn.dataset.status = status;
    }

    document.querySelectorAll('button[data-dropdown]').forEach(function(btn){
        setStatusButtonStyle(btn, btn.dataset.status || 'pending');
    });

    function togglePaymentDropdown(btn) {
        var id = btn.getAttribute('data-dropdown');
        var dd = document.getElementById(id);
        if (!dd) return;
        document.querySelectorAll('[id^="payment-"]').forEach(function(el){ if(el !== dd) el.classList.add('hidden'); });
        dd.classList.toggle('hidden');
        document.addEventListener('click', function onDoc(e){
            if (!dd.contains(e.target) && e.target !== btn) {
                dd.classList.add('hidden');
                document.removeEventListener('click', onDoc);
            }
        });
    }

    function updatePaymentStatus(url, status, el) {
        var dd = el.parentElement;
        var btn = dd.previousElementSibling;
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = tokenMeta ? tokenMeta.getAttribute('content') : '';
        el.disabled = true;
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
            body: JSON.stringify({ payment_status: status })
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res && res.success) {
                setStatusButtonStyle(btn, status);
                dd.classList.add('hidden');
            } else {
                alert(res.message || 'Gagal mengupdate status');
            }
        })
        .catch(function(){ alert('Terjadi kesalahan'); })
        .finally(function(){ el.disabled = false; });
    }

    function openPickupModal(id, name, isPickedUp, pickedBy) {
        document.getElementById('pickupModal').classList.remove('hidden');
        document.getElementById('participant_id').value = id;
        document.getElementById('participant_name_display').value = name;
        document.getElementById('pickup_status').value = isPickedUp ? '1' : '0';
        document.getElementById('picked_up_by').value = pickedBy || '';
        
        const form = document.getElementById('pickupForm');
        form.action = `{{ url('/eo/events/' . $event->id . '/participants') }}/${id}/status`;
        
        togglePickedByField();
    }

    function closePickupModal() {
        document.getElementById('pickupModal').classList.add('hidden');
    }

    function togglePickedByField() {
        const status = document.getElementById('pickup_status').value;
        const container = document.getElementById('picked_by_container');
        const input = document.getElementById('picked_up_by');
        
        if (status === '1') {
            container.classList.remove('hidden');
            input.required = true;
        } else {
            container.classList.add('hidden');
            input.required = false;
        }
    }

    document.getElementById('pickup_status').addEventListener('change', togglePickedByField);

    function deleteParticipant(id) {
        if (!confirm('Apakah anda yakin ingin menghapus peserta ini?')) return;
        
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = tokenMeta ? tokenMeta.getAttribute('content') : '';
        var url = '{{ url('eo/events/' . $event->id . '/participants') }}/' + id;
        
        fetch(url, {
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': csrf, 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success) {
                alert(res.message);
                var btn = document.querySelector('#filtersForm button[type="submit"]');
                if(btn) btn.click();
                else window.location.reload();
            } else {
                alert(res.message || 'Gagal menghapus peserta');
            }
        })
        .catch(function(){ alert('Terjadi kesalahan saat menghapus'); });
    }
</script>
@endsection
