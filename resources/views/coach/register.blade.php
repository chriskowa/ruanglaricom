@extends('layouts.pacerhub')

@section('content')
    <div id="register-coach-app" class="min-h-screen pt-28 pb-20 px-4 relative overflow-hidden font-sans">
        <!-- Background Accents -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-20 left-10 w-72 h-72 bg-cyan-500/10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-500/10 rounded-full blur-[100px]"></div>
        </div>

        <div class="max-w-4xl mx-auto relative z-10">
            <div class="text-center mb-12" data-aos="fade-down">
                <h1 class="text-4xl md:text-5xl font-black text-white mb-4 uppercase italic tracking-tighter">
                    BECOME A <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">PRO COACH</span>
                </h1>
                <p class="text-slate-400 text-lg max-w-2xl mx-auto">
                    Bergabunglah dengan ekosistem pelatih profesional. Kelola atlet, buat program latihan berbasis sains, dan tingkatkan karir kepelatihan Anda.
                </p>
            </div>

            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/40 text-red-300 p-6 rounded-2xl mb-8 backdrop-blur-sm animate-pulse">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('coach.register.store') }}" enctype="multipart/form-data" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-3xl p-8 md:p-10 shadow-2xl relative overflow-hidden group">
                @csrf
                
                <!-- Glow Effect on Hover -->
                <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-purple-500 opacity-0 group-hover:opacity-10 transition duration-1000 rounded-3xl blur pointer-events-none"></div>

                <div class="relative z-10 space-y-8">
                    
                    <!-- Section: Personal Info -->
                    <div>
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-cyan-400 border border-slate-700">1</span>
                            Identitas Pelatih
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-cyan-400 transition-colors">Nama Lengkap</label>
                                <input name="name" value="{{ old('name') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 outline-none transition-all placeholder-slate-600" placeholder="Nama Lengkap & Gelar" required />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-cyan-400 transition-colors">Email Profesional</label>
                                <input name="email" type="email" value="{{ old('email') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 outline-none transition-all placeholder-slate-600" placeholder="coach@example.com" required />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-cyan-400 transition-colors">Nomor Telepon / WhatsApp</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3.5 text-slate-500 text-sm font-mono">+62</span>
                                    <input name="phone" value="{{ old('phone') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 pl-12 pr-4 py-3 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 outline-none transition-all placeholder-slate-600 font-mono" placeholder="81234567890" required />
                                </div>
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-cyan-400 transition-colors">Domisili / Kota</label>
                                <input name="city" value="{{ old('city') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 outline-none transition-all placeholder-slate-600" placeholder="Jakarta Selatan" required />
                            </div>
                        </div>
                    </div>

                    <div class="w-full h-px bg-slate-700/50"></div>

                    <!-- Section: Professional Profile -->
                    <div>
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-purple-400 border border-slate-700">2</span>
                            Profil Profesional
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-purple-400 transition-colors">Spesialisasi Utama</label>
                                <div class="relative">
                                    <select name="specialization" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-purple-400 focus:ring-1 focus:ring-purple-400 outline-none transition-all appearance-none cursor-pointer">
                                        <option value="Road Running" {{ old('specialization') == 'Road Running' ? 'selected' : '' }}>Road Running (5K - Marathon)</option>
                                        <option value="Trail Running" {{ old('specialization') == 'Trail Running' ? 'selected' : '' }}>Trail & Ultra Running</option>
                                        <option value="Track & Field" {{ old('specialization') == 'Track & Field' ? 'selected' : '' }}>Track & Field (Sprint/Middle)</option>
                                        <option value="Strength & Conditioning" {{ old('specialization') == 'Strength & Conditioning' ? 'selected' : '' }}>Strength & Conditioning</option>
                                        <option value="Triathlon" {{ old('specialization') == 'Triathlon' ? 'selected' : '' }}>Triathlon</option>
                                    </select>
                                    <svg class="w-4 h-4 text-slate-400 absolute right-4 top-4 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-purple-400 transition-colors">Pengalaman Melatih (Tahun)</label>
                                <input name="experience_years" type="number" value="{{ old('experience_years') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-purple-400 focus:ring-1 focus:ring-purple-400 outline-none transition-all placeholder-slate-600" placeholder="Contoh: 5" required />
                            </div>
                            <div class="group/input md:col-span-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-purple-400 transition-colors">Sertifikasi & Lisensi</label>
                                <input name="certifications" value="{{ old('certifications') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-purple-400 focus:ring-1 focus:ring-purple-400 outline-none transition-all placeholder-slate-600" placeholder="Contoh: World Athletics Level 1, APKI Coach, dll." />
                                <p class="text-[10px] text-slate-500 mt-1">Pisahkan dengan koma jika lebih dari satu.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Bio & Photo -->
                    <div>
                        <div class="group/input mb-6">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-cyan-400 transition-colors">Filosofi Kepelatihan</label>
                            <textarea name="bio" rows="4" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 outline-none transition-all placeholder-slate-600 resize-none" placeholder="Ceritakan pendekatan Anda dalam melatih atlet (Contoh: Berbasis data, holistik, disiplin keras, dll)...">{{ old('bio') }}</textarea>
                        </div>

                        <!-- Drag & Drop Upload -->
                        <div class="group/input">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Foto Profil Profesional</label>
                            
                            <div 
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                                :class="{'border-cyan-500 bg-cyan-500/5': isDragging, 'border-slate-700 bg-slate-900/30': !isDragging}"
                                class="relative w-full border-2 border-dashed rounded-2xl p-8 transition-all duration-300 flex flex-col items-center justify-center text-center cursor-pointer hover:border-cyan-500/50 hover:bg-slate-800"
                            >
                                <input type="file" name="image" ref="fileInput" @change="handleFileSelect" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                
                                <div v-if="!previewUrl" class="pointer-events-none">
                                    <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-4 text-slate-400 group-hover:text-cyan-400 transition-colors">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <p class="text-white font-bold mb-1">Klik atau tarik foto ke sini</p>
                                    <p class="text-xs text-slate-500">Max 1MB. Gunakan foto formal/sporty yang jelas.</p>
                                </div>

                                <div v-else class="relative z-20">
                                    <div class="w-32 h-32 rounded-full border-4 border-slate-800 shadow-xl overflow-hidden mx-auto mb-4 relative group/preview">
                                        <img :src="previewUrl" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover/preview:opacity-100 transition-opacity">
                                            <p class="text-xs text-white font-bold">Ganti</p>
                                        </div>
                                    </div>
                                    <p class="text-cyan-400 text-sm font-bold truncate max-w-[200px]">@{{ fileName }}</p>
                                    <button type="button" @click.prevent="removeFile" class="text-xs text-red-400 hover:text-red-300 underline mt-2 relative z-30">Hapus foto</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-10 pt-6 border-t border-slate-700/50 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-500 text-center md:text-left">
                        Dengan mendaftar, Anda menyetujui <a href="#" class="text-cyan-400 hover:underline">Kode Etik Pelatih</a> & <a href="#" class="text-cyan-400 hover:underline">Ketentuan Layanan</a>.
                    </p>
                    <div class="flex gap-4 w-full md:w-auto">
                        <a href="{{ route('home') }}" class="px-6 py-3 rounded-xl border border-slate-600 text-slate-300 hover:text-white hover:bg-white/5 font-bold transition w-full md:w-auto text-center">Batal</a>
                        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-cyan-600 to-cyan-400 text-slate-900 font-black hover:from-cyan-500 hover:to-cyan-300 hover:scale-105 transition transform shadow-lg shadow-cyan-500/20 w-full md:w-auto">
                            DAFTAR SEKARANG
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                isDragging: false,
                previewUrl: null,
                fileName: null
            }
        },
        methods: {
            handleFileSelect(event) {
                const file = event.target.files[0];
                this.processFile(file);
            },
            handleDrop(event) {
                this.isDragging = false;
                const file = event.dataTransfer.files[0];
                if (file) {
                    this.$refs.fileInput.files = event.dataTransfer.files; // Update input file manually
                    this.processFile(file);
                }
            },
            processFile(file) {
                if (!file) return;
                
                // Validate size (1MB)
                if (file.size > 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 1MB.');
                    this.removeFile();
                    return;
                }

                // Validate type
                if (!file.type.startsWith('image/')) {
                    alert('Harap unggah file gambar.');
                    this.removeFile();
                    return;
                }

                this.fileName = file.name;
                
                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            removeFile() {
                this.previewUrl = null;
                this.fileName = null;
                this.$refs.fileInput.value = ''; // Reset input
            }
        }
    }).mount('#register-coach-app');
</script>
@endpush