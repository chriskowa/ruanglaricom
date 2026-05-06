@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Buat Email Blast')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('eo.blasts.index', ['event' => $event ? $event->id : null]) }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white font-bold">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>

        <div class="mb-8 relative z-10" data-aos="fade-up">
            <div class="text-neon font-mono text-xs tracking-widest uppercase">EO Panel</div>
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">BUAT EMAIL BLAST</h1>
            <div class="text-slate-400 text-sm mt-2">
                Custom HTML + placeholder dari CSV (contoh: <span class="font-mono text-slate-200">@{{name}}</span>, <span class="font-mono text-slate-200">@{{email}}</span>)
            </div>
        </div>

        <form action="{{ route('eo.blasts.store', ['event' => $event ? $event->id : null]) }}" method="POST" enctype="multipart/form-data" id="blastForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nama Blast</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl px-3 py-3 focus:border-neon focus:outline-none @error('name') border-red-500 @enderror" placeholder="Contoh: Sponsor Update Jan 2026" required>
                                @error('name')<div class="text-red-300 text-xs mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Target</label>
                                <div class="flex gap-4">
                                    <label class="inline-flex items-center gap-2 text-slate-200 text-sm">
                                        <input type="radio" name="source_type" id="sourceSingle" value="single" class="accent-neon" {{ old('source_type', 'single') == 'single' ? 'checked' : '' }}>
                                        Perorangan
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-slate-200 text-sm">
                                        <input type="radio" name="source_type" id="sourceCsv" value="csv" class="accent-neon" {{ old('source_type') == 'csv' ? 'checked' : '' }}>
                                        CSV List
                                    </label>
                                </div>
                            </div>

                            <div id="singleFields" class="{{ old('source_type', 'single') == 'single' ? '' : 'hidden' }} md:col-span-2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Emails (max 10)</label>
                                        <textarea name="to_emails" rows="4" class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl px-3 py-3 focus:border-neon focus:outline-none font-mono @error('to_emails') border-red-500 @enderror" placeholder="email1@domain.com, email2@domain.com&#10;email3@domain.com">{{ old('to_emails') }}</textarea>
                                        @error('to_emails')<div class="text-red-300 text-xs mt-1">{{ $message }}</div>@enderror
                                        <div class="text-slate-400 text-xs mt-1">Pisahkan dengan koma atau enter. Email tidak valid akan diabaikan.</div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">To Name (Opsional)</label>
                                        <input type="text" name="to_name" value="{{ old('to_name') }}" class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl px-3 py-3 focus:border-neon focus:outline-none @error('to_name') border-red-500 @enderror">
                                        @error('to_name')<div class="text-red-300 text-xs mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div id="csvFields" class="{{ old('source_type') == 'csv' ? '' : 'hidden' }} md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Upload CSV</label>
                                <input type="file" name="csv_file" id="csvFileInput" class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl px-3 py-3 focus:border-neon focus:outline-none @error('csv_file') border-red-500 @enderror" accept=".csv,.txt">
                                @error('csv_file')<div class="text-red-300 text-xs mt-1">{{ $message }}</div>@enderror
                                <div class="text-slate-400 text-xs mt-1">Max 10MB, harus ada header row.</div>

                                <div id="csvMappingSection" class="hidden mt-4 bg-slate-900/60 border border-slate-800 rounded-2xl p-4">
                                    <div class="text-white font-black tracking-tight mb-3">Mapping Kolom</div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Email Column</label>
                                            <select name="email_column" id="emailColumn" class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl px-3 py-3 focus:border-neon focus:outline-none"></select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Name Column (Opsional)</label>
                                            <select name="name_column" id="nameColumn" class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl px-3 py-3 focus:border-neon focus:outline-none">
                                                <option value="">-- None --</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-slate-300 text-sm font-bold">Sample Preview</div>
                                    <div class="mt-2 overflow-x-auto">
                                        <table class="min-w-full text-xs" id="csvPreviewTable">
                                            <thead class="bg-slate-900/80 text-slate-300"></thead>
                                            <tbody class="divide-y divide-slate-800"></tbody>
                                        </table>
                                    </div>
                                    <div class="text-slate-400 text-xs mt-2">
                                        Contoh placeholder: <span class="font-mono text-slate-200" id="placeholderExamples">@{{email}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6">
                        <div class="text-white font-black tracking-tight mb-4">Konten Email</div>

                        <div class="mb-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Subject</label>
                            <input type="text" name="subject_template" id="subjectTemplate" value="{{ old('subject_template') }}" class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl px-3 py-3 focus:border-neon focus:outline-none @error('subject_template') border-red-500 @enderror" required>
                            @error('subject_template')<div class="text-red-300 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">HTML Body</label>
                            <textarea name="html_template" id="htmlTemplate" rows="12" class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl px-3 py-3 focus:border-neon focus:outline-none font-mono @error('html_template') border-red-500 @enderror" placeholder="<h1>Hello @{{name}}</h1>..." required>{{ old('html_template') }}</textarea>
                            @error('html_template')<div class="text-red-300 text-xs mt-1">{{ $message }}</div>@enderror
                            <div class="text-slate-400 text-xs mt-1">Tulis raw HTML. Placeholder akan diisi dari payload (CSV row / input single).</div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 sticky top-24">
                        <div class="text-white font-black tracking-tight mb-4">Preview</div>

                        <div class="mb-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Preview Data Row</label>
                            <select id="previewRowSelect" class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl px-3 py-3 focus:border-neon focus:outline-none" disabled>
                                <option value="">Select CSV row...</option>
                            </select>
                        </div>

                        <button type="button" id="btnPreview" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 hover:text-white hover:bg-slate-700 transition font-bold text-sm">
                            <i class="fa-solid fa-eye"></i>
                            Generate Preview
                        </button>

                        <div class="mt-4 bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                            <div class="text-slate-400 text-xs font-black uppercase tracking-widest">Subject</div>
                            <div id="previewSubject" class="text-white font-bold mt-1 break-words">-</div>
                        </div>

                        <div class="mt-4 bg-slate-900/60 border border-slate-800 rounded-xl overflow-hidden">
                            <div class="px-3 py-2 bg-slate-900/80 border-b border-slate-800 text-slate-400 text-xs font-black uppercase tracking-widest">HTML Body</div>
                            <iframe id="previewIframe" class="w-full" style="height: 320px; border: none;"></iframe>
                        </div>

                        <button type="submit" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-neon text-black font-black hover:bg-neon/90 transition">
                            <i class="fa-solid fa-paper-plane"></i>
                            Queue Blast Email
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sourceSingle = document.getElementById('sourceSingle');
    const sourceCsv = document.getElementById('sourceCsv');
    const singleFields = document.getElementById('singleFields');
    const csvFields = document.getElementById('csvFields');
    
    // Toggle source fields
    function toggleSource() {
        if (sourceSingle.checked) {
            singleFields.classList.remove('hidden');
            csvFields.classList.add('hidden');
        } else {
            singleFields.classList.add('hidden');
            csvFields.classList.remove('hidden');
        }
    }
    
    sourceSingle.addEventListener('change', toggleSource);
    sourceCsv.addEventListener('change', toggleSource);

    // CSV Upload & Parse Header
    const csvFileInput = document.getElementById('csvFileInput');
    const csvMappingSection = document.getElementById('csvMappingSection');
    const emailColumn = document.getElementById('emailColumn');
    const nameColumn = document.getElementById('nameColumn');
    const csvPreviewTable = document.getElementById('csvPreviewTable');
    const placeholderExamples = document.getElementById('placeholderExamples');
    const previewRowSelect = document.getElementById('previewRowSelect');
    
    let sampleRowsData = [];

    csvFileInput.addEventListener('change', function(e) {
        if (!e.target.files.length) return;
        
        const formData = new FormData();
        formData.append('csv_file', e.target.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("eo.blasts.parse_csv") }}', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.headers && data.headers.length) {
                csvMappingSection.classList.remove('hidden');
                
                // Populate selects
                emailColumn.innerHTML = '';
                nameColumn.innerHTML = '<option value="">-- None --</option>';
                
                let emailFound = false;
                data.headers.forEach(h => {
                    const opt1 = new Option(h, h);
                    const opt2 = new Option(h, h);
                    
                    if (!emailFound && h.toLowerCase().includes('email')) {
                        opt1.selected = true;
                        emailFound = true;
                    }
                    
                    emailColumn.add(opt1);
                    nameColumn.add(opt2);
                });

                // Populate Examples
                placeholderExamples.innerHTML = data.headers.slice(0, 3).map(h => '{{' + h + '}}').join(', ') + '...';

                // Populate Table
                const thead = csvPreviewTable.querySelector('thead');
                const tbody = csvPreviewTable.querySelector('tbody');
                
                thead.innerHTML = '<tr>' + data.headers.map(h => `<th class="text-left px-3 py-2 font-black uppercase tracking-widest text-[10px]">${h}</th>`).join('') + '</tr>';
                
                tbody.innerHTML = '';
                sampleRowsData = data.sample_rows;
                data.sample_rows.forEach((row, i) => {
                    const tr = document.createElement('tr');
                    data.headers.forEach(h => {
                        tr.innerHTML += `<td class="px-3 py-2 text-slate-200">${row[h] || ''}</td>`;
                    });
                    tbody.appendChild(tr);
                });

                // Populate Preview Select
                previewRowSelect.disabled = false;
                previewRowSelect.innerHTML = '<option value="">Select CSV row for preview...</option>';
                data.sample_rows.forEach((row, i) => {
                    const email = row[emailColumn.value] || `Row ${i+1}`;
                    previewRowSelect.add(new Option(email, i));
                });
            }
        })
        .catch(err => {
            console.error('Error parsing CSV', err);
            alert('Failed to parse CSV file. Please make sure it is a valid CSV.');
        });
    });

    // Preview Generate
    const btnPreview = document.getElementById('btnPreview');
    const subjectTemplate = document.getElementById('subjectTemplate');
    const htmlTemplate = document.getElementById('htmlTemplate');
    const previewSubject = document.getElementById('previewSubject');
    const previewIframe = document.getElementById('previewIframe');

    btnPreview.addEventListener('click', function() {
        let payload = {};
        
        if (sourceCsv.checked && previewRowSelect.value !== '') {
            payload = sampleRowsData[previewRowSelect.value] || {};
        } else if (sourceSingle.checked) {
            const emailsRaw = (document.querySelector('textarea[name="to_emails"]')?.value || '');
            const tokens = emailsRaw.split(/[,\\r\\n]+/).map(s => s.trim()).filter(Boolean);
            const firstEmail = tokens.find(e => /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(e)) || '';
            payload = {
                email: firstEmail,
                name: document.querySelector('input[name="to_name"]').value
            };
        }

        fetch('{{ route("eo.blasts.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                subject_template: subjectTemplate.value,
                html_template: htmlTemplate.value,
                payload: payload
            })
        })
        .then(res => res.json())
        .then(data => {
            previewSubject.textContent = data.subject || '-';
            const doc = previewIframe.contentWindow.document;
            doc.open();
            doc.write(data.html);
            doc.close();
        });
    });
});
</script>
@endpush
@endsection
