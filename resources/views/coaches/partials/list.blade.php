@if($coaches->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($coaches as $coach)
            <div class="group bg-card hover:bg-slate-800 border border-slate-800 hover:border-neon/50 rounded-3xl overflow-hidden transition-all duration-300 relative" data-aos="fade-up">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-100 transition-opacity">
                    <div class="w-24 h-24 bg-neon blur-[60px] rounded-full"></div>
                </div>
                
                <div class="p-6 flex flex-col h-full relative z-10">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <img src="{{ $coach->avatar ? asset('storage/'.$coach->avatar) : asset('images/profile/default.png') }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-700 group-hover:border-neon transition-colors" alt="{{ $coach->name }}">
                            <div>
                                <h3 class="text-lg font-bold text-white group-hover:text-neon transition-colors">{{ $coach->name }}</h3>
                                <p class="text-sm text-slate-400 flex items-center gap-1">
                                    <i class="fas fa-map-marker-alt text-xs"></i> 
                                    {{ $coach->city->name ?? 'Indonesia' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <div class="flex items-center gap-1 text-yellow-400 mb-1">
                                <span class="font-black text-lg">{{ number_format($coach->programs_average_rating ?? 0, 1) }}</span>
                                <i class="fas fa-star text-sm"></i>
                            </div>
                            <span class="text-xs text-slate-500">{{ $coach->programs_count }} Programs</span>
                        </div>
                    </div>

                    <div class="mb-6 flex-grow">
                        <div class="flex flex-wrap gap-2">
                            @php
                                // Fake tags for now or use specialization if available
                                $tags = ['Marathon', '5K/10K', 'Strength']; 
                            @endphp
                            @foreach($tags as $tag)
                                <span class="px-3 py-1 rounded-full bg-slate-900 border border-slate-700 text-xs text-slate-300 group-hover:border-slate-500 transition-colors">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                        @if($coach->bio)
                            <p class="text-sm text-slate-400 mt-4 line-clamp-2">{{ Str::limit($coach->bio, 100) }}</p>
                        @endif
                    </div>

                    <div class="pt-6 border-t border-slate-800">
                        <a href="{{ route('runner.profile.show', $coach->username ?? $coach->id) }}" class="block w-full py-3 rounded-xl bg-slate-900 text-white font-bold text-center border border-slate-700 hover:bg-neon hover:text-dark hover:border-neon transition-all duration-300">
                            View Profile
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="mt-12 pagination-container">
        {{ $coaches->links('pagination::tailwind') }}
    </div>
@else
    <div class="text-center py-20 bg-card/30 rounded-3xl border border-slate-800 border-dashed">
        <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-500">
            <i class="fas fa-user-slash text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Tidak ada Coach ditemukan</h3>
        <p class="text-slate-400">Coba ubah filter atau kata kunci pencarian Anda.</p>
        <button type="button" onclick="resetFilters()" class="inline-block mt-6 px-6 py-2 rounded-xl bg-neon text-dark font-bold hover:bg-white transition-colors">
            Reset Filter
        </button>
    </div>
@endif
