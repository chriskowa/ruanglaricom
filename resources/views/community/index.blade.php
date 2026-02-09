@extends('layouts.pacerhub')
@php($withSidebar = false)

@section('title', 'Pendaftaran Komunitas')

@section('content')
<div class="min-h-screen px-4 md:px-8 py-10">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <div class="text-neon font-mono text-xs tracking-widest uppercase">Community Registration</div>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">Pendaftaran Komunitas</h1>
            <div class="text-slate-400 text-sm mt-1">Pilih event managed, isi PIC, lalu tambah peserta komunitas.</div>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-900/30 border border-red-500/30 text-red-200 rounded-2xl p-4 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('community.register.start') }}" method="POST" class="bg-card/80 backdrop-blur border border-slate-700/60 rounded-3xl p-6 space-y-6">
            @csrf

            <div class="p-5 rounded-3xl bg-slate-900/40 border border-slate-700/60">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-2xl bg-neon text-dark font-black flex items-center justify-center">1</div>
                    <div>
                        <div class="text-sm font-black text-white">Pilih Event</div>
                        <div class="text-xs text-slate-400">Ketik untuk mencari event managed.</div>
                    </div>
                </div>

                <div
                    class="mt-4"
                    x-data='communityEventData(@json($eventItems), @json($initialEventId))'
                >
                    <input type="hidden" name="event_id" x-model="selectedId" required>

                    <div class="relative">
                        <input
                            type="text"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-950/60 border border-slate-700 text-white focus:outline-none focus:border-neon"
                            placeholder="Cari event…"
                            x-model="query"
                            @focus="onFocus()"
                            @blur="onBlur()"
                            @input="open = true"
                        >

                        <div
                            x-show="open"
                            x-transition
                            class="absolute z-30 mt-2 w-full rounded-2xl bg-slate-950 border border-slate-700 shadow-2xl overflow-hidden"
                        >
                            <div class="max-h-72 overflow-y-auto">
                                <template x-for="item in filtered" :key="item.id">
                                    <button
                                        type="button"
                                        class="w-full text-left px-4 py-3 hover:bg-slate-900 transition flex items-center justify-between gap-3"
                                        @mousedown.prevent="select(item)"
                                    >
                                        <span class="text-sm text-white font-bold" x-text="item.label"></span>
                                        <span
                                            class="text-xs font-mono text-neon"
                                            x-show="Number(selectedId) === Number(item.id)"
                                        >SELECTED</span>
                                    </button>
                                </template>
                                <div x-show="filtered.length === 0" class="px-4 py-4 text-sm text-slate-400">
                                    Tidak ada event yang cocok.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 text-xs text-slate-500" x-show="selectedId">
                        Terpilih: <span class="text-slate-200 font-bold" x-text="selectedLabel"></span>
                    </div>
                </div>
            </div>

            @if(isset($communities) && count($communities) > 0)
            <div class="p-5 rounded-3xl bg-slate-900/40 border border-slate-700/60">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-2xl bg-slate-800 border border-slate-700 text-slate-200 font-black flex items-center justify-center">2</div>
                    <div>
                        <div class="text-sm font-black text-white">Pilih Komunitas (Opsional)</div>
                        <div class="text-xs text-slate-400">Jika sudah ada di master, pilih agar konsisten.</div>
                    </div>
                </div>

                <div class="mt-4">
                    <select name="community_id" id="community_select" class="w-full px-4 py-3 rounded-2xl bg-slate-950/60 border border-slate-700 text-white focus:outline-none focus:border-neon">
                        <option value="">-- Buat Komunitas Baru --</option>
                        @foreach($communities as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (PIC: {{ $c->pic_name }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <div id="manual_fields" class="p-5 rounded-3xl bg-slate-900/40 border border-slate-700/60 space-y-5">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-2xl bg-slate-800 border border-slate-700 text-slate-200 font-black flex items-center justify-center">3</div>
                    <div>
                        <div class="text-sm font-black text-white">Data Komunitas & PIC</div>
                        <div class="text-xs text-slate-400">Diisi jika belum memilih komunitas dari master.</div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Komunitas</label>
                    <input type="text" name="community_name" id="community_name" value="{{ old('community_name') }}" required class="w-full px-4 py-3 rounded-2xl bg-slate-950/60 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="Contoh: Komunitas Lari Bandung">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama PIC</label>
                        <input type="text" name="pic_name" id="pic_name" value="{{ old('pic_name') }}" required class="w-full px-4 py-3 rounded-2xl bg-slate-950/60 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="Nama lengkap">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email PIC</label>
                        <input type="email" name="pic_email" id="pic_email" value="{{ old('pic_email') }}" required class="w-full px-4 py-3 rounded-2xl bg-slate-950/60 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="email@contoh.com">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">WhatsApp PIC</label>
                        <input type="text" name="pic_phone" id="pic_phone" value="{{ old('pic_phone') }}" required class="w-full px-4 py-3 rounded-2xl bg-slate-950/60 border border-slate-700 text-white focus:outline-none focus:border-neon" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end">
                <button type="submit" class="px-6 py-3 rounded-2xl bg-neon text-dark font-black hover:bg-neon/90 transition">
                    Lanjut ke Form Peserta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function communityEventData(items, initialId) {
        return {
            open: false,
            query: '',
            items: items,
            selectedId: initialId,
            get filtered() {
                const q = (this.query || '').toLowerCase().trim();
                if (!q) return this.items;
                return this.items.filter(i => (i.label || '').toLowerCase().includes(q));
            },
            get selectedLabel() {
                const id = this.selectedId === '' ? null : Number(this.selectedId);
                const hit = this.items.find(i => Number(i.id) === id);
                return hit ? hit.label : '';
            },
            init() {
                if (this.selectedId) {
                    this.query = this.selectedLabel;
                }
            },
            onFocus() {
                this.open = true;
                this.query = '';
            },
            onBlur() {
                setTimeout(() => {
                    this.open = false;
                    this.query = this.selectedLabel;
                }, 120);
            },
            select(item) {
                this.selectedId = item.id;
                this.open = false;
                this.query = item.label;
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        const communitySelect = document.getElementById('community_select');
        const manualFields = document.getElementById('manual_fields');
        const inputs = manualFields.querySelectorAll('input');

        if (communitySelect) {
            communitySelect.addEventListener('change', function() {
                if (this.value) {
                    manualFields.style.display = 'none';
                    inputs.forEach(input => {
                        input.removeAttribute('required');
                        input.disabled = true; // Disable inputs to prevent submission
                    });

                    // Auto-submit if event is selected
                    const eventIdInput = document.querySelector('input[name="event_id"]');
                    if (eventIdInput && eventIdInput.value) {
                        // Show loading state
                        const btn = this.form.querySelector('button[type="submit"]');
                        if(btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                        }
                        this.form.submit();
                    } else {
                        alert('Mohon pilih event terlebih dahulu.');
                        this.value = ""; // Reset selection
                        manualFields.style.display = 'block'; // Show manual fields again
                        inputs.forEach(input => input.disabled = false); // Re-enable inputs
                    }
                } else {
                    manualFields.style.display = 'block';
                    inputs.forEach(input => {
                        input.setAttribute('required', 'required');
                        input.disabled = false;
                    });
                }
            });
            
            if (communitySelect.value) {
                communitySelect.dispatchEvent(new Event('change'));
            }
        }
    });
</script>
@endsection
