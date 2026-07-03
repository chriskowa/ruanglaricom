@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Program Lari & Training Plan Terbaik - Ruang Lari')
@section('meta_title', 'Program Lari & Training Plan Terbaik - Ruang Lari')
@section('meta_description', 'Temukan program lari dan training plan terbaik dari coach profesional di Ruang Lari. Rencana program latihan terstruktur untuk 5K, 10K, HM, hingga Marathon.')
@section('meta_keywords', 'program lari, training plan lari, coach profesional, program latihan, running program, pelatih lari, marathon training')

@section('content')
<div id="programs-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans bg-dark text-slate-200" v-cloak>
    
    <!-- Hero Section -->
    <div class="mb-10 relative z-10 mt-10" data-aos="fade-down">
        <div class="text-center max-w-4xl mx-auto">
            <p class="text-neon font-mono text-sm tracking-widest uppercase mb-2">Marketplace</p>
            <h1 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter mb-6 uppercase">
                Program Lari & <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400 pr-2">Training Plan Terbaik</span> Untuk Personal Best Anda
            </h1>
            <p class="text-slate-400 text-base leading-relaxed text-justify md:text-center">
                Selamat datang di marketplace program lari Ruang Lari, tempat terbaik untuk menemukan training program yang dirancang secara ilmiah untuk membantu Anda mencapai target kebugaran dan performa lari optimal. Apakah Anda sedang mempersiapkan program 5K pertama Anda, menargetkan finish kuat di 10K, atau menjalani program latihan marathon penuh yang menuntut disiplin tinggi, kami menyediakan berbagai pilihan rencana latihan terstruktur dari layanan coaching lari terbaik di Indonesia. Di sini, Anda dapat dengan mudah mencari program lari berdasarkan kategori jarak, tingkat kesulitan, serta rentang harga yang sesuai. 
            </p>
            <p class="text-slate-400 text-base leading-relaxed text-justify md:text-center mt-3">
                Selain program latihan yang dibuat oleh <a href="{{ route('coaches.index') }}" class="text-neon hover:underline font-bold">coach profesional terverifikasi</a>, kami juga menyediakan fitur "realistic program"—sebuah generator program latihan pintar berbasis AI dan formula VDOT Jack Daniels yang dapat Anda akses melalui kalkulator <a href="{{ url('/tools/realistic-running-program') }}" class="text-neon hover:underline font-bold">Realistic Running Program</a> khusus kami. Fitur ini secara otomatis menyusun jadwal lari mingguan yang dipersonalisasi sesuai dengan waktu target realistis Anda saat ini. Gunakan filter di sebelah kiri untuk menyaring program berdasarkan target jarak lari Anda, atau langsung mulai kustomisasi program latihan personal Anda sekarang!
            </p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8 relative z-10">
        
        <!-- Mobile Filter Button -->
        <div class="lg:hidden mb-4">
            <button @click="showMobileFilters = true" class="w-full py-3 bg-slate-800 border border-slate-700 rounded-xl text-white font-bold flex items-center justify-center gap-2 hover:border-neon transition-colors">
                <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                Filter Programs
            </button>
        </div>

        <!-- Sidebar Filters (Desktop) -->
        <aside class="hidden lg:block w-72 shrink-0 space-y-8 sticky top-24 h-fit">
            <!-- Search -->
            <div class="relative">
                <input v-model="filters.search" @input="debouncedSearch" type="text" placeholder="Search programs..." class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-4 py-3 pl-10 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all placeholder-slate-500">
                <svg class="w-5 h-5 text-slate-500 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>

            <!-- Categories -->
            <div>
                <h3 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Category</h3>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="radio" v-model="filters.category" value="" class="peer appearance-none w-5 h-5 border-2 border-slate-600 rounded-full checked:border-neon checked:bg-slate-800 transition-colors">
                            <div class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-neon scale-0 peer-checked:scale-100 transition-transform"></div>
                        </div>
                        <span class="text-slate-400 group-hover:text-white transition-colors">All Categories</span>
                    </label>
                    <label v-for="cat in categories" :key="cat.value" class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="radio" v-model="filters.category" :value="cat.value" class="peer appearance-none w-5 h-5 border-2 border-slate-600 rounded-full checked:border-neon checked:bg-slate-800 transition-colors">
                            <div class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-neon scale-0 peer-checked:scale-100 transition-transform"></div>
                        </div>
                        <span class="text-slate-400 group-hover:text-white transition-colors">@{{ cat.label }}</span>
                    </label>
                </div>
            </div>

            <!-- Difficulty -->
            <div>
                <h3 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Difficulty</h3>
                <div class="flex flex-wrap gap-2">
                    <button v-for="level in difficulties" :key="level.value" 
                        @click="toggleDifficulty(level.value)"
                        :class="filters.difficulty === level.value ? 'bg-neon text-dark border-neon' : 'bg-slate-800 text-slate-400 border-slate-700 hover:border-slate-500'"
                        class="px-3 py-1.5 rounded-lg border text-xs font-bold uppercase transition-all">
                        @{{ level.label }}
                    </button>
                </div>
            </div>

            <!-- Price Range -->
            <div>
                <h3 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Price Range</h3>
                <div class="flex items-center gap-2">
                    <input v-model.number="filters.price_min" @change="fetchPrograms" type="number" placeholder="Min" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon focus:outline-none">
                    <span class="text-slate-500">-</span>
                    <input v-model.number="filters.price_max" @change="fetchPrograms" type="number" placeholder="Max" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon focus:outline-none">
                </div>
            </div>

            <!-- Reset -->
            <button @click="resetFilters" class="w-full py-2 text-slate-500 hover:text-white text-sm underline decoration-slate-600 hover:decoration-white transition-all">
                Reset All Filters
            </button>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
            
            <!-- Top Bar -->
            <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                <div class="text-sm text-slate-400">
                    Showing <span class="text-white font-bold">@{{ programs.from || 0 }}-@{{ programs.to || 0 }}</span> of <span class="text-white font-bold">@{{ programs.total || 0 }}</span> programs
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-500">Sort by:</span>
                    <select v-model="filters.sort" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-white focus:border-neon focus:outline-none">
                        <option value="newest">Newest</option>
                        <option value="popular">Most Popular</option>
                        <option value="rating">Highest Rated</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                    </select>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="n in 6" :key="n" class="bg-slate-900 rounded-2xl p-4 border border-slate-800 animate-pulse">
                    <div class="h-48 bg-slate-800 rounded-xl mb-4"></div>
                    <div class="h-4 bg-slate-800 rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-slate-800 rounded w-1/2"></div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="programs.data && programs.data.length === 0" class="text-center py-20 bg-slate-900/50 rounded-3xl border border-dashed border-slate-800">
                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No Programs Found</h3>
                <p class="text-slate-400 mb-6">Try adjusting your filters or search terms.</p>
                <button @click="resetFilters" class="px-6 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition-colors">
                    Clear Filters
                </button>
            </div>

            <!-- Program Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="program in programs.data" :key="program.id" :class="['group bg-slate-900/50 backdrop-blur-sm border rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 flex flex-col', program.is_challenge ? 'border-neon shadow-[0_0_30px_rgba(57,255,20,0.3)] shadow-neon/50' : 'border-slate-800 hover:border-neon/50 hover:shadow-xl hover:shadow-neon/5']">
                    
                    <!-- Image -->
                    <div class="relative h-48 overflow-hidden">
                        <img :src="program.image_url || 'https://source.unsplash.com/random/400x300/?running'" :alt="program.title" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent opacity-80"></div>
                        
                        <!-- Badges -->
                        <div class="absolute top-3 right-3 flex flex-col gap-2 items-end">
                            <span class="px-3 py-1 rounded-full bg-slate-900/90 backdrop-blur text-xs font-bold text-white border border-slate-700">
                                @{{ formatCategory(program.distance_target) }}
                            </span>
                            <span :class="getDifficultyColor(program.difficulty)" class="px-3 py-1 rounded-full text-xs font-bold text-dark border border-transparent">
                                @{{ program.difficulty }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-5 flex-1 flex flex-col">
                        <!-- Coach Info -->
                        <div class="flex items-center gap-2 mb-3">
                            <img :src="getCoachAvatar(program.coach)" class="w-6 h-6 rounded-full object-cover border border-slate-600">
                            <span class="text-xs text-slate-400">Coach @{{ program.coach?.name || 'Unknown' }}</span>
                        </div>

                        <h3 class="text-lg font-bold text-white mb-2 line-clamp-2 group-hover:text-neon transition-colors">
                            <a :href="'/programs/' + program.slug">@{{ program.title }}</a>
                        </h3>

                        <!-- Rating -->
                        <div class="flex items-center gap-1 mb-4">
                            <div class="flex text-yellow-500 text-xs">
                                <i v-for="i in 5" :key="i" :class="i <= Math.round(program.average_rating || 0) ? 'fas fa-star' : 'far fa-star'"></i>
                            </div>
                            <span class="text-xs text-slate-500">(@{{ program.reviews_count || 0 }})</span>
                        </div>

                        <!-- Stats Row -->
                        <div class="grid grid-cols-2 gap-2 mb-4 py-3 border-y border-slate-800">
                            <div class="text-center border-r border-slate-800">
                                <p class="text-[10px] text-slate-500 uppercase">Duration</p>
                                <p class="text-sm font-bold text-white">@{{ program.duration_weeks }} Weeks</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] text-slate-500 uppercase">Sessions</p>
                                <p class="text-sm font-bold text-white">@{{ program.sessions_per_week }}/week</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="mt-auto flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs text-slate-500">Price</p>
                                <p class="text-xl font-black text-white">
                                    @{{ formatPrice(program.price) }}
                                </p>
                            </div>
                            <a :href="'{{ url('/programs') }}/' + program.slug" class="px-4 py-2 bg-white text-dark font-bold rounded-lg hover:bg-neon transition-colors text-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="programs.last_page > 1" class="mt-10 flex justify-center">
                <nav class="flex items-center gap-2">
                    <button @click="changePage(programs.current_page - 1)" :disabled="programs.current_page === 1" class="w-10 h-10 flex items-center justify-center rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <span class="text-sm text-slate-400 px-2">
                        Page <span class="text-white font-bold">@{{ programs.current_page }}</span> of @{{ programs.last_page }}
                    </span>

                    <button @click="changePage(programs.current_page + 1)" :disabled="programs.current_page === programs.last_page" class="w-10 h-10 flex items-center justify-center rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </nav>
            </div>
        </div>

    </div>

    <!-- Mobile Filters Slide-over -->
    <div v-if="showMobileFilters" class="fixed inset-0 z-50 lg:hidden">
        <div @click="showMobileFilters = false" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
        <div class="absolute right-0 top-0 bottom-0 w-80 bg-slate-900 border-l border-slate-800 p-6 overflow-y-auto shadow-2xl transform transition-transform duration-300">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-xl font-black text-white italic">FILTERS</h2>
                <button @click="showMobileFilters = false" class="text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <!-- Mobile Filter Content (Same as sidebar) -->
            <div class="space-y-8">
                <!-- Search -->
                <div class="relative">
                    <input v-model="filters.search" type="text" placeholder="Search..." class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white">
                </div>

                <!-- Categories -->
                <div>
                    <h3 class="text-white font-bold mb-4 uppercase text-sm">Category</h3>
                    <div class="space-y-3">
                        <label v-for="cat in categories" :key="cat.value" class="flex items-center gap-3">
                            <input type="radio" v-model="filters.category" :value="cat.value" class="accent-neon w-5 h-5">
                            <span class="text-slate-300">@{{ cat.label }}</span>
                        </label>
                    </div>
                </div>

                <!-- Difficulty -->
                <div>
                    <h3 class="text-white font-bold mb-4 uppercase text-sm">Difficulty</h3>
                    <div class="flex flex-wrap gap-2">
                        <button v-for="level in difficulties" :key="level.value" 
                            @click="toggleDifficulty(level.value)"
                            :class="filters.difficulty === level.value ? 'bg-neon text-dark' : 'bg-slate-800 text-slate-400'"
                            class="px-3 py-1.5 rounded-lg border border-transparent text-xs font-bold uppercase">
                            @{{ level.label }}
                        </button>
                    </div>
                </div>

                <button @click="showMobileFilters = false" class="w-full py-3 bg-neon text-dark font-bold rounded-xl mt-8">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Kategori Program Lari Populer -->
    <div class="mt-20 border-t border-slate-800 pt-16">
        <h2 class="text-3xl font-extrabold text-white text-center mb-12">
            Kategori <span class="text-neon">Program Lari</span> Terpopuler
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-8 hover:border-neon/30 transition-all duration-300">
                <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-neon/10 text-neon flex items-center justify-center text-sm font-mono font-bold">1</span>
                    Program 5K & 10K untuk Pemula
                </h2>
                <p class="text-slate-400 leading-relaxed mb-4 text-sm">
                    Mulailah perjalanan lari Anda dengan aman dan terstruktur. Kategori ini dirancang khusus untuk membangun daya tahan kardiovaskular, membiasakan otot dengan beban latihan, serta menetapkan dasar teknik lari yang benar untuk menghindari cedera umum.
                </p>
                <a href="?category=5k" class="text-neon hover:underline text-sm font-bold flex items-center gap-1">
                    Cari Program 5K & 10K <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-8 hover:border-neon/30 transition-all duration-300">
                <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center text-sm font-mono font-bold">2</span>
                    Program Half Marathon (21K) & Latihan Marathon (42K)
                </h2>
                <p class="text-slate-400 leading-relaxed mb-4 text-sm">
                    Kembangkan kekuatan, kecepatan, dan ketahanan mental Anda untuk jarak jauh. Program ini mencakup long run akhir pekan yang terukur, latihan tempo, interval, serta strategi nutrisi dan hidrasi yang krusial untuk menaklukkan garis finish perlombaan marathon Anda.
                </p>
                <a href="?category=42k" class="text-neon hover:underline text-sm font-bold flex items-center gap-1">
                    Cari Program Marathon <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Kenapa Pilih Program di RuangLari -->
    <div class="mt-20 bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 rounded-3xl p-8 md:p-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-neon/5 rounded-full blur-[120px]"></div>
        <div class="relative z-10">
            <h2 class="text-3xl font-extrabold text-white mb-6">
                Kenapa Pilih <span class="text-neon">Program Lari</span> di Ruang Lari?
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-neon/10 text-neon flex items-center justify-center text-xl">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h4 class="text-lg font-bold text-white">Coach Profesional Terverifikasi</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Seluruh rencana latihan disusun oleh <a href="{{ route('coaches.index') }}" class="text-neon hover:underline font-bold">coach profesional</a> berpengalaman yang siap membimbing Anda mencapai personal best secara aman.
                    </p>
                </div>
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h4 class="text-lg font-bold text-white">Integrasi Kalender & Aktivitas</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Jadwal latihan lari Anda tersinkronisasi langsung ke dashboard personal Anda, memudahkan pemantauan harian serta progres mingguan.
                    </p>
                </div>
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-xl">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h4 class="text-lg font-bold text-white">Teknologi Realistic Program AI</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Butuh program instan berbasis data performa terkini? Coba kalkulator pintar <a href="{{ url('/tools/realistic-running-program') }}" class="text-neon hover:underline font-bold">Realistic Running Program</a> kami.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonial Section -->
    <div class="mt-20">
        <h2 class="text-3xl font-extrabold text-white text-center mb-4">
            Cerita Sukses <span class="text-neon">Runners</span> Kami
        </h2>
        <p class="text-slate-400 text-center max-w-2xl mx-auto mb-12 text-sm">
            Lihat bagaimana program latihan lari di Ruang Lari telah membantu pelari dari berbagai tingkat kemampuan mencapai target mereka.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-slate-900/60 border border-slate-800 p-6 rounded-2xl flex flex-col justify-between">
                <div>
                    <div class="flex text-yellow-500 text-xs mb-4">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-slate-300 text-sm italic mb-6">
                        "Program latihan marathon 16 minggu dari Coach di Ruang Lari benar-benar mengubah cara saya berlari. Sebelumnya saya selalu cedera ITB, tapi dengan menu kekuatan otot tambahan, saya berhasil finish Full Marathon pertama saya dengan tangguh!"
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-neon font-bold">R</div>
                    <div>
                        <h5 class="text-sm font-bold text-white">Rian D.</h5>
                        <p class="text-[11px] text-slate-500">Marathon Finisher</p>
                    </div>
                </div>
            </div>
            <div class="bg-slate-900/60 border border-slate-800 p-6 rounded-2xl flex flex-col justify-between">
                <div>
                    <div class="flex text-yellow-500 text-xs mb-4">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-slate-300 text-sm italic mb-6">
                        "Saya mencari program lari untuk pemula karena baru mulai lari 3 bulan lalu. Mengikuti program 5K di sini sangat menyenangkan. Durasi latihan bertambah bertahap dan sangat realistis bagi pekerja kantoran seperti saya."
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-neon font-bold">S</div>
                    <div>
                        <h5 class="text-sm font-bold text-white">Siti A.</h5>
                        <p class="text-[11px] text-slate-500">5K Runner</p>
                    </div>
                </div>
            </div>
            <div class="bg-slate-900/60 border border-slate-800 p-6 rounded-2xl flex flex-col justify-between">
                <div>
                    <div class="flex text-yellow-500 text-xs mb-4">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-slate-300 text-sm italic mb-6">
                        "Fitur Realistic Program berbasis VDOT benar-benar akurat. Saya menginput waktu 10K terakhir saya, dan program latihan yang di-generate langsung menyesuaikan pace latihan interval saya dengan presisi tinggi."
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-neon font-bold">A</div>
                    <div>
                        <h5 class="text-sm font-bold text-white">Aditya K.</h5>
                        <p class="text-[11px] text-slate-500">Sub-50 10K Runner</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="mt-20 border-t border-slate-800 pt-16">
        <h2 class="text-3xl font-extrabold text-white text-center mb-12">
            FAQ - Pertanyaan Umum Seputar <span class="text-neon">Program Lari</span>
        </h2>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
                <h4 class="text-lg font-bold text-white mb-2">Bagaimana cara memilih program lari untuk pemula yang tepat?</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Bagi pemula, pilihlah program yang berfokus pada pembangunan daya tahan kardio dasar (base building) secara perlahan, seperti program latihan 5K. Pastikan program tersebut memiliki porsi hari istirahat (recovery days) yang cukup dan instruksi teknik lari dasar agar terhindar dari cedera otot.
                </p>
            </div>
            <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
                <h4 class="text-lg font-bold text-white mb-2">Idealnya, program marathon berapa minggu sebelum hari perlombaan?</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Untuk jarak Full Marathon (42.195K), durasi program latihan marathon yang ideal adalah 16 hingga 20 minggu. Durasi ini memberikan waktu yang cukup bagi tubuh untuk melakukan penyesuaian volume lari mingguan (weekly mileage) dan latihan long run terjadwal tanpa memicu overtraining.
                </p>
            </div>
            <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
                <h4 class="text-lg font-bold text-white mb-2">Apa keuntungan menggunakan program lari dari coach profesional?</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Layanan coaching lari terpandu menawarkan rencana latihan yang disesuaikan secara dinamis dengan kondisi fisik dan kesibukan harian Anda. Anda juga bisa membaca wawasan lari mendalam di <a href="/blog/kategori/program-lari" class="text-neon hover:underline font-bold">Blog Kategori Program Lari</a> untuk menunjang wawasan latihan harian Anda.
                </p>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="mt-20 text-center py-16 bg-gradient-to-r from-neon/10 via-transparent to-green-500/10 border border-neon/20 rounded-3xl relative overflow-hidden">
        <div class="absolute inset-0 bg-dark/60 backdrop-blur-sm -z-10"></div>
        <h2 class="text-3xl md:text-4xl font-black text-white italic mb-4">
            SIAP UNTUK MENJADI LEBIH CEPAT & LEBIH KUAT?
        </h2>
        <p class="text-slate-300 max-w-xl mx-auto mb-8 text-base">
            Mulai langkah pertama Anda sekarang dengan program latihan terstruktur. Pilih program terbaik atau rancang program personal lari Anda hari ini.
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <button @click="resetFilters" class="px-8 py-4 bg-neon hover:bg-neon/90 text-dark font-black rounded-xl text-base shadow-lg shadow-neon/20 transition-all uppercase tracking-wider">
                Mulai Program Lari Sekarang
            </button>
            <a href="{{ url('/tools/realistic-running-program') }}" class="px-8 py-4 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-base transition-colors border border-slate-700 uppercase tracking-wider">
                Rancang Realistic Program
            </a>
        </div>
    </div>

</div>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Program Lari & Training Plan Ruang Lari",
  "description": "Daftar program latihan lari terstruktur dari coach lari profesional.",
  "numberOfItems": {{ $programs->total() }},
  "itemListElement": [
    @foreach($programs as $index => $prog)
    {
      "@type": "ListItem",
      "position": {{ $index + 1 }},
      "item": {
        "@type": "Product",
        "name": {!! json_encode($prog->title) !!},
        "description": {!! json_encode(Str::limit(strip_tags($prog->description), 150)) !!},
        "image": "{{ $prog->image_url ?? asset('images/ruanglari.png') }}",
        "offers": {
          "@type": "Offer",
          "price": "{{ $prog->price }}",
          "priceCurrency": "IDR",
          "availability": "https://schema.org/InStock"
        },
        "provider": {
          "@type": "Person",
          "name": {!! json_encode($prog->coach->name ?? 'Coach Ruang Lari') !!}
        }
      }
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Bagaimana cara memilih program lari untuk pemula yang tepat?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Bagi pemula, pilihlah program lari untuk pemula yang berfokus pada pembangunan daya tahan dasar (base building) seperti Program 5K. Hindari langsung memulai program dengan intensitas tinggi untuk mencegah cedera."
      }
    },
    {
      "@type": "Question",
      "name": "Idealnya, program marathon berapa minggu sebelum hari perlombaan?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Untuk perlombaan Full Marathon, program marathon idealnya berlangsung selama 16 hingga 20 minggu. Ini memberikan waktu yang cukup bagi tubuh untuk beradaptasi dengan peningkatan jarak lari secara bertahap."
      }
    },
    {
      "@type": "Question",
      "name": "Apa itu Realistic Program di Ruang Lari?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Realistic Program adalah generator program latihan pintar berbasis web yang menyesuaikan target dan performa lari Anda secara otomatis berdasarkan rumus ilmiah VDOT."
      }
    }
  ]
}
</script>
@endsection

@push('scripts')
<script>
    const { createApp, ref, reactive, watch, onMounted } = Vue;

    createApp({
        setup() {
            const programs = ref(@json($programs));
            const loading = ref(false);
            const showMobileFilters = ref(false);
            
            const filters = reactive({
                search: '{{ request("search") }}',
                category: '{{ request("category") }}',
                difficulty: '{{ request("difficulty") }}',
                rating: '{{ request("rating") }}',
                price_min: '{{ request("price_min") }}',
                price_max: '{{ request("price_max") }}',
                sort: '{{ request("sort", "newest") }}',
                page: 1
            });

            const categories = [
                { label: '5K', value: '5k' },
                { label: '10K', value: '10k' },
                { label: 'Half Marathon (21K)', value: '21k' },
                { label: 'Marathon (42K)', value: '42k' }
            ];

            const difficulties = [
                { label: 'Beginner', value: 'beginner' },
                { label: 'Intermediate', value: 'intermediate' },
                { label: 'Advanced', value: 'advanced' }
            ];

            let debounceTimer;
            const debouncedSearch = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filters.page = 1;
                    fetchPrograms();
                }, 500);
            };

            const fetchPrograms = async () => {
                loading.value = true;
                
                // Construct Query String
                const params = new URLSearchParams();
                Object.entries(filters).forEach(([key, value]) => {
                    if (value) params.append(key, value);
                });

                try {
                    const response = await fetch(`{{ route("programs.index") }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    programs.value = data;
                    
                    // Update Browser URL
                    const newUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.pushState({}, '', newUrl);
                    
                } catch (error) {
                    console.error('Error fetching programs:', error);
                } finally {
                    loading.value = false;
                }
            };

            // Watchers for immediate filtering
            watch(() => filters.category, () => { filters.page = 1; fetchPrograms(); });
            watch(() => filters.sort, () => { filters.page = 1; fetchPrograms(); });
            
            const toggleDifficulty = (val) => {
                filters.difficulty = filters.difficulty === val ? '' : val;
                filters.page = 1;
                fetchPrograms();
            };

            const changePage = (page) => {
                filters.page = page;
                fetchPrograms();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            const resetFilters = () => {
                filters.search = '';
                filters.category = '';
                filters.difficulty = '';
                filters.price_min = '';
                filters.price_max = '';
                filters.sort = 'newest';
                filters.page = 1;
                fetchPrograms();
            };

            // Helpers
            const getCoachAvatar = (coach) => {
                if (coach && coach.avatar) {
                    if (coach.avatar.startsWith('http')) return coach.avatar;
                    if (coach.avatar.startsWith('images/')) return '/' + coach.avatar;
                    return '/storage/' + coach.avatar;
                }
                const name = coach ? coach.name : 'Coach';
                return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=1e293b&color=39FF14`;
            };

            const formatPrice = (price) => {
                if (!price || price == 0) return 'Free';
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
            };

            const formatCategory = (val) => {
                if(!val) return 'General';
                const map = { '5k': '5K', '10k': '10K', '21k': 'Half Marathon', '42k': 'Full Marathon' };
                return map[val] || val.toUpperCase();
            };

            const getDifficultyColor = (diff) => {
                const map = {
                    'beginner': 'bg-green-400',
                    'intermediate': 'bg-yellow-400',
                    'advanced': 'bg-red-400 text-white'
                };
                return map[diff] || 'bg-slate-400';
            };

            return {
                programs,
                filters,
                loading,
                showMobileFilters,
                categories,
                difficulties,
                debouncedSearch,
                fetchPrograms,
                toggleDifficulty,
                changePage,
                resetFilters,
                formatPrice,
                formatCategory,
                getDifficultyColor,
                getCoachAvatar
            };
        }
    }).mount('#programs-app');
</script>

<style>
    [v-cloak] { display: none; }
</style>
@endpush
