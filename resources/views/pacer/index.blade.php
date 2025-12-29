@extends('layouts.pacerhub')

@section('content')

    <header class="relative pt-32 pb-10 px-4 text-center overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-accent/20 rounded-full blur-[120px] -z-10"></div>
        <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6">
            FIND YOUR <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400">RHYTHM</span>
        </h1>

        <form action="{{ route('pacer.index') }}" method="GET" class="max-w-5xl mx-auto mt-10 text-left relative z-20">
            <div class="bg-card/50 backdrop-blur-xl border border-slate-700 p-6 rounded-2xl shadow-2xl space-y-4">
                <!-- Top Row -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5">
                        <label class="block text-xs font-mono text-cyan-400 mb-1 uppercase tracking-wider">Search</label>
                        <div class="relative">
                            <input name="search" value="{{ request('search') }}" type="text" placeholder="Name or nickname..." class="w-full bg-slate-900/50 text-white pl-10 pr-4 py-3 rounded-xl border border-slate-600 focus:border-neon outline-none transition placeholder-slate-600">
                            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-xs font-mono text-cyan-400 mb-1 uppercase tracking-wider">City</label>
                        <div class="relative">
                            <select name="city_id" class="w-full bg-slate-900/50 text-white px-4 py-3 rounded-xl border border-slate-600 focus:border-neon outline-none transition appearance-none cursor-pointer">
                                <option value="">All Cities</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                @endforeach
                            </select>
                            <svg class="w-4 h-4 text-slate-500 absolute right-3 top-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-mono text-cyan-400 mb-1 uppercase tracking-wider">Target Pace</label>
                        <input name="pace" value="{{ request('pace') }}" type="text" placeholder="e.g. 5:30" class="w-full bg-slate-900/50 text-white px-4 py-3 rounded-xl border border-slate-600 focus:border-neon outline-none transition placeholder-slate-600 text-center font-mono">
                    </div>
                </div>

                <!-- PB Row -->
                <div class="bg-slate-900/30 rounded-xl p-4 border border-slate-700/50">
                    <div class="flex items-center gap-2 mb-3 cursor-pointer group" onclick="document.getElementById('pb-filters').classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')">
                         <span class="text-xs font-mono text-neon uppercase tracking-wider group-hover:text-white transition-colors">Personal Best Requirements (Max Time)</span>
                         <svg class="w-3 h-3 text-neon transition-transform duration-300 {{ request()->anyFilled(['pb_5k', 'pb_10k', 'pb_hm', 'pb_fm']) ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                    <div id="pb-filters" class="grid grid-cols-2 md:grid-cols-4 gap-4 {{ request()->anyFilled(['pb_5k', 'pb_10k', 'pb_hm', 'pb_fm']) ? '' : 'hidden' }}">
                        <div>
                            <label class="text-[10px] text-slate-500 uppercase mb-1 block">5K PB</label>
                            <input name="pb_5k" value="{{ request('pb_5k') }}" type="text" placeholder="00:20:00" class="w-full bg-slate-900/50 text-white px-3 py-2 rounded-lg border border-slate-700 focus:border-neon outline-none text-xs font-mono text-center placeholder-slate-600">
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-500 uppercase mb-1 block">10K PB</label>
                            <input name="pb_10k" value="{{ request('pb_10k') }}" type="text" placeholder="00:45:00" class="w-full bg-slate-900/50 text-white px-3 py-2 rounded-lg border border-slate-700 focus:border-neon outline-none text-xs font-mono text-center placeholder-slate-600">
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-500 uppercase mb-1 block">Half Marathon PB</label>
                            <input name="pb_hm" value="{{ request('pb_hm') }}" type="text" placeholder="01:45:00" class="w-full bg-slate-900/50 text-white px-3 py-2 rounded-lg border border-slate-700 focus:border-neon outline-none text-xs font-mono text-center placeholder-slate-600">
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-500 uppercase mb-1 block">Full Marathon PB</label>
                            <input name="pb_fm" value="{{ request('pb_fm') }}" type="text" placeholder="03:45:00" class="w-full bg-slate-900/50 text-white px-3 py-2 rounded-lg border border-slate-700 focus:border-neon outline-none text-xs font-mono text-center placeholder-slate-600">
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-2">
                    <div class="flex flex-wrap justify-center gap-2">
                         <label class="cursor-pointer">
                            <input type="radio" name="category" value="All" class="hidden peer" {{ request('category', 'All') == 'All' ? 'checked' : '' }} onchange="this.form.submit()">
                            <span class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-900 border border-slate-700 text-slate-400 peer-checked:bg-neon peer-checked:text-dark peer-checked:border-neon transition hover:text-white hover:border-slate-500">All Categories</span>
                         </label>
                         @foreach(['HM (21K)', 'FM (42K)', '10K'] as $cat)
                         <label class="cursor-pointer">
                            <input type="radio" name="category" value="{{ $cat }}" class="hidden peer" {{ request('category') == $cat ? 'checked' : '' }} onchange="this.form.submit()">
                            <span class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-900 border border-slate-700 text-slate-400 peer-checked:bg-neon peer-checked:text-dark peer-checked:border-neon transition hover:text-white hover:border-slate-500">{{ $cat }}</span>
                         </label>
                         @endforeach
                    </div>
                    
                    <div class="flex gap-3 w-full md:w-auto items-center">
                        @if(request()->anyFilled(['search', 'city_id', 'pace', 'pb_5k', 'pb_10k', 'pb_hm', 'pb_fm']))
                        <a href="{{ route('pacer.index') }}" class="px-4 py-2 text-slate-400 hover:text-white text-xs font-bold uppercase tracking-wider transition">Reset Filters</a>
                        @endif
                        <button type="submit" class="flex-1 md:flex-none px-8 py-3 bg-neon text-dark font-black rounded-xl hover:bg-white hover:shadow-neon-cyan transition shadow-lg transform hover:-translate-y-0.5 text-sm">
                            FIND PACERS
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </header>

    <main class="max-w-7xl mx-auto px-4 pb-20 flex-grow w-full">
        <div class="mb-8 flex justify-between items-end border-b border-slate-800 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-white">Available Pacers</h2>
                <p class="text-slate-400 text-sm mt-1">Showing <span id="pacer-count">{{ $pacers->count() }}</span> professionals</p>
            </div>
        </div>

        <div id="pacer-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($pacers as $pacer)
                <div class="group bg-card rounded-2xl overflow-hidden border border-slate-700 hover:border-neon/50 transition-all duration-300 hover:-translate-y-2 relative flex flex-col" data-cat="{{ $pacer->category }}" data-name="{{ strtolower($pacer->user->name) }}" data-nick="{{ strtolower($pacer->nickname ?? '') }}" data-pace="{{ strtolower($pacer->pace ?? '') }}">
                    <div class="absolute top-4 left-4 z-10">
                        <span class="px-3 py-1 rounded-full text-xs font-bold border backdrop-blur-sm uppercase {{ $pacer->verified ? 'bg-green-500/20 text-green-400 border-green-500/50' : 'bg-slate-700 text-slate-300 border-slate-500' }}">
                            {{ $pacer->verified ? 'Verified' : 'Pacer' }}
                        </span>
                    </div>
                    <div class="h-64 overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-t from-card via-transparent to-transparent z-10 opacity-60 group-hover:opacity-40 transition-opacity duration-500"></div>
                        <img loading="lazy" decoding="async" src="{{ $pacer->user->avatar ? asset('storage/' . $pacer->user->avatar) : ($pacer->user->gender === 'female' ? asset('images/default-female.svg') : asset('images/default-male.svg')) }}" class="w-full h-full object-cover transition-transform duration-700 ease-out scale-100 group-hover:scale-105">
                        <div class="absolute bottom-4 right-4 z-20 bg-neon text-dark font-black text-xs px-3 py-1 skew-x-[-12deg] shadow-[4px_4px_0px_rgba(0,0,0,0.5)]">{{ $pacer->category }}</div>
                    </div>
                    <div class="p-6 pt-2 relative z-20 flex flex-col flex-grow">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <p class="text-slate-400 text-xs uppercase mb-1">Pace</p>
                                <h3 class="font-mono text-4xl font-bold text-white group-hover:text-neon transition-colors">{{ $pacer->pace }}</h3>
                            </div>
                            <div class="text-right">
                                <p class="text-slate-400 text-xs uppercase mb-1">Finish</p>
                                <p class="font-mono text-xl font-bold text-white">{{ $pacer->total_races }} races</p>
                            </div>
                        </div>
                        <div class="h-px w-full bg-slate-700 my-4"></div>
                        <div class="flex-grow">
                            <h4 class="font-bold text-lg text-white">{{ $pacer->user->name }}</h4>
                            @if($pacer->nickname)
                                <p class="text-neon text-sm italic">"{{ $pacer->nickname }}"</p>
                            @endif
                        </div>
                        <div class="flex gap-2 mt-6">
                            <a href="{{ route('pacer.show', $pacer->seo_slug) }}" class="flex-1 py-3 bg-neon text-dark font-black rounded-lg hover:bg-lime-400 transition-all duration-300 text-center shadow-[0_0_20px_rgba(204,255,0,0.3)]">LIHAT PROFIL</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

    <!-- Modal -->
    <div id="pacer-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-dark/90 backdrop-blur-sm"></div>
        <div class="relative z-10 max-w-4xl mx-auto mt-24 bg-card border border-slate-700 rounded-3xl overflow-hidden shadow-2xl">
            <div class="flex justify-between items-center p-4 border-b border-slate-800">
                <h3 id="modal-name" class="font-bold text-white">Pacer</h3>
                <button id="modal-close" class="bg-black/50 hover:bg-neon hover:text-dark text-white p-2 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <img id="modal-image" src="" class="w-full h-64 object-cover rounded-xl border border-slate-700" />
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase mb-1">Category</p>
                    <p id="modal-category" class="text-white font-bold mb-2"></p>
                    <p class="text-slate-400 text-xs uppercase mb-1">Pace</p>
                    <p id="modal-pace" class="text-white font-mono text-2xl"></p>
                    <div class="mt-6 flex gap-3">
                        <a id="modal-profile-link" href="#" class="px-4 py-2 bg-slate-700 text-white rounded-lg">View Full Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            // Modal logic
            const modal = document.getElementById('pacer-modal');
            const modalClose = document.getElementById('modal-close');
            function openModal(data){
                document.getElementById('modal-name').textContent = data.name + (data.nickname ? ' — "'+data.nickname+'"' : '');
                document.getElementById('modal-image').src = data.image || '{{ asset("images/placeholder-run.jpg") }}';
                document.getElementById('modal-category').textContent = data.category || '-';
                document.getElementById('modal-pace').textContent = data.pace || '-';
                document.getElementById('modal-profile-link').href = '/pacer/'+data.slug;
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            function closeModal(){ modal.classList.add('hidden'); document.body.style.overflow='auto'; }
            modalClose.addEventListener('click', closeModal);
            document.querySelectorAll('[data-open-modal]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const data = JSON.parse(btn.getAttribute('data-pacer'));
                    openModal(data);
                });
            });
        })();
    </script>
@endsection
