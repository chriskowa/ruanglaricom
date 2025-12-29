@extends('layouts.pacerhub')

@section('content')
    <div id="register-app" class="min-h-screen pt-28 pb-20 px-4 relative overflow-hidden">
        <!-- Background Accents -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-20 left-10 w-72 h-72 bg-neon/10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-blue-500/10 rounded-full blur-[100px]"></div>
        </div>

        <div class="max-w-4xl mx-auto relative z-10">
            <div class="text-center mb-12" data-aos="fade-down">
                <h1 class="text-4xl md:text-5xl font-black text-white mb-4 uppercase italic tracking-tighter">
                    JOIN THE <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400 pr-2">PACER TEAM</span>
                </h1>
                <p class="text-slate-400 text-lg max-w-2xl mx-auto">
                    Jadilah bagian dari komunitas elite, bantu pelari lain mencapai target mereka, dan inspirasi ribuan orang di setiap langkah.
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

            <form method="POST" action="{{ route('pacer.register.store') }}" enctype="multipart/form-data" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-3xl p-8 md:p-10 shadow-2xl relative overflow-hidden group">
                @csrf
                
                <!-- Glow Effect on Hover -->
                <div class="absolute -inset-0.5 bg-gradient-to-r from-neon to-blue-500 opacity-0 group-hover:opacity-10 transition duration-1000 rounded-3xl blur pointer-events-none"></div>

                <div class="relative z-10 space-y-8">
                    
                    <!-- Section: Personal Info -->
                    <div>
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-neon border border-slate-700">1</span>
                            Personal Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Full Name</label>
                                <input name="name" value="{{ old('name') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600" placeholder="John Doe" required />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Email Address</label>
                                <input name="email" type="email" value="{{ old('email') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600" placeholder="john@example.com" required />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Phone Number</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3.5 text-slate-500 text-sm font-mono">+62</span>
                                    <input name="phone" value="{{ old('phone') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 pl-12 pr-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600 font-mono" placeholder="81234567890" required />
                                </div>
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">WhatsApp <span class="text-[10px] normal-case opacity-50 ml-1">(Optional)</span></label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3.5 text-slate-500 text-sm font-mono">+62</span>
                                    <input name="whatsapp" value="{{ old('whatsapp') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 pl-12 pr-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600 font-mono" placeholder="81234567890" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full h-px bg-slate-700/50"></div>

                    <!-- Section: Social Media -->
                    <div>
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-neon border border-slate-700">2</span>
                            Social Media
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Instagram URL</label>
                                <input name="instagram_url" value="{{ old('instagram_url') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600" placeholder="https://instagram.com/username" />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Facebook URL</label>
                                <input name="facebook_url" value="{{ old('facebook_url') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600" placeholder="https://facebook.com/username" />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">TikTok URL</label>
                                <input name="tiktok_url" value="{{ old('tiktok_url') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600" placeholder="https://tiktok.com/@username" />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Strava URL</label>
                                <input name="strava_url" value="{{ old('strava_url') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600" placeholder="https://strava.com/athletes/12345" />
                            </div>
                        </div>
                    </div>

                    <div class="w-full h-px bg-slate-700/50"></div>

                    <!-- Section: Running Profile -->
                    <div>
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-neon border border-slate-700">3</span>
                            Running Profile
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Target Category</label>
                                <div class="relative">
                                    <select name="category" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all appearance-none cursor-pointer">
                                        <option value="10K" {{ old('category') == '10K' ? 'selected' : '' }}>10K - Ten Kilometers</option>
                                        <option value="HM (21K)" {{ old('category') == 'HM (21K)' ? 'selected' : '' }}>HM - Half Marathon (21K)</option>
                                        <option value="FM (42K)" {{ old('category') == 'FM (42K)' ? 'selected' : '' }}>FM - Full Marathon (42K)</option>
                                    </select>
                                    <svg class="w-4 h-4 text-slate-400 absolute right-4 top-4 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Pace Strategy</label>
                                <input name="pace" value="{{ old('pace') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600 font-mono" placeholder="06:00" required />
                                <p class="text-[10px] text-slate-500 mt-1">Format: MM:SS per km</p>
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Nickname <span class="text-[10px] normal-case opacity-50 ml-1">(Optional)</span></label>
                                <input name="nickname" value="{{ old('nickname') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600" placeholder="Speedy" />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Specialty Tags</label>
                                <input name="tags" value="{{ old('tags') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600" placeholder="Consistent, Motivator, Fun" />
                            </div>
                        </div>
                    </div>

                    <!-- Section: Personal Bests -->
                    <div>
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-neon border border-slate-700">4</span>
                            Personal Bests (PB)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">5K PB</label>
                                <input name="pb5k" value="{{ old('pb5k') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600 font-mono" placeholder="00:25:00" />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">10K PB</label>
                                <input name="pb10k" value="{{ old('pb10k') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600 font-mono" placeholder="00:55:00" />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Half Marathon PB</label>
                                <input name="pbhm" value="{{ old('pbhm') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600 font-mono" placeholder="02:00:00" />
                            </div>
                            <div class="group/input">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Full Marathon PB</label>
                                <input name="pbfm" value="{{ old('pbfm') }}" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600 font-mono" placeholder="04:30:00" />
                            </div>
                        </div>
                    </div>

                    <div class="w-full h-px bg-slate-700/50"></div>

                    <!-- Section: Race Portfolio -->
                    <div>
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-neon border border-slate-700">5</span>
                            Race Portfolio
                        </h3>
                        <div class="group/input">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Daftar Race yang Pernah Diikuti</label>
                            <textarea name="race_portfolio" rows="3" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600 resize-none" placeholder="Contoh: Jakarta Marathon, Borobudur Marathon, Bali Marathon, 10K Kota Bandung">{{ old('race_portfolio') }}</textarea>
                            <p class="text-[10px] text-slate-500 mt-1">Pisahkan dengan koma.</p>
                        </div>
                    </div>

                    <!-- Section: Bio & Photo -->
                    <div>
                        <div class="group/input mb-6">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 group-focus-within/input:text-neon transition-colors">Short Bio / Motivation</label>
                            <textarea name="bio" rows="4" class="w-full bg-slate-900/50 text-white rounded-xl border border-slate-700 px-4 py-3 focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600 resize-none" placeholder="Tell us why you want to be a pacer...">{{ old('bio') }}</textarea>
                        </div>

                        <!-- Drag & Drop Upload -->
                        <div class="group/input">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Profile Photo</label>
                            
                            <div 
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                                :class="{'border-neon bg-neon/5': isDragging, 'border-slate-700 bg-slate-900/30': !isDragging}"
                                class="relative w-full border-2 border-dashed rounded-2xl p-8 transition-all duration-300 flex flex-col items-center justify-center text-center cursor-pointer hover:border-neon/50 hover:bg-slate-800"
                            >
                                <input type="file" name="image" ref="fileInput" @change="handleFileSelect" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                
                                <div v-if="!previewUrl" class="pointer-events-none">
                                    <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-4 text-slate-400 group-hover:text-neon transition-colors">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <p class="text-white font-bold mb-1">Click or drag image here</p>
                                    <p class="text-xs text-slate-500">Max size 1MB. JPG, PNG supported.</p>
                                </div>

                                <div v-else class="relative z-20">
                                    <div class="w-32 h-32 rounded-full border-4 border-slate-800 shadow-xl overflow-hidden mx-auto mb-4 relative group/preview">
                                        <img :src="previewUrl" class="w-full h-full object-cover" />
                                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover/preview:opacity-100 transition-opacity">
                                            <p class="text-xs text-white font-bold">Change</p>
                                        </div>
                                    </div>
                                    <p class="text-neon text-sm font-bold truncate max-w-[200px]">@{{ fileName }}</p>
                                    <button type="button" @click.prevent="removeFile" class="text-xs text-red-400 hover:text-red-300 underline mt-2 relative z-30">Remove photo</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-10 pt-6 border-t border-slate-700/50 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-500 text-center md:text-left">
                        By registering, you agree to our <a href="#" class="text-neon hover:underline">Terms of Service</a> & <a href="#" class="text-neon hover:underline">Privacy Policy</a>.
                    </p>
                    <div class="flex gap-4 w-full md:w-auto">
                        <a href="{{ route('pacer.index') }}" class="px-6 py-3 rounded-xl border border-slate-600 text-slate-300 hover:text-white hover:bg-white/5 font-bold transition w-full md:w-auto text-center">Cancel</a>
                        <button type="submit" class="px-8 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 hover:scale-105 transition transform shadow-[0_0_20px_rgba(204,255,0,0.3)] w-full md:w-auto">
                            SUBMIT APPLICATION
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
                    alert('File size too large. Max 1MB.');
                    this.removeFile();
                    return;
                }

                // Validate type
                if (!file.type.startsWith('image/')) {
                    alert('Please upload an image file.');
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
    }).mount('#register-app');
</script>
@endpush
