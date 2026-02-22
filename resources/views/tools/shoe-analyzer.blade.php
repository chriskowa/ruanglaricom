@extends('layouts.pacerhub')

@section('title', 'Shoe Analyzer – TreadAI')

@section('content')
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="max-w-5xl mx-auto px-4 py-10">
            <div class="mb-8">
                <h1 class="text-3xl md:text-4xl font-black tracking-tight text-white">
                    TreadAI <span class="text-emerald-400">Shoe Analyzer</span>
                </h1>
                <p class="mt-3 text-sm md:text-base text-slate-400 max-w-2xl">
                    Foto outsole sepatu lari kamu dan dapatkan analisis usia sepatu, risiko cedera, rekomendasi sepatu berikutnya, dan fokus penguatan otot yang paling relevan.
                </p>
            </div>

            <div id="shoe-analyzer-app" class="grid md:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] gap-6 items-start">
                <div class="space-y-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 md:p-5 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-[11px] tracking-widest uppercase text-slate-500">Input</p>
                                <h2 class="text-lg md:text-xl font-bold text-white">Foto outsole & jarak tempuh</h2>
                            </div>
                            <div class="text-[11px] px-2 py-1 rounded-full bg-slate-800 text-slate-300 border border-slate-700">
                                <span class="hidden md:inline">Langkah 1 dari 2</span>
                                <span class="md:hidden">1/2</span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div
                                class="relative border-2 border-dashed rounded-xl px-4 py-8 flex flex-col items-center justify-center cursor-pointer transition
                                    bg-slate-900/60 border-slate-700 hover:border-emerald-400/70 hover:bg-slate-900/80"
                                :class="dragOver ? 'border-emerald-400 bg-slate-900/80' : ''"
                                @dragover.prevent="dragOver = true"
                                @dragleave.prevent="dragOver = false"
                                @drop.prevent="handleDrop"
                                @click="triggerFile"
                            >
                                <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center mb-4 border border-slate-700">
                                    <i class="fa-solid fa-shoe-prints text-emerald-400 text-xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-white text-center">
                                    Tarik & lepas foto outsole sepatu lari kamu
                                </p>
                                <p class="mt-1 text-xs text-slate-400 text-center max-w-xs">
                                    Ideal: tampak bawah sepatu (outsole) dengan pencahayaan jelas. Format JPG/PNG, maksimal 10MB.
                                </p>
                                <button
                                    type="button"
                                    class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold
                                        bg-emerald-500 text-slate-950 hover:bg-emerald-400 transition"
                                >
                                    <i class="fa-solid fa-upload"></i>
                                    Pilih foto dari galeri
                                </button>
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    ref="fileInput"
                                    @change="handleFileChange"
                                >
                                <p v-if="fileName" class="mt-3 text-[11px] text-emerald-300 font-mono truncate max-w-full">
                                    @{{ fileName }}
                                </p>
                            </div>

                            <div class="grid grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] gap-3">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                            Perkiraan total jarak tempuh sepatu ini <span class="text-slate-500">(km)</span>
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="number"
                                                min="0"
                                                max="10000"
                                                step="10"
                                                v-model.number="estimatedMileage"
                                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400"
                                                placeholder="Contoh: 350"
                                            >
                                        </div>
                                        <p class="mt-1 text-[11px] text-slate-500">
                                            Boleh kira-kira. Info ini membantu mengestimasi sisa nyawa sepatu.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-2">
                                            Pola keausan outsole yang paling mirip
                                        </label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button
                                                type="button"
                                                class="text-[11px] px-2.5 py-1.5 rounded-lg border flex flex-col items-start gap-0.5"
                                                :class="wearPattern === 'heel_lateral' ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="setWearPattern('heel_lateral')"
                                            >
                                                <span class="font-semibold">Tumit luar lebih habis</span>
                                                <span class="text-[10px] text-slate-400">Jejak cenderung di sisi luar tumit.</span>
                                            </button>
                                            <button
                                                type="button"
                                                class="text-[11px] px-2.5 py-1.5 rounded-lg border flex flex-col items-start gap-0.5"
                                                :class="wearPattern === 'heel_medial' ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="setWearPattern('heel_medial')"
                                            >
                                                <span class="font-semibold">Tumit dalam lebih habis</span>
                                                <span class="text-[10px] text-slate-400">Jejak berat di sisi dalam tumit.</span>
                                            </button>
                                            <button
                                                type="button"
                                                class="text-[11px] px-2.5 py-1.5 rounded-lg border flex flex-col items-start gap-0.5"
                                                :class="wearPattern === 'forefoot_lateral' ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="setWearPattern('forefoot_lateral')"
                                            >
                                                <span class="font-semibold">Depan luar lebih habis</span>
                                                <span class="text-[10px] text-slate-400">Bagian luar forefoot menipis duluan.</span>
                                            </button>
                                            <button
                                                type="button"
                                                class="text-[11px] px-2.5 py-1.5 rounded-lg border flex flex-col items-start gap-0.5"
                                                :class="wearPattern === 'forefoot_medial' ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="setWearPattern('forefoot_medial')"
                                            >
                                                <span class="font-semibold">Depan dalam lebih habis</span>
                                                <span class="text-[10px] text-slate-400">Bagian dalam forefoot lebih terkikis.</span>
                                            </button>
                                            <button
                                                type="button"
                                                class="text-[11px] px-2.5 py-1.5 rounded-lg border flex flex-col items-start gap-0.5"
                                                :class="wearPattern === 'midfoot' ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="setWearPattern('midfoot')"
                                            >
                                                <span class="font-semibold">Tengah tapak paling habis</span>
                                                <span class="text-[10px] text-slate-400">Keausan fokus di midfoot.</span>
                                            </button>
                                            <button
                                                type="button"
                                                class="text-[11px] px-2.5 py-1.5 rounded-lg border flex flex-col items-start gap-0.5"
                                                :class="wearPattern === 'even' ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="setWearPattern('even')"
                                            >
                                                <span class="font-semibold">Merata</span>
                                                <span class="text-[10px] text-slate-400">Hampir semua area aus merata.</span>
                                            </button>
                                        </div>
                                        <p class="mt-1 text-[11px] text-slate-500">
                                            Lihat outsole baik-baik lalu pilih pola yang paling mendekati.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                            Keausan kanan-kiri terasa…
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                type="button"
                                                class="px-2.5 py-1.5 rounded-full text-[11px] border"
                                                :class="wearSymmetry === 'symmetrical' ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="setWearSymmetry('symmetrical')"
                                            >
                                                Kurang lebih seimbang
                                            </button>
                                            <button
                                                type="button"
                                                class="px-2.5 py-1.5 rounded-full text-[11px] border"
                                                :class="wearSymmetry === 'asymmetrical' ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="setWearSymmetry('asymmetrical')"
                                            >
                                                Jelas beda kanan vs kiri
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                            Bagian tubuh yang paling sering nyeri belakangan ini
                                        </label>
                                        <div class="flex flex-wrap gap-1.5">
                                            <button
                                                type="button"
                                                class="px-2.5 py-1.5 rounded-full text-[11px] border"
                                                :class="painZones.has('knee') ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="togglePainZone('knee')"
                                            >
                                                Lutut
                                            </button>
                                            <button
                                                type="button"
                                                class="px-2.5 py-1.5 rounded-full text-[11px] border"
                                                :class="painZones.has('shin') ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="togglePainZone('shin')"
                                            >
                                                Tulang kering
                                            </button>
                                            <button
                                                type="button"
                                                class="px-2.5 py-1.5 rounded-full text-[11px] border"
                                                :class="painZones.has('it_band') ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="togglePainZone('it_band')"
                                            >
                                                Sisi luar paha/lutut
                                            </button>
                                            <button
                                                type="button"
                                                class="px-2.5 py-1.5 rounded-full text-[11px] border"
                                                :class="painZones.has('achilles') ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="togglePainZone('achilles')"
                                            >
                                                Achilles
                                            </button>
                                            <button
                                                type="button"
                                                class="px-2.5 py-1.5 rounded-full text-[11px] border"
                                                :class="painZones.has('plantar') ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="togglePainZone('plantar')"
                                            >
                                                Telapak kaki
                                            </button>
                                            <button
                                                type="button"
                                                class="px-2.5 py-1.5 rounded-full text-[11px] border"
                                                :class="painZones.has('hip') ? 'border-emerald-400 bg-emerald-500/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-200'"
                                                @click="togglePainZone('hip')"
                                            >
                                                Pinggul
                                            </button>
                                        </div>
                                        <p class="mt-1 text-[11px] text-slate-500">
                                            Info nyeri membantu menghubungkan pola outsole dengan risiko cedera spesifik.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-col justify-between gap-2">
                                    <button
                                        type="button"
                                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold
                                            border border-slate-700 bg-slate-900 text-slate-100 hover:bg-slate-800 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                        :disabled="!canAnalyze"
                                        @click="startAnalysis"
                                    >
                                        <span v-if="phase === 'idle' || phase === 'error'">
                                            <i class="fa-solid fa-magnifying-glass-chart mr-1 text-emerald-400"></i>
                                            Analisis outsole sekarang
                                        </span>
                                        <span v-else-if="phase === 'scanning'">
                                            <i class="fa-solid fa-circle-notch mr-2 animate-spin text-emerald-400"></i>
                                            Membaca pola keausan...
                                        </span>
                                        <span v-else>
                                            <i class="fa-solid fa-rotate mr-1 text-emerald-400"></i>
                                            Analisis ulang dengan foto lain
                                        </span>
                                    </button>
                                    <p class="text-[11px] text-slate-500 leading-snug">
                                        TreadAI tidak menyimpan foto di server. Gambar hanya dipakai sementara untuk memproses analisis ini.
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="errorMessage"
                                class="mt-2 text-xs text-red-300 bg-red-900/30 border border-red-500/40 rounded-lg px-3 py-2"
                            >
                                @{{ errorMessage }}
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 md:p-5 h-full flex flex-col">
                        <p class="text-[11px] tracking-widest uppercase text-slate-500 mb-3">Preview</p>
                        <div class="relative flex-1 flex items-center justify-center rounded-xl bg-slate-950 border border-slate-800 overflow-hidden min-h-[220px]">
                            <template v-if="imagePreviewUrl">
                                <img :src="imagePreviewUrl" alt="Preview outsole" class="max-h-72 max-w-full object-contain">
                                <div
                                    v-if="phase === 'scanning'"
                                    class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm flex flex-col items-center justify-center"
                                >
                                    <div class="relative w-40 h-40 border border-emerald-400/40 rounded-xl overflow-hidden">
                                        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-emerald-400/20 to-transparent animate-pulse"></div>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="w-24 h-24 border border-emerald-400/60 rounded-full flex items-center justify-center">
                                                <i class="fa-solid fa-magnifying-glass-chart text-emerald-400 text-2xl"></i>
                                            </div>
                                        </div>
                                        <div class="absolute inset-x-4 h-[2px] bg-emerald-400 shadow-[0_0_20px_#22c55e] animate-ping opacity-70"></div>
                                    </div>
                                    <p class="mt-4 text-xs text-emerald-300 font-mono tracking-wide">MEMBACA POLA KEAUSAN...</p>
                                    <p class="mt-1 text-[11px] text-slate-300 text-center px-6">
                                        Mengestimasi distribusi beban, pola pendaratan, dan nyawa midsole berdasarkan jejak outsole.
                                    </p>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex flex-col items-center text-center px-6">
                                    <div class="w-16 h-16 rounded-full bg-slate-900 flex items-center justify-center mb-3 border border-slate-700">
                                        <i class="fa-solid fa-camera text-slate-400 text-xl"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-200">Belum ada foto outsole</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Ambil foto tampak bawah sepatu lari favoritmu. Pastikan cahaya cukup dan seluruh outsole terlihat.
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 md:p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-[11px] tracking-widest uppercase text-slate-500">Ringkasan</p>
                                <h2 class="text-lg md:text-xl font-bold text-white">Shoe Health & Landing Profile</h2>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] text-slate-500">Shoe Health</p>
                                <p class="text-sm md:text-base font-semibold" :class="healthStatusColor">
                                    @{{ displayHealthStatus }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                    <span>Persentase keausan outsole</span>
                                    <span class="font-semibold text-slate-200">@{{ wearPercentageLabel }}</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                                    <div
                                        class="h-2 rounded-full transition-all duration-500"
                                        :class="healthBarColor"
                                        :style="{ width: wearBarWidth }"
                                    ></div>
                                </div>
                                <p class="text-[11px] text-slate-500">
                                    Outsole di atas 80% aus biasanya menandakan midsole di dalamnya juga mulai kehilangan kemampuan menyerap benturan.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-3">
                                    <p class="text-[11px] text-slate-500 mb-1">Estimasi jarak tempuh</p>
                                    <p class="text-sm font-semibold text-slate-100">
                                        @{{ estimatedMileageLabel }}
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-500">
                                        Perkiraan dari input kamu, bukan GPS pasti.
                                    </p>
                                </div>
                                <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-3">
                                    <p class="text-[11px] text-slate-500 mb-1">Sisa aman sebelum “dead foam”</p>
                                    <p class="text-sm font-semibold" :class="remainingKmColor">
                                        @{{ remainingKmLabel }}
                                    </p>
                                    <p class="mt-1 text-[11px] text-slate-500">
                                        Setelah melewati batas, risiko nyeri sendi dan otot meningkat signifikan.
                                    </p>
                                </div>
                            </div>

                            <div class="border-t border-slate-800 pt-3 mt-2">
                                <p class="text-[11px] text-slate-500 mb-1">Landing & support profile</p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="px-2 py-1 rounded-full text-[11px] font-semibold" :class="landingBadgeColor">
                                            @{{ displayBiomechanicsType }}
                                        </div>
                                        <span class="text-[11px] text-slate-400">
                                            Cara kaki mendarat dari jejak outsole.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 md:p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] tracking-widest uppercase text-slate-500">Risk Assessment</p>
                                <h2 class="text-lg font-bold text-white">Peringatan risiko cedera</h2>
                            </div>
                            <div class="flex items-center gap-2 text-[11px]">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full border" :class="injuryLevelBadgeColor">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="injuryDotColor"></span>
                                    <span class="font-semibold">@{{ displayInjuryLevel }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <p class="text-xs text-slate-300 leading-relaxed">
                                @{{ injuryWarning }}
                            </p>

                            <div v-if="injuryList.length" class="space-y-1">
                                <p class="text-[11px] text-slate-500">Cedera yang paling sering muncul pada pola seperti ini:</p>
                                <ul class="text-xs text-slate-200 space-y-1 list-disc list-inside">
                                    <li v-for="injury in injuryList" :key="injury">
                                        @{{ injury }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 md:p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] tracking-widest uppercase text-slate-500">Next Gear</p>
                                <h2 class="text-lg font-bold text-white">Kurasi sepatu berikutnya</h2>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-400/30">
                                    @{{ gearTypeLabel }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                @{{ gearReason }}
                            </p>

                            <div v-if="gearExamples.length" class="space-y-1">
                                <p class="text-[11px] text-slate-500">Contoh lini sepatu dengan karakter mirip:</p>
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="model in gearExamples"
                                        :key="model"
                                        class="px-2 py-1 rounded-full text-[11px] bg-slate-950 border border-slate-700 text-slate-200"
                                    >
                                        @{{ model }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 md:p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] tracking-widest uppercase text-slate-500">Coaching</p>
                                <h2 class="text-lg font-bold text-white">Strength & mobility focus</h2>
                            </div>
                        </div>

                        <p class="text-xs text-slate-300 leading-relaxed">
                            @{{ formAdvice }}
                        </p>
                        <p class="text-[11px] text-slate-500">
                            Fokuskan latihan 2–3x seminggu, lalu pantau apakah pola keausan outsole di sepatu berikutnya mulai lebih seimbang.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const { createApp, ref, computed, onMounted } = Vue;

            const app = createApp({
                setup() {
                    const fileInput = ref(null);
                    const dragOver = ref(false);
                    const imageFile = ref(null);
                    const imagePreviewUrl = ref(null);
                    const fileName = ref('');
                    const estimatedMileage = ref(null);
                    const wearPattern = ref(null);
                    const wearSymmetry = ref(null);
                    const painZones = ref(new Set());
                    const phase = ref('idle');
                    const errorMessage = ref('');
                    const result = ref(null);
                    const analyzeUrl = @json(route('tools.shoe-analyzer.analyze'));

                    const canAnalyze = computed(() => !!imageFile.value && phase.value !== 'scanning');

                    const wearPercentage = computed(() => {
                        if (!result.value || typeof result.value.wear_percentage !== 'number') {
                            return null;
                        }
                        return Math.max(0, Math.min(100, result.value.wear_percentage));
                    });

                    const wearBarWidth = computed(() => {
                        if (wearPercentage.value === null) {
                            return '0%';
                        }
                        return wearPercentage.value + '%';
                    });

                    const wearPercentageLabel = computed(() => {
                        if (wearPercentage.value === null) {
                            return '--';
                        }
                        return wearPercentage.value + '% aus';
                    });

                    const displayHealthStatus = computed(() => {
                        if (!result.value || !result.value.health_status) {
                            return 'Belum dianalisis';
                        }
                        if (result.value.health_status === 'Healthy') return 'Sehat';
                        if (result.value.health_status === 'Warning') return 'Mendekati batas';
                        if (result.value.health_status === 'Critical') return 'Kritis';
                        return result.value.health_status;
                    });

                    const healthStatusColor = computed(() => {
                        if (!result.value || !result.value.health_status) {
                            return 'text-slate-400';
                        }
                        if (result.value.health_status === 'Healthy') return 'text-emerald-400';
                        if (result.value.health_status === 'Warning') return 'text-yellow-400';
                        if (result.value.health_status === 'Critical') return 'text-red-400';
                        return 'text-slate-400';
                    });

                    const healthBarColor = computed(() => {
                        if (!result.value || !result.value.health_status) {
                            return 'bg-slate-700';
                        }
                        if (result.value.health_status === 'Healthy') return 'bg-emerald-500';
                        if (result.value.health_status === 'Warning') return 'bg-yellow-500';
                        if (result.value.health_status === 'Critical') return 'bg-red-500';
                        return 'bg-slate-700';
                    });

                    const estimatedMileageLabel = computed(() => {
                        const inputKm = typeof estimatedMileage.value === 'number' && !Number.isNaN(estimatedMileage.value)
                            ? estimatedMileage.value
                            : null;
                        if (inputKm === null && (!result.value || typeof result.value.estimated_remaining_km !== 'number')) {
                            return '--';
                        }
                        if (inputKm !== null) {
                            return inputKm + ' km';
                        }
                        return '--';
                    });

                    const remainingKm = computed(() => {
                        if (!result.value || typeof result.value.estimated_remaining_km !== 'number') {
                            return null;
                        }
                        return Math.max(0, result.value.estimated_remaining_km);
                    });

                    const remainingKmLabel = computed(() => {
                        if (remainingKm.value === null) {
                            return '--';
                        }
                        if (remainingKm.value === 0) {
                            return '0 km (sudah melewati batas aman)';
                        }
                        return remainingKm.value + ' km';
                    });

                    const remainingKmColor = computed(() => {
                        if (remainingKm.value === null) {
                            return 'text-slate-300';
                        }
                        if (remainingKm.value === 0) {
                            return 'text-red-400';
                        }
                        if (remainingKm.value < 100) {
                            return 'text-yellow-400';
                        }
                        return 'text-emerald-400';
                    });

                    const displayBiomechanicsType = computed(() => {
                        if (!result.value || !result.value.biomechanics_type) {
                            return 'Belum dianalisis';
                        }
                        return result.value.biomechanics_type;
                    });

                    const landingBadgeColor = computed(() => {
                        if (!result.value || !result.value.biomechanics_type) {
                            return 'bg-slate-800 text-slate-300 border border-slate-600';
                        }
                        const type = String(result.value.biomechanics_type).toLowerCase();
                        if (type.includes('overpronation')) {
                            return 'bg-amber-500/15 text-amber-300 border border-amber-400/40';
                        }
                        if (type.includes('supination')) {
                            return 'bg-sky-500/15 text-sky-300 border border-sky-400/40';
                        }
                        return 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/40';
                    });

                    const injuryData = computed(() => {
                        return result.value && result.value.injury_risks ? result.value.injury_risks : {};
                    });

                    const displayInjuryLevel = computed(() => {
                        const level = String(injuryData.value.level || '').toLowerCase();
                        if (!level) return 'Belum dianalisis';
                        if (level === 'high') return 'High';
                        if (level === 'medium') return 'Medium';
                        if (level === 'low') return 'Low';
                        return injuryData.value.level;
                    });

                    const injuryLevelBadgeColor = computed(() => {
                        const level = String(injuryData.value.level || '').toLowerCase();
                        if (!level) {
                            return 'border-slate-600 bg-slate-900 text-slate-300';
                        }
                        if (level === 'high') {
                            return 'border-red-500/40 bg-red-500/10 text-red-200';
                        }
                        if (level === 'medium') {
                            return 'border-yellow-500/40 bg-yellow-500/10 text-yellow-200';
                        }
                        return 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200';
                    });

                    const injuryDotColor = computed(() => {
                        const level = String(injuryData.value.level || '').toLowerCase();
                        if (level === 'high') return 'bg-red-400';
                        if (level === 'medium') return 'bg-yellow-400';
                        if (level === 'low') return 'bg-emerald-400';
                        return 'bg-slate-500';
                    });

                    const injuryWarning = computed(() => {
                        if (injuryData.value.biomechanical_warning) {
                            return injuryData.value.biomechanical_warning;
                        }
                        if (!result.value) {
                            return 'Belum ada analisis. Upload foto outsole dan mulai analisis untuk melihat risiko cedera yang paling mungkin muncul.';
                        }
                        return 'Pola keausan outsole memberikan petunjuk tentang distribusi beban dan arah gaya yang berulang di tubuh kamu. Perubahan kecil di sini bisa mencegah banyak masalah ke depan.';
                    });

                    const injuryList = computed(() => {
                        if (!Array.isArray(injuryData.value.potential_injuries)) {
                            return [];
                        }
                        return injuryData.value.potential_injuries;
                    });

                    const gearData = computed(() => {
                        return result.value && result.value.gear_recommendation ? result.value.gear_recommendation : {};
                    });

                    const gearTypeLabel = computed(() => {
                        if (!gearData.value.type) {
                            return 'Belum dianalisis';
                        }
                        return gearData.value.type;
                    });

                    const gearReason = computed(() => {
                        if (gearData.value.reason) {
                            return gearData.value.reason;
                        }
                        if (!result.value) {
                            return 'Setelah analisis outsole selesai, kamu akan mendapat rekomendasi kategori sepatu yang paling cocok dengan biomekanikmu.';
                        }
                        return '';
                    });

                    const gearExamples = computed(() => {
                        if (!Array.isArray(gearData.value.examples)) {
                            return [];
                        }
                        return gearData.value.examples;
                    });

                    const formAdvice = computed(() => {
                        if (result.value && result.value.form_advice) {
                            return result.value.form_advice;
                        }
                        if (!result.value) {
                            return 'Sepatu hanya alat bantu. Akar masalah pendaratan yang kurang ideal biasanya ada pada kontrol otot dan mobilitas sendi. Setelah analisis outsole selesai, kamu akan mendapat fokus penguatan otot dan mobilitas yang paling relevan dengan pola keausanmu.';
                        }
                        return '';
                    });

                    const wearPatternValue = computed(() => wearPattern.value || '');

                    const wearSymmetryValue = computed(() => wearSymmetry.value || '');

                    const painZonesValue = computed(() => {
                        if (!(painZones.value instanceof Set)) {
                            return '';
                        }
                        return Array.from(painZones.value.values()).join(',');
                    });

                    const setWearPattern = (value) => {
                        wearPattern.value = value;
                    };

                    const setWearSymmetry = (value) => {
                        wearSymmetry.value = value;
                    };

                    const togglePainZone = (zone) => {
                        if (!(painZones.value instanceof Set)) {
                            painZones.value = new Set();
                        }
                        if (painZones.value.has(zone)) {
                            painZones.value.delete(zone);
                        } else {
                            painZones.value.add(zone);
                        }
                        painZones.value = new Set(painZones.value);
                    };

                    const triggerFile = () => {
                        if (fileInput.value) {
                            fileInput.value.click();
                        }
                    };

                    const handleFileChange = (event) => {
                        const files = event.target.files || [];
                        if (!files.length) {
                            return;
                        }
                        setImageFile(files[0]);
                    };

                    const handleDrop = (event) => {
                        dragOver.value = false;
                        const files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : [];
                        if (!files.length) {
                            return;
                        }
                        setImageFile(files[0]);
                    };

                    const setImageFile = (file) => {
                        errorMessage.value = '';
                        if (!file || !file.type || !file.type.startsWith('image/')) {
                            errorMessage.value = 'File harus berupa gambar (JPG, PNG, atau sejenisnya).';
                            return;
                        }
                        if (file.size > 10 * 1024 * 1024) {
                            errorMessage.value = 'Ukuran file maksimal 10MB.';
                            return;
                        }
                        imageFile.value = file;
                        fileName.value = file.name;
                        if (imagePreviewUrl.value) {
                            URL.revokeObjectURL(imagePreviewUrl.value);
                        }
                        imagePreviewUrl.value = URL.createObjectURL(file);
                        if (phase.value === 'result') {
                            phase.value = 'idle';
                            result.value = null;
                        }
                    };

                    const startAnalysis = () => {
                        if (!imageFile.value || phase.value === 'scanning') {
                            return;
                        }
                        errorMessage.value = '';
                        phase.value = 'scanning';

                        setTimeout(() => {
                            doAnalyze();
                        }, 900);
                    };

                    const doAnalyze = async () => {
                        const formData = new FormData();
                        formData.append('shoe_image', imageFile.value);
                        if (typeof estimatedMileage.value === 'number' && !Number.isNaN(estimatedMileage.value)) {
                            formData.append('estimated_mileage', String(estimatedMileage.value));
                        }
                        if (wearPatternValue.value) {
                            formData.append('wear_pattern', wearPatternValue.value);
                        }
                        if (wearSymmetryValue.value) {
                            formData.append('wear_symmetry', wearSymmetryValue.value);
                        }
                        if (painZonesValue.value) {
                            formData.append('pain_zones', painZonesValue.value);
                        }

                        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                        const csrf = tokenMeta ? tokenMeta.getAttribute('content') : null;

                        try {
                            const res = await fetch(analyzeUrl, {
                                method: 'POST',
                                headers: csrf ? { 'X-CSRF-TOKEN': csrf } : {},
                                body: formData,
                            });
                            const data = await res.json().catch(() => null);
                            if (!res.ok) {
                                errorMessage.value = (data && (data.message || data.error)) || 'Analisis gagal diproses. Coba ulang dengan foto lain.';
                                phase.value = 'error';
                                return;
                            }
                            result.value = data;
                            phase.value = 'result';
                        } catch (e) {
                            errorMessage.value = 'Terjadi kendala koneksi. Coba ulang beberapa saat lagi.';
                            phase.value = 'error';
                        }
                    };

                    onMounted(() => {
                        const mobileRule = window.matchMedia && window.matchMedia('(max-width: 768px)');
                        if (mobileRule && mobileRule.matches) {
                            estimatedMileage.value = null;
                        }
                    });

                    return {
                        fileInput,
                        dragOver,
                        imagePreviewUrl,
                        fileName,
                        estimatedMileage,
                        wearPattern,
                        wearSymmetry,
                        painZones,
                        phase,
                        errorMessage,
                        result,
                        canAnalyze,
                        wearBarWidth,
                        wearPercentageLabel,
                        displayHealthStatus,
                        healthStatusColor,
                        healthBarColor,
                        estimatedMileageLabel,
                        remainingKmLabel,
                        remainingKmColor,
                        displayBiomechanicsType,
                        landingBadgeColor,
                        injuryList,
                        injuryWarning,
                        displayInjuryLevel,
                        injuryLevelBadgeColor,
                        injuryDotColor,
                        gearTypeLabel,
                        gearReason,
                        gearExamples,
                        formAdvice,
                        setWearPattern,
                        setWearSymmetry,
                        togglePainZone,
                        triggerFile,
                        handleFileChange,
                        handleDrop,
                        startAnalysis,
                    };
                },
            });

            app.mount('#shoe-analyzer-app');
        });
    </script>
@endsection
