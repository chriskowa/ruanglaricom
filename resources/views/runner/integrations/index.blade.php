@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Integrasi Layanan & Akun - RuangLari')

@push('styles')
<style>
    .font-heading {
        font-family: 'Inter Tight', 'Sora', sans-serif;
    }
    .strava-color {
        color: #FC4C02;
    }
    .strava-badge {
        background-color: rgba(252, 76, 2, 0.12);
        border: 1px solid rgba(252, 76, 2, 0.28);
    }
    .strava-button {
        background-color: #FC4C02;
        color: #ffffff;
    }
    .strava-button:hover {
        background-color: #e34402;
    }
</style>
@endpush

@section('content')
<main class="min-h-screen pt-0 pb-16 px-4 md:px-8 font-sans bg-slate-950 text-slate-200">
    <div class="max-w-5xl mx-auto space-y-6 pt-6">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                    <a href="{{ route('runner.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-200">Integrasi</span>
                </div>
                <h1 class="text-2xl font-bold text-white font-heading tracking-tight">
                    Integrasi Akun & Layanan
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    Pusat kendali koneksi aplikasi eksternal, sinkronisasi telemetri latihan, dan status autentikasi akun Anda.
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($stravaData['is_connected'])
                    <form action="{{ route('runner.strava.sync') }}" method="POST" onsubmit="this.querySelector('button').disabled = true;">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-md bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800 text-xs font-semibold transition flex items-center gap-2">
                            <i class="fas fa-rotate"></i>
                            <span>Sinkronisasi Strava</span>
                        </button>
                    </form>
                @endif
                <a href="{{ route('runner.profile') }}" class="px-3 py-2 rounded-md bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-semibold transition">
                    Edit Profil
                </a>
            </div>
        </div>

        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-md bg-emerald-950/40 border border-emerald-800/40 text-xs text-emerald-300 flex items-start gap-3">
                <i class="fas fa-circle-check mt-0.5 text-sm shrink-0"></i>
                <div class="leading-relaxed">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-md bg-rose-950/40 border border-rose-800/40 text-xs text-rose-300 flex items-start gap-3">
                <i class="fas fa-circle-exclamation mt-0.5 text-sm shrink-0"></i>
                <div class="leading-relaxed">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 rounded-md bg-sky-950/40 border border-sky-800/40 text-xs text-sky-300 flex items-start gap-3">
                <i class="fas fa-circle-info mt-0.5 text-sm shrink-0"></i>
                <div class="leading-relaxed">{{ session('info') }}</div>
            </div>
        @endif

        <!-- Integration Grid -->
        <div class="grid grid-cols-1 gap-6">

            <!-- 1. STRAVA INTEGRATION CARD -->
            <div class="bg-slate-900 rounded-lg border border-slate-800 p-5 md:p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-800/80">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-md strava-badge flex items-center justify-center shrink-0">
                            <i class="fab fa-strava text-2xl strava-color"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-semibold text-white font-heading">Strava Activity Sync</h2>
                                @if($stravaData['is_connected'])
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-800/40">
                                        Terhubung
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                        Belum Terhubung
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Sinkronisasi otomatis jarak, moving time, heart rate zone, split KM, dan elevasi dari jam tangan atlet.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($stravaData['is_connected'])
                            <form action="{{ route('runner.strava.disconnect') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan integrasi dengan Strava?');">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-md bg-slate-950 hover:bg-rose-950 text-slate-300 hover:text-rose-400 border border-slate-800 hover:border-rose-800 text-xs font-semibold transition">
                                    Putuskan Koneksi
                                </button>
                            </form>
                        @else
                            <a href="{{ route('runner.strava.connect') }}" class="px-4 py-2 rounded-md strava-button text-xs font-bold transition flex items-center gap-2">
                                <i class="fab fa-strava"></i>
                                <span>Hubungkan Akun Strava</span>
                            </a>
                        @endif
                    </div>
                </div>

                @if($stravaData['is_connected'])
                    <!-- Telemetry & Status Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-slate-950 p-3.5 rounded-md border border-slate-800">
                            <div class="text-xs text-slate-400">Total Sesi Tersinkron</div>
                            <div class="text-lg font-bold font-mono text-white mt-1">
                                {{ number_format($stravaData['synced_count']) }} <span class="text-xs font-normal text-slate-400">aktivitas</span>
                            </div>
                        </div>

                        <div class="bg-slate-950 p-3.5 rounded-md border border-slate-800">
                            <div class="text-xs text-slate-400">Strava Athlete ID</div>
                            <div class="text-sm font-semibold font-mono text-white mt-1.5 truncate">
                                {{ $stravaData['athlete_id'] ?: '-' }}
                            </div>
                            @if($stravaData['athlete_url'])
                                <a href="{{ $stravaData['athlete_url'] }}" target="_blank" rel="noopener noreferrer" class="text-[11px] strava-color hover:underline inline-flex items-center gap-1 mt-1 font-mono">
                                    <span>Buka Profil Strava</span>
                                    <i class="fas fa-arrow-up-right-from-square text-[9px]"></i>
                                </a>
                            @endif
                        </div>

                        <div class="bg-slate-950 p-3.5 rounded-md border border-slate-800">
                            <div class="text-xs text-slate-400">Status Otorisasi Token</div>
                            <div class="text-sm font-semibold font-mono text-emerald-400 mt-1.5 flex items-center gap-1.5">
                                <i class="fas fa-shield-halved text-xs"></i>
                                <span>{{ $stravaData['is_expired'] ? 'Perlu Refresh Otomatis' : 'Aktif & Valid' }}</span>
                            </div>
                            @if($stravaData['expires_at'])
                                <div class="text-[11px] text-slate-400 mt-1 font-mono">
                                    Token aktif hingga: {{ $stravaData['expires_at']->format('d M Y, H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Last Activity Highlight -->
                    @if($stravaData['last_activity'])
                        @php
                            $act = $stravaData['last_activity'];
                            $distKm = $act->distance_m ? round(((float)$act->distance_m) / 1000, 2) : 0;
                            $durMins = $act->moving_time_s ? gmdate($act->moving_time_s >= 3600 ? 'H:i:s' : 'i:s', $act->moving_time_s) : '-';
                            $pace = app(\App\Services\StravaApiService::class)->formatPaceFromSpeed($act->average_speed);
                            $dateStr = $act->local_start_date ? $act->local_start_date->format('d M Y, H:i') : ($act->start_date ? $act->start_date->format('d M Y, H:i') : '-');
                        @endphp
                        <div class="bg-slate-950/70 p-4 rounded-md border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Aktivitas Terakhir Tersinkron</div>
                                <div class="text-sm font-bold text-white">{{ $act->name }}</div>
                                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-300 font-mono">
                                    <span>{{ $dateStr }}</span>
                                    <span class="text-slate-600">•</span>
                                    <span>{{ $distKm }} km</span>
                                    <span class="text-slate-600">•</span>
                                    <span>Pace {{ $pace ?: '-' }} /km</span>
                                    <span class="text-slate-600">•</span>
                                    <span>Durasi {{ $durMins }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('runner.calendar') }}" class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-800 text-xs font-semibold transition">
                                    Buka di Kalender
                                </a>
                                @if($act->strava_activity_id)
                                    <a href="https://www.strava.com/activities/{{ $act->strava_activity_id }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 rounded-md bg-slate-900 hover:bg-slate-800 strava-color border border-slate-800 text-xs font-semibold transition flex items-center gap-1">
                                        <span>Strava</span>
                                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Not Connected Explanation -->
                    <div class="bg-slate-950 p-4 rounded-md border border-slate-800 text-xs text-slate-300 leading-relaxed space-y-2">
                        <div class="font-semibold text-white">Keuntungan Menghubungkan Akun Strava:</div>
                        <ul class="space-y-1 list-disc list-inside text-slate-300">
                            <li>Setiap kali Anda selesai berlari menggunakan Garmin, Coros, Apple Watch, atau ponsel, data langsung masuk ke RuangLari.</li>
                            <li>Sesi program latihan di kalender Anda ditandai selesai (*completed*) secara otomatis jika jarak dan tanggal sesuai.</li>
                            <li>Analisis AI RuangLari dapat mendeteksi interval, heart rate zone (Z1 - Z5), dan konsistensi pacing secara komprehensif.</li>
                        </ul>
                    </div>
                @endif
            </div>

            <!-- 2. EMAIL INTEGRATION CARD -->
            <div class="bg-slate-900 rounded-lg border border-slate-800 p-5 md:p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-800/80">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-md bg-sky-500/10 border border-sky-500/20 flex items-center justify-center shrink-0">
                            <i class="fas fa-envelope text-xl text-sky-400"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-semibold text-white font-heading">Akun Email Utama</h2>
                                @if($emailData['is_verified'])
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-800/40">
                                        Terverifikasi
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-800/40">
                                        Belum Diverifikasi
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Identitas utama untuk autentikasi masuk, konfirmasi pembayaran, dan korespondensi resmi platform.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(!$emailData['is_verified'])
                            <form action="{{ route('runner.integrations.verify-email') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 rounded-md bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold text-xs transition flex items-center gap-1.5">
                                    <i class="fas fa-paper-plane text-xs"></i>
                                    <span>Kirim Verifikasi</span>
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('runner.profile') }}" class="px-3 py-2 rounded-md bg-slate-950 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-semibold transition">
                            Perbarui Email
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-slate-950 p-3.5 rounded-md border border-slate-800">
                        <div class="text-xs text-slate-400">Alamat Email Terdaftar</div>
                        <div class="text-sm font-semibold font-mono text-white mt-1.5 truncate">
                            {{ $emailData['address'] }}
                        </div>
                    </div>

                    <div class="bg-slate-950 p-3.5 rounded-md border border-slate-800">
                        <div class="text-xs text-slate-400">Status Keamanan</div>
                        <div class="text-sm font-semibold font-mono mt-1.5 flex items-center gap-1.5 {{ $emailData['is_verified'] ? 'text-emerald-400' : 'text-amber-400' }}">
                            <i class="fas {{ $emailData['is_verified'] ? 'fa-circle-check' : 'fa-triangle-exclamation' }} text-xs"></i>
                            <span>{{ $emailData['is_verified'] ? 'Email Terverifikasi' : 'Menunggu Verifikasi' }}</span>
                        </div>
                        @if($emailData['verified_at'])
                            <div class="text-[11px] text-slate-400 mt-1 font-mono">
                                Tanggal: {{ $emailData['verified_at']->format('d M Y, H:i') }}
                            </div>
                        @endif
                    </div>

                    <div class="bg-slate-950 p-3.5 rounded-md border border-slate-800">
                        <div class="text-xs text-slate-400">Terdaftar Sejak</div>
                        <div class="text-sm font-semibold font-mono text-white mt-1.5">
                            {{ $emailData['created_at'] ? $emailData['created_at']->format('d M Y') : '-' }}
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1">
                            Akun Pelari RuangLari
                        </div>
                    </div>
                </div>

                <div class="bg-slate-950 p-4 rounded-md border border-slate-800 text-xs text-slate-300 leading-relaxed">
                    <span class="font-semibold text-white">Notifikasi Email Transaksional:</span>
                    Invoice pembelian program latihan, konfirmasi pembayaran saldo/tiket event, serta bukti penerimaan dana dikirimkan otomatis ke alamat email ini.
                </div>
            </div>

            <!-- 3. WHATSAPP NOTIFICATIONS CARD -->
            <div class="bg-slate-900 rounded-lg border border-slate-800 p-5 md:p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-800/80">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-md bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center shrink-0">
                            <i class="fab fa-whatsapp text-2xl text-emerald-400"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-semibold text-white font-heading">WhatsApp Reminder</h2>
                                @if($waData['has_phone'])
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $waData['is_receive_wa'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-800/40' : 'bg-slate-800 text-slate-300 border border-slate-700' }}">
                                        {{ $waData['is_receive_wa'] ? 'Pengingat Aktif' : 'Nomor Tersimpan' }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                        Nomor Belum Diatur
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Pengiriman notifikasi pengingat sesi latihan pagi/sore dan instruksi latihan langsung ke WhatsApp.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($waData['has_phone'])
                            <form action="{{ route('runner.profile.toggle-wa') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-2 rounded-md {{ $waData['is_receive_wa'] ? 'bg-slate-950 hover:bg-slate-800 text-slate-300 border border-slate-800' : 'bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold' }} text-xs transition">
                                    {{ $waData['is_receive_wa'] ? 'Nonaktifkan Pengingat' : 'Aktifkan Pengingat WA' }}
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('runner.profile') }}" class="px-3 py-2 rounded-md bg-slate-950 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-semibold transition">
                            {{ $waData['has_phone'] ? 'Ubah Nomor' : 'Tambah Nomor WhatsApp' }}
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-slate-950 p-3.5 rounded-md border border-slate-800">
                        <div class="text-xs text-slate-400">Nomor WhatsApp Aktif</div>
                        <div class="text-sm font-semibold font-mono text-white mt-1.5">
                            {{ $waData['phone'] ?: 'Belum ditambahkan' }}
                        </div>
                    </div>

                    <div class="bg-slate-950 p-3.5 rounded-md border border-slate-800">
                        <div class="text-xs text-slate-400">Status Langganan Pengingat</div>
                        <div class="text-sm font-semibold font-mono mt-1.5 flex items-center gap-1.5 {{ $waData['is_receive_wa'] ? 'text-emerald-400' : 'text-slate-400' }}">
                            <i class="fas {{ $waData['is_receive_wa'] ? 'fa-bell' : 'fa-bell-slash' }} text-xs"></i>
                            <span>{{ $waData['is_receive_wa'] ? 'Menerima Pengingat Sesi Latihan' : 'Notifikasi Dinonaktifkan' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. PRIVACY & DATA TRANSPARENCY NOTICE -->
            <div class="p-4 bg-slate-900/60 rounded-lg border border-slate-850 text-xs text-slate-400 space-y-1">
                <div class="font-semibold text-slate-300 flex items-center gap-1.5">
                    <i class="fas fa-lock text-[11px]"></i>
                    <span>Keamanan & Transparansi Data Telemetri</span>
                </div>
                <p class="leading-relaxed">
                    Data aktivitas, split waktu, dan zona denyut jantung dari Strava disimpan secara privat dan terenkripsi pada infrastruktur RuangLari. Data ini hanya digunakan untuk mengevaluasi program latihan Anda dan tidak pernah dijual atau diserahkan kepada pihak ketiga tanpa persetujuan eksplisit.
                </p>
            </div>

        </div>

    </div>
</main>
@endsection
