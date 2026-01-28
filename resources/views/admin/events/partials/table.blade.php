<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-slate-700/50 bg-slate-800/30 text-xs uppercase tracking-wider text-slate-400 font-bold">
                <th class="px-6 py-4">Event Name</th>
                <th class="px-6 py-4">Date</th>
                <th class="px-6 py-4">Location</th>
                <th class="px-6 py-4">Type</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-700/50">
            @forelse($events as $event)
            <tr class="hover:bg-slate-700/20 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        @if($event->banner_image)
                            <img src="{{ $event->banner_image }}" class="w-10 h-10 rounded-lg object-cover bg-slate-700">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-slate-700 flex items-center justify-center text-slate-500">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        @endif
                        <div>
                            <div class="font-bold text-white group-hover:text-neon transition-colors">{{ $event->name }}</div>
                            <div class="text-xs text-slate-500">{{ $event->organizer_name ?? 'Unknown Organizer' }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-slate-300">
                    {{ $event->event_date->format('d M Y') }}
                    <div class="text-xs text-slate-500">{{ $event->start_time ? $event->start_time->format('H:i') : '' }}</div>
                </td>
                <td class="px-6 py-4 text-slate-300">
                    {{ $event->city ? $event->city->name : $event->location_name }}
                </td>
                <td class="px-6 py-4 text-slate-300">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-300">
                        {{ $event->raceType ? $event->raceType->name : '-' }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        {{ $event->status === 'published' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 
                          ($event->status === 'draft' ? 'bg-slate-500/10 text-slate-400 border border-slate-500/20' : 
                          'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                        {{ ucfirst($event->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('running-event.detail', $event->slug) }}" target="_blank" class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-colors" title="View Public Page">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </a>
                        <a href="{{ route('admin.events.edit', $event) }}" class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-blue-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </a>
                        <form action="{{ route('admin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-red-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                    No events found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="px-6 py-4 border-t border-slate-700/50">
    {{ $events->links() }}
</div>