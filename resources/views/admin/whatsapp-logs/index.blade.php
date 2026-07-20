@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'WhatsApp Logs')

@section('content')
<div class="px-4 py-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">WhatsApp Logs</h1>
            <p class="text-sm text-slate-400">Riwayat pesan WhatsApp yang dikirim melalui sistem.</p>
        </div>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-400">Cari (nomor / pesan)</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Contoh: 62812..."
                class="w-64 rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-400">Status</label>
            <select name="status"
                class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none">
                <option value="all" @if(request('status', 'all') === 'all') selected @endif>Semua</option>
                @foreach ($statuses as $st)
                    <option value="{{ $st }}" @if(request('status') === $st) selected @endif>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Filter</button>
        <a href="{{ route('admin.whatsapp-logs.index') }}"
            class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Reset</a>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-700 bg-slate-900">
        <table class="min-w-full divide-y divide-slate-700 text-sm">
            <thead class="bg-slate-800 text-left text-xs uppercase tracking-wider text-slate-400">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">To</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">HTTP</th>
                    <th class="px-4 py-3">Message</th>
                    <th class="px-4 py-3">Sent At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @if (count($logs) > 0)
                    @foreach ($logs as $log)
                    <tr class="text-slate-300 hover:bg-slate-800/50">
                        <td class="px-4 py-3 text-slate-500">{{ $log->id }}</td>
                        <td class="px-4 py-3 font-mono text-slate-200">{{ $log->to }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $log->status === 'sent' ? 'bg-green-500/20 text-green-400' : ($log->status === 'failed' ? 'bg-red-500/20 text-red-400' : ($log->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-slate-600/30 text-slate-300')) }}">{{ ucfirst($log->status) }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $log->http_code ?? '-' }}</td>
                        <td class="max-w-md px-4 py-3">
                            <div class="line-clamp-3 whitespace-pre-wrap break-words text-slate-300">{{ $log->message }}</div>
                            @if ($log->error_message)
                                <div class="mt-1 text-xs text-red-400">Err: {{ $log->error_message }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-400">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada log WhatsApp.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
