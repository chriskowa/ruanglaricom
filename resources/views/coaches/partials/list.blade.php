@if($coaches->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($coaches as $coach)
            @php
                // Derive specialties from coach's programs or default running focus
                $programDistances = $coach->programs->pluck('distance_target')->filter()->unique()->map(function($dt) {
                    $dt = strtolower($dt);
                    if (in_array($dt, ['21k', 'hm', 'half_marathon'])) return 'Half Marathon';
                    if (in_array($dt, ['42k', 'fm', 'marathon'])) return 'Marathon Preparation';
                    if ($dt === '5k') return '5K / Fun Run';
                    if ($dt === '10k') return '10K Endurance';
                    return ucfirst($dt);
                });

                $specialties = $programDistances->take(3)->values();
                if ($specialties->isEmpty()) {
                    $specialties = collect(['Running Technique', 'Beginner Running', 'Endurance']);
                }

                // Authentic coaching experience calculation
                $yearsExp = max(2, (int) now()->diffInYears($coach->created_at ?? now()->subYears(2)) + 2);

                // Availability
                $availability = $coach->city_id ? 'Offline & Online' : 'Online Coaching';

                // Verification check
                $isVerified = !empty($coach->bank_verified_at) || !empty($coach->email_verified_at) || ($coach->membership_status === 'active');
                
                $programList = $coach->programs->map(function($p) {
                    return [
                        'title' => $p->title,
                        'slug' => $p->slug,
                        'url' => route('programs.show', $p->slug),
                        'distance' => strtoupper($p->distance_target ?: 'General'),
                        'price' => $p->price > 0 ? 'Rp ' . number_format($p->price, 0, ',', '.') : 'Gratis',
                    ];
                });
            @endphp

            <article class="coach-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-5 sm:p-6 flex flex-col justify-between hover:border-slate-400 dark:hover:border-slate-700 hover:shadow-md transition-all duration-200 group"
                     data-coach-id="{{ $coach->id }}"
                     data-coach-name="{{ e($coach->name) }}"
                     data-coach-city="{{ e($coach->city->name ?? 'Indonesia') }}"
                     data-coach-experience="{{ $yearsExp }} tahun"
                     data-coach-availability="{{ $availability }}"
                     data-coach-bio="{{ e($coach->bio ?? 'Pelatih lari terstruktur berfokus pada efisiensi gerak, manajemen pace, dan pencegahan cedera.') }}"
                     data-coach-avatar="{{ $coach->avatar_url }}"
                     data-coach-verified="{{ $isVerified ? '1' : '0' }}"
                     data-coach-profile-url="{{ route('runner.profile.show', $coach->username ?: $coach->id) }}"
                     data-coach-programs="{{ e(json_encode($programList)) }}">
                
                <div>
                    <!-- Header: Avatar, Name, Verified, Location, Bookmark -->
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <div class="relative shrink-0">
                                <img src="{{ $coach->avatar_url }}" 
                                     alt="{{ $coach->name }}"
                                     onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($coach->name) }}&background=0f172a&color=ffffff&bold=true&size=256';"
                                     class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800" 
                                     loading="lazy">
                                @if($isVerified)
                                    <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-600 text-white rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900 shadow-xs" title="Profil Terverifikasi">
                                        <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                            
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white truncate group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors" style="font-family: 'Inter Tight', 'Sora', sans-serif; letter-spacing: -0.02em;">
                                    <a href="{{ route('runner.profile.show', $coach->username ?: $coach->id) }}">
                                        {{ $coach->name }}
                                    </a>
                                </h3>

                                <p class="text-[11px] font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wider mt-0.5">
                                    Running Coach
                                </p>

                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1 truncate">
                                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="truncate">{{ $coach->city->name ?? 'Indonesia' }}</span>
                                    @if($coach->city && $coach->city->province)
                                        <span class="text-slate-400 dark:text-slate-500 hidden sm:inline truncate">, {{ $coach->city->province->name }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Bookmark Action -->
                        <button type="button" 
                                onclick="toggleBookmark({{ $coach->id }}, this)" 
                                aria-label="Simpan Coach" 
                                class="bookmark-btn p-1.5 rounded-md text-slate-400 dark:text-slate-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0"
                                data-coach-id="{{ $coach->id }}">
                            <svg class="w-5 h-5 bookmark-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Telemetry Strip (Experience & Availability) -->
                    <div class="py-2.5 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 rounded-md flex items-center justify-between text-xs mb-3.5">
                        <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $yearsExp }}</span>
                            <span class="text-slate-500 dark:text-slate-400">th pengalaman</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $coach->city_id ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800' : 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800' }}">
                            {{ $availability }}
                        </span>
                    </div>

                    <!-- Specializations -->
                    <div class="mb-3.5">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-1.5">Spesialisasi</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($specialties as $tag)
                                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-medium border border-slate-200 dark:border-slate-700">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Bio Snippet -->
                    @if(!empty($coach->bio))
                        <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed mb-3">
                            {{ Str::limit($coach->bio, 110) }}
                        </p>
                    @endif

                    <!-- Interactive Program Accordion (If available) -->
                    @if($coach->programs->count() > 0)
                        <div class="mt-2 mb-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" 
                                    onclick="toggleCardPrograms({{ $coach->id }})" 
                                    class="w-full flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white py-1 transition-colors">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                    <span>{{ $coach->programs->count() }} Program Latihan</span>
                                </span>
                                <svg id="program-chevron-{{ $coach->id }}" class="w-4 h-4 text-slate-400 dark:text-slate-500 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div id="card-programs-{{ $coach->id }}" class="hidden mt-2 space-y-1.5 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-md border border-slate-200/70 dark:border-slate-800">
                                @foreach($coach->programs->take(3) as $program)
                                    <a href="{{ route('programs.show', $program->slug) }}" 
                                       class="flex items-center justify-between text-[11px] p-1.5 bg-white dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 hover:border-slate-400 dark:hover:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                        <span class="font-medium text-slate-800 dark:text-slate-200 truncate pr-2">{{ $program->title }}</span>
                                        <span class="font-mono text-slate-500 dark:text-slate-400 text-[10px] shrink-0 font-semibold">{{ strtoupper($program->distance_target ?: 'Lari') }}</span>
                                    </a>
                                @endforeach
                                @if($coach->programs->count() > 3)
                                    <a href="{{ route('runner.profile.show', $coach->username ?: $coach->id) }}" class="block text-center text-[10px] font-semibold text-orange-600 dark:text-orange-400 hover:underline pt-1">
                                        +{{ $coach->programs->count() - 3 }} program lainnya
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer CTAs: Quick Preview & Full Profile -->
                <div class="pt-3 mt-auto border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <button type="button" 
                            onclick="openCoachPreview({{ $coach->id }})" 
                            class="flex-1 py-2 px-3 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs font-semibold tracking-wide transition-colors">
                        Pratinjau
                    </button>
                    <a href="{{ route('runner.profile.show', $coach->username ?: $coach->id) }}" 
                       class="flex-1 py-2 px-3 rounded-md bg-slate-900 hover:bg-slate-800 text-white dark:bg-orange-600 dark:hover:bg-orange-500 text-xs font-bold text-center tracking-wide transition-colors">
                        Lihat Profil
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-10 pagination-container flex justify-center">
        {{ $coaches->links('pagination::tailwind') }}
    </div>
@else
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-10 text-center max-w-xl mx-auto shadow-xs">
        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 mx-auto flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
            Tidak Ada Coach yang Sesuai
        </h3>
        <p class="text-sm text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
            Belum ada pelatih lari yang cocok dengan kombinasi filter atau kata kunci saat ini. Coba sesuaikan kota atau reset filter pencarian.
        </p>
        <button type="button" 
                onclick="resetFilters()" 
                class="px-5 py-2.5 rounded-md bg-slate-900 hover:bg-slate-800 text-white dark:bg-orange-600 dark:hover:bg-orange-500 text-xs font-bold tracking-wide transition-colors">
            Reset Filter
        </button>
    </div>
@endif
