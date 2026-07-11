@extends('layouts.pacerhub')
@php
    $withSidebar = true;

    $statusColors = [
        'queued'          => 'bg-blue-900 text-blue-300 border-blue-700',
        'analyzing'       => 'bg-yellow-900 text-yellow-300 border-yellow-700',
        'review_required' => 'bg-orange-900 text-orange-300 border-orange-700',
        'approved'        => 'bg-green-900 text-green-300 border-green-700',
        'published'       => 'bg-[#ccff00] text-black border-[#ccff00]',
        'invalid'         => 'bg-red-900 text-red-300 border-red-700',
        'failed'          => 'bg-red-900 text-red-300 border-red-700',
    ];
    $statusColor = $statusColors[$trial->status] ?? 'bg-slate-800 text-slate-300 border-slate-700';
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
            <div>
                <span class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusColor }}">
                    {{ str_replace('_', ' ', strtoupper($trial->status)) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Panel: Visualizer -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-[0_0_20px_rgba(204,255,0,0.05)]">

                    @php
                        $videoArtifact = $trial->artifacts->where('type', 'video_clip')->first();
                    @endphp

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
                        <video id="video-player" controls
                            class="absolute inset-0 w-full h-full object-contain"
                            style="{{ $poseData ? '' : '' }}">
                            <source src="{{ route('admin.running-analysis.trials.artifact', [$trial, $videoArtifact]) }}" type="video/webm">
                        </video>
                        @endif

                        {{-- Skeleton canvas layer (on top of video, transparent bg) --}}
                        <canvas id="playback-canvas"
                            class="absolute inset-0 w-full h-full object-contain pointer-events-none"
                            style="{{ !$poseData ? 'display:none' : ($videoArtifact ? 'display:none' : '') }}">
                        </canvas>

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

                    <!-- Skeleton Playback Controls (only if pose data and in skeleton mode) -->
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
                </div>


                <!-- Action Bar -->
                @if(!in_array($trial->status, ['invalid', 'published']))
                <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl flex gap-3 justify-end items-center">
                    <span class="text-xs text-slate-500 mr-auto italic">Actions update trial status permanently.</span>
                    <button onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                        class="px-6 py-2 rounded text-sm font-bold bg-red-900/50 text-red-400 border border-red-700 hover:bg-red-900 transition-colors">
                        <i class="fas fa-times mr-2"></i> Reject Trial
                    </button>
                    <form action="{{ route('admin.running-analysis.trials.approve', $trial) }}" method="POST" id="approve-form">
                        @csrf
                        <button type="submit" onclick="return confirm('Approve and publish this trial to the runner?')"
                            class="px-6 py-2 rounded text-sm font-bold bg-[#ccff00] text-black border border-[#ccff00] hover:bg-[#b3e600] transition-colors shadow-[0_0_10px_rgba(204,255,0,0.2)]">
                            <i class="fas fa-check mr-2"></i> Approve & Publish
                        </button>
                    </form>
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
            </div>

            <!-- Right Panel: Data -->
            <div class="space-y-6">
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

@if($poseData || isset($videoArtifact))
<script>
// View mode switcher (global helper)
window.currentViewMode = '{{ $videoArtifact ? "video" : "skeleton" }}';

window.switchView = function(mode) {
    window.currentViewMode = mode;
    const videoPlayer = document.getElementById('video-player');
    const skeletonCanvas = document.getElementById('playback-canvas');
    const skeletonControls = document.getElementById('skeleton-controls');
    const tabVideo = document.getElementById('tab-video');
    const tabSkeleton = document.getElementById('tab-skeleton');

    if (mode === 'video') {
        if (videoPlayer) videoPlayer.style.display = '';
        if (skeletonCanvas) skeletonCanvas.style.display = ''; // Overlay on top of video
        if (skeletonControls) skeletonControls.style.display = 'none';
        if (tabVideo) { tabVideo.classList.add('border-[#ccff00]', 'text-[#ccff00]'); tabVideo.classList.remove('border-transparent', 'text-slate-500'); }
        if (tabSkeleton) { tabSkeleton.classList.remove('border-[#ccff00]', 'text-[#ccff00]'); tabSkeleton.classList.add('border-transparent', 'text-slate-500'); }
    } else {
        if (videoPlayer) {
            videoPlayer.style.display = 'none';
            videoPlayer.pause();
        }
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
        const calculateAngle = (a, b, c) => {
            if (!a || !b || !c || a.visibility < 0.5 || b.visibility < 0.5 || c.visibility < 0.5) return null;
            const abx = a.x - b.x, aby = a.y - b.y;
            const cbx = c.x - b.x, cby = c.y - b.y;
            const dot = abx * cbx + aby * cby;
            const mag1 = Math.hypot(abx, aby);
            const mag2 = Math.hypot(cbx, cby);
            if (!mag1 || !mag2) return null;
            return toDeg(Math.acos(clamp(dot / (mag1 * mag2), -1, 1)));
        };

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
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (!landmarks || landmarks.length === 0) return;
            
            ctx.fillStyle = "#ccff00";
            ctx.strokeStyle = "#ccff00";
            ctx.lineWidth = 4;
            for (const lm of landmarks) {
                if (lm.visibility > 0.5) {
                    ctx.beginPath();
                    ctx.arc(lm.x * canvas.width, lm.y * canvas.height, 5, 0, 2 * Math.PI);
                    ctx.fill();
                }
            }
            
            const connections = [
                [0,1],[1,2],[2,3],[3,7],[0,4],[4,5],[5,6],[6,8],[9,10],
                [11,12],[23,24],[11,23],[12,24],
                [11,13],[13,15],[12,14],[14,16],
                [23,25],[25,27],[24,26],[26,28],
                [27,29],[29,31],[31,27],[28,30],[30,32],[32,28]
            ];
            ctx.beginPath();
            for (const [s, e] of connections) {
                const lmS = landmarks[s], lmE = landmarks[e];
                if (lmS && lmE && lmS.visibility > 0.5 && lmE.visibility > 0.5) {
                    ctx.moveTo(lmS.x * canvas.width, lmS.y * canvas.height);
                    ctx.lineTo(lmE.x * canvas.width, lmE.y * canvas.height);
                }
            }
            ctx.stroke();

            const angles = [
                [23,25,27],[24,26,28],[11,23,25],[12,24,26],[11,13,15],[12,14,16]
            ];
            ctx.fillStyle = "white";
            ctx.font = "bold 14px Inter";
            ctx.textAlign = "center";
            for (const [a, b, c] of angles) {
                const ang = calculateAngle(landmarks[a], landmarks[b], landmarks[c]);
                if (ang !== null) {
                    const mid = landmarks[b];
                    ctx.fillText(Math.round(ang) + '°', mid.x * canvas.width + 18, mid.y * canvas.height);
                }
            }

            if (timeline) timeline.value = idx;
            if (currentFrameSpan) currentFrameSpan.innerText = idx + 1;
        }

        // Time synchronization with Video Player
        window.syncSkeletonFrame = function() {
            if (window.currentViewMode !== 'video' || !videoPlayer || frames.length === 0) return;
            const elapsedMs = videoPlayer.currentTime * 1000;
            const startTs = frames[0].timestamp_ms ?? frames[0].ts ?? 0;
            let closestFrameIdx = 0;
            let minDiff = Infinity;
            for (let i = 0; i < frames.length; i++) {
                const frameElapsed = (frames[i].timestamp_ms ?? frames[i].ts ?? 0) - startTs;
                const diff = Math.abs(frameElapsed - elapsedMs);
                if (diff < minDiff) {
                    minDiff = diff;
                    closestFrameIdx = i;
                }
            }
            drawFrame(closestFrameIdx);
        };

        if (videoPlayer) {
            videoPlayer.addEventListener('timeupdate', window.syncSkeletonFrame);
            videoPlayer.addEventListener('seeked', window.syncSkeletonFrame);
            
            let isPlayingVideo = false;
            const videoPlayLoop = (ts) => {
                if (!isPlayingVideo || window.currentViewMode !== 'video') return;
                window.syncSkeletonFrame();
                requestAnimationFrame(videoPlayLoop);
            };

            videoPlayer.addEventListener('play', () => {
                isPlayingVideo = true;
                requestAnimationFrame(videoPlayLoop);
            });
            videoPlayer.addEventListener('pause', () => { isPlayingVideo = false; });
            videoPlayer.addEventListener('ended', () => { isPlayingVideo = false; });
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
        
    } catch (e) {
        console.error("Error parsing pose data", e);
    }
});
</script>
@endif

@endsection
