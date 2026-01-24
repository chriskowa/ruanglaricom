@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Manage Products - Marketplace')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <div class="flex justify-between items-end mb-8">
        <div>
            <p class="text-neon font-mono text-sm tracking-widest uppercase mb-1">Seller Dashboard</p>
            <h1 class="text-4xl font-black text-white italic tracking-tighter">
                MY <span class="text-neon">PRODUCTS</span>
            </h1>
        </div>
        <a href="{{ route('marketplace.seller.products.create') }}" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Add Product
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl mb-6 flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-6 py-4 border-b border-slate-800 bg-slate-900/50 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Product
                        </th>
                        <th class="px-6 py-4 border-b border-slate-800 bg-slate-900/50 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Price
                        </th>
                        <th class="px-6 py-4 border-b border-slate-800 bg-slate-900/50 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Stock
                        </th>
                        <th class="px-6 py-4 border-b border-slate-800 bg-slate-900/50 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($products as $product)
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden border border-slate-700 bg-slate-800">
                                    @if($product->primaryImage)
                                        <img class="w-full h-full object-cover" src="{{ asset('storage/' . $product->primaryImage->image_path) }}" alt="" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-600">
                                            <svg class="w-6 h-6 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <p class="text-white font-bold text-sm">
                                        {{ $product->title }}
                                    </p>
                                    <p class="text-slate-500 text-xs mt-0.5 uppercase tracking-wide">
                                        {{ $product->type }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @if($product->sale_type === 'auction')
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-neon/15 border border-neon/30 text-neon">Lelang</span>
                                        @endif
                                        @if($product->fulfillment_mode === 'consignment')
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-cyan-500/10 border border-cyan-400/20 text-cyan-200">Titip Jual</span>
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-300">{{ $product->consignment_status }}</span>
                                        @endif
                                        @if(! $product->is_active)
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-yellow-500/10 border border-yellow-400/20 text-yellow-200">Hidden</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-slate-300 font-mono text-sm">
                                Rp {{ number_format($product->sale_type === 'auction' ? ($product->current_price ?? $product->starting_price ?? $product->price) : $product->price, 0, ',', '.') }}
                            </p>
                            @if($product->sale_type === 'auction' && $product->auction_end_at)
                                <p class="text-slate-500 text-xs mt-1 font-mono">Ends {{ $product->auction_end_at->diffForHumans() }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($product->fulfillment_mode === 'consignment' && $product->consignment_status === 'requested')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-200 border border-yellow-400/20">
                                    Pending Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->stock > 0 ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                    {{ $product->stock }} Available
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('marketplace.show', $product->slug) }}" target="_blank" class="text-slate-400 hover:text-cyan-400 transition-colors" title="View Product">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('marketplace.seller.products.edit', $product->id) }}" class="text-slate-400 hover:text-neon transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('marketplace.seller.products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
