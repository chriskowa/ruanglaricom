@extends('layouts.pacerhub')

@section('content')

    <header class="relative pt-32 pb-10 px-4 text-center overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-accent/20 rounded-full blur-[120px] -z-10"></div>
        <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6">
            FIND YOUR <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400">RHYTHM</span>
        </h1>

        <div class="max-w-4xl mx-auto bg-card/50 backdrop-blur-xl border border-slate-700 p-2 rounded-2xl shadow-2xl flex flex-col md:flex-row gap-2 mt-10">
            <div class="relative flex-grow">
                <input id="pacer-search" type="text" placeholder="Cari nama atau target waktu..." class="w-full bg-slate-900/50 text-white pl-6 pr-4 py-4 rounded-xl border border-transparent focus:border-neon outline-none transition">
            </div>
            <div class="flex bg-slate-900/50 rounded-xl p-1 overflow-x-auto">
                <button class="px-6 py-3 rounded-lg text-sm font-bold transition-all whitespace-nowrap text-slate-400 hover:text-white" data-cat="All">All</button>
                <button class="px-6 py-3 rounded-lg text-sm font-bold transition-all whitespace-nowrap text-slate-400 hover:text-white" data-cat="HM (21K)">HM (21K)</button>
                <button class="px-6 py-3 rounded-lg text-sm font-bold transition-all whitespace-nowrap text-slate-400 hover:text-white" data-cat="FM (42K)">FM (42K)</button>
                <button class="px-6 py-3 rounded-lg text-sm font-bold transition-all whitespace-nowrap text-slate-400 hover:text-white" data-cat="10K">10K</button>
            </div>
        </div>
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
            const search = document.getElementById('pacer-search');
            const grid = document.getElementById('pacer-grid');
            const cards = Array.from(grid.children);
            const countEl = document.getElementById('pacer-count');
            let currentCat = 'All';

            function applyFilter(){
                const q = (search.value || '').toLowerCase();
                let visible = 0;
                cards.forEach(card => {
                    const inCat = currentCat === 'All' || card.getAttribute('data-cat') === currentCat;
                    const hasQuery = !q || card.getAttribute('data-name').includes(q) || card.getAttribute('data-nick').includes(q) || card.getAttribute('data-pace').includes(q);
                    const show = inCat && hasQuery;
                    card.style.display = show ? '' : 'none';
                    if(show) visible++;
                });
                countEl.textContent = visible;
            }

            document.querySelectorAll('[data-cat]').forEach(btn => {
                btn.addEventListener('click', () => {
                    currentCat = btn.getAttribute('data-cat');
                    applyFilter();
                });
            });
            search.addEventListener('input', applyFilter);
            applyFilter();
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
