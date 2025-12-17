@extends('layouts.pacerhub')

@section('content')
    <div class="max-w-md mx-auto pt-28 pb-16 px-4">
        <h1 class="text-2xl font-extrabold text-white mb-2">Verifikasi OTP</h1>
        <p class="text-slate-400 mb-4">Masukkan kode OTP yang telah dikirim ke WhatsApp Anda.</p>
        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/40 text-green-300 p-3 rounded-xl mb-3">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('pacer.otp.verify') }}" class="bg-card border border-slate-700 rounded-2xl p-6 space-y-4">
            @csrf
            <input type="hidden" name="user_id" value="{{ request('user') }}" />
            <div>
                <label class="block text-sm text-slate-400 mb-1">Kode OTP</label>
                <input name="code" maxlength="6" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2 font-mono text-center tracking-widest" required />
            </div>
            <div class="flex justify-end gap-3">
                <button class="px-4 py-2 bg-neon text-dark font-bold rounded-lg">Verifikasi</button>
            </div>
        </form>
    </div>
@endsection

