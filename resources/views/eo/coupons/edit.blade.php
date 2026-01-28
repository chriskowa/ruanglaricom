@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Edit Kupon')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans" x-data="couponForm()">
    
    <!-- Header -->
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('eo.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">Dashboard</a>
                </li>
                <li>
                    <a href="{{ route('eo.coupons.index') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                        <svg class="w-4 h-4 mx-2 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        Master Kupon
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mx-2 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium text-white">Edit: {{ $coupon->code }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl font-black text-white italic tracking-tighter">
            EDIT <span class="text-yellow-400">KUPON</span>
        </h1>
    </div>

    <!-- Form -->
    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10 p-6 md:p-8 max-w-4xl">
        <form action="{{ route('eo.coupons.update', $coupon) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Event Selection (Disabled / Readonly usually, but let's see) -->
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Event <span class="text-xs font-normal text-slate-500">(Tidak dapat diubah)</span></label>
                <div class="px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-slate-400 cursor-not-allowed">
                    {{ $coupon->event->name }}
                </div>
                <!-- Hidden input if needed validation, but update controller doesn't use event_id usually -->
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kode Kupon -->
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Kode Kupon <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" x-model="code" name="code" class="flex-1 bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white font-mono uppercase focus:outline-none focus:border-yellow-500 transition-colors" placeholder="Contoh: MERDEKA45" required>
                        <button type="button" @click="generateCode()" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl border border-slate-700 transition-colors" title="Generate Random Code">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </button>
                    </div>
                    @error('code') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tipe Diskon -->
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Tipe Diskon <span class="text-red-500">*</span></label>
                    <select x-model="type" name="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors" required>
                        <option value="percent">Persentase (%)</option>
                        <option value="fixed">Nominal Tetap (Rp)</option>
                    </select>
                    @error('type') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Nilai Diskon -->
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Nilai Diskon <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500" x-text="type === 'fixed' ? 'Rp' : ''" x-show="type === 'fixed'"></span>
                        <input type="number" name="value" value="{{ old('value', $coupon->value) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors" :class="type === 'fixed' ? 'pl-10' : ''" placeholder="0" required min="0">
                        <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-slate-500" x-text="type === 'percent' ? '%' : ''" x-show="type === 'percent'"></span>
                    </div>
                    @error('value') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Min Transaksi -->
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Min. Transaksi (Opsional)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500">Rp</span>
                        <input type="number" name="min_transaction_amount" value="{{ old('min_transaction_amount', $coupon->min_transaction_amount + 0) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors" placeholder="0" min="0">
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Isi 0 jika tidak ada minimum pembelian.</p>
                </div>
            </div>

            <div class="border-t border-slate-700/50 pt-6 mt-6">
                <h3 class="text-lg font-bold text-white mb-4">Batasan Penggunaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kuota Total -->
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Kuota Total (Stok)</label>
                        <div class="flex items-center gap-2 mb-1">
                             <span class="text-xs text-slate-400">Terpakai: {{ $coupon->used_count }}</span>
                        </div>
                        <input type="number" name="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors" placeholder="Kosongkan untuk tidak terbatas" min="1">
                        @error('max_uses') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Batas Per User -->
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Batas Per User</label>
                        <div class="h-5 mb-1"></div> <!-- Spacer align -->
                        <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors" placeholder="Contoh: 1" min="1">
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Tanggal Mulai</label>
                        <input type="datetime-local" name="start_at" value="{{ old('start_at', $coupon->start_at ? $coupon->start_at->format('Y-m-d\TH:i') : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
                    </div>

                    <!-- Tanggal Berakhir -->
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">Tanggal Berakhir</label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors">
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-700/50 pt-6 mt-6">
                <h3 class="text-lg font-bold text-white mb-4">Pengaturan Lainnya</h3>
                <div class="space-y-4">
                    <label class="flex items-center space-x-3 cursor-pointer group">
                        <input type="checkbox" name="is_active" class="form-checkbox h-5 w-5 text-yellow-500 rounded border-slate-600 bg-slate-800 focus:ring-offset-slate-900 focus:ring-yellow-500" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }} value="1">
                        <span class="text-slate-300 group-hover:text-white transition-colors">Aktifkan Kupon Ini</span>
                    </label>

                    <label class="flex items-center space-x-3 cursor-pointer group">
                        <input type="checkbox" name="is_stackable" class="form-checkbox h-5 w-5 text-yellow-500 rounded border-slate-600 bg-slate-800 focus:ring-offset-slate-900 focus:ring-yellow-500" {{ old('is_stackable', $coupon->is_stackable) ? 'checked' : '' }} value="1">
                        <span class="text-slate-300 group-hover:text-white transition-colors">Dapat Digabungkan dengan Promo Lain</span>
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-700">
                <a href="{{ route('eo.coupons.index') }}" class="px-6 py-3 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white font-bold transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-black font-black shadow-lg shadow-yellow-500/20 transition-all transform hover:scale-105">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function couponForm() {
        return {
            type: '{{ old('type', $coupon->type) }}',
            code: '{{ old('code', $coupon->code) }}',
            generateCode() {
                fetch('{{ route('eo.coupons.generate') }}')
                    .then(response => response.json())
                    .then(data => {
                        this.code = data.code;
                    });
            }
        }
    }
</script>
@endpush
@endsection
