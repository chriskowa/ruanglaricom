<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capture - {{ $session->name }} - Ruang Lari</title>
    
    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Dexie (IndexedDB) -->
    <script src="https://unpkg.com/dexie@4.0.7/dist/dexie.min.js"></script>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        neon: '#ccff00',
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #060a17; color: white; overflow: hidden; }
        .pulse-red { animation: pulseRed 1.5s infinite; }
        @keyframes pulseRed {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
                #video-container { position: relative; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; background: #000; overflow: hidden; }
        #camera-feed, #canvas-overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            transform: scaleX(1);
            object-fit: contain; /* Default to Contain (Landscape) */
            transition: object-fit 0.2s ease;
        }
        #canvas-overlay { z-index: 10; pointer-events: none; }
        
        /* Analysis Zone Indicators */
        .zone-line { position: absolute; top: 0; bottom: 0; width: 4px; z-index: 20; opacity: 0.5; pointer-events: none; }
        .zone-line-left { left: 15%; background: linear-gradient(to right, transparent, #ccff00); border-right: 2px dashed #ccff00; }
        .zone-line-right { right: 15%; background: linear-gradient(to left, transparent, #ccff00); border-left: 2px dashed #ccff00; }
        .zone-active .zone-line-left, .zone-active .zone-line-right { border-color: #ef4444; background: transparent; opacity: 1; border-style: solid; }
    </style>
</head>
<body class="h-screen w-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="h-14 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-4 z-30 shrink-0">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.running-analysis.sessions.show', $session) }}" class="text-slate-400 hover:text-white transition-colors" title="Exit Capture">
                <i class="fas fa-times text-xl"></i>
            </a>
            <div class="h-6 w-px bg-slate-700"></div>
            <div>
                <h1 class="font-black italic tracking-tighter text-sm uppercase">Ruang<span class="text-neon">Lari</span> Analysis</h1>
                <div class="text-xs text-slate-500 truncate max-w-[200px]">{{ $session->name }}</div>
            </div>
        </div>

        <!-- Camera Controls -->
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-xs">
                <label class="text-slate-400 font-semibold uppercase">Camera:</label>
                <select id="camera-selector" class="bg-slate-800 border border-slate-700 text-white rounded px-2 py-1 outline-none focus:border-neon max-w-[200px]">
                    <option value="">Loading cameras...</option>
                </select>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <label class="text-slate-400 font-semibold uppercase">Resolution:</label>
                <select id="resolution-selector" class="bg-slate-800 border border-slate-700 text-white rounded px-2 py-1 outline-none focus:border-neon">
                    <option value="1920x1080">1080p (FHD)</option>
                    <option value="1280x720" selected>720p (HD)</option>
                    <option value="640x480">480p (SD)</option>
                </select>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <label class="text-slate-400 font-semibold uppercase">FPS:</label>
                <select id="fps-selector" class="bg-slate-800 border border-slate-700 text-white rounded px-2 py-1 outline-none focus:border-neon">
                    <option value="60">60 fps</option>
                    <option value="30" selected>30 fps</option>
                </select>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <label class="text-slate-400 font-semibold uppercase">View Mode:</label>
                <select id="view-mode-selector" onchange="setViewMode(this.value)" class="bg-slate-800 border border-slate-700 text-white rounded px-2 py-1 outline-none focus:border-neon">
                    <option value="contain" selected>Landscape (Contain)</option>
                    <option value="cover">Full Screen (Cover)</option>
                </select>
            </div>
            <button id="apply-camera-btn" class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-1 rounded text-xs font-bold transition-colors">Apply</button>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- Sidebar: Runner Queue & Settings -->
        <aside class="w-80 bg-slate-900 border-r border-slate-800 flex flex-col z-20 shrink-0">
            <!-- Active Runner Card -->
            <div class="p-4 border-b border-slate-800 bg-slate-800/50">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Up Next</h2>
                <div id="active-runner-card" class="bg-slate-950 border border-neon rounded-lg p-3 shadow-[0_0_15px_rgba(204,255,0,0.1)]">
                    <!-- Populated by JS -->
                    <div class="text-center py-4 text-slate-500 italic text-sm">No runners in queue</div>
                </div>
            </div>

            <!-- Capture Mode Toggle -->
            <div class="p-4 border-b border-slate-800 bg-slate-900/30 shrink-0">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Capture Mode</h2>
                <div class="grid grid-cols-2 gap-2">
                    <button id="mode-auto-btn" onclick="setCaptureMode('auto')" type="button" class="px-2 py-1.5 rounded text-[10px] font-black uppercase tracking-wider text-center border bg-neon text-black border-neon transition-all focus:outline-none">
                        <i class="fas fa-magic mr-1"></i> Auto
                    </button>
                    <button id="mode-manual-btn" onclick="setCaptureMode('manual')" type="button" class="px-2 py-1.5 rounded text-[10px] font-black uppercase tracking-wider text-center border bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700 hover:text-white transition-all focus:outline-none">
                        <i class="fas fa-hand-pointer mr-1"></i> Manual
                    </button>
                </div>
                <p id="mode-description" class="text-[9px] text-slate-500 mt-1.5 leading-relaxed font-sans">
                    Auto-starts ketika pelvis memasuki zona aktif. Auto-stops ketika keluar.
                </p>
            </div>

            <!-- Queue List -->
            <div class="flex-1 overflow-y-auto p-2" id="runner-queue-list">
                <!-- Populated by JS -->
            </div>

            <!-- Settings / Debug Info -->
            <div class="p-4 border-t border-slate-800 bg-slate-950 text-xs font-mono text-slate-400 space-y-1">
                <div class="flex justify-between"><span>Model:</span> <span id="model-status" class="text-yellow-400">Loading...</span></div>
                <div class="flex justify-between"><span>Inference:</span> <span id="inference-fps">0</span> fps</div>
                <div class="flex justify-between"><span>Stream:</span> <span id="actual-resolution">-</span> @ <span id="actual-fps">-</span> fps</div>
                <div class="flex justify-between"><span>Visibility:</span> <span id="pose-visibility">No Pose</span></div>
            </div>
        </aside>

        <!-- Center: Camera Feed -->
        <main class="flex-1 relative bg-black flex flex-col">
            <!-- Video Container -->
            <div id="video-container" class="flex-1 w-full relative">
                <video id="camera-feed" autoplay playsinline muted></video>
                <canvas id="canvas-overlay"></canvas>
                
                <!-- Zone Lines -->
                <div class="zone-line zone-line-left"></div>
                <div class="zone-line zone-line-right"></div>

                <!-- Capture Overlay UI -->
                <div class="absolute top-6 left-1/2 -translate-x-1/2 flex gap-4 z-30">
                    <div id="capture-status-badge" class="bg-slate-900/80 backdrop-blur border border-slate-700 text-slate-300 px-4 py-2 rounded-full font-bold uppercase tracking-wider text-sm transition-all duration-300">
                        Waiting for Runner
                    </div>
                </div>

                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 flex items-center gap-4">
                    <button id="manual-record-btn" class="w-16 h-16 rounded-full border-4 border-slate-700 bg-slate-800 hover:border-red-500 hover:bg-red-500 transition-all flex items-center justify-center group outline-none hidden">
                        <div class="w-6 h-6 rounded-sm bg-white group-hover:scale-90 transition-transform"></div>
                    </button>
                    <div id="recording-timer" class="text-white font-mono text-xl font-bold bg-black/50 px-3 py-1 rounded hidden pulse-red">00:00.0</div>
                </div>
            </div>

            <!-- Toast Notification Container -->
            <div id="toast-container" class="absolute top-20 right-4 flex flex-col gap-2 z-50"></div>
        </main>
    </div>

    <!-- Data passed to JS -->
    <script>
        window.SessionData = {
            id: "{{ $session->id }}",
            runners: {!! json_encode($session->runners->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'username' => $r->username,
                'avatar' => $r->avatar_url,
                'gender' => $r->gender,
                'status' => $r->pivot->status,
                'sequence_no' => $r->pivot->sequence_no
            ])->values()) !!}
        };
    </script>

    <!-- App Logic -->
    <script type="module">
        import { FilesetResolver, PoseLandmarker } from "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14";
        // State
        const state = {
            runners: window.SessionData.runners,
            activeRunnerIndex: 0,
            
            // Camera
            stream: null,
            videoWidth: 0,
            videoHeight: 0,
            
            // MediaPipe
            vision: null,
            poseLandmarker: null,
            lastVideoTime: -1,
            
            // Capture Logic
            captureMode: 'auto', // auto, manual
            userSelectedActiveIndex: null,
            status: 'IDLE', // IDLE, READY, RECORDING, PROCESSING
            captureFrames: [],
            recordingStartTime: 0,

            // Video Recording
            mediaRecorder: null,
            videoChunks: [],
            
            // Zone config
            zoneMinX: 0.15, // 15% from left
            zoneMaxX: 0.85, // 15% from right

            // Biomechanics analysis
            analysisHistory: [],
            travelDirection: 'unknown',
            realTimeMetrics: null
        };

        // -------------------------------------------------------------
        // BIOMECHANICS & KINEMATICS ENGINE CONFIGURATION (Stage 4)
        // -------------------------------------------------------------
        const ANALYSIS_CONFIG = {
            minVisibility: 0.55,                  // Minimum confidence required to accept landmark values.
            smoothingAlpha: 0.35,                 // Exponential moving average coefficient. Low = smoother, High = faster.
            minimumDeltaMs: 5,                    // Min frame-to-frame delta to avoid noise.
            maximumDeltaMs: 100,                  // Max time delta before reset/gap is declared.
            contactGroundToleranceLegRatio: 0.04,   // Vertical ankle closeness to ground to estimate contact.
            minimumEventConfidence: 0.6,          // Heuristic score threshold for gait events.
            minimumStepDurationMs: 180,           // Lower constraint on step duration.
            maximumStepDurationMs: 1200,          // Upper constraint on step duration.
            phaseDebounceFrames: 2,               // Frame window to avoid contact status flicker.
            overstrideThreshold: 0.12             // Normalised landing lever above which overstride is flagged.
        };
        
        // Math Utilities (Stage 5)
        const safeNumber = (val, fallback = null) => {
            if (val === undefined || val === null || isNaN(val) || !isFinite(val)) return fallback;
            return val;
        };

        const distance2D = (a, b) => {
            if (!a || !b) return null;
            return Math.hypot(a.x - b.x, a.y - b.y);
        };

        const distance3D = (a, b) => {
            if (!a || !b) return null;
            return Math.hypot(a.x - b.x, a.y - b.y, a.z - b.z);
        };

        const midpoint = (a, b) => {
            if (!a || !b) return null;
            return {
                x: (a.x + b.x) / 2,
                y: (a.y + b.y) / 2,
                z: (a.z + b.z) / 2
            };
        };

        const calculateAngle2D = (a, b, c) => {
            if (!a || !b || !c) return null;
            const abx = a.x - b.x, aby = a.y - b.y;
            const cbx = c.x - b.x, cby = c.y - b.y;
            const dot = abx * cbx + aby * cby;
            const mag1 = Math.hypot(abx, aby);
            const mag2 = Math.hypot(cbx, cby);
            if (!mag1 || !mag2) return null;
            const cos = clamp(dot / (mag1 * mag2), -1, 1);
            return toDeg(Math.acos(cos));
        };

        const calculateAngle3D = (a, b, c) => {
            if (!a || !b || !c) return null;
            const abx = a.x - b.x, aby = a.y - b.y, abz = a.z - b.z;
            const cbx = c.x - b.x, cby = c.y - b.y, cbz = c.z - b.z;
            const dot = abx * cbx + aby * cby + abz * cbz;
            const mag1 = Math.hypot(abx, aby, abz);
            const mag2 = Math.hypot(cbx, cby, cbz);
            if (!mag1 || !mag2) return null;
            const cos = clamp(dot / (mag1 * mag2), -1, 1);
            return toDeg(Math.acos(cos));
        };

        const calculateSegmentAngle = (a, b) => {
            if (!a || !b) return null;
            const dx = b.x - a.x;
            const dy = b.y - a.y;
            return toDeg(Math.atan2(dx, dy));
        };

        const calculateVisibilityScore = (landmarks, indexes) => {
            if (!landmarks || !indexes || indexes.length === 0) return 0;
            let sum = 0;
            let count = 0;
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

        const mean = (values) => {
            if (!values || values.length === 0) return null;
            return values.reduce((sum, v) => sum + v, 0) / values.length;
        };

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

        // Landmark Exponential Moving Average Filter (Stage 6)
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

        // Biomechanical Helpers (Stage 7)
        function getAngleWithFallback(landmarks, worldLandmarks, p, is3D = true) {
            const lms = is3D && worldLandmarks ? worldLandmarks : landmarks;
            const a = lms[p[0]], b = lms[p[1]], c = lms[p[2]];
            if (!a || !b || !c) return { value: null, source: "failed", confidence: 0 };
            
            const val = is3D && worldLandmarks 
                ? calculateAngle3D(a, b, c) 
                : calculateAngle2D(a, b, c);
                
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
            
            const dx = b.x - a.x;
            const dy = b.y - a.y;
            let angle = toDeg(Math.atan2(dx, dy));
            if (relativeTo === "horizontal") {
                angle = toDeg(Math.atan2(dy, dx));
            }
            
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

        // Body Scale & Trajectory Direction (Stage 8)
        function calculateBodyProportions(frames) {
            const hipKneeDists = [];
            const kneeAnkleDists = [];
            const shHipDists = [];
            const pelvisXCoords = [];
            const timestamps = [];

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
                    timestamps.push(frame.timestamp_ms);
                }
            });

            const medianHK = median(hipKneeDists) || 0.15;
            const medianKA = median(kneeAnkleDists) || 0.15;
            const medianSH = median(shHipDists) || 0.25;
            const refLegLength = medianHK + medianKA;
            const bodyScale = (medianHK + medianKA + medianSH) / 3;

            let travelDirection = 'unknown';
            let directionConfidence = 0.0;
            if (pelvisXCoords.length > 5) {
                const firstIdx = 0;
                const lastIdx = pelvisXCoords.length - 1;
                const diffX = pelvisXCoords[lastIdx] - pelvisXCoords[firstIdx];
                if (Math.abs(diffX) > 0.05) {
                    travelDirection = diffX > 0 ? 'left_to_right' : 'right_to_left';
                    directionConfidence = 1.0;
                } else {
                    let positiveDiffs = 0;
                    let negativeDiffs = 0;
                    for (let i = 1; i < pelvisXCoords.length; i++) {
                        const diff = pelvisXCoords[i] - pelvisXCoords[i - 1];
                        if (diff > 0.001) positiveDiffs++;
                        else if (diff < -0.001) negativeDiffs++;
                    }
                    if (positiveDiffs > negativeDiffs * 2) {
                        travelDirection = 'left_to_right';
                        directionConfidence = clamp(positiveDiffs / pelvisXCoords.length, 0.5, 0.9);
                    } else if (negativeDiffs > positiveDiffs * 2) {
                        travelDirection = 'right_to_left';
                        directionConfidence = clamp(negativeDiffs / pelvisXCoords.length, 0.5, 0.9);
                    }
                }
            }

            return { refLegLength, bodyScale, travelDirection, directionConfidence };
        }

        // Ground Plane Estimation (Stage 9)
        function estimateGroundPlane(frames) {
            const footYCandidates = [];
            frames.forEach(frame => {
                const lms = frame.smoothed_landmarks;
                if (!lms) return;
                const candidates = [27, 28, 29, 30, 31, 32];
                candidates.forEach(idx => {
                    const lm = lms[idx];
                    if (lm && lm.visibility > ANALYSIS_CONFIG.minVisibility) footYCandidates.push(lm.y);
                });
            });

            if (footYCandidates.length === 0) {
                return { ground_y_normalized: 0.85, confidence: 0.1, method: "default_fallback" };
            }

            const groundY = percentile(footYCandidates, 90);
            return {
                ground_y_normalized: groundY,
                confidence: clamp(1.0 - standardDeviation(footYCandidates), 0.5, 0.95),
                method: "rolling_foot_percentile"
            };
        }

        // Contact Estimation (Stage 10)
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
            
            let leftContactState = false;
            let rightContactState = false;
            let leftDebounce = 0;
            let rightDebounce = 0;
            
            return framesWithVelocities.map(frame => {
                const lms = frame.smoothed_landmarks;
                if (!lms) {
                    return {
                        ...frame,
                        contact: {
                            left: { is_contact: false, confidence: 0.0, evidence: {} },
                            right: { is_contact: false, confidence: 0.0, evidence: {} }
                        }
                    };
                }
                
                const lHeel = lms[29], lAnkle = lms[27], lToe = lms[31];
                const rHeel = lms[30], rAnkle = lms[28], rToe = lms[32];
                
                const leftLowestY = Math.max(lHeel?.y || 0, lAnkle?.y || 0, lToe?.y || 0);
                const leftDistToGround = groundY - leftLowestY;
                const leftNearGround = leftDistToGround <= tolerance;
                const leftSpeedLow = Math.abs(frame.leftFootSpeedY) < 0.5;
                const leftVisibilityOk = (lAnkle?.visibility || 0) > ANALYSIS_CONFIG.minVisibility;
                
                const leftEvidence = { near_ground: leftNearGround, vertical_speed_low: leftSpeedLow, visibility_ok: leftVisibilityOk };
                let leftIsContactCandidate = leftNearGround && (leftSpeedLow || leftLowestY > groundY - 0.02) && leftVisibilityOk;
                
                if (leftIsContactCandidate !== leftContactState) {
                    leftDebounce++;
                    if (leftDebounce >= ANALYSIS_CONFIG.phaseDebounceFrames) {
                        leftContactState = leftIsContactCandidate;
                        leftDebounce = 0;
                    }
                } else {
                    leftDebounce = 0;
                }
                const leftConfidence = leftVisibilityOk ? (leftNearGround ? 0.8 : 0.4) + (leftSpeedLow ? 0.15 : 0.0) : 0.0;
                
                const rightLowestY = Math.max(rHeel?.y || 0, rAnkle?.y || 0, rToe?.y || 0);
                const rightDistToGround = groundY - rightLowestY;
                const rightNearGround = rightDistToGround <= tolerance;
                const rightSpeedLow = Math.abs(frame.rightFootSpeedY) < 0.5;
                const rightVisibilityOk = (rAnkle?.visibility || 0) > ANALYSIS_CONFIG.minVisibility;
                
                const rightEvidence = { near_ground: rightNearGround, vertical_speed_low: rightSpeedLow, visibility_ok: rightVisibilityOk };
                let rightIsContactCandidate = rightNearGround && (rightSpeedLow || rightLowestY > groundY - 0.02) && rightVisibilityOk;
                
                if (rightIsContactCandidate !== rightContactState) {
                    rightDebounce++;
                    if (rightDebounce >= ANALYSIS_CONFIG.phaseDebounceFrames) {
                        rightContactState = rightIsContactCandidate;
                        rightDebounce = 0;
                    }
                } else {
                    rightDebounce = 0;
                }
                const rightConfidence = rightVisibilityOk ? (rightNearGround ? 0.8 : 0.4) + (rightSpeedLow ? 0.15 : 0.0) : 0.0;
                
                return {
                    ...frame,
                    contact: {
                        left: { is_contact: leftContactState, confidence: clamp(leftConfidence, 0, 1.0), evidence: leftEvidence },
                        right: { is_contact: rightContactState, confidence: clamp(rightConfidence, 0, 1.0), evidence: rightEvidence }
                    }
                };
            });
        }

        // Phase Estimation (Stage 11)
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
                    const lKnee = lms[25];
                    if (lKnee && pelvis && lAnkle) {
                        leftPhase = ((pelvis.x - lAnkle.x) * dirMult < 0) ? "estimated_pull" : "estimated_swing";
                    }
                }
                
                let rightPhase = "estimated_swing";
                if (frame.contact.right.is_contact) {
                    rightPhase = (rAnkle && pelvis && (pelvis.x - rAnkle.x) * dirMult > 0) ? "estimated_loading" : "estimated_push";
                } else {
                    const rKnee = lms[26];
                    if (rKnee && pelvis && rAnkle) {
                        rightPhase = ((pelvis.x - rAnkle.x) * dirMult < 0) ? "estimated_pull" : "estimated_swing";
                    }
                }
                
                return { ...frame, phase: { left: leftPhase, right: rightPhase } };
            });
        }

        // Gait Events & Step Building (Stage 11, 13)
        function detectGaitEvents(frames) {
            const events = [];
            let prevLeftContact = false;
            let prevRightContact = false;
            let lastLeftIcTime = 0;
            let lastLeftToTime = 0;
            let lastRightIcTime = 0;
            let lastRightToTime = 0;
            
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
                    } else {
                        steps.push({
                            step_index: stepCount++,
                            side: side,
                            initial_contact_ms: ic1.timestamp_ms,
                            status: "incomplete",
                            reason: "Missing toe-off or next initial contact"
                        });
                    }
                }
            };
            
            processSideSteps(leftIcs, leftTos, 'left');
            processSideSteps(rightIcs, rightTos, 'right');
            
            return steps.sort((a, b) => a.initial_contact_ms - b.initial_contact_ms);
        }

        // Summary & Indicators (Stage 14, 15, 16)
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

            if (summary.step_count < 2) {
                flags.push({
                    code: "insufficient_complete_steps",
                    severity: "warning",
                    value: summary.step_count,
                    threshold: 2,
                    confidence: 0.95,
                    message: "Jumlah langkah lengkap tidak mencukupi untuk analisis biomekanik yang stabil."
                });
            }
            
            return flags;
        }

        // Quality Report (Stage 21)
        function generateQualityReport(frames, steps) {
            const totalFrames = frames.length;
            if (totalFrames === 0) return { overall_score: 0 };
            
            const framesWithPerson = frames.filter(f => f.smoothed_landmarks).length;
            const presenceRatio = framesWithPerson / totalFrames;
            
            let sumVisibility = 0;
            let sumLowerBodyVisibility = 0;
            let worldCount = 0;
            
            frames.forEach(f => {
                const lms = f.smoothed_landmarks;
                if (!lms) return;
                
                sumVisibility += calculateVisibilityScore(lms, Array.from({ length: 33 }, (_, i) => i));
                sumLowerBodyVisibility += calculateVisibilityScore(lms, [23, 24, 25, 26, 27, 28, 29, 30, 31, 32]);
                if (f.smoothed_world_landmarks) worldCount++;
            });

            const meanVisibility = sumVisibility / framesWithPerson;
            const lowerBodyVisibility = sumLowerBodyVisibility / framesWithPerson;
            const worldLandmarkAvailability = worldCount / framesWithPerson;
            
            const deltas = [];
            for (let i = 1; i < frames.length; i++) {
                deltas.push(frames[i].timestamp_ms - frames[i - 1].timestamp_ms);
            }
            const stdDevDelta = standardDeviation(deltas) || 0;
            const timestampStability = clamp(1.0 - (stdDevDelta / 50), 0.0, 1.0);
            const completeStepRatio = steps.length > 0 ? steps.filter(s => s.status !== 'incomplete').length / steps.length : 0.0;
            const overallScore = (presenceRatio * 0.2) + (meanVisibility * 0.25) + (lowerBodyVisibility * 0.25) + (timestampStability * 0.15) + (completeStepRatio * 0.15);
            
            return {
                pose_presence_ratio: presenceRatio,
                mean_visibility: meanVisibility,
                lower_body_visibility: lowerBodyVisibility,
                world_landmark_availability: worldLandmarkAvailability,
                timestamp_stability: timestampStability,
                ground_estimation_confidence: 0.8,
                event_detection_confidence: completeStepRatio,
                complete_step_ratio: completeStepRatio,
                overall_score: overallScore
            };
        }

        // Orchestrator function (Stage 19)
        function analyzeCapturedTrial(rawFrames, runnerId, trialId) {
            console.log("[Biomechanics] Starting trial analysis...", rawFrames.length, "frames");
            
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
            
            const payload = {
                schema_version: 2,
                trial: {
                    trial_id: trialId,
                    runner_id: runnerId,
                    captured_at: new Date().toISOString(),
                    camera: {
                        width: state.videoWidth || 1280,
                        height: state.videoHeight || 720,
                        fps_reported: 30,
                        view_mode: state.viewMode || "contain"
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
            
            console.log("[Biomechanics] Analysis complete! Steps count:", steps.length);
            return payload;
        }

        // Real-Time dashboard update during recording (Stage 17)
        function updateRealTimeAnalysis(landmarks, timestamp) {
            if (!landmarks) return;
            
            state.analysisHistory.push({ timestamp_ms: timestamp, landmarks: landmarks });
            if (state.analysisHistory.length > 60) state.analysisHistory.shift();
            
            const smoothed = landmarks.map((lm, idx) => {
                let sumX = 0, sumY = 0, sumZ = 0, count = 0;
                for (let i = Math.max(0, state.analysisHistory.length - 3); i < state.analysisHistory.length; i++) {
                    const hLm = state.analysisHistory[i].landmarks[idx];
                    if (hLm && hLm.visibility > VISIBILITY_THRESHOLD) {
                        sumX += hLm.x; sumY += hLm.y; sumZ += hLm.z; count++;
                    }
                }
                return count > 0 ? { x: sumX / count, y: sumY / count, z: sumZ / count, visibility: lm.visibility } : lm;
            });
            
            const footYs = [];
            state.analysisHistory.forEach(h => {
                [27, 28, 29, 30, 31, 32].forEach(idx => {
                    const lm = h.landmarks[idx];
                    if (lm && lm.visibility > VISIBILITY_THRESHOLD) footYs.push(lm.y);
                });
            });
            const groundY = percentile(footYs, 90) || 0.85;
            
            const lHip = smoothed[23], rHip = smoothed[24];
            const lKnee = smoothed[25], rKnee = smoothed[26];
            const lAnkle = smoothed[27], rAnkle = smoothed[28];
            const hk = distance2D(lHip, lKnee) || 0.15;
            const ka = distance2D(lKnee, lAnkle) || 0.15;
            const refLegLength = hk + ka;
            
            const tolerance = 0.04 * refLegLength;
            const lLowest = Math.max(smoothed[29]?.y || 0, lAnkle?.y || 0, smoothed[31]?.y || 0);
            const rLowest = Math.max(smoothed[30]?.y || 0, rAnkle?.y || 0, smoothed[32]?.y || 0);
            
            const leftContact = (groundY - lLowest) <= tolerance && (lAnkle?.visibility || 0) > VISIBILITY_THRESHOLD;
            const rightContact = (groundY - rLowest) <= tolerance && (rAnkle?.visibility || 0) > VISIBILITY_THRESHOLD;
            
            const pelvis = midpoint(lHip, rHip);
            const travelDirection = state.travelDirection || 'left_to_right';
            const dirMult = travelDirection === 'right_to_left' ? -1 : 1;
            
            let leftPhase = "SWING";
            if (leftContact) leftPhase = (pelvis && lAnkle && (pelvis.x - lAnkle.x) * dirMult > 0) ? "LOADING" : "PUSH";
            
            let rightPhase = "SWING";
            if (rightContact) rightPhase = (pelvis && rAnkle && (pelvis.x - rAnkle.x) * dirMult > 0) ? "LOADING" : "PUSH";
            
            state.realTimeMetrics = { leftContact, rightContact, leftPhase, rightPhase, groundY };
        }

        // Rendering dashboard overlay (Stage 18)
        function drawDiagnosticDashboard(scale) {
            if (!state.realTimeMetrics) return;
            const metrics = state.realTimeMetrics;
            const x = canvas.width - 200 * scale;
            const y = 12 * scale;
            const width = 188 * scale;
            const height = 120 * scale;
            
            ctx.save();
            roundedRectPath(ctx, x, y, width, height, 10 * scale);
            ctx.fillStyle = 'rgba(2, 6, 23, 0.76)';
            ctx.fill();
            ctx.strokeStyle = 'rgba(204, 255, 0, 0.4)';
            ctx.lineWidth = 1.5 * scale;
            ctx.stroke();
            
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            
            ctx.fillStyle = '#ccff00';
            ctx.font = `900 ${11 * scale}px Inter, sans-serif`;
            ctx.fillText("ESTIMATED KINEMATICS", x + 10 * scale, y + 16 * scale);
            
            ctx.fillStyle = '#E2E8F0';
            ctx.font = `600 ${10 * scale}px Inter, sans-serif`;
            ctx.fillText("L-LEG:", x + 10 * scale, y + 42 * scale);
            ctx.fillStyle = metrics.leftContact ? '#A3E635' : '#94A3B8';
            ctx.font = `900 ${11 * scale}px Inter, sans-serif`;
            ctx.fillText(`${metrics.leftPhase} ${metrics.leftContact ? '●' : '○'}`, x + 60 * scale, y + 42 * scale);
            
            ctx.fillStyle = '#E2E8F0';
            ctx.font = `600 ${10 * scale}px Inter, sans-serif`;
            ctx.fillText("R-LEG:", x + 10 * scale, y + 66 * scale);
            ctx.fillStyle = metrics.rightContact ? '#60A5FA' : '#94A3B8';
            ctx.font = `900 ${11 * scale}px Inter, sans-serif`;
            ctx.fillText(`${metrics.rightPhase} ${metrics.rightContact ? '●' : '○'}`, x + 60 * scale, y + 66 * scale);
            
            if (metrics.groundY) {
                ctx.setLineDash([4 * scale, 4 * scale]);
                ctx.strokeStyle = 'rgba(239, 68, 68, 0.4)';
                ctx.lineWidth = 1.5 * scale;
                ctx.beginPath();
                ctx.moveTo(0, metrics.groundY * canvas.height);
                ctx.lineTo(canvas.width, metrics.groundY * canvas.height);
                ctx.stroke();
                
                ctx.fillStyle = 'rgba(239, 68, 68, 0.7)';
                ctx.font = `700 ${9 * scale}px Inter, sans-serif`;
                ctx.fillText("ESTIMATED GROUND", 12 * scale, metrics.groundY * canvas.height - 6 * scale);
            }
            
            ctx.fillStyle = '#94A3B8';
            ctx.font = `500 ${9 * scale}px Inter, sans-serif`;
            ctx.fillText(`MODE: ${state.captureMode.toUpperCase()} | DIR: ${state.travelDirection || 'WAITING'}`, x + 10 * scale, y + 96 * scale);
            ctx.restore();
        }

        // DOM Elements
        const video = document.getElementById('camera-feed');
        const canvas = document.getElementById('canvas-overlay');
        const ctx = canvas.getContext('2d');
        const statusBadge = document.getElementById('capture-status-badge');
        const queueContainer = document.getElementById('runner-queue-list');
        const activeRunnerCard = document.getElementById('active-runner-card');
        
        // Mode control handler
        function setCaptureMode(mode) {
            state.captureMode = mode;
            const autoBtn = document.getElementById('mode-auto-btn');
            const manualBtn = document.getElementById('mode-manual-btn');
            const desc = document.getElementById('mode-description');
            
            if (mode === 'auto') {
                autoBtn.className = "px-2 py-1.5 rounded text-[10px] font-black uppercase tracking-wider text-center border bg-neon text-black border-neon transition-all focus:outline-none";
                manualBtn.className = "px-2 py-1.5 rounded text-[10px] font-black uppercase tracking-wider text-center border bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700 hover:text-white transition-all focus:outline-none";
                desc.innerText = "Auto-starts ketika pelvis memasuki zona aktif. Auto-stops ketika keluar.";
            } else {
                autoBtn.className = "px-2 py-1.5 rounded text-[10px] font-black uppercase tracking-wider text-center border bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700 hover:text-white transition-all focus:outline-none";
                manualBtn.className = "px-2 py-1.5 rounded text-[10px] font-black uppercase tracking-wider text-center border bg-neon text-black border-neon transition-all focus:outline-none";
                desc.innerText = "Pelatih harus menekan tombol merah untuk MULAI merekam dan menekan kotak putih untuk BERHENTI merekam.";
            }
            
            setStatus(state.status, state.status === 'RECORDING' ? 'RECORDING' : 'Waiting for Runner');
        }

        // 1. Initialize UI
        function renderQueue() {
            // Find first pending runner if user hasn't selected one manually
            if (state.userSelectedActiveIndex === null || state.userSelectedActiveIndex === -1) {
                state.activeRunnerIndex = state.runners.findIndex(r => r.status === 'pending');
            } else {
                state.activeRunnerIndex = state.userSelectedActiveIndex;
            }
            
            if (state.activeRunnerIndex === -1 && state.runners.length > 0) {
                // All done
                activeRunnerCard.innerHTML = `<div class="text-center py-4 text-slate-300 font-bold"><i class="fas fa-check-circle text-green-500 text-2xl mb-2 block"></i>All runners captured!</div>`;
            } else if (state.activeRunnerIndex >= 0) {
                const runner = state.runners[state.activeRunnerIndex];
                let genderBadge = '';
                if (runner.gender === 'male') {
                    genderBadge = '<span class="ml-2 bg-blue-900/50 text-[9px] text-blue-400 border border-blue-800 px-1 py-0.5 rounded uppercase font-bold tracking-wider inline-flex items-center gap-1"><i class="fas fa-mars text-[8px]"></i> M</span>';
                } else if (runner.gender === 'female') {
                    genderBadge = '<span class="ml-2 bg-pink-900/50 text-[9px] text-pink-400 border border-pink-800 px-1 py-0.5 rounded uppercase font-bold tracking-wider inline-flex items-center gap-1"><i class="fas fa-venus text-[8px]"></i> F</span>';
                }

                activeRunnerCard.innerHTML = `
                    <div class="flex items-center gap-3">
                        <img src="${runner.avatar}" class="w-12 h-12 rounded-full border border-slate-700 bg-slate-800 object-cover">
                        <div class="flex-1 truncate">
                            <div class="font-bold text-white truncate flex items-center">${runner.name} ${genderBadge}</div>
                            <div class="text-xs text-slate-400">@${runner.username} &bull; #${runner.sequence_no}</div>
                        </div>
                    </div>
                `;
            }

            // Render rest of queue
            queueContainer.innerHTML = state.runners.map((r, i) => {
                if (i === state.activeRunnerIndex) return ''; // Skip active
                
                let statusIcon = '';
                if (r.status === 'pending') statusIcon = '<i class="fas fa-clock text-slate-600"></i>';
                else if (r.status === 'captured') statusIcon = '<i class="fas fa-video text-blue-400"></i>';
                else if (r.status === 'published') statusIcon = '<i class="fas fa-check-circle text-green-500"></i>';

                let genderIcon = '';
                if (r.gender === 'male') genderIcon = '<i class="fas fa-mars text-blue-400/70 ml-1.5 text-[10px]"></i>';
                else if (r.gender === 'female') genderIcon = '<i class="fas fa-venus text-pink-400/70 ml-1.5 text-[10px]"></i>';
                
                return `
                    <div onclick="selectActiveRunner(${i})" class="flex items-center gap-3 p-2 rounded hover:bg-slate-850 cursor-pointer border border-transparent hover:border-slate-800 transition-colors ${i < state.activeRunnerIndex ? 'opacity-50' : ''}">
                        <div class="text-xs text-slate-500 font-mono w-4">${r.sequence_no}</div>
                        <img src="${r.avatar}" class="w-8 h-8 rounded-full border border-slate-700 bg-slate-800 object-cover">
                        <div class="flex-1 truncate">
                            <div class="text-sm font-semibold text-slate-300 truncate flex items-center">${r.name} ${genderIcon}</div>
                        </div>
                        <div class="w-4 text-center">${statusIcon}</div>
                    </div>
                `;
            }).join('');
        }

        function selectActiveRunner(index) {
            if (state.status === 'RECORDING') {
                alert("Cannot change runner while recording is in progress!");
                return;
            }
            state.userSelectedActiveIndex = index;
            renderQueue();
            setStatus('IDLE', 'Waiting for Runner');
        }

        // View Mode Handler
        function setViewMode(mode) {
            const feed = document.getElementById('camera-feed');
            const overlay = document.getElementById('canvas-overlay');
            if (feed && overlay) {
                feed.style.objectFit = mode;
                overlay.style.objectFit = mode;
            }
        }

        // 2. Initialize Camera
        async function initCameraDevices() {
            if (!navigator.mediaDevices) {
                const errMsg = "Akses kamera ditolak karena koneksi tidak aman (Secure Context Required).\n\n" +
                               "Silakan lakukan salah satu langkah berikut:\n" +
                               "1. Akses aplikasi menggunakan http://localhost (misal: jalankan 'php artisan serve' lalu buka http://localhost:8000)\n" +
                               "2. Aktifkan SSL/HTTPS di Laragon Anda (Menu Laragon -> Apache -> SSL -> Enabled)\n" +
                               "3. Buka Chrome Flags 'chrome://flags/#unsafely-treat-insecure-origin-as-secure', masukkan http://ruanglari.test (atau domain lokal Anda), aktifkan (Enable), lalu restart browser.";
                console.error(errMsg);
                alert(errMsg);
                return;
            }

            try {
                await navigator.mediaDevices.getUserMedia({ video: true }); // Request permission first
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(d => d.kind === 'videoinput');
                
                const select = document.getElementById('camera-selector');
                select.innerHTML = '';
                videoDevices.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.deviceId;
                    option.text = d.label || `Camera ${select.length + 1}`;
                    select.appendChild(option);
                });
            } catch (err) {
                console.error("Camera access denied", err);
                alert("Camera permission is required.");
            }
        }

        async function startCamera() {
            if (!navigator.mediaDevices) {
                return;
            }

            if (state.stream) {
                state.stream.getTracks().forEach(track => track.stop());
            }

            const deviceId = document.getElementById('camera-selector').value;
            const res = document.getElementById('resolution-selector').value.split('x');
            const fps = parseInt(document.getElementById('fps-selector').value);

            const constraints = {
                video: {
                    deviceId: deviceId ? { exact: deviceId } : undefined,
                    width: { ideal: parseInt(res[0]) },
                    height: { ideal: parseInt(res[1]) },
                    frameRate: { ideal: fps }
                }
            };

            try {
                state.stream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = state.stream;
                
                // Wait for video metadata to set canvas size
                video.onloadedmetadata = () => {
                    state.videoWidth = video.videoWidth;
                    state.videoHeight = video.videoHeight;
                    canvas.width = state.videoWidth;
                    canvas.height = state.videoHeight;
                    
                    const track = state.stream.getVideoTracks()[0];
                    const settings = track.getSettings();
                    document.getElementById('actual-resolution').innerText = `${settings.width}x${settings.height}`;
                    document.getElementById('actual-fps').innerText = Math.round(settings.frameRate);
                };
            } catch (err) {
                console.error("Failed to start camera stream", err);
                alert("Could not start camera with requested settings.");
            }
        }

        document.getElementById('apply-camera-btn').addEventListener('click', startCamera);

        // 3. Initialize MediaPipe
        async function initMediaPipe() {
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
            
            // Start processing loop
            requestAnimationFrame(predictWebcam);
        }

        // 4. Processing Loop
        let frameCount = 0;
        let lastFpsTime = performance.now();

        async function predictWebcam() {
            if (!state.poseLandmarker || !video.videoWidth) {
                requestAnimationFrame(predictWebcam);
                return;
            }

            const startTimeMs = performance.now();
            
            // Check if there is a new frame
            if (state.lastVideoTime !== video.currentTime) {
                state.lastVideoTime = video.currentTime;
                
                // Run inference
                const result = state.poseLandmarker.detectForVideo(video, startTimeMs);
                
                // Real-time biomechanics update
                if (result.landmarks && result.landmarks.length > 0) {
                    updateRealTimeAnalysis(result.landmarks[0], startTimeMs);
                }

                // Calculate Inference FPS
                frameCount++;
                if (startTimeMs - lastFpsTime >= 1000) {
                    document.getElementById('inference-fps').innerText = frameCount;
                    frameCount = 0;
                    lastFpsTime = startTimeMs;
                }

                drawOverlay(result);
                processLogic(result, startTimeMs);
            }

            requestAnimationFrame(predictWebcam);
        }

        // 5. Drawing & Logic
        const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
        const toDeg = (rad) => rad * 180 / Math.PI;
        const VISIBILITY_THRESHOLD = 0.5;

        // Warna dibedakan berdasarkan area tubuh agar form lebih cepat dibaca.
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

        function drawPoseLegend(scale) {
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
            const height = padding * 2 + rowHeight * items.length;
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
            ctx.restore();
        }

        function drawOverlay(result) {
            ctx.save();
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (result.landmarks && result.landmarks.length > 0) {
                const landmarks = result.landmarks[0];
                const scale = clamp(Math.min(canvas.width, canvas.height) / 720, 0.85, 1.6);
                const visibleLandmarks = landmarks.filter(l => l.visibility >= VISIBILITY_THRESHOLD);
                const averageVisibility = visibleLandmarks.length
                    ? visibleLandmarks.reduce((sum, l) => sum + l.visibility, 0) / visibleLandmarks.length
                    : 0;

                document.getElementById('pose-visibility').innerText = `Detected ${Math.round(averageVisibility * 100)}%`;

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

                // 4) Busur dan label sudut berkontras tinggi.
                for (const angleConfig of ANGLES_TO_DRAW) {
                    drawAngleAnnotation(angleConfig, landmarks, scale);
                }

                // 5) Legenda warna untuk pembacaan cepat.
                drawPoseLegend(scale);

                // 6) Real-time diagnostic dashboard.
                drawDiagnosticDashboard(scale);
            } else {
                document.getElementById('pose-visibility').innerText = 'Lost';
            }
            ctx.restore();
        }

        function processLogic(result, timestamp) {
            if (state.activeRunnerIndex === -1) {
                setStatus('IDLE', 'Queue Empty');
                return;
            }

            if (state.captureMode === 'auto') {
                if (!result.landmarks || result.landmarks.length === 0) {
                    if (state.status === 'READY') {
                        setStatus('IDLE', 'Waiting for Runner');
                    }
                    return;
                }

                const pelvisX = (result.landmarks[0][23].x + result.landmarks[0][24].x) / 2;
                
                const inZone = (pelvisX >= state.zoneMinX && pelvisX <= state.zoneMaxX);
                const inLeftBuffer = (pelvisX < state.zoneMinX);
                const inRightBuffer = (pelvisX > state.zoneMaxX);

                if (state.status === 'IDLE' || state.status === 'READY') {
                    if (inLeftBuffer || inRightBuffer) {
                        setStatus('READY', 'Runner in position. Waiting for entry.');
                    } else if (inZone) {
                        startRecording(timestamp);
                    }
                } else if (state.status === 'RECORDING') {
                    if (result.landmarks && result.landmarks.length > 0) {
                        const copyLandmarks = result.landmarks[0].map(l => ({
                            x: l.x, y: l.y, z: l.z, visibility: l.visibility, presence: l.presence ?? 0
                        }));
                        const copyWorldLandmarks = (result.worldLandmarks && result.worldLandmarks.length > 0)
                            ? result.worldLandmarks[0].map(l => ({
                                x: l.x, y: l.y, z: l.z, visibility: l.visibility, presence: l.presence ?? 0
                              }))
                            : null;

                        state.captureFrames.push({
                            frame_index: state.captureFrames.length,
                            timestamp_ms: timestamp,
                            video_time_ms: video.currentTime * 1000,
                            delta_ms: state.captureFrames.length > 0 ? (timestamp - state.captureFrames[state.captureFrames.length - 1].timestamp_ms) : 33.3,
                            landmarks: copyLandmarks,
                            world_landmarks: copyWorldLandmarks
                        });
                    }

                    if (!inZone) {
                        stopRecordingAndUpload();
                    }
                }
            } else {
                // Manual Mode logic: landmarks are collected only while status is RECORDING
                if (state.status === 'RECORDING') {
                    if (result.landmarks && result.landmarks.length > 0) {
                        const copyLandmarks = result.landmarks[0].map(l => ({
                            x: l.x, y: l.y, z: l.z, visibility: l.visibility, presence: l.presence ?? 0
                        }));
                        const copyWorldLandmarks = (result.worldLandmarks && result.worldLandmarks.length > 0)
                            ? result.worldLandmarks[0].map(l => ({
                                x: l.x, y: l.y, z: l.z, visibility: l.visibility, presence: l.presence ?? 0
                              }))
                            : null;

                        state.captureFrames.push({
                            frame_index: state.captureFrames.length,
                            timestamp_ms: timestamp,
                            video_time_ms: video.currentTime * 1000,
                            delta_ms: state.captureFrames.length > 0 ? (timestamp - state.captureFrames[state.captureFrames.length - 1].timestamp_ms) : 33.3,
                            landmarks: copyLandmarks,
                            world_landmarks: copyWorldLandmarks
                        });
                    }
                }
            }
        }

        function setStatus(status, text) {
            state.status = status;
            
            if (state.captureMode === 'manual' && (status === 'IDLE' || status === 'READY')) {
                statusBadge.innerText = 'Manual Mode - Click to Start';
            } else {
                statusBadge.innerText = text;
            }
            
            const manualBtn = document.getElementById('manual-record-btn');

            if (status === 'RECORDING') {
                statusBadge.className = 'bg-red-500 text-white px-4 py-2 rounded-full font-bold uppercase tracking-wider text-sm transition-all duration-300 pulse-red';
                document.getElementById('video-container').classList.add('zone-active');
                manualBtn.classList.remove('hidden');
                manualBtn.innerHTML = `<div class="w-6 h-6 rounded-sm bg-white group-hover:scale-90 transition-transform"></div>`;
            } else if (status === 'READY' || status === 'IDLE') {
                statusBadge.className = (status === 'READY' && state.captureMode === 'auto') ? 'bg-[#ccff00] text-black px-4 py-2 rounded-full font-bold uppercase tracking-wider text-sm transition-all duration-300' : 'bg-slate-900/80 backdrop-blur border border-slate-700 text-slate-300 px-4 py-2 rounded-full font-bold uppercase tracking-wider text-sm transition-all duration-300';
                document.getElementById('video-container').classList.remove('zone-active');
                
                // Show manual start button if active runner exists
                if (state.activeRunnerIndex >= 0) {
                    manualBtn.classList.remove('hidden');
                    manualBtn.innerHTML = `<div class="w-6 h-6 rounded-full bg-red-500 group-hover:scale-90 transition-transform pulse-red"></div>`;
                } else {
                    manualBtn.classList.add('hidden');
                }
            }
        }

        // Manual controls
        document.getElementById('manual-record-btn').addEventListener('click', () => {
            if (state.status === 'RECORDING') {
                stopRecordingAndUpload();
            } else if (state.activeRunnerIndex >= 0) {
                startRecording(performance.now());
            }
        });

        function startRecording(ts) {
            state.recordingStartTime = ts;
            state.captureFrames = [];
            state.videoChunks = [];

            // Start video recording via MediaRecorder
            if (state.stream && window.MediaRecorder) {
                try {
                    const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp9') 
                        ? 'video/webm;codecs=vp9' 
                        : 'video/webm';
                    state.mediaRecorder = new MediaRecorder(state.stream, { mimeType });
                    state.mediaRecorder.ondataavailable = (e) => {
                        if (e.data && e.data.size > 0) state.videoChunks.push(e.data);
                    };
                    state.mediaRecorder.start(500); // collect chunk every 500ms
                } catch (err) {
                    console.warn('[Video] MediaRecorder failed to start:', err);
                    state.mediaRecorder = null;
                }
            }

            setStatus('RECORDING', 'RECORDING');
        }

        function validateActivity(frames) {
            const totalFrames = frames.length;
            if (totalFrames === 0) {
                return { isValid: false, reason: "Tidak ada frame data pose yang direkam." };
            }

            let framesWithPerson = 0;
            let minX = 1.0;
            let maxX = 0.0;
            let maxStrideWidth = 0.0;

            frames.forEach(frame => {
                const landmarks = frame.landmarks;
                if (landmarks && landmarks.length > 0) {
                    const visibleLandmarks = landmarks.filter(l => l.visibility >= 0.5);
                    if (visibleLandmarks.length > 10) {
                        framesWithPerson++;
                        const pelvisX = (landmarks[23].x + landmarks[24].x) / 2;
                        if (pelvisX < minX) minX = pelvisX;
                        if (pelvisX > maxX) maxX = pelvisX;
                    }
                    
                    const lHeel = landmarks[29];
                    const rHeel = landmarks[30];
                    if (lHeel && rHeel && lHeel.visibility > 0.5 && rHeel.visibility > 0.5) {
                        const stride = Math.abs(lHeel.x - rHeel.x);
                        if (stride > maxStrideWidth) {
                            maxStrideWidth = stride;
                        }
                    }
                }
            });

            const presenceRatio = framesWithPerson / totalFrames;
            const horizontalDisplacement = maxX - minX;

            // 1. Validasi keberadaan orang (minimal 50% frame terdeteksi orang)
            if (presenceRatio < 0.5) {
                return { 
                    isValid: false, 
                    reason: `Orang tidak terdeteksi dengan jelas (Kualitas deteksi: ${Math.round(presenceRatio * 100)}%). Pastikan seluruh tubuh pelari masuk dalam frame kamera.` 
                };
            }

            // 2. Validasi pergerakan lari/jalan (horizontal displacement > 12% lebar layar ATAU stride range lari)
            const isStationary = (horizontalDisplacement < 0.12) && (maxStrideWidth < 0.08);
            if (isStationary) {
                return {
                    isValid: false,
                    reason: `Pelari terdeteksi diam / tidak melintas (Displacement: ${Math.round(horizontalDisplacement * 100)}%, Waktu Kontak/Langkah: ${Math.round(maxStrideWidth * 100)}%).` 
                };
            }

            return {
                isValid: true,
                stats: {
                    presenceRatio,
                    horizontalDisplacement,
                    maxStrideWidth
                }
            };
        }

        function stopRecordingAndUpload() {
            setStatus('PROCESSING', 'Processing Trial...');
            const framesCount = state.captureFrames.length;

            // Stop MediaRecorder first (async — wait for it before upload)
            const getVideoBlob = () => new Promise((resolve) => {
                if (!state.mediaRecorder || state.mediaRecorder.state === 'inactive') {
                    resolve(null);
                    return;
                }
                state.mediaRecorder.onstop = () => {
                    resolve(state.videoChunks.length > 0
                        ? new Blob(state.videoChunks, { type: 'video/webm' })
                        : null);
                };
                state.mediaRecorder.stop();
            });

            if (framesCount < 10) {
                alert("Trial too short. Discarding.");
                setStatus('IDLE', 'Waiting for Runner');
                return;
            }

            const trialId = crypto.randomUUID();
            const runner = state.runners[state.activeRunnerIndex];

            // Collect video then save everything to outbox
            getVideoBlob().then(videoBlob => {
                // Jalankan smart filtering & validasi aktivitas
                const validation = validateActivity(state.captureFrames);
                if (!validation.isValid) {
                    alert(`Perekaman dibatalkan & dibuang:\n${validation.reason}`);
                    setStatus('IDLE', 'Waiting for Runner');
                    return;
                }

                const stats = validation.stats;
                console.log("[Validation OK] Stats:", stats);

                // Run Full Biomechanics V2 Engine
                let poseData;
                try {
                    const payload = analyzeCapturedTrial(state.captureFrames, runner.id, trialId);
                    poseData = JSON.stringify(payload);
                } catch (err) {
                    console.error("[Biomechanics] Engine error, using fallback format", err);
                    poseData = JSON.stringify({ 
                        schema_version: 1,
                        landmarks: state.captureFrames,
                        metadata: {
                            displacement: stats.horizontalDisplacement,
                            stride_width: stats.maxStrideWidth
                        }
                    });
                }
                
                crypto.subtle.digest('SHA-256', new TextEncoder().encode(poseData)).then(hashBuffer => {
                    const hashArray = Array.from(new Uint8Array(hashBuffer));
                    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                    
                    db.outbox.add({
                        trialId: trialId,
                        runnerId: runner.id,
                        data: poseData,
                        hash: hashHex,
                        videoBlob: videoBlob, // may be null if recording failed
                        status: 'pending',
                        timestamp: Date.now()
                    }).then(() => {
                        // Try to upload immediately
                        processOutbox();
                        
                        // Advance queue
                        runner.status = 'captured';
                        
                        // Show success UX Toast
                        showToast(`Trial saved for ${runner.name}!`);

                        // Clear custom selection in auto mode to load next pending
                        if (state.captureMode === 'auto') {
                            state.userSelectedActiveIndex = null;
                        }
                        
                        renderQueue();
                        setStatus('IDLE', 'Waiting for Runner');
                    });
                });
            });
        }

        // Toast Helper
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

        // 6. Resilient Upload with Dexie
        const db = new Dexie("RuangLariCaptureDB");
        db.version(1).stores({
            outbox: '++id, trialId, runnerId, status, timestamp'
        });

        async function processOutbox() {
            const pending = await db.outbox.where('status').equals('pending').toArray();
            
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
                            camera_device_label: 'Webcam',
                            camera_width: state.videoWidth,
                            camera_height: state.videoHeight,
                            camera_fps: 30,
                            inference_fps: 30,
                            pose_model: 'pose_landmarker'
                        })
                    });

                    if (!storeRes.ok) {
                        const errBody = await storeRes.text();
                        throw new Error(`HTTP ${storeRes.status}: ${errBody.substring(0, 200)}`);
                    }
                    console.log(`[Upload] Trial record created OK`);

                    // 2. Upload Artifact — use File so server sees filename.json
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
                    console.log(`[Upload] Artifact uploaded OK`);

                    // 3. Upload Video Clip (if recorded)
                    if (item.videoBlob && item.videoBlob.size > 0) {
                        console.log(`[Upload] Uploading video clip (${(item.videoBlob.size / 1024 / 1024).toFixed(1)} MB)...`);
                        const videoBuffer = await item.videoBlob.arrayBuffer();
                        const videoHashBuffer = await crypto.subtle.digest('SHA-256', videoBuffer);
                        const videoHashHex = Array.from(new Uint8Array(videoHashBuffer))
                            .map(b => b.toString(16).padStart(2, '0')).join('');

                        const videoFile = new File([item.videoBlob], 'video_clip.webm', { type: 'video/webm' });
                        const videoFormData = new FormData();
                        videoFormData.append('type', 'video_clip');
                        videoFormData.append('file', videoFile);
                        videoFormData.append('sha256', videoHashHex);

                        const videoUploadRes = await fetch(`{{ url('/admin/running-analysis/trials') }}/${item.trialId}/artifacts`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: videoFormData
                        });

                        if (videoUploadRes.ok) {
                            console.log(`[Upload] Video clip uploaded OK`);
                        } else {
                            const errBody = await videoUploadRes.text();
                            console.warn(`[Upload] Video upload failed (non-fatal): ${errBody.substring(0, 100)}`);
                        }
                    }

                    // 4. Finalize
                    const finalizeRes = await fetch(`{{ url('/admin/running-analysis/trials') }}/${item.trialId}/finalize`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (!finalizeRes.ok) {
                        const errBody = await finalizeRes.text();
                        throw new Error(`Finalize HTTP ${finalizeRes.status}: ${errBody.substring(0, 200)}`);
                    }

                    // 4. Mark success locally
                    await db.outbox.update(item.id, { status: 'uploaded' });
                    
                    console.log(`[Upload] Trial ${item.trialId} complete!`);

                } catch (err) {
                    console.error(`[Upload] FAILED:`, err.message);
                    showToast(`Upload error: ${err.message.substring(0, 80)}`, true);
                    // Will be retried on next interval
                }
            }
        }

        // Retry every 30 seconds
        setInterval(processOutbox, 30000);

        // Expose to global scope for HTML inline event handlers since we are in a module
        window.setCaptureMode = setCaptureMode;
        window.setViewMode = setViewMode;
        window.selectActiveRunner = selectActiveRunner;

        // Boot
        async function boot() {
            renderQueue();
            await initCameraDevices();
            await startCamera();
            await initMediaPipe();
        }

        boot();

    </script>
</body>
</html>
