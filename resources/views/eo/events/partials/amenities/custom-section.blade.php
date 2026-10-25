@php
    $csData = old('premium_amenities.custom_section', isset($event) && isset($event->premium_amenities['custom_section']) ? $event->premium_amenities['custom_section'] : []);
    $csTag = $csData['tag'] ?? '';
    $csTitle = $csData['title'] ?? '';
    $csDesc = $csData['description'] ?? '';
    $csBtnText = $csData['button_text'] ?? '';
    $csBtnUrl = $csData['button_url'] ?? '';
@endphp

<div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4" x-data="{
    tag: @js($csTag),
    title: @js($csTitle),
    btnText: @js($csBtnText),
    btnUrl: @js($csBtnUrl),
    fillShuttlePreset() {
        this.tag = 'Fasilitas & Transportasi';
        this.title = 'Layanan Free Shuttle Bus';
        const shuttleHtml = '<p>Panitia menyediakan fasilitas armada shuttle bus gratis pulang-pergi khusus peserta lomba terdaftar dari stasiun/terminal transit utama menuju area race village.</p><p><strong>Panduan &amp; Syarat Naik Shuttle:</strong></p><ol><li>Tunjukkan e-ticket atau BIB number kepada kru shuttle di halte penjemputan.</li><li>Harap tiba di titik kumpul paling lambat 20 menit sebelum jadwal keberangkatan bus.</li><li>Kapasitas tempat duduk <em>first-come first-served</em> sesuai antrean pelari.</li></ol>';
        if (window.editors && window.editors['custom_section_desc']) {
            window.editors['custom_section_desc'].setData(shuttleHtml);
        }
        const ta = document.getElementById('custom_section_desc');
        if (ta) ta.value = shuttleHtml;
        this.btnText = 'Lihat Titik Jemput di Google Maps';
        this.btnUrl = 'https://maps.google.com';
    }
}">
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <div class="relative">
                <input type="checkbox" name="premium_amenities[custom_section][enabled]" value="1" x-model="amenities.custom_section" class="peer sr-only">
                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </div>
            <span class="font-bold text-white">Custom Section / Info Tambahan (misal: Free Shuttle)</span>
        </label>
        <button type="button" 
                x-show="amenities.custom_section" 
                @click="fillShuttlePreset()" 
                class="text-xs font-semibold px-2.5 py-1 rounded bg-yellow-500/10 text-yellow-400 hover:bg-yellow-500/20 border border-yellow-500/30 transition-colors flex items-center gap-1.5"
                title="Isi contoh informasi Free Shuttle dengan sekali klik">
            <i class="fas fa-magic text-xs"></i>
            <span>Isi Contoh Free Shuttle</span>
        </button>
    </div>

    <div x-show="amenities.custom_section" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 -translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100 translate-y-0" 
         x-transition:leave-end="opacity-0 -translate-y-2" 
         class="mt-4 border-t border-slate-700 pt-4 space-y-4">
        
        <p class="text-xs text-slate-400">
            Tambahkan section khusus pada halaman event (misal info layanan shuttle bus, fasilitas penginapan, panduan khusus, dll). Sisi kiri menampilkan heading, deskripsi berformat HTML, dan tombol tautan. Sisi kanan menampilkan galeri foto showcase.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Judul Section (Heading) <span class="text-red-400">*</span></label>
                <input type="text" 
                       name="premium_amenities[custom_section][title]" 
                       x-model="title" 
                       id="custom_section_title"
                       class="w-full bg-slate-900 border border-slate-700 rounded-md px-3.5 py-2.5 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" 
                       placeholder="e.g. Layanan Free Shuttle Bus">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Kicker / Kategori Tag</label>
                <input type="text" 
                       name="premium_amenities[custom_section][tag]" 
                       x-model="tag" 
                       id="custom_section_tag"
                       class="w-full bg-slate-900 border border-slate-700 rounded-md px-3.5 py-2.5 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" 
                       placeholder="e.g. Fasilitas & Transportasi">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Informasi / Deskripsi Lengkap <span class="text-red-400">*</span></label>
            <div class="rounded-xl overflow-hidden border border-slate-700">
                <div id="custom_section_desc_editor"></div>
                <textarea name="premium_amenities[custom_section][description]" 
                          id="custom_section_desc" 
                          class="hidden">{!! $csDesc !!}</textarea>
            </div>
            <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">
                Editor teks kaya (CKEditor): Mendukung format paragraf, teks tebal/miring, daftar bernomor/bullet, tabel, dan tautan. Otomatis dirender dalam format HTML di halaman depan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Teks Tombol Tautan (Custom Button)</label>
                <input type="text" 
                       name="premium_amenities[custom_section][button_text]" 
                       x-model="btnText" 
                       id="custom_section_button_text"
                       class="w-full bg-slate-900 border border-slate-700 rounded-md px-3.5 py-2.5 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" 
                       placeholder="e.g. Lihat Titik Jemput di Google Maps">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">URL / Link Tombol</label>
                <input type="url" 
                       name="premium_amenities[custom_section][button_url]" 
                       x-model="btnUrl" 
                       id="custom_section_button_url"
                       class="w-full bg-slate-900 border border-slate-700 rounded-md px-3.5 py-2.5 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" 
                       placeholder="e.g. https://maps.google.com/... atau link WhatsApp / info sheet">
            </div>
        </div>

        <!-- Galeri Multiple Image Upload via Dropzone -->
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Galeri Foto Section (Multiple, Max 6 Foto, Drag untuk Urutkan)</label>
            <p class="text-xs text-slate-400 mb-2.5">Unggah foto armada bus, denah halte penjemputan, jadwal visual, atau foto fasilitas.</p>
            <div id="custom-section-dropzone" class="dropzone bg-slate-900 border-2 border-dashed border-slate-700 rounded-md hover:border-yellow-400 transition-colors min-h-[140px]">
                <div class="dz-message text-center py-6">
                    <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <p class="text-xs text-slate-400">Klik atau seret foto ke sini untuk mengunggah (Format: JPG, PNG, WEBP)</p>
                </div>
            </div>
            <div id="custom-section-images-inputs"></div>
        </div>
    </div>
</div>
