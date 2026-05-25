@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Admin Dashboard')

@section('content')
<div id="admin-dashboard-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center gap-3 relative z-10" data-aos="fade-down">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Hero Section -->
    <div class="mb-10 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <p class="text-red-500 font-mono text-sm tracking-widest uppercase mb-1">System Administration</p>
                <h1 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter">
                    {{ strtoupper(auth()->user()->name) }}
                </h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-red-500 hover:text-red-500 transition-all font-bold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Manage Users
                </a>
                <a href="#" class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 text-white font-black hover:scale-105 transition-all shadow-lg shadow-red-500/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    System Health
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-10 relative z-10">
        
        <!-- Total Users -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-blue-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-blue-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Total Users</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ \App\Models\User::count() }}</h3>
            <div class="mt-2 text-xs text-blue-400 font-bold">Active Accounts</div>
        </div>

        <!-- Total Programs -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-purple-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-purple-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Programs</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ \App\Models\Program::count() }}</h3>
            <div class="mt-2 text-xs text-purple-400 font-bold">Training Plans</div>
        </div>

        <!-- Total Events -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-yellow-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-yellow-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Events</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ \App\Models\Event::count() }}</h3>
            <div class="mt-2 text-xs text-yellow-400 font-bold">Races Organized</div>
        </div>

        <!-- System Status -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-green-400/50 transition-all group relative overflow-hidden">
            <div class="absolute right-0 bottom-0 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-green-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            </div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-green-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">System Status</span>
            </div>
            <h3 class="text-2xl font-bold text-white">Online</h3>
            <div class="mt-2 text-xs text-green-400 font-bold">All services operational</div>
        </div>

        <!-- WhatsApp Gateway Status Card -->
        @php($waActive = (bool) \App\Models\AppSettings::get('whatsapp_is_active', false))
        <div onclick="openWhatsappSettingsModal()" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-green-400/50 transition-all group cursor-pointer relative overflow-hidden">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-green-400 transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.253 8.477 3.52 2.262 2.268 3.51 5.28 3.507 8.483-.006 6.66-5.344 11.997-11.957 11.997a11.903 11.903 0 01-5.688-1.448L.057 24zm6.305-1.655a9.882 9.882 0 005.683 1.449h.005c5.46 0 9.902-4.443 9.907-9.908.002-2.65-1.03-5.14-2.898-7.01C17.19 5.006 14.7 3.972 12.05 3.972c-5.462 0-9.904 4.443-9.909 9.909-.001 2.09.547 4.12 1.588 5.925l-.235-.382-3.742.983.998-3.648-.214-.361a9.824 9.824 0 01-1.378-5.03c.004-4.887 3.51-8.91 8.413-9.81z"/>
                    </svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">WhatsApp Gateway</span>
            </div>
            <h3 class="text-2xl font-bold {{ $waActive ? 'text-green-400' : 'text-slate-400' }}">
                {{ $waActive ? 'Active' : 'Disabled' }}
            </h3>
            <div class="mt-2 text-xs {{ $waActive ? 'text-green-400/70' : 'text-slate-500' }} font-bold">
                {{ $waActive ? 'Click to configure' : 'Click to enable' }}
            </div>
        </div>
    </div>

    <!-- Admin Tools -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Quick Actions -->
        <div class="lg:col-span-2 bg-card/30 border border-slate-700 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-white mb-6">Administrative Tools</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.users.index') }}" class="p-4 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group text-center">
                    <div class="w-12 h-12 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <span class="text-sm font-bold text-slate-300">User Management</span>
                </a>
                <a href="{{ route('admin.transactions.index') }}" class="p-4 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group text-center">
                    <div class="w-12 h-12 rounded-full bg-green-500/10 text-green-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <span class="text-sm font-bold text-slate-300">Transactions</span>
                </a>
                <a href="{{ route('admin.races.index') }}" class="p-4 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group text-center">
                    <div class="w-12 h-12 rounded-full bg-purple-500/10 text-purple-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    </div>
                    <span class="text-sm font-bold text-slate-300">Race Master</span>
                </a>
            </div>
        </div>

        <!-- System Logs -->
        <div class="space-y-6">
            <div class="bg-card/50 border border-slate-700 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-700 pb-2">Recent Logs</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-xs text-slate-300">System backup completed successfully.</p>
                            <p class="text-[10px] text-slate-500">2 mins ago</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-yellow-500 mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-xs text-slate-300">High traffic detected on API endpoint.</p>
                            <p class="text-[10px] text-slate-500">15 mins ago</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-xs text-slate-300">New user registration: John Doe</p>
                            <p class="text-[10px] text-slate-500">1 hour ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Gateway Settings Modal -->
<div id="whatsapp-settings-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeWhatsappSettingsModal()"></div>
    
    <!-- Modal Body -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl overflow-hidden">
            <!-- Decorative Glow -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-green-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                        <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.536 0 1.52 1.115 2.988 1.264 3.186.149.198 2.19 3.361 5.27 4.69 2.151.928 2.988.94 3.518.865.592-.084 1.758-.717 2.006-1.41.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.381a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        WHATSAPP GATEWAY
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">Configure your WhatsApp Jitu Property keys.</p>
                </div>
                <button type="button" onclick="closeWhatsappSettingsModal()" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form action="{{ route('admin.integration.settings.update') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Toggle Switch -->
                <div class="flex items-center justify-between p-4 bg-slate-950/40 border border-slate-800 rounded-xl">
                    <div>
                        <span class="text-sm font-bold text-white block">Status Integrasi</span>
                        <span class="text-[11px] text-slate-400">Aktifkan pengiriman pesan WhatsApp</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="whatsapp_is_active" value="0">
                        <input type="checkbox" name="whatsapp_is_active" value="1" class="sr-only peer" {{ \App\Models\AppSettings::get('whatsapp_is_active', false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-green-400 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </div>

                <!-- App Key -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">App Key</label>
                    <input type="password" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-green-400 transition" 
                        name="whatsapp_app_key" value="{{ \App\Models\AppSettings::get('whatsapp_app_key') }}" placeholder="App Key Jitu Property">
                </div>

                <!-- Auth Key -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Auth Key</label>
                    <input type="password" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-green-400 transition" 
                        name="whatsapp_auth_key" value="{{ \App\Models\AppSettings::get('whatsapp_auth_key') }}" placeholder="Auth Key Jitu Property">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeWhatsappSettingsModal()" class="px-4 py-2 rounded-xl border border-slate-700 hover:bg-slate-800 text-slate-300 text-sm font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-green-500 hover:bg-green-400 text-slate-950 font-black text-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openWhatsappSettingsModal() {
    const modal = document.getElementById('whatsapp-settings-modal');
    modal.classList.remove('hidden');
}

function closeWhatsappSettingsModal() {
    const modal = document.getElementById('whatsapp-settings-modal');
    modal.classList.add('hidden');
}
</script>
@endsection
