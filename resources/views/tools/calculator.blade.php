@extends('layouts.pacerhub')

@section('title', 'Ruang Lari Tools - Calculator')

@push('styles')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        #rl-calculator {
            --primary: #ccff00;
            --primary-dark: #a3cc00;
            --secondary: #3b82f6;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --card-bg: rgba(30,41,59,.72);
            --panel-bg: rgba(2,6,23,.42);
            --border: rgba(148,163,184,.18);
            --text: rgba(226,232,240,.92);
            --muted: rgba(148,163,184,.88);
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        #rl-calculator * { box-sizing: border-box; }

        #rl-calculator .rlc-wrap {
            max-width: 100%;
            margin: 0 auto;
            padding: 1.25rem;
        }

        #rl-calculator .rlc-header { text-align: center; margin-bottom: 1.5rem; }
        #rl-calculator .rlc-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, rgba(204,255,0,.95) 0%, rgba(6,182,212,.95) 50%, rgba(59,130,246,.95) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: .25rem;
            line-height: 1.1;
        }
        #rl-calculator .rlc-header p { margin: 0; color: var(--muted); font-weight: 600; }

        #rl-calculator .rlc-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 0 0 1px rgba(2,6,23,.45) inset;
        }

        #rl-calculator .global-controls { display: flex; justify-content: center; margin-bottom: 1rem; }
        #rl-calculator .unit-selector { padding: 1rem; }
        #rl-calculator .unit-selector label { display: block; margin-bottom: .5rem; font-weight: 800; color: var(--text); }
        #rl-calculator .unit-selector select {
            width: 100%;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text);
            border: 1px solid rgba(148,163,184,.24);
            border-radius: 12px;
            padding: .75rem 1rem;
            background: var(--panel-bg);
            cursor: pointer;
            outline: none;
        }
        #rl-calculator .unit-selector select:focus { border-color: rgba(204,255,0,.6); box-shadow: 0 0 0 3px rgba(204,255,0,.14); }

        #rl-calculator .tab-navigation {
            display: flex;
            gap: .5rem;
            overflow-x: auto;
            padding: .25rem;
            padding-bottom: .75rem; /* Space for scrollbar/shadow */
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
        }
        #rl-calculator .tab-navigation::-webkit-scrollbar { display: none; }
        #rl-calculator .tab-btn {
            scroll-snap-align: start;
            flex: 0 0 auto;
            padding: .6rem 1rem;
            border: 1px solid #334155; /* slate-700 */
            border-radius: 0.75rem; /* rounded-xl */
            background: #1e293b; /* slate-800 */
            color: #94a3b8; /* slate-400 */
            font-weight: 700;
            font-size: .85rem;
            cursor: pointer;
            transition: all .2s ease;
            white-space: nowrap;
        }
        #rl-calculator .tab-btn:hover {
            background: #334155; /* slate-700 */
            color: #f1f5f9; /* slate-100 */
            border-color: #475569; /* slate-600 */
        }
        #rl-calculator .tab-btn.active {
            background: #ccff00; /* neon */
            color: #0f172a; /* dark */
            border-color: #ccff00;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(204, 255, 0, 0.25);
        }

        #rl-calculator .tab-content {
            display: none;
            padding: 1rem;
            margin-top: .75rem;
            scroll-margin-top: 110px;
        }
        #rl-calculator .tab-content.active { display: block; }

        #rl-calculator .info-note {
            background: rgba(2,6,23,.35);
            padding: .9rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border-left: 4px solid rgba(204,255,0,.9);
            font-size: .9rem;
            color: var(--text);
        }

        #rl-calculator .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: .75rem;
            align-items: end;
        }
        #rl-calculator .form-group { margin-bottom: .9rem; }
        #rl-calculator label { display: block; margin-bottom: .5rem; font-weight: 800; color: rgba(226,232,240,.88); font-size: .85rem; }
        #rl-calculator input, #rl-calculator select {
            width: 100%;
            padding: .75rem;
            border: 1px solid rgba(148,163,184,.22);
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            transition: all .15s ease;
            background: var(--panel-bg);
            color: var(--text);
            outline: none;
        }
        #rl-calculator input:focus, #rl-calculator select:focus { border-color: rgba(204,255,0,.6); box-shadow: 0 0 0 3px rgba(204,255,0,.14); }
        #rl-calculator input:disabled { opacity: .65; cursor: not-allowed; }

        #rl-calculator .time-inputs { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
        #rl-calculator .time-inputs input { text-align: center; }

        #rl-calculator button.rlc-action {
            width: 100%;
            padding: .9rem 1.2rem;
            background: linear-gradient(135deg, rgba(204,255,0,.95) 0%, rgba(6,182,212,.95) 55%, rgba(59,130,246,.95) 100%);
            color: rgba(2,6,23,.92);
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 900;
            cursor: pointer;
            transition: all .15s ease;
        }
        #rl-calculator button.rlc-action:hover { transform: translateY(-1px); box-shadow: 0 18px 42px rgba(0,0,0,.35); }

        #rl-calculator .results {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(15,23,42,.45);
            border-radius: 16px;
            display: none;
            border: 1px solid rgba(148,163,184,.18);
            scroll-margin-top: 110px;
        }
        #rl-calculator .results.show { display: block; }
        #rl-calculator .result-item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            padding: .75rem;
            background: rgba(2,6,23,.4);
            border-radius: 12px;
            border: 1px solid rgba(148,163,184,.14);
        }
        #rl-calculator .result-grid { display: grid; gap: .6rem; }
        #rl-calculator .result-label { font-weight: 700; color: rgba(226,232,240,.82); font-size: .85rem; }
        #rl-calculator .result-value { font-weight: 900; color: rgba(204,255,0,.95); font-size: 1rem; }

        #rl-calculator .rlc-export-btn {
            width: 100%;
            display: none;
            margin-top: .75rem;
            justify-content: center;
            align-items: center;
            gap: .5rem;
            padding: .85rem 1rem;
            border-radius: 14px;
            border: 1px solid rgba(204,255,0,.35);
            background: rgba(204,255,0,.08);
            color: rgba(226,232,240,.92);
            font-weight: 900;
            cursor: pointer;
            transition: all .15s ease;
        }
        #rl-calculator .rlc-export-btn:hover { transform: translateY(-1px); background: rgba(204,255,0,.14); border-color: rgba(204,255,0,.5); }

        #rl-calculator .error {
            color: #fecaca;
            background: rgba(239,68,68,.14);
            padding: .9rem;
            border-radius: 12px;
            margin-top: .75rem;
            border-left: 4px solid var(--error);
            font-size: .9rem;
        }

        @media (min-width: 768px) {
            #rl-calculator .rlc-wrap { padding: 1.5rem; }
            #rl-calculator .rlc-header h1 { font-size: 2.4rem; }
            #rl-calculator .tab-content { padding: 1.25rem; }
            #rl-calculator .form-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
@endpush

@section('content')
    <div id="rl-calculator" class="w-full pt-10">
        <div class="rlc-wrap max-w-5xl mx-auto">
                <div class="rlc-header">
                    <h1>Ruang Lari Tools Calculator</h1>
                    <p>Calculator lari khusus untuk para pelari</p>
                </div>

                <div class="global-controls">
                    <div class="unit-selector rlc-card" style="width: min(640px, 100%);">
                        <label for="globalUnit">Unit System</label>
                        <select id="globalUnit" onchange="updateGlobalUnit()">
                            <option value="metric">Metric (km, min/km, km/h)</option>
                            <option value="imperial">Imperial (miles, min/mile, mph)</option>
                        </select>
                    </div>
                </div>

                <div class="rlc-card">
                    <div class="tab-navigation">
                        <button class="tab-btn active" onclick="openTab(event, 'magicMile')">Magic Mile</button>
                        <button class="tab-btn" onclick="openTab(event, 'marathon')">Marathon Pace</button>
                        <button class="tab-btn" onclick="openTab(event, 'pace')">Pace</button>
                        <button class="tab-btn" onclick="openTab(event, 'predictor')">Predictor</button>
                        <button class="tab-btn" onclick="openTab(event, 'improvement')">Improvement</button>
                        <button class="tab-btn" onclick="openTab(event, 'splits')">Splits</button>
                        <button class="tab-btn" onclick="openTab(event, 'steps')">Steps</button>
                        <button class="tab-btn" onclick="openTab(event, 'stride')">Stride</button>
                        <button class="tab-btn" onclick="openTab(event, 'training')">Training</button>
                        <button class="tab-btn" onclick="openTab(event, 'hydration')">Hydration</button>
                        <button class="tab-btn" onclick="openTab(event, 'fueling')">Fueling</button>
                        <button class="tab-btn" onclick="openTab(event, 'vo2max')">VO2 Max</button>
                        <button class="tab-btn" onclick="openTab(event, 'heartrate')">Heart Rate</button>
                        <button class="tab-btn" onclick="openTab(event, 'smartMileage')">Smart Mileage Builder</button>
                    </div>

                    <div id="smartMileage" class="tab-content" data-hash="smartmileage">
                        <div class="info-note">
                            Rencanakan peningkatan volume lari Anda secara aman dengan algoritma "Cutback Week" otomatis.
                        </div>

                        <!-- Smart Recommendation Section -->
                        <div class="form-group" style="padding: 1rem; background: rgba(2,6,23,.3); border-radius: 12px; border: 1px solid rgba(148,163,184,.1); margin-bottom: 1.5rem;">
                            <label style="color:#3b82f6; margin-bottom:0.75rem; display:block; border-bottom:1px solid rgba(59,130,246,0.2); padding-bottom:0.5rem;">
                                <i class="fas fa-magic" style="margin-right:0.5rem;"></i> Smart Recommendation (Opsional)
                            </label>
                            <p style="font-size:0.85rem; color:var(--muted); margin-bottom:1rem;">
                                Bingung menentukan volume awal? Masukkan Personal Best (PB) 1 bulan terakhir untuk mendapatkan saran volume mingguan yang aman.
                            </p>
                            <div class="form-row">
                                 <div class="form-group">
                                    <label>Recent Distance</label>
                                    <select id="smPbDist">
                                        <option value="5">5K</option>
                                        <option value="10">10K</option>
                                        <option value="21.1">Half Marathon</option>
                                        <option value="42.2">Marathon</option>
                                    </select>
                                 </div>
                                 <div class="form-group">
                                    <label>Time</label>
                                    <div class="time-inputs" style="grid-template-columns: repeat(3, 1fr); gap: 0.25rem;">
                                         <input type="number" id="smPbH" placeholder="H" min="0">
                                         <input type="number" id="smPbM" placeholder="M" min="0">
                                         <input type="number" id="smPbS" placeholder="S" min="0">
                                    </div>
                                 </div>
                            </div>
                            <button type="button" onclick="calculateSmartSuggestion()" style="width:100%; padding:0.6rem; background:rgba(59,130,246,0.15); border:1px solid rgba(59,130,246,0.4); color:#60a5fa; border-radius:8px; font-weight:700; cursor:pointer; margin-top:0.5rem; transition:all .2s;">
                                <i class="fas fa-calculator"></i> Hitung Rekomendasi Volume
                            </button>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Mode Perhitungan</label>
                                <select id="smMode" onchange="updateSmartMileageInputs()">
                                    <option value="distance">Berdasarkan Jarak (km/miles)</option>
                                    <option value="time">Berdasarkan Waktu (menit)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label id="smCurrentLabel">Current Weekly Volume</label>
                                <input type="number" id="smCurrent" min="0" step="1" placeholder="e.g., 20">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Mulai Tanggal</label>
                                <input type="date" id="smStartDate">
                            </div>
                            <div class="form-group">
                                <label>Durasi Program (Minggu)</label>
                                <input type="number" id="smDuration" min="4" max="52" value="16">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Target Event / Goal</label>
                            <select id="smGoal">
                                <option value="5K">5K</option>
                                <option value="10K">10K</option>
                                <option value="Half Marathon">Half Marathon</option>
                                <option value="Marathon">Marathon</option>
                                <option value="Ultra Marathon">Ultra Marathon</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-top: 1rem; padding: 1rem; background: rgba(2,6,23,.3); border-radius: 12px; border: 1px solid rgba(148,163,184,.1);">
                            <label style="display:flex; justify-content:space-between; align-items:center;">
                                Progression Aggressiveness
                                <span id="smAggressivenessLabel" style="font-size:0.8rem; padding:2px 8px; border-radius:4px; background:#10b981; color:#fff;">Standard Progressive</span>
                            </label>
                            <div style="display:flex; align-items:center; gap:1rem;">
                                <input type="range" id="smSlider" min="1" max="15" value="10" step="1" style="flex:1;" oninput="updateSmartMileageSlider()">
                                <span id="smSliderVal" style="font-weight:800; color:var(--primary); min-width:3rem; text-align:right;">10%</span>
                            </div>
                            <p style="font-size:0.8rem; color:var(--muted); margin-top:0.5rem; line-height:1.4;">
                                Persentase kenaikan volume mingguan. <br>
                                <span style="color:#10b981;">&lt; 8%: Conservative / Rehab</span> | 
                                <span style="color:#3b82f6;">8-12%: Standard</span> | 
                                <span style="color:#ef4444;">&gt; 13%: Aggressive / High Risk</span>
                            </p>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-container" style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                                <input type="checkbox" id="smInjury" onchange="updateSmartMileageSlider()" style="width:auto; transform:scale(1.2);">
                                <span>Sedang dalam pemulihan cedera? (Rehab Mode)</span>
                            </label>
                            <p style="font-size:0.8rem; color:var(--muted); margin-left:1.8rem; margin-top:0.2rem;">
                                Mengunci kenaikan maks 7% dan memperbanyak frekuensi minggu istirahat (cutback).
                            </p>
                        </div>

                        <button class="rlc-action" onclick="calculateSmartMileage()">Generate Smart Plan</button>
                        <div id="smartMileageError" class="error" style="display: none;"></div>
                        
                        <div id="smartMileageResults" class="results">
                            <canvas id="smChart" style="max-height: 300px; margin-bottom: 1.5rem;"></canvas>
                            <div id="smTable"></div>
                        </div>
                        
                        <div style="display:flex; gap:0.5rem; margin-top:0.75rem;">
                             <button type="button" class="rlc-export-btn" id="smartMileageExportBtn" onclick="exportResults('smartMileageResults', 'Smart Mileage Plan')" style="flex:1;">Export Image (PNG)</button>
                             <button type="button" class="rlc-export-btn" id="smartMileageIcsBtn" onclick="exportSmartMileageICS()" style="flex:1; display:none; background:rgba(59,130,246,.15); border-color:rgba(59,130,246,.5); color:#93c5fd;">Export to Calendar (.ics)</button>
                        </div>
                    </div>

                    <div id="magicMile" class="tab-content active" data-hash="magicmile">
                        <div class="info-note">
                            Prediksi waktu race berdasarkan waktu lari 1 mile. Magic Mile akurat untuk benchmark dan target pace.
                        </div>
                        <div class="form-group">
                            <label>Magic Mile Time</label>
                            <div class="time-inputs">
                                <input type="number" id="magicHours" placeholder="Hours" min="0">
                                <input type="number" id="magicMinutes" placeholder="Min" min="0">
                                <input type="number" id="magicSeconds" placeholder="Sec" min="0" max="59">
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateMagicMile()">Calculate Race Predictions</button>
                        <div id="magicMileError" class="error" style="display: none;"></div>
                        <div id="magicMileResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="magicMileExportBtn" onclick="exportResults('magicMileResults', 'Magic Mile')">Export Hasil (PNG)</button>
                    </div>

                    <div id="marathon" class="tab-content" data-hash="marathon">
                        <div class="info-note">
                            Hitung pace untuk mencapai target waktu marathon (42.195 km / 26.2 miles).
                        </div>
                        <div class="form-group">
                            <label>Target Marathon Time</label>
                            <div class="time-inputs">
                                <input type="number" id="marathonHours" placeholder="Hours" min="0">
                                <input type="number" id="marathonMinutes" placeholder="Min" min="0" max="59">
                                <input type="number" id="marathonSeconds" placeholder="Sec" min="0" max="59">
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateMarathonPace()">Calculate Marathon Pace</button>
                        <div id="marathonError" class="error" style="display: none;"></div>
                        <div id="marathonResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="marathonExportBtn" onclick="exportResults('marathonResults', 'Marathon Pace')">Export Hasil (PNG)</button>
                    </div>

                    <div id="pace" class="tab-content" data-hash="pacecalculator">
                        <div class="info-note">Hitung pace berdasarkan jarak dan waktu lari Anda.</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Unit Jarak</label>
                                <select id="paceUnit" onchange="updatePaceDistanceLabel()">
                                    <option value="meter">Meter (m)</option>
                                    <option value="km">Kilometer (km)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label id="paceDistanceLabel">Distance (m)</label>
                                <input type="number" id="paceDistance" step="0.1" min="0.1" placeholder="e.g., 5000">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Time</label>
                            <div class="time-inputs">
                                <input type="number" id="paceHours" placeholder="Hours" min="0">
                                <input type="number" id="paceMinutes" placeholder="Min" min="0">
                                <input type="number" id="paceSeconds" placeholder="Sec" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Pace (<span id="pacePerUnitLabel">min/km</span>)</label>
                            <div class="time-inputs">
                                <input type="number" id="pacePerUnitMinutes" placeholder="Min per unit" min="0">
                                <input type="number" id="pacePerUnitSeconds" placeholder="Sec per unit" min="0" max="59">
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculatePace()">Calculate Pace</button>
                        <div id="paceError" class="error" style="display: none;"></div>
                        <div id="paceResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="paceExportBtn" onclick="exportResults('paceResults', 'Pace Calculator')">Export Hasil (PNG)</button>
                    </div>

                    <div id="predictor" class="tab-content" data-hash="racepredictor">
                        <div class="info-note">Prediksi waktu race berdasarkan performa race sebelumnya menggunakan Riegel's Formula.</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label id="recentRaceDistanceLabel">Recent Race Distance (km)</label>
                                <select id="recentRaceDistanceSelect" onchange="updateRecentRaceDistance()">
                                    <option value="custom">Custom Distance</option>
                                    <option value="5">5K (5 km)</option>
                                    <option value="10">10K (10 km)</option>
                                    <option value="21.1">Half Marathon (21.1 km)</option>
                                    <option value="42.2">Marathon (42.2 km)</option>
                                </select>
                                <input type="number" id="recentRaceDistance" step="0.1" min="0.1" placeholder="e.g., 10" style="margin-top: .5rem;">
                            </div>
                            <div class="form-group">
                                <label id="targetRaceDistanceLabel">Target Race Distance (km)</label>
                                <select id="targetRaceDistanceSelect" onchange="updateTargetRaceDistance()">
                                    <option value="custom">Custom Distance</option>
                                    <option value="5">5K (5 km)</option>
                                    <option value="10">10K (10 km)</option>
                                    <option value="21.1">Half Marathon (21.1 km)</option>
                                    <option value="42.2">Marathon (42.2 km)</option>
                                </select>
                                <input type="number" id="targetRaceDistance" step="0.1" min="0.1" placeholder="e.g., 21.1" style="margin-top: .5rem;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Recent Race Time</label>
                            <div class="time-inputs">
                                <input type="number" id="recentRaceHours" placeholder="Hours" min="0">
                                <input type="number" id="recentRaceMinutes" placeholder="Min" min="0">
                                <input type="number" id="recentRaceSeconds" placeholder="Sec" min="0">
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateRacePredictor()">Predict Race Time</button>
                        <div id="racePredictorError" class="error" style="display: none;"></div>
                        <div id="racePredictorResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="racePredictorExportBtn" onclick="exportResults('racePredictorResults', 'Race Predictor')">Export Hasil (PNG)</button>
                    </div>

                    <div id="improvement" class="tab-content" data-hash="improvement">
                        <div class="info-note">Hitung berapa banyak improvement yang dibutuhkan untuk mencapai target waktu race.</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label id="improvementDistanceLabel">Race Distance (km)</label>
                                <input type="number" id="improvementDistance" step="0.1" min="0.1" placeholder="e.g., 10">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Current Best Time</label>
                            <div class="time-inputs">
                                <input type="number" id="currentBestHours" placeholder="Hours" min="0">
                                <input type="number" id="currentBestMinutes" placeholder="Min" min="0">
                                <input type="number" id="currentBestSeconds" placeholder="Sec" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Target Time</label>
                            <div class="time-inputs">
                                <input type="number" id="targetTimeHours" placeholder="Hours" min="0">
                                <input type="number" id="targetTimeMinutes" placeholder="Min" min="0">
                                <input type="number" id="targetTimeSeconds" placeholder="Sec" min="0">
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateImprovement()">Calculate Improvement</button>
                        <div id="improvementError" class="error" style="display: none;"></div>
                        <div id="improvementResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="improvementExportBtn" onclick="exportResults('improvementResults', 'Improvement')">Export Hasil (PNG)</button>
                    </div>

                    <div id="splits" class="tab-content" data-hash="splits">
                        <div class="info-note">Hitung split times untuk race. Gunakan strategi even/negative/positive.</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label id="splitDistanceLabel">Total Distance (km)</label>
                                <select id="splitDistanceSelect" onchange="updateSplitDistance()">
                                    <option value="custom">Custom Distance</option>
                                    <option value="5">5K (5 km)</option>
                                    <option value="10">10K (10 km)</option>
                                    <option value="21.1">Half Marathon (21.1 km)</option>
                                    <option value="42.2">Marathon (42.2 km)</option>
                                </select>
                                <input type="number" id="splitDistance" step="0.1" min="0.1" placeholder="e.g., 21.1" style="margin-top: .5rem;">
                            </div>
                            <div class="form-group">
                                <label id="splitIntervalLabel">Split Interval (km)</label>
                                <input type="number" id="splitInterval" step="0.1" min="0.1" placeholder="e.g., 1" value="1">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Target Total Time</label>
                            <div class="time-inputs">
                                <input type="number" id="splitHours" placeholder="Hours" min="0">
                                <input type="number" id="splitMinutes" placeholder="Min" min="0">
                                <input type="number" id="splitSeconds" placeholder="Sec" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Split Strategy</label>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="splitStrategy">Strategy Type</label>
                                    <select id="splitStrategy" onchange="updateSplitStrategy()">
                                        <option value="even">Even Splits (Konsisten)</option>
                                        <option value="negative">Negative Splits (Perlahan ke Cepat)</option>
                                        <option value="positive">Positive Splits (Cepat ke Perlahan)</option>
                                    </select>
                                </div>
                                <div class="form-group" id="splitPercentageGroup" style="display: none;">
                                    <label for="splitPercentage">Pace Change per Split (%)</label>
                                    <input type="range" id="splitPercentage" min="0" max="10" step="0.1" value="2" oninput="updateSplitPercentageValue()">
                                    <div id="splitPercentageValue" style="margin-top:.25rem;color:rgba(226,232,240,.85);font-weight:700;">2.0%</div>
                                </div>
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateSplits()">Calculate Splits</button>
                        <div id="splitError" class="error" style="display: none;"></div>
                        <div id="splitResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="splitExportBtn" onclick="exportResults('splitResults', 'Splits')">Export Hasil (PNG)</button>
                    </div>

                    <div id="steps" class="tab-content" data-hash="steps">
                        <div class="info-note">Konversi jumlah langkah ke jarak berdasarkan stride length Anda.</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Number of Steps</label>
                                <input type="number" id="stepsCount" min="1" placeholder="e.g., 10000">
                            </div>
                            <div class="form-group">
                                <label id="strideLengthLabel">Stride Length (cm)</label>
                                <input type="number" id="strideLength" step="0.1" min="1" placeholder="e.g., 75" value="75">
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateStepsToDistance()">Calculate Distance</button>
                        <div id="stepsError" class="error" style="display: none;"></div>
                        <div id="stepsResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="stepsExportBtn" onclick="exportResults('stepsResults', 'Steps to Distance')">Export Hasil (PNG)</button>
                    </div>

                    <div id="stride" class="tab-content" data-hash="stride">
                        <div class="info-note">Hitung stride length berdasarkan jarak yang ditempuh dan jumlah langkah.</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label id="strideDistanceLabel">Distance Covered (km)</label>
                                <input type="number" id="strideDistance" step="0.01" min="0.01" placeholder="e.g., 1">
                            </div>
                            <div class="form-group">
                                <label>Number of Steps</label>
                                <input type="number" id="strideStepsCount" min="1" placeholder="e.g., 1333">
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateStrideLength()">Calculate Stride Length</button>
                        <div id="strideLengthError" class="error" style="display: none;"></div>
                        <div id="strideLengthResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="strideLengthExportBtn" onclick="exportResults('strideLengthResults', 'Stride Length')">Export Hasil (PNG)</button>
                    </div>

                    <div id="training" class="tab-content" data-hash="training">
                        <div class="info-note">Hitung training paces berdasarkan best time (VDOT/Daniels).</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Base Pace Type</label>
                                <select id="basePaceType" onchange="setDefaultDistance()">
                                    <option value="5k">5K Race Pace</option>
                                    <option value="10k">10K Race Pace</option>
                                    <option value="half">Half Marathon Pace</option>
                                    <option value="marathon">Marathon Pace</option>
                                    <option value="threshold">Threshold Pace</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label id="trainingDistanceLabel">Best Race Distance (km)</label>
                                <input type="number" id="trainingDistance" step="0.1" min="0.1" placeholder="e.g., 5" value="5">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Best Race Time (optional)</label>
                            <div class="time-inputs">
                                <input type="number" id="bestTimeHours" placeholder="Hours" min="0">
                                <input type="number" id="bestTimeMinutes" placeholder="Min" min="0">
                                <input type="number" id="bestTimeSeconds" placeholder="Sec" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Manual Base Pace (fallback)</label>
                            <div class="time-inputs">
                                <input type="number" id="basePaceHours" placeholder="Hours" min="0">
                                <input type="number" id="basePaceMinutes" placeholder="Min" min="0">
                                <input type="number" id="basePaceSeconds" placeholder="Sec" min="0" max="59">
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateTrainingPaces()">Calculate Training Paces</button>
                        <div id="trainingPaceError" class="error" style="display: none;"></div>
                        <div id="trainingPaceResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="trainingPaceExportBtn" onclick="exportResults('trainingPaceResults', 'Training Paces')">Export Hasil (PNG)</button>
                    </div>

                    <div id="hydration" class="tab-content" data-hash="hydration">
                        <div class="info-note">Estimasi kebutuhan cairan dan elektrolit per jam, terutama untuk lari di cuaca panas/lembab.</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Durasi Lari (menit)</label>
                                <input type="number" id="hydDuration" min="10" max="600" value="60" placeholder="e.g., 90">
                            </div>
                            <div class="form-group">
                                <label>Target Replacement</label>
                                <select id="hydReplacePct">
                                    <option value="0.5">50% (aman untuk kebanyakan)</option>
                                    <option value="0.6" selected>60% (umum dipakai)</option>
                                    <option value="0.7">70% (cuaca panas)</option>
                                    <option value="0.8">80% (advanced, hati-hati)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Suhu (°C)</label>
                                <input type="number" id="hydTemp" min="0" max="50" value="28" placeholder="e.g., 30">
                            </div>
                            <div class="form-group">
                                <label>Kelembaban (%)</label>
                                <input type="number" id="hydHumidity" min="0" max="100" value="70" placeholder="e.g., 80">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Sweat Rate (ml/jam) (opsional)</label>
                                <input type="number" id="hydSweatRate" min="200" max="2500" step="10" placeholder="mis. 800">
                            </div>
                            <div class="form-group">
                                <label>Saltiness (opsional)</label>
                                <select id="hydSaltiness">
                                    <option value="low">Low (jarang salt stain)</option>
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High (sering salt stain)</option>
                                </select>
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateHydration()">Hitung Hydration Plan</button>
                        <div id="hydrationError" class="error" style="display: none;"></div>
                        <div id="hydrationResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="hydrationExportBtn" onclick="exportResults('hydrationResults', 'Hydration & Electrolyte')">Export Hasil (PNG)</button>
                    </div>

                    <div id="fueling" class="tab-content" data-hash="fueling">
                        <div class="info-note">Rencana asupan karbo per jam + jadwal minum/gel selama race atau long run.</div>
                        <div class="form-group">
                            <label>Target Durasi</label>
                            <div class="time-inputs">
                                <input type="number" id="fuelHours" placeholder="Hours" min="0" value="2">
                                <input type="number" id="fuelMinutes" placeholder="Min" min="0" max="59" value="0">
                                <input type="number" id="fuelSeconds" placeholder="Sec" min="0" max="59" value="0">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Intensitas</label>
                                <select id="fuelIntensity">
                                    <option value="easy">Easy / Long Run</option>
                                    <option value="tempo">Tempo</option>
                                    <option value="race" selected>Race</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Toleransi GI</label>
                                <select id="fuelTolerance">
                                    <option value="low">Low (sensitif)</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High (tahan banyak)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jenis Fuel Utama</label>
                                <select id="fuelType">
                                    <option value="gel" selected>Gel</option>
                                    <option value="drink">Sports Drink</option>
                                    <option value="mix">Mix (gel + drink)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Interval Konsumsi</label>
                                <select id="fuelInterval">
                                    <option value="15">Setiap 15 menit</option>
                                    <option value="20" selected>Setiap 20 menit</option>
                                    <option value="30">Setiap 30 menit</option>
                                </select>
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateFuelingPlan()">Hitung Fueling Plan</button>
                        <div id="fuelingError" class="error" style="display: none;"></div>
                        <div id="fuelingResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="fuelingExportBtn" onclick="exportResults('fuelingResults', 'Carb/Fueling Plan')">Export Hasil (PNG)</button>
                    </div>

                    <div id="vo2max" class="tab-content" data-hash="vo2max">
                        <div class="info-note">Estimasi VO2 Max berdasarkan performa race.</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label id="vo2DistanceLabel">Race Distance (km)</label>
                                <input type="number" id="vo2Distance" step="0.1" min="0.1" placeholder="e.g., 5">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Race Time</label>
                            <div class="time-inputs">
                                <input type="number" id="vo2Hours" placeholder="Hours" min="0">
                                <input type="number" id="vo2Minutes" placeholder="Min" min="0">
                                <input type="number" id="vo2Seconds" placeholder="Sec" min="0">
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateVO2Max()">Calculate VO2 Max</button>
                        <div id="vo2Error" class="error" style="display: none;"></div>
                        <div id="vo2Results" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="vo2ExportBtn" onclick="exportResults('vo2Results', 'VO2 Max')">Export Hasil (PNG)</button>
                    </div>

                    <div id="heartrate" class="tab-content" data-hash="heartrate">
                        <div class="info-note">Hitung zona detak jantung optimal untuk latihan.</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Usia (tahun)</label>
                                <input type="number" id="hrAge" min="10" max="100" placeholder="e.g., 30" required>
                            </div>
                            <div class="form-group">
                                <label>Detak Jantung Istirahat (HRrest)</label>
                                <input type="number" id="hrRest" min="30" max="120" placeholder="e.g., 60 (opsional)">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select id="hrGender">
                                    <option value="male">Laki-laki</option>
                                    <option value="female">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tingkat Kebugaran</label>
                                <select id="hrFitness">
                                    <option value="beginner">Pemula</option>
                                    <option value="intermediate">Menengah</option>
                                    <option value="advanced">Lanjutan</option>
                                    <option value="athlete">Atlet</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Intensitas Latihan</label>
                                <select id="hrIntensity">
                                    <option value="recovery">Recovery (Pemulihan)</option>
                                    <option value="easy">Easy (Mudah)</option>
                                    <option value="moderate">Moderate (Sedang)</option>
                                    <option value="tempo">Tempo (Threshold)</option>
                                    <option value="interval">Interval (VO2 Max)</option>
                                    <option value="repetition">Repetition (Kecepatan)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Durasi Latihan (menit)</label>
                                <input type="number" id="hrDuration" min="5" max="300" placeholder="e.g., 30" value="30">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Suhu Lingkungan (°C)</label>
                                <input type="number" id="hrTemperature" min="0" max="50" placeholder="e.g., 25" value="25">
                            </div>
                            <div class="form-group">
                                <label>Kelembaban (%)</label>
                                <input type="number" id="hrHumidity" min="0" max="100" placeholder="e.g., 60" value="60">
                            </div>
                        </div>
                        <button class="rlc-action" onclick="calculateHeartRateZones()">Hitung Zona Heart Rate</button>
                        <div id="heartRateError" class="error" style="display: none;"></div>
                        <div id="heartRateResults" class="results"></div>
                        <button type="button" class="rlc-export-btn" id="heartRateExportBtn" onclick="exportResults('heartRateResults', 'Heart Rate Zones')">Export Hasil (PNG)</button>
                    </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        let globalUnit = 'metric';
        
        function updateGlobalUnit() {
            globalUnit = document.getElementById('globalUnit').value;
            updateAllLabels();
        }
        
        function updateAllLabels() {
            const isMetric = globalUnit === 'metric';
            const distanceUnit = isMetric ? 'km' : 'miles';
            
            const distanceLabels = [
                'paceDistanceLabel', 'recentRaceDistanceLabel', 'targetRaceDistanceLabel',
                'improvementDistanceLabel', 'splitDistanceLabel', 'splitIntervalLabel',
                'strideDistanceLabel', 'vo2DistanceLabel', 'trainingDistanceLabel'
            ];
            
            distanceLabels.forEach(labelId => {
                const element = document.getElementById(labelId);
                if (element) {
                    element.textContent = element.textContent.replace(/(km|miles)/, distanceUnit);
                }
            });
            
            const strideLengthLabel = document.getElementById('strideLengthLabel');
            if (strideLengthLabel) {
                strideLengthLabel.textContent = isMetric ? 'Stride Length (cm)' : 'Stride Length (inches)';
            }
        }
        
        function openTab(evt, tabName) {
            let i;
            const tabContent = document.getElementsByClassName('tab-content');
            for (i = 0; i < tabContent.length; i++) {
                tabContent[i].classList.remove('active');
            }
            
            const tabBtns = document.getElementsByClassName('tab-btn');
            for (i = 0; i < tabBtns.length; i++) {
                tabBtns[i].classList.remove('active');
            }
            
            const tabElement = document.getElementById(tabName);
            tabElement.classList.add('active');
            if (evt && evt.currentTarget) {
                evt.currentTarget.classList.add('active');
            }
            
            const hash = tabElement.getAttribute('data-hash');
            if (hash) {
                window.location.hash = hash;
            }

            setTimeout(() => {
                tabElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 60);
        }
        
        function openTabByHash(hash) {
            if (!hash) return;
            
            const tabContent = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContent.length; i++) {
                const tabHash = tabContent[i].getAttribute('data-hash');
                if (tabHash === hash) {
                    const tabName = tabContent[i].id;
                    
                    for (let j = 0; j < tabContent.length; j++) {
                        tabContent[j].classList.remove('active');
                    }
                    
                    const tabBtns = document.getElementsByClassName('tab-btn');
                    for (let j = 0; j < tabBtns.length; j++) {
                        tabBtns[j].classList.remove('active');
                    }
                    
                    tabContent[i].classList.add('active');
                    
                    for (let j = 0; j < tabBtns.length; j++) {
                        const onclick = tabBtns[j].getAttribute('onclick');
                        if (onclick && onclick.includes(tabName)) {
                            tabBtns[j].classList.add('active');
                            break;
                        }
                    }

                    setTimeout(() => {
                        tabContent[i].scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 60);
                    break;
                }
            }
        }
        
        function convertDistanceToMetric(distance) {
            return globalUnit === 'imperial' ? distance * 1.60934 : distance;
        }
        
        function convertDistanceFromMetric(distance) {
            return globalUnit === 'imperial' ? distance / 1.60934 : distance;
        }
        
        function formatTime(totalSeconds, includeHours = true) {
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = Math.floor(totalSeconds % 60);
            
            if (includeHours && hours > 0) {
                return `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            } else {
                return `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
        }
        
        function timeToSeconds(hours, minutes, seconds) {
            return (hours || 0) * 3600 + (minutes || 0) * 60 + (seconds || 0);
        }
        
        function showError(errorId, message) {
            const errorElement = document.getElementById(errorId);
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            document.getElementById(errorId.replace('Error', 'Results')).classList.remove('show');
            const exportBtn = document.getElementById(errorId.replace('Error', 'ExportBtn'));
            if (exportBtn) {
                exportBtn.style.display = 'none';
            }
        }
        
        function showResults(resultsId, results) {
            document.getElementById(resultsId.replace('Results', 'Error')).style.display = 'none';
            const resultsElement = document.getElementById(resultsId);
            resultsElement.innerHTML = results;
            resultsElement.classList.add('show');
            const exportBtn = document.getElementById(resultsId.replace('Results', 'ExportBtn'));
            if (exportBtn) {
                exportBtn.style.display = 'inline-flex';
            }

            setTimeout(() => {
                resultsElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 60);
        }

        function exportResults(resultsId, calculatorTitle) {
            const resultsElement = document.getElementById(resultsId);
            if (!resultsElement || !resultsElement.classList.contains('show')) {
                alert('Tidak ada hasil untuk diekspor. Silakan hitung terlebih dahulu.');
                return;
            }

            const exportContainer = document.createElement('div');
            exportContainer.style.cssText = [
                'position:fixed',
                'left:-10000px',
                'top:0',
                'width:900px',
                'max-width:900px',
                'background:#ffffff',
                'padding:24px',
                'border-radius:18px',
                'font-family:Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif',
                'color:#0f172a',
                'box-shadow:0 24px 60px rgba(0,0,0,.18)'
            ].join(';');

            const header = document.createElement('div');
            header.style.cssText = 'display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:18px';

            const brandLeft = document.createElement('div');
            brandLeft.style.cssText = 'display:flex;align-items:center;gap:10px';

            const logo = document.createElement('img');
            logo.src = "{{ asset('images/logo saja ruang lari.png') }}";
            logo.alt = 'Ruang Lari';
            logo.style.cssText = 'width:44px;height:44px;object-fit:contain';
            brandLeft.appendChild(logo);

            const brandText = document.createElement('div');
            brandText.style.cssText = 'display:flex;flex-direction:column;line-height:1.1';

            const title1 = document.createElement('div');
            title1.textContent = 'RUANG LARI';
            title1.style.cssText = 'font-weight:900;font-size:16px;letter-spacing:.04em';
            brandText.appendChild(title1);

            const title2 = document.createElement('div');
            title2.textContent = 'TOOLS CALCULATOR';
            title2.style.cssText = 'font-weight:800;font-size:12px;color:#334155;letter-spacing:.08em';
            brandText.appendChild(title2);

            brandLeft.appendChild(brandText);
            header.appendChild(brandLeft);

            const metaRight = document.createElement('div');
            metaRight.style.cssText = 'text-align:right';

            const calcName = document.createElement('div');
            calcName.textContent = calculatorTitle;
            calcName.style.cssText = 'font-weight:900;font-size:14px;color:#0f172a';
            metaRight.appendChild(calcName);

            const date = document.createElement('div');
            date.textContent = new Date().toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
            date.style.cssText = 'font-weight:700;font-size:12px;color:#64748b;margin-top:2px';
            metaRight.appendChild(date);

            header.appendChild(metaRight);
            exportContainer.appendChild(header);

            const divider = document.createElement('div');
            divider.style.cssText = 'height:1px;background:#e2e8f0;margin:0 0 18px 0';
            exportContainer.appendChild(divider);

            const resultsClone = resultsElement.cloneNode(true);
            resultsClone.classList.remove('show');
            resultsClone.style.cssText = 'display:block;background:transparent;border:none;padding:0;margin:0';
            styleContentForExport(resultsClone);
            exportContainer.appendChild(resultsClone);

            document.body.appendChild(exportContainer);

            html2canvas(exportContainer, {
                backgroundColor: '#ffffff',
                scale: 2,
                useCORS: true
            }).then(canvas => {
                const link = document.createElement('a');
                const safeName = calculatorTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                link.download = `${safeName}-${new Date().toISOString().slice(0, 10)}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
                document.body.removeChild(exportContainer);
            }).catch(() => {
                alert('Gagal mengekspor hasil. Silakan coba lagi.');
                document.body.removeChild(exportContainer);
            });
        }

        function styleContentForExport(container) {
            const resultGrids = container.querySelectorAll('.result-grid');
            resultGrids.forEach(grid => {
                grid.style.cssText = 'display:grid;gap:12px';
            });

            const items = container.querySelectorAll('.result-item');
            items.forEach(item => {
                item.style.cssText = 'display:flex;justify-content:space-between;gap:12px;align-items:center;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px';
            });

            const labels = container.querySelectorAll('.result-label');
            labels.forEach(l => {
                l.style.cssText = 'font-weight:800;font-size:12px;color:#334155';
            });

            const values = container.querySelectorAll('.result-value');
            values.forEach(v => {
                v.style.cssText = 'font-weight:900;font-size:14px;color:#2563eb';
            });

            const notes = container.querySelectorAll('.info-note');
            notes.forEach(n => {
                n.style.cssText = 'background:#f1f5f9;border-left:4px solid #2563eb;border-radius:12px;padding:12px 14px;margin-bottom:12px;color:#0f172a;font-weight:700;font-size:12px';
            });
        }
        
        function calculateVO2Max() {
            const distance = parseFloat(document.getElementById('vo2Distance').value);
            const hours = parseInt(document.getElementById('vo2Hours').value) || 0;
            const minutes = parseInt(document.getElementById('vo2Minutes').value) || 0;
            const seconds = parseInt(document.getElementById('vo2Seconds').value) || 0;
            
            if (!distance) {
                showError('vo2Error', 'Please enter a valid distance.');
                return;
            }
            
            const totalSeconds = timeToSeconds(hours, minutes, seconds);
            if (totalSeconds === 0) {
                showError('vo2Error', 'Please enter a valid time.');
                return;
            }
            
            const distanceMetric = convertDistanceToMetric(distance);
            const velocityMPerMin = (distanceMetric * 1000) / (totalSeconds / 60);
            const vo2Max = -4.6 + 0.182258 * velocityMPerMin + 0.000104 * Math.pow(velocityMPerMin, 2);
            
            const pace = totalSeconds / distanceMetric;
            const adjustedPace = globalUnit === 'metric' ? pace : pace * 1.60934;
            const paceUnit = globalUnit === 'metric' ? 'min/km' : 'min/mile';
            
            let results = `
                <div class="result-grid">
                    <div class="result-item">
                        <div class="result-label">Race Pace</div>
                        <div class="result-value">${formatTime(adjustedPace, false)} ${paceUnit}</div>
                    </div>
                    <div class="result-item">
                        <div class="result-label">Estimated VO2 Max</div>
                        <div class="result-value">${vo2Max.toFixed(1)} ml/kg/min</div>
                    </div>
                </div>
            `;
            
            showResults('vo2Results', results);
        }
        
        function calculateMagicMile() {
            const hours = parseInt(document.getElementById('magicHours').value) || 0;
            const minutes = parseInt(document.getElementById('magicMinutes').value) || 0;
            const seconds = parseInt(document.getElementById('magicSeconds').value) || 0;
            
            const magicMileSeconds = timeToSeconds(hours, minutes, seconds);
            if (magicMileSeconds === 0) {
                showError('magicMileError', 'Please enter a valid magic mile time.');
                return;
            }
            
            const mileToKm = 1.60934;
            const basePace = magicMileSeconds / (globalUnit === 'metric' ? mileToKm : 1);
            
            const predictions = {
                '5K': basePace * (globalUnit === 'metric' ? 5 : 3.1) * 1.05,
                '10K': basePace * (globalUnit === 'metric' ? 10 : 6.2) * 1.08,
                'Half Marathon': basePace * (globalUnit === 'metric' ? 21.1 : 13.1) * 1.15,
                'Marathon': basePace * (globalUnit === 'metric' ? 42.2 : 26.2) * 1.2
            };
            
            let results = '<div class="result-grid">';
            results += `<div class="result-item"><div class="result-label">Magic Mile Time</div><div class="result-value">${formatTime(magicMileSeconds, true)}</div></div>`;
            
            Object.entries(predictions).forEach(([race, time]) => {
                results += `<div class="result-item"><div class="result-label">${race} Prediction</div><div class="result-value">${formatTime(time, true)}</div></div>`;
            });
            
            results += '</div>';
            showResults('magicMileResults', results);
        }
        
        function calculateMarathonPace() {
            const hours = parseInt(document.getElementById('marathonHours').value) || 0;
            const minutes = parseInt(document.getElementById('marathonMinutes').value) || 0;
            const seconds = parseInt(document.getElementById('marathonSeconds').value) || 0;
            
            const totalSeconds = timeToSeconds(hours, minutes, seconds);
            if (totalSeconds === 0) {
                showError('marathonError', 'Please enter a valid marathon time.');
                return;
            }
            
            const marathonDistance = globalUnit === 'metric' ? 42.195 : 26.2;
            const pacePerUnit = totalSeconds / marathonDistance;
            const speed = (marathonDistance / (totalSeconds / 3600)).toFixed(2);
            
            const paceUnit = globalUnit === 'metric' ? 'min/km' : 'min/mile';
            const speedUnit = globalUnit === 'metric' ? 'km/h' : 'mph';
            
            let results = `
                <div class="result-grid">
                    <div class="result-item"><div class="result-label">Required Pace</div><div class="result-value">${formatTime(pacePerUnit, false)} ${paceUnit}</div></div>
                    <div class="result-item"><div class="result-label">Average Speed</div><div class="result-value">${speed} ${speedUnit}</div></div>
                </div>
            `;
            
            showResults('marathonResults', results);
        }
        
        function updatePaceDistanceLabel() {
            const paceUnit = document.getElementById('paceUnit').value;
            const label = document.getElementById('paceDistanceLabel');
            const input = document.getElementById('paceDistance');
            
            if (paceUnit === 'meter') {
                label.textContent = 'Distance (m)';
                input.placeholder = 'e.g., 5000';
                input.step = '1';
            } else {
                label.textContent = 'Distance (km)';
                input.placeholder = 'e.g., 5';
                input.step = '0.1';
            }
        }
        
        function updatePacePerUnitLabel() {
            const label = document.getElementById('pacePerUnitLabel');
            if (label) {
                label.textContent = globalUnit === 'metric' ? 'min/km' : 'min/mile';
            }
        }
        
        function getPaceSecondsPerUnit() {
            const m = parseInt(document.getElementById('pacePerUnitMinutes').value) || 0;
            const s = parseInt(document.getElementById('pacePerUnitSeconds').value) || 0;
            return m * 60 + s;
        }
        
        function setPaceInputsFromSeconds(secPerUnit) {
            const m = Math.floor(secPerUnit / 60);
            const s = Math.round(secPerUnit % 60);
            document.getElementById('pacePerUnitMinutes').value = isFinite(m) ? m : 0;
            document.getElementById('pacePerUnitSeconds').value = isFinite(s) ? s : 0;
        }
        
        function getTimeTotalSeconds() {
            const h = parseInt(document.getElementById('paceHours').value) || 0;
            const m = parseInt(document.getElementById('paceMinutes').value) || 0;
            const s = parseInt(document.getElementById('paceSeconds').value) || 0;
            return timeToSeconds(h, m, s);
        }
        
        function setTimeFieldsFromSeconds(totalSeconds) {
            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = Math.floor(totalSeconds % 60);
            document.getElementById('paceHours').value = isFinite(h) ? h : 0;
            document.getElementById('paceMinutes').value = isFinite(m) ? m : 0;
            document.getElementById('paceSeconds').value = isFinite(s) ? s : 0;
        }
        
        function getDistanceInGlobalUnit() {
            const distance = parseFloat(document.getElementById('paceDistance').value);
            const unit = document.getElementById('paceUnit').value; // meter|km
            if (!distance || distance <= 0) return 0;
            let km = unit === 'meter' ? (distance / 1000) : distance;
            return globalUnit === 'metric' ? km : km / 1.60934;
        }
        
        let _syncLock = false;
        function syncPaceTriplet(changed) {
            if (_syncLock) return;
            _syncLock = true;
            try {
                const distUnits = getDistanceInGlobalUnit();
                const totalSec = getTimeTotalSeconds();
                const paceSec = getPaceSecondsPerUnit();
                
                const hasDist = distUnits > 0;
                const hasTime = totalSec > 0;
                const hasPace = paceSec > 0;
                
                if (changed === 'time' && hasTime && hasDist) {
                    const secPerUnit = totalSec / distUnits;
                    setPaceInputsFromSeconds(secPerUnit);
                } else if (changed === 'pace' && hasPace && hasDist) {
                    const total = paceSec * distUnits;
                    setTimeFieldsFromSeconds(total);
                } else if (changed === 'distance') {
                    if (hasTime) {
                        const secPerUnit = totalSec / distUnits;
                        setPaceInputsFromSeconds(secPerUnit);
                    } else if (hasPace) {
                        const total = paceSec * distUnits;
                        setTimeFieldsFromSeconds(total);
                    }
                }
            } finally {
                _syncLock = false;
            }
        }
        
        function calculatePace() {
            const distance = parseFloat(document.getElementById('paceDistance').value);
            const paceUnitInput = document.getElementById('paceUnit').value;
            const hours = parseInt(document.getElementById('paceHours').value) || 0;
            const minutes = parseInt(document.getElementById('paceMinutes').value) || 0;
            const seconds = parseInt(document.getElementById('paceSeconds').value) || 0;
            
            if (!distance || distance <= 0) {
                showError('paceError', 'Please enter a valid distance.');
                return;
            }
            
            const totalSeconds = timeToSeconds(hours, minutes, seconds);
            if (totalSeconds === 0) {
                showError('paceError', 'Please enter a valid time.');
                return;
            }
            
            let distanceInKm;
            if (paceUnitInput === 'meter') {
                distanceInKm = distance / 1000;
            } else {
                distanceInKm = distance;
            }
            
            const pacePerKm = totalSeconds / distanceInKm;
            const pacePerUnit = globalUnit === 'metric' ? pacePerKm : pacePerKm * 1.60934;
            const speed = (distanceInKm / (totalSeconds / 3600) * (globalUnit === 'metric' ? 1 : 0.621371)).toFixed(2);
            
            const paceUnit = globalUnit === 'metric' ? 'min/km' : 'min/mile';
            const speedUnit = globalUnit === 'metric' ? 'km/h' : 'mph';
            
            let results = `
                <div class="result-grid">
                    <div class="result-item"><div class="result-label">Pace</div><div class="result-value">${formatTime(pacePerUnit, false)} ${paceUnit}</div></div>
                    <div class="result-item"><div class="result-label">Speed</div><div class="result-value">${speed} ${speedUnit}</div></div>
                </div>
            `;
            
            showResults('paceResults', results);
            
            setPaceInputsFromSeconds(pacePerUnit);
        }
        
        function calculateRacePredictor() {
            const recentDistance = parseFloat(document.getElementById('recentRaceDistance').value);
            const targetDistance = parseFloat(document.getElementById('targetRaceDistance').value);
            const hours = parseInt(document.getElementById('recentRaceHours').value) || 0;
            const minutes = parseInt(document.getElementById('recentRaceMinutes').value) || 0;
            const seconds = parseInt(document.getElementById('recentRaceSeconds').value) || 0;
            
            if (!recentDistance || !targetDistance) {
                showError('racePredictorError', 'Please enter valid distances.');
                return;
            }
            
            const totalSeconds = timeToSeconds(hours, minutes, seconds);
            if (totalSeconds === 0) {
                showError('racePredictorError', 'Please enter a valid race time.');
                return;
            }
            
            const recentDistanceMetric = convertDistanceToMetric(recentDistance);
            const targetDistanceMetric = convertDistanceToMetric(targetDistance);
            
            const predictedSeconds = totalSeconds * Math.pow(targetDistanceMetric / recentDistanceMetric, 1.06);
            let results = `
                <div class="result-grid">
                    <div class="result-item"><div class="result-label">Predicted Time</div><div class="result-value">${formatTime(predictedSeconds, true)}</div></div>
                </div>
            `;
            showResults('racePredictorResults', results);
        }
        
        function calculateImprovement() {
            const currentHours = parseInt(document.getElementById('currentBestHours').value) || 0;
            const currentMinutes = parseInt(document.getElementById('currentBestMinutes').value) || 0;
            const currentSeconds = parseInt(document.getElementById('currentBestSeconds').value) || 0;
            const targetHours = parseInt(document.getElementById('targetTimeHours').value) || 0;
            const targetMinutes = parseInt(document.getElementById('targetTimeMinutes').value) || 0;
            const targetSeconds = parseInt(document.getElementById('targetTimeSeconds').value) || 0;
            
            const currentTime = timeToSeconds(currentHours, currentMinutes, currentSeconds);
            const targetTime = timeToSeconds(targetHours, targetMinutes, targetSeconds);
            
            if (currentTime === 0 || targetTime === 0) {
                showError('improvementError', 'Please enter valid times.');
                return;
            }
            
            const improvement = currentTime - targetTime;
            const percentImprovement = ((currentTime - targetTime) / currentTime * 100).toFixed(2);
            
            let results = `
                <div class="result-grid">
                    <div class="result-item"><div class="result-label">Time Improvement</div><div class="result-value">${formatTime(improvement, true)} (${percentImprovement}%)</div></div>
                </div>
            `;
            showResults('improvementResults', results);
        }
        
        function updateSplitStrategy() {
            const strategy = document.getElementById('splitStrategy').value;
            const percentageGroup = document.getElementById('splitPercentageGroup');
            
            if (strategy === 'even') {
                percentageGroup.style.display = 'none';
            } else {
                percentageGroup.style.display = 'block';
            }
        }
        
        function updateSplitPercentageValue() {
            const slider = document.getElementById('splitPercentage');
            const valueDisplay = document.getElementById('splitPercentageValue');
            valueDisplay.textContent = parseFloat(slider.value).toFixed(1) + '%';
        }
        
        function updateSplitDistance() {
            const select = document.getElementById('splitDistanceSelect');
            const input = document.getElementById('splitDistance');
            
            if (select.value === 'custom') {
                input.disabled = false;
                input.placeholder = 'e.g., 21.1';
                input.value = '';
            } else {
                input.disabled = true;
                input.value = select.value;
                input.placeholder = select.options[select.selectedIndex].text;
            }
        }
        
        function updateRecentRaceDistance() {
            const select = document.getElementById('recentRaceDistanceSelect');
            const input = document.getElementById('recentRaceDistance');
            
            if (select.value === 'custom') {
                input.disabled = false;
                input.placeholder = 'e.g., 10';
                input.value = '';
            } else {
                input.disabled = true;
                input.value = select.value;
                input.placeholder = select.options[select.selectedIndex].text;
            }
        }
        
        function updateTargetRaceDistance() {
            const select = document.getElementById('targetRaceDistanceSelect');
            const input = document.getElementById('targetRaceDistance');
            
            if (select.value === 'custom') {
                input.disabled = false;
                input.placeholder = 'e.g., 21.1';
                input.value = '';
            } else {
                input.disabled = true;
                input.value = select.value;
                input.placeholder = select.options[select.selectedIndex].text;
            }
        }
        
        function calculateSplits() {
            const distance = parseFloat(document.getElementById('splitDistance').value);
            const interval = parseFloat(document.getElementById('splitInterval').value);
            const hours = parseInt(document.getElementById('splitHours').value) || 0;
            const minutes = parseInt(document.getElementById('splitMinutes').value) || 0;
            const seconds = parseInt(document.getElementById('splitSeconds').value) || 0;
            
            if (!distance || !interval) {
                showError('splitError', 'Please enter valid distance and interval.');
                return;
            }
            
            const totalSeconds = timeToSeconds(hours, minutes, seconds);
            if (totalSeconds === 0) {
                showError('splitError', 'Please enter a valid total time.');
                return;
            }
            
            const distanceMetric = convertDistanceToMetric(distance);
            const intervalMetric = convertDistanceToMetric(interval);
            const numberOfSplits = Math.floor(distanceMetric / intervalMetric);
            
            const basePace = totalSeconds / distanceMetric;
            const distanceUnit = globalUnit === 'metric' ? 'km' : 'miles';
            const paceUnit = globalUnit === 'metric' ? 'min/km' : 'min/mile';
            
            let results = '<div class="result-grid">';
            let cumulativeTime = 0;
            for (let i = 1; i <= numberOfSplits; i++) {
                const splitTime = basePace * intervalMetric;
                cumulativeTime += splitTime;
                results += `<div class="result-item"><div class="result-label">${(interval * i)} ${distanceUnit}</div><div class="result-value">${formatTime(cumulativeTime, true)} (${formatTime(globalUnit === 'metric' ? basePace : basePace * 1.60934, false)} ${paceUnit})</div></div>`;
            }
            results += '</div>';
            showResults('splitResults', results);
        }
        
        function calculateStepsToDistance() {
            const steps = parseInt(document.getElementById('stepsCount').value);
            const strideLength = parseFloat(document.getElementById('strideLength').value);
            
            if (!steps || !strideLength) {
                showError('stepsError', 'Please enter valid steps and stride length.');
                return;
            }
            
            let distanceMeters;
            if (globalUnit === 'metric') {
                distanceMeters = (steps * strideLength) / 100;
            } else {
                distanceMeters = (steps * strideLength * 2.54) / 100;
            }
            
            const distanceKm = distanceMeters / 1000;
            const distanceMiles = distanceKm * 0.621371;
            const displayDistance = globalUnit === 'metric' ? distanceKm : distanceMiles;
            const distanceUnit = globalUnit === 'metric' ? 'km' : 'miles';
            
            let results = `
                <div class="result-grid">
                    <div class="result-item"><div class="result-label">Distance</div><div class="result-value">${displayDistance.toFixed(2)} ${distanceUnit}</div></div>
                </div>
            `;
            showResults('stepsResults', results);
        }
        
        function calculateStrideLength() {
            const distance = parseFloat(document.getElementById('strideDistance').value);
            const steps = parseInt(document.getElementById('strideStepsCount').value);
            
            if (!distance || !steps) {
                showError('strideLengthError', 'Please enter valid distance and steps.');
                return;
            }
            
            const distanceMetric = convertDistanceToMetric(distance);
            const distanceMeters = distanceMetric * 1000;
            const strideLengthCm = (distanceMeters / steps) * 100;
            const strideLengthInches = strideLengthCm / 2.54;
            
            const displayStrideLength = globalUnit === 'metric' ? strideLengthCm : strideLengthInches;
            const strideLengthUnit = globalUnit === 'metric' ? 'cm' : 'inches';
            
            let results = `
                <div class="result-grid">
                    <div class="result-item"><div class="result-label">Stride Length</div><div class="result-value">${displayStrideLength.toFixed(1)} ${strideLengthUnit}</div></div>
                </div>
            `;
            showResults('strideLengthResults', results);
        }
        
        function calculateVDOT(distKm, timeSec) {
            const timeMin = timeSec / 60;
            const velocity = (distKm * 1000 / timeSec) * 60;
            const vo2 = -4.60 + 0.182258 * velocity + 0.000104 * Math.pow(velocity, 2);
            const percentMax = 0.8 + 0.1894393 * Math.exp(-0.012778 * timeMin) + 0.2989558 * Math.exp(-0.1932605 * timeMin);
            return vo2 / percentMax;
        }
        
        function getPaceFromVDOT(vdot, percent, globalUnit) {
            const vo2Target = percent * vdot;
            const a = 0.000104;
            const b = 0.182258;
            const c = -(vo2Target + 4.6);
            const discriminant = Math.pow(b, 2) - 4 * a * c;
            if (discriminant < 0) return null;
            const v = (-b + Math.sqrt(discriminant)) / (2 * a);
            const secPerKm = (1000 / v) * 60;
            return globalUnit === 'metric' ? secPerKm : secPerKm * 1.60934;
        }
        
        function setDefaultDistance() {
            const basePaceType = document.getElementById('basePaceType').value;
            const distanceInput = document.getElementById('trainingDistance');
            const distances = { '5k': 5, '10k': 10, 'half': 21.1, 'marathon': 42.2, 'threshold': 10 };
            distanceInput.value = distances[basePaceType] || 5;
        }
        
        function calculateTrainingPaces() {
            const distance = parseFloat(document.getElementById('trainingDistance').value) || 0;
            const bestHours = parseInt(document.getElementById('bestTimeHours').value) || 0;
            const bestMinutes = parseInt(document.getElementById('bestTimeMinutes').value) || 0;
            const bestSeconds = parseInt(document.getElementById('bestTimeSeconds').value) || 0;
            
            const bestTimeSec = timeToSeconds(bestHours, bestMinutes, bestSeconds);
            if (distance <= 0 || bestTimeSec <= 0) {
                showError('trainingPaceError', 'Please enter best race distance and time.');
                return;
            }
            
            const distMetric = convertDistanceToMetric(distance);
            const vdot = calculateVDOT(distMetric, bestTimeSec);
            const paceUnit = globalUnit === 'metric' ? 'min/km' : 'min/mile';
            
            const trainingPercents = { easy: 0.65, moderate: 0.75, tempo: 0.85, interval: 0.95, repetition: 1.00 };
            
            let results = '<div class="result-grid">';
            results += `<div class="result-item"><div class="result-label">Estimated VDOT</div><div class="result-value">${vdot.toFixed(1)}</div></div>`;
            Object.entries(trainingPercents).forEach(([name, pct]) => {
                const sec = getPaceFromVDOT(vdot, pct, globalUnit);
                if (!sec) return;
                results += `<div class="result-item"><div class="result-label">${name.toUpperCase()}</div><div class="result-value">${formatTime(sec, false)} ${paceUnit}</div></div>`;
            });
            results += '</div>';
            showResults('trainingPaceResults', results);
        }
        
        function calculateHeartRateZones() {
            const age = parseInt(document.getElementById('hrAge').value);
            const hrRestInput = document.getElementById('hrRest').value;
            const hrRest = hrRestInput ? parseInt(hrRestInput) : 60;
            
            if (!age) {
                showError('heartRateError', 'Usia wajib diisi.');
                return;
            }
            
            const hrmax = 220 - age;
            const hrr = hrmax - hrRest;
            
            const zones = [
                { name: 'Zone 1 - Recovery', min: 0.50, max: 0.60 },
                { name: 'Zone 2 - Easy', min: 0.60, max: 0.70 },
                { name: 'Zone 3 - Moderate', min: 0.70, max: 0.80 },
                { name: 'Zone 4 - Tempo', min: 0.80, max: 0.90 },
                { name: 'Zone 5 - Interval', min: 0.90, max: 1.00 },
            ];
            
            let results = '<div class="result-grid">';
            results += `<div class="result-item"><div class="result-label">HR Max (estimasi)</div><div class="result-value">${Math.round(hrmax)} bpm</div></div>`;
            zones.forEach(z => {
                const minHR = Math.round(hrRest + (z.min * hrr));
                const maxHR = Math.round(hrRest + (z.max * hrr));
                results += `<div class="result-item"><div class="result-label">${z.name}</div><div class="result-value">${minHR}-${maxHR} bpm</div></div>`;
            });
            results += '</div>';
            showResults('heartRateResults', results);
        }

        function clampNumber(value, min, max) {
            return Math.min(max, Math.max(min, value));
        }

        function calculateHydration() {
            const durationMin = parseInt(document.getElementById('hydDuration').value) || 0;
            const replacePct = parseFloat(document.getElementById('hydReplacePct').value) || 0.6;
            const tempC = parseFloat(document.getElementById('hydTemp').value);
            const humidity = parseFloat(document.getElementById('hydHumidity').value);
            const sweatRateInput = parseFloat(document.getElementById('hydSweatRate').value);
            const saltiness = document.getElementById('hydSaltiness').value || 'normal';

            if (!durationMin || durationMin < 10) {
                showError('hydrationError', 'Durasi lari minimal 10 menit.');
                return;
            }

            const safeTempC = Number.isFinite(tempC) ? clampNumber(tempC, 0, 50) : 28;
            const safeHumidity = Number.isFinite(humidity) ? clampNumber(humidity, 0, 100) : 70;

            let sweatRateMlPerHour;
            if (Number.isFinite(sweatRateInput) && sweatRateInput > 0) {
                sweatRateMlPerHour = clampNumber(sweatRateInput, 200, 2500);
            } else {
                let estimated = 600;
                estimated += (safeTempC - 20) * 25;
                estimated += (safeHumidity - 50) * 5;
                if (durationMin >= 90) estimated += 50;
                sweatRateMlPerHour = clampNumber(estimated, 350, 1800);
            }

            const mgPerLiterMap = { low: 500, normal: 700, high: 900 };
            const sodiumMgPerLiter = mgPerLiterMap[saltiness] || 700;
            const sodiumMgPerHour = (sweatRateMlPerHour / 1000) * sodiumMgPerLiter;

            const recommendedMlPerHour = clampNumber(sweatRateMlPerHour * replacePct, 200, 1200);
            const totalMl = recommendedMlPerHour * (durationMin / 60);
            const totalSodiumMg = sodiumMgPerHour * (durationMin / 60);

            const toOz = (ml) => ml / 29.5735;
            const perHourText = globalUnit === 'imperial'
                ? `${Math.round(recommendedMlPerHour)} ml/jam (${toOz(recommendedMlPerHour).toFixed(0)} fl oz/jam)`
                : `${Math.round(recommendedMlPerHour)} ml/jam`;

            const totalText = globalUnit === 'imperial'
                ? `${Math.round(totalMl)} ml (${toOz(totalMl).toFixed(0)} fl oz)`
                : `${Math.round(totalMl)} ml`;

            const results = `
                <div class="result-grid">
                    <div class="result-item"><div class="result-label">Sweat Rate (estimasi)</div><div class="result-value">${Math.round(sweatRateMlPerHour)} ml/jam</div></div>
                    <div class="result-item"><div class="result-label">Target Intake</div><div class="result-value">${perHourText}</div></div>
                    <div class="result-item"><div class="result-label">Total Intake</div><div class="result-value">${totalText}</div></div>
                    <div class="result-item"><div class="result-label">Sodium (estimasi)</div><div class="result-value">${Math.round(sodiumMgPerHour)} mg/jam</div></div>
                    <div class="result-item"><div class="result-label">Total Sodium</div><div class="result-value">${Math.round(totalSodiumMg)} mg</div></div>
                </div>
            `;

            showResults('hydrationResults', results);
        }

        function calculateFuelingPlan() {
            const hours = parseInt(document.getElementById('fuelHours').value) || 0;
            const minutes = parseInt(document.getElementById('fuelMinutes').value) || 0;
            const seconds = parseInt(document.getElementById('fuelSeconds').value) || 0;
            const intensity = document.getElementById('fuelIntensity').value || 'race';
            const tolerance = document.getElementById('fuelTolerance').value || 'medium';
            const fuelType = document.getElementById('fuelType').value || 'gel';
            const intervalMin = parseInt(document.getElementById('fuelInterval').value) || 20;

            const totalSec = timeToSeconds(hours, minutes, seconds);
            if (!totalSec || totalSec < 20 * 60) {
                showError('fuelingError', 'Masukkan target durasi yang valid (minimal 20 menit).');
                return;
            }

            const durationHours = totalSec / 3600;

            let baseCarbs;
            if (durationHours < 1) baseCarbs = 25;
            else if (durationHours < 2) baseCarbs = 50;
            else if (durationHours < 3) baseCarbs = 70;
            else baseCarbs = 85;

            const intensityAdj = intensity === 'easy' ? -10 : intensity === 'tempo' ? 0 : 5;
            const toleranceAdj = tolerance === 'low' ? -15 : tolerance === 'high' ? 10 : 0;

            let carbsPerHour = clampNumber(baseCarbs + intensityAdj + toleranceAdj, 20, 95);
            const carbsPerInterval = carbsPerHour * (intervalMin / 60);

            const gelCarbs = 25;
            const drinkCarbsPer500 = 30;

            let guidance = '';
            if (fuelType === 'gel') {
                guidance = `≈ ${(carbsPerHour / gelCarbs).toFixed(1)} gel/jam (asumsi ${gelCarbs}g/gel)`;
            } else if (fuelType === 'drink') {
                const mlPerHour = (carbsPerHour / drinkCarbsPer500) * 500;
                guidance = `≈ ${Math.round(mlPerHour)} ml/jam sports drink (asumsi ${drinkCarbsPer500}g/500ml)`;
            } else {
                const gelHalf = (carbsPerHour * 0.5) / gelCarbs;
                const drinkHalfMl = ((carbsPerHour * 0.5) / drinkCarbsPer500) * 500;
                guidance = `≈ ${gelHalf.toFixed(1)} gel/jam + ${Math.round(drinkHalfMl)} ml/jam drink`;
            }

            const scheduleLines = [];
            for (let t = intervalMin; t < Math.ceil(totalSec / 60) + 0.1; t += intervalMin) {
                if (t > (totalSec / 60)) break;
                scheduleLines.push(`${formatTime(t * 60, true)} → ~${Math.round(carbsPerInterval)} g`);
            }

            const scheduleHtml = scheduleLines.length
                ? scheduleLines.join('<br>')
                : 'Durasi terlalu pendek untuk jadwal interval.';

            const results = `
                <div class="result-grid">
                    <div class="result-item"><div class="result-label">Durasi</div><div class="result-value">${formatTime(totalSec, true)}</div></div>
                    <div class="result-item"><div class="result-label">Rekomendasi Karbo</div><div class="result-value">${Math.round(carbsPerHour)} g/jam</div></div>
                    <div class="result-item"><div class="result-label">Per ${intervalMin} menit</div><div class="result-value">~${Math.round(carbsPerInterval)} g</div></div>
                    <div class="result-item"><div class="result-label">Panduan Praktis</div><div class="result-value">${guidance}</div></div>
                    <div class="result-item" style="align-items:flex-start;">
                        <div class="result-label">Jadwal</div>
                        <div class="result-value" style="text-align:right;white-space:normal;line-height:1.35;">${scheduleHtml}</div>
                    </div>
                </div>
            `;

            showResults('fuelingResults', results);
        }
        
        let smChartInstance = null;

        function updateSmartMileageInputs() {
            const mode = document.getElementById('smMode').value;
            const label = document.getElementById('smCurrentLabel');
            const input = document.getElementById('smCurrent');
            
            if (mode === 'time') {
                label.textContent = 'Current Weekly Duration (Minutes)';
                input.placeholder = 'e.g., 180';
            } else {
                label.textContent = `Current Weekly Volume (${globalUnit === 'metric' ? 'km' : 'miles'})`;
                input.placeholder = 'e.g., 20';
            }
        }

        function calculateSmartSuggestion() {
            const dist = parseFloat(document.getElementById('smPbDist').value);
            const h = parseInt(document.getElementById('smPbH').value) || 0;
            const m = parseInt(document.getElementById('smPbM').value) || 0;
            const s = parseInt(document.getElementById('smPbS').value) || 0;
            
            const totalMinutes = (h * 60) + m + (s / 60);
            
            if (totalMinutes <= 0) {
                alert('Mohon masukkan waktu PB yang valid.');
                return;
            }
            
            let suggestedVol = 0;
            
            // Logic Heuristics (Upper Bound / Aggressive Start)
            // User feedback: "gunakan batas atasnya saja"
            if (dist === 5) {
                if (totalMinutes < 20) suggestedVol = 50;      // Was 35
                else if (totalMinutes < 25) suggestedVol = 40; // Was 25
                else if (totalMinutes < 30) suggestedVol = 30; // Was 20
                else suggestedVol = 25;                        // Was 15
            } else if (dist === 10) {
                if (totalMinutes < 40) suggestedVol = 65;      // Was 45
                else if (totalMinutes < 50) suggestedVol = 50; // Was 35
                else if (totalMinutes < 60) suggestedVol = 40; // Was 25
                else suggestedVol = 30;                        // Was 20
            } else if (dist === 21.1) {
                if (totalMinutes < 90) suggestedVol = 80;      // Was 60
                else if (totalMinutes < 105) suggestedVol = 65;// Was 45
                else if (totalMinutes < 120) suggestedVol = 50;// Was 35
                else suggestedVol = 40;                        // Was 25
            } else if (dist === 42.2) {
                if (totalMinutes < 180) suggestedVol = 100;    // Was 80
                else if (totalMinutes < 210) suggestedVol = 80;// Was 60
                else if (totalMinutes < 240) suggestedVol = 65;// Was 50
                else suggestedVol = 50;                        // Was 40
            }
            
            // Adjust for imperial if needed
            if (globalUnit === 'imperial') {
                suggestedVol = Math.round(suggestedVol * 0.621371);
            }
            
            // Set value
            const currentInput = document.getElementById('smCurrent');
            currentInput.value = suggestedVol;
            
            // Auto-set mode to distance since this calculation is distance-based
            const modeSelect = document.getElementById('smMode');
            if (modeSelect.value !== 'distance') {
                modeSelect.value = 'distance';
                updateSmartMileageInputs();
            }
            
            // Visual Feedback
            currentInput.style.transition = 'all 0.3s';
            currentInput.style.borderColor = '#10b981';
            currentInput.style.boxShadow = '0 0 0 4px rgba(16, 185, 129, 0.2)';
            
            // Show toast or alert
            // Simple approach: remove highlight after 2s
            setTimeout(() => {
                currentInput.style.borderColor = '';
                currentInput.style.boxShadow = '';
            }, 2000);
            
            // Scroll to input
            currentInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function updateSmartMileageSlider() {
            const slider = document.getElementById('smSlider');
            const output = document.getElementById('smSliderVal');
            const label = document.getElementById('smAggressivenessLabel');
            const injury = document.getElementById('smInjury');
            
            let val = parseInt(slider.value);
            
            // Injury Logic: Cap at 7%
            if (injury.checked && val > 7) {
                slider.value = 7;
                val = 7;
            }
            
            output.textContent = val + '%';
            
            if (val < 8) {
                label.textContent = 'Conservative / Rehab';
                label.style.background = '#10b981'; // Green
                output.style.color = '#10b981';
            } else if (val >= 8 && val <= 12) {
                label.textContent = 'Standard Progressive';
                label.style.background = '#3b82f6'; // Blue
                output.style.color = '#3b82f6';
            } else {
                label.textContent = 'Aggressive / High Risk';
                label.style.background = '#ef4444'; // Red
                output.style.color = '#ef4444';
            }
        }

        function calculateSmartMileage() {
            const current = parseFloat(document.getElementById('smCurrent').value);
            const duration = parseInt(document.getElementById('smDuration').value);
            const pct = parseInt(document.getElementById('smSlider').value) / 100;
            const injury = document.getElementById('smInjury').checked;
            const mode = document.getElementById('smMode').value;
            
            if (!current || current <= 0) {
                showError('smartMileageError', 'Please enter a valid current volume.');
                return;
            }
            
            const cutbackFreq = injury ? 3 : 4; // Cutback every 3 weeks if injured, else 4
            const cutbackRate = 0.25; // 25% volume reduction
            
            // Rational Cap (User feedback: "tidak bisa lebih dari 200km")
            // Assuming 200km is the hard cap for metric. For imperial ~125 miles.
            const maxVolume = globalUnit === 'metric' ? 200 : 125;

            let plan = [];
            let volume = current;
            let peakVolume = current; // Track highest volume before cutback
            
            // Week 1 is current volume (or maybe week 1 starts higher? Usually start from current)
            // User input is "Current Weekly Volume", so Week 1 should probably be Current * (1+pct) or just Current?
            // Usually "Current" means what I did last week. So Week 1 is Current * (1+pct).
            // Let's assume Week 1 is the first build week.
            
            // Re-reading user prompt: "Minggu 1-3: Naik sesuai % slider."
            // This implies starting from Week 1.
            
            // Let's initialize previous volume as 'current'.
            let prevVolume = current;
            
            for (let w = 1; w <= duration; w++) {
                let weekVol = 0;
                let isCutback = false;
                
                if (w % cutbackFreq === 0) {
                    // Cutback Week
                    isCutback = true;
                    // "Turunkan volume sekitar 20-30% dari Minggu 3" (which is peakVolume)
                    weekVol = peakVolume * (1 - cutbackRate);
                } else {
                    // Build Week
                    if (w > 1 && (w - 1) % cutbackFreq === 0) {
                        // Post-Cutback Week: "Kembali ke volume Minggu 3 + % kenaikan"
                        // Meaning: Go back to Peak Volume + increase
                        weekVol = peakVolume * (1 + pct);
                    } else {
                        // Normal increase
                        weekVol = prevVolume * (1 + pct);
                    }
                    
                    // Apply Cap
                    if (weekVol > maxVolume) weekVol = maxVolume;
                    
                    peakVolume = weekVol;
                }
                
                // Also cap cutback weeks just in case (though unlikely to exceed if peak is capped)
                if (weekVol > maxVolume) weekVol = maxVolume;

                plan.push({
                    week: w,
                    volume: weekVol,
                    isCutback: isCutback
                });
                prevVolume = weekVol;
            }
            
            renderSmartMileageChart(plan, mode);
            renderSmartMileageTable(plan, mode);
            
            document.getElementById('smartMileageError').style.display = 'none';
            document.getElementById('smartMileageResults').classList.add('show');
            document.getElementById('smartMileageExportBtn').style.display = 'inline-flex';
            document.getElementById('smartMileageIcsBtn').style.display = 'inline-flex';
            
            // Scroll to results
             setTimeout(() => {
                document.getElementById('smartMileageResults').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 60);
        }

        function renderSmartMileageChart(plan, mode) {
            const ctx = document.getElementById('smChart').getContext('2d');
            
            if (smChartInstance) {
                smChartInstance.destroy();
            }
            
            const labels = plan.map(p => `Week ${p.week}`);
            const data = plan.map(p => p.volume.toFixed(1));
            const colors = plan.map(p => p.isCutback ? '#10b981' : '#3b82f6'); // Green for cutback, Blue for build
            
            const unitLabel = mode === 'time' ? 'Minutes' : (globalUnit === 'metric' ? 'km' : 'miles');
            
            smChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: `Weekly Volume (${unitLabel})`,
                        data: data,
                        backgroundColor: colors,
                        borderRadius: 4,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148,163,184,0.1)' },
                            ticks: { color: '#94a3b8' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#94a3b8' }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.9)',
                            titleColor: '#fff',
                            bodyColor: '#cbd5e1',
                            callbacks: {
                                label: function(context) {
                                    return `${context.parsed.y} ${unitLabel} ${plan[context.dataIndex].isCutback ? '(Cutback)' : ''}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function renderSmartMileageTable(plan, mode) {
            const unit = mode === 'time' ? 'min' : (globalUnit === 'metric' ? 'km' : 'mi');
            
            let html = '<div class="result-grid" style="grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));">';
            
            plan.forEach(p => {
                const color = p.isCutback ? '#10b981' : 'rgba(226,232,240,.82)';
                const label = p.isCutback ? 'Cutback' : 'Build';
                
                html += `
                    <div class="result-item" style="flex-direction:column; align-items:flex-start; gap:0.2rem;">
                        <div class="result-label" style="font-size:0.75rem;">Week ${p.week}</div>
                        <div class="result-value" style="font-size:1.1rem; color:${p.isCutback ? '#10b981' : '#fff'};">
                            ${Math.round(p.volume)} <span style="font-size:0.8rem; color:var(--muted);">${unit}</span>
                        </div>
                        <div style="font-size:0.7rem; color:${color}; opacity:0.8;">${label}</div>
                    </div>
                `;
            });
            
            html += '</div>';
            document.getElementById('smTable').innerHTML = html;
        }

        function exportSmartMileageICS() {
            const startDateVal = document.getElementById('smStartDate').value;
            if (!startDateVal) {
                alert('Silakan pilih Start Date untuk export ke kalender.');
                return;
            }
            
            // Retrieve plan data (we need to re-calculate or store it globally. Re-calc is safer/easier here)
            // Copy logic from calculateSmartMileage
             const current = parseFloat(document.getElementById('smCurrent').value);
            const duration = parseInt(document.getElementById('smDuration').value);
            const pct = parseInt(document.getElementById('smSlider').value) / 100;
            const injury = document.getElementById('smInjury').checked;
            const mode = document.getElementById('smMode').value;
            const goal = document.getElementById('smGoal').value || 'Smart Mileage Builder';
            
            const cutbackFreq = injury ? 3 : 4;
            const cutbackRate = 0.25;
            
            let volume = current;
            let peakVolume = current;
            let prevVolume = current;
            
            let icsContent = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Ruang Lari//Smart Mileage//EN\nCALSCALE:GREGORIAN\n";
            
            let currentDate = new Date(startDateVal);
            
            const unit = mode === 'time' ? 'min' : (globalUnit === 'metric' ? 'km' : 'mi');
            
            for (let w = 1; w <= duration; w++) {
                let weekVol = 0;
                let isCutback = false;
                
                if (w % cutbackFreq === 0) {
                    isCutback = true;
                    weekVol = peakVolume * (1 - cutbackRate);
                } else {
                    if (w > 1 && (w - 1) % cutbackFreq === 0) {
                        weekVol = peakVolume * (1 + pct);
                    } else {
                        weekVol = prevVolume * (1 + pct);
                    }
                    peakVolume = weekVol;
                }
                
                // Format Date for ICS: YYYYMMDD
                const dStart = currentDate.toISOString().replace(/-/g, '').split('T')[0];
                
                // End date is +6 days (Sunday) or +1 day (Event usually marks the start of the week)
                // Let's make it an all-day event for the Monday
                // DTEND for all-day event is the next day
                let nextDay = new Date(currentDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const dEnd = nextDay.toISOString().replace(/-/g, '').split('T')[0];
                
                const type = isCutback ? 'Recovery Week' : 'Build Week';
                const desc = `Target: ${Math.round(weekVol)} ${unit}. Focus: ${type}. Goal: ${goal}`;
                
                icsContent += "BEGIN:VEVENT\n";
                icsContent += `DTSTART;VALUE=DATE:${dStart}\n`;
                icsContent += `DTEND;VALUE=DATE:${dEnd}\n`;
                icsContent += `SUMMARY:Week ${w} Run Volume: ${Math.round(weekVol)} ${unit}\n`;
                icsContent += `DESCRIPTION:${desc}\n`;
                icsContent += "END:VEVENT\n";
                
                prevVolume = weekVol;
                
                // Move to next week
                currentDate.setDate(currentDate.getDate() + 7);
            }
            
            icsContent += "END:VCALENDAR";
            
            // Download
            const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.setAttribute('download', 'smart_mileage_plan.ics');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to today
            document.getElementById('smStartDate').valueAsDate = new Date();

            setDefaultDistance();
            updateAllLabels();
            updatePacePerUnitLabel();
            updateSplitPercentageValue();
            updateSplitDistance();
            updateRecentRaceDistance();
            updateTargetRaceDistance();
            updatePaceDistanceLabel();
            
            const paceInputs = ['paceHours','paceMinutes','paceSeconds','pacePerUnitMinutes','pacePerUnitSeconds','paceDistance','paceUnit','globalUnit'];
            paceInputs.forEach(function(id) {
                const el = document.getElementById(id);
                if (!el) return;
                const handler = function() {
                    if (id === 'globalUnit') {
                        updateAllLabels();
                        updatePacePerUnitLabel();
                    }
                    if (id === 'paceUnit') updatePaceDistanceLabel();
                    const changedGroup = (id.startsWith('pacePerUnit') ? 'pace' : (id.startsWith('pace') && (id.endsWith('Hours') || id.endsWith('Minutes') || id.endsWith('Seconds')) ? 'time' : (id === 'paceDistance' || id === 'paceUnit' ? 'distance' : null)));
                    if (changedGroup) syncPaceTriplet(changedGroup);
                };
                el.addEventListener('input', handler);
                el.addEventListener('change', handler);
            });
            
            const hash = window.location.hash.substring(1);
            if (hash) {
                openTabByHash(hash);
            }
        });
        
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                openTabByHash(hash);
            }
        });
    </script>
@endpush
