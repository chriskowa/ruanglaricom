@php
    $oldAmenities = old('premium_amenities', []);
    $dbAmenities = $event->premium_amenities ?? [];
    
    $isEnabled = function($key) use ($oldAmenities, $dbAmenities) {
        if (!empty($oldAmenities)) {
            return isset($oldAmenities[$key]['enabled']);
        }
        return isset($dbAmenities[$key]['enabled']) && $dbAmenities[$key]['enabled'];
    };
@endphp

<div x-data="{
    amenities: {
        jersey: {{ $isEnabled('jersey') ? 'true' : 'false' }},
        medal: {{ $isEnabled('medal') ? 'true' : 'false' }},
        faq: {{ $isEnabled('faq') ? 'true' : 'false' }},
        rpc: {{ $isEnabled('rpc') ? 'true' : 'false' }},
        venue: {{ $isEnabled('venue') ? 'true' : 'false' }},
        what_to_bring: {{ $isEnabled('what_to_bring') ? 'true' : 'false' }}
    }
}" class="border-b border-slate-700 pb-8">
    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-sm border border-yellow-500/50">5</span>
        Premium Amenities
    </h3>

    <div class="space-y-4">
        @include('eo.events.partials.amenities.jersey')
        @include('eo.events.partials.amenities.medal')
        @include('eo.events.partials.amenities.faq')
        @include('eo.events.partials.amenities.rpc')
        @include('eo.events.partials.amenities.venue')
        @include('eo.events.partials.amenities.what-to-bring')
    </div>

    <!-- Form Fields Configuration -->
    <div class="mt-8 border-t border-slate-700/50 pt-6">
        <h4 class="text-lg font-bold text-white mb-3">Pendaftaran - Form Fields</h4>
        <p class="text-xs text-slate-400 mb-4">Pilih field data peserta mana saja yang ingin ditampilkan dan wajib diisi pada form pendaftaran (khusus tema Quick Light):</p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-900/50 border border-slate-800 p-4 rounded-xl">
            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" name="premium_amenities[form_fields][id_card]" value="1" 
                    {{ old('premium_amenities.form_fields.id_card', ($event->premium_amenities['form_fields']['id_card'] ?? 0)) == 1 ? 'checked' : '' }}
                    class="rounded bg-slate-800 border-slate-700 text-yellow-500 focus:ring-yellow-500">
                <div>
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">ID Card (KTP/SIM)</span>
                    <p class="text-[10px] text-slate-500">Meminta nomor kartu identitas peserta</p>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" name="premium_amenities[form_fields][address]" value="1" 
                    {{ old('premium_amenities.form_fields.address', ($event->premium_amenities['form_fields']['address'] ?? 0)) == 1 ? 'checked' : '' }}
                    class="rounded bg-slate-800 border-slate-700 text-yellow-500 focus:ring-yellow-500">
                <div>
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">Alamat Lengkap</span>
                    <p class="text-[10px] text-slate-500">Meminta alamat tempat tinggal peserta</p>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" name="premium_amenities[form_fields][date_of_birth]" value="1" 
                    {{ old('premium_amenities.form_fields.date_of_birth', ($event->premium_amenities['form_fields']['date_of_birth'] ?? 0)) == 1 ? 'checked' : '' }}
                    class="rounded bg-slate-800 border-slate-700 text-yellow-500 focus:ring-yellow-500">
                <div>
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">Tanggal Lahir</span>
                    <p class="text-[10px] text-slate-500">Meminta tanggal lahir peserta</p>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" name="premium_amenities[form_fields][emergency_contact]" value="1" 
                    {{ old('premium_amenities.form_fields.emergency_contact', ($event->premium_amenities['form_fields']['emergency_contact'] ?? 0)) == 1 ? 'checked' : '' }}
                    class="rounded bg-slate-800 border-slate-700 text-yellow-500 focus:ring-yellow-500">
                <div>
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">Kontak Darurat</span>
                    <p class="text-[10px] text-slate-500">Meminta nama & nomor telepon kontak darurat</p>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" name="premium_amenities[form_fields][jersey_size]" value="1" 
                    {{ old('premium_amenities.form_fields.jersey_size', ($event->premium_amenities['form_fields']['jersey_size'] ?? 0)) == 1 ? 'checked' : '' }}
                    class="rounded bg-slate-800 border-slate-700 text-yellow-500 focus:ring-yellow-500">
                <div>
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">Ukuran Jersey</span>
                    <p class="text-[10px] text-slate-500">Meminta ukuran baju jersey</p>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" name="premium_amenities[form_fields][target_time]" value="1" 
                    {{ old('premium_amenities.form_fields.target_time', ($event->premium_amenities['form_fields']['target_time'] ?? 0)) == 1 ? 'checked' : '' }}
                    class="rounded bg-slate-800 border-slate-700 text-yellow-500 focus:ring-yellow-500">
                <div>
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">Target Time</span>
                    <p class="text-[10px] text-slate-500">Meminta perkiraan waktu tempuh lari</p>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" name="premium_amenities[form_fields][blood_type]" value="1" 
                    {{ old('premium_amenities.form_fields.blood_type', ($event->premium_amenities['form_fields']['blood_type'] ?? 0)) == 1 ? 'checked' : '' }}
                    class="rounded bg-slate-800 border-slate-700 text-yellow-500 focus:ring-yellow-500">
                <div>
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">Golongan Darah</span>
                    <p class="text-[10px] text-slate-500">Meminta golongan darah peserta</p>
                </div>
            </label>
        </div>
    </div>
</div>
