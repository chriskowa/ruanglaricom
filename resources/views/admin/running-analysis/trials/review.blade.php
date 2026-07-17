@extends('layouts.pacerhub')

@php
    $withSidebar = true;
    $isAdmin = auth()->user()?->role === 'admin';

    $statusColors = [
        'capturing'       => 'bg-white/[0.035] text-slate-300 border-white/10',
        'queued'          => 'bg-white/[0.035] text-slate-300 border-white/10',
        'analyzing'       => 'bg-white/[0.035] text-slate-300 border-white/10',
        'review_required' => 'bg-white/[0.035] text-slate-200 border-white/10',
        'approved'        => 'bg-[#ccff00]/10 text-[#ccff00] border-[#ccff00]/20',
        'published'       => 'bg-[#ccff00] text-[#07101c] border-[#ccff00]',
        'invalid'         => 'bg-rose-400/10 text-rose-300 border-rose-400/20',
        'failed'          => 'bg-rose-400/10 text-rose-300 border-rose-400/20',
    ];

    $statusLabels = [
        'capturing'       => 'Perekaman',
        'queued'          => 'Dalam Antrean',
        'analyzing'       => 'Sedang Dianalisis',
        'review_required' => 'Perlu Ditinjau',
        'approved'        => 'Disetujui',
        'published'       => 'Dipublikasikan',
        'invalid'         => 'Tidak Valid',
        'failed'          => 'Analisis Gagal',
    ];

    $statusIcons = [
        'capturing'       => 'fa-circle-dot',
        'queued'          => 'fa-clock',
        'analyzing'       => 'fa-spinner fa-spin',
        'review_required' => 'fa-eye',
        'approved'        => 'fa-check',
        'published'       => 'fa-check-double',
        'invalid'         => 'fa-ban',
        'failed'          => 'fa-triangle-exclamation',
    ];

    $statusColor = $statusColors[$trial->status] ?? 'bg-slate-400/10 text-slate-300 border-slate-400/20';
    $statusLabel = $statusLabels[$trial->status] ?? ucwords(str_replace('_', ' ', $trial->status));
    $statusIcon = $statusIcons[$trial->status] ?? 'fa-circle-info';

    $report = $trial->latestReport;
    $narrative = is_array($report?->runner_narrative_json) ? $report->runner_narrative_json : [];
    $coachMessage = $narrative['coach_message'] ?? null;
    $positives = is_array($narrative['positives'] ?? null) ? $narrative['positives'] : [];
    $score = $trial->quality_score !== null ? round((float) $trial->quality_score * 100) : null;

    $toText = function ($value) use (&$toText) {
        if (is_array($value)) {
            return trim(implode(' ', array_filter(array_map(fn ($item) => $toText($item), $value))));
        }
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        return is_scalar($value) ? trim((string) $value) : '';
    };

    $coachMessageText = $toText($coachMessage);

    if ($score === null) {
        $scoreCategory = 'Belum tersedia';
        $scoreDescription = 'Analisis belum menghasilkan skor form.';
        $scoreAccent = 'text-slate-400';
        $scoreRing = 'border-white/10';
        $scoreBar = 'bg-slate-600';
    } elseif ($score >= 85) {
        $scoreCategory = 'Excellent';
        $scoreDescription = 'Teknik lari sangat baik dengan koreksi minimal.';
        $scoreAccent = 'text-white';
        $scoreRing = 'border-white/10';
        $scoreBar = 'bg-[#ccff00]';
    } elseif ($score >= 70) {
        $scoreCategory = 'Good';
        $scoreDescription = 'Fondasi teknik baik; beberapa detail masih dapat ditingkatkan.';
        $scoreAccent = 'text-white';
        $scoreRing = 'border-white/10';
        $scoreBar = 'bg-[#ccff00]';
    } elseif ($score >= 55) {
        $scoreCategory = 'Needs Improvement';
        $scoreDescription = 'Terdapat pola gerak yang perlu dikoreksi secara bertahap.';
        $scoreAccent = 'text-white';
        $scoreRing = 'border-white/10';
        $scoreBar = 'bg-[#ccff00]';
    } else {
        $scoreCategory = 'Priority Correction';
        $scoreDescription = 'Prioritaskan koreksi form sebelum meningkatkan beban latihan.';
        $scoreAccent = 'text-white';
        $scoreRing = 'border-white/10';
        $scoreBar = 'bg-[#ccff00]';
    }

    $severityOrder = ['significant' => 1, 'moderate' => 2, 'minor' => 3];
    $findings = $trial->findings
        ->sortBy(fn ($finding) => $severityOrder[$finding->severity] ?? 99)
        ->values();
    $priorityFindings = $findings->take(3);
    $priorityIssueCount = $findings->whereIn('severity', ['significant', 'moderate'])->count();

    $recommendations = $trial->recommendations;
    $cues = $recommendations->where('type', \App\Models\RunningAnalysis\Recommendation::TYPE_CUE);
    $drills = $recommendations->where('type', \App\Models\RunningAnalysis\Recommendation::TYPE_DRILL);
    $strengths = $recommendations->where('type', \App\Models\RunningAnalysis\Recommendation::TYPE_STRENGTH);

    $metricLabels = [
        'knee_flexion'        => 'Knee Flexion',
        'hip_flexion'         => 'Hip Flexion',
        'hip_drop'            => 'Hip Drop',
        'ground_contact_time' => 'Ground Contact Time',
        'cadence'             => 'Cadence',
        'vertical_oscillation'=> 'Vertical Oscillation',
        'trunk_lean'          => 'Trunk Lean',
        'ankle_dorsiflexion'  => 'Ankle Dorsiflexion',
        'stride_length'       => 'Stride Length',
    ];

    $flattenEvidence = function ($value, $prefix = '') use (&$flattenEvidence) {
        $rows = [];
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $label = is_string($key) ? ucwords(str_replace('_', ' ', $key)) : $prefix;
                $nextPrefix = $prefix && $label ? $prefix . ' · ' . $label : ($label ?: $prefix);
                if (is_array($item)) {
                    $rows = array_merge($rows, $flattenEvidence($item, $nextPrefix));
                } elseif ($item !== null && $item !== '') {
                    $rows[] = [
                        'label' => $nextPrefix ?: 'Evidence',
                        'value' => is_bool($item) ? ($item ? 'Ya' : 'Tidak') : (string) $item,
                    ];
                }
            }
        } elseif ($value !== null && $value !== '') {
            $rows[] = ['label' => $prefix ?: 'Evidence', 'value' => (string) $value];
        }
        return $rows;
    };

    $videoArtifact = $trial->artifacts->where('type', 'video_clip')->first();
    $poseArtifact = $trial->artifacts->where('type', 'pose_landmarks')->first();

    $analysisChecks = [
        'Video' => (bool) $videoArtifact,
        'Pose landmarks' => (bool) $poseArtifact || !empty($poseData),
        'Metrik' => $trial->metrics->count() > 0,
        'Temuan' => $trial->findings->count() > 0,
        'Report' => (bool) $report,
    ];
    $analysisReadiness = (int) round((collect($analysisChecks)->filter()->count() / max(count($analysisChecks), 1)) * 100);

    $pdfRoute = $isAdmin
        ? 'admin.running-analysis.trials.pdf'
        : 'runner.running-analysis.trials.pdf';

    $backUrl = $isAdmin
        ? route('admin.running-analysis.sessions.show', $trial->session)
        : url()->previous();
@endphp

@section('title', 'Review Trial - ' . $trial->runner->name)

@section('content')
<style>
    html { scroll-behavior: smooth; }
    .review-grid-bg { background: #070b12; }
    .review-card {
        background: #0b111c;
        border: 1px solid rgba(148, 163, 184, .12);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .16);
    }
    .review-card-soft {
        background: #0a101a;
        border: 1px solid rgba(148, 163, 184, .10);
    }
    .section-anchor { scroll-margin-top: 110px; }
    .metric-value { font-variant-numeric: tabular-nums; }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { scrollbar-width: none; }
</style>

<div class="min-h-screen bg-[#070b12] text-white pt-20 pb-16 relative overflow-hidden review-grid-bg">

    <div class="relative max-w-[1500px] mx-auto px-4 md:px-8">
        {{-- Breadcrumb and context --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-[#ccff00] transition-colors w-fit">
                <span class="w-8 h-8 rounded-lg border border-white/10 bg-white/[0.03] flex items-center justify-center">
                    <i class="fas fa-arrow-left text-xs"></i>
                </span>
                Kembali ke sesi
            </a>
            <div class="text-[10px] uppercase tracking-[0.24em] text-slate-600 font-bold">
                Running Analysis / Trial Review
            </div>
        </div>

        @if(session('success'))
        <div class="mb-6 rounded-2xl border border-[#ccff00]/20 bg-[#ccff00]/10 px-4 py-3 flex items-start gap-3 text-slate-200">
            <i class="fas fa-circle-check mt-0.5 text-[#ccff00]"></i>
            <div class="text-sm font-medium">{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 rounded-2xl border border-rose-400/20 bg-rose-400/10 px-4 py-3 flex items-start gap-3 text-rose-200">
            <i class="fas fa-triangle-exclamation mt-0.5 text-rose-300"></i>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
        @endif

        {{-- Runner header --}}
        <section class="review-card rounded-2xl p-5 md:p-7 mb-6 relative overflow-hidden">
            <div class="relative flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
                <div class="flex items-start sm:items-center gap-4 min-w-0">
                    <div class="relative shrink-0">
                        <img src="{{ $trial->runner->avatar_url ?? asset('images/default-avatar.png') }}"
                            alt="{{ $trial->runner->name }}"
                            class="w-16 h-16 md:w-20 md:h-20 rounded-2xl border border-white/10 object-cover bg-slate-800">
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-[0.22em] text-[#ccff00]">Trial Review</span>
                            <span class="px-2.5 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $statusColor }}">
                                <i class="fas {{ $statusIcon }} mr-1.5"></i>{{ $statusLabel }}
                            </span>
                        </div>
                        <h1 class="text-2xl md:text-4xl font-bold tracking-[-0.035em] text-white truncate">
                            {{ $trial->runner->name }}
                        </h1>
                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs md:text-sm text-slate-400">
                            <span><i class="fas fa-hashtag mr-1.5 text-slate-600"></i>{{ $trial->attempt_no }}</span>
                            <span><i class="far fa-calendar mr-1.5 text-slate-600"></i>{{ $trial->created_at->format('d M Y, H:i') }}</span>
                            @if($trial->camera_width)
                                <span><i class="fas fa-camera mr-1.5 text-slate-600"></i>{{ $trial->camera_width }}×{{ $trial->camera_height }} @ {{ $trial->camera_fps }} fps</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 w-full xl:w-auto">
                    @if($isAdmin && in_array($trial->status, ['capturing', 'failed']) && $trial->artifacts->where('type', 'pose_landmarks')->count() > 0)
                    <form method="POST" action="{{ route('admin.running-analysis.trials.analyze-sync', $trial) }}" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" id="btn-reanalyze"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 h-11 px-4 rounded-xl text-xs font-bold uppercase tracking-wider bg-white/[0.04] border border-white/10 text-slate-300 hover:border-white/20 hover:text-white transition-all"
                            onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Analyzing...'; this.form.submit();">
                            <i class="fas fa-rotate-right"></i> Re-analyze
                        </button>
                    </form>
                    @endif

                    @if($poseData && $videoArtifact)
                    <button type="button" id="btn-export-video"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 h-11 px-4 rounded-xl text-xs font-bold uppercase tracking-wider bg-white/[0.04] border border-white/10 text-slate-200 hover:border-[#ccff00]/50 hover:text-[#ccff00] transition-all">
                        <i class="fas fa-video"></i> Export Video
                    </button>
                    @endif

                    @if($trial->latestReport || $trial->findings->count() > 0 || $trial->metrics->count() > 0)
                    <a href="{{ route($pdfRoute, $trial) }}" target="_blank" id="btn-download-pdf"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 h-11 px-4 rounded-xl text-xs font-bold uppercase tracking-wider bg-[#ccff00] border border-[#ccff00] text-[#07101c] hover:bg-[#b7e800] transition-all ">
                        <i class="fas fa-file-arrow-down"></i> PDF Report
                    </a>
                    @endif
                </div>
            </div>
        </section>

        @if($trial->invalid_reason)
        <div class="mb-6 rounded-2xl border border-rose-400/25 bg-rose-400/[0.08] px-4 py-4 flex items-start gap-3">
            <span class="w-9 h-9 rounded-xl bg-rose-400/10 border border-rose-400/20 text-rose-300 flex items-center justify-center shrink-0">
                <i class="fas fa-triangle-exclamation"></i>
            </span>
            <div class="min-w-0">
                <p class="font-bold text-sm text-rose-200">Analisis tidak dapat diselesaikan</p>
                <p class="text-xs md:text-sm mt-1 font-mono text-rose-200/70 break-words">{{ $trial->invalid_reason }}</p>
            </div>
        </div>
        @endif

        {{-- KPI strip --}}
        <section class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
            <div class="review-card-soft rounded-2xl p-4 col-span-2 lg:col-span-1">
                <div class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-2">Form Score</div>
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <div class="text-3xl font-bold metric-value {{ $scoreAccent }}">{{ $score ?? '—' }}</div>
                        <div class="text-[11px] text-slate-400 mt-1">{{ $scoreCategory }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl border {{ $scoreRing }} flex items-center justify-center {{ $scoreAccent }}">
                        <i class="fas fa-gauge-high"></i>
                    </div>
                </div>
            </div>
            <div class="review-card-soft rounded-2xl p-4">
                <div class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-2">Temuan Prioritas</div>
                <div class="text-3xl font-bold metric-value text-white">{{ $priorityIssueCount }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Significant + moderate</div>
            </div>
            <div class="review-card-soft rounded-2xl p-4">
                <div class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-2">Metrik</div>
                <div class="text-3xl font-bold metric-value text-white">{{ $trial->metrics->count() }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Biomechanical values</div>
            </div>
            <div class="review-card-soft rounded-2xl p-4">
                <div class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-2">Gait Events</div>
                <div class="text-3xl font-bold metric-value text-white">{{ $trial->gaitEvents->count() }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Momen terdeteksi</div>
            </div>
            <div class="review-card-soft rounded-2xl p-4">
                <div class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-2">Kelengkapan Data</div>
                <div class="flex items-end justify-between gap-3">
                    <div class="text-3xl font-bold metric-value text-white">{{ $analysisReadiness }}%</div>
                    <div class="text-[10px] text-slate-500">{{ collect($analysisChecks)->filter()->count() }}/{{ count($analysisChecks) }}</div>
                </div>
                <div class="mt-2 h-1.5 rounded-full bg-slate-800 overflow-hidden">
                    <div class="h-full rounded-full bg-[#ccff00]" style="width: {{ $analysisReadiness }}%"></div>
                </div>
            </div>
        </section>

        {{-- Primary review workspace --}}
        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_370px] gap-6 items-start mb-8">
            <div class="min-w-0 space-y-4">
                <div class="review-card rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-white/5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white/[0.015]">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#ccff00]">Trial Playback</div>
                            <h2 class="text-lg font-bold text-white mt-1">Video dan Overlay Gerak</h2>
                        </div>
                        <div class="flex items-center gap-2 text-[10px] text-slate-500 font-semibold">
                            <span class="px-2.5 py-1 rounded-full border {{ $videoArtifact ? 'border-white/10 bg-white/[0.035] text-slate-300' : 'border-white/[0.06] text-slate-600' }}">
                                <i class="fas fa-video mr-1"></i>{{ $videoArtifact ? 'Video ready' : 'No video' }}
                            </span>
                            <span class="px-2.5 py-1 rounded-full border {{ !empty($poseData) ? 'border-white/10 bg-white/[0.035] text-slate-300' : 'border-white/[0.06] text-slate-600' }}">
                                <i class="fas fa-person-running mr-1"></i>{{ !empty($poseData) ? 'Pose ready' : 'No pose' }}
                            </span>
                        </div>
                    </div>
                    <!-- View Mode Tabs -->
                    @if($poseData || $videoArtifact)
                    <div class="flex border-b border-white/5 bg-[#080f1d] px-2">
                        @if($videoArtifact)
                        <button onclick="switchView('video')" id="tab-video"
                            class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider transition-colors border-b-2 border-[#ccff00] text-[#ccff00]">
                            <i class="fas fa-video mr-1.5"></i> Video
                        </button>
                        @endif
                        @if($poseData)
                        <button onclick="switchView('skeleton')" id="tab-skeleton"
                            class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider transition-colors border-b-2 {{ $videoArtifact ? 'border-transparent text-slate-500 hover:text-white' : 'border-[#ccff00] text-[#ccff00]' }}">
                            <i class="fas fa-project-diagram mr-1.5"></i> Skeleton
                        </button>
                        @endif
                    </div>
                    @endif

                    <div class="aspect-video bg-black relative flex items-center justify-center ring-1 ring-inset ring-white/5">
                        {{-- Video player layer --}}
                        @if($videoArtifact)
                        @php
                            $videoMime = $videoArtifact->mime_type ?: match(strtolower(pathinfo($videoArtifact->path ?? '', PATHINFO_EXTENSION))) {
                                'mp4'  => 'video/mp4',
                                'mov'  => 'video/mp4',
                                'webm' => 'video/webm',
                                'avi'  => 'video/x-msvideo',
                                default => 'video/mp4'
                            };
                            $videoSourceUrl = auth()->user()?->role === 'admin' 
                                ? route('admin.running-analysis.trials.artifact', [$trial, $videoArtifact])
                                : route('runner.running-analysis.trials.artifact', [$trial, $videoArtifact]);
                        @endphp
                        <video id="video-player" preload="auto"
                            class="absolute inset-0 w-full h-full object-contain">
                            <source src="{{ $videoSourceUrl }}"
                                type="{{ $videoMime }}">
                        </video>
                        @endif

                        {{-- Skeleton canvas layer (on top of video, transparent bg) --}}
                        <canvas id="playback-canvas"
                            class="absolute inset-0 w-full h-full object-contain pointer-events-none"
                            style="{{ !$poseData ? 'display:none' : ($videoArtifact ? 'display:none' : '') }}">
                        </canvas>

                        <!-- Biomechanical review tooltip -->
                        <style>
                        #ai-moment-tooltip {
                            transition: opacity 0.3s ease, transform 0.3s ease;
                        }
                        @media (max-width: 639px) {
                            #ai-moment-tooltip {
                                transform: translateY(100%) !important;
                            }
                            #ai-moment-tooltip.show {
                                opacity: 1 !important;
                                pointer-events: auto !important;
                                transform: translateY(0) !important;
                            }
                        }
                        @media (min-width: 640px) {
                            #ai-moment-tooltip {
                                transform: translateY(8px) !important;
                            }
                            #ai-moment-tooltip.show {
                                opacity: 1 !important;
                                pointer-events: auto !important;
                                transform: translateY(0) !important;
                            }
                        }
                        </style>
                        <div id="ai-moment-tooltip" class="absolute bottom-0 inset-x-0 sm:bottom-auto sm:top-4 sm:right-4 sm:left-auto z-30 w-full sm:max-w-[340px] bg-[#070b13] border-t border-x sm:border border-slate-700/80 rounded-t-xl sm:rounded-xl p-4 shadow-[0_12px_40px_rgba(0,0,0,0.9)] transition-all duration-300 opacity-0 pointer-events-none">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="min-w-0">
                                    <div class="text-[9px] font-semibold text-slate-400 uppercase tracking-[0.14em] mb-0.5">Umpan Balik Biomekanik</div>
                                    <h4 id="tooltip-title" class="text-sm font-bold text-white tracking-tight truncate">Landing Position</h4>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span id="tooltip-status" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider border">OK</span>
                                    <button type="button" onclick="window.hideGaitMomentTooltip()" class="text-slate-400 hover:text-white transition-colors text-xs p-1">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <p id="tooltip-summary" class="text-slate-200 text-xs mb-3 font-medium leading-relaxed"></p>
                            
                            <div id="tooltip-findings-container" class="mb-3">
                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Temuan</div>
                                <ul id="tooltip-findings" class="space-y-1 text-slate-200 text-[11px] leading-relaxed"></ul>
                            </div>
                            
                            <div id="tooltip-actions-container">
                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tindakan Koreksi</div>
                                <ul id="tooltip-actions" class="space-y-1 text-slate-200 text-[11px] leading-relaxed"></ul>
                            </div>
                        </div>

                        {{-- Video error overlay --}}
                        <div id="video-error-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-black/90 gap-3" style="display: none;">
                            <i class="fas fa-exclamation-circle text-3xl text-red-500"></i>
                            <div class="text-slate-300 text-sm text-center px-4">
                                <p class="font-bold text-white mb-1">Video gagal dimuat</p>
                                <p class="text-xs">Video tidak dapat dimuat atau diputar. Muat ulang halaman lalu coba kembali.</p>
                            </div>
                        </div>

                        @if(!$poseData && !$videoArtifact)
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/80 gap-3">
                            <i class="fas fa-exclamation-triangle text-3xl text-yellow-500"></i>
                            <div class="text-slate-400 font-mono text-sm text-center">
                                <p class="font-bold text-white mb-1">Data pose dan video tidak tersedia.</p>
                                <p class="text-xs">Proses unggah mungkin gagal saat perekaman. Lakukan perekaman ulang untuk pelari ini.</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Custom Video Controls (mirrors skeleton controls style) --}}
                    @if($videoArtifact)
                    <div id="video-controls" class="p-4 bg-[#0b1424] border-t border-white/5 flex items-center gap-4"
                        style="{{ ($videoArtifact && !$poseData) ? '' : 'display:none' }}">
                        <button id="video-play-btn" class="w-10 h-10 rounded-full bg-[#ccff00] text-black hover:bg-[#b3e600] flex items-center justify-center transition-colors shadow-[0_0_10px_rgba(204,255,0,0.3)]">
                            <i class="fas fa-play" id="video-play-icon"></i>
                        </button>
                        <div class="flex-1">
                            <input type="range" id="video-timeline" min="0" max="100" value="0" step="0.01"
                                class="w-full accent-[#ccff00] h-1.5 bg-slate-600 rounded-lg appearance-none cursor-pointer hover:bg-slate-500 transition-colors">
                        </div>
                        <div class="text-xs font-mono text-slate-400 w-28 text-right">
                            <span id="video-current-time" class="text-white">0:00</span>
                            <span class="text-slate-600 mx-1">/</span>
                            <span id="video-duration">0:00</span>
                        </div>
                    </div>
                    @endif

                    {{-- Skeleton Playback Controls (only if pose data and in skeleton mode) --}}
                    @if($poseData)
                    <div id="skeleton-controls" class="p-4 bg-[#0b1424] border-t border-white/5 flex items-center gap-4" style="{{ $videoArtifact ? 'display:none' : '' }}">
                        <button id="play-btn" class="w-10 h-10 rounded-full bg-[#ccff00] text-black hover:bg-[#b3e600] flex items-center justify-center transition-colors shadow-[0_0_10px_rgba(204,255,0,0.3)]">
                            <i class="fas fa-play" id="play-icon"></i>
                        </button>
                        <div class="flex-1">
                            <input type="range" id="timeline" min="0" max="100" value="0" class="w-full accent-[#ccff00] h-1.5 bg-slate-600 rounded-lg appearance-none cursor-pointer hover:bg-slate-500 transition-colors">
                        </div>
                        <div class="text-xs font-mono text-slate-400 w-24 text-right">
                            <span id="current-frame" class="text-white">0</span> / <span id="total-frames">0</span>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Momen Kunci Siklus Lari — Horizontal Scroll Slider -->
                    @if($trial->gaitEvents->count() > 0)
                    @php
                        $sortedEvents = $trial->gaitEvents->sortBy('timestamp_ms')->values();
                        $eventBaseMs  = $sortedEvents->min('timestamp_ms') ?? 0;
                        $totalEvents  = $sortedEvents->count();
                    @endphp
                    <div class="border-t border-white/5 bg-[#080f1d]/90">
                        <!-- Header row -->
                        <div class="px-4 pt-3 pb-1 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                <i class="fas fa-camera text-[#ccff00]"></i>
                                Momen Kunci Siklus Lari
                                <span class="bg-slate-800 text-slate-400 border border-slate-700 rounded-full text-[9px] px-1.5 py-px font-bold">{{ $totalEvents }}</span>
                            </div>
                            <!-- Scroll nav arrows -->
                            <div class="flex items-center gap-1">
                                <button id="gait-scroll-left" type="button"
                                    class="w-6 h-6 flex items-center justify-center rounded-md bg-slate-800 border border-slate-700 text-slate-400 hover:text-[#ccff00] hover:border-[#ccff00]/50 transition-all duration-200 text-[10px] disabled:opacity-30"
                                    onclick="document.getElementById('gait-scroll-track').scrollBy({left:-220, behavior:'smooth'})">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button id="gait-scroll-right" type="button"
                                    class="w-6 h-6 flex items-center justify-center rounded-md bg-slate-800 border border-slate-700 text-slate-400 hover:text-[#ccff00] hover:border-[#ccff00]/50 transition-all duration-200 text-[10px]"
                                    onclick="document.getElementById('gait-scroll-track').scrollBy({left:220, behavior:'smooth'})">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Scroll progress indicator -->
                        <div class="px-4 mb-1.5">
                            <div class="h-[2px] w-full bg-slate-800 rounded-full overflow-hidden">
                                <div id="gait-progress-bar" class="h-full bg-[#ccff00]/60 rounded-full transition-all duration-200" style="width:0%"></div>
                            </div>
                        </div>

                        <!-- Scrollable card track -->
                        <div id="gait-scroll-track"
                            class="flex gap-2.5 px-4 pb-4 overflow-x-auto scrollbar-none"
                            style="scroll-snap-type: x mandatory; -ms-overflow-style: none; scrollbar-width: none; -webkit-overflow-scrolling: touch;">

                            @foreach($sortedEvents as $idx => $event)
                            @php
                                $seqNo    = $idx + 1;
                                $relSec   = number_format(($event->timestamp_ms - $eventBaseMs) / 1000, 2);
                                $label    = match($event->event_type) {
                                    'initial_contact'   => 'Landing',
                                    'midstance'         => 'Midstance',
                                    'toe_off'           => 'Push Off',
                                    'max_swing_flexion' => 'Knee Pull',
                                    default             => ucfirst(str_replace('_', ' ', $event->event_type)),
                                };
                                $subLabel = match($event->event_type) {
                                    'initial_contact'   => 'Foot Strike',
                                    'midstance'         => 'Mid-Stance',
                                    'toe_off'           => 'Toe-Off',
                                    'max_swing_flexion' => 'Swing Phase',
                                    default             => '',
                                };
                                $icon     = match($event->event_type) {
                                    'initial_contact'   => 'fa-shoe-prints',
                                    'toe_off'           => 'fa-running',
                                    'max_swing_flexion' => 'fa-arrow-up',
                                    default             => 'fa-stopwatch',
                                };
                                $phaseColor = 'bg-[#0a101a] border-white/10 hover:border-white/20 hover:bg-white/[0.025]';
                                $iconColor = 'text-slate-400 group-hover:text-[#ccff00]';
                                $sidePill = 'bg-white/[0.035] border-white/10 text-slate-400';
                            @endphp
                            <button type="button"
                                data-gait-card="{{ $idx }}"
                                onclick="seekToGaitEvent({{ $event->timestamp_ms }}, '{{ $event->event_type }}'); if(window._highlightGaitCard) window._highlightGaitCard({{ $idx }});"
                                class="gait-moment-card flex-none w-[160px] flex flex-col items-start text-left p-3 rounded-xl
                                       {{ $phaseColor }}
                                       border transition-all duration-300 group
                                       focus:outline-none focus:ring-1 focus:ring-[#ccff00]/60"
                                style="scroll-snap-align: start;">

                                <!-- Sequence + side -->
                                <div class="flex items-center justify-between w-full mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full bg-slate-700 border border-slate-600 text-slate-300 text-[8px] font-bold flex items-center justify-center flex-none">{{ $seqNo }}</span>
                                        <span class="px-1.5 py-px rounded border text-[8px] font-bold uppercase {{ $sidePill }}">{{ $event->side }}</span>
                                    </div>
                                    <i class="fas {{ $icon }} {{ $iconColor }} text-[11px] transition-colors duration-200"></i>
                                </div>

                                <!-- Phase name -->
                                <div class="text-[11px] font-bold text-white leading-tight mb-px tracking-tight">{{ $label }}</div>
                                <div class="text-[9px] font-medium text-slate-500 uppercase tracking-widest mb-2.5">{{ $subLabel }}</div>

                                <!-- Timestamp -->
                                <div class="mt-auto w-full flex items-center justify-between border-t border-white/5 pt-2">
                                    <span class="font-mono text-[10px] text-slate-400 font-bold">+{{ $relSec }}s</span>
                                    <span class="text-[8px] uppercase tracking-wider font-bold text-slate-600 group-hover:text-[#ccff00] transition-colors duration-200 flex items-center gap-0.5">
                                        Lihat <i class="fas fa-play text-[7px] ml-0.5"></i>
                                    </span>
                                </div>
                            </button>
                            @endforeach

                        </div><!-- end track -->
                    </div><!-- end slider wrapper -->
                    <script>
                    (function() {
                        const track = document.getElementById('gait-scroll-track');
                        const progressBar = document.getElementById('gait-progress-bar');
                        const cards = document.querySelectorAll('.gait-moment-card');
                        let activeCard = null;

                        function updateProgress() {
                            if (!track) return;
                            const scrollLeft = track.scrollLeft;
                            const maxScroll = track.scrollWidth - track.clientWidth;
                            const pct = maxScroll > 0 ? (scrollLeft / maxScroll) * 100 : 0;
                            if (progressBar) progressBar.style.width = Math.min(100, pct) + '%';
                        }

                        if (track) {
                            track.addEventListener('scroll', updateProgress, { passive: true });
                            updateProgress();

                            // drag-to-scroll (mouse only — touch uses native momentum scroll)
                            let isDragging = false, startX, scrollStart, didDrag = false;
                            const DRAG_THRESHOLD = 8; // px before drag is considered intentional

                            track.addEventListener('mousedown', function(e) {
                                // ignore right-click or clicks on buttons (let them through)
                                if (e.button !== 0) return;
                                isDragging = true;
                                didDrag = false;
                                startX = e.pageX;
                                scrollStart = track.scrollLeft;
                                track.style.cursor = 'grabbing';
                                track.style.userSelect = 'none';
                            });

                            window.addEventListener('mousemove', function(e) {
                                if (!isDragging) return;
                                const dx = e.pageX - startX;
                                if (Math.abs(dx) > DRAG_THRESHOLD) {
                                    didDrag = true;
                                    track.scrollLeft = scrollStart - dx;
                                }
                            });

                            window.addEventListener('mouseup', function(e) {
                                if (!isDragging) return;
                                isDragging = false;
                                track.style.cursor = '';
                                track.style.userSelect = '';
                                // If we dragged, suppress the next click on the track
                                if (didDrag) {
                                    track.addEventListener('click', function stopClick(ev) {
                                        ev.stopPropagation();
                                        ev.preventDefault();
                                        track.removeEventListener('click', stopClick, true);
                                    }, { capture: true, once: true });
                                }
                                didDrag = false;
                            });
                        }

                        // Highlight active card when seeked
                        window._highlightGaitCard = function(dataIdx) {
                            cards.forEach(function(c) {
                                c.classList.remove('ring-1', 'ring-[#ccff00]');
                            });
                            if (activeCard) activeCard.classList.remove('ring-1', 'ring-[#ccff00]');
                            const target = document.querySelector('[data-gait-card="' + dataIdx + '"]');
                            if (target) {
                                target.classList.add('ring-1', 'ring-[#ccff00]');
                                activeCard = target;
                                // scroll card into view inside track
                                target.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
                            }
                        };
                    })();
                    </script>
                    @endif
                </div>



            </div>

            {{-- Sticky decision panel --}}
            <aside class="xl:sticky xl:top-24 space-y-4">
                <section class="review-card rounded-2xl p-5 overflow-hidden relative">
                    <div class="relative">
                        <div class="flex items-center justify-between gap-3 mb-5">
                            <div>
                                <div class="text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold">Review Snapshot</div>
                                <h2 class="text-base font-bold text-white mt-1">Ringkasan Analisis</h2>
                            </div>
                            <span class="w-10 h-10 rounded-xl bg-white/[0.04] border border-white/10 flex items-center justify-center text-[#ccff00]">
                                <i class="fas fa-chart-simple"></i>
                            </span>
                        </div>

                        <div class="rounded-2xl bg-black/20 border border-white/[0.06] p-4 mb-4">
                            <div class="flex items-end justify-between gap-4">
                                <div>
                                    <div class="text-5xl font-bold tracking-[-0.06em] metric-value {{ $scoreAccent }}">{{ $score ?? '—' }}</div>
                                    <div class="text-xs text-slate-500 mt-1">dari 100</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold uppercase tracking-wider {{ $scoreAccent }}">{{ $scoreCategory }}</div>
                                    <div class="text-[10px] text-slate-500 mt-1 max-w-[150px]">{{ $scoreDescription }}</div>
                                </div>
                            </div>
                            <div class="mt-4 h-2 rounded-full bg-slate-800 overflow-hidden">
                                <div class="h-full rounded-full {{ $scoreBar }}" style="width: {{ $score ?? 0 }}%"></div>
                            </div>
                        </div>

                        <div class="space-y-2.5">
                            @foreach($analysisChecks as $label => $ready)
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">{{ $label }}</span>
                                <span class="inline-flex items-center gap-1.5 {{ $ready ? 'text-[#ccff00]' : 'text-slate-600' }}">
                                    <i class="fas {{ $ready ? 'fa-circle-check' : 'fa-circle-minus' }}"></i>
                                    {{ $ready ? 'Tersedia' : 'Belum ada' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                @if($coachMessageText || count($positives) > 0)
                <section class="review-card rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-8 h-8 rounded-lg bg-[#ccff00]/10 border border-[#ccff00]/20 flex items-center justify-center text-[#ccff00] text-xs">
                            <i class="fas fa-comment-dots"></i>
                        </span>
                        <div>
                            <div class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Coach Feedback</div>
                            <div class="text-sm font-bold text-white">Catatan Utama</div>
                        </div>
                    </div>

                    @if($coachMessageText)
                    <blockquote class="text-sm leading-relaxed text-slate-300 border-l-2 border-[#ccff00] pl-3 italic">
                        “{{ $coachMessageText }}”
                    </blockquote>
                    @endif

                    @if(count($positives) > 0)
                    <div class="mt-4 pt-4 border-t border-white/5 space-y-2.5">
                        @foreach(collect($positives)->take(3) as $positive)
                        <div class="flex items-start gap-2.5">
                            <span class="w-5 h-5 mt-0.5 rounded-full bg-[#ccff00]/10 border border-[#ccff00]/20 text-[#ccff00] flex items-center justify-center text-[9px] shrink-0">
                                <i class="fas fa-check"></i>
                            </span>
                            <div class="text-xs leading-relaxed min-w-0">
                                @if(is_array($positive))
                                    <div class="font-bold text-slate-200">{{ $positive['title'] ?? $toText($positive) }}</div>
                                    @if(!empty($positive['description']))
                                        <div class="text-slate-500 mt-0.5">{{ $positive['description'] }}</div>
                                    @endif
                                @else
                                    <div class="text-slate-300">{{ $positive }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </section>
                @endif

                {{-- Review decision actions --}}
                @if($isAdmin)
                <section class="review-card rounded-2xl p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <div class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Admin Decision</div>
                            <div class="text-sm font-bold text-white mt-1">Keputusan Review</div>
                        </div>
                        <i class="fas fa-user-shield text-slate-600"></i>
                    </div>

                    @if(!$trial->isPublished() && $trial->status !== 'invalid')
                    <p class="text-xs leading-relaxed text-slate-500 mb-4">Pastikan video, skeleton, temuan, dan rekomendasi sudah sesuai sebelum hasil dipublikasikan.</p>
                    <div class="space-y-2.5">
                        @if(in_array($trial->status, ['queued', 'analyzing', 'failed']))
                        <form action="{{ route('admin.running-analysis.trials.analyze-sync', $trial) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full h-11 rounded-xl bg-[#ccff00] text-[#07101c] font-bold text-xs uppercase tracking-wider hover:bg-[#b7e800] transition-colors">
                                <i class="fas fa-play mr-2"></i> Analyze Now
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.running-analysis.trials.approve', $trial) }}" method="POST" id="approve-form">
                            @csrf
                            <button type="submit" onclick="return confirm('Approve and publish this trial to the runner?')"
                                class="w-full h-11 rounded-xl bg-[#ccff00] text-[#07101c] font-bold text-xs uppercase tracking-wider hover:bg-[#b7e800] transition-colors">
                                <i class="fas fa-check-double mr-2"></i> Approve & Publish
                            </button>
                        </form>
                        @endif

                        <div class="grid grid-cols-2 gap-2.5">
                            @if($videoArtifact)
                            <a href="{{ route('admin.running-analysis.upload-video.form', $trial->session_id) }}?trial_id={{ $trial->id }}"
                                class="h-10 rounded-xl bg-white/[0.04] border border-white/10 text-slate-300 hover:text-white hover:border-white/20 transition-colors flex items-center justify-center text-xs font-bold">
                                <i class="fas fa-rotate-right mr-2"></i> Re-analyze
                            </a>
                            @else
                            <span class="h-10 rounded-xl bg-white/[0.02] border border-white/[0.06] text-slate-700 flex items-center justify-center text-xs font-bold cursor-not-allowed">
                                <i class="fas fa-rotate-right mr-2"></i> Re-analyze
                            </span>
                            @endif
                            <button type="button" onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                                class="h-10 rounded-xl bg-rose-400/10 border border-rose-400/20 text-rose-300 hover:bg-rose-400 hover:text-white transition-colors text-xs font-bold">
                                <i class="fas fa-ban mr-2"></i> Reject
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="rounded-2xl p-4 {{ $trial->isPublished() ? 'bg-[#ccff00]/10 border border-[#ccff00]/20' : 'bg-rose-400/10 border border-rose-400/20' }}">
                        <div class="flex items-start gap-3">
                            <i class="fas {{ $trial->isPublished() ? 'fa-circle-check text-[#ccff00]' : 'fa-circle-xmark text-rose-300' }} mt-0.5"></i>
                            <div>
                                <div class="text-sm font-bold {{ $trial->isPublished() ? 'text-[#ccff00]' : 'text-rose-200' }}">{{ $statusLabel }}</div>
                                <div class="text-xs text-slate-400 mt-1">
                                    {{ $trial->isPublished() ? 'Hasil sudah tersedia bagi pelari.' : 'Trial ditandai tidak valid dan tidak ditampilkan kepada pelari.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($trial->isPublished())
                    <div class="mt-3">
                        <form action="{{ route('admin.running-analysis.trials.approve', $trial) }}" method="POST" id="reapprove-form">
                            @csrf
                            <button type="submit" onclick="return confirm('Re-approve and publish this trial again?')"
                                class="w-full h-10 rounded-xl bg-white/[0.04] border border-white/10 text-slate-300 hover:text-white hover:border-[#ccff00]/50 transition-colors flex items-center justify-center text-xs font-bold">
                                <i class="fas fa-check-double mr-2"></i> Re-approve & Publish
                            </button>
                        </form>
                    </div>
                    @endif
                    @endif
                </section>
                @endif
            </aside>
        </div>

        {{-- In-page navigation --}}
        <nav class="section-anchor sticky top-16 z-20 mb-6 rounded-2xl bg-[#0a101a]/95 backdrop-blur-sm border border-white/10 p-2 overflow-x-auto hide-scrollbar">
            <div class="flex min-w-max gap-1">
                <a href="#findings" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-[#ccff00] hover:bg-white/[0.04] transition-colors"><i class="fas fa-crosshairs mr-2"></i>Temuan</a>
                <a href="#metrics" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-[#ccff00] hover:bg-white/[0.04] transition-colors"><i class="fas fa-chart-line mr-2"></i>Metrik</a>
                <a href="#training" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-[#ccff00] hover:bg-white/[0.04] transition-colors"><i class="fas fa-dumbbell mr-2"></i>Program Latihan</a>
                <a href="#artifacts" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-[#ccff00] hover:bg-white/[0.04] transition-colors"><i class="fas fa-folder-open mr-2"></i>Artefak</a>
            </div>
        </nav>

        {{-- Findings --}}
        <section id="findings" class="section-anchor review-card rounded-2xl p-5 md:p-7 mb-6">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6">
                <div>
                    <div class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#ccff00]">Review Summary</div>
                    <h2 class="text-xl md:text-2xl font-bold text-white mt-1">Temuan Biomekanik</h2>
                    <p class="text-sm text-slate-500 mt-2">Diurutkan berdasarkan tingkat dampak agar reviewer dapat mengambil keputusan lebih cepat.</p>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider">
                    <span class="px-2.5 py-1 rounded-full bg-white/[0.03] border border-white/10 text-slate-400">{{ $findings->where('severity', 'significant')->count() }} Significant</span>
                    <span class="px-2.5 py-1 rounded-full bg-white/[0.03] border border-white/10 text-slate-400">{{ $findings->where('severity', 'moderate')->count() }} Moderate</span>
                    <span class="px-2.5 py-1 rounded-full bg-white/[0.03] border border-white/10 text-slate-400">{{ $findings->where('severity', 'minor')->count() }} Minor</span>
                </div>
            </div>

            @if($findings->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($findings as $index => $finding)
                @php
                    $severityConfig = match($finding->severity) {
                        'significant' => [
                            'label' => 'Significant',
                            'icon' => 'fa-arrow-trend-up',
                            'accent' => 'text-rose-300',
                            'badge' => 'bg-white/[0.03] border-white/10 text-rose-300',
                            'line' => 'bg-rose-400',
                        ],
                        'moderate' => [
                            'label' => 'Moderate',
                            'icon' => 'fa-triangle-exclamation',
                            'accent' => 'text-amber-300',
                            'badge' => 'bg-white/[0.03] border-white/10 text-amber-300',
                            'line' => 'bg-amber-400',
                        ],
                        default => [
                            'label' => 'Minor',
                            'icon' => 'fa-circle-info',
                            'accent' => 'text-slate-400',
                            'badge' => 'bg-white/[0.03] border-white/10 text-slate-400',
                            'line' => 'bg-slate-500',
                        ],
                    };
                    $findingTitle = ucwords(str_replace('_', ' ', strtolower($finding->explanation_key ?? $finding->finding_code)));
                    $evidenceRows = $flattenEvidence($finding->evidence_json);
                @endphp
                <article class="relative rounded-2xl bg-white/[0.018] border border-white/[0.07] p-4 md:p-5 overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 {{ $severityConfig['line'] }}"></div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-white/[0.04] border border-white/10 flex items-center justify-center {{ $severityConfig['accent'] }} shrink-0">
                                <i class="fas {{ $severityConfig['icon'] }}"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="text-[10px] font-mono text-slate-600 mb-1">{{ $finding->finding_code }}</div>
                                <h3 class="text-sm md:text-base font-bold text-white leading-snug">{{ $findingTitle }}</h3>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full border text-[9px] font-bold uppercase tracking-wider shrink-0 {{ $severityConfig['badge'] }}">{{ $severityConfig['label'] }}</span>
                    </div>

                    @if(count($evidenceRows) > 0)
                    <div class="mt-4 rounded-xl border border-white/[0.06] bg-white/[0.02] divide-y divide-white/[0.05]">
                        @foreach(array_slice($evidenceRows, 0, 5) as $row)
                        <div class="px-3 py-2.5 flex items-start justify-between gap-4 text-xs">
                            <span class="text-slate-500">{{ $row['label'] }}</span>
                            <span class="font-mono text-slate-200 text-right break-all">{{ $row['value'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </article>
                @endforeach
            </div>
            @else
            <div class="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] py-12 text-center">
                <span class="w-12 h-12 mx-auto rounded-2xl bg-[#ccff00]/10 border border-[#ccff00]/20 text-[#ccff00] flex items-center justify-center mb-3">
                    <i class="fas fa-check"></i>
                </span>
                <div class="font-bold text-white">Tidak ada temuan biomekanik</div>
                <div class="text-sm text-slate-500 mt-1">Temuan akan muncul setelah proses analisis selesai.</div>
            </div>
            @endif
        </section>

        {{-- Metrics --}}
        <section id="metrics" class="section-anchor review-card rounded-2xl p-5 md:p-7 mb-6">
            <div class="mb-6">
                <div class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-500">Measurement Layer</div>
                <h2 class="text-xl md:text-2xl font-bold text-white mt-1">Metrik Biomekanik</h2>
                <p class="text-sm text-slate-500 mt-2">Nilai ditampilkan secara netral; interpretasi klinis atau performance range harus berasal dari rule backend.</p>
            </div>

            @if($trial->metrics->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($trial->metrics as $metric)
                @php
                    $metricLabel = $metricLabels[$metric->metric_code] ?? ucwords(str_replace('_', ' ', $metric->metric_code));
                    $sideClass = 'bg-white/[0.03] border-white/10 text-slate-400';
                @endphp
                <article class="rounded-2xl border border-white/[0.07] bg-black/15 p-4 hover:border-white/15 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-5">
                        <div class="text-xs font-bold text-slate-300 leading-snug">{{ $metricLabel }}</div>
                        <span class="px-2 py-0.5 rounded-full border text-[9px] uppercase font-bold tracking-wider {{ $sideClass }}">
                            {{ $metric->side ? ucfirst($metric->side) : 'General' }}
                        </span>
                    </div>
                    <div class="flex items-end gap-2">
                        <div class="text-3xl font-bold text-white metric-value tracking-tight">{{ round((float) $metric->value_decimal, 2) }}</div>
                        <div class="text-xs text-slate-500 mb-1">{{ $metric->unit }}</div>
                    </div>
                    <div class="mt-4 h-px bg-white/[0.07]"></div>
                </article>
                @endforeach
            </div>
            @else
            <div class="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] py-12 text-center text-sm text-slate-500">
                Metrik belum dihasilkan untuk trial ini.
            </div>
            @endif
        </section>

        {{-- Training --}}
        <section id="training" class="section-anchor review-card rounded-2xl p-5 md:p-7 mb-6">
            <div class="mb-6">
                <div class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#ccff00]">Training Plan</div>
                <h2 class="text-xl md:text-2xl font-bold text-white mt-1">Program Latihan & Koreksi</h2>
                <p class="text-sm text-slate-500 mt-2">Rekomendasi dipisahkan berdasarkan cue, drill teknik, dan latihan kekuatan.</p>
            </div>

            @if($recommendations->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                @php
                    $trainingGroups = [
                        ['title' => 'Running Cues', 'subtitle' => 'Pengingat singkat saat berlari', 'icon' => 'fa-comment-dots', 'accent' => 'text-slate-300', 'items' => $cues],
                        ['title' => 'Technique Drills', 'subtitle' => 'Latihan pola gerak spesifik', 'icon' => 'fa-person-running', 'accent' => 'text-slate-300', 'items' => $drills],
                        ['title' => 'Strength Exercises', 'subtitle' => 'Penguatan pendukung form', 'icon' => 'fa-dumbbell', 'accent' => 'text-slate-300', 'items' => $strengths],
                    ];
                @endphp
                @foreach($trainingGroups as $group)
                <div class="rounded-2xl border border-white/[0.07] bg-black/15 p-4">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="w-9 h-9 rounded-xl bg-white/[0.04] border border-white/10 flex items-center justify-center {{ $group['accent'] }} shrink-0">
                            <i class="fas {{ $group['icon'] }}"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-white">{{ $group['title'] }}</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $group['subtitle'] }}</p>
                        </div>
                    </div>

                    @if($group['items']->count() > 0)
                    <div class="space-y-2.5">
                        @foreach($group['items'] as $item)
                        <article class="rounded-xl border border-white/[0.06] bg-white/[0.025] p-3.5">
                            <div class="font-bold text-sm text-slate-100">{{ $item->title }}</div>
                            <div class="text-xs leading-relaxed text-slate-500 mt-1.5">{{ $item->description }}</div>
                        </article>
                        @endforeach
                    </div>
                    @else
                    <div class="rounded-xl border border-dashed border-white/[0.07] py-8 text-center text-xs text-slate-600">Belum ada rekomendasi.</div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] py-12 text-center text-sm text-slate-500">
                Program latihan belum tersedia.
            </div>
            @endif
        </section>

        {{-- Artifacts --}}
        <section id="artifacts" class="section-anchor review-card rounded-2xl p-5 md:p-7">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6">
                <div>
                    <div class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-500">Analysis Files</div>
                    <h2 class="text-xl md:text-2xl font-bold text-white mt-1">Artefak Analisis</h2>
                    <p class="text-sm text-slate-500 mt-2">Daftar file sumber dan keluaran yang digunakan pada trial.</p>
                </div>
                <span class="px-3 py-1.5 rounded-full border border-white/10 bg-white/[0.03] text-xs font-bold text-slate-400">{{ $trial->artifacts->count() }} file</span>
            </div>

            @if($trial->artifacts->count() > 0)
            <div class="overflow-hidden rounded-2xl border border-white/[0.07]">
                <div class="hidden md:grid grid-cols-[1fr_180px_120px] gap-4 px-4 py-3 bg-white/[0.035] text-[10px] uppercase tracking-[0.16em] font-bold text-slate-500">
                    <div>Artifact</div>
                    <div>Format</div>
                    <div class="text-right">Ukuran</div>
                </div>
                <div class="divide-y divide-white/[0.06]">
                    @foreach($trial->artifacts as $artifact)
                    @php
                        $artifactType = ucwords(str_replace('_', ' ', $artifact->type));
                        $artifactSizeKb = ((float) ($artifact->size_bytes ?? 0)) / 1024;
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-[1fr_180px_120px] gap-2 md:gap-4 px-4 py-3.5 items-center bg-black/10 hover:bg-white/[0.02] transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-white/[0.04] border border-white/10 text-slate-400 flex items-center justify-center shrink-0">
                                <i class="fas {{ $artifact->type === 'video_clip' ? 'fa-video' : ($artifact->type === 'pose_landmarks' ? 'fa-person' : 'fa-file-code') }}"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-200 truncate">{{ $artifactType }}</div>
                                <div class="text-[10px] font-mono text-slate-600 truncate">{{ $artifact->path ?? 'Stored artifact' }}</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500 md:text-slate-400">{{ $artifact->mime_type ?? '—' }}</div>
                        <div class="text-xs font-mono text-slate-400 md:text-right">{{ number_format($artifactSizeKb, 1) }} KB</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] py-12 text-center text-sm text-slate-500">Belum ada artefak yang diunggah.</div>
            @endif
        </section>
    </div>
</div>

{{-- Reject modal --}}
@if($isAdmin)
<div id="reject-modal" class="hidden fixed inset-0 bg-[#02050b]/85 backdrop-blur-md z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="reject-modal-title">
    <div class="review-card rounded-2xl p-6 w-full max-w-md shadow-2xl relative overflow-hidden">
        <div class="relative">
            <div class="flex items-start gap-3 mb-5">
                <span class="w-10 h-10 rounded-xl bg-rose-400/10 border border-rose-400/20 text-rose-300 flex items-center justify-center shrink-0">
                    <i class="fas fa-ban"></i>
                </span>
                <div>
                    <h3 id="reject-modal-title" class="text-lg font-bold text-white">Tolak Trial</h3>
                    <p class="text-slate-500 text-sm mt-1">Trial akan ditandai tidak valid dan tidak ditampilkan kepada pelari.</p>
                </div>
            </div>
            <form action="{{ route('admin.running-analysis.trials.reject', $trial) }}" method="POST">
                @csrf
                <label for="reject-reason" class="block text-[10px] font-bold text-slate-500 uppercase tracking-[0.16em] mb-2">Alasan penolakan</label>
                <textarea id="reject-reason" name="reason" rows="4" placeholder="Contoh: sudut kamera tidak sesuai, tubuh pelari keluar frame, atau video terhalang..."
                    class="w-full bg-black/20 border border-white/10 text-white rounded-2xl px-4 py-3 text-sm outline-none focus:border-rose-400/50 focus:ring-2 focus:ring-rose-400/10 resize-none placeholder:text-slate-700"></textarea>
                <div class="grid grid-cols-2 gap-3 mt-5">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')"
                        class="h-11 rounded-xl bg-white/[0.04] border border-white/10 text-slate-300 hover:text-white hover:border-white/20 transition-colors text-sm font-bold">
                        Batal
                    </button>
                    <button type="submit" class="h-11 rounded-xl bg-rose-500 text-white hover:bg-rose-400 transition-colors text-sm font-bold">
                        <i class="fas fa-ban mr-2"></i> Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($poseData || isset($videoArtifact))
<script>
// View mode switcher (global helper)
window.currentViewMode = '{{ $videoArtifact ? "video" : "skeleton" }}';

window.switchView = function(mode) {
    window.currentViewMode = mode;
    const videoPlayer = document.getElementById('video-player');
    const videoControls = document.getElementById('video-controls');
    const skeletonCanvas = document.getElementById('playback-canvas');
    const skeletonControls = document.getElementById('skeleton-controls');
    const tabVideo = document.getElementById('tab-video');
    const tabSkeleton = document.getElementById('tab-skeleton');

    if (mode === 'video') {
        if (videoPlayer) videoPlayer.style.display = '';
        if (videoControls) videoControls.style.display = '';
        if (skeletonCanvas) skeletonCanvas.style.display = ''; // Overlay on top of video
        if (skeletonControls) skeletonControls.style.display = 'none';
        if (tabVideo) { tabVideo.classList.add('border-[#ccff00]', 'text-[#ccff00]'); tabVideo.classList.remove('border-transparent', 'text-slate-500'); }
        if (tabSkeleton) { tabSkeleton.classList.remove('border-[#ccff00]', 'text-[#ccff00]'); tabSkeleton.classList.add('border-transparent', 'text-slate-500'); }
    } else {
        if (videoPlayer) {
            videoPlayer.style.display = 'none';
            videoPlayer.pause();
        }
        if (videoControls) videoControls.style.display = 'none';
        if (skeletonCanvas) skeletonCanvas.style.display = '';
        if (skeletonControls) skeletonControls.style.display = '';
        if (tabVideo) { tabVideo.classList.remove('border-[#ccff00]', 'text-[#ccff00]'); tabVideo.classList.add('border-transparent', 'text-slate-500'); }
        if (tabSkeleton) { tabSkeleton.classList.add('border-[#ccff00]', 'text-[#ccff00]'); tabSkeleton.classList.remove('border-transparent', 'text-slate-500'); }
    }
    
    // Draw initial frame for mode
    if (window.syncSkeletonFrame) {
        window.syncSkeletonFrame();
    }
};

// ---- Custom Video Player Controls ----
(function() {
    const vp = document.getElementById('video-player');
    if (!vp) return;

    const playBtn = document.getElementById('video-play-btn');
    const playIcon = document.getElementById('video-play-icon');
    const timeline = document.getElementById('video-timeline');
    const curTime = document.getElementById('video-current-time');
    const durEl = document.getElementById('video-duration');
    const errOverlay = document.getElementById('video-error-overlay');

    function fmt(s) {
        s = isFinite(s) ? s : 0;
        const m = Math.floor(s / 60), ss = Math.floor(s % 60);
        return m + ':' + String(ss).padStart(2, '0');
    }
    window.fmtVideoTime = fmt;

    // A. Centralized seek function (Requirement A/B/F/G)
    window.seekVideoTo = function(targetTime, options = {}) {
        let target = parseFloat(targetTime);
        if (isNaN(target) || !isFinite(target)) return;

        // Clamp values to [startSec, endSec] or [0, duration]
        const st = window._vpStartSec ?? 0;
        const en = window._vpEndSec ?? vp.duration ?? 0;
        target = Math.max(st, Math.min(target, en));

        // 1. Video must pause on seek (Requirement 1 & B)
        vp.pause();
        
        // 2. Set time (Requirement 2)
        vp.currentTime = target;

        // 3. Update timeline slider (Requirement 5)
        if (timeline) {
            timeline.value = target;
        }

        // 4. Update current time display (Requirement 5)
        if (curTime) {
            curTime.textContent = fmt(target);
        }

        // 5. Change play icon to play (Requirement 4)
        if (playIcon) {
            playIcon.className = 'fas fa-play';
        }

        // 6. Draw closest skeleton frame (Requirement 6 & F)
        if (window.syncSkeletonFrame) {
            window.syncSkeletonFrame();
        }
    };

    function applyRange() {
        const st = window._vpStartSec ?? 0;
        const en = window._vpEndSec   ?? vp.duration ?? 0;
        if (timeline) {
            timeline.min   = st;
            timeline.max   = en;
            timeline.step  = 0.01;
            const val = parseFloat(timeline.value);
            if (val < st || val > en) {
                timeline.value = st;
            }
        }
        if (durEl) durEl.textContent = fmt(en);
    }

    // Set up range constraint updates
    vp.addEventListener('loadedmetadata', applyRange);
    window.addEventListener('_vpRangeReady', applyRange);

    // timeupdate handler
    vp.addEventListener('timeupdate', () => {
        // Only loop/constrain if playing
        const en = window._vpEndSec ?? vp.duration ?? 0;
        const st = window._vpStartSec ?? 0;
        if (!vp.paused && vp.currentTime > en) {
            vp.pause();
            window.seekVideoTo(st);
            return;
        }

        if (curTime) curTime.textContent = fmt(vp.currentTime);
        
        // Update timeline only if user is not actively dragging it
        if (timeline && !timeline._dragging) {
            timeline.value = vp.currentTime;
        }
        if (playIcon) playIcon.className = vp.paused ? 'fas fa-play' : 'fas fa-pause';
    });

    vp.addEventListener('ended', () => {
        const st = window._vpStartSec ?? 0;
        window.seekVideoTo(st);
    });

    if (playBtn) {
        playBtn.addEventListener('click', () => {
            if (vp.paused) {
                // Autoplay/play request
                vp.play().catch(err => {
                    console.warn("Playback prevented or error occurred:", err);
                });
            } else {
                vp.pause();
            }
        });
    }

    // Register Pointer Events on timeline (Requirement A)
    if (timeline) {
        timeline.addEventListener('pointerdown', (event) => {
            timeline._dragging = true;
            vp.pause();
            if (timeline.setPointerCapture) {
                timeline.setPointerCapture(event.pointerId);
            }
        });

        timeline.addEventListener('input', () => {
            window.seekVideoTo(timeline.value);
        });

        timeline.addEventListener('pointerup', (event) => {
            window.seekVideoTo(timeline.value);
            timeline._dragging = false;
            if (timeline.releasePointerCapture) {
                timeline.releasePointerCapture(event.pointerId);
            }
        });

        timeline.addEventListener('pointercancel', () => {
            timeline._dragging = false;
        });

        timeline.addEventListener('change', () => {
            window.seekVideoTo(timeline.value);
            timeline._dragging = false;
        });
    }

    // Robust error and status handling (Requirement I)
    const logVideoError = (evt) => {
        console.error("Video player issue:", evt.type, {
            error: vp.error,
            duration: vp.duration,
            currentTime: vp.currentTime,
            readyState: vp.readyState,
            networkState: vp.networkState
        });

        if (evt.type === 'error') {
            if (errOverlay) {
                errOverlay.style.display = 'flex';
            }
        }
    };

    vp.addEventListener('error', logVideoError);
    vp.addEventListener('stalled', logVideoError);
    vp.addEventListener('waiting', logVideoError);
})();

document.addEventListener('DOMContentLoaded', () => {
    // Initial view set
    window.switchView(window.currentViewMode);

    try {
        const poseData = {!! $poseData ?? 'null' !!};
        if (!poseData) return; // no skeleton data, video only

        const frames = poseData.landmarks || [];
        
        const canvas = document.getElementById('playback-canvas');
        const ctx = canvas.getContext('2d');
        const timeline = document.getElementById('timeline');
        const playBtn = document.getElementById('play-btn');
        const playIcon = document.getElementById('play-icon');
        const currentFrameSpan = document.getElementById('current-frame');
        const totalFramesSpan = document.getElementById('total-frames');
        const videoPlayer = document.getElementById('video-player');
        
        let currentFrameIdx = 0;
        let isPlaying = false;
        let animationId = null;
        let lastFrameTime = 0;
        
        const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
        const toDeg = (rad) => rad * 180 / Math.PI;

        const VISIBILITY_THRESHOLD = 0.5;

        const POSE_COLORS = {
            head: '#E2E8F0',
            torso: '#22D3EE',
            leftArm: '#F472B6',
            rightArm: '#FB923C',
            leftLeg: '#A3E635',
            rightLeg: '#60A5FA',
        };

        const SKELETON_GROUPS = [
            {
                key: 'head',
                label: 'Head',
                color: POSE_COLORS.head,
                connections: [[0,1], [1,2], [2,3], [3,7], [0,4], [4,5], [5,6], [6,8], [9,10]],
            },
            {
                key: 'torso',
                label: 'Torso',
                color: POSE_COLORS.torso,
                connections: [[11,12], [11,23], [12,24], [23,24]],
            },
            {
                key: 'leftArm',
                label: 'L Arm',
                color: POSE_COLORS.leftArm,
                connections: [[11,13], [13,15]],
            },
            {
                key: 'rightArm',
                label: 'R Arm',
                color: POSE_COLORS.rightArm,
                connections: [[12,14], [14,16]],
            },
            {
                key: 'leftLeg',
                label: 'L Leg',
                color: POSE_COLORS.leftLeg,
                connections: [[23,25], [25,27], [27,29], [29,31], [31,27]],
            },
            {
                key: 'rightLeg',
                label: 'R Leg',
                color: POSE_COLORS.rightLeg,
                connections: [[24,26], [26,28], [28,30], [30,32], [32,28]],
            },
        ];

        const ANGLES_TO_DRAW = [
            { p: [23,25,27], name: 'L KNEE', color: POSE_COLORS.leftLeg, radius: 34 },
            { p: [24,26,28], name: 'R KNEE', color: POSE_COLORS.rightLeg, radius: 34 },
            { p: [11,23,25], name: 'L HIP', color: POSE_COLORS.leftLeg, radius: 38 },
            { p: [12,24,26], name: 'R HIP', color: POSE_COLORS.rightLeg, radius: 38 },
            { p: [11,13,15], name: 'L ELBOW', color: POSE_COLORS.leftArm, radius: 30 },
            { p: [12,14,16], name: 'R ELBOW', color: POSE_COLORS.rightArm, radius: 30 },
        ];

        const landmarkGroupColor = (index) => {
            if (index <= 10) return POSE_COLORS.head;
            if ([11,13,15,17,19,21].includes(index)) return POSE_COLORS.leftArm;
            if ([12,14,16,18,20,22].includes(index)) return POSE_COLORS.rightArm;
            if ([23,25,27,29,31].includes(index)) return POSE_COLORS.leftLeg;
            if ([24,26,28,30,32].includes(index)) return POSE_COLORS.rightLeg;
            return POSE_COLORS.torso;
        };

        const calculateAngle = (a, b, c) => {
            if (!a || !b || !c) return null;
            if (
                a.visibility < VISIBILITY_THRESHOLD ||
                b.visibility < VISIBILITY_THRESHOLD ||
                c.visibility < VISIBILITY_THRESHOLD
            ) return null;

            const abx = a.x - b.x;
            const aby = a.y - b.y;
            const cbx = c.x - b.x;
            const cby = c.y - b.y;
            const dot = abx * cbx + aby * cby;
            const mag1 = Math.hypot(abx, aby);
            const mag2 = Math.hypot(cbx, cby);
            if (!mag1 || !mag2) return null;
            const cos = clamp(dot / (mag1 * mag2), -1, 1);
            return toDeg(Math.acos(cos));
        };

        const pointOnCanvas = (landmark) => ({
            x: landmark.x * canvas.width,
            y: landmark.y * canvas.height,
        });

        function detectPerspective(landmarks) {
            if (!landmarks || landmarks.length === 0) return 'side';
            
            // Check visibility of face landmarks (nose = 0, eyes = 1-6)
            let faceVisibilitySum = 0;
            let faceCount = 0;
            for (let i = 0; i <= 10; i++) {
                if (landmarks[i]) {
                    faceVisibilitySum += landmarks[i].visibility;
                    faceCount++;
                }
            }
            const averageFaceVisibility = faceCount > 0 ? (faceVisibilitySum / faceCount) : 0;
            
            // Calculate distance between shoulders in 2D
            const lShoulder = landmarks[11];
            const rShoulder = landmarks[12];
            if (!lShoulder || !rShoulder) return 'side';
            
            const shoulderWidth = Math.abs(lShoulder.x - rShoulder.x);
            
            // If face visibility is high and shoulder width is wide: front view
            // If face visibility is low and shoulder width is wide: back view
            // If shoulder width is very narrow: side view
            if (shoulderWidth > 0.12) {
                if (averageFaceVisibility > 0.5) {
                    return 'front';
                } else {
                    return 'back';
                }
            }
            
            return 'side';
        }

        function getFrontBackAnnotations(landmarks) {
            const annotations = [];
            const lShoulder = landmarks[11];
            const rShoulder = landmarks[12];
            const lHip = landmarks[23];
            const rHip = landmarks[24];
            
            if (lShoulder && rShoulder && lShoulder.visibility > 0.5 && rShoulder.visibility > 0.5) {
                const angle = Math.abs(toDeg(Math.atan2(rShoulder.y - lShoulder.y, rShoulder.x - lShoulder.x)));
                annotations.push({
                    name: 'SHOULDER TILT',
                    angle: angle,
                    point: { x: (lShoulder.x + rShoulder.x) / 2, y: (lShoulder.y + rShoulder.y) / 2 - 0.05 },
                    color: '#22D3EE'
                });
            }
            
            if (lHip && rHip && lHip.visibility > 0.5 && rHip.visibility > 0.5) {
                const angle = Math.abs(toDeg(Math.atan2(rHip.y - lHip.y, rHip.x - lHip.x)));
                annotations.push({
                    name: 'PELVIS TILT',
                    angle: angle,
                    point: { x: (lHip.x + rHip.x) / 2, y: (lHip.y + rHip.y) / 2 - 0.05 },
                    color: '#A3E635'
                });
            }
            
            return annotations;
        }

        function drawCustomAnnotation(anno, scale) {
            ctx.save();
            const x = anno.point.x * canvas.width;
            const y = anno.point.y * canvas.height;
            
            const labelWidth = 100 * scale;
            const labelHeight = 44 * scale;
            
            const labelX = x - labelWidth / 2;
            const labelY = y - labelHeight / 2;
            
            roundedRectPath(ctx, labelX, labelY, labelWidth, labelHeight, 8 * scale);
            ctx.fillStyle = 'rgba(2, 6, 23, 0.86)';
            ctx.fill();
            ctx.strokeStyle = anno.color;
            ctx.lineWidth = 2 * scale;
            ctx.stroke();
            
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = anno.color;
            ctx.font = `700 ${10 * scale}px Inter, sans-serif`;
            ctx.fillText(anno.name, x, y - 9 * scale);
            
            ctx.fillStyle = '#FFFFFF';
            ctx.font = `900 ${16 * scale}px Inter, sans-serif`;
            ctx.fillText(`${anno.angle.toFixed(1)}°`, x, y + 8 * scale);
            ctx.restore();
        }

        function roundedRectPath(context, x, y, width, height, radius) {
            const r = Math.min(radius, width / 2, height / 2);
            context.beginPath();
            context.moveTo(x + r, y);
            context.lineTo(x + width - r, y);
            context.quadraticCurveTo(x + width, y, x + width, y + r);
            context.lineTo(x + width, y + height - r);
            context.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
            context.lineTo(x + r, y + height);
            context.quadraticCurveTo(x, y + height, x, y + height - r);
            context.lineTo(x, y + r);
            context.quadraticCurveTo(x, y, x + r, y);
            context.closePath();
        }

        function drawConnection(startLandmark, endLandmark, color, scale) {
            if (!startLandmark || !endLandmark) return;
            if (
                startLandmark.visibility < VISIBILITY_THRESHOLD ||
                endLandmark.visibility < VISIBILITY_THRESHOLD
            ) return;

            const start = pointOnCanvas(startLandmark);
            const end = pointOnCanvas(endLandmark);
            const confidence = Math.min(startLandmark.visibility, endLandmark.visibility);

            ctx.save();
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.globalAlpha = clamp(0.55 + confidence * 0.45, 0.65, 1);

            // Outline gelap supaya skeleton tetap terbaca pada pakaian/latar terang.
            ctx.strokeStyle = 'rgba(2, 6, 23, 0.9)';
            ctx.lineWidth = 9 * scale;
            ctx.beginPath();
            ctx.moveTo(start.x, start.y);
            ctx.lineTo(end.x, end.y);
            ctx.stroke();

            ctx.strokeStyle = color;
            ctx.lineWidth = 5 * scale;
            ctx.beginPath();
            ctx.moveTo(start.x, start.y);
            ctx.lineTo(end.x, end.y);
            ctx.stroke();
            ctx.restore();
        }

        function drawJoint(landmark, index, scale, emphasized = false) {
            if (!landmark || landmark.visibility < VISIBILITY_THRESHOLD) return;
            const point = pointOnCanvas(landmark);
            const color = landmarkGroupColor(index);
            const outerRadius = (emphasized ? 9 : 6.5) * scale;
            const innerRadius = (emphasized ? 5.5 : 4) * scale;

            ctx.save();
            ctx.globalAlpha = clamp(0.55 + landmark.visibility * 0.45, 0.65, 1);
            ctx.fillStyle = 'rgba(2, 6, 23, 0.92)';
            ctx.beginPath();
            ctx.arc(point.x, point.y, outerRadius, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(point.x, point.y, innerRadius, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }

        function drawTrunkGuide(landmarks, scale) {
            const lShoulder = landmarks[11];
            const rShoulder = landmarks[12];
            const lHip = landmarks[23];
            const rHip = landmarks[24];
            if ([lShoulder, rShoulder, lHip, rHip].some(p => !p || p.visibility < VISIBILITY_THRESHOLD)) return;

            const shoulderMid = {
                x: ((lShoulder.x + rShoulder.x) / 2) * canvas.width,
                y: ((lShoulder.y + rShoulder.y) / 2) * canvas.height,
            };
            const hipMid = {
                x: ((lHip.x + rHip.x) / 2) * canvas.width,
                y: ((lHip.y + rHip.y) / 2) * canvas.height,
            };

            ctx.save();
            ctx.setLineDash([8 * scale, 7 * scale]);
            ctx.strokeStyle = 'rgba(34, 211, 238, 0.8)';
            ctx.lineWidth = 2.5 * scale;
            ctx.beginPath();
            ctx.moveTo(shoulderMid.x, shoulderMid.y);
            ctx.lineTo(hipMid.x, hipMid.y);
            ctx.stroke();
            ctx.restore();
        }

        function drawAngleAnnotation(config, landmarks, scale) {
            const [aIndex, bIndex, cIndex] = config.p;
            const aLandmark = landmarks[aIndex];
            const bLandmark = landmarks[bIndex];
            const cLandmark = landmarks[cIndex];
            const angle = calculateAngle(aLandmark, bLandmark, cLandmark);
            if (angle === null) return;

            const a = pointOnCanvas(aLandmark);
            const b = pointOnCanvas(bLandmark);
            const c = pointOnCanvas(cLandmark);
            const startAngle = Math.atan2(a.y - b.y, a.x - b.x);
            const endAngle = Math.atan2(c.y - b.y, c.x - b.x);

            // Gunakan busur terpendek di antara kedua segmen.
            let delta = endAngle - startAngle;
            while (delta > Math.PI) delta -= Math.PI * 2;
            while (delta < -Math.PI) delta += Math.PI * 2;

            const radius = config.radius * scale;
            const arcEnd = startAngle + delta;

            ctx.save();
            ctx.lineCap = 'round';

            // Outline busur.
            ctx.strokeStyle = 'rgba(2, 6, 23, 0.95)';
            ctx.lineWidth = 8 * scale;
            ctx.beginPath();
            ctx.arc(b.x, b.y, radius, startAngle, arcEnd, delta < 0);
            ctx.stroke();

            // Busur berwarna sesuai bagian tubuh.
            ctx.strokeStyle = config.color;
            ctx.lineWidth = 4 * scale;
            ctx.beginPath();
            ctx.arc(b.x, b.y, radius, startAngle, arcEnd, delta < 0);
            ctx.stroke();

            // Label ditempatkan pada arah tengah busur.
            const middleAngle = startAngle + delta / 2;
            const labelDistance = radius + 35 * scale;
            const labelWidth = 92 * scale;
            const labelHeight = 54 * scale;
            let labelCenterX = b.x + Math.cos(middleAngle) * labelDistance;
            let labelCenterY = b.y + Math.sin(middleAngle) * labelDistance;

            labelCenterX = clamp(labelCenterX, labelWidth / 2 + 8, canvas.width - labelWidth / 2 - 8);
            labelCenterY = clamp(labelCenterY, labelHeight / 2 + 8, canvas.height - labelHeight / 2 - 8);

            const labelX = labelCenterX - labelWidth / 2;
            const labelY = labelCenterY - labelHeight / 2;

            roundedRectPath(ctx, labelX, labelY, labelWidth, labelHeight, 10 * scale);
            ctx.fillStyle = 'rgba(2, 6, 23, 0.86)';
            ctx.fill();
            ctx.strokeStyle = config.color;
            ctx.lineWidth = 2 * scale;
            ctx.stroke();

            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = config.color;
            ctx.font = `700 ${12 * scale}px Inter, sans-serif`;
            ctx.fillText(config.name, labelCenterX, labelCenterY - 13 * scale);

            ctx.fillStyle = '#FFFFFF';
            ctx.font = `900 ${23 * scale}px Inter, sans-serif`;
            ctx.fillText(`${Math.round(angle)}°`, labelCenterX, labelCenterY + 10 * scale);
            ctx.restore();
        }

        function drawPoseLegend(scale, perspective = 'side') {
            const items = [
                ['Torso', POSE_COLORS.torso],
                ['L Arm', POSE_COLORS.leftArm],
                ['R Arm', POSE_COLORS.rightArm],
                ['L Leg', POSE_COLORS.leftLeg],
                ['R Leg', POSE_COLORS.rightLeg],
            ];
            const padding = 10 * scale;
            const rowHeight = 19 * scale;
            const width = 92 * scale;
            const height = padding * 2 + rowHeight * (items.length + 1);
            const x = 12 * scale;
            const y = 12 * scale;

            ctx.save();
            roundedRectPath(ctx, x, y, width, height, 10 * scale);
            ctx.fillStyle = 'rgba(2, 6, 23, 0.68)';
            ctx.fill();
            ctx.strokeStyle = 'rgba(148, 163, 184, 0.28)';
            ctx.lineWidth = 1 * scale;
            ctx.stroke();

            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            ctx.font = `700 ${11 * scale}px Inter, sans-serif`;

            items.forEach(([label, color], index) => {
                const cy = y + padding + rowHeight * index + rowHeight / 2;
                ctx.fillStyle = color;
                ctx.beginPath();
                ctx.arc(x + padding + 4 * scale, cy, 4 * scale, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = '#E2E8F0';
                ctx.fillText(label, x + padding + 14 * scale, cy);
            });

            // Draw Perspective label at the bottom of the legend
            const labelY = y + padding + rowHeight * items.length + rowHeight / 2;
            ctx.fillStyle = '#ccff00';
            ctx.font = `900 ${10 * scale}px Inter, sans-serif`;
            ctx.fillText(perspective.toUpperCase() + ' VIEW', x + padding + 4 * scale, labelY);
            
            ctx.restore();
        }

        if (frames.length > 0) {
            canvas.width = {{ $trial->camera_width ?? 1280 }};
            canvas.height = {{ $trial->camera_height ?? 720 }};
            if (timeline) timeline.max = frames.length - 1;
            if (totalFramesSpan) totalFramesSpan.innerText = frames.length;
            drawFrame(0);
        }

        function drawFrame(idx) {
            if (idx >= frames.length) return;
            const landmarks = frames[idx].landmarks;
            ctx.save();
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (!landmarks || landmarks.length === 0) {
                ctx.restore();
                return;
            }

            const scale = clamp(Math.min(canvas.width, canvas.height) / 720, 0.85, 1.6);

            // 1) Skeleton per area tubuh.
            for (const group of SKELETON_GROUPS) {
                for (const [start, end] of group.connections) {
                    drawConnection(landmarks[start], landmarks[end], group.color, scale);
                }
            }

            // 2) Garis tengah torso membantu membaca trunk alignment.
            drawTrunkGuide(landmarks, scale);

            // 3) Landmark; sendi yang dianalisis dibuat lebih besar.
            const evaluatedJoints = new Set(ANGLES_TO_DRAW.map(item => item.p[1]));
            landmarks.forEach((landmark, index) => {
                drawJoint(landmark, index, scale, evaluatedJoints.has(index));
            });

            // Detect perspective of the current frame
            const perspective = detectPerspective(landmarks);

            // 4) Busur dan label sudut berkontras tinggi.
            if (perspective === 'side') {
                for (const angleConfig of ANGLES_TO_DRAW) {
                    drawAngleAnnotation(angleConfig, landmarks, scale);
                }
            } else {
                // For front/back views, draw specific tilt annotations (Shoulder Tilt & Pelvis Tilt)
                const annos = getFrontBackAnnotations(landmarks);
                for (const anno of annos) {
                    drawCustomAnnotation(anno, scale);
                }
                
                // Draw elbow and knee angles which are visible in front/back views
                const visibleAngles = ANGLES_TO_DRAW.filter(a => a.name.includes('KNEE') || a.name.includes('ELBOW'));
                for (const angleConfig of visibleAngles) {
                    drawAngleAnnotation(angleConfig, landmarks, scale);
                }
            }

            // 5) Legenda warna untuk pembacaan cepat.
            drawPoseLegend(scale, perspective);

            ctx.restore();

            if (timeline) timeline.value = idx;
            if (currentFrameSpan) currentFrameSpan.innerText = idx + 1;
        }

        const frameBaseMs = frames.length
            ? Number(frames[0].timestamp_ms ?? frames[0].ts ?? 0)
            : 0;

        // ---------------------------------------------------------------
        // seekToGaitEvent — called by the Key Gait Moments card buttons.
        // Works in both VIDEO mode (seeks video player) and SKELETON-ONLY
        // mode (seeks the timeline scrubber directly).
        // ---------------------------------------------------------------
        window.seekToGaitEvent = function(timestampMs, eventType) {
            const targetSec = timestampMs / 1000;

            // 1. Find closest skeleton frame using absolute timestampMs
            let closestIdx = 0;
            let minDiff = Infinity;
            for (let i = 0; i < frames.length; i++) {
                const fTs = Number(frames[i].timestamp_ms ?? frames[i].ts ?? 0);
                const diff = Math.abs(fTs - timestampMs);
                if (diff < minDiff) {
                    minDiff = diff;
                    closestIdx = i;
                }
            }

            // 2. Always draw skeleton frame immediately
            drawFrame(closestIdx);
            if (timeline) timeline.value = closestIdx;
            if (currentFrameSpan) currentFrameSpan.innerText = closestIdx + 1;

            // 3. Seek video player using the unified seek function
            const vp = document.getElementById('video-player');
            if (vp && vp.style.display !== 'none') {
                window.seekVideoTo(targetSec);
            }

            // 4. Show biomechanical review tooltip
            if (eventType && typeof window.showGaitMomentTooltip === 'function') {
                window.showGaitMomentTooltip(eventType);
            }
        }; // end seekToGaitEvent

        // ---------------------------------------------------------------
        // Biomechanical review tooltip overlay logic
        // ---------------------------------------------------------------
        const tooltipEl = document.getElementById('ai-moment-tooltip');

        window.hideGaitMomentTooltip = function() {
            if (tooltipEl) {
                tooltipEl.classList.remove('show');
            }
        };

        window.showGaitMomentTooltip = function(eventType) {
            if (!tooltipEl) return;

            const GAIT_PHASE_MAP = {
                'initial_contact': 'landing',
                'midstance': 'lever',
                'toe_off': 'push',
                'max_swing_flexion': 'pull'
            };
            
            const GAIT_PHASE_TITLES = {
                'initial_contact': 'Landing Position',
                'midstance': 'Lever (Mid-Stance)',
                'toe_off': 'Push-Off Position',
                'max_swing_flexion': 'Knee Pull Position'
            };

            const phaseCode = GAIT_PHASE_MAP[eventType];
            if (!phaseCode) return;

            const title = GAIT_PHASE_TITLES[eventType] || 'Gait Moment';
            const titleEl = document.getElementById('tooltip-title');
            if (titleEl) titleEl.textContent = title;

            // Fetch from formReport
            const formReport = {!! $report ? json_encode($report->deterministic_summary_json['form_report'] ?? []) : '[]' !!} || [];
            let phaseData = formReport.find(item => item.code === phaseCode);

            // Fallback to database findings if formReport doesn't have it
            if (!phaseData) {
                const dbFindings = {!! json_encode($trial->findings) !!} || [];
                const dbRecommendations = {!! json_encode($trial->recommendations) !!} || [];

                const FINDINGS_PHASE_MAP = {
                    'LANDING_AHEAD_OF_PELVIS': 'landing',
                    'LOW_LANDING_KNEE_FLEXION': 'landing',
                    'NON_VERTICAL_SHIN_AT_CONTACT': 'landing',
                    'EXCESSIVE_TRUNK_LEAN': 'posture',
                    'LIMITED_TRAILING_LEG': 'push',
                    'LIMITED_HIP_EXTENSION_PROXY': 'push',
                    'DELAYED_LEG_RECOVERY': 'pull',
                    'LOW_SWING_KNEE_FLEXION': 'pull'
                };

                const matchedFindings = dbFindings.filter(f => FINDINGS_PHASE_MAP[f.finding_code] === phaseCode);
                
                if (matchedFindings.length > 0) {
                    const findingsList = matchedFindings.map(f => {
                        if (f.evidence_json && f.evidence_json.metric_value) {
                            return f.evidence_json.metric_value;
                        }
                        return f.explanation_key ? f.explanation_key.replace(/_/g, ' ') : f.finding_code.replace(/_/g, ' ');
                    });

                    // Gather recommendations for these findings
                    const findingIds = matchedFindings.map(f => f.id);
                    const matchedRecs = dbRecommendations.filter(r => findingIds.includes(r.finding_id));
                    const actionsList = matchedRecs.map(r => r.recommendation_code.replace(/_/g, ' '));

                    phaseData = {
                        status: 'warn',
                        summary: 'Biomechanical review detected concerns in this position.',
                        findings: findingsList,
                        actions: actionsList.length > 0 ? actionsList : ['Focus on optimizing joint alignment and strike mechanics.']
                    };
                } else {
                    phaseData = {
                        status: 'ok',
                        summary: 'No issues detected for this position.',
                        findings: [],
                        actions: ['Running form metrics are within optimal ranges in this position.']
                    };
                }
            }

            // Populate HTML
            const statusEl = document.getElementById('tooltip-status');
            if (statusEl) {
                statusEl.textContent = phaseData.status.toUpperCase();
                statusEl.className = 'px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider border ';
                if (phaseData.status === 'ok') {
                    statusEl.classList.add('bg-[#ccff00]/10', 'border-[#ccff00]/30', 'text-[#ccff00]');
                } else if (phaseData.status === 'warn') {
                    statusEl.classList.add('bg-yellow-900/30', 'border-yellow-700', 'text-yellow-400');
                } else if (phaseData.status === 'issue') {
                    statusEl.classList.add('bg-red-900/30', 'border-red-700', 'text-red-400');
                } else {
                    statusEl.classList.add('bg-white/[0.04]', 'border-white/10', 'text-slate-400');
                }
            }

            const summaryEl = document.getElementById('tooltip-summary');
            if (summaryEl) summaryEl.textContent = phaseData.summary || '';

            // Populate findings
            const findingsContainer = document.getElementById('tooltip-findings-container');
            const findingsListEl = document.getElementById('tooltip-findings');
            if (findingsListEl) {
                findingsListEl.innerHTML = '';
                if (phaseData.findings && phaseData.findings.length > 0) {
                    if (findingsContainer) findingsContainer.style.display = 'block';
                    phaseData.findings.forEach(f => {
                        const li = document.createElement('li');
                        li.className = 'flex items-start gap-1.5 mb-1';
                        li.innerHTML = `<span class="text-slate-400 mt-0.5 shrink-0">•</span> <span class="text-slate-200">${f}</span>`;
                        findingsListEl.appendChild(li);
                    });
                } else {
                    if (findingsContainer) findingsContainer.style.display = 'none';
                }
            }

            // Populate actions
            const actionsContainer = document.getElementById('tooltip-actions-container');
            const actionsListEl = document.getElementById('tooltip-actions');
            if (actionsListEl) {
                actionsListEl.innerHTML = '';
                if (phaseData.actions && phaseData.actions.length > 0) {
                    if (actionsContainer) actionsContainer.style.display = 'block';
                    phaseData.actions.forEach(a => {
                        const li = document.createElement('li');
                        li.className = 'flex items-start gap-1.5 mb-1';
                        li.innerHTML = `<span class="text-[#ccff00] mt-0.5 shrink-0">•</span> <span class="text-slate-100">${a}</span>`;
                        actionsListEl.appendChild(li);
                    });
                } else {
                    if (actionsContainer) actionsContainer.style.display = 'none';
                }
            }

            // Show tooltip
            tooltipEl.classList.add('show');
        };

        // Attach event listeners to dismiss tooltip on timeline scrub or playback
        if (videoPlayer) {
            videoPlayer.addEventListener('play', window.hideGaitMomentTooltip);
        }
        const videoTimeline = document.getElementById('video-timeline');
        if (videoTimeline) {
            videoTimeline.addEventListener('input', window.hideGaitMomentTooltip);
        }
        const skTimeline = document.getElementById('timeline');
        if (skTimeline) {
            skTimeline.addEventListener('input', window.hideGaitMomentTooltip);
        }
        const videoPlayBtn = document.getElementById('video-play-btn');
        if (videoPlayBtn) {
            videoPlayBtn.addEventListener('click', window.hideGaitMomentTooltip);
        }
        const skPlayBtn = document.getElementById('play-btn');
        if (skPlayBtn) {
            skPlayBtn.addEventListener('click', window.hideGaitMomentTooltip);
        }

        // Time synchronization with Video Player
        window.syncSkeletonFrame = function() {
            if (window.currentViewMode !== 'video' || !videoPlayer || frames.length === 0) return;
            const elapsedMs = videoPlayer.currentTime * 1000;
            
            // If the video time is outside the skeleton frames range, clear the canvas
            const firstFrameTs = Number(frames[0].timestamp_ms ?? frames[0].ts ?? 0);
            const lastFrameTs = Number(frames[frames.length - 1].timestamp_ms ?? frames[frames.length - 1].ts ?? 0);
            if (elapsedMs < firstFrameTs - 100 || elapsedMs > lastFrameTs + 100) {
                const canvasPlayback = document.getElementById('playback-canvas');
                if (canvasPlayback) {
                    const ctxPlayback = canvasPlayback.getContext('2d');
                    ctxPlayback.clearRect(0, 0, canvasPlayback.width, canvasPlayback.height);
                }
                return;
            }

            let closestFrameIdx = 0;
            let minDiff = Infinity;
            for (let i = 0; i < frames.length; i++) {
                const frameTs = Number(frames[i].timestamp_ms ?? frames[i].ts ?? 0);
                const diff = Math.abs(frameTs - elapsedMs);
                if (diff < minDiff) {
                    minDiff = diff;
                    closestFrameIdx = i;
                }
            }
            drawFrame(closestFrameIdx);
        };

        if (videoPlayer && frames.length > 0) {
            const startTs = frames[0] ? Number(frames[0].timestamp_ms ?? frames[0].ts ?? 0) : 0;
            const endTs = frames[frames.length - 1] ? Number(frames[frames.length - 1].timestamp_ms ?? frames[frames.length - 1].ts ?? 0) : 0;
            
            let startSec = startTs / 1000;
            let endSec = endTs / 1000;

            function applyRange() {
                const duration = videoPlayer.duration || 0;
                if (duration > 0) {
                    if (startSec < 0 || startSec >= duration) {
                        startSec = 0;
                    }
                    if (endSec <= startSec || endSec > duration) {
                        endSec = duration;
                    }
                }
                
                const vtl = document.getElementById('video-timeline');
                if (vtl) {
                    vtl.min = startSec;
                    vtl.max = endSec;
                    vtl.step = 0.01;
                    const val = parseFloat(vtl.value);
                    if (val < startSec || val > endSec) {
                        vtl.value = startSec;
                    }
                }
                
                const durEl = document.getElementById('video-duration');
                if (durEl && window.fmtVideoTime) {
                    durEl.textContent = window.fmtVideoTime(endSec);
                }
            }

            videoPlayer.addEventListener('loadedmetadata', applyRange);
            window.addEventListener('_vpRangeReady', applyRange);

            // Set initial start time on load
            videoPlayer.addEventListener('loadedmetadata', () => {
                if (videoPlayer.currentTime < startSec || videoPlayer.currentTime > endSec) {
                    window.seekVideoTo(startSec);
                }
            });

            if (videoPlayer.readyState >= 1 && (videoPlayer.currentTime < startSec || videoPlayer.currentTime > endSec)) {
                window.seekVideoTo(startSec);
            }

            videoPlayer.addEventListener('timeupdate', () => {
                // Only auto-loop when actually playing — never interrupt manual seeking
                if (!videoPlayer.paused && videoPlayer.currentTime > endSec) {
                    videoPlayer.pause();
                    window.seekVideoTo(startSec);
                }
                
                const vtl = document.getElementById('video-timeline');
                if (vtl && !vtl._dragging) {
                    vtl.value = videoPlayer.currentTime;
                }

                const curTime = document.getElementById('video-current-time');
                if (curTime && window.fmtVideoTime) {
                    curTime.textContent = window.fmtVideoTime(videoPlayer.currentTime);
                }

                window.syncSkeletonFrame();
            });

            videoPlayer.addEventListener('seeked', window.syncSkeletonFrame);

            // Network and Error Handling (Requirement I)
            const logVideoError = (evt) => {
                console.error("Video player event error/stalled/waiting:", evt.type, {
                    error: videoPlayer.error,
                    duration: videoPlayer.duration,
                    currentTime: videoPlayer.currentTime,
                    readyState: videoPlayer.readyState,
                    networkState: videoPlayer.networkState
                });
                
                if (evt.type === 'error') {
                    const errOverlay = document.getElementById('video-error-overlay');
                    if (errOverlay) {
                        errOverlay.style.display = 'flex';
                    }
                }
            };
            
            videoPlayer.addEventListener('error', logVideoError);
            videoPlayer.addEventListener('stalled', logVideoError);
            videoPlayer.addEventListener('waiting', logVideoError);
            
            let isPlayingVideo = false;
            const videoPlayLoop = (ts) => {
                if (!isPlayingVideo || window.currentViewMode !== 'video') return;
                
                if (videoPlayer.currentTime > endSec) {
                    isPlayingVideo = false;
                    videoPlayer.pause();
                    window.seekVideoTo(startSec);
                } else {
                    window.syncSkeletonFrame();
                    requestAnimationFrame(videoPlayLoop);
                }
            };

            videoPlayer.addEventListener('play', () => {
                if (videoPlayer.currentTime < startSec || videoPlayer.currentTime >= endSec) {
                    window.seekVideoTo(startSec);
                }
                isPlayingVideo = true;
                requestAnimationFrame(videoPlayLoop);
            });
            videoPlayer.addEventListener('pause', () => { isPlayingVideo = false; });
            videoPlayer.addEventListener('ended', () => { 
                isPlayingVideo = false; 
                window.seekVideoTo(startSec);
            });
        }

        // Skeleton-only Playback Loop
        function playLoop(ts) {
            if (!isPlaying || window.currentViewMode !== 'skeleton') return;
            if (ts - lastFrameTime >= 33) {
                currentFrameIdx++;
                if (currentFrameIdx >= frames.length) {
                    currentFrameIdx = 0;
                    isPlaying = false;
                    if (playIcon) playIcon.className = "fas fa-play";
                    cancelAnimationFrame(animationId);
                    drawFrame(currentFrameIdx);
                    return;
                }
                drawFrame(currentFrameIdx);
                lastFrameTime = ts;
            }
            animationId = requestAnimationFrame(playLoop);
        }

        if (playBtn) {
            playBtn.addEventListener('click', () => {
                isPlaying = !isPlaying;
                if (playIcon) playIcon.className = isPlaying ? "fas fa-pause" : "fas fa-play";
                if (isPlaying) {
                    if (currentFrameIdx >= frames.length - 1) currentFrameIdx = 0;
                    lastFrameTime = performance.now();
                    animationId = requestAnimationFrame(playLoop);
                } else {
                    cancelAnimationFrame(animationId);
                }
            });
        }

        if (timeline) {
            timeline.addEventListener('input', (e) => {
                currentFrameIdx = parseInt(e.target.value);
                drawFrame(currentFrameIdx);
                if (isPlaying) {
                    isPlaying = false;
                    if (playIcon) playIcon.className = "fas fa-play";
                    cancelAnimationFrame(animationId);
                }
            });
        }
        // ---------------------------------------------------------------
        // Video Export Engine (Frame-by-Frame Offline Export)
        // ---------------------------------------------------------------
        const exportBtn = document.getElementById('btn-export-video');
        if (exportBtn && videoPlayer) {
            exportBtn.addEventListener('click', async () => {
                if (frames.length === 0) {
                    showToast("No skeleton data available to export.", true);
                    return;
                }

                // Calculate bounds at the beginning of scope so they are shared
                const startTs = frames[0] ? Number(frames[0].timestamp_ms ?? frames[0].ts ?? 0) : 0;
                const endTs = frames[frames.length - 1] ? Number(frames[frames.length - 1].timestamp_ms ?? frames[frames.length - 1].ts ?? 0) : 0;
                const startSecVal = startTs / 1000;
                const endSecVal = endTs / 1000;

                // Show progress overlay
                const overlay = document.getElementById('export-overlay');
                const progressBar = document.getElementById('export-progress');
                const progressText = document.getElementById('export-percent');
                if (overlay) overlay.classList.remove('hidden');

                // Mute and pause player
                const wasMuted = videoPlayer.muted;
                const wasPaused = videoPlayer.paused;
                videoPlayer.pause();
                videoPlayer.muted = true;

                // Setup export canvas
                const exportCanvas = document.createElement('canvas');
                exportCanvas.width = videoPlayer.videoWidth || 1280;
                exportCanvas.height = videoPlayer.videoHeight || 720;
                const exportCtx = exportCanvas.getContext('2d');

                // Setup MediaRecorder on export canvas stream
                const fps = 30;
                const stream = exportCanvas.captureStream(fps);
                
                let mimeType = 'video/webm;codecs=vp9';
                if (!MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = 'video/webm;codecs=vp8';
                }
                if (!MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = 'video/webm';
                }
                
                const recorder = new MediaRecorder(stream, { mimeType });
                const chunks = [];
                recorder.ondataavailable = e => {
                    if (e.data && e.data.size > 0) chunks.push(e.data);
                };
                
                recorder.onstop = () => {
                    const blob = new Blob(chunks, { type: mimeType });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `running-analysis-{{ $trial->id }}.webm`;
                    a.click();
                    URL.revokeObjectURL(url);

                    // Restore player state
                    videoPlayer.muted = wasMuted;
                    window.seekVideoTo(startSecVal);
                    if (!wasPaused) videoPlayer.play();

                    if (overlay) overlay.classList.add('hidden');
                };

                recorder.start();

                // Export frame-by-frame
                let currentSec = startSecVal;
                const totalDuration = endSecVal - startSecVal;
                const step = 1 / fps;

                const exportNextFrame = async () => {
                    if (currentSec > endSecVal) {
                        recorder.stop();
                        return;
                    }

                    // Seek video player to current timestamp
                    window.seekVideoTo(currentSec);

                    // Wait for seeked event
                    await new Promise(resolve => {
                        const onSeeked = () => {
                            videoPlayer.removeEventListener('seeked', onSeeked);
                            resolve();
                        };
                        videoPlayer.addEventListener('seeked', onSeeked);
                    });

                    // Draw video frame to export canvas
                    exportCtx.drawImage(videoPlayer, 0, 0, exportCanvas.width, exportCanvas.height);

                    // Sync the skeleton frame to playbackCanvas
                    window.syncSkeletonFrame();

                    // Overlay the skeleton canvas onto export canvas
                    exportCtx.drawImage(canvas, 0, 0, exportCanvas.width, exportCanvas.height);

                    // Update progress UI
                    const elapsed = currentSec - (startTs / 1000);
                    const percent = Math.min(100, Math.round((elapsed / totalDuration) * 100));
                    if (progressBar) progressBar.style.width = `${percent}%`;
                    if (progressText) progressText.innerText = `${percent}%`;

                    // Advance time
                    currentSec += step;
                    requestAnimationFrame(exportNextFrame);
                };

                // Start the frame loop
                exportNextFrame();
            });
        }
        
    } catch (e) {
        console.error("Error parsing pose data", e);
    }
});
</script>
@endif

{{-- Video Export Progress Overlay --}}
<div id="export-overlay" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex flex-col items-center justify-center z-50 hidden">
    <div class="bg-slate-900 border border-slate-800 p-8 rounded-2xl max-w-sm w-full mx-4 shadow-2xl text-center space-y-4">
        <div class="relative w-20 h-20 mx-auto">
            <div class="absolute inset-0 rounded-full border-4 border-slate-800"></div>
            <div class="absolute inset-0 rounded-full border-4 border-t-[#ccff00] animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <i class="fas fa-video text-2xl text-[#ccff00]"></i>
            </div>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-white">Mengekspor Video</h3>
            <p class="text-xs text-slate-400 mt-1">Menggabungkan video dengan skeleton overlay...</p>
        </div>
        <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
            <div id="export-progress" class="bg-[#ccff00] h-full w-0 transition-all duration-200"></div>
        </div>
        <div id="export-percent" class="text-xs font-bold text-[#ccff00] tracking-wider">0%</div>
    </div>
</div>

@endsection