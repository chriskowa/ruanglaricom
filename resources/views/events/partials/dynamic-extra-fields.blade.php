@php
    $ff = $event->premium_amenities['form_fields'] ?? [];
    $showBibName = !empty($ff['bib_name']);
    $showEmergencyRelation = !empty($ff['emergency_contact_relation']);
    $showMedicalHistory = !empty($ff['medical_history']);
    $isDark = ($mode ?? 'dark') === 'dark';
    $labelClass = $isDark
        ? 'block text-xs font-bold text-slate-300 uppercase mb-1'
        : 'block text-xs font-bold text-slate-700 uppercase mb-1';
    $inputClass = $isDark
        ? 'w-full rounded-md bg-slate-900 border border-slate-700 px-3 py-2 text-sm text-white placeholder-slate-400 focus:border-lime-400 focus:outline-none'
        : 'w-full rounded-md bg-white border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-slate-500 focus:outline-none';
    $hintClass = $isDark ? 'text-[11px] text-slate-400 mt-1' : 'text-[11px] text-slate-500 mt-1';
@endphp
@if($showBibName || $showEmergencyRelation || $showMedicalHistory)
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2">
    @if($showBibName)
    <div>
        <label class="{{ $labelClass }}">Nama di BIB (Opsional)</label>
        <input type="text" data-field="bib_name" name="participants[0][bib_name]" maxlength="20" placeholder="Contoh: BUDI (maks. 20 karakter)" class="{{ $inputClass }}">
        <p class="{{ $hintClass }}">Kosongkan jika ingin memakai nama lengkap.</p>
    </div>
    @endif
    @if($showEmergencyRelation)
    <div>
        <label class="{{ $labelClass }}">Hubungan Kontak Darurat</label>
        <select data-field="emergency_contact_relation" name="participants[0][emergency_contact_relation]" class="{{ $inputClass }}">
            <option value="">-- Pilih Hubungan --</option>
            <option value="Orang Tua">Orang Tua</option>
            <option value="Pasangan">Pasangan (Suami/Istri)</option>
            <option value="Saudara">Saudara Kandung</option>
            <option value="Kerabat">Kerabat / Keluarga</option>
            <option value="Teman">Teman / Rekan</option>
            <option value="Lainnya">Lainnya</option>
        </select>
    </div>
    @endif
    @if($showMedicalHistory)
    <div class="sm:col-span-2">
        <label class="{{ $labelClass }}">Riwayat Penyakit / Kondisi Medis (Opsional)</label>
        <input type="text" data-field="medical_history" name="participants[0][medical_history]" maxlength="500" placeholder="Contoh: Asma, alergi obat, atau tulis 'Tidak ada'" class="{{ $inputClass }}">
    </div>
    @endif
</div>
@endif
