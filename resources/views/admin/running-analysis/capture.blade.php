<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capture - {{ $session->name }} - Ruang Lari</title>
    
    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- MediaPipe Vision & Dexie -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision/vision_bundle.js" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/dexie@latest/dist/dexie.js"></script>

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
                'status' => $r->pivot->status,
                'sequence_no' => $r->pivot->sequence_no
            ])->values()) !!}
        };
    </script>

    <!-- App Logic -->
    <script>
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
            
            // Zone config
            zoneMinX: 0.15, // 15% from left
            zoneMaxX: 0.85, // 15% from right
        };

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
                activeRunnerCard.innerHTML = `
                    <div class="flex items-center gap-3">
                        <img src="${runner.avatar}" class="w-12 h-12 rounded-full border border-slate-700 bg-slate-800 object-cover">
                        <div class="flex-1 truncate">
                            <div class="font-bold text-white truncate">${runner.name}</div>
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
                
                return `
                    <div onclick="selectActiveRunner(${i})" class="flex items-center gap-3 p-2 rounded hover:bg-slate-850 cursor-pointer border border-transparent hover:border-slate-800 transition-colors ${i < state.activeRunnerIndex ? 'opacity-50' : ''}">
                        <div class="text-xs text-slate-500 font-mono w-4">${r.sequence_no}</div>
                        <img src="${r.avatar}" class="w-8 h-8 rounded-full border border-slate-700 bg-slate-800 object-cover">
                        <div class="flex-1 truncate">
                            <div class="text-sm font-semibold text-slate-300 truncate">${r.name}</div>
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
                "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/wasm"
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
            if (lastVideoTime !== video.currentTime) {
                lastVideoTime = video.currentTime;
                
                // Run inference
                const result = state.poseLandmarker.detectForVideo(video, startTimeMs);
                
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
        function drawOverlay(result) {
            ctx.save();
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            if (result.landmarks && result.landmarks.length > 0) {
                const landmarks = result.landmarks[0];
                document.getElementById('pose-visibility').innerText = "Detected";
                
                // Draw skeleton (simplified for now)
                ctx.fillStyle = "#ccff00";
                ctx.strokeStyle = "#ccff00";
                ctx.lineWidth = 2;

                for (const landmark of landmarks) {
                    if (landmark.visibility > 0.5) {
                        ctx.beginPath();
                        ctx.arc(landmark.x * canvas.width, landmark.y * canvas.height, 4, 0, 2 * Math.PI);
                        ctx.fill();
                    }
                }
                
                // Draw connecting lines (example: shoulders, hips)
                const connections = [
                    [11, 12], // shoulders
                    [23, 24], // hips
                    [11, 23], [12, 24], // torso
                    [23, 25], [25, 27], // left leg
                    [24, 26], [26, 28]  // right leg
                ];
                
                ctx.beginPath();
                for (const [start, end] of connections) {
                    const s = landmarks[start];
                    const e = landmarks[end];
                    if (s && e && s.visibility > 0.5 && e.visibility > 0.5) {
                        ctx.moveTo(s.x * canvas.width, s.y * canvas.height);
                        ctx.lineTo(e.x * canvas.width, e.y * canvas.height);
                    }
                }
                ctx.stroke();

            } else {
                document.getElementById('pose-visibility').innerText = "Lost";
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
                    state.captureFrames.push({
                        ts: timestamp,
                        landmarks: result.landmarks[0]
                    });

                    if (!inZone) {
                        stopRecordingAndUpload();
                    }
                }
            } else {
                // Manual Mode logic: landmarks are collected only while status is RECORDING
                if (state.status === 'RECORDING') {
                    if (result.landmarks && result.landmarks.length > 0) {
                        state.captureFrames.push({
                            ts: timestamp,
                            landmarks: result.landmarks[0]
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
            setStatus('RECORDING', 'RECORDING');
        }

        function stopRecordingAndUpload() {
            setStatus('PROCESSING', 'Processing Trial...');
            const framesCount = state.captureFrames.length;
            
            if (framesCount < 10) {
                alert("Trial too short. Discarding.");
                setStatus('IDLE', 'Waiting for Runner');
                return;
            }

            const trialId = crypto.randomUUID();
            const runner = state.runners[state.activeRunnerIndex];

            // 1. Save to local IndexedDB (Dexie)
            const poseData = JSON.stringify({ landmarks: state.captureFrames });
            
            // Calculate SHA-256 for the data
            crypto.subtle.digest('SHA-256', new TextEncoder().encode(poseData)).then(hashBuffer => {
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                
                db.outbox.add({
                    trialId: trialId,
                    runnerId: runner.id,
                    data: poseData,
                    hash: hashHex,
                    status: 'pending',
                    timestamp: Date.now()
                }).then(() => {
                    // Try to upload immediately
                    processOutbox();
                    
                    // Advance queue
                    runner.status = 'captured';
                    
                    // Clear custom selection in auto mode to load next pending
                    if (state.captureMode === 'auto') {
                        state.userSelectedActiveIndex = null;
                    }
                    
                    renderQueue();
                    setStatus('IDLE', 'Waiting for Next Runner');
                });
            });
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

                    if (!storeRes.ok) throw new Error("Failed to create trial");

                    // 2. Upload Artifact
                    const formData = new FormData();
                    formData.append('type', 'pose_landmarks');
                    formData.append('file', new Blob([item.data], { type: 'application/json' }));
                    formData.append('sha256', item.hash);

                    const uploadRes = await fetch(`{{ url('/admin/running-analysis/trials') }}/${item.trialId}/artifacts`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    if (!uploadRes.ok) throw new Error("Failed to upload artifact");

                    // 3. Finalize
                    const finalizeRes = await fetch(`{{ url('/admin/running-analysis/trials') }}/${item.trialId}/finalize`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (!finalizeRes.ok) throw new Error("Failed to finalize trial");

                    // 4. Mark success locally
                    await db.outbox.update(item.id, { status: 'uploaded' });
                    
                    console.log(`Successfully uploaded trial ${item.trialId}`);

                } catch (err) {
                    console.error("Upload failed for item", item.id, err);
                    // Will be retried on next page load or interval
                }
            }
        }

        // Retry every 30 seconds
        setInterval(processOutbox, 30000);

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
