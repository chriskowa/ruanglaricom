@extends('layouts.pacerhub')

@section('title', 'Setor Aktivitas - Ruang Lari')

@push('styles')
    <style>
        .bg-fixed-image {
            background-image: url('https://res.cloudinary.com/dslfarxct/images/v1760944069/pelari-kece/pelari-kece.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        /* Custom File Input */
        input[type="file"]::file-selector-button {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div id="submit-app" class="relative z-10 w-full min-h-screen flex items-center justify-center p-4 pt-20">
        
        <div class="fixed inset-0 z-[-1] bg-fixed-image"></div>
        <div class="fixed inset-0 z-[-1] bg-slate-900/80"></div>

        <div class="glass-panel w-full max-w-md rounded-2xl p-6 shadow-2xl relative overflow-hidden">
            
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-white">Setor Lari 🏃‍♂️</h2>
                    <p class="text-xs text-gray-400">40 Days Challenge</p>
                </div>
            </div>

            <form @submit.prevent="submitActivity" class="space-y-4">
                
                <div>
                    <label class="block text-xs text-gray-400 mb-1 ml-1">Tanggal Lari</label>
                    <div class="relative">
                        <input type="date" v-model="form.date" required
                            class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-green-500 transition-colors text-white placeholder-gray-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-1">Jarak (KM)</label>
                        <div class="relative">
                            <input type="number" step="0.01" v-model="form.distance" placeholder="0.00" required
                                class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-green-500 transition-colors text-white">
                            <span class="absolute right-4 top-3 text-xs text-gray-500 font-bold">KM</span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-1">Durasi</label>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="relative">
                                <input type="number" v-model="form.duration_hours" placeholder="0" min="0"
                                    class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-2 py-3 text-center text-sm focus:outline-none focus:border-green-500 transition-colors text-white">
                                <span class="text-[10px] text-gray-500 absolute bottom-1 left-0 right-0 text-center">Jam</span>
                            </div>
                            <div class="relative">
                                <input type="number" v-model="form.duration_minutes" placeholder="0" min="0" max="59"
                                    class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-2 py-3 text-center text-sm focus:outline-none focus:border-green-500 transition-colors text-white">
                                <span class="text-[10px] text-gray-500 absolute bottom-1 left-0 right-0 text-center">Menit</span>
                            </div>
                            <div class="relative">
                                <input type="number" v-model="form.duration_seconds" placeholder="0" min="0" max="59"
                                    class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-2 py-3 text-center text-sm focus:outline-none focus:border-green-500 transition-colors text-white">
                                <span class="text-[10px] text-gray-500 absolute bottom-1 left-0 right-0 text-center">Detik</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1 ml-1">Screenshot Aplikasi Lari / Foto Jam</label>
                    <div class="relative group cursor-pointer">
                        <input type="file" @change="handleFileUpload" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required>
                        
                        <div class="border-2 border-dashed border-slate-600 rounded-xl p-6 text-center group-hover:border-green-500 group-hover:bg-slate-800/50 transition-all">
                            <div v-if="!previewImage">
                                <i class="fas fa-cloud-upload-alt text-2xl text-gray-500 mb-2 group-hover:text-green-400"></i>
                                <p class="text-xs text-gray-400">Tap untuk upload bukti</p>
                            </div>
                            <div v-else class="relative">
                                <img :src="previewImage" class="h-32 mx-auto rounded-lg object-cover shadow-lg">
                                <p class="text-[10px] text-green-400 mt-2"><i class="fas fa-check-circle"></i> Foto siap diupload</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1 ml-1">Link Activity Strava (Opsional)</label>
                    <div class="relative">
                        <i class="fab fa-strava absolute left-4 top-3.5 text-orange-500"></i>
                        <input type="url" v-model="form.stravaLink" placeholder="https://strava.com/activities/..."
                            class="w-full bg-slate-800/50 border border-slate-600 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-orange-500 transition-colors text-white">
                    </div>
                </div>

                <div v-if="message" :class="messageType === 'success' ? 'text-green-400' : 'text-red-400'" class="text-xs text-center font-bold">
                    @{{ message }}
                </div>

                <button type="submit" :disabled="isSubmitting"
                    class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-green-900/20 transform active:scale-95 transition-all flex items-center justify-center gap-2 mt-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span v-if="!isSubmitting">Kirim Laporan <i class="fas fa-paper-plane text-xs"></i></span>
                    <span v-else><i class="fas fa-circle-notch fa-spin"></i> Mengirim...</span>
                </button>

            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('runner.calendar') }}" class="text-xs text-gray-500 hover:text-white transition-colors">Kembali ke Calendar</a>
            </div>
        </div>

        <!-- History Section -->
        <div class="glass-panel w-full max-w-md rounded-2xl p-6 shadow-2xl relative overflow-hidden mt-6 md:mt-0 md:ml-6 h-fit">
            <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Riwayat Setoran</h3>
            <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                @forelse($activities as $activity)
                    <div class="bg-slate-800/50 rounded-lg p-3 border border-slate-700 relative">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-sm font-bold text-white">{{ \Carbon\Carbon::parse($activity->date)->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $activity->distance }} KM • {{ gmdate('H:i:s', $activity->duration_seconds) }}
                                </div>
                            </div>
                            <div class="text-right">
                                @if($activity->status == 'approved')
                                    <span class="inline-block px-2 py-0.5 bg-green-500/20 text-green-400 text-[10px] font-bold rounded-full border border-green-500/30">
                                        <i class="fas fa-check"></i> Diterima
                                    </span>
                                @elseif($activity->status == 'rejected')
                                    <span class="inline-block px-2 py-0.5 bg-red-500/20 text-red-400 text-[10px] font-bold rounded-full border border-red-500/30">
                                        <i class="fas fa-times"></i> Ditolak
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-[10px] font-bold rounded-full border border-yellow-500/30">
                                        <i class="fas fa-clock"></i> Menunggu
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($activity->status == 'rejected' && $activity->rejection_reason)
                            <div class="mt-2 text-[10px] text-red-300 bg-red-900/30 p-2 rounded border border-red-800/50">
                                <strong>Alasan:</strong> {{ $activity->rejection_reason }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-gray-500 text-xs py-4">
                        Belum ada riwayat setoran.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

@push('scripts')
    <script>
        const { createApp, ref } = Vue;

        createApp({
            setup() {
                const isSubmitting = ref(false);
                const previewImage = ref(null);
                const message = ref('');
                const messageType = ref('success');
                const form = ref({
                    date: new Date().toISOString().substr(0, 10),
                    distance: '',
                    duration_hours: 0,
                    duration_minutes: 0,
                    duration_seconds: 0,
                    stravaLink: '',
                    image: null
                });

                const handleFileUpload = (event) => {
                    const file = event.target.files[0];
                    if (file) {
                        form.value.image = file;
                        // Create preview URL
                        previewImage.value = URL.createObjectURL(file);
                    }
                };

                const submitActivity = async () => {
                    // Calculate total seconds to verify duration > 0
                    const totalSeconds = (parseInt(form.value.duration_hours || 0) * 3600) + 
                                       (parseInt(form.value.duration_minutes || 0) * 60) + 
                                       parseInt(form.value.duration_seconds || 0);

                    if(!form.value.distance || !form.value.image || totalSeconds <= 0) {
                        message.value = 'Mohon isi jarak, durasi, dan upload bukti foto!';
                        messageType.value = 'error';
                        return;
                    }

                    isSubmitting.value = true;
                    message.value = '';

                    const formData = new FormData();
                    formData.append('date', form.value.date);
                    formData.append('distance', form.value.distance);
                    formData.append('duration_hours', form.value.duration_hours || 0);
                    formData.append('duration_minutes', form.value.duration_minutes || 0);
                    formData.append('duration_seconds', form.value.duration_seconds || 0);
                    if(form.value.stravaLink) formData.append('strava_link', form.value.stravaLink);
                    formData.append('image', form.value.image);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    try {
                        const response = await fetch("{{ route('challenge.store') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            message.value = result.message;
                            messageType.value = 'success';
                            
                            // Redirect after short delay or reset
                            setTimeout(() => {
                                window.location.href = "{{ route('challenge.index') }}";
                            }, 1500);
                        } else {
                            message.value = result.message || 'Terjadi kesalahan saat mengirim data.';
                            messageType.value = 'error';
                        }
                    } catch (error) {
                        console.error(error);
                        message.value = 'Terjadi kesalahan jaringan atau server.';
                        messageType.value = 'error';
                    } finally {
                        isSubmitting.value = false;
                    }
                };

                return {
                    isSubmitting,
                    previewImage,
                    message,
                    messageType,
                    form,
                    handleFileUpload,
                    submitActivity
                }
            }
        }).mount('#submit-app');
    </script>
@endpush
@endsection
