@extends('layouts.pacerhub')

@section('title', 'Pendaftaran Coach')

@section('content')
    <div id="register-coach-app" class="min-h-screen pt-24 pb-16 px-4 font-sans">
        <div class="max-w-3xl mx-auto space-y-8">
            <div class="border-b border-slate-800 pb-6 text-center sm:text-left">
                <p class="text-xs text-slate-400 mb-1">Program Kemitraan Pelatih</p>
                <h1 class="text-2xl font-bold text-white">
                    Pendaftaran Pelatih / Coach
                </h1>
                <p class="text-xs text-slate-300 mt-1">
                    Bergabunglah dengan ekosistem pelatih RuangLari. Kelola atlet, publikasikan program latihan terstruktur, dan kembangkan karir kepelatihan Anda.
                </p>
            </div>

            @if ($errors->any())
                <div class="bg-red-900 border border-red-800 text-red-200 p-4 rounded-md text-xs font-medium">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('coach.register.store') }}" enctype="multipart/form-data" class="bg-slate-900 border border-slate-800 rounded-lg p-6 sm:p-8 space-y-8">
                @csrf

                <!-- Section: Personal Info -->
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-white border-b border-slate-800 pb-2">
                        1. Identitas Pelatih
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Nama Lengkap & Gelar <span class="text-red-400">*</span></label>
                            <input name="name" value="{{ old('name') }}" class="w-full bg-slate-950 text-white text-xs rounded-md border border-slate-800 px-3.5 py-2.5 focus:border-slate-600 outline-none placeholder-slate-400" placeholder="Nama Lengkap" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Email Profesional <span class="text-red-400">*</span></label>
                            <input name="email" type="email" value="{{ old('email') }}" class="w-full bg-slate-950 text-white text-xs rounded-md border border-slate-800 px-3.5 py-2.5 focus:border-slate-600 outline-none placeholder-slate-400" placeholder="coach@example.com" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Nomor Telepon / WhatsApp <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-400 text-xs font-mono">+62</span>
                                <input name="phone" value="{{ old('phone') }}" class="w-full bg-slate-950 text-white text-xs rounded-md border border-slate-800 pl-11 pr-3.5 py-2.5 focus:border-slate-600 outline-none placeholder-slate-400 font-mono" placeholder="81234567890" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Domisili / Kota <span class="text-red-400">*</span></label>
                            <input name="city" value="{{ old('city') }}" class="w-full bg-slate-950 text-white text-xs rounded-md border border-slate-800 px-3.5 py-2.5 focus:border-slate-600 outline-none placeholder-slate-400" placeholder="Jakarta Selatan" required />
                        </div>
                    </div>
                </div>

                <!-- Section: Professional Profile -->
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-white border-b border-slate-800 pb-2">
                        2. Profil Profesional
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Spesialisasi Utama <span class="text-red-400">*</span></label>
                            <select name="specialization" class="w-full bg-slate-950 text-white text-xs rounded-md border border-slate-800 px-3.5 py-2.5 focus:border-slate-600 outline-none cursor-pointer">
                                <option value="Road Running" {{ old('specialization') == 'Road Running' ? 'selected' : '' }}>Road Running (5K - Marathon)</option>
                                <option value="Trail Running" {{ old('specialization') == 'Trail Running' ? 'selected' : '' }}>Trail & Ultra Running</option>
                                <option value="Track & Field" {{ old('specialization') == 'Track & Field' ? 'selected' : '' }}>Track & Field (Sprint/Middle)</option>
                                <option value="Strength & Conditioning" {{ old('specialization') == 'Strength & Conditioning' ? 'selected' : '' }}>Strength & Conditioning</option>
                                <option value="Triathlon" {{ old('specialization') == 'Triathlon' ? 'selected' : '' }}>Triathlon</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Pengalaman Melatih (Tahun) <span class="text-red-400">*</span></label>
                            <input name="experience_years" type="number" value="{{ old('experience_years') }}" class="w-full bg-slate-950 text-white text-xs rounded-md border border-slate-800 px-3.5 py-2.5 focus:border-slate-600 outline-none placeholder-slate-400 font-mono" placeholder="Contoh: 5" required />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-300 mb-1">Sertifikasi & Lisensi</label>
                            <input name="certifications" value="{{ old('certifications') }}" class="w-full bg-slate-950 text-white text-xs rounded-md border border-slate-800 px-3.5 py-2.5 focus:border-slate-600 outline-none placeholder-slate-400" placeholder="Contoh: World Athletics Level 1, APKI Coach, dll." />
                            <p class="text-[11px] text-slate-400 mt-1">Pisahkan dengan koma jika lebih dari satu sertifikasi.</p>
                        </div>
                    </div>
                </div>

                <!-- Section: Bio & Photo -->
                <div class="space-y-4">
                    <h2 class="text-base font-semibold text-white border-b border-slate-800 pb-2">
                        3. Filosofi & Foto Profil
                    </h2>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Filosofi Kepelatihan</label>
                        <textarea name="bio" rows="4" class="w-full bg-slate-950 text-white text-xs rounded-md border border-slate-800 px-3.5 py-2.5 focus:border-slate-600 outline-none placeholder-slate-400 resize-none leading-relaxed" placeholder="Ceritakan pendekatan Anda dalam menyusun program dan mendampingi atlet...">{{ old('bio') }}</textarea>
                    </div>

                    <!-- Upload Foto -->
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Foto Profil Profesional</label>
                        
                        <div 
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleDrop"
                            :class="{'border-neon bg-slate-800': isDragging, 'border-slate-800 bg-slate-950': !isDragging}"
                            class="relative w-full border border-dashed rounded-lg p-6 transition flex flex-col items-center justify-center text-center cursor-pointer hover:border-slate-700"
                        >
                            <input type="file" name="image" ref="fileInput" @change="handleFileSelect" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                            
                            <div v-if="!previewUrl" class="pointer-events-none space-y-1">
                                <p class="text-xs text-white font-semibold">Pilih atau tarik foto profil ke area ini</p>
                                <p class="text-[11px] text-slate-400">Format JPG/PNG, maksimal 1MB.</p>
                            </div>

                            <div v-else class="relative z-20">
                                <div class="w-24 h-24 rounded-full border-2 border-slate-700 overflow-hidden mx-auto mb-3">
                                    <img :src="previewUrl" class="w-full h-full object-cover" />
                                </div>
                                <p class="text-xs text-white font-medium truncate max-w-[200px]">@{{ fileName }}</p>
                                <button type="button" @click.prevent="removeFile" class="text-xs text-red-400 hover:text-red-300 underline mt-1 relative z-30">Hapus foto</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-400 text-center sm:text-left">
                        Dengan mendaftar, Anda menyetujui Ketentuan Layanan & Kode Etik Pelatih RuangLari.
                    </p>
                    <div class="flex gap-2.5 w-full sm:w-auto justify-end">
                        <a href="{{ route('home') }}" class="px-4 py-2 rounded-md bg-slate-800 border border-slate-700 text-slate-300 hover:text-white text-xs font-medium transition text-center">Batal</a>
                        <button type="submit" class="px-5 py-2 rounded-md bg-neon text-dark font-bold text-xs hover:brightness-110 transition">
                            Daftar Sekarang
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