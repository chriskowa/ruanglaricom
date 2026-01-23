    <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-700/50 bg-slate-800/30 text-xs uppercase tracking-wider text-slate-400 font-bold">
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Joined</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-700/20 transition-colors group">
                        <td class="px-6 py-4 cursor-pointer" @click="openModal(@js($user))">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold overflow-hidden relative">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/'.preg_replace('/^(\/)?storage\//', '', $user->avatar)) }}" 
                                            alt="{{ $user->name }}" 
                                            class="w-full h-full object-cover">
                                    @else
                                        {{ substr($user->name, 0, 1) }}
                                    @endif

                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold text-white group-hover:text-blue-400 transition-colors flex items-center gap-2">
                                        {{ $user->name }}
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $user->role === 'admin' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 
                                  ($user->role === 'coach' ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' : 
                                  'bg-blue-500/10 text-blue-400 border border-blue-500/20') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $user->is_active ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-400' : 'bg-slate-400' }}"></span>
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                
                                @if(auth()->id() !== $user->id)
                                    <!-- Impersonate -->
                                    <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-colors" title="Login as {{ $user->name }}" onclick="return confirm('Are you sure you want to login as this user?')">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                        </button>
                                    </form>

                                    <!-- Toggle Status -->
                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 rounded-lg hover:bg-slate-700 {{ $user->is_active ? 'text-green-400 hover:text-red-400' : 'text-slate-500 hover:text-green-400' }} transition-colors" 
                                            title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                            onclick="return confirm('Are you sure you want to {{ $user->is_active ? 'deactivate' : 'activate' }} this user?')">
                                            @if($user->is_active)
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            @endif
                                        </button>
                                    </form>
                                @endif
                                
                                <button @click="openModal(@js($user))" class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-blue-400 transition-colors" title="Edit Profile">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 mb-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                <p class="text-lg font-medium">No users found</p>
                                <p class="text-sm">Try adjusting your search or filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-700/50">
            {{ $users->links() }}
        </div>
    </div>