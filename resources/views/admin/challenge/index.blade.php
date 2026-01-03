@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Approval Challenge')

@section('content')
<div class="p-16 bg-slate-900 rounded-xl overflow-hidden shadow-xl border border-slate-700">
    <h2 class="text-2xl font-bold text-white mb-6">Approval Aktivitas Challenge</h2>

    @if(session('success'))
        <div class="bg-green-600/20 border border-green-500 text-green-100 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabs -->
    <div class="flex gap-4 mb-4 border-b border-slate-700">
        <button id="tab-pending" onclick="showTab('pending')" class="pb-2 text-white border-b-2 border-neon font-bold transition-colors">Aktivitas Menunggu</button>
        <button id="tab-enrolled" onclick="showTab('enrolled')" class="pb-2 text-slate-400 hover:text-white transition-colors">Peserta 40 Days</button>
    </div>

    <div class="bg-slate-800 rounded-xl overflow-hidden shadow-xl border border-slate-700">
        <!-- View Pending -->
        <div id="view-pending">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-900 text-slate-100 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-6 py-4">Runner</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Jarak / Durasi</th>
                            <th class="px-6 py-4">Bukti</th>
                            <th class="px-6 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($activities as $activity)
                        <tr class="hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-white">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $activity->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($activity->user->name) }}" class="w-8 h-8 rounded-full">
                                    <div>
                                        <div class="font-bold">{{ $activity->user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $activity->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                {{ \Carbon\Carbon::parse($activity->date)->format('d M Y') }}
                                <div class="text-xs text-slate-500">Submit: {{ $activity->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $activity->distance }} KM</div>
                                <div class="text-xs">{{ gmdate('H:i:s', $activity->duration_seconds) }}</div>
                                <div class="text-xs text-slate-500">Pace: {{ number_format($activity->duration_seconds / 60 / ($activity->distance > 0 ? $activity->distance : 1), 2) }} min/km</div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ asset('storage/' . $activity->image_path) }}" target="_blank" class="inline-flex items-center gap-1 text-blue-400 hover:text-blue-300">
                                    <i class="fas fa-image"></i> Lihat Foto
                                </a>
                                @if($activity->strava_link)
                                <br>
                                <a href="{{ $activity->strava_link }}" target="_blank" class="inline-flex items-center gap-1 text-orange-500 hover:text-orange-400 mt-1">
                                    <i class="fab fa-strava"></i> Strava
                                </a>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.challenge.approve', $activity->id) }}" method="POST" onsubmit="return confirm('Approve aktivitas ini?')">
                                        @csrf
                                        <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <button onclick="openRejectModal({{ $activity->id }})" class="bg-red-600 hover:bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                <i class="fas fa-check-circle text-4xl mb-3 text-slate-700"></i>
                                <p>Tidak ada aktivitas yang menunggu persetujuan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-700">
                {{ $activities->links() }}
            </div>
        </div>

        <!-- View Enrolled -->
        <div id="view-enrolled" class="hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-900 text-slate-100 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-6 py-4">Runner</th>
                            <th class="px-6 py-4">Tanggal Join</th>
                            <th class="px-6 py-4">Strava URL</th>
                            <th class="px-6 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($enrolledRunners as $enrollment)
                        <tr class="hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-white">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $enrollment->runner->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($enrollment->runner->name) }}" class="w-8 h-8 rounded-full">
                                    <div>
                                        <div class="font-bold">{{ $enrollment->runner->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $enrollment->runner->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                {{ $enrollment->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($enrollment->runner->strava_url)
                                    <a href="{{ $enrollment->runner->strava_url }}" target="_blank" class="text-orange-500 hover:text-orange-400">
                                        <i class="fab fa-strava"></i> Link
                                    </a>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openSyncModal({{ $enrollment->runner->id }})" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                    <i class="fas fa-sync"></i> Sync Strava
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                <p>Tidak ada peserta terdaftar.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeRejectModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                <h3 class="font-bold text-white">Tolak Aktivitas</h3>
                <button onclick="closeRejectModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Alasan Penolakan</label>
                        <textarea name="reason" rows="3" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white text-sm focus:outline-none focus:border-red-500" placeholder="Contoh: Foto buram, tanggal tidak sesuai..." required></textarea>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-700 bg-slate-900/50 flex justify-end gap-2">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Batal</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-bold">Tolak Aktivitas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sync Modal -->
<div id="syncModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeSyncModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                <h3 class="font-bold text-white">Sync Strava</h3>
                <button onclick="closeSyncModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form id="syncForm" method="POST" onsubmit="event.preventDefault(); submitSync(event);">
                @csrf
                <input type="hidden" id="syncUserId" name="user_id">
                <div class="p-4 space-y-4">
                    <!-- Helper Link -->
                    <div class="mb-4 text-xs text-slate-400 bg-slate-900 p-3 rounded border border-slate-700">
                        <p class="mb-1 font-bold text-slate-300">Helper: Dapatkan Code/Token</p>
                        <p class="mb-2">Gunakan URL ini di browser untuk authorize user manual:</p>
                        <code class="block bg-black p-2 rounded text-green-400 mb-2 break-all select-all cursor-pointer" onclick="navigator.clipboard.writeText(this.innerText); alert('URL copied!');">https://www.strava.com/oauth/authorize?client_id={{ config('services.strava.client_id') }}&response_type=code&redirect_uri={{ config('app.url') }}&approval_prompt=force&scope=read,activity:read_all</code>
                        <p class="text-[10px] text-slate-500">*Ganti redirect_uri jika perlu. Code akan muncul di URL setelah authorize.</p>
                    </div>

                    <div class="bg-blue-900/30 p-3 rounded border border-blue-500/30 text-xs text-blue-200 mb-2">
                        <i class="fas fa-info-circle mr-1"></i> 
                        <b>Club Mode (Auto):</b> Kosongkan semua field.<br>
                        <b>Manual Mode:</b> Isi Strava ID dan salah satu Token untuk update kredensial user.
                    </div>

                    <!-- Strava ID -->
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Strava ID (Opsional - Jika URL invalid)</label>
                        <input type="text" name="strava_id" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white text-sm focus:outline-none focus:border-blue-500" placeholder="Contoh: 12345678">
                    </div>

                    <!-- Access Token -->
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Access Token (Opsional - String Panjang)</label>
                        <input type="text" name="access_token" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white text-sm focus:outline-none focus:border-blue-500" placeholder="Paste Access Token here">
                    </div>

                    <!-- Refresh Token -->
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Refresh Token (Opsional)</label>
                        <input type="text" id="refreshToken" name="refresh_token" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white text-sm focus:outline-none focus:border-blue-500" placeholder="Paste Refresh Token here">
                    </div>

                    <!-- Expires At -->
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Expires At (Timestamp - Opsional)</label>
                        <input type="text" name="expires_at" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white text-sm focus:outline-none focus:border-blue-500" placeholder="Contoh: 1735891234">
                    </div>
                </div>
                <div class="p-4 border-t border-slate-700 bg-slate-900/50 flex justify-end gap-2">
                    <button type="button" onclick="closeSyncModal()" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Batal</button>
                    <button type="submit" id="btnSyncSubmit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2">
                        <span id="syncBtnText">Sync Sekarang</span>
                        <i id="syncBtnLoading" class="fas fa-spinner fa-spin hidden"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showTab(tab) {
        if (tab === 'pending') {
            document.getElementById('view-pending').classList.remove('hidden');
            document.getElementById('view-enrolled').classList.add('hidden');
            document.getElementById('tab-pending').classList.add('text-white', 'border-b-2', 'border-neon');
            document.getElementById('tab-pending').classList.remove('text-slate-400');
            document.getElementById('tab-enrolled').classList.remove('text-white', 'border-b-2', 'border-neon');
            document.getElementById('tab-enrolled').classList.add('text-slate-400');
        } else {
            document.getElementById('view-pending').classList.add('hidden');
            document.getElementById('view-enrolled').classList.remove('hidden');
            document.getElementById('tab-pending').classList.remove('text-white', 'border-b-2', 'border-neon');
            document.getElementById('tab-pending').classList.add('text-slate-400');
            document.getElementById('tab-enrolled').classList.add('text-white', 'border-b-2', 'border-neon');
            document.getElementById('tab-enrolled').classList.remove('text-slate-400');
        }
    }

    function openRejectModal(id) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        form.action = "{{ url('admin/challenge/reject') }}/" + id;
        modal.classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    function openSyncModal(userId) {
        document.getElementById('syncUserId').value = userId;
        document.getElementById('syncModal').classList.remove('hidden');
    }

    function closeSyncModal() {
        document.getElementById('syncModal').classList.add('hidden');
        document.getElementById('syncForm').reset();
    }

    async function submitSync(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSyncSubmit');
        const btnText = document.getElementById('syncBtnText');
        const btnLoading = document.getElementById('syncBtnLoading');
        
        btn.disabled = true;
        btnText.innerText = 'Syncing...';
        btnLoading.classList.remove('hidden');

        const formData = new FormData(document.getElementById('syncForm'));

        try {
            const response = await fetch("{{ route('admin.challenge.sync-strava') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                closeSyncModal();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('An error occurred during sync.');
            console.error(error);
        } finally {
            btn.disabled = false;
            btnText.innerText = 'Sync Sekarang';
            btnLoading.classList.add('hidden');
        }
        return false;
    }
</script>
@endsection
