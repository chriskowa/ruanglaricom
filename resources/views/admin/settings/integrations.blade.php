@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Platform Settings')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans text-slate-200" x-data="{ 
    activeTab: 'general',
    bankAccounts: {{ json_encode($settings['moota_bank_accounts'] ?? []) }},
    addAccount() {
        this.bankAccounts.push({ bank_type: 'bca', name: '', account_number: '', bank_id: '' });
    },
    removeAccount(index) {
        this.bankAccounts.splice(index, 1);
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black italic tracking-tighter text-white">PLATFORM <span class="text-primary">SETTINGS</span></h1>
            <p class="text-slate-400 mt-1">Manage general configuration, financials, and integrations.</p>
        </div>
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="#" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-primary">Admin</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-slate-500 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-white md:ml-2">Settings</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex flex-wrap gap-2 mb-8 border-b border-slate-800 pb-1">
        <button @click="activeTab = 'general'" 
            :class="activeTab === 'general' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700'"
            class="px-6 py-3 border-b-2 font-bold text-sm tracking-wide transition-colors">
            GENERAL & SOCIALS
        </button>
        <button @click="activeTab = 'finance'" 
            :class="activeTab === 'finance' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700'"
            class="px-6 py-3 border-b-2 font-bold text-sm tracking-wide transition-colors">
            FINANCE
        </button>
        <button @click="activeTab = 'integrations'" 
            :class="activeTab === 'integrations' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700'"
            class="px-6 py-3 border-b-2 font-bold text-sm tracking-wide transition-colors">
            INTEGRATIONS (SEO/ADS)
        </button>
        <button @click="activeTab = 'whatsapp'" 
            :class="activeTab === 'whatsapp' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700'"
            class="px-6 py-3 border-b-2 font-bold text-sm tracking-wide transition-colors">
            WHATSAPP GATEWAY
        </button>
    </div>

    <!-- Content -->
    <div class="max-w-4xl">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 md:p-8 shadow-xl relative overflow-hidden">
            <!-- Neon Glow Effect -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>

            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.integration.settings.update') }}" method="POST">
                @csrf
                
                <!-- General Tab -->
                <div x-show="activeTab === 'general'" class="space-y-6">
                    <div class="flex items-center gap-3 mb-8 border-b border-slate-800 pb-4">
                        <div class="p-2 bg-slate-800 rounded-lg text-primary">
                            <i class="flaticon-381-internet text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-white">General Information</h2>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">Site Title</label>
                            <input type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                name="site_title" value="{{ $settings['site_title'] }}">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">Tagline</label>
                            <input type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                name="site_tagline" value="{{ $settings['site_tagline'] }}">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6 pt-4 border-t border-slate-800/50">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">Contact Email</label>
                            <input type="email" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                name="contact_email" value="{{ $settings['contact_email'] }}">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">WhatsApp Number</label>
                            <input type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                name="contact_whatsapp" placeholder="628123456789" value="{{ $settings['contact_whatsapp'] }}">
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-slate-800/50">
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Social Media Links</h3>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-pink-500"><i class="fab fa-instagram"></i></div>
                                <input type="url" class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-primary transition" 
                                    name="social_instagram" placeholder="https://instagram.com/..." value="{{ $settings['social_instagram'] }}">
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-white"><i class="fab fa-tiktok"></i></div>
                                <input type="url" class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-primary transition" 
                                    name="social_tiktok" placeholder="https://tiktok.com/@..." value="{{ $settings['social_tiktok'] }}">
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-blue-500"><i class="fab fa-facebook-f"></i></div>
                                <input type="url" class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-primary transition" 
                                    name="social_facebook" placeholder="https://facebook.com/..." value="{{ $settings['social_facebook'] }}">
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-red-500"><i class="fab fa-youtube"></i></div>
                                <input type="url" class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-primary transition" 
                                    name="social_youtube" placeholder="https://youtube.com/..." value="{{ $settings['social_youtube'] }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Finance Tab -->
                <div x-show="activeTab === 'finance'" class="space-y-6" style="display: none;">
                    <div class="flex items-center gap-3 mb-8 border-b border-slate-800 pb-4">
                        <div class="p-2 bg-slate-800 rounded-lg text-primary">
                            <i class="flaticon-381-controls-3 text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-white">Financial Configuration</h2>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">Platform Fee (%)</label>
                            <div class="relative">
                                <input type="number" step="0.01" class="w-full bg-slate-950 border border-slate-700 rounded-xl pl-4 pr-12 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                    name="platform_fee_percent" value="{{ $settings['platform_fee_percent'] }}">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <span class="text-slate-500 font-bold">%</span>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500">Persentase potongan admin untuk setiap transaksi marketplace/event.</p>
                        </div>
                    </div>

                    <!-- Moota Integration -->
                    <div class="pt-8 mt-8 border-t border-slate-800">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-slate-800 rounded-lg text-green-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 class="text-lg font-bold text-white">Moota Payment Integration</h3>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="moota_is_active" value="0">
                                    <input type="checkbox" name="moota_is_active" value="1" class="sr-only peer" {{ $settings['moota_is_active'] ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                    <span class="ml-3 text-sm font-medium text-slate-300">Enable Moota Payment</span>
                                </label>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">API Token</label>
                                    <input type="password" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                        name="moota_api_token" value="{{ $settings['moota_api_token'] }}" placeholder="Enter Moota API Token">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">Webhook Secret</label>
                                    <input type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                        name="moota_webhook_secret" value="{{ $settings['moota_webhook_secret'] }}" placeholder="Random Secret String">
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">Bank ID (Optional)</label>
                                <input type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                    name="moota_bank_id" value="{{ $settings['moota_bank_id'] }}" placeholder="Specific Bank ID from Moota">
                                <p class="text-xs text-slate-500">Leave empty to accept all banks connected to Moota.</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">Payment Instructions</label>
                                <textarea rows="4" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                    name="moota_instructions" placeholder="Silakan transfer ke rekening BCA 1234567890 a.n PT Ruang Lari...">{{ $settings['moota_instructions'] }}</textarea>
                            </div>

                            <!-- Moota Bank Accounts Editor -->
                            <div class="space-y-4 pt-4 border-t border-slate-800/50">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <label class="text-sm font-bold text-slate-300 uppercase tracking-wider block">Moota Bank Accounts</label>
                                        <span class="text-xs text-slate-500">Add the bank accounts where users should transfer funds.</span>
                                    </div>
                                    <button type="button" @click="addAccount()" class="bg-primary/10 hover:bg-primary/20 text-primary border border-primary/20 hover:border-primary/40 font-bold py-1.5 px-4 rounded-xl text-xs transition flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        Add Bank Account
                                    </button>
                                </div>

                                <input type="hidden" name="moota_bank_accounts" :value="JSON.stringify(bankAccounts)">

                                <div class="space-y-3">
                                    <template x-for="(acc, index) in bankAccounts" :key="index">
                                        <div class="bg-slate-950/40 border border-slate-800 rounded-xl p-4 relative group space-y-3">
                                            <button type="button" @click="removeAccount(index)" class="absolute top-4 right-4 text-slate-500 hover:text-red-400 transition" title="Remove Account">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>

                                            <div class="grid md:grid-cols-4 gap-4">
                                                <div class="space-y-1">
                                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Bank Type</label>
                                                    <select class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-primary transition" 
                                                        x-model="acc.bank_type">
                                                        <option value="bca">BCA</option>
                                                        <option value="mandiri">Mandiri</option>
                                                        <option value="bni">BNI</option>
                                                        <option value="bri">BRI</option>
                                                        <option value="cimb">CIMB Niaga</option>
                                                        <option value="permata">Permata</option>
                                                        <option value="other">Other / E-Wallet</option>
                                                    </select>
                                                </div>

                                                <div class="space-y-1 md:col-span-2">
                                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Account Number</label>
                                                    <input type="text" placeholder="e.g. 3312233671" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-primary transition" 
                                                        x-model="acc.account_number" required>
                                                </div>

                                                <div class="space-y-1">
                                                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Bank ID (Dashboard)</label>
                                                    <input type="text" placeholder="e.g. bpPkB9d4WB2" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-primary transition" 
                                                        x-model="acc.bank_id">
                                                </div>
                                            </div>

                                            <div class="space-y-1">
                                                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Account Name</label>
                                                <input type="text" placeholder="e.g. PT RUANG LARI" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-primary transition" 
                                                    x-model="acc.name" required>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="bankAccounts.length === 0">
                                        <div class="p-6 bg-slate-950/20 rounded-xl border border-dashed border-slate-800 text-center text-slate-500 text-sm">
                                            No bank accounts added. Click "Add Bank Account" to configure bank details for Moota payments.
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <div class="p-4 bg-slate-800/50 rounded-xl border border-slate-700/50">
                                <p class="text-sm text-slate-400">Webhook URL: <code class="text-primary">{{ url('/api/moota/webhook') }}</code></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Integrations Tab -->
                <div x-show="activeTab === 'integrations'" class="space-y-6" style="display: none;">
                    <div class="flex items-center gap-3 mb-8 border-b border-slate-800 pb-4">
                        <div class="p-2 bg-slate-800 rounded-lg text-primary">
                            <i class="flaticon-381-settings-2 text-xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-white">Tracking & SEO Integrations</h2>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4 md:gap-8 items-start">
                        <label class="text-sm font-bold text-slate-300 uppercase tracking-wider pt-2">Google Analytics<br><span class="text-xs font-normal text-slate-500 normal-case">(Measurement ID)</span></label>
                        <div class="md:col-span-2 space-y-2">
                            <input type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                name="google_analytics" placeholder="G-XXXXXXXXXX" value="{{ $settings['google_analytics'] }}">
                            <p class="text-xs text-slate-500">Enter your GA4 Measurement ID.</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4 md:gap-8 items-start border-t border-slate-800/50 pt-6">
                        <label class="text-sm font-bold text-slate-300 uppercase tracking-wider pt-2">Google Search Console</label>
                        <div class="md:col-span-2 space-y-2">
                            <input type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                name="google_search_console" placeholder="HTML Tag Content" value="{{ $settings['google_search_console'] }}">
                            <p class="text-xs text-slate-500">Enter only the <code class="bg-slate-800 px-1 py-0.5 rounded text-primary">content</code> value of the meta tag named <code class="text-slate-400">google-site-verification</code>.</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4 md:gap-8 items-start border-t border-slate-800/50 pt-6">
                        <label class="text-sm font-bold text-slate-300 uppercase tracking-wider pt-2">Bing Search Console</label>
                        <div class="md:col-span-2 space-y-2">
                            <input type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                name="bing_search_console" placeholder="HTML Tag Content" value="{{ $settings['bing_search_console'] }}">
                            <p class="text-xs text-slate-500">Enter only the <code class="bg-slate-800 px-1 py-0.5 rounded text-primary">content</code> value of the meta tag named <code class="text-slate-400">msvalidate.01</code>.</p>
                        </div>
                    </div>

                     <div class="grid md:grid-cols-3 gap-4 md:gap-8 items-start border-t border-slate-800/50 pt-6">
                        <label class="text-sm font-bold text-slate-300 uppercase tracking-wider pt-2">Google Ads<br><span class="text-xs font-normal text-slate-500 normal-case">(Conversion ID)</span></label>
                        <div class="md:col-span-2 space-y-2">
                            <input type="text" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                name="google_ads_tag" placeholder="AW-XXXXXXXXXX" value="{{ $settings['google_ads_tag'] }}">
                            <p class="text-xs text-slate-500">Enter your Google Ads Conversion ID.</p>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Tab -->
                <div x-show="activeTab === 'whatsapp'" class="space-y-6" style="display: none;">
                    <div class="flex items-center gap-3 mb-8 border-b border-slate-800 pb-4">
                        <div class="p-2 bg-slate-800 rounded-lg text-primary">
                            <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.536 0 1.52 1.115 2.988 1.264 3.186.149.198 2.19 3.361 5.27 4.69 2.151.928 2.988.94 3.518.865.592-.084 1.758-.717 2.006-1.41.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.381a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        </div>
                        <h2 class="text-xl font-bold text-white">WhatsApp Gateway Settings</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="whatsapp_is_active" value="0">
                                <input type="checkbox" name="whatsapp_is_active" value="1" class="sr-only peer" {{ $settings['whatsapp_is_active'] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                <span class="ml-3 text-sm font-medium text-slate-300">Enable WhatsApp Gateway</span>
                            </label>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">App Key</label>
                                <input type="password" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                    name="whatsapp_app_key" value="{{ $settings['whatsapp_app_key'] }}" placeholder="Enter WhatsApp App Key">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-300 uppercase tracking-wider">Auth Key</label>
                                <input type="password" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition placeholder-slate-600" 
                                    name="whatsapp_auth_key" value="{{ $settings['whatsapp_auth_key'] }}" placeholder="Enter WhatsApp Auth Key">
                            </div>
                        </div>

                        <div class="p-4 bg-slate-800/50 rounded-xl border border-slate-700/50 text-slate-400 text-xs leading-relaxed space-y-1">
                            <span class="font-bold text-white block mb-1">Catatan Integrasi:</span>
                            <p>• Menggunakan provider gateway <a href="https://wa.jituproperty.com/" target="_blank" class="text-primary hover:underline font-semibold">Jitu Property</a>.</p>
                            <p>• Kredensial di atas akan digunakan sebagai preferensi utama. Jika kosong, sistem akan menggunakan nilai fallback dari file konfigurasi env.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-8 border-t border-slate-800 mt-8">
                    <button type="submit" class="bg-primary hover:bg-lime-400 text-slate-900 font-bold py-3 px-8 rounded-xl transition transform hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-primary/20">
                        Save All Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
