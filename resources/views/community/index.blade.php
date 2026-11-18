@extends('layouts.pacerhub', ['withSidebar' => false])

@section('title', 'Pendaftaran Komunitas - Ruang Lari')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
<style>
    .kp-community-reg {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .kp-community-reg h1,
    .kp-community-reg h2 {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .kp-mono {
        font-family: 'JetBrains Mono', monospace;
        font-variant-numeric: tabular-nums;
    }
</style>
@endpush

@section('content')
@php
    $communityItems = ($communities ?? collect())->map(function ($c) {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'pic_name' => $c->pic_name ?: '-',
            'pic_phone' => $c->pic_phone ?: '',
        ];
    })->values();
    $hasCommunities = $communityItems->isNotEmpty();
    $initialMode = old('community_name') || !$hasCommunities ? 'new' : 'existing';
@endphp

<div class="kp-community-reg min-h-screen bg-slate-950 text-slate-100 pt-10 sm:pt-12 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl text-white">Pendaftaran Komunitas</h1>
            <p class="text-sm text-slate-300 mt-1.5 max-w-2xl">
                Daftarkan anggota komunitas secara kolektif dalam satu portal. Pilih event terlebih dahulu, lalu pilih komunitas Anda atau buat profil komunitas baru.
            </p>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="mb-6 bg-slate-900 border border-red-800 text-red-200 rounded-lg p-4 text-sm">
                <div class="font-semibold text-white mb-1">Periksa kembali isian formulir:</div>
                <ul class="list-disc pl-5 space-y-1 text-xs sm:text-sm">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('community.register.start') }}"
              method="POST"
              x-data='communityRegistrationForm(@json($eventItems), @json($initialEventId), @json($communityItems), @json(old("community_id")), @json($initialMode))'
              @submit="onSubmit($event)"
              class="space-y-5">
            @csrf

            {{-- Step 1: Pilih Event --}}
            <section class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="kp-mono w-7 h-7 rounded bg-neon text-slate-950 text-xs font-bold flex items-center justify-center shrink-0">01</span>
                        <div>
                            <h2 class="text-base text-white">Pilih Event</h2>
                            <p class="text-xs text-slate-400">Pilih event lari yang membuka pendaftaran komunitas.</p>
                        </div>
                    </div>
                    <span class="kp-mono text-xs text-slate-400 shrink-0" x-text="filteredEvents.length + ' Event'"></span>
                </div>

                <input type="hidden" name="event_id" :value="selectedEventId" required>

                <div class="mb-3">
                    <input type="search"
                           x-model="eventQuery"
                           placeholder="Cari nama event atau kota..."
                           class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon">
                </div>

                <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                    <template x-for="item in filteredEvents" :key="item.id">
                        <button type="button"
                                @click="selectEvent(item)"
                                :class="Number(selectedEventId) === Number(item.id)
                                    ? 'bg-slate-800 border-neon text-white'
                                    : 'bg-slate-950 border-slate-800 text-slate-200 hover:border-slate-700 hover:bg-slate-800'"
                                class="w-full text-left px-4 py-3 rounded-md border transition duration-150 flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold leading-snug" x-text="item.label"></span>
                            <span x-show="Number(selectedEventId) === Number(item.id)"
                                  class="kp-mono text-[11px] font-bold px-2 py-0.5 rounded bg-neon text-slate-950 shrink-0">
                                DIPILIH
                            </span>
                        </button>
                    </template>

                    <div x-show="filteredEvents.length === 0" class="py-6 text-center text-sm text-slate-400 bg-slate-950 border border-slate-800 rounded-md">
                        Tidak ada event yang cocok dengan pencarian Anda.
                    </div>
                </div>
            </section>

            {{-- Step 2: Pilih atau Buat Komunitas --}}
            <section class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="kp-mono w-7 h-7 rounded bg-slate-800 border border-slate-700 text-white text-xs font-bold flex items-center justify-center shrink-0">02</span>
                        <div>
                            <h2 class="text-base text-white">Data Komunitas & PIC</h2>
                            <p class="text-xs text-slate-400">Cari komunitas terdaftar atau daftarkan komunitas baru.</p>
                        </div>
                    </div>

                    @if($hasCommunities)
                    <div class="grid grid-cols-2 gap-1.5 bg-slate-950 p-1 rounded-md border border-slate-800 shrink-0" role="tablist">
                        <button type="button"
                                @click="setMode('existing')"
                                :class="mode === 'existing' ? 'bg-neon text-slate-950 font-bold' : 'bg-slate-950 text-slate-300 font-semibold hover:text-white'"
                                class="h-8 px-3 rounded text-xs transition duration-150 whitespace-nowrap">
                            Komunitas Terdaftar
                        </button>
                        <button type="button"
                                @click="setMode('new')"
                                :class="mode === 'new' ? 'bg-neon text-slate-950 font-bold' : 'bg-slate-950 text-slate-300 font-semibold hover:text-white'"
                                class="h-8 px-3 rounded text-xs transition duration-150 whitespace-nowrap">
                            Komunitas Baru
                        </button>
                    </div>
                    @endif
                </div>

                <input type="hidden" name="community_id" :value="mode === 'existing' ? (selectedCommunityId || '') : ''">

                {{-- Mode A: Searchable Existing Communities --}}
                @if($hasCommunities)
                <div x-show="mode === 'existing'" class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <input type="search"
                               x-model="communityQuery"
                               placeholder="Cari nama komunitas atau nama PIC..."
                               class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon">
                        <span class="kp-mono text-xs text-slate-400 shrink-0 whitespace-nowrap" x-text="filteredCommunities.length + ' Komunitas'"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-72 overflow-y-auto pr-1">
                        <template x-for="c in filteredCommunities" :key="c.id">
                            <button type="button"
                                    @click="selectCommunity(c)"
                                    :class="Number(selectedCommunityId) === Number(c.id)
                                        ? 'bg-slate-800 border-neon'
                                        : 'bg-slate-950 border-slate-800 hover:border-slate-700 hover:bg-slate-800'"
                                    class="text-left p-3.5 rounded-md border transition duration-150 flex items-start justify-between gap-2.5">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-white truncate" x-text="c.name"></div>
                                    <div class="text-xs text-slate-400 mt-0.5 truncate">
                                        PIC: <span class="text-slate-300" x-text="c.pic_name"></span>
                                    </div>
                                </div>
                                <span x-show="Number(selectedCommunityId) === Number(c.id)"
                                      class="kp-mono text-[10px] font-bold px-2 py-0.5 rounded bg-neon text-slate-950 shrink-0">
                                    DIPILIH
                                </span>
                            </button>
                        </template>
                    </div>

                    <div x-show="filteredCommunities.length === 0" class="py-6 px-4 text-center bg-slate-950 border border-slate-800 rounded-md">
                        <p class="text-sm text-slate-300">Komunitas tidak ditemukan.</p>
                        <button type="button"
                                @click="setMode('new')"
                                class="mt-2.5 inline-flex items-center justify-center h-9 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-white transition">
                            + Daftarkan Komunitas Baru
                        </button>
                    </div>
                </div>
                @endif

                {{-- Mode B: Create New Community & PIC --}}
                <div x-show="mode === 'new'" class="space-y-4">
                    <div>
                        <label for="community_name" class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Komunitas</label>
                        <input type="text"
                               name="community_name"
                               id="community_name"
                               value="{{ old('community_name') }}"
                               :disabled="mode !== 'new'"
                               :required="mode === 'new'"
                               placeholder="Contoh: Komunitas Lari Bandung"
                               class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="pic_name" class="block text-xs font-semibold text-slate-300 mb-1.5">Nama PIC</label>
                            <input type="text"
                                   name="pic_name"
                                   id="pic_name"
                                   value="{{ old('pic_name') }}"
                                   :disabled="mode !== 'new'"
                                   :required="mode === 'new'"
                                   placeholder="Nama lengkap penanggung jawab"
                                   class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon">
                        </div>
                        <div>
                            <label for="pic_email" class="block text-xs font-semibold text-slate-300 mb-1.5">Email PIC</label>
                            <input type="email"
                                   name="pic_email"
                                   id="pic_email"
                                   value="{{ old('pic_email') }}"
                                   :disabled="mode !== 'new'"
                                   :required="mode === 'new'"
                                   placeholder="email@contoh.com"
                                   class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="pic_phone" class="block text-xs font-semibold text-slate-300 mb-1.5">WhatsApp PIC</label>
                            <input type="text"
                                   name="pic_phone"
                                   id="pic_phone"
                                   inputmode="numeric"
                                   value="{{ old('pic_phone') }}"
                                   :disabled="mode !== 'new'"
                                   :required="mode === 'new'"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   placeholder="08xxxxxxxxxx"
                                   class="w-full h-10 px-3.5 rounded-md bg-slate-950 border border-slate-700 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-neon">
                        </div>
                    </div>
                </div>
            </section>

            {{-- Validation Hint & Submit --}}
            <div x-show="formError" x-text="formError" class="bg-slate-900 border border-red-800 text-red-200 rounded-md px-4 py-3 text-xs sm:text-sm"></div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                <p class="text-xs text-slate-400">
                    Setelah masuk portal, anggota dapat ditambah bertahap sebelum membuat invoice kolektif.
                </p>
                <button type="submit"
                        :disabled="submitting"
                        class="w-full sm:w-auto inline-flex items-center justify-center h-11 px-6 rounded-md bg-neon hover:bg-lime-300 text-slate-950 text-sm font-bold transition duration-150 whitespace-nowrap disabled:opacity-60">
                    <span x-text="submitting ? 'Memproses...' : 'Lanjut ke Form Peserta'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function communityRegistrationForm(events, initialEventId, communities, initialCommunityId, initialMode) {
        return {
            events: events || [],
            eventQuery: '',
            selectedEventId: initialEventId || '',
            communities: communities || [],
            communityQuery: '',
            selectedCommunityId: initialCommunityId || '',
            mode: initialMode || 'existing',
            formError: '',
            submitting: false,

            get filteredEvents() {
                const q = (this.eventQuery || '').toLowerCase().trim();
                if (!q) return this.events;
                return this.events.filter(e => (e.label || '').toLowerCase().includes(q));
            },

            get filteredCommunities() {
                const q = (this.communityQuery || '').toLowerCase().trim();
                if (!q) return this.communities;
                return this.communities.filter(c =>
                    (c.name || '').toLowerCase().includes(q) ||
                    (c.pic_name || '').toLowerCase().includes(q)
                );
            },

            selectEvent(item) {
                this.selectedEventId = item.id;
                this.formError = '';
            },

            selectCommunity(c) {
                this.selectedCommunityId = c.id;
                this.formError = '';
            },

            setMode(nextMode) {
                this.mode = nextMode;
                this.formError = '';
            },

            onSubmit(e) {
                if (!this.selectedEventId) {
                    e.preventDefault();
                    this.formError = 'Silakan pilih event terlebih dahulu.';
                    return;
                }
                if (this.mode === 'existing' && !this.selectedCommunityId) {
                    e.preventDefault();
                    this.formError = 'Silakan pilih komunitas dari daftar atau pindah ke tab Komunitas Baru.';
                    return;
                }
                this.formError = '';
                this.submitting = true;
            }
        };
    }
</script>
@endsection
