@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('content')
<div class="pt-24 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto min-h-screen">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black italic text-white uppercase tracking-wider">
                TRANSACTION <span class="text-neon">HISTORY</span>
            </h1>
            <p class="text-slate-400 mt-2">Manage your purchases and sales</p>
        </div>
    </div>

    <div x-data="{ tab: 'purchases' }" class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 overflow-hidden shadow-2xl">
        <!-- Tabs -->
        <div class="flex border-b border-slate-800">
            <button @click="tab = 'purchases'" 
                class="flex-1 py-4 text-center font-bold uppercase tracking-wider transition-all relative overflow-hidden group"
                :class="tab === 'purchases' ? 'text-neon bg-slate-800/50' : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                <span class="relative z-10">My Purchases</span>
                <div class="absolute bottom-0 left-0 w-full h-0.5 bg-neon transform scale-x-0 transition-transform duration-300"
                    :class="tab === 'purchases' ? 'scale-x-100' : 'group-hover:scale-x-50'"></div>
            </button>
            <button @click="tab = 'sales'" 
                class="flex-1 py-4 text-center font-bold uppercase tracking-wider transition-all relative overflow-hidden group"
                :class="tab === 'sales' ? 'text-neon bg-slate-800/50' : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                <span class="relative z-10">My Sales</span>
                <div class="absolute bottom-0 left-0 w-full h-0.5 bg-neon transform scale-x-0 transition-transform duration-300"
                    :class="tab === 'sales' ? 'scale-x-100' : 'group-hover:scale-x-50'"></div>
            </button>
        </div>

        <div class="p-6">
            <!-- Purchases Tab -->
            <div x-show="tab === 'purchases'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                @forelse($purchases as $order)
                    <div class="group bg-slate-800/40 rounded-xl p-5 mb-4 border border-slate-700 hover:border-neon/50 transition-all hover:shadow-lg hover:shadow-neon/5">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-xs font-mono text-slate-400 bg-slate-800 px-2 py-1 rounded border border-slate-700">
                                        {{ $order->created_at->format('d M Y') }}
                                    </span>
                                    <span class="text-xs font-mono text-neon">#{{ $order->invoice_number }}</span>
                                </div>
                                
                                <h3 class="font-bold text-white text-lg group-hover:text-neon transition-colors">
                                    {{ $order->items->first()->product_title_snapshot }}
                                    @if($order->items->count() > 1)
                                        <span class="text-sm font-normal text-slate-400 ml-2">+{{ $order->items->count() - 1 }} others</span>
                                    @endif
                                </h3>
                            </div>

                            <div class="text-right w-full md:w-auto flex flex-row md:flex-col justify-between items-center md:items-end">
                                <div class="mb-0 md:mb-2">
                                    <div class="text-2xl font-black text-white italic">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border
                                        {{ $order->status === 'completed' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 
                                          ($order->status === 'shipped' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 
                                          ($order->status === 'paid' ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20' :
                                           'bg-slate-700 text-slate-300 border-slate-600')) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                    
                                    <a href="{{ route('marketplace.orders.show', $order->id) }}" 
                                       class="px-4 py-2 bg-slate-700 hover:bg-neon hover:text-slate-900 text-white text-sm font-bold rounded-lg transition-all flex items-center gap-2 group/btn">
                                        Details
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">No purchases yet</h3>
                        <p class="text-slate-400 mb-6">Start exploring our marketplace to find great gear!</p>
                        <a href="{{ route('marketplace.index') }}" class="inline-block px-6 py-3 bg-neon text-slate-900 font-bold rounded-xl hover:scale-105 transition-transform">
                            Browse Marketplace
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Sales Tab -->
            <div x-show="tab === 'sales'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                @forelse($sales as $order)
                    <div class="group bg-slate-800/40 rounded-xl p-5 mb-4 border border-slate-700 hover:border-neon/50 transition-all hover:shadow-lg hover:shadow-neon/5">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-xs font-mono text-slate-400 bg-slate-800 px-2 py-1 rounded border border-slate-700">
                                        {{ $order->created_at->format('d M Y') }}
                                    </span>
                                    <span class="text-xs font-mono text-neon">#{{ $order->invoice_number }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-slate-400 text-sm">Buyer:</span>
                                    <span class="text-white font-bold">{{ $order->buyer->name }}</span>
                                </div>
                                <div class="text-sm text-slate-400">
                                    Total Items: {{ $order->items->count() }}
                                </div>
                            </div>

                            <div class="text-right w-full md:w-auto flex flex-row md:flex-col justify-between items-center md:items-end">
                                <div class="mb-0 md:mb-2">
                                    <div class="text-2xl font-black text-white italic">
                                        Rp {{ number_format($order->seller_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border
                                        {{ $order->status === 'completed' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 
                                          ($order->status === 'shipped' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 
                                          ($order->status === 'paid' ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20' :
                                           'bg-slate-700 text-slate-300 border-slate-600')) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                    
                                    <a href="{{ route('marketplace.orders.show', $order->id) }}" 
                                       class="px-4 py-2 bg-slate-700 hover:bg-neon hover:text-slate-900 text-white text-sm font-bold rounded-lg transition-all flex items-center gap-2 group/btn">
                                        Manage
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">No sales yet</h3>
                        <p class="text-slate-400 mb-6">Start selling your gear to the community!</p>
                        @if(Auth::user()->is_seller)
                            <a href="{{ route('marketplace.seller.products.create') }}" class="inline-block px-6 py-3 bg-neon text-slate-900 font-bold rounded-xl hover:scale-105 transition-transform">
                                Add Product
                            </a>
                        @else
                             <a href="{{ route('marketplace.seller.register') }}" class="inline-block px-6 py-3 bg-neon text-slate-900 font-bold rounded-xl hover:scale-105 transition-transform">
                                Become a Seller
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
