@extends('layouts.pacerhub')

@section('title', 'Shopping Cart')

@push('styles')
<script>
    tailwind.config.theme.extend.colors.neon = '#ccff00';
</script>
<style>
    .glass-panel {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 font-sans bg-dark text-slate-200">
    
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8" data-aos="fade-down">
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter mb-2">
                YOUR <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400">CART</span>
            </h1>
            <p class="text-slate-400">Review your selected programs before checkout.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-4">
                @if($cartItems->count() > 0)
                    <div class="flex justify-end mb-4">
                        <form action="{{ route('marketplace.cart.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to empty your cart?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-300 font-bold flex items-center gap-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Empty Cart
                            </button>
                        </form>
                    </div>

                    @foreach($cartItems as $item)
                        <div class="glass-panel rounded-2xl p-4 md:p-6 hover:border-neon/30 transition-all group relative overflow-hidden">
                            <!-- Background Glow -->
                            <div class="absolute inset-0 bg-gradient-to-r from-neon/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                            <div class="flex flex-col md:flex-row gap-6 relative z-10">
                                <!-- Image -->
                                <div class="w-full md:w-32 h-32 rounded-xl overflow-hidden shrink-0 border border-slate-700">
                                    <img src="{{ $item->program->thumbnail_url ?? 'https://source.unsplash.com/random/200x200/?running' }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                </div>

                                <!-- Details -->
                                <div class="flex-grow flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-xl font-bold text-white mb-1 group-hover:text-neon transition-colors">
                                                    <a href="{{ route('programs.show', $item->program->slug) }}">{{ $item->program->title }}</a>
                                                </h3>
                                                <p class="text-sm text-slate-400 mb-2">Coach <span class="text-white font-bold">{{ $item->program->coach->name ?? 'Unknown' }}</span></p>
                                            </div>
                                            <form action="{{ route('marketplace.cart.remove', $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all" title="Remove Item">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">{{ $item->program->distance_target }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $item->program->difficulty == 'beginner' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : ($item->program->difficulty == 'intermediate' ? 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                                {{ ucfirst($item->program->difficulty) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-end mt-4 md:mt-0">
                                        <!-- Quantity Control (Usually 1 for programs, but keeping logic just in case) -->
                                        <div class="flex items-center gap-3 bg-slate-800/50 rounded-lg p-1 border border-slate-700">
                                            <button onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-700 text-slate-400 hover:text-white transition-colors" {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                                            <span class="w-8 text-center font-bold text-white text-sm" id="qty-text-{{ $item->id }}">{{ $item->quantity }}</span>
                                            <button onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-700 text-slate-400 hover:text-white transition-colors" {{ $item->quantity >= 10 ? 'disabled' : '' }}>+</button>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-xs text-slate-500 mb-0.5">Price</p>
                                            <p class="text-lg font-black text-neon">Rp <span id="subtotal-{{ $item->id }}">{{ number_format($item->subtotal, 0, ',', '.') }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="glass-panel rounded-2xl p-12 text-center border-dashed border-slate-700">
                        <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-600">
                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Your cart is empty</h3>
                        <p class="text-slate-400 mb-8">Looks like you haven't added any training programs yet.</p>
                        <a href="{{ route('programs.index') }}" class="px-8 py-3 bg-neon text-dark font-black rounded-xl hover:bg-white transition-all shadow-lg shadow-neon/20 inline-block">
                            Browse Programs
                        </a>
                    </div>
                @endif
            </div>

            <!-- Summary -->
            <div class="lg:col-span-1">
                <div class="sticky top-24">
                    <div class="glass-panel rounded-2xl p-6 border border-slate-700">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            Order Summary
                        </h3>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Subtotal</span>
                                <span class="text-white font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Tax</span>
                                <span class="text-white font-medium">Rp {{ number_format($tax, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-px bg-slate-700 my-2"></div>
                            <div class="flex justify-between text-lg">
                                <span class="text-white font-bold">Total</span>
                                <span class="text-neon font-black">Rp <span id="cart-total">{{ number_format($total, 0, ',', '.') }}</span></span>
                            </div>
                        </div>

                        @if($cartItems->count() > 0)
                            <a href="{{ route('marketplace.checkout.index') }}" class="block w-full py-4 bg-neon hover:bg-white hover:text-dark text-dark font-black text-center rounded-xl transition-all shadow-lg shadow-neon/20 mb-3 uppercase tracking-wider">
                                Checkout Now
                            </a>
                            <a href="{{ route('programs.index') }}" class="block w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-center rounded-xl transition-colors text-sm">
                                Continue Shopping
                            </a>
                        @else
                            <button disabled class="block w-full py-4 bg-slate-800 text-slate-500 font-bold text-center rounded-xl cursor-not-allowed mb-3 uppercase tracking-wider">
                                Checkout Now
                            </button>
                        @endif

                        <div class="mt-6 flex items-center justify-center gap-2 text-slate-500 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Secure Checkout
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateQuantity(cartId, quantity) {
        if (quantity < 1 || quantity > 10) return;
        
        // Show loading state if needed
        const qtyText = document.getElementById(`qty-text-${cartId}`);
        const originalQty = qtyText.innerText;
        qtyText.innerText = '...';

        fetch('{{ url("marketplace/cart") }}/' + cartId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                qtyText.innerText = quantity;
                document.getElementById(`subtotal-${cartId}`).innerText = new Intl.NumberFormat('id-ID').format(data.subtotal);
                document.getElementById('cart-total').innerText = new Intl.NumberFormat('id-ID').format(data.total);
                
                // Update cart count in nav
                if (window.fetchCartCount) window.fetchCartCount();
            } else {
                alert('Failed to update quantity');
                qtyText.innerText = originalQty;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
            qtyText.innerText = originalQty;
        });
    }
</script>
@endpush
