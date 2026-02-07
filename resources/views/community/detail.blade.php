<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $community->name }} - RuangLari Community</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Oswald:wght@500;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            neon: '{{ $palette[0] }}', // Primary/Accent
                            dark: '{{ $palette[1] }}', // Background
                            grey: '{{ $palette[2] }}'  // Secondary Background
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Oswald', 'sans-serif'],
                    },
                    animation: {
                        'marquee': 'marquee 25s linear infinite',
                    },
                    keyframes: {
                        marquee: {
                            '0%': { transform: 'translateX(0%)' },
                            '100%': { transform: 'translateX(-100%)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Hide scrollbar for clean UI */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        [v-cloak] { display: none; }
    </style>
</head>
<body class="bg-brand-dark text-white font-sans antialiased">

    <div id="app" v-cloak>
        <nav class="fixed w-full z-50 top-0 start-0 border-b border-white/10 bg-brand-dark/80 backdrop-blur-md transition-all duration-300">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between mx-auto p-4">
                <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
                    @if($community->logo)
                        <img src="{{ asset('storage/' . $community->logo) }}" class="h-10 w-10 rounded-full object-cover" alt="Logo">
                    @endif
                    <span class="self-center text-2xl font-display font-bold italic tracking-wider text-brand-neon uppercase">
                        {{ $community->name }}
                    </span>
                </a>
                <div class="flex md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
                    @if($community->wa_group_link)
                    <a href="{{ $community->wa_group_link }}" target="_blank" class="text-brand-dark bg-brand-neon hover:bg-white focus:ring-4 focus:outline-none focus:ring-lime-300 font-bold rounded-full text-sm px-6 py-2 text-center transition-all transform hover:scale-105">
                        Join WA Group
                    </a>
                    @endif
                </div>
            </div>
        </nav>

        <section class="relative h-screen flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="{{ $community->hero_image ? asset('storage/' . $community->hero_image) : 'https://images.unsplash.com/photo-1552674605-469455965637?q=80&w=2070&auto=format&fit=crop' }}" alt="Running Community" class="w-full h-full object-cover opacity-40">
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-transparent to-brand-dark/50"></div>
            </div>

            <div class="relative z-10 text-center px-4 max-w-4xl mx-auto mt-10">
                <p class="text-brand-neon font-bold tracking-[0.2em] mb-4 animate-pulse uppercase">
                    {{ $community->city->name ?? 'INDONESIA' }}
                </p>
                <h1 class="text-6xl md:text-8xl font-display font-bold mb-6 leading-tight uppercase italic">
                    {{ $community->name }}
                </h1>
                <p class="text-gray-300 text-lg md:text-xl mb-8 max-w-2xl mx-auto">
                    {{ $community->description ?? 'Bukan sekadar lari. Ini tentang kopi pagi, teman baru, dan endorfin yang bikin nagih. Level pemula sampai marathoner, semua satu pace: Happy Pace.' }}
                </p>
                <div class="flex flex-col md:flex-row gap-4 justify-center">
                    <a href="#schedule" class="text-brand-dark bg-white hover:bg-brand-neon font-bold py-3 px-8 rounded-full transition-all duration-300">
                        Lihat Jadwal
                    </a>
                    @if($community->instagram_link)
                    <a href="{{ $community->instagram_link }}" target="_blank" class="border border-white/30 hover:border-brand-neon hover:text-brand-neon text-white font-bold py-3 px-8 rounded-full transition-all duration-300 backdrop-blur-sm">
                        Instagram
                    </a>
                    @endif
                </div>
            </div>
        </section>

        <div class="relative flex overflow-x-hidden bg-brand-neon text-brand-dark py-3 rotate-1 transform origin-left">
            <div class="animate-marquee whitespace-nowrap">
                <span class="text-4xl font-display font-bold mx-4">RUN • SWEAT • CONNECT • REPEAT • RUN • SWEAT • CONNECT • REPEAT • RUN • SWEAT • CONNECT • REPEAT •</span>
                <span class="text-4xl font-display font-bold mx-4">RUN • SWEAT • CONNECT • REPEAT • RUN • SWEAT • CONNECT • REPEAT • RUN • SWEAT • CONNECT • REPEAT •</span>
            </div>
        </div>

        @if(count($schedules) > 0)
        <section id="schedule" class="py-20 px-4 bg-brand-dark">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-4xl font-display font-bold mb-12 text-center">Weekly <span class="text-brand-neon">Agenda</span></h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="(schedule, index) in schedules" :key="index" 
                         class="group relative bg-brand-grey border border-white/5 rounded-2xl p-8 hover:border-brand-neon/50 transition-all duration-300 hover:-translate-y-2 overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM9.8 8.9L7 23h2.1l1.8-8 2.1 2v6h2v-7.5l-2.1-2 .6-3C14.8 12 16.8 13 19 13v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.2L8 8v2h1.8l0-1.1z"/></svg>
                        </div>
                        <h3 class="text-brand-neon font-bold text-xl mb-2">@{{ schedule.day }}</h3>
                        <div class="text-3xl font-display font-bold mb-4">@{{ schedule.time }}</div>
                        <div class="space-y-2 text-gray-400">
                            <p class="flex items-center"><span class="w-2 h-2 bg-brand-neon rounded-full mr-2"></span> @{{ schedule.type }}</p>
                            <p class="flex items-center"><span class="w-2 h-2 bg-white rounded-full mr-2"></span> @{{ schedule.loc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <section class="py-20 px-4 bg-gradient-to-b from-brand-dark to-brand-grey">
            <div class="max-w-6xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12">
                    <div>
                        <h2 class="text-4xl font-display font-bold">Find Your <span class="text-brand-neon">Tribe</span></h2>
                        <p class="text-gray-400 mt-2">Jangan takut tertinggal. Ada pacer untuk setiap speed.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="(pace, index) in paceGroups" :key="index" 
                         class="bg-black/40 backdrop-blur-md p-6 rounded-xl border-l-4 border-brand-neon hover:bg-white/5 transition-all cursor-default">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-3xl">@{{ pace.emoji }}</span>
                            <span class="text-xs font-bold px-2 py-1 rounded bg-white/10 text-brand-neon">@{{ pace.range }} min/km</span>
                        </div>
                        <h4 class="text-xl font-bold text-white mb-1">@{{ pace.name }}</h4>
                        <p class="text-sm text-gray-400">@{{ pace.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if(count($captains) > 0)
        <section class="py-20 px-4 bg-brand-dark">
            <div class="max-w-6xl mx-auto text-center mb-12">
                <h2 class="text-4xl font-display font-bold">The <span class="text-brand-neon">Captains</span></h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                <div v-for="(cap, i) in captains" :key="i" class="group relative aspect-square rounded-2xl overflow-hidden cursor-pointer">
                    <img :src="cap.img" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 grayscale group-hover:grayscale-0">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-90">
                        <div class="absolute bottom-0 p-4 text-left">
                            <p class="text-brand-neon font-bold text-sm">@{{ cap.role }}</p>
                            <h3 class="text-white font-display font-bold text-lg">@{{ cap.name }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if(count($faqs) > 0)
        <section id="faq" class="py-20 px-4 bg-brand-grey">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-4xl font-display font-bold mb-8 text-center">Newbie <span class="text-brand-neon">FAQ</span></h2>
                
                <div class="space-y-4">
                    <div v-for="(item, index) in faqs" :key="index" 
                         class="bg-brand-dark rounded-xl overflow-hidden border border-white/5">
                        <button @click="toggleFaq(index)" 
                                class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none hover:bg-white/5 transition-colors">
                            <span class="font-bold text-lg" :class="item.open ? 'text-brand-neon' : 'text-white'">@{{ item.question }}</span>
                            <span class="transform transition-transform duration-300" :class="item.open ? 'rotate-180' : ''">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </button>
                        <div v-show="item.open" class="px-6 pb-6 text-gray-400 animate-fadeIn">
                            @{{ item.answer }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <footer class="py-20 px-4 text-center bg-brand-neon text-brand-dark">
            <h2 class="text-5xl md:text-7xl font-display font-bold mb-6 italic uppercase">Ready to Start?</h2>
            <p class="text-xl font-bold mb-8 max-w-2xl mx-auto">Bergabung dengan kami sekarang. Tidak perlu pendaftaran ribet, cukup datang dan lari.</p>
            @if($community->wa_group_link)
            <a href="{{ $community->wa_group_link }}" target="_blank" class="inline-block bg-brand-dark text-white hover:bg-white hover:text-brand-dark px-10 py-4 rounded-full font-bold text-lg transition-all transform hover:scale-105 shadow-xl">
                Join WhatsApp Community
            </a>
            @endif
            <p class="mt-12 text-sm font-bold opacity-60">© {{ date('Y') }} {{ $community->name }}. Powered by RuangLari.</p>
        </footer>

    </div>

    <script>
        const { createApp, ref } = Vue;

        createApp({
            setup() {
                // Data Jadwal from Server
                const schedules = ref(@json($schedules));

                // Data Captains from Server
                const captains = ref(@json($captains));

                // Data Pace Group (Static for now, can be moved to DB later)
                const paceGroups = ref([
                    { name: 'The Rockets', range: '4:00 - 5:00', desc: 'Untuk yang mau ngejar PB atau latihan speed.', emoji: '🚀' },
                    { name: 'The Cruisers', range: '5:30 - 6:30', desc: 'Pace nyaman, masih bisa ngobrol tipis-tipis.', emoji: '🛳️' },
                    { name: 'Happy Pace', range: '7:00 - 8:00', desc: 'Jogging santai, banyak foto, finish happy.', emoji: '🥳' },
                    { name: 'Walk/Run', range: 'Mix', desc: 'Kombinasi jalan dan lari. Pemula friendly!', emoji: '🐢' },
                ]);

                // FAQ Logic
                const faqs = ref(@json($faqs));

                const toggleFaq = (index) => {
                    faqs.value[index].open = !faqs.value[index].open;
                };

                return {
                    schedules,
                    paceGroups,
                    captains,
                    faqs,
                    toggleFaq
                };
            }
        }).mount('#app');
    </script>
</body>
</html>
