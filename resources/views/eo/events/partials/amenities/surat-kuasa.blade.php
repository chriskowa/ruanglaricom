@php
    $suratKuasa = $event->premium_amenities['surat_kuasa'] ?? [];
    $existingFilePath = $suratKuasa['file_path'] ?? null;
    $existingFileName = $suratKuasa['original_name'] ?? ($existingFilePath ? basename($existingFilePath) : null);
    $existingTitle = $suratKuasa['title'] ?? 'Surat Kuasa Pengambilan Race Pack';
    $existingDesc = $suratKuasa['description'] ?? 'Surat kuasa bermaterai untuk pengambilan paket perlengkapan lomba (RPC) oleh perwakilan.';
    $existingExternalUrl = $suratKuasa['external_url'] ?? '';
@endphp

<div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <div class="relative">
                <input type="checkbox" name="premium_amenities[surat_kuasa][enabled]" value="1" x-model="amenities.surat_kuasa" class="peer sr-only">
                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </div>
            <div>
                <span class="font-bold text-white text-sm sm:text-base">Dokumen Peserta (Surat Kuasa & Formulir)</span>
                <p class="text-xs text-slate-400">Upload surat kuasa atau formulir resmi yang wajib dibawa peserta saat pengambilan Race Pack.</p>
            </div>
        </label>
    </div>

    <div x-show="amenities.surat_kuasa" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-4 border-t border-slate-700 pt-4 space-y-4">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Judul Dokumen</label>
                <input type="text" name="premium_amenities[surat_kuasa][title]" value="{{ old('premium_amenities.surat_kuasa.title', $existingTitle) }}" class="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. Surat Kuasa Pengambilan Race Pack">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Link Eksternal Alternatif (Opsional)</label>
                <input type="url" name="premium_amenities[surat_kuasa][external_url]" value="{{ old('premium_amenities.surat_kuasa.external_url', $existingExternalUrl) }}" class="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="https://drive.google.com/... atau link Cloudinary">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Keterangan / Deskripsi Dokumen</label>
            <input type="text" name="premium_amenities[surat_kuasa][description]" value="{{ old('premium_amenities.surat_kuasa.description', $existingDesc) }}" class="w-full bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-white text-sm focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors" placeholder="e.g. Surat kuasa bermaterai untuk pengambilan paket perlengkapan lomba (RPC) oleh perwakilan.">
        </div>

        <!-- File Upload Area -->
        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Upload File Dokumen Resmi</label>
            
            @if($existingFilePath)
                <div class="mb-3 p-3 bg-slate-900/80 border border-slate-700 rounded-md flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-yellow-500/10 text-yellow-400 border border-yellow-500/30 flex items-center justify-center shrink-0">
                            <i class="fas fa-file-alt text-sm"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-xs font-bold text-white truncate max-w-xs sm:max-w-md">{{ $existingFileName }}</div>
                            <div class="text-[11px] text-slate-400">File tersimpan di server</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 self-end sm:self-auto">
                        <a href="{{ asset('storage/' . $existingFilePath) }}" target="_blank" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-yellow-400 text-xs font-semibold transition-colors flex items-center gap-1.5">
                            <i class="fas fa-external-link-alt text-[10px]"></i>
                            <span>Buka Dokumen</span>
                        </a>
                        <label class="flex items-center gap-1.5 text-xs text-red-400 hover:text-red-300 cursor-pointer select-none">
                            <input type="checkbox" name="remove_surat_kuasa_file" value="1" class="rounded bg-slate-800 border-red-500/50 text-red-500 focus:ring-red-500">
                            <span>Hapus File</span>
                        </label>
                    </div>
                </div>
            @endif

            <div class="relative border-2 border-dashed border-slate-700 hover:border-yellow-400/70 rounded-lg p-5 text-center transition-colors bg-slate-900/40">
                <input type="file" name="surat_kuasa_file" accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                <div class="flex flex-col items-center justify-center pointer-events-none">
                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 mb-2">
                        <i class="fas fa-cloud-upload-alt text-lg"></i>
                    </div>
                    <p class="text-xs text-slate-300 font-semibold mb-1">
                        {{ $existingFilePath ? 'Klik untuk mengganti dokumen yang tersimpan' : 'Pilih file dokumen atau seret ke area ini' }}
                    </p>
                    <p class="text-[11px] text-slate-500">Format didukung: PDF, DOC, DOCX, ZIP, JPG, PNG (Maksimal 10MB)</p>
                </div>
            </div>
            @if(isset($errors) && $errors->has('surat_kuasa_file'))
                <p class="text-red-400 text-xs mt-1.5">{{ $errors->first('surat_kuasa_file') }}</p>
            @endif
        </div>
    </div>
</div>
