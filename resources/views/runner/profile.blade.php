@extends('layouts.pacerhub')

@section('title', $user->name . ' - Runner Profile')
@section('meta_title', $user->name . ' (@' . ($user->username ?? $user->id) . ') - Profile Pelari & Coach')
@section('meta_description', 'Profil dan performa lari ' . $user->name . ' (@' . ($user->username ?? $user->id) . ') di Ruang Lari. Lihat riwayat aktivitas, statistik komunitas, dan program latihan lari.')
@section('meta_keywords', $user->name . ', pelari indonesia, profil pelari, coach lari, ruang lari')

@push('styles')
<script>
    tailwind.config.theme.extend.colors.neon = '#ccff00';
</script>
<style>
    .glass-panel {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@section('content')
<main class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans bg-dark text-slate-200">
    <div class="max-w-5xl mx-auto">
        
        <!-- Header / Banner -->
        <div class="glass-panel rounded-3xl overflow-hidden shadow-2xl mb-8 relative group">
            <div class="h-48 md:h-64 bg-slate-800 relative overflow-hidden">
                @if($user->banner)
                    <img src="{{ asset('storage/' . $user->banner) }}" class="w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 opacity-30 bg-[url('https://upload.wikimedia.org/wikipedia/commons/e/ec/World_map_blank_without_borders.svg')] bg-cover bg-center"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-dark to-transparent"></div>
                @endif
            </div>

            <div class="px-6 pb-6 relative -mt-20 flex flex-col md:flex-row items-end gap-6">
                <div class="relative group">
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-2xl overflow-hidden border-4 border-slate-900 shadow-xl bg-slate-800">
                        <img loading="lazy" decoding="async" src="{{ $user->avatar_url }}" class="w-full h-full object-cover">
                    </div>
                    @if($user->role === 'coach')
                        <div class="absolute -bottom-2 -right-2 bg-blue-500 text-white text-xs font-black px-3 py-1 rounded-full border-2 border-slate-900 shadow-lg">
                            COACH
                        </div>
                    @elseif($user->role === 'eo')
                        <div class="absolute -bottom-2 -right-2 bg-purple-500 text-white text-xs font-black px-3 py-1 rounded-full border-2 border-slate-900 shadow-lg">
                            EO
                        </div>
                    @else
                        <div class="absolute -bottom-2 -right-2 bg-slate-700 text-slate-300 text-xs font-black px-3 py-1 rounded-full border-2 border-slate-900 shadow-lg">
                            RUNNER
                        </div>
                    @endif
                </div>

                <div class="flex-grow pb-2 w-full">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-black text-white leading-tight mb-1">{{ $user->name }}</h1>
                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                @if($user->username)
                                    <span class="text-neon font-mono">@</span><span class="text-slate-400 font-mono">{{ $user->username }}</span>
                                @endif
                                
                                @if($user->city)
                                    <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                    <span class="text-slate-300 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        {{ $user->city->name }}
                                    </span>
                                @endif

                                @if($user->gender)
                                    <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                    <span class="text-slate-300 capitalize">{{ $user->gender }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            @if(auth()->id() !== $user->id)
                                @auth
                                    @if(auth()->user()->isFollowing($user))
                                        <form action="{{ route('unfollow', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-red-500/20 hover:text-red-500 border border-slate-700 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" /></svg>
                                                Unfollow
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('follow', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-5 py-2.5 bg-neon hover:bg-white hover:text-dark text-dark rounded-xl text-sm font-black transition-all shadow-lg shadow-neon/20 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                                Follow
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(auth()->user()->role !== 'eo')
                                        <a href="{{ route('chat.show', $user) }}" class="p-2.5 bg-slate-800 hover:bg-blue-500/20 hover:text-blue-400 border border-slate-700 rounded-xl transition-all">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="px-5 py-2.5 bg-neon hover:bg-white hover:text-dark text-dark rounded-xl text-sm font-black transition-all shadow-lg shadow-neon/20">
                                        Login to Follow
                                    </a>
                                @endauth
                            @else
                                <a href="{{ route('profile.show') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-white hover:text-dark border border-slate-700 rounded-xl text-sm font-bold transition-all">
                                    Edit Profile
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: About & Stats -->
            <div class="space-y-6">
                <!-- Stats -->
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Community Stats</h3>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div>
                            <p class="text-xl font-black text-white">{{ $user->posts()->count() }}</p>
                            <p class="text-[10px] text-slate-500 uppercase">Posts</p>
                        </div>
                        <div>
                            <p class="text-xl font-black text-white">{{ $user->followers()->count() }}</p>
                            <p class="text-[10px] text-slate-500 uppercase">Followers</p>
                        </div>
                        <div>
                            <p class="text-xl font-black text-white">{{ $user->following()->count() }}</p>
                            <p class="text-[10px] text-slate-500 uppercase">Following</p>
                        </div>
                    </div>
                </div>

                @if($user->role === 'coach')
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Coach Stats</h3>
                    <div class="text-center">
                        <p class="text-xl font-black text-white">{{ $user->programs()->count() }}</p>
                        <p class="text-[10px] text-slate-500 uppercase">Programs</p>
                    </div>
                    @if($user->programs()->exists())
                    <div class="mt-4 pt-4 border-t border-slate-800">
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Related Programs</h4>
                        <div class="space-y-2">
                            @foreach($user->programs()->latest()->take(3)->get() as $program)
                            <a href="{{ route('programs.show', $program->slug) }}" class="block p-2 rounded-lg bg-slate-800 hover:bg-slate-700 transition-colors">
                                <p class="text-sm font-bold text-white">{{ $program->title }}</p>
                                <p class="text-[10px] text-slate-500">{{ $program->distance_target }} • {{ ucfirst($program->difficulty) }}</p>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Bio / Info -->
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">About</h3>
                    @if($user->bio)
                        <p class="text-sm text-slate-300 leading-relaxed">{{ $user->bio }}</p>
                    @else
                        <p class="text-sm text-slate-500 italic">No bio added yet.</p>
                    @endif
                    
                    <div class="mt-4 pt-4 border-t border-slate-800 space-y-2">
                        <div class="flex items-center gap-2 text-sm text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Joined {{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Gallery & Recent Activity -->
            <div class="lg:col-span-2 space-y-6">
                
                @if($user->role === 'coach')
                @php
                $coachDetails = [
                    'coach-raka' => [
                        'certifications' => ['PASI Athletics Coach Level 1', 'World Athletics Coach Education Level 1', 'Physiotherapy & Sports Injury Certified'],
                        'experience' => 'Mulai melatih sejak tahun 2018. Telah membimbing lebih dari 150+ pelari pemula hingga berhasil finish marathon pertama mereka tanpa cedera.',
                        'specialties' => ['Marathon Training Plan', '5K & 10K Speed Development', 'Injury Prevention & Recovery'],
                        'races' => ['Borobudur Marathon 2023 (Full Marathon)', 'Jakarta Marathon 2022', 'Maybank Marathon Bali 2023'],
                        'testimonials' => [
                            ['name' => 'Aditya Pratama', 'text' => 'Coach Raka sangat detail memantau heart rate saya. Berkat dia, saya berhasil finish Maybank Marathon sub-4 jam!', 'rating' => 5],
                            ['name' => 'Siti Aminah', 'text' => 'Program Couch to 5K dari Coach Raka gampang banget diikutin, bebas cedera shin splints!', 'rating' => 5]
                        ]
                    ],
                    'jefri-angga' => [
                        'certifications' => ['World Athletics Coach Level 2', 'Certified Strength & Conditioning Specialist (CSCS)'],
                        'experience' => 'Berpengalaman melatih atlet daerah dan pelari komunitas sejak tahun 2019, berfokus pada mekanika biomekanik lari efisien.',
                        'specialties' => ['10K Speed Performance', 'Half Marathon Strategy', 'Strength Training for Runners'],
                        'races' => ['Pocari Sweat Run 2023 (Half Marathon)', 'Singapore Marathon 2022'],
                        'testimonials' => [
                            ['name' => 'Budi Santoso', 'text' => 'Program strength trainingnya luar biasa, lutut saya ga pernah sakit lagi pas tanjakan!', 'rating' => 5]
                        ]
                    ]
                ];

                $currentCoach = $coachDetails[$user->username] ?? [
                    'certifications' => ['Certified Athletic Coach', 'Running Specialist'],
                    'experience' => 'Berpengalaman membimbing pelari komunitas dalam menyusun program latihan terstruktur baik jarak pendek maupun maraton.',
                    'specialties' => ['5K & 10K Training', 'Heart Rate Zone Training', 'Running Form Analysis'],
                    'races' => ['Maybank Marathon Bali', 'Pocari Sweat Run', 'Borobudur Marathon'],
                    'testimonials' => [
                        ['name' => 'Rian H.', 'text' => 'Program latihannya sangat terstruktur dan mudah diikuti via aplikasi.', 'rating' => 5]
                    ]
                ];
                @endphp

                <!-- Coach Professional Profile Details -->
                <div class="glass-panel rounded-3xl p-6 space-y-6">
                    <h2 class="text-xl font-black text-white italic uppercase tracking-tight pb-2 border-b border-slate-800">
                        Professional Coaching Profile
                    </h2>
                    
                    <!-- Certifications -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Sertifikasi & Lisensi Resmi</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($currentCoach['certifications'] as $cert)
                                <span class="px-3 py-1.5 bg-slate-900/90 text-neon border border-slate-800 rounded-xl text-xs font-mono font-bold flex items-center gap-1.5">
                                    <i class="fas fa-certificate text-neon"></i> {{ $cert }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Specialties -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Spesialisasi Program</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($currentCoach['specialties'] as $spec)
                                <span class="px-3 py-1.5 bg-neon/10 text-white border border-neon/20 rounded-xl text-xs font-bold">
                                    {{ $spec }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Experience & Bio -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-1">Pengalaman Melatih</h4>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            {{ $currentCoach['experience'] }}
                        </p>
                    </div>

                    <!-- Race Experience -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Pengalaman Race Utama</h4>
                        <div class="space-y-1.5">
                            @foreach($currentCoach['races'] as $race)
                                <div class="flex items-center gap-2 text-sm text-slate-400">
                                    <i class="fas fa-running text-slate-500"></i>
                                    <span>{{ $race }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Personal Bests (PB) -->
                    @if($user->pb_5k || $user->pb_10k || $user->pb_hm || $user->pb_fm)
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Personal Bests (PB)</h4>
                        <div class="grid grid-cols-4 gap-2">
                            @if($user->pb_5k)
                            <div class="bg-slate-900/50 rounded-xl p-3 border border-slate-800 text-center">
                                <p class="text-[10px] text-slate-500 uppercase">5K</p>
                                <p class="text-sm font-bold text-white">{{ $user->pb_5k }}</p>
                            </div>
                            @endif
                            @if($user->pb_10k)
                            <div class="bg-slate-900/50 rounded-xl p-3 border border-slate-800 text-center">
                                <p class="text-[10px] text-slate-500 uppercase">10K</p>
                                <p class="text-sm font-bold text-white">{{ $user->pb_10k }}</p>
                            </div>
                            @endif
                            @if($user->pb_hm)
                            <div class="bg-slate-900/50 rounded-xl p-3 border border-slate-800 text-center">
                                <p class="text-[10px] text-slate-500 uppercase">HM (21K)</p>
                                <p class="text-sm font-bold text-white">{{ $user->pb_hm }}</p>
                            </div>
                            @endif
                            @if($user->pb_fm)
                            <div class="bg-slate-900/50 rounded-xl p-3 border border-slate-800 text-center">
                                <p class="text-[10px] text-slate-500 uppercase">FM (42K)</p>
                                <p class="text-sm font-bold text-white">{{ $user->pb_fm }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Testimonials -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-3">Testimoni Peserta Program</h4>
                        <div class="space-y-3">
                            @foreach($currentCoach['testimonials'] as $testi)
                                <div class="bg-slate-950/40 p-4 border border-slate-900 rounded-xl space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-white">{{ $testi['name'] }}</span>
                                        <div class="flex text-yellow-500 text-[10px]">
                                            @for($i=1; $i<=5; $i++)
                                                <i class="fas fa-star"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-400 leading-relaxed italic">
                                        "{{ $testi['text'] }}"
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Active Created Programs -->
                    @if($user->programs()->exists())
                    <div>
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-3">Daftar Program Latihan yang Dibuat</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($user->programs()->where('is_published', true)->get() as $p)
                                <div class="bg-slate-900/30 hover:bg-slate-900/60 p-4 border border-slate-800 hover:border-neon/30 rounded-xl flex flex-col justify-between transition-colors">
                                    <div>
                                        <span class="px-2 py-0.5 rounded bg-slate-800 text-[9px] font-bold text-slate-300 border border-slate-700">
                                            {{ strtoupper($p->distance_target) }}
                                        </span>
                                        <h5 class="text-sm font-bold text-white mt-2 mb-1 group-hover:text-neon">
                                            <a href="{{ url('/programs/' . $p->slug) }}" class="hover:text-neon transition-colors">{{ $p->title }}</a>
                                        </h5>
                                        <p class="text-xs text-slate-500 mb-3">{{ $p->duration_weeks }} Minggu • {{ $p->sessions_per_week }} Sesi/Minggu</p>
                                    </div>
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-800/80">
                                        <span class="text-sm font-black text-white">
                                            {{ $p->price > 0 ? 'Rp ' . number_format($p->price, 0, ',', '.') : 'GRATIS' }}
                                        </span>
                                        <a href="{{ url('/programs/' . $p->slug) }}" class="text-xs text-neon font-bold hover:underline">
                                            Detail Program →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
                @endif

                <!-- Gallery Carousel -->
                @if($user->profile_images && count($user->profile_images) > 0)
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">Gallery</h3>
                    <div class="flex overflow-x-auto gap-4 no-scrollbar snap-x snap-mandatory pb-2">
                        @foreach($user->profile_images as $image)
                            <div class="flex-none w-48 h-48 rounded-xl overflow-hidden border border-slate-700 snap-center">
                                <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Recent Posts -->
                <div>
                    <h3 class="text-lg font-bold text-white mb-4">Recent Activity</h3>
                    <div class="space-y-4">
                        @forelse($user->posts()->latest()->take(5)->get() as $post)
                            <div class="glass-panel rounded-2xl p-4">
                                <div class="flex items-start gap-4">
                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : ($user->gender === 'female' ? 'https://avatar.iran.liara.run/public/girl' : 'https://avatar.iran.liara.run/public/boy') }}" class="w-10 h-10 rounded-full object-cover border border-slate-700">
                                    <div class="flex-grow">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-sm font-bold text-white">{{ $user->name }}</h4>
                                                <p class="text-xs text-slate-500">{{ $post->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <p class="text-sm text-slate-300 mt-2">{{ $post->content }}</p>
                                        @if($post->images)
                                            <div class="mt-3 grid grid-cols-3 gap-2">
                                                @foreach(array_slice($post->images, 0, 3) as $img)
                                                    <img src="{{ asset('storage/' . $img) }}" class="rounded-lg h-20 w-full object-cover">
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="mt-3 flex items-center gap-4 text-xs text-slate-500">
                                            <span class="flex items-center gap-1"><i class="fas fa-heart"></i> {{ $post->likes_count }}</span>
                                            <span class="flex items-center gap-1"><i class="fas fa-comment"></i> {{ $post->comments_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-slate-500">
                                <p>No recent activity.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
</main>

@if($user->role === 'coach')
@push('scripts')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "ProfilePage",
      "@@id": "{{ Request::url() }}#webpage",
      "url": "{{ Request::url() }}",
      "name": {!! json_encode($user->name . ' - Coach Lari Profesional | Ruang Lari') !!},
      "description": {!! json_encode($user->name . ' adalah coach lari terverifikasi di Ruang Lari. Temukan program latihan lari, pengalaman, dan ulasan peserta.') !!},
      "mainEntity": {
        "@@type": "Person",
        "name": {!! json_encode($user->name) !!},
        "image": "{{ $user->avatar_url }}",
        "description": {!! json_encode($user->bio ?? 'Coach Lari Profesional di Ruang Lari') !!},
        "jobTitle": "Running Coach"
      }
    }
  ]
}
</script>
@endpush
@endif
@endsection
