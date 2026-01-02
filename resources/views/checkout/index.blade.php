@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Checkout')

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 font-sans bg-dark text-slate-200">
    
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8" data-aos="fade-down">
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter mb-2">
                SECURE <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400">CHECKOUT</span>
            </h1>
            <p class="text-slate-400">Complete your purchase to start training.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Payment Form -->
            <div class="lg:col-span-2 space-y-6">
                
                @if(session('error'))
                    <div class="bg-red-500/10 border border-red-500/50 rounded-xl p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            <h4 class="font-bold text-red-500 text-sm">Transaction Failed</h4>
                            <p class="text-sm text-red-400 mt-1">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-500/10 border border-red-500/50 rounded-xl p-4">
                        <h4 class="font-bold text-red-500 text-sm mb-2">Please check the following errors:</h4>
                        <ul class="list-disc list-inside text-sm text-red-400 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('marketplace.checkout.store') }}" method="POST" id="checkout-form">
                    @csrf
                    
                    <!-- Payment Method -->
                    <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 p-6 md:p-8 mb-6 shadow-xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-neon font-mono text-sm">1</div>
                            Select Payment Method
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Wallet Option -->
                            <label class="block cursor-pointer group">
                                <input type="radio" name="payment_method" value="wallet" class="peer hidden" checked>
                                <div class="p-4 rounded-xl border border-slate-700 bg-slate-800/30 hover:bg-slate-800/50 transition-all flex items-center justify-between group-hover:border-slate-600 peer-checked:border-neon peer-checked:bg-neon/5 peer-checked:[&_.radio-indicator]:bg-neon peer-checked:[&_.radio-indicator]:border-neon peer-checked:[&_.radio-indicator]:shadow-[0_0_10px_rgba(204,255,0,0.3)]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-lg bg-slate-900 flex items-center justify-center border border-slate-700">
                                            <svg class="w-6 h-6 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-white">Wallet Balance</p>
                                            <p class="text-sm text-slate-400">Current Balance: <span class="text-neon font-bold">Rp {{ number_format($walletBalance, 0, ',', '.') }}</span></p>
                                        </div>
                                    </div>
                                    <div class="radio-indicator w-5 h-5 rounded-full border-2 border-slate-600 bg-transparent transition-all"></div>
                                </div>
                                @if($walletBalance < $total)
                                    <div class="mt-2 text-xs text-red-400 flex items-center gap-1 pl-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        Insufficient balance. <a href="{{ route('wallet.index') }}" class="underline hover:text-red-300 font-bold">Top up now</a>
                                    </div>
                                @endif
                            </label>

                            <!-- Midtrans Option (Disabled) -->
                            <label class="block cursor-not-allowed opacity-60">
                                <input type="radio" name="payment_method" value="midtrans" class="peer hidden" disabled>
                                <div class="p-4 rounded-xl border border-slate-700 bg-slate-800/30 flex items-center justify-between peer-checked:border-neon peer-checked:bg-neon/5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-lg bg-slate-900 flex items-center justify-center border border-slate-700 grayscale">
                                            <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-400">Credit Card / Bank Transfer</p>
                                            <p class="text-xs text-slate-500">Currently Unavailable</p>
                                        </div>
                                    </div>
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-700 bg-transparent"></div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 p-6 md:p-8 mb-6 shadow-xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-neon font-mono text-sm">2</div>
                            Additional Notes
                        </h3>
                        <textarea name="notes" rows="3" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl p-4 text-white placeholder-slate-500 focus:border-neon focus:ring-1 focus:ring-neon transition-all resize-none" placeholder="Any special requests or notes for your coach?"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submit-btn" class="w-full py-4 bg-neon hover:bg-white hover:text-dark text-dark font-black text-lg rounded-xl transition-all shadow-lg shadow-neon/20 flex items-center justify-center gap-2 group disabled:opacity-50 disabled:cursor-not-allowed">
                        <span>PAY NOW</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </button>
                    <p class="text-center text-xs text-slate-500 mt-4">
                        By proceeding, you agree to our Terms of Service and Privacy Policy.
                    </p>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="sticky top-24">
                    <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 p-6 shadow-xl">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            Order Summary
                        </h3>
                        
                        <div class="space-y-4 mb-6">
                            @foreach($cartItems as $item)
                                <div class="flex justify-between items-start pb-4 border-b border-slate-800">
                                    <div>
                                        <p class="text-sm font-bold text-white line-clamp-1">{{ $item->program->title }}</p>
                                        <p class="text-xs text-slate-400">Coach {{ $item->program->coach->name ?? 'Unknown' }}</p>
                                    </div>
                                    <p class="text-sm font-medium text-slate-300">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Subtotal</span>
                                <span class="text-white font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Tax</span>
                                <span class="text-white font-medium">Rp {{ number_format($tax, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-slate-800/50 rounded-xl p-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-400 font-bold uppercase">Total to Pay</span>
                                <span class="text-xl font-black text-neon">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-2 text-slate-500 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Secure Payment Encryption
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
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('checkout-form');
        const submitBtn = document.getElementById('submit-btn');
        
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                PROCESSING...
            `;
        });
    });
</script>
@endpush
