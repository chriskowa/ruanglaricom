@extends('layouts.pacerhub')

@section('content')
    <div class="max-w-3xl mx-auto pt-28 pb-16 px-4">
        <h1 class="text-3xl font-extrabold text-white mb-6">Daftar sebagai Pacer</h1>
        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/40 text-red-300 p-4 rounded-xl mb-6">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pacer.register.store') }}" enctype="multipart/form-data" class="bg-card border border-slate-700 rounded-2xl p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Nama</label>
                    <input name="name" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2" required />
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Email</label>
                    <input name="email" type="email" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2" required />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Phone</label>
                    <input name="phone" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2" required />
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Upload Image (max 1MB)</label>
                    <input name="image" type="file" accept="image/*" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Kategori</label>
                    <select name="category" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2">
                        <option value="HM (21K)">HM (21K)</option>
                        <option value="FM (42K)">FM (42K)</option>
                        <option value="10K">10K</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Pace</label>
                    <input name="pace" placeholder="05:30" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2" required />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">WhatsApp (opsional)</label>
                    <input name="whatsapp" placeholder="6281234567890" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Nickname (opsional)</label>
                    <input name="nickname" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2" />
                </div>
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-1">Bio</label>
                <textarea name="bio" rows="4" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2"></textarea>
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-1">Tags (pisahkan dengan koma)</label>
                <input name="tags" placeholder="NegativeSplit, Marathon, Coach" class="w-full bg-slate-900 text-white rounded-lg border border-slate-700 px-3 py-2" />
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('pacer.index') }}" class="px-4 py-2 border border-slate-600 rounded-lg text-white">Batal</a>
                <button class="px-4 py-2 bg-neon text-dark font-bold rounded-lg">Daftar</button>
            </div>
        </form>
    </div>
@endsection

