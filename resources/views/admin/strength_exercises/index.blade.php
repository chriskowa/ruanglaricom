@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Manajemen Master Strength Exercises')

@section('content')
<div id="admin-strength-app" class="min-h-screen pt-20 pb-12 px-4 md:px-8 relative overflow-hidden font-sans">

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center justify-between gap-3 relative z-10">
            <div class="flex items-center gap-2 text-xs font-bold">
                <i class="fa-solid fa-circle-check text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">✕</button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="mb-8 relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 text-brand-400 text-xs font-bold tracking-wider uppercase mb-1">
                <i class="fa-solid fa-dumbbell"></i>
                <span>Master Data Latihan</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                Strength Exercises Library
            </h1>
            <p class="text-xs text-slate-400 mt-1">Kelola master gerakan latihan kekuatan, media visual (GIF/Video/Foto), dan petunjuk form untuk pelari.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.strength-exercises.seed') }}" method="POST" onsubmit="return confirm('Muat master data gerakan awal ke database?');">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:border-slate-600 font-bold text-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-database text-xs text-brand-400"></i>
                    <span>Load Master Data Awal</span>
                </button>
            </form>
            <button onclick="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-dark font-black text-xs uppercase tracking-wider transition shadow-lg shadow-brand-500/20 flex items-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Gerakan</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 mb-6 relative z-10">
        <form method="GET" action="{{ route('admin.strength-exercises.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Kategori</label>
                <select name="category" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs font-bold text-white focus:border-brand-500 outline-none">
                    <option value="">Semua Kategori</option>
                    <option value="legs_lower_body" {{ request('category') == 'legs_lower_body' ? 'selected' : '' }}>Legs / Lower Body</option>
                    <option value="core" {{ request('category') == 'core' ? 'selected' : '' }}>Core / Abdominals</option>
                    <option value="full_body" {{ request('category') == 'full_body' ? 'selected' : '' }}>Full Body</option>
                    <option value="upper_body" {{ request('category') == 'upper_body' ? 'selected' : '' }}>Upper Body</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Pencarian</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama gerakan, alat, atau otot..." class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-3.5 py-2 text-xs text-white focus:border-brand-500 outline-none">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-500 text-xs"></i>
                </div>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter text-xs text-slate-400"></i>
                    <span>Filter</span>
                </button>
                @if(request()->anyFilled(['category', 'search']))
                    <a href="{{ route('admin.strength-exercises.index') }}" class="py-2 px-3 bg-slate-800/60 hover:bg-slate-800 text-slate-400 hover:text-white font-bold text-xs rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Exercises Table -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden shadow-xl relative z-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-950 text-slate-400 text-[10px] uppercase tracking-wider border-b border-slate-800">
                        <th class="py-3.5 px-4 font-extrabold">Gerakan</th>
                        <th class="py-3.5 px-4 font-extrabold">Kategori</th>
                        <th class="py-3.5 px-4 font-extrabold">Peralatan</th>
                        <th class="py-3.5 px-4 font-extrabold">Sets & Reps</th>
                        <th class="py-3.5 px-4 font-extrabold">Media Preview</th>
                        <th class="py-3.5 px-4 font-extrabold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @if($exercises->count() > 0)
                        @foreach($exercises as $ex)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 px-4 font-bold text-white">
                                    <div>{{ $ex->name }}</div>
                                    @if($ex->target_muscles)
                                        <div class="text-[10px] font-normal text-slate-400 mt-0.5">Otot: {{ $ex->target_muscles }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($ex->category == 'legs_lower_body')
                                        <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider bg-blue-500/10 text-blue-400 border-blue-500/20">Legs / Lower Body</span>
                                    @elseif($ex->category == 'core')
                                        <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border-emerald-500/20">Core</span>
                                    @elseif($ex->category == 'full_body')
                                        <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider bg-purple-500/10 text-purple-400 border-purple-500/20">Full Body</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-400 border-cyan-500/20">Upper Body</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-300">
                                    {{ $ex->equipment ?: 'Bodyweight' }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-200">
                                    {{ $ex->default_sets }} Set × {{ $ex->default_reps }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($ex->media_url)
                                        <div class="flex items-center gap-2">
                                            @if(in_array($ex->media_type, ['image', 'gif']))
                                                <img src="{{ $ex->media_url }}" alt="{{ $ex->name }}" class="w-10 h-10 object-cover rounded-lg border border-slate-700 bg-slate-950">
                                            @elseif($ex->media_type === 'video')
                                                <video src="{{ $ex->media_url }}" autoplay loop muted class="w-10 h-10 object-cover rounded-lg border border-slate-700 bg-slate-950"></video>
                                            @else
                                                <a href="{{ $ex->media_url }}" target="_blank" class="text-xs text-brand-400 hover:underline flex items-center gap-1">
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Media URL
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-500 italic">Belum Ada Media</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" data-exercise='@json($ex)' onclick="openEditModalFromData(this)" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </button>
                                        <form action="{{ route('admin.strength-exercises.destroy', $ex->id) }}" method="POST" onsubmit="return confirm('Hapus gerakan ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-slate-800 hover:bg-red-950 text-slate-400 hover:text-red-400 transition">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                <div class="space-y-2">
                                    <i class="fa-solid fa-dumbbell text-2xl text-slate-600"></i>
                                    <p class="text-xs font-bold">Belum ada master data gerakan Strength Training.</p>
                                    <form action="{{ route('admin.strength-exercises.seed') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="mt-2 px-3 py-1.5 bg-brand-500 text-dark font-black text-xs rounded-lg uppercase tracking-wider">
                                            Muat Data Standar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if($exercises->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $exercises->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Modal -->
    <div id="exerciseFormModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeFormModal()"></div>
        <div class="relative z-10 max-w-xl mx-auto my-10 p-6 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl space-y-5">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/10 border border-brand-500/30 flex items-center justify-center text-brand-400 text-sm">
                        <i class="fa-solid fa-dumbbell"></i>
                    </div>
                    <div>
                        <h3 id="modalTitle" class="text-sm font-black text-white uppercase tracking-tight">Tambah Gerakan Strength</h3>
                        <p class="text-[10px] text-slate-400">Isi detail master gerakan dan media visual</p>
                    </div>
                </div>
                <button type="button" onclick="closeFormModal()" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center text-xs transition">✕</button>
            </div>

            <!-- Form -->
            <form id="exerciseForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Nama Gerakan <span class="text-brand-400">*</span></label>
                    <input type="text" id="inputName" name="name" required class="w-full bg-slate-950 border border-slate-800 focus:border-brand-500 rounded-xl px-3.5 py-2.5 text-xs font-bold text-white placeholder-slate-600 outline-none transition" placeholder="Contoh: Barbell Squats">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Kategori <span class="text-brand-400">*</span></label>
                        <select id="selectCategory" name="category" required class="w-full bg-slate-950 border border-slate-800 focus:border-brand-500 rounded-xl px-3.5 py-2.5 text-xs font-bold text-white cursor-pointer outline-none transition">
                            <option value="legs_lower_body">Legs / Lower Body</option>
                            <option value="core">Core / Abdominals</option>
                            <option value="full_body">Full Body</option>
                            <option value="upper_body">Upper Body</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Peralatan (Equipment)</label>
                        <input type="text" id="inputEquipment" name="equipment" class="w-full bg-slate-950 border border-slate-800 focus:border-brand-500 rounded-xl px-3.5 py-2.5 text-xs font-bold text-white placeholder-slate-600 outline-none transition" placeholder="Bodyweight, Dumbbell, Barbell">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Default Sets</label>
                        <input type="text" id="inputSets" name="default_sets" class="w-full bg-slate-950 border border-slate-800 focus:border-brand-500 rounded-xl px-3.5 py-2.5 text-xs font-bold text-white placeholder-slate-600 outline-none transition" placeholder="Contoh: 3">
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Default Reps / Durasi</label>
                        <input type="text" id="inputReps" name="default_reps" class="w-full bg-slate-950 border border-slate-800 focus:border-brand-500 rounded-xl px-3.5 py-2.5 text-xs font-bold text-white placeholder-slate-600 outline-none transition" placeholder="Contoh: 10-12 reps atau 45-60s">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Target Otot</label>
                    <input type="text" id="inputMuscles" name="target_muscles" class="w-full bg-slate-950 border border-slate-800 focus:border-brand-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-600 outline-none transition" placeholder="Contoh: Quad, Glutes, Core">
                </div>

                <div class="p-3.5 bg-slate-950/60 rounded-xl border border-slate-800 space-y-2">
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Media Visual (GIF / Video MP4 / Foto)</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <select id="selectMediaType" name="media_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-white cursor-pointer outline-none">
                                <option value="gif">GIF Animasi</option>
                                <option value="video">Video MP4/WebM</option>
                                <option value="image">Foto WebP/PNG</option>
                                <option value="url">External URL</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <input type="text" id="inputMediaUrl" name="media_url" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-600 outline-none" placeholder="URL Media External (Opsional)">
                        </div>
                    </div>
                    <div class="pt-1">
                        <input type="file" name="media_file" class="block w-full text-[11px] text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-slate-800 file:text-brand-400 hover:file:bg-slate-700 cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Instruksi Form & Panduan Langkah</label>
                    <textarea id="inputInstructions" name="instructions" rows="3" class="w-full bg-slate-950 border border-slate-800 focus:border-brand-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-600 outline-none transition leading-relaxed" placeholder="Berikan petunjuk posisi tubuh, instruksi gerak, dan tips keamanan..."></textarea>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                    <button type="button" onclick="closeFormModal()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-black text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-brand-500/20 flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Gerakan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Gerakan Strength Baru';
        document.getElementById('exerciseForm').action = "{{ route('admin.strength-exercises.store') }}";
        document.getElementById('formMethod').value = "POST";
        document.getElementById('inputName').value = '';
        document.getElementById('inputEquipment').value = 'Bodyweight';
        document.getElementById('inputSets').value = '3';
        document.getElementById('inputReps').value = '10-12 reps';
        document.getElementById('inputMuscles').value = '';
        document.getElementById('inputMediaUrl').value = '';
        document.getElementById('inputInstructions').value = '';
        document.getElementById('exerciseFormModal').classList.remove('hidden');
    }

    function openEditModalFromData(btn) {
        try {
            const ex = JSON.parse(btn.getAttribute('data-exercise'));
            document.getElementById('modalTitle').innerText = 'Edit Gerakan: ' + ex.name;
            document.getElementById('exerciseForm').action = "/admin/strength-exercises/" + ex.id;
            document.getElementById('formMethod').value = "PUT";
            document.getElementById('inputName').value = ex.name || '';
            document.getElementById('selectCategory').value = ex.category || 'legs_lower_body';
            document.getElementById('inputEquipment').value = ex.equipment || '';
            document.getElementById('inputSets').value = ex.default_sets || '3';
            document.getElementById('inputReps').value = ex.default_reps || '10-12 reps';
            document.getElementById('inputMuscles').value = ex.target_muscles || '';
            document.getElementById('selectMediaType').value = ex.media_type || 'gif';
            document.getElementById('inputMediaUrl').value = ex.media_url || '';
            document.getElementById('inputInstructions').value = ex.instructions || '';
            document.getElementById('exerciseFormModal').classList.remove('hidden');
        } catch(e) {
            console.error(e);
        }
    }

    function closeFormModal() {
        document.getElementById('exerciseFormModal').classList.add('hidden');
    }
</script>
@endsection
