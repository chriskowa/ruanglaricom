@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Popup Management')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <div class="text-xs font-mono text-slate-400 uppercase tracking-widest">Popup Management</div>
            <h1 class="text-3xl font-black text-white">All Popups</h1>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.popups.create') }}" class="px-5 py-2 rounded-xl bg-primary text-slate-900 font-bold">Create New Popup</a>
            <a href="{{ route('admin.popups.analytics') }}" class="px-5 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white font-bold">Analytics</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
        <input name="search" value="{{ request('search') }}" placeholder="Search name or slug" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
        <select name="status" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
            <option value="">All Status</option>
            @foreach(['draft','scheduled','active','expired'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="sort" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
            <option value="">Sort Latest</option>
            <option value="name:asc" @selected(request('sort')==='name:asc')>Name A-Z</option>
            <option value="name:desc" @selected(request('sort')==='name:desc')>Name Z-A</option>
            <option value="starts_at:asc" @selected(request('sort')==='starts_at:asc')>Start Soonest</option>
            <option value="ends_at:desc" @selected(request('sort')==='ends_at:desc')>End Latest</option>
        </select>
        <button class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-600 text-white font-bold">Filter</button>
    </form>

    <form method="POST" action="{{ route('admin.popups.bulk') }}">
        @csrf
        <div class="flex flex-wrap items-center gap-3 mb-3">
            <select name="action" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white" required>
                <option value="">Bulk Actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
            </select>
            <button class="px-4 py-2 rounded-xl bg-primary text-slate-900 font-bold">Apply</button>
        </div>

        <div class="overflow-x-auto bg-slate-900/60 border border-slate-700 rounded-2xl">
            <table class="w-full text-sm text-slate-200">
                <thead class="text-xs uppercase text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="p-3"><input type="checkbox" id="select-all"></th>
                        <th class="p-3 text-left">Popup</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Schedule</th>
                        <th class="p-3 text-left">Views</th>
                        <th class="p-3 text-left">Clicks</th>
                        <th class="p-3 text-left">Conversions</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($popups->count())
                        @foreach($popups as $popup)
                            <tr class="border-b border-slate-800 hover:bg-slate-800/40">
                                <td class="p-3"><input type="checkbox" name="ids[]" value="{{ $popup->id }}"></td>
                                <td class="p-3">
                                    <div class="font-bold text-white">{{ $popup->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $popup->slug }}</div>
                                </td>
                                <td class="p-3">
                                    @if($popup->status === 'draft')
                                        <span class="px-2 py-1 rounded-full text-xs bg-slate-700 text-slate-200">Draft</span>
                                    @elseif($popup->status === 'scheduled')
                                        <span class="px-2 py-1 rounded-full text-xs bg-blue-500/30 text-blue-200">Scheduled</span>
                                    @elseif($popup->status === 'active')
                                        <span class="px-2 py-1 rounded-full text-xs bg-emerald-500/30 text-emerald-200">Active</span>
                                    @elseif($popup->status === 'expired')
                                        <span class="px-2 py-1 rounded-full text-xs bg-rose-500/30 text-rose-200">Expired</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs bg-slate-700 text-slate-200">{{ ucfirst($popup->status) }}</span>
                                    @endif
                                </td>
                                <td class="p-3 text-xs text-slate-300">
                                    <div>{{ optional($popup->starts_at)->format('d M Y H:i') ?? '—' }}</div>
                                    <div class="text-slate-500">{{ optional($popup->ends_at)->format('d M Y H:i') ?? '—' }}</div>
                                </td>
                                <td class="p-3">{{ $popup->views ?? 0 }}</td>
                                <td class="p-3">{{ $popup->clicks ?? 0 }}</td>
                                <td class="p-3">{{ $popup->conversions ?? 0 }}</td>
                                <td class="p-3 text-right">
                                    <a href="{{ route('admin.popups.edit', $popup) }}" class="px-3 py-1 rounded-lg bg-slate-800 border border-slate-600 text-white text-xs">Edit</a>
                                    <form action="{{ route('admin.popups.destroy', $popup) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-1 rounded-lg bg-rose-600/80 text-white text-xs">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="p-4 text-center text-slate-400" colspan="8">No popups found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $popups->links() }}</div>
    </form>
</div>

<script>
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            document.querySelectorAll('input[name="ids[]"]').forEach((cb) => { cb.checked = selectAll.checked; });
        });
    }
</script>
@endsection
