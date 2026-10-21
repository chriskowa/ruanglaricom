<div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4" x-data="{ 
    benefits: {{ json_encode(array_values(old('premium_amenities.benefits.items', isset($event) && isset($event->premium_amenities['benefits']['items']) ? $event->premium_amenities['benefits']['items'] : []))) }},
    addBenefit() {
        this.benefits.push({ title: '', tag: '', icon: 'fas fa-award', description: '' });
    },
    removeBenefit(index) {
        this.benefits.splice(index, 1);
    },
    useStandardPreset() {
        this.benefits = [
            { 
                title: 'Medali Finisher', 
                tag: 'All Finisher COT', 
                icon: 'fas fa-medal', 
                description: 'Medali cetak logam cor eksklusif untuk seluruh peserta yang menyelesaikan rute lomba dalam batas waktu Cut Off Time (COT).' 
            },
            { 
                title: 'Jersey Resmi', 
                tag: 'Dry-Fit Atletik', 
                icon: 'fas fa-tshirt', 
                description: 'Jersey lari berbahan Dry-Fit premium berpori mikro yang ringan, sejuk di iklim tropis, dan dirancang khusus mengikuti pola gerak atletik.' 
            },
            { 
                title: 'Refreshment & Pos Hidrasi', 
                tag: 'Rute & Garis Finish', 
                icon: 'fas fa-tint', 
                description: 'Pos hidrasi berkala berisi air mineral segar dan isotonik di sepanjang rute, serta camilan pemulihan nutrisi di tenda garis finish.' 
            },
            { 
                title: 'Nomor BIB', 
                tag: 'Nomor Dada Resmi', 
                icon: 'fas fa-id-card', 
                description: 'Nomor dada cetak presisi tahan air sebagai identitas atlet resmi yang memuat nama pelari, kategori lomba, dan data darurat.' 
            },
            { 
                title: 'Dokumentasi Foto', 
                tag: 'Akses Foto Gratis', 
                icon: 'fas fa-camera', 
                description: 'Foto aksi lari dan momen berharga di sepanjang jalur serta garis finish oleh tim fotografer profesional, dapat diunduh gratis pasca-event.' 
            },
            { 
                title: 'Race Pack Eksklusif', 
                tag: 'Paket RPC', 
                icon: 'fas fa-box-open', 
                description: 'Paket perlengkapan lari eksklusif berupa tas serut (drawstring bag), produk suplemen, voucher mitra, dan merchandise pendukung dari sponsor.' 
            }
        ];
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <div class="relative">
                <input type="checkbox" name="premium_amenities[benefits][enabled]" value="1" x-model="amenities.benefits" class="peer sr-only">
                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </div>
            <div>
                <span class="font-bold text-white text-sm sm:text-base">Fasilitas & Benefit Peserta (Race Entitlements)</span>
                <p class="text-xs text-slate-400">Atur paket kelengkapan yang didapatkan peserta (Medali, Jersey, Pos Hidrasi, BIB, Dokumentasi, dsb).</p>
            </div>
        </label>
        
        <div x-show="amenities.benefits" class="flex items-center gap-2 self-start sm:self-auto">
            <button type="button" @click="useStandardPreset()" class="px-3 py-1.5 rounded-md bg-slate-700 hover:bg-slate-600 text-xs font-semibold text-slate-200 transition-colors flex items-center gap-1.5" title="Muat 6 benefit standar resmi lari">
                <i class="fas fa-magic text-yellow-400 text-xs"></i>
                <span>Preset Standar (6 Benefit)</span>
            </button>
            <button type="button" @click="addBenefit()" class="px-3 py-1.5 rounded-md bg-yellow-500 hover:bg-yellow-400 text-slate-900 text-xs font-bold transition-colors flex items-center gap-1.5">
                <i class="fas fa-plus text-xs"></i>
                <span>Tambah Benefit</span>
            </button>
        </div>
    </div>

    <div x-show="amenities.benefits" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-4 border-t border-slate-700 pt-4 space-y-3">
        
        <template x-for="(benefit, index) in benefits" :key="index">
            <div class="bg-slate-900/60 p-4 rounded-lg relative group border border-slate-800 hover:border-slate-700 transition-colors">
                <button type="button" @click="removeBenefit(index)" class="absolute top-3 right-3 text-slate-500 hover:text-red-400 transition-colors p-1" title="Hapus benefit ini">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>

                <div class="space-y-3 pr-8">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                        <!-- Icon Selector -->
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Ikon Benefit</label>
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 rounded-md bg-slate-800 border border-slate-700 flex items-center justify-center text-yellow-400 shrink-0">
                                    <i :class="benefit.icon || 'fas fa-award'" class="text-sm"></i>
                                </div>
                                <select :name="`premium_amenities[benefits][items][${index}][icon]`" x-model="benefit.icon" class="w-full bg-slate-800 border border-slate-700 rounded-md px-2 py-2 text-white text-xs focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors">
                                    <option value="fas fa-medal">Medali (fas fa-medal)</option>
                                    <option value="fas fa-tshirt">Jersey (fas fa-tshirt)</option>
                                    <option value="fas fa-tint">Pos Hidrasi / Air (fas fa-tint)</option>
                                    <option value="fas fa-id-card">Nomor BIB (fas fa-id-card)</option>
                                    <option value="fas fa-camera">Dokumentasi (fas fa-camera)</option>
                                    <option value="fas fa-box-open">Race Pack (fas fa-box-open)</option>
                                    <option value="fas fa-file-invoice">E-Ticket / Sertifikat (fas fa-file-invoice)</option>
                                    <option value="fas fa-utensils">Konsumsi / Nutrisi (fas fa-utensils)</option>
                                    <option value="fas fa-heartbeat">Tim Medis (fas fa-heartbeat)</option>
                                    <option value="fas fa-gift">Doorprize (fas fa-gift)</option>
                                    <option value="fas fa-bus">Shuttle Bus (fas fa-bus)</option>
                                    <option value="fas fa-shield-alt">Asuransi (fas fa-shield-alt)</option>
                                    <option value="fas fa-award">Penghargaan / Umum (fas fa-award)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="sm:col-span-5">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Fasilitas / Benefit</label>
                            <input type="text" :name="`premium_amenities[benefits][items][${index}][title]`" x-model="benefit.title" class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. Medali Finisher" required>
                        </div>

                        <!-- Tag / Badge -->
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Tag / Keterangan Singkat</label>
                            <input type="text" :name="`premium_amenities[benefits][items][${index}][tag]`" x-model="benefit.tag" class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-white text-xs focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. All Finisher COT">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Deskripsi Lengkap Benefit</label>
                        <textarea :name="`premium_amenities[benefits][items][${index}][description]`" x-model="benefit.description" rows="2" class="w-full bg-slate-800/80 border border-slate-700 rounded-md px-3 py-1.5 text-white text-xs focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors leading-relaxed" placeholder="Jelaskan spesifikasi bahan, ketentuan pembagian, atau benefit detail yang didapatkan pelari..."></textarea>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="benefits.length === 0" class="text-center py-6 border-2 border-dashed border-slate-800 rounded-lg">
            <p class="text-sm text-slate-400">Belum ada fasilitas dan benefit yang ditambahkan.</p>
            <p class="text-xs text-slate-500 mt-1">Klik <strong>Preset Standar (6 Benefit)</strong> untuk memuat paket umum lari atau <strong>Tambah Benefit</strong> untuk input kustom.</p>
        </div>
    </div>
</div>
