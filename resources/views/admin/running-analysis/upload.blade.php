@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Manual Video Analysis - ' . $session->name)

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans bg-[#060a17]">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6 flex items-center text-slate-400 text-sm font-medium">
            <a href="{{ route('admin.running-analysis.sessions.show', $session) }}" class="hover:text-[#ccff00]">
                <i class="fas fa-arrow-left mr-2"></i> Back to Session Details
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Controls -->
            <div class="space-y-6">
                <div class="bg-[#0f172a] rounded-xl border border-slate-800 p-6">
                    <h3 class="text-xl font-bold text-white uppercase italic tracking-wider mb-4 border-b border-slate-800 pb-3">Manual Video Analysis</h3>
                    
                    <div class="space-y-4">
                        <!-- Select Runner -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Select Runner</label>
                            <select id="runner-selector" class="w-full bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-[#ccff00] p-3 cursor-pointer">
                                <option value="">-- Choose Runner --</option>
                                @foreach($session->runners as $r)
                                    <option value="{{ $r->id }}" {{ request('runner_id') == $r->id ? 'selected' : '' }}>
                                        {{ $r->name }} ({{ strtoupper($r->pivot->status) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Choose Video File -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Upload Video File</label>
                            <div class="relative border-2 border-dashed border-slate-800 hover:border-slate-700 bg-slate-950/40 rounded-xl p-4 transition-all flex flex-col items-center justify-center text-center cursor-pointer group">
                                <input type="file" id="video-file-input" accept="video/*" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                                <div class="p-3 bg-slate-900 rounded-full text-slate-500 mb-2 group-hover:text-white transition-colors">
                                    <i class="fas fa-video text-xl"></i>
                                </div>
                                <span id="file-label" class="text-xs text-slate-400 font-semibold truncate max-w-xs">Click or drag video here</span>
                                <span class="text-[9px] text-slate-500 mt-1">MP4, WebM, MOV, AVI</span>
                            </div>
                        </div>

                        <!-- Size warning container -->
                        <div id="size-warning-container" class="hidden p-3 bg-red-950/40 border border-red-500/30 rounded-xl text-[11px] text-red-400 leading-relaxed">
                            <i class="fas fa-exclamation-triangle mr-1"></i> 
                            <span>File size (<span id="warn-file-size">0</span>MB) exceeds ideal limit (15MB). Please compress the video or select a smaller/shorter clip to prevent upload failures (HTTP 413).</span>
                        </div>

                        <!-- Ideal Video Guide -->
                        <div class="p-3 bg-slate-900/40 border border-slate-800 rounded-xl space-y-1.5 text-[11px] text-slate-400">
                            <div class="font-bold text-white uppercase tracking-wider mb-1">Ideal Video Guide</div>
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-[#ccff00]"></i> Side-view lateral shot</div>
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-[#ccff00]"></i> 2 - 5 seconds duration</div>
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-[#ccff00]"></i> Max size: 15MB</div>
                        </div>

                        <!-- Trim Video Range -->
                        <div id="trim-controls" class="hidden p-3 bg-slate-900/60 border border-slate-850 rounded-xl space-y-3">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Trim Video (Optional)</div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <label class="block text-slate-500 mb-1">Start Time (sec)</label>
                                    <input type="number" id="trim-start" min="0" step="0.1" value="0" class="w-full bg-slate-950 border border-slate-850 text-white rounded p-1.5 outline-none focus:border-[#ccff00]">
                                </div>
                                <div>
                                    <label class="block text-slate-500 mb-1">End Time (sec)</label>
                                    <input type="number" id="trim-end" min="0" step="0.1" value="0" class="w-full bg-slate-950 border border-slate-850 text-white rounded p-1.5 outline-none focus:border-[#ccff00]">
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-[10px] text-slate-500">
                                <span>Total Duration: <span id="video-total-duration">0.0</span>s</span>
                                <span>Selected: <span id="video-selected-duration">0.0</span>s</span>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="p-3 bg-slate-900/60 border border-slate-850 rounded-xl space-y-3">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-semibold uppercase">Process FPS</span>
                                <select id="fps-selector" class="bg-slate-950 border border-slate-850 text-white rounded px-2 py-1 outline-none">
                                    <option value="30" selected>30 FPS</option>
                                    <option value="24">24 FPS</option>
                                    <option value="15">15 FPS (Fast)</option>
                                </select>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-semibold uppercase">Execution Mode</span>
                                <select id="exec-mode" class="bg-slate-950 border border-slate-850 text-white rounded px-2 py-1 outline-none">
                                    <option value="sync" selected>Direct / Sync</option>
                                    <option value="queue">Queue (Background)</option>
                                </select>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-semibold uppercase">Pose Model</span>
                                <span class="text-slate-300 font-mono">pose_landmarker_full</span>
                            </div>
                        </div>

                        <!-- Start Button -->
                        <button type="button" id="start-analysis-btn" disabled class="w-full py-3 bg-[#ccff00] text-black font-black italic tracking-wider uppercase rounded hover:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-play mr-2"></i> Start Analysis
                        </button>
                    </div>
                </div>

                <!-- Model loading status -->
                <div class="bg-[#0f172a] rounded-xl border border-slate-800 p-4 text-xs font-mono text-slate-400 space-y-1">
                    <div class="flex justify-between"><span>MediaPipe Engine:</span> <span id="model-status" class="text-yellow-400">Loading...</span></div>
                    <div class="flex justify-between"><span>Processed Frames:</span> <span id="processed-frames-count">0</span></div>
                    <div class="flex justify-between"><span>Outbox Pending:</span> <span id="outbox-pending-count">0</span></div>
                </div>
            </div>

            <!-- Right Side: Rendering Canvas Overlay -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-[#0f172a] rounded-xl border border-slate-800 p-6 flex flex-col h-full min-h-[480px]">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">Analysis Workspace</h3>
                    
                    <!-- Canvas & Video player container -->
                    <div class="flex-1 min-h-[320px] bg-black rounded-lg border border-slate-850 relative flex items-center justify-center overflow-hidden">
                        <video id="processing-video" class="hidden" muted playsinline></video>
                        <canvas id="processing-canvas" class="w-full h-full object-contain max-h-[420px]"></canvas>
                        
                        <!-- Progress overlay -->
                        <div id="processing-overlay" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center z-20 hidden">
                            <div class="w-16 h-16 rounded-full border-4 border-slate-800 border-t-[#ccff00] animate-spin mb-4"></div>
                            <h4 id="progress-title" class="text-lg font-bold text-white mb-2">Analyzing Video</h4>
                            <p id="progress-desc" class="text-xs text-slate-400 max-w-sm mb-4">Initializing MediaPipe Pose tracker...</p>
                            
                            <!-- Custom Progress Bar -->
                            <div class="w-full max-w-xs bg-slate-900 border border-slate-800 rounded-full h-2.5 overflow-hidden">
                                <div id="progress-bar" class="bg-[#ccff00] h-full transition-all duration-150" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <!-- Empty State -->
                        <div id="workspace-empty" class="text-center p-6 text-slate-500">
                            <i class="fas fa-file-video text-5xl mb-3 text-slate-700"></i>
                            <p class="text-sm font-semibold">No video loaded yet.</p>
                            <p class="text-xs max-w-xs mx-auto mt-1 leading-relaxed">Choose a runner and upload a video file on the left to begin.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dexie (IndexedDB) -->
<script src="https://unpkg.com/dexie@4.0.7/dist/dexie.min.js"></script>

<div id="toast-container" class="fixed top-20 right-4 flex flex-col gap-2 z-50"></div>
@endsection

@push('scripts')
<script type="module">
    import { FilesetResolver, PoseLandmarker } from "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14";

    // Setup Local Database for Outbox Buffering
    const db = new Dexie("RuangLariCaptureDB");
    db.version(1).stores({
        outbox: '++id, trialId, runnerId, status, timestamp'
    });

    const state = {
        runners: {!! json_encode($session->runners->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->name
        ])) !!},
        poseLandmarker: null,
        videoFile: null,
        processing: false
    };

    const ANALYSIS_CONFIG = {
        minVisibility: 0.55,
        smoothingAlpha: 0.35,
        minimumDeltaMs: 5,
        maximumDeltaMs: 100,
        contactGroundToleranceLegRatio: 0.04,
        minimumEventConfidence: 0.6,
        minimumStepDurationMs: 180,
        maximumStepDurationMs: 1200,
        phaseDebounceFrames: 2,
        overstrideThreshold: 0.12
    };

    // Math Utilities
    const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
    const toDeg = (rad) => rad * 180 / Math.PI;
    const safeNumber = (val, fallback = null) => (val === undefined || val === null || isNaN(val) || !isFinite(val)) ? fallback : val;
    const distance2D = (a, b) => (!a || !b) ? null : Math.hypot(a.x - b.x, a.y - b.y);
    const midpoint = (a, b) => (!a || !b) ? null : { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2, z: (a.z + b.z) / 2 };

    const calculateAngle2D = (a, b, c) => {
        if (!a || !b || !c) return null;
        const abx = a.x - b.x, aby = a.y - b.y;
        const cbx = c.x - b.x, cby = c.y - b.y;
        const dot = abx * cbx + aby * cby;
        const mag1 = Math.hypot(abx, aby), mag2 = Math.hypot(cbx, cby);
        if (!mag1 || !mag2) return null;
        return toDeg(Math.acos(clamp(dot / (mag1 * mag2), -1, 1)));
    };

    const calculateAngle3D = (a, b, c) => {
        if (!a || !b || !c) return null;
        const abx = a.x - b.x, aby = a.y - b.y, abz = a.z - b.z;
        const cbx = c.x - b.x, cby = c.y - b.y, cbz = c.z - b.z;
        const dot = abx * cbx + aby * cby + abz * cbz;
        const mag1 = Math.hypot(abx, aby, abz), mag2 = Math.hypot(cbx, cby, cbz);
        if (!mag1 || !mag2) return null;
        return toDeg(Math.acos(clamp(dot / (mag1 * mag2), -1, 1)));
    };

    const calculateSegmentAngle = (a, b, relativeTo = "vertical") => {
        if (!a || !b) return null;
        const dx = b.x - a.x, dy = b.y - a.y;
        return relativeTo === "horizontal" ? toDeg(Math.atan2(dy, dx)) : toDeg(Math.atan2(dx, dy));
    };

    const calculateVisibilityScore = (landmarks, indexes) => {
        if (!landmarks || !indexes || indexes.length === 0) return 0;
        let sum = 0, count = 0;
        indexes.forEach(idx => {
            if (landmarks[idx]) {
                sum += landmarks[idx].visibility ?? 0;
                count++;
            }
        });
        return count > 0 ? sum / count : 0;
    };

    const median = (values) => {
        if (!values || values.length === 0) return null;
        const sorted = [...values].sort((a, b) => a - b);
        const mid = Math.floor(sorted.length / 2);
        return sorted.length % 2 !== 0 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2;
    };

    const mean = (values) => (!values || values.length === 0) ? null : values.reduce((sum, v) => sum + v, 0) / values.length;

    const standardDeviation = (values) => {
        if (!values || values.length === 0) return null;
        const avg = mean(values);
        const squareDiffs = values.map(v => Math.pow(v - avg, 2));
        return Math.sqrt(mean(squareDiffs));
    };

    const percentile = (values, p) => {
        if (!values || values.length === 0) return null;
        const sorted = [...values].sort((a, b) => a - b);
        const idx = Math.ceil((p / 100) * sorted.length) - 1;
        return sorted[clamp(idx, 0, sorted.length - 1)];
    };

    // Landmark Exponential Moving Average Filter
    class LandmarkFilter {
        constructor(alpha = 0.35) {
            this.alpha = alpha;
            this.previousSmooth = null;
        }
        
        filter(current, visibility, deltaMs) {
            if (!current) {
                this.previousSmooth = null;
                return null;
            }
            if (visibility < ANALYSIS_CONFIG.minVisibility || deltaMs > ANALYSIS_CONFIG.maximumDeltaMs) {
                this.previousSmooth = null;
                return current;
            }
            if (!this.previousSmooth) {
                this.previousSmooth = { ...current };
                return current;
            }
            const smoothed = {};
            for (const key of ['x', 'y', 'z']) {
                smoothed[key] = this.alpha * current[key] + (1 - this.alpha) * this.previousSmooth[key];
            }
            smoothed.visibility = current.visibility;
            smoothed.presence = current.presence;
            this.previousSmooth = smoothed;
            return smoothed;
        }
    }

    function smoothFrameSequence(frames) {
        const filters = Array.from({ length: 33 }, () => new LandmarkFilter(ANALYSIS_CONFIG.smoothingAlpha));
        const worldFilters = Array.from({ length: 33 }, () => new LandmarkFilter(ANALYSIS_CONFIG.smoothingAlpha));
        
        return frames.map((frame, frameIdx) => {
            const deltaMs = frameIdx > 0 ? frame.timestamp_ms - frames[frameIdx - 1].timestamp_ms : 33.3;
            
            const smoothedLandmarks = frame.landmarks.map((lm, idx) => {
                return filters[idx].filter(lm, lm.visibility ?? 1.0, deltaMs);
            });
            
            const smoothedWorldLandmarks = frame.world_landmarks 
                ? frame.world_landmarks.map((lm, idx) => {
                    return worldFilters[idx].filter(lm, lm.visibility ?? 1.0, deltaMs);
                  })
                : null;
                
            return {
                ...frame,
                smoothed_landmarks: smoothedLandmarks,
                smoothed_world_landmarks: smoothedWorldLandmarks
            };
        });
    }

    // Biomechanical Helpers
    function getAngleWithFallback(landmarks, worldLandmarks, p, is3D = true) {
        const lms = is3D && worldLandmarks ? worldLandmarks : landmarks;
        const a = lms[p[0]], b = lms[p[1]], c = lms[p[2]];
        if (!a || !b || !c) return { value: null, source: "failed", confidence: 0 };
        
        const val = is3D && worldLandmarks ? calculateAngle3D(a, b, c) : calculateAngle2D(a, b, c);
        const confidence = calculateVisibilityScore(landmarks, p);
        return {
            value: safeNumber(val),
            source: is3D && worldLandmarks ? "world_3d" : "normalized_2d",
            confidence: safeNumber(confidence, 0)
        };
    }

    function getSegmentAngleWithFallback(landmarks, worldLandmarks, p, relativeTo = "vertical") {
        const a = landmarks[p[0]], b = landmarks[p[1]];
        if (!a || !b || a.visibility < ANALYSIS_CONFIG.minVisibility || b.visibility < ANALYSIS_CONFIG.minVisibility) {
            return { value: null, source: "failed", confidence: 0 };
        }
        const angle = calculateSegmentAngle(a, b, relativeTo);
        const confidence = (a.visibility + b.visibility) / 2;
        return {
            value: safeNumber(angle),
            source: "normalized_2d",
            confidence: safeNumber(confidence, 0)
        };
    }

    function calculateFrameAngles(frames) {
        return frames.map(frame => {
            const lms = frame.smoothed_landmarks;
            const wlms = frame.smoothed_world_landmarks;
            if (!lms) return { ...frame, angles: {} };
            
            const lShoulder = lms[11], rShoulder = lms[12];
            const lHip = lms[23], rHip = lms[24];
            
            let trunkLean = { value: null, source: "failed", confidence: 0 };
            if (lShoulder && rShoulder && lHip && rHip) {
                const shMid = midpoint(lShoulder, rShoulder);
                const hipMid = midpoint(lHip, rHip);
                trunkLean = getSegmentAngleWithFallback([shMid, hipMid], null, [0, 1], "vertical");
            }
            
            const pelvisTilt = getSegmentAngleWithFallback(lms, wlms, [23, 24], "horizontal");
            const shoulderTilt = getSegmentAngleWithFallback(lms, wlms, [11, 12], "horizontal");
            
            const angles = {
                left_knee_flexion: getAngleWithFallback(lms, wlms, [23, 25, 27]),
                right_knee_flexion: getAngleWithFallback(lms, wlms, [24, 26, 28]),
                left_hip: getAngleWithFallback(lms, wlms, [11, 23, 25]),
                right_hip: getAngleWithFallback(lms, wlms, [12, 24, 26]),
                left_elbow: getAngleWithFallback(lms, wlms, [11, 13, 15]),
                right_elbow: getAngleWithFallback(lms, wlms, [12, 14, 16]),
                left_ankle: getAngleWithFallback(lms, wlms, [25, 27, 31]),
                right_ankle: getAngleWithFallback(lms, wlms, [26, 28, 32]),
                left_thigh_to_vertical: getSegmentAngleWithFallback(lms, wlms, [23, 25], "vertical"),
                right_thigh_to_vertical: getSegmentAngleWithFallback(lms, wlms, [24, 26], "vertical"),
                left_tibia_to_vertical: getSegmentAngleWithFallback(lms, wlms, [25, 27], "vertical"),
                right_tibia_to_vertical: getSegmentAngleWithFallback(lms, wlms, [26, 28], "vertical"),
                left_foot_to_horizontal: getSegmentAngleWithFallback(lms, wlms, [29, 31], "horizontal"),
                right_foot_to_horizontal: getSegmentAngleWithFallback(lms, wlms, [30, 32], "horizontal"),
                trunk_lean: trunkLean,
                pelvis_tilt: pelvisTilt,
                shoulder_tilt: shoulderTilt
            };
            
            return { ...frame, angles };
        });
    }

    function calculateBodyProportions(frames) {
        const hipKneeDists = [];
        const kneeAnkleDists = [];
        const shHipDists = [];
        const pelvisXCoords = [];

        frames.forEach(frame => {
            const lms = frame.smoothed_landmarks;
            if (!lms) return;
            const lShoulder = lms[11], rShoulder = lms[12];
            const lHip = lms[23], rHip = lms[24];
            const lKnee = lms[25], rKnee = lms[26];
            const lAnkle = lms[27], rAnkle = lms[28];

            const lHK = distance2D(lHip, lKnee);
            const rHK = distance2D(rHip, rKnee);
            if (lHK) hipKneeDists.push(lHK);
            if (rHK) hipKneeDists.push(rHK);

            const lKA = distance2D(lKnee, lAnkle);
            const rKA = distance2D(rKnee, rAnkle);
            if (lKA) kneeAnkleDists.push(lKA);
            if (rKA) kneeAnkleDists.push(rKA);

            const lSH = distance2D(lShoulder, lHip);
            const rSH = distance2D(rShoulder, rHip);
            if (lSH) shHipDists.push(lSH);
            if (rSH) shHipDists.push(rSH);

            if (lHip && rHip && lHip.visibility > ANALYSIS_CONFIG.minVisibility && rHip.visibility > ANALYSIS_CONFIG.minVisibility) {
                pelvisXCoords.push((lHip.x + rHip.x) / 2);
            }
        });

        const medianHK = median(hipKneeDists) || 0.15;
        const medianKA = median(kneeAnkleDists) || 0.15;
        const medianSH = median(shHipDists) || 0.25;
        const refLegLength = medianHK + medianKA;
        const bodyScale = (medianHK + medianKA + medianSH) / 3;

        let travelDirection = 'unknown';
        if (pelvisXCoords.length > 5) {
            const diffX = pelvisXCoords[pelvisXCoords.length - 1] - pelvisXCoords[0];
            if (Math.abs(diffX) > 0.05) {
                travelDirection = diffX > 0 ? 'left_to_right' : 'right_to_left';
            }
        }

        return { refLegLength, bodyScale, travelDirection };
    }

    function estimateGroundPlane(frames) {
        const footYCandidates = [];
        frames.forEach(frame => {
            const lms = frame.smoothed_landmarks;
            if (!lms) return;
            [27, 28, 29, 30, 31, 32].forEach(idx => {
                const lm = lms[idx];
                if (lm && lm.visibility > ANALYSIS_CONFIG.minVisibility) footYCandidates.push(lm.y);
            });
        });
        if (footYCandidates.length === 0) return { ground_y_normalized: 0.85 };
        return { ground_y_normalized: percentile(footYCandidates, 90) };
    }

    function estimateFrameContacts(frames, groundY, refLegLength) {
        const tolerance = ANALYSIS_CONFIG.contactGroundToleranceLegRatio * refLegLength;
        
        const framesWithVelocities = frames.map((frame, i) => {
            const prevFrame = i > 0 ? frames[i - 1] : null;
            const dT = prevFrame ? (frame.timestamp_ms - prevFrame.timestamp_ms) / 1000 : 0.033;
            const lms = frame.smoothed_landmarks;
            const prevLms = prevFrame ? prevFrame.smoothed_landmarks : null;
            
            let leftFootSpeedY = 0;
            let rightFootSpeedY = 0;
            
            if (lms && prevLms) {
                const lAnkle = lms[27], prevLAnkle = prevLms[27];
                const rAnkle = lms[28], prevRAnkle = prevLms[28];
                if (lAnkle && prevLAnkle) leftFootSpeedY = (lAnkle.y - prevLAnkle.y) / dT;
                if (rAnkle && prevRAnkle) rightFootSpeedY = (rAnkle.y - prevRAnkle.y) / dT;
            }
            
            return { ...frame, leftFootSpeedY, rightFootSpeedY };
        });
        
        let leftContactState = false, rightContactState = false;
        let leftDebounce = 0, rightDebounce = 0;
        
        return framesWithVelocities.map(frame => {
            const lms = frame.smoothed_landmarks;
            if (!lms) {
                return {
                    ...frame,
                    contact: {
                        left: { is_contact: false, confidence: 0 },
                        right: { is_contact: false, confidence: 0 }
                    }
                };
            }
            
            const lAnkle = lms[27], rAnkle = lms[28];
            const leftLowestY = Math.max(lms[29]?.y || 0, lAnkle?.y || 0, lms[31]?.y || 0);
            const leftNearGround = (groundY - leftLowestY) <= tolerance;
            const leftSpeedLow = Math.abs(frame.leftFootSpeedY) < 0.5;
            const leftVisibilityOk = (lAnkle?.visibility || 0) > ANALYSIS_CONFIG.minVisibility;
            const leftIsContactCandidate = leftNearGround && (leftSpeedLow || leftLowestY > groundY - 0.02) && leftVisibilityOk;
            
            if (leftIsContactCandidate !== leftContactState) {
                leftDebounce++;
                if (leftDebounce >= ANALYSIS_CONFIG.phaseDebounceFrames) { leftContactState = leftIsContactCandidate; leftDebounce = 0; }
            } else { leftDebounce = 0; }
            
            const rightLowestY = Math.max(lms[30]?.y || 0, rAnkle?.y || 0, lms[32]?.y || 0);
            const rightNearGround = (groundY - rightLowestY) <= tolerance;
            const rightSpeedLow = Math.abs(frame.rightFootSpeedY) < 0.5;
            const rightVisibilityOk = (rAnkle?.visibility || 0) > ANALYSIS_CONFIG.minVisibility;
            const rightIsContactCandidate = rightNearGround && (rightSpeedLow || rightLowestY > groundY - 0.02) && rightVisibilityOk;
            
            if (rightIsContactCandidate !== rightContactState) {
                rightDebounce++;
                if (rightDebounce >= ANALYSIS_CONFIG.phaseDebounceFrames) { rightContactState = rightIsContactCandidate; rightDebounce = 0; }
            } else { rightDebounce = 0; }
            
            return {
                ...frame,
                contact: {
                    left: { is_contact: leftContactState, confidence: leftVisibilityOk ? 0.9 : 0 },
                    right: { is_contact: rightContactState, confidence: rightVisibilityOk ? 0.9 : 0 }
                }
            };
        });
    }

    function estimateFramePhases(frames, travelDirection) {
        return frames.map(frame => {
            const lms = frame.smoothed_landmarks;
            if (!lms) return { ...frame, phase: { left: "unknown", right: "unknown" } };
            
            const lHip = lms[23], rHip = lms[24];
            const pelvis = midpoint(lHip, rHip);
            const lAnkle = lms[27], rAnkle = lms[28];
            const dirMult = travelDirection === 'right_to_left' ? -1 : 1;
            
            let leftPhase = "estimated_swing";
            if (frame.contact.left.is_contact) {
                leftPhase = (lAnkle && pelvis && (pelvis.x - lAnkle.x) * dirMult > 0) ? "estimated_loading" : "estimated_push";
            } else {
                leftPhase = (lAnkle && pelvis && (pelvis.x - lAnkle.x) * dirMult < 0) ? "estimated_pull" : "estimated_swing";
            }
            
            let rightPhase = "estimated_swing";
            if (frame.contact.right.is_contact) {
                rightPhase = (rAnkle && pelvis && (pelvis.x - rAnkle.x) * dirMult > 0) ? "estimated_loading" : "estimated_push";
            } else {
                rightPhase = (rAnkle && pelvis && (rAnkle.x - pelvis.x) * dirMult > 0) ? "estimated_swing" : "estimated_pull";
            }
            
            return { ...frame, phase: { left: leftPhase, right: rightPhase } };
        });
    }

    function detectGaitEvents(frames) {
        const events = [];
        let prevLeftContact = false, prevRightContact = false;
        let lastLeftIcTime = 0, lastLeftToTime = 0;
        let lastRightIcTime = 0, lastRightToTime = 0;
        
        frames.forEach((frame, idx) => {
            const leftContact = frame.contact.left.is_contact;
            const rightContact = frame.contact.right.is_contact;
            const timeMs = frame.timestamp_ms;
            
            if (leftContact && !prevLeftContact) {
                if (timeMs - lastLeftIcTime > ANALYSIS_CONFIG.minimumStepDurationMs) {
                    events.push({ type: "initial_contact", side: "left", frame_index: idx, timestamp_ms: timeMs, confidence: frame.contact.left.confidence });
                    lastLeftIcTime = timeMs;
                }
            } else if (!leftContact && prevLeftContact) {
                if (timeMs - lastLeftToTime > ANALYSIS_CONFIG.minimumStepDurationMs) {
                    events.push({ type: "toe_off", side: "left", frame_index: idx, timestamp_ms: timeMs, confidence: frame.contact.left.confidence });
                    lastLeftToTime = timeMs;
                }
            }
            
            if (rightContact && !prevRightContact) {
                if (timeMs - lastRightIcTime > ANALYSIS_CONFIG.minimumStepDurationMs) {
                    events.push({ type: "initial_contact", side: "right", frame_index: idx, timestamp_ms: timeMs, confidence: frame.contact.right.confidence });
                    lastRightIcTime = timeMs;
                }
            } else if (!rightContact && prevRightContact) {
                if (timeMs - lastRightToTime > ANALYSIS_CONFIG.minimumStepDurationMs) {
                    events.push({ type: "toe_off", side: "right", frame_index: idx, timestamp_ms: timeMs, confidence: frame.contact.right.confidence });
                    lastRightToTime = timeMs;
                }
            }
            
            prevLeftContact = leftContact;
            prevRightContact = rightContact;
        });
        
        return events;
    }

    function buildSteps(frames, events, refLegLength, travelDirection) {
        const steps = [];
        let stepCount = 0;
        const dirMult = travelDirection === 'right_to_left' ? -1 : 1;
        
        const leftIcs = events.filter(e => e.type === 'initial_contact' && e.side === 'left');
        const leftTos = events.filter(e => e.type === 'toe_off' && e.side === 'left');
        const rightIcs = events.filter(e => e.type === 'initial_contact' && e.side === 'right');
        const rightTos = events.filter(e => e.type === 'toe_off' && e.side === 'right');
        
        const processSideSteps = (ics, tos, side) => {
            for (let i = 0; i < ics.length - 1; i++) {
                const ic1 = ics[i];
                const ic2 = ics[i + 1];
                const to = tos.find(t => t.timestamp_ms > ic1.timestamp_ms && t.timestamp_ms < ic2.timestamp_ms);
                
                if (to) {
                    const contactTime = to.timestamp_ms - ic1.timestamp_ms;
                    const stepTime = ic2.timestamp_ms - ic1.timestamp_ms;
                    const cadence = 60000 / stepTime;
                    
                    let flightTimeMs = 0;
                    for (let f = to.frame_index; f <= ic2.frame_index; f++) {
                        if (frames[f] && !frames[f].contact.left.is_contact && !frames[f].contact.right.is_contact) {
                            const prevF = f > 0 ? frames[f - 1] : null;
                            flightTimeMs += prevF ? (frames[f].timestamp_ms - prevF.timestamp_ms) : 33.3;
                        }
                    }
                    
                    const icFrame = frames[ic1.frame_index];
                    let landingLever = null;
                    let landingLegExtension = null;
                    let trunkLeanAtContact = null;
                    
                    if (icFrame && icFrame.smoothed_landmarks) {
                        const lms = icFrame.smoothed_landmarks;
                        const ankle = lms[side === 'left' ? 27 : 28];
                        const hip = lms[side === 'left' ? 23 : 24];
                        const pelvis = midpoint(lms[23], lms[24]);
                        
                        if (ankle && pelvis) landingLever = ((ankle.x - pelvis.x) * dirMult) / refLegLength;
                        if (ankle && hip) landingLegExtension = distance2D(hip, ankle) / refLegLength;
                        if (icFrame.angles.trunk_lean?.value !== null) trunkLeanAtContact = icFrame.angles.trunk_lean.value;
                    }
                    
                    let maxKneeFlexion = 0;
                    let recoveryCompactness = 999.0;
                    let maxSwingLever = 0;
                    
                    for (let f = ic1.frame_index; f <= ic2.frame_index; f++) {
                        const frame = frames[f];
                        if (!frame || !frame.smoothed_landmarks) continue;
                        
                        const lms = frame.smoothed_landmarks;
                        const kneeAngle = frame.angles[side + '_knee_flexion']?.value;
                        if (kneeAngle !== null && kneeAngle > maxKneeFlexion) maxKneeFlexion = kneeAngle;
                        
                        const heel = lms[side === 'left' ? 29 : 30];
                        const hip = lms[side === 'left' ? 23 : 24];
                        if (heel && hip) {
                            const dist = distance2D(heel, hip) / refLegLength;
                            if (dist < recoveryCompactness) recoveryCompactness = dist;
                        }
                        
                        const ankle = lms[side === 'left' ? 27 : 28];
                        const pelvis = midpoint(lms[23], lms[24]);
                        if (ankle && pelvis) {
                            const horizontalDist = Math.abs(pelvis.x - ankle.x) / refLegLength;
                            if (horizontalDist > maxSwingLever) maxSwingLever = horizontalDist;
                        }
                    }
                    
                    steps.push({
                        step_index: stepCount++,
                        side: side,
                        initial_contact_ms: ic1.timestamp_ms,
                        toe_off_ms: to.timestamp_ms,
                        next_initial_contact_ms: ic2.timestamp_ms,
                        contact_time_ms: contactTime,
                        flight_time_ms: flightTimeMs || (stepTime - contactTime),
                        step_time_ms: stepTime,
                        cadence_spm: cadence,
                        landing: {
                            trunk_lean_degree: trunkLeanAtContact,
                            leg_extension_ratio: landingLegExtension
                        },
                        push: { label: "estimated_push" },
                        pull: { label: "estimated_pull" },
                        lever: {
                            landing_lever_ratio: landingLever,
                            swing_lever_ratio: maxSwingLever,
                            recovery_compactness_ratio: recoveryCompactness
                        },
                        angle_extrema: {
                            max_knee_flexion_degree: maxKneeFlexion
                        },
                        quality: {
                            confidence: (ic1.confidence + to.confidence + ic2.confidence) / 3
                        }
                    });
                }
            }
        };
        
        processSideSteps(leftIcs, leftTos, 'left');
        processSideSteps(rightIcs, rightTos, 'right');
        
        return steps.sort((a, b) => a.initial_contact_ms - b.initial_contact_ms);
    }

    const calculateSymmetryDiff = (left, right) => {
        if (left === null || right === null || left === 0 || right === 0) return null;
        const denominator = (Math.abs(left) + Math.abs(right)) / 2;
        if (denominator === 0) return null;
        return (Math.abs(left - right) / denominator) * 100;
    };

    function generateTrialSummary(frames, steps, travelDirection, refLegLength) {
        const validSteps = steps.filter(s => s.status !== 'incomplete');
        const leftSteps = validSteps.filter(s => s.side === 'left');
        const rightSteps = validSteps.filter(s => s.side === 'right');
        
        const cadences = validSteps.map(s => s.cadence_spm);
        const leftGcts = leftSteps.map(s => s.contact_time_ms);
        const rightGcts = rightSteps.map(s => s.contact_time_ms);
        const flightTimes = validSteps.map(s => s.flight_time_ms);
        
        const leftKneeAtContact = [];
        const rightKneeAtContact = [];
        const peakKneeFlexionLeft = [];
        const peakKneeFlexionRight = [];
        const trunkLeanAtContact = [];
        const landingLeverLeft = [];
        const landingLeverRight = [];

        validSteps.forEach(s => {
            if (s.side === 'left') {
                leftKneeAtContact.push(s.landing.leg_extension_ratio);
                peakKneeFlexionLeft.push(s.angle_extrema.max_knee_flexion_degree);
                landingLeverLeft.push(s.lever.landing_lever_ratio);
            } else {
                rightKneeAtContact.push(s.landing.leg_extension_ratio);
                peakKneeFlexionRight.push(s.angle_extrema.max_knee_flexion_degree);
                landingLeverRight.push(s.lever.landing_lever_ratio);
            }
            if (s.landing.trunk_lean_degree !== null) trunkLeanAtContact.push(s.landing.trunk_lean_degree);
        });

        const medianCadence = median(cadences);
        const medianLeftGct = median(leftGcts);
        const medianRightGct = median(rightGcts);
        const medianFlight = median(flightTimes);
        const gctSymmetry = calculateSymmetryDiff(medianLeftGct, medianRightGct);
        
        return {
            duration_ms: frames.length > 0 ? (frames[frames.length - 1].timestamp_ms - frames[0].timestamp_ms) : 0,
            valid_frame_count: frames.filter(f => f.smoothed_landmarks).length,
            travel_direction: travelDirection,
            step_count: validSteps.length,
            cadence_spm: { median: safeNumber(medianCadence), confidence: validSteps.length > 2 ? 0.85 : 0.5 },
            contact_time_ms: { left_median: safeNumber(medianLeftGct), right_median: safeNumber(medianRightGct) },
            flight_time_ms: { median: safeNumber(medianFlight) },
            angles: {
                left_knee_at_contact_median: safeNumber(median(leftKneeAtContact)),
                right_knee_at_contact_median: safeNumber(median(rightKneeAtContact)),
                peak_knee_flexion_left_median: safeNumber(median(peakKneeFlexionLeft)),
                peak_knee_flexion_right_median: safeNumber(median(peakKneeFlexionRight)),
                trunk_lean_at_contact_median: safeNumber(median(trunkLeanAtContact))
            },
            lever: { landing_left_median: safeNumber(median(landingLeverLeft)), landing_right_median: safeNumber(median(landingLeverRight)) },
            symmetry: { gct_asymmetry_percent: safeNumber(gctSymmetry) },
            flags: [],
            limitations: []
        };
    }

    function evaluateFormIndicators(summary, overallQuality) {
        const flags = [];
        const leftLandingLever = summary.lever.landing_left_median;
        const rightLandingLever = summary.lever.landing_right_median;
        const maxLever = Math.max(leftLandingLever || 0, rightLandingLever || 0);
        
        if (maxLever > ANALYSIS_CONFIG.overstrideThreshold) {
            flags.push({
                code: "possible_overstride",
                severity: "info",
                side: leftLandingLever > rightLandingLever ? "left" : "right",
                value: maxLever,
                threshold: ANALYSIS_CONFIG.overstrideThreshold,
                confidence: 0.75,
                message: "Terindikasi overstride: titik pendaratan kaki berada di depan pusat massa tubuh."
            });
        }

        const trunkLean = Math.abs(summary.angles.trunk_lean_at_contact_median || 0);
        if (trunkLean > 12) {
            flags.push({
                code: "high_trunk_lean",
                severity: "info",
                value: trunkLean,
                threshold: 12,
                confidence: 0.8,
                message: "Condong tubuh (trunk lean) terindikasi cukup tinggi. Perlu ditinjau keselarasan postur."
            });
        }

        const asymmetry = summary.symmetry.gct_asymmetry_percent;
        if (asymmetry !== null && asymmetry > 5.0) {
            flags.push({
                code: "left_right_contact_asymmetry",
                severity: "info",
                value: asymmetry,
                threshold: 5.0,
                confidence: 0.85,
                message: "Terdeteksi perbedaan waktu kontak tanah antara kaki kiri dan kanan."
            });
        }
        
        return flags;
    }

    function generateQualityReport(frames, steps) {
        const totalFrames = frames.length;
        if (totalFrames === 0) return { overall_score: 0 };
        
        const framesWithPerson = frames.filter(f => f.smoothed_landmarks).length;
        const presenceRatio = framesWithPerson / totalFrames;
        
        let sumVisibility = 0;
        let sumLowerBodyVisibility = 0;
        
        frames.forEach(f => {
            const lms = f.smoothed_landmarks;
            if (!lms) return;
            sumVisibility += calculateVisibilityScore(lms, Array.from({ length: 33 }, (_, i) => i));
            sumLowerBodyVisibility += calculateVisibilityScore(lms, [23, 24, 25, 26, 27, 28, 29, 30, 31, 32]);
        });

        const meanVisibility = sumVisibility / framesWithPerson;
        const lowerBodyVisibility = sumLowerBodyVisibility / framesWithPerson;
        const timestampStability = 0.95;
        const completeStepRatio = steps.length > 0 ? steps.filter(s => s.status !== 'incomplete').length / steps.length : 0.0;
        const overallScore = (presenceRatio * 0.2) + (meanVisibility * 0.25) + (lowerBodyVisibility * 0.25) + (timestampStability * 0.15) + (completeStepRatio * 0.15);
        
        return {
            pose_presence_ratio: presenceRatio,
            mean_visibility: meanVisibility,
            lower_body_visibility: lowerBodyVisibility,
            timestamp_stability: timestampStability,
            ground_estimation_confidence: 0.8,
            event_detection_confidence: completeStepRatio,
            complete_step_ratio: completeStepRatio,
            overall_score: overallScore
        };
    }

    function trimNonRunningFrames(rawFrames) {
        // 1. Identify valid frames where pelvis is detected and is inside the active horizontal zone
        const validFrames = [];
        rawFrames.forEach(f => {
            const lms = f.landmarks;
            if (!lms) return;
            const lHip = lms[23];
            const rHip = lms[24];
            if (lHip && rHip && lHip.visibility > 0.5 && rHip.visibility > 0.5) {
                const pelvisX = (lHip.x + rHip.x) / 2;
                // Keep frames where pelvis is inside the active zone (10% to 90% of screen width)
                if (pelvisX >= 0.10 && pelvisX <= 0.90) {
                    validFrames.push(f);
                }
            }
        });

        // 2. If no valid frames inside the zone, fallback to all frames where a person is detected
        if (validFrames.length < 10) {
            return rawFrames.filter(f => {
                const lms = f.landmarks;
                if (!lms) return false;
                const lHip = lms[23];
                const rHip = lms[24];
                return lHip && rHip && lHip.visibility > 0.5 && rHip.visibility > 0.5;
            });
        }

        // 3. Ensure the runner shows horizontal movement (displacement) in the trimmed segment
        let minX = 1.0;
        let maxX = 0.0;
        validFrames.forEach(f => {
            const pelvisX = (f.landmarks[23].x + f.landmarks[24].x) / 2;
            if (pelvisX < minX) minX = pelvisX;
            if (pelvisX > maxX) maxX = pelvisX;
        });

        const displacement = maxX - minX;
        console.log(`[Activity Trimmer] Original frames: ${rawFrames.length}, Trimmed: ${validFrames.length}, Horizontal displacement: ${displacement.toFixed(3)}`);

        // Re-index the trimmed frames starting from 0
        return validFrames.map((f, idx) => {
            return {
                ...f,
                frame_index: idx
            };
        });
    }

    function analyzeCapturedTrial(rawFrames, runnerId, trialId, videoWidth, videoHeight) {
        const smoothedFrames = smoothFrameSequence(rawFrames);
        const proportions = calculateBodyProportions(smoothedFrames);
        const ground = estimateGroundPlane(smoothedFrames);
        const framesWithContact = estimateFrameContacts(smoothedFrames, ground.ground_y_normalized, proportions.refLegLength);
        const framesWithAngles = calculateFrameAngles(framesWithContact);
        const framesWithPhases = estimateFramePhases(framesWithAngles, proportions.travelDirection);
        const events = detectGaitEvents(framesWithPhases);
        const steps = buildSteps(framesWithPhases, events, proportions.refLegLength, proportions.travelDirection);
        const summary = generateTrialSummary(framesWithPhases, steps, proportions.travelDirection, proportions.refLegLength);
        const qualityReport = generateQualityReport(framesWithPhases, steps);
        
        summary.flags = evaluateFormIndicators(summary, qualityReport.overall_score);
        
        return {
            schema_version: 2,
            trial: {
                trial_id: trialId,
                runner_id: runnerId,
                captured_at: new Date().toISOString(),
                camera: {
                    width: videoWidth,
                    height: videoHeight,
                    fps_reported: 30,
                    view_mode: "contain"
                },
                pose_model: {
                    name: "pose_landmarker_full",
                    version: "0.10.14"
                }
            },
            frames: framesWithPhases.map(f => ({
                frame_index: f.frame_index,
                timestamp_ms: f.timestamp_ms,
                video_time_ms: f.video_time_ms,
                delta_ms: f.delta_ms,
                landmarks: f.landmarks,
                world_landmarks: f.world_landmarks,
                angles: f.angles,
                contact: {
                    left: { is_contact: f.contact.left.is_contact, confidence: f.contact.left.confidence },
                    right: { is_contact: f.contact.right.is_contact, confidence: f.contact.right.confidence }
                },
                phase: f.phase,
                quality: {
                    visibility: calculateVisibilityScore(f.landmarks, Array.from({ length: 33 }, (_, i) => i))
                }
            })),
            events: events,
            steps: steps,
            summary: summary,
            quality: qualityReport,
            landmarks: rawFrames.map(f => ({
                ts: f.timestamp_ms,
                landmarks: f.landmarks
            })),
            analysis_engine: {
                name: "ruanglari_gait_analyzer",
                version: "2.0.0",
                mode: "heuristic_monocular_side_view"
            }
        };
    }

    // -------------------------------------------------------------
    // Drawing Overlay Helpers
    // -------------------------------------------------------------
    const POSE_COLORS = {
        head: '#E2E8F0',
        torso: '#22D3EE',
        leftArm: '#F472B6',
        rightArm: '#FB923C',
        leftLeg: '#A3E635',
        rightLeg: '#60A5FA',
    };

    const SKELETON_GROUPS = [
        { key: 'head', color: POSE_COLORS.head, connections: [[0,1], [1,2], [2,3], [3,7], [0,4], [4,5], [5,6], [6,8], [9,10]] },
        { key: 'torso', color: POSE_COLORS.torso, connections: [[11,12], [11,23], [12,24], [23,24]] },
        { key: 'leftArm', color: POSE_COLORS.leftArm, connections: [[11,13], [13,15]] },
        { key: 'rightArm', color: POSE_COLORS.rightArm, connections: [[12,14], [14,16]] },
        { key: 'leftLeg', color: POSE_COLORS.leftLeg, connections: [[23,25], [25,27], [27,29], [29,31], [31,27]] },
        { key: 'rightLeg', color: POSE_COLORS.rightLeg, connections: [[24,26], [26,28], [28,30], [30,32], [32,28]] },
    ];

    function drawSkeleton(ctx, landmarks, canvasWidth, canvasHeight) {
        if (!landmarks) return;
        const pointOnCanvas = (lm) => ({ x: lm.x * canvasWidth, y: lm.y * canvasHeight });

        // Draw connections
        SKELETON_GROUPS.forEach(group => {
            ctx.strokeStyle = group.color;
            ctx.lineWidth = 3;
            group.connections.forEach(conn => {
                const start = landmarks[conn[0]];
                const end = landmarks[conn[1]];
                if (start && end && start.visibility > 0.5 && end.visibility > 0.5) {
                    ctx.beginPath();
                    const p1 = pointOnCanvas(start);
                    const p2 = pointOnCanvas(end);
                    ctx.moveTo(p1.x, p1.y);
                    ctx.lineTo(p2.x, p2.y);
                    ctx.stroke();
                }
            });
        });

        // Draw points
        landmarks.forEach((lm, idx) => {
            if (lm.visibility > 0.5) {
                ctx.beginPath();
                const p = pointOnCanvas(lm);
                ctx.arc(p.x, p.y, 4, 0, 2 * Math.PI);
                ctx.fillStyle = '#FFFFFF';
                ctx.fill();
            }
        });
    }

    // -------------------------------------------------------------
    // UI Events and Flow Orchestration
    // -------------------------------------------------------------
    const fileInput = document.getElementById('video-file-input');
    const startBtn = document.getElementById('start-analysis-btn');
    const videoEl = document.getElementById('processing-video');
    const canvasEl = document.getElementById('processing-canvas');
    const ctx = canvasEl.getContext('2d');
    
    // File upload change
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const sizeInMB = file.size / (1024 * 1024);
            const warningEl = document.getElementById('size-warning-container');
            const sizeTextEl = document.getElementById('warn-file-size');
            
            if (sizeInMB > 15) {
                warningEl.classList.remove('hidden');
                sizeTextEl.innerText = sizeInMB.toFixed(1);
                showToast(`File size (${sizeInMB.toFixed(1)}MB) is too large. Ideal limit is 15MB.`, true);
                state.videoFile = null;
                document.getElementById('file-label').innerText = "Click or drag video here";
                fileInput.value = ""; // clear selection
                checkFormReady();
                return;
            } else {
                warningEl.classList.add('hidden');
            }

            state.videoFile = file;
            document.getElementById('file-label').innerText = file.name;
            document.getElementById('workspace-empty').classList.add('hidden');
            
            // Render basic file preview
            videoEl.src = URL.createObjectURL(file);
            videoEl.onloadedmetadata = () => {
                canvasEl.width = videoEl.videoWidth;
                canvasEl.height = videoEl.videoHeight;
                ctx.fillStyle = '#1e293b';
                ctx.fillRect(0, 0, canvasEl.width, canvasEl.height);
                
                // Show trim controls and initialize values
                const duration = videoEl.duration;
                document.getElementById('trim-controls').classList.remove('hidden');
                document.getElementById('trim-start').value = 0;
                document.getElementById('trim-start').max = duration;
                document.getElementById('trim-end').value = duration.toFixed(1);
                document.getElementById('trim-end').max = duration;
                document.getElementById('video-total-duration').innerText = duration.toFixed(1);
                document.getElementById('video-selected-duration').innerText = duration.toFixed(1);

                // Draw first frame
                videoEl.currentTime = 0;
                videoEl.onseeked = () => {
                    ctx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);
                };
            };
            
            checkFormReady();
        }
    });

    // Trim input preview triggers
    document.getElementById('trim-start').addEventListener('change', async (e) => {
        const val = parseFloat(e.target.value) || 0;
        const duration = videoEl.duration || 0;
        const trimEndEl = document.getElementById('trim-end');
        const endVal = parseFloat(trimEndEl.value) || duration;
        
        const start = clamp(val, 0, endVal - 0.1);
        e.target.value = start.toFixed(1);
        
        document.getElementById('video-selected-duration').innerText = (endVal - start).toFixed(1);
        
        await seekTo(videoEl, start);
        ctx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);
    });

    document.getElementById('trim-end').addEventListener('change', async (e) => {
        const val = parseFloat(e.target.value) || 0;
        const duration = videoEl.duration || 0;
        const trimStartEl = document.getElementById('trim-start');
        const startVal = parseFloat(trimStartEl.value) || 0;
        
        const end = clamp(val, startVal + 0.1, duration);
        e.target.value = end.toFixed(1);
        
        document.getElementById('video-selected-duration').innerText = (end - startVal).toFixed(1);
        
        await seekTo(videoEl, end);
        ctx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);
    });

    document.getElementById('runner-selector').addEventListener('change', checkFormReady);

    function checkFormReady() {
        const runnerId = document.getElementById('runner-selector').value;
        if (runnerId && state.videoFile) {
            startBtn.removeAttribute('disabled');
        } else {
            startBtn.setAttribute('disabled', 'true');
        }
    }

    // Load MediaPipe on page load
    async function loadEngine() {
        try {
            document.getElementById('model-status').innerText = "Loading WASM...";
            const vision = await FilesetResolver.forVisionTasks(
                "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/wasm"
            );
            
            document.getElementById('model-status').innerText = "Loading Model...";
            state.poseLandmarker = await PoseLandmarker.createFromOptions(vision, {
                baseOptions: {
                    modelAssetPath: "https://storage.googleapis.com/mediapipe-models/pose_landmarker/pose_landmarker_full/float16/1/pose_landmarker_full.task",
                    delegate: "GPU"
                },
                runningMode: "VIDEO",
                numPoses: 1,
                minPoseDetectionConfidence: 0.5,
                minPosePresenceConfidence: 0.5,
                minTrackingConfidence: 0.5,
            });
            document.getElementById('model-status').innerText = "Ready";
            document.getElementById('model-status').className = "text-green-400";
        } catch (err) {
            console.error("Failed to load MediaPipe Pose model", err);
            document.getElementById('model-status').innerText = "Error Loading";
            document.getElementById('model-status').className = "text-red-500";
            showToast("Failed to initialize analysis engine: " + err.message, true);
        }
    }

    // Seek helper promise
    const seekTo = (video, time) => new Promise((resolve) => {
        const onSeeked = () => {
            video.removeEventListener('seeked', onSeeked);
            resolve();
        };
        video.addEventListener('seeked', onSeeked);
        video.currentTime = time;
    });

    // Start processing
    startBtn.addEventListener('click', async () => {
        if (state.processing) return;
        
        const runnerId = document.getElementById('runner-selector').value;
        const fps = parseInt(document.getElementById('fps-selector').value) || 30;
        
        if (!runnerId || !state.videoFile || !state.poseLandmarker) {
            showToast("Please make sure a runner is selected, video is uploaded, and engine is ready.", true);
            return;
        }

        state.processing = true;
        document.getElementById('processing-overlay').classList.remove('hidden');
        updateProgress(0, "Preparing video file and frames...");

        try {
            const videoWidth = videoEl.videoWidth;
            const videoHeight = videoEl.videoHeight;
            const duration = videoEl.duration;
            const interval = 1 / fps;
            const trimStartVal = parseFloat(document.getElementById('trim-start').value) || 0;
            const trimEndVal = parseFloat(document.getElementById('trim-end').value) || duration;
            
            const startLimit = Math.max(0, trimStartVal);
            const endLimit = Math.min(duration, trimEndVal);
            
            let currentTime = startLimit;
            const targetDuration = endLimit - startLimit;
            const rawFrames = [];
            let frameIndex = 0;

            while (currentTime < endLimit) {
                await seekTo(videoEl, currentTime);
                
                // Draw current frame to canvas
                ctx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);
                
                // Run pose detection
                const timestampMs = Math.round(currentTime * 1000);
                const results = state.poseLandmarker.detectForVideo(videoEl, timestampMs);
                
                if (results.landmarks && results.landmarks.length > 0) {
                    const landmarks = results.landmarks[0];
                    const worldLandmarks = results.worldLandmarks ? results.worldLandmarks[0] : null;
                    
                    rawFrames.push({
                        frame_index: frameIndex++,
                        timestamp_ms: timestampMs,
                        video_time_ms: timestampMs,
                        delta_ms: Math.round(interval * 1000),
                        landmarks: landmarks,
                        world_landmarks: worldLandmarks
                    });

                    // Draw skeleton overlays on canvas in real-time
                    drawSkeleton(ctx, landmarks, canvasEl.width, canvasEl.height);
                }

                currentTime += interval;
                const pct = Math.min(99, Math.round(((currentTime - startLimit) / targetDuration) * 100));
                updateProgress(pct, `Extracting landmarks: ${pct}% (${frameIndex} frames)`);
                document.getElementById('processed-frames-count').innerText = frameIndex;
            }

            updateProgress(100, "Extracting landmarks: 100% (Complete)");
            await new Promise(r => setTimeout(r, 600));

            // Trim non-running frames (leading/trailing static states outside the active zone)
            updateProgress(100, "Trimming static frames and isolating running pass...");
            const trimmedFrames = trimNonRunningFrames(rawFrames);
            
            if (trimmedFrames.length < 10) {
                throw new Error("Tidak dapat mengisolasi fase lari yang valid dalam video. Pastikan pelari melintas di depan kamera.");
            }

            // Validate activity
            updateProgress(100, "Validating gait movement pattern...");
            const totalFrames = trimmedFrames.length;
            const framesWithPerson = trimmedFrames.filter(f => f.landmarks).length;
            const presenceRatio = framesWithPerson / totalFrames;

            if (presenceRatio < 0.5) {
                throw new Error(`Orang tidak terdeteksi dengan jelas (Kualitas deteksi: ${Math.round(presenceRatio * 100)}%). Pastikan seluruh tubuh pelari terlihat jelas.`);
            }

            // Run analysis engine
            updateProgress(100, "Calculating biomechanics and steps...");
            const trialId = window.PreloadedTrial ? window.PreloadedTrial.id : crypto.randomUUID();
            const payload = analyzeCapturedTrial(trimmedFrames, runnerId, trialId, videoWidth, videoHeight);
            const poseData = JSON.stringify(payload);

            // Compute Hash
            const hashBuffer = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(poseData));
            const hashHex = Array.from(new Uint8Array(hashBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');

            // Save to Dexie outbox
            updateProgress(100, "Saving to upload queue...");
            await db.outbox.add({
                trialId: trialId,
                runnerId: runnerId,
                data: poseData,
                hash: hashHex,
                videoBlob: state.videoFile, // direct reference to the manual file
                status: 'pending',
                timestamp: Date.now()
            });

            showToast("Video processing complete! Trial added to upload queue.");
            updateProgress(100, "Uploading data to server...");
            
            // Update UI count
            updatePendingCount();

            // Trigger instant upload processing
            processOutbox();

        } catch (err) {
            console.error("Error during manual video analysis", err);
            showToast("Analysis Error: " + err.message, true);
        } finally {
            state.processing = false;
            document.getElementById('processing-overlay').classList.add('hidden');
        }
    });

    function updateProgress(pct, message) {
        document.getElementById('progress-bar').style.width = `${pct}%`;
        document.getElementById('progress-desc').innerText = message;
    }

    // Dexie Upload Engine
    async function processOutbox() {
        const pending = await db.outbox.where('status').equals('pending').toArray();
        updatePendingCount();
        
        for (const item of pending) {
            try {
                // 1. Create Trial (Idempotent)
                const storeRes = await fetch(`{{ url('/admin/running-analysis/sessions/' . $session->id . '/trials') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        id: item.trialId,
                        runner_id: item.runnerId,
                        camera_device_label: 'Manual Upload',
                        camera_width: canvasEl.width,
                        camera_height: canvasEl.height,
                        camera_fps: 30,
                        inference_fps: 30,
                        pose_model: 'pose_landmarker'
                    })
                });

                if (!storeRes.ok) {
                    const errBody = await storeRes.text();
                    throw new Error(`HTTP ${storeRes.status}: ${errBody.substring(0, 200)}`);
                }

                // 2. Upload Artifact JSON
                const jsonFile = new File([item.data], 'pose_landmarks.json', { type: 'application/json' });
                const formData = new FormData();
                formData.append('type', 'pose_landmarks');
                formData.append('file', jsonFile);
                formData.append('sha256', item.hash);

                const uploadRes = await fetch(`{{ url('/admin/running-analysis/trials') }}/${item.trialId}/artifacts`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                if (!uploadRes.ok) {
                    const errBody = await uploadRes.text();
                    throw new Error(`Upload HTTP ${uploadRes.status}: ${errBody.substring(0, 200)}`);
                }

                // 3. Upload Original Video Clip
                if (item.videoBlob && item.videoBlob.size > 0) {
                    const videoBuffer = await item.videoBlob.arrayBuffer();
                    const videoHashBuffer = await crypto.subtle.digest('SHA-256', videoBuffer);
                    const videoHashHex = Array.from(new Uint8Array(videoHashBuffer))
                        .map(b => b.toString(16).padStart(2, '0')).join('');

                    const videoFile = new File([item.videoBlob], item.videoBlob.name || 'video_clip.mp4', { type: item.videoBlob.type });
                    const videoFormData = new FormData();
                    videoFormData.append('type', 'video_clip');
                    videoFormData.append('file', videoFile);
                    videoFormData.append('sha256', videoHashHex);

                    const videoUploadRes = await fetch(`{{ url('/admin/running-analysis/trials') }}/${item.trialId}/artifacts`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: videoFormData
                    });

                    if (!videoUploadRes.ok) {
                        const errBody = await videoUploadRes.text();
                        console.warn(`[Upload] Video upload failed: ${errBody.substring(0, 100)}`);
                    }
                }

                // 4. Finalize
                const isSync = document.getElementById('exec-mode').value === 'sync';
                const finalizeRes = await fetch(`{{ url('/admin/running-analysis/trials') }}/${item.trialId}/finalize?sync=${isSync}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (!finalizeRes.ok) {
                    const errBody = await finalizeRes.text();
                    throw new Error(`Finalize HTTP ${finalizeRes.status}: ${errBody.substring(0, 200)}`);
                }

                // Mark success locally
                await db.outbox.update(item.id, { status: 'uploaded' });
                showToast("Trial uploaded and finalized successfully!");
                
                // Redirect back to session show page after complete upload
                setTimeout(() => {
                    window.location.href = "{{ route('admin.running-analysis.sessions.show', $session) }}";
                }, 1000);

            } catch (err) {
                console.error(`[Upload] FAILED:`, err.message);
                showToast(`Upload error: ${err.message.substring(0, 80)}`, true);
            }
        }
        updatePendingCount();
    }

    async function updatePendingCount() {
        const count = await db.outbox.where('status').equals('pending').count();
        document.getElementById('outbox-pending-count').innerText = count;
    }

    // Toast Notification Helper
    function showToast(message, isError = false) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        const borderColor = isError ? 'border-red-500' : 'border-neon';
        const iconClass = isError ? 'fas fa-exclamation-triangle text-red-400' : 'fas fa-check-circle text-neon';
        toast.className = `bg-slate-800 border ${borderColor} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 transition-opacity duration-300`;
        toast.innerHTML = `<i class="${iconClass}"></i><div class="text-sm font-semibold">${message}</div>`;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, isError ? 6000 : 3000);
    }

    // Run on boot
    loadEngine();
    updatePendingCount();
    setInterval(processOutbox, 30000);

    // Check for preloaded trial video from the server
    window.PreloadedTrial = {!! isset($trial) ? json_encode([
        'id' => $trial->id,
        'runner_id' => $trial->runner_id,
        'video_url' => $videoUrl
    ]) : 'null' !!};

    if (window.PreloadedTrial) {
        (async () => {
            const runnerSelector = document.getElementById('runner-selector');
            runnerSelector.value = window.PreloadedTrial.runner_id;
            runnerSelector.disabled = true;
            
            document.getElementById('video-file-input').disabled = true;
            
            document.getElementById('processing-overlay').classList.remove('hidden');
            updateProgress(30, "Downloading video from server...");
            
            try {
                const response = await fetch(window.PreloadedTrial.video_url);
                if (!response.ok) throw new Error("Server responded with HTTP " + response.status);
                const blob = await response.blob();
                const file = new File([blob], "reanalyze_video.mp4", { type: blob.type });
                
                state.videoFile = file;
                document.getElementById('file-label').innerText = "Loaded server video (Re-analyze Mode)";
                document.getElementById('workspace-empty').classList.add('hidden');
                
                videoEl.src = URL.createObjectURL(file);
                videoEl.onloadedmetadata = () => {
                    canvasEl.width = videoEl.videoWidth;
                    canvasEl.height = videoEl.videoHeight;
                    ctx.fillStyle = '#1e293b';
                    ctx.fillRect(0, 0, canvasEl.width, canvasEl.height);
                    
                    const duration = videoEl.duration;
                    document.getElementById('trim-controls').classList.remove('hidden');
                    document.getElementById('trim-start').value = 0;
                    document.getElementById('trim-start').max = duration;
                    document.getElementById('trim-end').value = duration.toFixed(1);
                    document.getElementById('trim-end').max = duration;
                    document.getElementById('video-total-duration').innerText = duration.toFixed(1);
                    document.getElementById('video-selected-duration').innerText = duration.toFixed(1);

                    videoEl.currentTime = 0;
                    videoEl.onseeked = () => {
                        ctx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);
                    };
                    
                    checkFormReady();
                };
            } catch (e) {
                console.error("Failed to load preloaded video", e);
                showToast("Failed to load video from server: " + e.message, true);
            } finally {
                document.getElementById('processing-overlay').classList.add('hidden');
            }
        })();
    }
</script>
@endpush
