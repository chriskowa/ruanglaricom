@extends('layouts.pacerhub')
@php
    $withSidebar = true;

    $statusColors = [
        'capturing'       => 'bg-amber-900 text-amber-300 border-amber-700',
        'queued'          => 'bg-blue-900 text-blue-300 border-blue-700',
        'analyzing'       => 'bg-yellow-900 text-yellow-300 border-yellow-700',
        'review_required' => 'bg-orange-900 text-orange-300 border-orange-700',
        'approved'        => 'bg-green-900 text-green-300 border-green-700',
        'published'       => 'bg-[#ccff00] text-black border-[#ccff00]',
        'invalid'         => 'bg-red-900 text-red-300 border-red-700',
        'failed'          => 'bg-red-900 text-red-300 border-red-700',
    ];
    $statusColor = $statusColors[$trial->status] ?? 'bg-slate-800 text-slate-300 border-slate-700';

    $report = $trial->latestReport;
    $narrative = $report ? $report->runner_narrative_json : null;
    $coachMessage = $narrative['coach_message'] ?? null;
    $positives = $narrative['positives'] ?? [];
    $score = $trial->quality_score ? round((float) $trial->quality_score * 100) : null;
    
    $pdfRoute = auth()->user()?->role === 'admin'
        ? 'admin.running-analysis.trials.pdf'
        : 'runner.running-analysis.trials.pdf';

    $videoArtifact = $trial->artifacts->where('type', 'video_clip')->first();
@endphp

@section('title', 'Review Trial - ' . $trial->runner->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans bg-[#060a17]">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb -->
        <div class="mb-6 flex items-center text-slate-400 text-sm font-medium">
            <a href="{{ route('admin.running-analysis.sessions.show', $trial->session) }}" class="hover:text-[#ccff00] transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Session
            </a>
        </div>

        @if(session('success'))
        <div class="bg-green-900/50 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-900/50 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        </div>
        @endif

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <img src="{{ $trial->runner->avatar_url ?? asset('images/default-avatar.png') }}" class="w-16 h-16 rounded-full border-2 border-slate-700 object-cover bg-slate-800">
                <div>
                    <h1 class="text-3xl font-black italic tracking-tighter uppercase text-white">{{ $trial->runner->name }}</h1>
                    <div class="text-slate-400 text-sm mt-1">
                        Trial #{{ $trial->attempt_no }}
                        &bull; {{ $trial->created_at->format('d M Y, H:i') }}
                        @if($trial->camera_width)
                            &bull; {{ $trial->camera_width }}×{{ $trial->camera_height }} @ {{ $trial->camera_fps }} fps
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusColor }}">
                    {{ str_replace('_', ' ', strtoupper($trial->status)) }}
                </span>

                {{-- Re-analyze button for trials stuck in capturing/failed that already have artifacts --}}
                @if(in_array($trial->status, ['capturing', 'failed']) && $trial->artifacts->where('type', 'pose_landmarks')->count() > 0)
                <form method="POST" action="{{ route('admin.running-analysis.trials.analyze-sync', $trial) }}" class="inline">
                    @csrf
                    <button type="submit" id="btn-reanalyze"
                        class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-amber-600 border border-amber-500
                               text-white hover:bg-amber-500 transition-all duration-200 shadow-sm"
                        onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Analyzing...'; this.form.submit();">
                        <i class="fas fa-redo"></i>
                        Re-analyze
                    </button>
                </form>
                @endif

                {{-- Export Video with Skeleton --}}
                @if($poseData && $videoArtifact)
                <button type="button" id="btn-export-video"
                   title="Export Video with Skeleton Overlay"
                   class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-slate-800 border border-slate-700
                          text-slate-300 hover:bg-[#ccff00] hover:text-black hover:border-[#ccff00]
                          transition-all duration-200 shadow-sm group">
                    <i class="fas fa-video text-[#ccff00] group-hover:text-black transition-colors"></i>
                    Export Video
                </button>
                @endif

                {{-- PDF download — only makes sense once there is analysis data --}}
                @if($trial->latestReport || $trial->findings->count() > 0 || $trial->metrics->count() > 0)
                <a href="{{ route($pdfRoute, $trial) }}"
                   target="_blank"
                   id="btn-download-pdf"
                   title="Download PDF Report"
                   class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-slate-800 border border-slate-700
                          text-slate-300 hover:bg-[#ccff00] hover:text-black hover:border-[#ccff00]
                          transition-all duration-200 shadow-sm group">
                    <i class="fas fa-file-pdf text-red-400 group-hover:text-black transition-colors"></i>
                    Download PDF
                </a>
                @endif
            </div>
        </div>

        {{-- Error banner for trials that failed analysis --}}
        @if($trial->invalid_reason)
        <div class="bg-red-900/30 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-6 flex items-start gap-3">
            <i class="fas fa-exclamation-triangle mt-0.5 text-red-400"></i>
            <div>
                <p class="font-bold text-sm text-red-200">Analysis gagal:</p>
                <p class="text-xs mt-1 font-mono">{{ $trial->invalid_reason }}</p>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Panel: Visualizer -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-[0_0_20px_rgba(204,255,0,0.05)]">



                    <!-- View Mode Tabs -->
                    @if($poseData || $videoArtifact)
                    <div class="flex border-b border-slate-800 bg-slate-950">
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

                    <div class="aspect-video bg-black relative flex items-center justify-center">
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

                        <!-- Floating AI Biomechanical Annotation Tooltip -->
                        <style>
                        #ai-moment-tooltip {
                            transition: opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1), transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
                        }
                        #ai-moment-tooltip.show {
                            opacity: 1 !important;
                            pointer-events: auto !important;
                            transform: translateY(0) !important;
                        }
                        </style>
                        <div id="ai-moment-tooltip" class="absolute top-4 right-4 z-30 max-w-[280px] md:max-w-[340px] bg-slate-950/90 backdrop-blur-md border border-slate-800/80 rounded-xl p-4 shadow-2xl transition-all duration-300 opacity-0 pointer-events-none transform translate-y-2">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="min-w-0">
                                    <div class="text-[9px] font-mono text-cyan-400 uppercase tracking-widest mb-0.5">AI Biomechanics Feedback</div>
                                    <h4 id="tooltip-title" class="text-sm font-black text-white italic tracking-tight truncate">Landing Position</h4>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span id="tooltip-status" class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border">OK</span>
                                    <button type="button" onclick="window.hideGaitMomentTooltip()" class="text-slate-400 hover:text-white transition-colors text-xs p-1">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <p id="tooltip-summary" class="text-slate-300 text-xs mb-3 font-medium leading-relaxed"></p>
                            
                            <div id="tooltip-findings-container" class="mb-3">
                                <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Findings</div>
                                <ul id="tooltip-findings" class="space-y-1 text-slate-300 text-[11px] leading-relaxed"></ul>
                            </div>
                            
                            <div id="tooltip-actions-container">
                                <div class="text-[9px] font-bold text-[#ccff00] uppercase tracking-widest mb-1.5">Correction Actions</div>
                                <ul id="tooltip-actions" class="space-y-1 text-slate-300 text-[11px] leading-relaxed"></ul>
                            </div>
                        </div>

                        {{-- Video error overlay --}}
                        <div id="video-error-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-black/90 gap-3" style="display: none;">
                            <i class="fas fa-exclamation-circle text-3xl text-red-500"></i>
                            <div class="text-slate-350 text-sm text-center px-4">
                                <p class="font-bold text-white mb-1">Failed to load video</p>
                                <p class="text-xs">The video could not be loaded or played. Please try reloading the page.</p>
                            </div>
                        </div>

                        @if(!$poseData && !$videoArtifact)
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/80 gap-3">
                            <i class="fas fa-exclamation-triangle text-3xl text-yellow-500"></i>
                            <div class="text-slate-400 font-mono text-sm text-center">
                                <p class="font-bold text-white mb-1">No pose data or video found.</p>
                                <p class="text-xs">Upload may have failed during capture. Try recapturing this runner.</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Custom Video Controls (mirrors skeleton controls style) --}}
                    @if($videoArtifact)
                    <div id="video-controls" class="p-4 bg-slate-800 border-t border-slate-700 flex items-center gap-4"
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
                    <div id="skeleton-controls" class="p-4 bg-slate-800 border-t border-slate-700 flex items-center gap-4" style="{{ $videoArtifact ? 'display:none' : '' }}">
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
                    
                    <!-- Key Gait Moments — Horizontal Scroll Slider -->
                    @if($trial->gaitEvents->count() > 0)
                    @php
                        $sortedEvents = $trial->gaitEvents->sortBy('timestamp_ms')->values();
                        $eventBaseMs  = $sortedEvents->min('timestamp_ms') ?? 0;
                        $totalEvents  = $sortedEvents->count();
                    @endphp
                    <div class="border-t border-slate-800/80 bg-slate-900/40">
                        <!-- Header row -->
                        <div class="px-4 pt-3 pb-1 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                <i class="fas fa-camera text-[#ccff00]"></i>
                                Key Gait Moments
                                <span class="bg-slate-800 text-slate-400 border border-slate-700 rounded-full text-[9px] px-1.5 py-px font-black">{{ $totalEvents }}</span>
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
                            style="scroll-snap-type: x mandatory; -ms-overflow-style: none; scrollbar-width: none;">

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
                                $phaseColor = match($event->event_type) {
                                    'initial_contact'   => 'from-sky-900/60 to-slate-900 border-sky-800/60 hover:border-sky-500/80',
                                    'midstance'         => 'from-violet-900/60 to-slate-900 border-violet-800/60 hover:border-violet-500/80',
                                    'toe_off'           => 'from-orange-900/60 to-slate-900 border-orange-800/60 hover:border-orange-500/80',
                                    'max_swing_flexion' => 'from-emerald-900/60 to-slate-900 border-emerald-800/60 hover:border-emerald-500/80',
                                    default             => 'from-slate-800/60 to-slate-900 border-slate-700/60 hover:border-[#ccff00]/60',
                                };
                                $iconColor = match($event->event_type) {
                                    'initial_contact'   => 'text-sky-400',
                                    'midstance'         => 'text-violet-400',
                                    'toe_off'           => 'text-orange-400',
                                    'max_swing_flexion' => 'text-emerald-400',
                                    default             => 'text-[#ccff00]',
                                };
                                $sideIsLeft = $event->side === 'left';
                                $sidePill   = $sideIsLeft
                                    ? 'bg-lime-950/60 border-lime-700 text-lime-400'
                                    : 'bg-blue-950/60 border-blue-700 text-blue-400';
                            @endphp
                            <button type="button"
                                data-gait-card="{{ $idx }}"
                                onclick="seekToGaitEvent({{ $event->timestamp_ms }}, '{{ $event->event_type }}'); if(window._highlightGaitCard) window._highlightGaitCard({{ $idx }});"
                                class="gait-moment-card flex-none w-[160px] flex flex-col items-start text-left p-3 rounded-xl
                                       bg-gradient-to-b {{ $phaseColor }}
                                       border transition-all duration-300 group
                                       focus:outline-none focus:ring-1 focus:ring-[#ccff00]/60"
                                style="scroll-snap-align: start;">

                                <!-- Sequence + side -->
                                <div class="flex items-center justify-between w-full mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full bg-slate-700 border border-slate-600 text-slate-300 text-[8px] font-black flex items-center justify-center flex-none">{{ $seqNo }}</span>
                                        <span class="px-1.5 py-px rounded border text-[8px] font-black uppercase {{ $sidePill }}">{{ $event->side }}</span>
                                    </div>
                                    <i class="fas {{ $icon }} {{ $iconColor }} text-[11px] group-hover:scale-110 transition-transform duration-200"></i>
                                </div>

                                <!-- Phase name -->
                                <div class="text-[11px] font-black text-white leading-tight mb-px tracking-tight">{{ $label }}</div>
                                <div class="text-[9px] font-medium text-slate-500 uppercase tracking-widest mb-2.5">{{ $subLabel }}</div>

                                <!-- Timestamp -->
                                <div class="mt-auto w-full flex items-center justify-between border-t border-white/5 pt-2">
                                    <span class="font-mono text-[10px] text-slate-400 font-bold">+{{ $relSec }}s</span>
                                    <span class="text-[8px] uppercase tracking-wider font-bold text-slate-600 group-hover:text-[#ccff00] transition-colors duration-200 flex items-center gap-0.5">
                                        Seek <i class="fas fa-play text-[7px] ml-0.5"></i>
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
                                c.classList.remove('ring-1', 'ring-[#ccff00]', 'scale-[1.03]');
                            });
                            if (activeCard) activeCard.classList.remove('ring-1', 'ring-[#ccff00]', 'scale-[1.03]');
                            const target = document.querySelector('[data-gait-card="' + dataIdx + '"]');
                            if (target) {
                                target.classList.add('ring-1', 'ring-[#ccff00]', 'scale-[1.03]');
                                activeCard = target;
                                // scroll card into view inside track
                                target.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
                            }
                        };
                    })();
                    </script>
                    @endif
                </div>


                <!-- Action Bar -->
                @if(auth()->user()?->role === 'admin')
                    @if(!in_array($trial->status, ['invalid', 'published']))
                    <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl flex gap-3 justify-end items-center">
                        <span class="text-xs text-slate-500 mr-auto italic">Actions update trial status permanently.</span>
                        @if($videoArtifact)
                        <a href="{{ route('admin.running-analysis.upload-video.form', $trial->session_id) }}?trial_id={{ $trial->id }}"
                            class="px-6 py-2 rounded text-sm font-bold bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 transition-colors">
                            Re-analyze
                        </a>
                        @endif
                        <button onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                            class="px-6 py-2 rounded text-sm font-bold bg-red-900/50 text-red-400 border border-red-700 hover:bg-red-900 transition-colors">
                            Reject Trial
                        </button>
                        @if(in_array($trial->status, ['queued', 'analyzing', 'failed']))
                        <form action="{{ route('admin.running-analysis.trials.analyze-sync', $trial) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit"
                                class="px-6 py-2 rounded text-sm font-bold bg-[#ccff00] text-black border border-[#ccff00] hover:bg-[#b3e600] transition-colors shadow-[0_0_10px_rgba(204,255,0,0.2)]">
                                <i class="fas fa-play mr-2"></i> Analyze Now
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.running-analysis.trials.approve', $trial) }}" method="POST" id="approve-form">
                            @csrf
                            <button type="submit" onclick="return confirm('Approve and publish this trial to the runner?')"
                                class="px-6 py-2 rounded text-sm font-bold bg-[#ccff00] text-black border border-[#ccff00] hover:bg-[#b3e600] transition-colors shadow-[0_0_10px_rgba(204,255,0,0.2)]">
                                Approve & Publish
                            </button>
                        </form>
                        @endif
                    </div>
                    @else
                    <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl flex items-center gap-3">
                        @if($trial->status === 'published')
                            <i class="fas fa-check-circle text-[#ccff00]"></i>
                            <span class="text-sm text-slate-300">This trial has been <strong class="text-[#ccff00]">published</strong> and is visible to the runner.</span>
                        @else
                            <i class="fas fa-times-circle text-red-400"></i>
                            <span class="text-sm text-slate-300">This trial has been <strong class="text-red-400">rejected</strong>.
                                @if($trial->invalid_reason) Reason: {{ $trial->invalid_reason }} @endif
                            </span>
                        @endif
                    </div>
                    @endif
                @endif
            </div>

            <!-- Right Panel: Data -->
            <div class="space-y-6">
                <!-- Score Card -->
                @if($score)
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-lg relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 text-slate-800/10 text-8xl font-black italic tracking-tighter group-hover:scale-110 transition-transform select-none">
                        SCORE
                    </div>
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><i class="fas fa-medal mr-2 text-[#ccff00]"></i> Form Score</h2>
                    <div class="flex items-baseline gap-2">
                        <span class="text-5xl font-black italic text-white tracking-tighter">{{ $score }}</span>
                        <span class="text-slate-500 text-sm">/ 100</span>
                    </div>
                </div>
                @endif

                <!-- Coach Message & Positives -->
                @if($coachMessage || count($positives) > 0)
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg space-y-4">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider"><i class="fas fa-comment-dots mr-2 text-[#ccff00]"></i> Coach Feedback</h2>
                    
                    @if($coachMessage)
                    <div class="p-4 bg-slate-950 border border-slate-850 rounded-lg text-sm text-slate-300 leading-relaxed font-medium relative">
                        <i class="fas fa-quote-left absolute top-3 left-3 text-slate-800 text-xl"></i>
                        <div class="pl-6 italic">"{{ is_array($coachMessage) ? implode(' ', array_values($coachMessage)) : $coachMessage }}"</div>
                    </div>
                    @endif

                    @if(count($positives) > 0)
                    <div class="space-y-2">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">What Went Well</div>
                        @foreach($positives as $pos)
                        <div class="flex items-start gap-2.5 text-xs text-slate-300">
                            <span class="w-5 h-5 shrink-0 rounded-full bg-green-950/60 border border-green-800/40 flex items-center justify-center text-green-400 text-[10px] mt-0.5">
                                <i class="fas fa-check"></i>
                            </span>
                            <div class="leading-relaxed">
                                @if(is_array($pos))
                                    @if(isset($pos['title']))
                                        <strong class="text-white block">{{ $pos['title'] }}</strong>
                                        @if(isset($pos['description']))
                                            <span class="text-slate-400 text-[11px] block mt-0.5">{{ $pos['description'] }}</span>
                                        @endif
                                    @else
                                        {{ implode(' ', array_values($pos)) }}
                                    @endif
                                @else
                                    {{ $pos }}
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

                <!-- Recommendations -->
                @if($trial->recommendations->count() > 0)
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3"><i class="fas fa-dumbbell mr-2 text-[#ccff00]"></i> Training Program</h2>
                    <div class="space-y-4">
                        @php
                            $cues = $trial->recommendations->where('type', \App\Models\RunningAnalysis\Recommendation::TYPE_CUE);
                            $drills = $trial->recommendations->where('type', \App\Models\RunningAnalysis\Recommendation::TYPE_DRILL);
                            $strengths = $trial->recommendations->where('type', \App\Models\RunningAnalysis\Recommendation::TYPE_STRENGTH);
                        @endphp

                        @if($cues->count() > 0)
                        <div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Posture & Gait Cues</div>
                            <div class="space-y-2">
                                @foreach($cues as $cue)
                                <div class="p-2.5 bg-slate-950 border border-slate-850 rounded-lg text-xs">
                                    <div class="font-bold text-[#ccff00] mb-0.5">{{ $cue->title }}</div>
                                    <div class="text-slate-400 leading-relaxed">{{ $cue->description }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($drills->count() > 0)
                        <div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Recommended Drills</div>
                            <div class="space-y-2">
                                @foreach($drills as $drill)
                                <div class="p-2.5 bg-slate-950 border border-slate-850 rounded-lg text-xs">
                                    <div class="font-bold text-white mb-0.5">{{ $drill->title }}</div>
                                    <div class="text-slate-400 leading-relaxed">{{ $drill->description }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($strengths->count() > 0)
                        <div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Strength Exercises</div>
                            <div class="space-y-2">
                                @foreach($strengths as $strength)
                                <div class="p-2.5 bg-slate-950 border border-slate-850 rounded-lg text-xs">
                                    <div class="font-bold text-white mb-0.5">{{ $strength->title }}</div>
                                    <div class="text-slate-400 leading-relaxed">{{ $strength->description }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Artifacts Info -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3"><i class="fas fa-file-archive mr-2 text-[#ccff00]"></i> Artifacts</h2>
                    @if($trial->artifacts->count() > 0)
                        <div class="space-y-2">
                            @foreach($trial->artifacts as $artifact)
                            <div class="flex items-center justify-between text-xs py-2 border-b border-slate-800 last:border-0">
                                <span class="text-slate-400 font-mono">{{ $artifact->type }}</span>
                                <span class="text-slate-500">{{ number_format($artifact->size_bytes / 1024, 1) }} KB</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-slate-600 text-sm italic py-4 text-center">No artifacts uploaded.</div>
                    @endif
                </div>

                <!-- Metrics -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3"><i class="fas fa-chart-line mr-2 text-[#ccff00]"></i> Biomechanical Metrics</h2>
                    @if($trial->metrics->count() > 0)
                        <div class="space-y-3">
                            @foreach($trial->metrics as $metric)
                            <div class="flex justify-between items-center pb-2 border-b border-slate-800 last:border-0 last:pb-0">
                                <div class="text-slate-300 text-sm">
                                    {{ str_replace('_', ' ', $metric->metric_code) }}
                                    @if($metric->side) <span class="text-xs text-slate-500 ml-1">({{ $metric->side }})</span> @endif
                                </div>
                                <div class="text-[#ccff00] font-mono font-bold text-sm">
                                    {{ round((float) $metric->value_decimal, 1) }}
                                    <span class="text-slate-500 font-normal text-xs">{{ $metric->unit }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-slate-600 text-sm italic py-6 text-center bg-slate-950 rounded-lg border border-slate-800/50">Metrics not generated yet.</div>
                    @endif
                </div>

                <!-- Findings -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3"><i class="fas fa-search mr-2 text-[#ccff00]"></i> AI Findings</h2>
                    @if($trial->findings->count() > 0)
                        <div class="space-y-3">
                            @foreach($trial->findings as $finding)
                            @php
                                $sevColor = match($finding->severity) {
                                    'significant' => 'text-red-400 bg-red-900/30 border-red-700',
                                    'moderate'    => 'text-yellow-400 bg-yellow-900/30 border-yellow-700',
                                    default       => 'text-blue-400 bg-blue-900/30 border-blue-700',
                                };
                            @endphp
                            <div class="p-3 rounded-lg bg-slate-800 border border-slate-700">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $sevColor }} uppercase">{{ $finding->severity }}</span>
                                    <span class="text-xs text-slate-400 font-mono">{{ $finding->finding_code }}</span>
                                </div>
                                <div class="text-sm text-slate-300">{{ str_replace('_', ' ', ucfirst(strtolower($finding->explanation_key ?? $finding->finding_code))) }}</div>
                                @if($finding->evidence_json)
                                    <div class="text-xs text-slate-500 mt-1">{{ is_array($finding->evidence_json) ? implode(', ', array_map(fn($k,$v)=>"$k: $v", array_keys($finding->evidence_json), $finding->evidence_json)) : $finding->evidence_json }}</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-slate-600 text-sm italic py-6 text-center bg-slate-950 rounded-lg border border-slate-800/50">No AI findings available.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
@if(auth()->user()?->role === 'admin')
<div id="reject-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-red-700/50 rounded-xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-lg font-bold text-white mb-1"><i class="fas fa-times-circle text-red-400 mr-2"></i> Reject Trial</h3>
        <p class="text-slate-400 text-sm mb-4">This will mark the trial as <strong class="text-red-400">Invalid</strong>. The runner will not see this result.</p>
        <form action="{{ route('admin.running-analysis.trials.reject', $trial) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Reason (optional)</label>
                <textarea name="reason" rows="3" placeholder="e.g. Camera angle too far, runner not in frame, obstructed view..."
                    class="w-full bg-slate-800 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm outline-none focus:border-red-500 resize-none"></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')"
                    class="px-4 py-2 rounded text-sm font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 rounded text-sm font-bold bg-red-700 text-white hover:bg-red-600 transition-colors">
                    <i class="fas fa-times mr-2"></i> Confirm Reject
                </button>
            </div>
        </form>
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

            // 4. Show AI Biomechanical feedback tooltip
            if (eventType && typeof window.showGaitMomentTooltip === 'function') {
                window.showGaitMomentTooltip(eventType);
            }
        }; // end seekToGaitEvent

        // ---------------------------------------------------------------
        // AI Biomechanical Annotation Tooltip Overlay Logic
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
                        summary: 'AI Analysis detected biomechanical concerns in this position.',
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
                statusEl.className = 'px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border ';
                if (phaseData.status === 'ok') {
                    statusEl.classList.add('bg-[#ccff00]/10', 'border-[#ccff00]/30', 'text-[#ccff00]');
                } else if (phaseData.status === 'warn') {
                    statusEl.classList.add('bg-yellow-900/30', 'border-yellow-700', 'text-yellow-400');
                } else if (phaseData.status === 'issue') {
                    statusEl.classList.add('bg-red-900/30', 'border-red-700', 'text-red-400');
                } else {
                    statusEl.classList.add('bg-blue-900/30', 'border-blue-700', 'text-blue-400');
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
                        li.innerHTML = `<span class="text-cyan-400 mt-0.5 shrink-0">•</span> <span class="text-slate-300">${f}</span>`;
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
                        li.innerHTML = `<span class="text-[#ccff00] mt-0.5 shrink-0">•</span> <span class="text-slate-200">${a}</span>`;
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
            <h3 class="text-lg font-bold text-white uppercase italic tracking-wider">Exporting Video</h3>
            <p class="text-xs text-slate-400 mt-1">Merging video with skeleton overlay...</p>
        </div>
        <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
            <div id="export-progress" class="bg-[#ccff00] h-full w-0 transition-all duration-200"></div>
        </div>
        <div id="export-percent" class="text-xs font-black text-[#ccff00] tracking-wider">0%</div>
    </div>
</div>

@endsection
