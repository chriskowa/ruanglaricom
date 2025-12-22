<div class="p-3">
    <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-bold text-white">Notifications</h4>
        <a href="{{ route('notifications.read-all') }}" class="text-xs text-cyan-400 hover:underline">Mark all as read</a>
    </div>
    <div class="space-y-2 max-h-64 overflow-y-auto">
        <a href="{{ route('notifications.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-800">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                <span class="text-sm text-slate-300">You have new updates in your feed</span>
            </div>
            <span class="text-[10px] text-slate-500">Just now</span>
        </a>
        <a href="{{ route('chat.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-800">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                <span class="text-sm text-slate-300">New message from Coach</span>
            </div>
            <span class="text-[10px] text-slate-500">2m ago</span>
        </a>
        <a href="{{ route('runner.calendar') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-800">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                <span class="text-sm text-slate-300">Upcoming workout in your calendar</span>
            </div>
            <span class="text-[10px] text-slate-500">Today</span>
        </a>
    </div>
    <div class="mt-3">
        <a href="{{ route('notifications.index') }}" class="block w-full text-center px-3 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700 text-sm">View all</a>
    </div>
</div>
