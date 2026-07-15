@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', $session->name . ' - Running Analysis')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans bg-[#060a17]">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex items-center text-slate-400 text-sm font-medium">
            <a href="{{ route('admin.running-analysis.sessions.index') }}" class="hover:text-[#ccff00]"><i class="fas fa-arrow-left mr-2"></i> Back to Sessions</a>
        </div>

        @if(session('success'))
        <div class="bg-green-900/50 border border-green-500/50 text-green-400 px-4 py-3 rounded relative mb-6">
            {{ session('success') }}
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content: Runner Queue -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-[#0f172a] rounded-xl border border-slate-800 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-800 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white uppercase italic tracking-wider">Runner Queue</h3>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.running-analysis.upload-video.form', $session) }}" class="px-4 py-2 bg-slate-850 hover:bg-slate-750 text-white text-sm font-semibold rounded border border-slate-700 transition-colors flex items-center gap-1.5">
                                <i class="fas fa-upload text-slate-400"></i> Upload Manual Video
                            </a>
                            <button onclick="openAddRunnersModal()" class="px-4 py-2 bg-slate-800 text-white text-sm font-semibold rounded hover:bg-slate-700 transition-colors">
                                <i class="fas fa-plus mr-1"></i> Add Runners
                            </button>
                        </div>
                    </div>
                    <div class="p-0">
                        @if($session->runners->count() > 0)
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-900 text-slate-400 text-xs uppercase tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">#</th>
                                        <th class="px-6 py-3 font-semibold">Runner</th>
                                        <th class="px-6 py-3 font-semibold">Status</th>
                                        <th class="px-6 py-3 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800 text-slate-300">
                                    @foreach($session->runners->sortBy('pivot.sequence_no') as $runner)
                                    <tr class="hover:bg-slate-800/30 transition-colors group">
                                        <td class="px-6 py-4 text-slate-500 font-mono">{{ $runner->pivot->sequence_no }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full overflow-hidden mr-3 bg-slate-800">
                                                    <img src="{{ $runner->avatar_url }}" alt="avatar" class="w-full h-full object-cover">
                                                </div>
                                                <div>
                                                    <div class="font-bold text-white">{{ $runner->name }}</div>
                                                    <div class="text-xs text-slate-500">{{ '@'.$runner->username }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $status = $runner->pivot->status;
                                                $badgeClass = match($status) {
                                                    'pending' => 'bg-slate-800 text-slate-400 border-slate-700',
                                                    'captured' => 'bg-blue-900/40 text-blue-400 border-blue-800',
                                                    'analyzed' => 'bg-purple-900/40 text-purple-400 border-purple-800',
                                                    'published' => 'bg-green-900/40 text-green-400 border-green-800',
                                                    'repeat_required' => 'bg-red-900/40 text-red-400 border-red-800',
                                                    default => 'bg-slate-800 text-slate-400 border-slate-700',
                                                };
                                            @endphp
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded uppercase border {{ $badgeClass }}">
                                                {{ str_replace('_', ' ', $status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-end gap-3">
                                                @php
                                                    $latestTrial = $session->trials->where('runner_id', $runner->id)->sortByDesc('created_at')->first();
                                                @endphp
                                                @if($latestTrial)
                                                    <a href="{{ route('admin.running-analysis.trials.review', $latestTrial->id) }}" class="text-xs font-semibold text-white bg-slate-750 hover:bg-slate-650 px-3 py-1.5 rounded border border-slate-700 transition-colors">
                                                        Review Trial
                                                    </a>
                                                @else
                                                    <span class="text-xs text-slate-500 mr-2">No trials yet</span>
                                                @endif

                                                <a href="{{ route('admin.running-analysis.upload-video.form', $session) }}?runner_id={{ $runner->id }}" class="text-xs font-semibold text-black bg-[#ccff00] hover:bg-white px-3 py-1.5 rounded transition-colors flex items-center gap-1">
                                                    <i class="fas fa-upload text-[10px]"></i> Upload Video
                                                </a>

                                                <form action="{{ route('admin.running-analysis.sessions.runners.remove', [$session, $runner]) }}" method="POST" onsubmit="return confirm('Remove {{ $runner->name }} from this session?')" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-slate-500 hover:text-red-500 p-1.5 rounded transition-colors" title="Remove Runner">
                                                        <i class="fas fa-trash-alt text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-12 px-6">
                                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                                    <i class="fas fa-users text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-bold text-white mb-2">No Runners Added</h4>
                                <p class="text-slate-400 text-sm max-w-md mx-auto">Add runners to this session to build the capture queue. Runners will be captured in sequence.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar: Session Details -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-[#0f172a] rounded-xl border border-slate-800 p-6">
                    <h2 class="text-2xl font-black italic tracking-tighter text-white mb-1">{{ $session->name }}</h2>
                    <p class="text-slate-400 text-sm mb-6"><i class="fas fa-map-marker-alt mr-1"></i> {{ $session->location ?: 'No location set' }} &bull; {{ $session->session_date->format('d M Y') }}</p>

                    <form action="{{ route('admin.running-analysis.sessions.update', $session) }}" method="POST" class="mb-6">
                        @csrf
                        @method('PATCH')
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Session Status</label>
                        <div class="flex gap-2">
                            <select name="status" class="flex-1 bg-slate-900 border border-slate-700 rounded text-white text-sm focus:outline-none focus:border-[#ccff00] p-2">
                                <option value="draft" {{ $session->status === 'draft' ? 'selected' : '' }}>Draft (Setup)</option>
                                <option value="active" {{ $session->status === 'active' ? 'selected' : '' }}>Active (Capturing)</option>
                                <option value="completed" {{ $session->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="archived" {{ $session->status === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-2 rounded font-semibold text-sm transition-colors">Update</button>
                        </div>
                    </form>

                    @if($session->status === 'active')
                        <a href="{{ route('admin.running-analysis.capture', $session) }}" class="block w-full text-center px-4 py-3 bg-[#ccff00] text-black font-black italic tracking-wider uppercase rounded hover:bg-white transition-colors pulse-css">
                            <i class="fas fa-camera mr-2"></i> Launch Capture
                        </a>
                    @else
                        <button disabled class="block w-full text-center px-4 py-3 bg-slate-800 text-slate-500 font-black italic tracking-wider uppercase rounded cursor-not-allowed">
                            <i class="fas fa-camera mr-2"></i> Launch Capture
                        </button>
                        <p class="text-xs text-center text-slate-500 mt-2">Set status to Active to launch capture interface.</p>
                    @endif
                </div>

                <!-- Stats Card -->
                <div class="bg-[#0f172a] rounded-xl border border-slate-800 p-6">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">Session Stats</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-sm">Total Runners</span>
                            <span class="text-white font-bold">{{ $session->runners->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-sm">Captured</span>
                            <span class="text-[#ccff00] font-bold">{{ $session->runners->whereIn('pivot.status', ['captured','analyzed','published'])->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-sm">Published Reports</span>
                            <span class="text-green-400 font-bold">{{ $session->runners->where('pivot.status', 'published')->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-slate-800/50">
                            <span class="text-slate-400 text-sm">Total Trials</span>
                            <span class="text-white font-bold">{{ $session->trials->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Runners Modal -->
<div id="addRunnersModal" class="hidden fixed inset-0 z-[1100] overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-950/80 backdrop-blur-sm" onclick="closeAddRunnersModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-[#0f172a] border border-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
            <form action="{{ route('admin.running-analysis.sessions.runners.add', $session) }}" method="POST" id="add-runners-form">
                @csrf
                <div class="px-6 py-5 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white uppercase italic tracking-wider">Add Runners</h3>
                    <button type="button" class="text-slate-400 hover:text-white" onclick="closeAddRunnersModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Search Runner</label>
                        <div class="relative">
                            <input type="text" id="runner-search-input" placeholder="Type name, email, or username..." class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white focus:outline-none focus:border-[#ccff00]">
                            <div class="absolute left-3 top-3.5 text-slate-500">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div class="border border-slate-800 rounded-lg overflow-hidden bg-slate-950/50">
                        <div class="text-xs font-bold text-slate-500 px-4 py-2 border-b border-slate-800 uppercase tracking-wider">Search Results</div>
                        <div id="search-results-list" class="max-h-48 overflow-y-auto divide-y divide-slate-800/60">
                            <!-- JS populated -->
                            <div class="p-4 text-center text-slate-500 text-sm">Type to search for runners...</div>
                        </div>
                    </div>

                    <!-- Selected Runners list -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Selected Runners (<span id="selected-count">0</span>)</label>
                        <div id="selected-runners-container" class="flex flex-wrap gap-2 min-h-[40px] p-2 border border-dashed border-slate-800 rounded-lg bg-slate-900/30">
                            <div class="text-xs text-slate-500 italic p-1" id="no-runners-selected">No runners selected yet.</div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-900 border-t border-slate-800 flex justify-end">
                    <button type="button" class="px-4 py-2 text-slate-300 hover:text-white mr-4" onclick="closeAddRunnersModal()">Cancel</button>
                    <button type="submit" id="submit-btn" disabled class="px-6 py-2 bg-[#ccff00] text-black font-bold uppercase rounded hover:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Add Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let selectedRunners = [];

    function openAddRunnersModal() {
        document.getElementById('addRunnersModal').classList.remove('hidden');
        document.getElementById('runner-search-input').value = '';
        searchRunners('');
    }

    function closeAddRunnersModal() {
        document.getElementById('addRunnersModal').classList.add('hidden');
    }

    // Debounce helper
    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    const searchInput = document.getElementById('runner-search-input');
    searchInput.addEventListener('input', debounce((e) => {
        searchRunners(e.target.value);
    }));

    async function searchRunners(query) {
        const resultsList = document.getElementById('search-results-list');
        resultsList.innerHTML = '<div class="p-4 text-center text-slate-500 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i>Searching...</div>';
        
        try {
            const response = await fetch(`{{ route('admin.running-analysis.sessions.runners.search', $session) }}?q=${encodeURIComponent(query)}`);
            const runners = await response.json();
            
            if (runners.length === 0) {
                resultsList.innerHTML = '<div class="p-4 text-center text-slate-500 text-sm">No runners found.</div>';
                return;
            }
            
            resultsList.innerHTML = '';
            runners.forEach(runner => {
                const isAlreadySelected = selectedRunners.some(r => r.id === runner.id);
                
                const item = document.createElement('div');
                item.className = `p-3 flex justify-between items-center transition-colors hover:bg-slate-800/40 ${isAlreadySelected ? 'opacity-50' : 'cursor-pointer'}`;
                item.innerHTML = `
                    <div>
                        <div class="text-sm font-bold text-white">${runner.name}</div>
                        <div class="text-xs text-slate-400">${runner.email}</div>
                    </div>
                    <div>
                        ${isAlreadySelected ? '<span class="text-xs text-slate-500 font-semibold">Selected</span>' : '<button type="button" class="text-xs bg-slate-800 hover:bg-[#ccff00] hover:text-black text-white px-2.5 py-1 rounded transition-colors font-bold uppercase">Select</button>'}
                    </div>
                `;
                
                if (!isAlreadySelected) {
                    item.addEventListener('click', () => selectRunner(runner));
                }
                resultsList.appendChild(item);
            });
        } catch (err) {
            console.error("Search failed", err);
            resultsList.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Failed to load runners.</div>';
        }
    }

    function selectRunner(runner) {
        selectedRunners.push(runner);
        updateSelectedList();
        searchRunners(searchInput.value);
    }

    function deselectRunner(id) {
        selectedRunners = selectedRunners.filter(r => r.id !== id);
        updateSelectedList();
        searchRunners(searchInput.value);
    }

    function updateSelectedList() {
        const container = document.getElementById('selected-runners-container');
        const noSelected = document.getElementById('no-runners-selected');
        const submitBtn = document.getElementById('submit-btn');
        const countEl = document.getElementById('selected-count');
        
        const badges = container.querySelectorAll('.selected-runner-badge');
        badges.forEach(b => b.remove());
        
        countEl.innerText = selectedRunners.length;

        if (selectedRunners.length === 0) {
            noSelected.classList.remove('hidden');
            submitBtn.disabled = true;
        } else {
            noSelected.classList.add('hidden');
            submitBtn.disabled = false;
            
            selectedRunners.forEach(runner => {
                const badge = document.createElement('div');
                badge.className = 'selected-runner-badge flex items-center gap-2 bg-[#ccff00]/10 text-[#ccff00] border border-[#ccff00]/30 px-3 py-1 rounded-full text-xs font-semibold mb-1 mr-1';
                badge.innerHTML = `
                    <span>${runner.name}</span>
                    <input type="hidden" name="runner_ids[]" value="${runner.id}">
                    <button type="button" class="hover:text-white transition-colors text-slate-400 font-bold focus:outline-none" onclick="deselectRunner('${runner.id}')">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(badge);
            });
        }
    }
</script>
@endpush
