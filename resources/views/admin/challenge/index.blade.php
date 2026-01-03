@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Approval Challenge')

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-bold text-white mb-6">Approval Aktivitas Challenge</h2>

    @if(session('success'))
        <div class="bg-green-600/20 border border-green-500 text-green-100 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-slate-800 rounded-xl overflow-hidden shadow-xl border border-slate-700">
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

<script>
    function openRejectModal(id) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        form.action = "{{ url('admin/challenge/reject') }}/" + id;
        modal.classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endsection
