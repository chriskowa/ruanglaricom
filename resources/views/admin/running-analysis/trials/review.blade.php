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

    $report = $trial->latestReport;
    $narrative = $report ? $report->runner_narrative_json : null;
    $coachMessage = $narrative['coach_message'] ?? null;
    $positives = $narrative['positives'] ?? [];
    $score = $trial->quality_score ? round((float) $trial->quality_score * 100) : null;
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
                        @php
                            $videoMime = $videoArtifact->mime_type ?: match(strtolower(pathinfo($videoArtifact->path ?? '', PATHINFO_EXTENSION))) {
                                'mp4'  => 'video/mp4',
                                'mov'  => 'video/mp4',
                                'webm' => 'video/webm',
                                'avi'  => 'video/x-msvideo',
                                default => 'video/mp4'
                            };
                        @endphp
                        <video id="video-player" preload="auto"
                            class="absolute inset-0 w-full h-full object-contain">
                            <source src="{{ route('admin.running-analysis.trials.artifact', [$trial, $videoArtifact]) }}"
                                type="{{ $videoMime }}">
                        </video>
                        @endif

                        {{-- Skeleton canvas layer (on top of video, transparent bg) --}}
                        <canvas id="playback-canvas"
                            class="absolute inset-0 w-full h-full object-contain pointer-events-none"
                            style="{{ !$poseData ? 'display:none' : ($videoArtifact ? 'display:none' : '') }}">
                        </canvas>

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
                    
                    <!-- Key Gait Moments (Click to seek) -->
                    @if($trial->gaitEvents->count() > 0)
                    <div class="p-4 bg-slate-900/60 border-t border-slate-800/80">
                        <div class="text-[10px] font-bold text-slate-550 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i class="fas fa-camera text-[#ccff00]"></i> Key Gait Moments (Click to seek video)
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($trial->gaitEvents->sortBy('timestamp_ms') as $event)
                                @php
                                    $label = match($event->event_type) {
                                        'initial_contact' => 'Landing (Land)',
                                        'midstance'       => 'Midstance',
                                        'toe_off'         => 'Push Off (Push)',
                                        'max_swing_flexion' => 'Knee Pull (Pull)',
                                        default           => ucfirst(str_replace('_', ' ', $event->event_type)),
                                    };
                                    $sideBadgeColor = $event->side === 'left' ? 'text-lime-450 bg-lime-950/40 border-lime-800' : 'text-blue-450 bg-blue-950/40 border-blue-800';
                                    $icon = match($event->event_type) {
                                        'initial_contact' => 'fa-shoe-prints',
                                        'toe_off'         => 'fa-running',
                                        'max_swing_flexion' => 'fa-arrow-up',
                                        default           => 'fa-stopwatch',
                                    };
                                @endphp
                                <button type="button" 
                                    onclick="seekToGaitEvent({{ $event->timestamp_ms }})"
                                    class="flex flex-col items-start text-left p-3 rounded-lg bg-slate-800/60 border border-slate-700/50 hover:border-[#ccff00] hover:bg-slate-800 transition-all duration-300 group">
                                    <div class="flex items-center justify-between w-full mb-1.5">
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border {{ $sideBadgeColor }} uppercase">{{ $event->side }}</span>
                                        <i class="fas {{ $icon }} text-slate-500 group-hover:text-[#ccff00] transition-colors text-xs"></i>
                                    </div>
                                    <div class="text-xs font-bold text-white mb-0.5 truncate w-full">{{ $label }}</div>
                                    <div class="text-[10px] font-mono text-slate-400 mt-2 flex items-center justify-between w-full">
                                        <span>{{ number_format(($event->timestamp_ms - ($trial->gaitEvents->min('timestamp_ms') ?? 0)) / 1000, 2) }}s</span>
                                        <span class="text-[8px] uppercase tracking-wider text-slate-500 group-hover:text-[#ccff00] font-bold">Seek <i class="fas fa-chevron-right ml-0.5"></i></span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>


                <!-- Action Bar -->
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
        window.seekToGaitEvent = function(timestampMs) {
            const eventBaseMs = {{ $trial->gaitEvents->min('timestamp_ms') ?? 0 }};
            const targetRelativeMs = timestampMs - eventBaseMs;
            const targetSec = targetRelativeMs / 1000;

            // 1. Find closest skeleton frame
            let closestIdx = 0;
            let minDiff = Infinity;
            for (let i = 0; i < frames.length; i++) {
                const fTs = Number(frames[i].timestamp_ms ?? frames[i].ts ?? 0);
                const relativeFrameMs = fTs - frameBaseMs;
                const diff = Math.abs(relativeFrameMs - targetRelativeMs);
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
        }; // end seekToGaitEvent

        // Time synchronization with Video Player
        window.syncSkeletonFrame = function() {
            if (window.currentViewMode !== 'video' || !videoPlayer || frames.length === 0) return;
            const elapsedMs = videoPlayer.currentTime * 1000;
            let closestFrameIdx = 0;
            let minDiff = Infinity;
            for (let i = 0; i < frames.length; i++) {
                const frameTs = Number(frames[i].timestamp_ms ?? frames[i].ts ?? 0);
                const relativeFrameMs = frameTs - frameBaseMs;
                const diff = Math.abs(relativeFrameMs - elapsedMs);
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
            
            let startSec = 0;
            let endSec = (endTs - startTs) / 1000;

            function applyRange() {
                const duration = videoPlayer.duration || 0;
                if (duration > 0) {
                    if (startSec < 0 || endSec > duration || startSec >= endSec) {
                        startSec = 0;
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
        
    } catch (e) {
        console.error("Error parsing pose data", e);
    }
});
</script>
@endif

@endsection
