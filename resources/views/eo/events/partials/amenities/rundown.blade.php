<div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4" x-data="{ 
    rundowns: {{ json_encode(array_values(old('premium_amenities.rundown.items', isset($event) && isset($event->premium_amenities['rundown']['items']) ? $event->premium_amenities['rundown']['items'] : []))) }},
    addRundown() {
        this.rundowns.push({ time: '', title: '', description: '', is_highlight: false });
    },
    removeRundown(index) {
        this.rundowns.splice(index, 1);
    },
    usePreset() {
        this.rundowns = [
            { time: '04:30 WIB', title: 'Open Gate & Drop Bag', description: 'Area race village dan penitipan barang peserta dibuka.', is_highlight: false },
            { time: '05:00 WIB', title: 'Pemanasan Bersama', description: 'Sesi warming up bersama instruktur dan pengarahan rute.', is_highlight: false },
            { time: '{{ isset($event) && $event->start_at ? $event->start_at->format('H:i') . ' WIB' : '05:30 WIB' }}', title: 'Flag Off Start Lomba', description: 'Pelepasan resmi seluruh kategori lari.', is_highlight: true },
            { time: '07:00 WIB', title: 'Cut Off Time (COT)', description: 'Batas akhir waktu tempuh peserta melintasi garis finis.', is_highlight: false },
            { time: '07:15 WIB', title: 'Cooling Down & Entertainment', description: 'Pendinginan, panggung musik, dan zumba bersama.', is_highlight: false },
            { time: '08:00 WIB', title: 'Doorprize & Pembagian Podium', description: 'Pengundian hadiah doorprize dan seremoni pemenang lomba.', is_highlight: false }
        ];
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <div class="relative">
                <input type="checkbox" name="premium_amenities[rundown][enabled]" value="1" x-model="amenities.rundown" class="peer sr-only">
                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </div>
            <div>
                <span class="font-bold text-white text-sm sm:text-base">Rundown Acara (Jadwal Race Day)</span>
                <p class="text-xs text-slate-400">Atur jadwal kegiatan mulai dari Open Gate, Flag Off, hingga pembagian podium.</p>
            </div>
        </label>
        
        <div x-show="amenities.rundown" class="flex items-center gap-2 self-start sm:self-auto">
            <button type="button" @click="usePreset()" class="px-3 py-1.5 rounded-md bg-slate-700 hover:bg-slate-600 text-xs font-semibold text-slate-200 transition-colors flex items-center gap-1.5" title="Muat template susunan jadwal standar lari">
                <i class="fas fa-magic text-yellow-400 text-xs"></i>
                <span>Preset Standar</span>
            </button>
            <button type="button" @click="addRundown()" class="px-3 py-1.5 rounded-md bg-yellow-500 hover:bg-yellow-400 text-slate-900 text-xs font-bold transition-colors flex items-center gap-1.5">
                <i class="fas fa-plus text-xs"></i>
                <span>Tambah Jadwal</span>
            </button>
        </div>
    </div>

    <div x-show="amenities.rundown" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-4 border-t border-slate-700 pt-4 space-y-3">
        
        <template x-for="(rundown, index) in rundowns" :key="index">
            <div class="bg-slate-900/60 p-4 rounded-lg relative group border transition-colors" :class="rundown.is_highlight ? 'border-yellow-500/50 bg-yellow-500/5' : 'border-slate-800 hover:border-slate-700'">
                <button type="button" @click="removeRundown(index)" class="absolute top-3 right-3 text-slate-500 hover:text-red-400 transition-colors p-1" title="Hapus jadwal ini">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>

                <div class="space-y-3 pr-8">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Waktu / Jam</label>
                            <input type="text" :name="`premium_amenities[rundown][items][${index}][time]`" x-model="rundown.time" class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors font-mono" placeholder="05:30 WIB" required>
                        </div>
                        <div class="sm:col-span-8">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Agenda / Kegiatan</label>
                            <input type="text" :name="`premium_amenities[rundown][items][${index}][title]`" x-model="rundown.title" class="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. Flag Off Start Lomba" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                        <div class="sm:col-span-9">
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Keterangan Tambahan (Opsional)</label>
                            <input type="text" :name="`premium_amenities[rundown][items][${index}][description]`" x-model="rundown.description" class="w-full bg-slate-800/80 border border-slate-700 rounded-md px-3 py-1.5 text-white text-xs focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. Pelepasan resmi pelari kategori 10K dan 5K">
                        </div>
                        <div class="sm:col-span-3 pt-4 sm:pt-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" :name="`premium_amenities[rundown][items][${index}][is_highlight]`" value="1" x-model="rundown.is_highlight" class="rounded bg-slate-800 border-slate-700 text-yellow-500 focus:ring-yellow-500 h-4 w-4">
                                <span class="text-xs font-semibold text-slate-300">Highlight Acara</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="rundowns.length === 0" class="text-center py-6 border-2 border-dashed border-slate-800 rounded-lg">
            <p class="text-sm text-slate-400">Belum ada susunan jadwal acara yang ditambahkan.</p>
            <p class="text-xs text-slate-500 mt-1">Klik <strong>Preset Standar</strong> untuk mengisi otomatis atau <strong>Tambah Jadwal</strong> untuk input manual.</p>
        </div>
    </div>
</div>
