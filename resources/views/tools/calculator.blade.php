@extends('layouts.pacerhub')

@section('title', 'Kalkulator Lari Terlengkap | Ruang Lari Tools')
@section('meta_title', 'Kalkulator Lari Terlengkap: Pace, VO2 Max, Marathon, Magic Mile | Ruang Lari')
@section('meta_description', 'Gunakan Kalkulator Ruang Lari untuk menghitung Pace, VO2 Max, Magic Mile, Prediksi Waktu Marathon, Splits, Kebutuhan Hidrasi, Fueling Karbohidrat, hingga Smart Mileage Builder.')
@section('meta_keywords', 'kalkulator lari, kalkulator pace, kalkulator vo2 max, prediksi marathon, magic mile, kalkulator split lari, smart mileage builder, kalkulator hidrasi pelari, kalkulator fueling marathon, ruang lari tools')
@section('og_title', 'Kalkulator Lari Terlengkap | Ruang Lari Tools')
@section('og_description', 'Hitung Pace, VO2 Max, Magic Mile, Waktu Marathon, Hidrasi, dan Fueling Karbohidrat secara akurat menggunakan Kalkulator Ruang Lari.')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    #rl-calculator{
        --bg:#07101c;--surface:#0b1522;--soft:#0f1b2a;--line:rgba(255,255,255,.12);
        --line2:rgba(255,255,255,.22);--text:#f8fafc;--muted:#cbd5e1;--accent:#b8ff00;
        --blue:#60a5fa;--green:#34d399;--amber:#fbbf24;--red:#fb7185;
        min-height:100vh;background:radial-gradient(circle at 88% 0%,rgba(184,255,0,.045),transparent 30rem),var(--bg);
        color:var(--text);font-variant-numeric:tabular-nums;
    }
    #rl-calculator *{box-sizing:border-box}
    #rl-calculator .rlc-wrap{max-width:1180px;margin:auto;padding:2.25rem 1rem 5rem}
    #rl-calculator .rlc-header{padding:1.8rem 0 2rem;border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
    #rl-calculator .rlc-kicker{display:flex;align-items:center;gap:.7rem;color:var(--accent);font-size:10px;font-weight:900;letter-spacing:.18em;text-transform:uppercase}
    #rl-calculator .rlc-kicker:before{content:"";width:30px;height:2px;background:var(--accent)}
    #rl-calculator .rlc-header h1{margin:.65rem 0 0;font-size:clamp(2.7rem,6vw,5.2rem);line-height:.88;letter-spacing:-.06em;font-weight:900;text-transform:uppercase;color:#fff}
    #rl-calculator .rlc-header p{max-width:45rem;margin:.95rem 0 0;color:#cbd5e1;font-size:12px;line-height:1.65}
    #rl-calculator .rlc-system{display:grid;grid-template-columns:1fr minmax(250px,330px);gap:1rem;align-items:center;margin:1rem 0;padding:.9rem 1rem;border:1px solid var(--line);background:rgba(11,21,34,.9)}
    #rl-calculator .eyebrow{font-size:10px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;color:#e2e8f0}
    #rl-calculator .system-copy{margin-top:.28rem;font-size:11px;color:#94a3b8}
    #rl-calculator .rlc-shell{border:1px solid var(--line);background:rgba(11,21,34,.9)}
    #rl-calculator .tab-navigation{position:sticky;top:76px;z-index:20;display:flex;gap:1.45rem;overflow-x:auto;padding:0 1.2rem;border-bottom:1px solid var(--line);background:rgba(11,21,34,.98);backdrop-filter:blur(12px);scrollbar-width:none}
    #rl-calculator .tab-navigation::-webkit-scrollbar{display:none}
    #rl-calculator .tab-btn{position:relative;flex:0 0 auto;min-height:56px;padding:0;border:0;background:none;color:#94a3b8;font-size:10px;font-weight:900;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;transition:color .15s ease}
    #rl-calculator .tab-btn:after{content:"";position:absolute;left:0;right:0;bottom:-1px;height:2px;background:transparent}
    #rl-calculator .tab-btn:hover{color:#ffffff}
    #rl-calculator .tab-btn.active{color:#ffffff}
    #rl-calculator .tab-btn.active:after{background:var(--accent)}
    #rl-calculator .tab-content{display:none;max-width:860px;margin:auto;padding:2rem 1.25rem 2.5rem;scroll-margin-top:145px}
    #rl-calculator .tab-content.active{display:block}
    #rl-calculator .info-note{margin:0 0 1.8rem;padding:.85rem 1rem;border-left:3px solid var(--accent);background:rgba(255,255,255,.03);color:#f1f5f9;font-size:12px;line-height:1.6}
    #rl-calculator .form-row{display:grid;grid-template-columns:1fr;gap:1rem}
    #rl-calculator .form-group{margin-bottom:1.15rem}
    #rl-calculator label{display:block;margin-bottom:.45rem;color:#f1f5f9;font-size:11px;font-weight:700;letter-spacing:.02em}
    #rl-calculator input:not([type=checkbox]):not([type=range]),#rl-calculator select{
        width:100%;height:46px;padding:0 .85rem;border:1px solid rgba(255,255,255,.18);border-radius:3px;background:rgba(2,6,13,.7);color:#ffffff;font-size:12px;font-weight:700;outline:none;transition:border-color .15s ease,box-shadow .15s ease;
    }
    #rl-calculator input:focus,#rl-calculator select:focus{border-color:rgba(184,255,0,.7);box-shadow:0 0 0 3px rgba(184,255,0,.08)}
    #rl-calculator input::placeholder{color:#94a3b8}
    #rl-calculator input:disabled{opacity:.55;color:#94a3b8}
    #rl-calculator input[type=checkbox],#rl-calculator input[type=range]{accent-color:var(--accent)}
    #rl-calculator .time-inputs{display:grid;grid-template-columns:repeat(3,1fr);gap:.45rem}
    #rl-calculator .time-inputs input{text-align:center;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
    #rl-calculator .rlc-action{min-width:210px;height:44px;padding:0 1.15rem;border:1px solid var(--accent);border-radius:3px;background:var(--accent);color:#07101c;font-size:10px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;cursor:pointer}
    #rl-calculator .rlc-action:hover{background:#d2ff65}
    #rl-calculator .results{display:none;margin-top:1.7rem;padding-top:1rem;border-top:1px solid var(--line);scroll-margin-top:145px}
    #rl-calculator .results.show{display:block}
    #rl-calculator .result-grid{display:grid}
    #rl-calculator .result-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:1.4rem;align-items:center;padding:.9rem 0;border-bottom:1px solid var(--line)}
    #rl-calculator .result-label{color:#cbd5e1;font-size:11px;font-weight:700}
    #rl-calculator .result-value{color:#ffffff;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:15px;font-weight:900;text-align:right}
    #rl-calculator .error{display:none;margin-top:1rem;padding:.8rem 1rem;border:1px solid rgba(251,113,133,.3);border-left:3px solid var(--red);background:rgba(251,113,133,.08);color:#fecdd3;font-size:11px;font-weight:600}
    #rl-calculator .rlc-export-btn{display:none;min-height:40px;margin-top:1rem;padding:0 .9rem;border:1px solid var(--line2);border-radius:3px;background:none;color:#e2e8f0;font-size:10px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;cursor:pointer}
    #rl-calculator .rlc-export-btn:hover{border-color:rgba(255,255,255,.4);color:#ffffff;background:rgba(255,255,255,.04)}
    #rl-calculator .subpanel{margin-bottom:1.5rem;padding:1.15rem;border:1px solid var(--line);background:rgba(255,255,255,.02)}
    #rl-calculator .subpanel-head{display:flex;justify-content:space-between;gap:1rem;padding-bottom:.7rem;margin-bottom:.8rem;border-bottom:1px solid var(--line)}
    #rl-calculator .subpanel-title{color:#93c5fd;font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    #rl-calculator .subpanel-copy{font-size:11px;line-height:1.55;color:#cbd5e1}
    #rl-calculator .secondary-action{width:100%;min-height:40px;margin-top:.65rem;border:1px solid rgba(96,165,250,.35);border-radius:3px;background:rgba(96,165,250,.06);color:#93c5fd;font-size:11px;font-weight:800;cursor:pointer}
    #rl-calculator .secondary-action:hover{background:rgba(96,165,250,.12);color:#bfdbfe}
    #rl-calculator .risk-row{display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:center}
    #rl-calculator .risk-badge{padding:.25rem .5rem;background:#10b981;color:#fff;font-size:9px;font-weight:900;border-radius:2px}
    #rl-calculator .checkbox-line{display:flex;align-items:center;gap:.55rem;cursor:pointer;color:#f1f5f9;font-size:11px;font-weight:700}
    #rl-calculator .checkbox-line input{width:auto}
    #smartMileageResults canvas{max-height:300px;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--line)}
    @media(min-width:768px){#rl-calculator .form-row{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:767px){
        #rl-calculator .rlc-wrap{padding:1.25rem .85rem 3rem}
        #rl-calculator .rlc-system{grid-template-columns:1fr}
        #rl-calculator .tab-navigation{top:0}
        #rl-calculator .rlc-action{width:100%}
        #rl-calculator .result-value{font-size:13px}
    }
</style>
@endpush

@section('content')
@if(request()->has('embed'))
<style>
    #ph-sidebar,#pacerhub-nav,#chatbox-toggle,header{display:none!important}
    #main-content-wrapper{padding-left:0!important;padding-top:0!important;margin:0!important}
    body{padding-top:0!important;background:transparent!important}
    main{padding-top:0!important;margin:0!important}
    #rl-calculator{padding-top:0!important}
    #rl-calculator .rlc-header{display:none!important}
</style>
<script>
(function(){
    if(window.parent&&window.parent!==window){
        const report=()=>window.parent.postMessage({type:'resize-iframe-calculator',height:document.documentElement.scrollHeight||document.body.scrollHeight},'*');
        window.addEventListener('load',report);window.addEventListener('resize',report);setInterval(report,400);
    }
})();
</script>
@endif

<div id="rl-calculator" class="w-full pt-10">
<div class="rlc-wrap">
    <header class="rlc-header">
        <div class="rlc-kicker">RuangLari / Performance Lab</div>
        <h1>Running Lab.</h1>
        <p>Pace, race prediction, training intensity, hydration, fueling, heart rate, dan mileage planning untuk keputusan latihan yang lebih terukur.</p>
    </header>

    <section class="rlc-system">
        <div>
            <div class="eyebrow">Measurement system</div>
            <div class="system-copy">Satu sistem unit untuk seluruh kalkulator.</div>
        </div>
        <select id="globalUnit" onchange="updateGlobalUnit()">
            <option value="metric">Metric — km · min/km · km/h</option>
            <option value="imperial">Imperial — miles · min/mile · mph</option>
        </select>
    </section>

    <section class="rlc-shell">
        <nav class="tab-navigation">
            <button class="tab-btn" onclick="openTab(event,'magicMile')">Magic Mile</button>
            <button class="tab-btn" onclick="openTab(event,'marathon')">Marathon</button>
            <button class="tab-btn active" onclick="openTab(event,'pace')">Pace</button>
            <button class="tab-btn" onclick="openTab(event,'predictor')">Predictor</button>
            <button class="tab-btn" onclick="openTab(event,'improvement')">Improvement</button>
            <button class="tab-btn" onclick="openTab(event,'splits')">Splits</button>
            <button class="tab-btn" onclick="openTab(event,'steps')">Steps</button>
            <button class="tab-btn" onclick="openTab(event,'stride')">Stride</button>
            <button class="tab-btn" onclick="openTab(event,'training')">Training</button>
            <button class="tab-btn" onclick="openTab(event,'hydration')">Hydration</button>
            <button class="tab-btn" onclick="openTab(event,'fueling')">Fueling</button>
            <button class="tab-btn" onclick="openTab(event,'vo2max')">VO2 Max</button>
            <button class="tab-btn" onclick="openTab(event,'heartrate')">Heart Rate</button>
            <button class="tab-btn" onclick="openTab(event,'smartMileage')">Mileage Builder</button>
        </nav>

        <div id="pace" class="tab-content active" data-hash="pacecalculator">
            <div class="info-note">Hitung pace berdasarkan jarak dan waktu lari Anda.</div>
            <div class="form-row">
                <div class="form-group"><label>Unit Jarak</label><select id="paceUnit" onchange="updatePaceDistanceLabel()"><option value="meter">Meter (m)</option><option value="km">Kilometer (km)</option></select></div>
                <div class="form-group"><label id="paceDistanceLabel">Distance (m)</label><input type="number" id="paceDistance" step="0.1" min="0.1" placeholder="e.g. 5000"></div>
            </div>
            <div class="form-group"><label>Time</label><div class="time-inputs"><input type="number" id="paceHours" placeholder="Hours" min="0"><input type="number" id="paceMinutes" placeholder="Min" min="0"><input type="number" id="paceSeconds" placeholder="Sec" min="0"></div></div>
            <div class="form-group"><label>Pace (<span id="pacePerUnitLabel">min/km</span>)</label><div class="time-inputs"><input type="number" id="pacePerUnitMinutes" placeholder="Min" min="0"><input type="number" id="pacePerUnitSeconds" placeholder="Sec" min="0" max="59"><input disabled value="per unit"></div></div>
            <button class="rlc-action" onclick="calculatePace()">Calculate Pace</button>
            <div id="paceError" class="error"></div><div id="paceResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="paceExportBtn" onclick="exportResults('paceResults','Pace Calculator')">Export PNG</button>
        </div>

        <div id="magicMile" class="tab-content" data-hash="magicmile">
            <div class="info-note">Prediksi race time berdasarkan benchmark lari 1 mile.</div>
            <div class="form-group"><label>Magic Mile Time</label><div class="time-inputs"><input type="number" id="magicHours" placeholder="Hours" min="0"><input type="number" id="magicMinutes" placeholder="Min" min="0"><input type="number" id="magicSeconds" placeholder="Sec" min="0" max="59"></div></div>
            <button class="rlc-action" onclick="calculateMagicMile()">Calculate Predictions</button>
            <div id="magicMileError" class="error"></div><div id="magicMileResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="magicMileExportBtn" onclick="exportResults('magicMileResults','Magic Mile')">Export PNG</button>
        </div>

        <div id="marathon" class="tab-content" data-hash="marathon">
            <div class="info-note">Hitung pace yang dibutuhkan untuk target waktu marathon 42.195 km.</div>
            <div class="form-group"><label>Target Marathon Time</label><div class="time-inputs"><input type="number" id="marathonHours" placeholder="Hours" min="0"><input type="number" id="marathonMinutes" placeholder="Min" min="0" max="59"><input type="number" id="marathonSeconds" placeholder="Sec" min="0" max="59"></div></div>
            <button class="rlc-action" onclick="calculateMarathonPace()">Calculate Marathon Pace</button>
            <div id="marathonError" class="error"></div><div id="marathonResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="marathonExportBtn" onclick="exportResults('marathonResults','Marathon Pace')">Export PNG</button>
        </div>

        <div id="predictor" class="tab-content" data-hash="racepredictor">
            <div class="info-note">Prediksi waktu race menggunakan Riegel's Formula dari performa race sebelumnya.</div>
            <div class="form-row">
                <div class="form-group"><label id="recentRaceDistanceLabel">Recent Race Distance (km)</label><select id="recentRaceDistanceSelect" onchange="updateRecentRaceDistance()"><option value="custom">Custom</option><option value="5">5K</option><option value="10">10K</option><option value="21.1">Half Marathon</option><option value="42.2">Marathon</option></select><input type="number" id="recentRaceDistance" step="0.1" min="0.1" placeholder="e.g. 10" class="mt-2"></div>
                <div class="form-group"><label id="targetRaceDistanceLabel">Target Race Distance (km)</label><select id="targetRaceDistanceSelect" onchange="updateTargetRaceDistance()"><option value="custom">Custom</option><option value="5">5K</option><option value="10">10K</option><option value="21.1">Half Marathon</option><option value="42.2">Marathon</option></select><input type="number" id="targetRaceDistance" step="0.1" min="0.1" placeholder="e.g. 21.1" class="mt-2"></div>
            </div>
            <div class="form-group"><label>Recent Race Time</label><div class="time-inputs"><input type="number" id="recentRaceHours" placeholder="Hours"><input type="number" id="recentRaceMinutes" placeholder="Min"><input type="number" id="recentRaceSeconds" placeholder="Sec"></div></div>
            <button class="rlc-action" onclick="calculateRacePredictor()">Predict Race Time</button>
            <div id="racePredictorError" class="error"></div><div id="racePredictorResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="racePredictorExportBtn" onclick="exportResults('racePredictorResults','Race Predictor')">Export PNG</button>
        </div>

        <div id="improvement" class="tab-content" data-hash="improvement">
            <div class="info-note">Ukur improvement yang dibutuhkan untuk mencapai target race time.</div>
            <div class="form-group"><label id="improvementDistanceLabel">Race Distance (km)</label><input type="number" id="improvementDistance" step="0.1" min="0.1" placeholder="e.g. 10"></div>
            <div class="form-row"><div class="form-group"><label>Current Best Time</label><div class="time-inputs"><input id="currentBestHours" type="number" placeholder="H"><input id="currentBestMinutes" type="number" placeholder="M"><input id="currentBestSeconds" type="number" placeholder="S"></div></div><div class="form-group"><label>Target Time</label><div class="time-inputs"><input id="targetTimeHours" type="number" placeholder="H"><input id="targetTimeMinutes" type="number" placeholder="M"><input id="targetTimeSeconds" type="number" placeholder="S"></div></div></div>
            <button class="rlc-action" onclick="calculateImprovement()">Calculate Improvement</button>
            <div id="improvementError" class="error"></div><div id="improvementResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="improvementExportBtn" onclick="exportResults('improvementResults','Improvement')">Export PNG</button>
        </div>

        <div id="splits" class="tab-content" data-hash="splits">
            <div class="info-note">Buat split plan untuk strategi even, negative, atau positive split.</div>
            <div class="form-row">
                <div class="form-group"><label id="splitDistanceLabel">Total Distance (km)</label><select id="splitDistanceSelect" onchange="updateSplitDistance()"><option value="custom">Custom</option><option value="5">5K</option><option value="10">10K</option><option value="21.1">Half Marathon</option><option value="42.2">Marathon</option></select><input id="splitDistance" type="number" step=".1" min=".1" class="mt-2"></div>
                <div class="form-group"><label id="splitIntervalLabel">Split Interval (km)</label><input id="splitInterval" type="number" step=".1" min=".1" value="1"></div>
            </div>
            <div class="form-group"><label>Target Total Time</label><div class="time-inputs"><input id="splitHours" type="number" placeholder="H"><input id="splitMinutes" type="number" placeholder="M"><input id="splitSeconds" type="number" placeholder="S"></div></div>
            <div class="form-row"><div class="form-group"><label>Strategy</label><select id="splitStrategy" onchange="updateSplitStrategy()"><option value="even">Even</option><option value="negative">Negative</option><option value="positive">Positive</option></select></div><div class="form-group" id="splitPercentageGroup" style="display:none"><label>Pace Change / Split</label><input type="range" id="splitPercentage" min="0" max="10" step=".1" value="2" oninput="updateSplitPercentageValue()"><div id="splitPercentageValue" class="mt-2 text-xs text-white/60">2.0%</div></div></div>
            <button class="rlc-action" onclick="calculateSplits()">Calculate Splits</button>
            <div id="splitError" class="error"></div><div id="splitResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="splitExportBtn" onclick="exportResults('splitResults','Splits')">Export PNG</button>
        </div>

        <div id="steps" class="tab-content" data-hash="steps">
            <div class="info-note">Konversi jumlah langkah menjadi jarak berdasarkan stride length.</div>
            <div class="form-row"><div class="form-group"><label>Number of Steps</label><input id="stepsCount" type="number" min="1"></div><div class="form-group"><label id="strideLengthLabel">Stride Length (cm)</label><input id="strideLength" type="number" step=".1" min="1" value="75"></div></div>
            <button class="rlc-action" onclick="calculateStepsToDistance()">Calculate Distance</button>
            <div id="stepsError" class="error"></div><div id="stepsResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="stepsExportBtn" onclick="exportResults('stepsResults','Steps to Distance')">Export PNG</button>
        </div>

        <div id="stride" class="tab-content" data-hash="stride">
            <div class="info-note">Hitung stride length dari jarak tempuh dan jumlah langkah.</div>
            <div class="form-row"><div class="form-group"><label id="strideDistanceLabel">Distance Covered (km)</label><input id="strideDistance" type="number" step=".01" min=".01"></div><div class="form-group"><label>Number of Steps</label><input id="strideStepsCount" type="number" min="1"></div></div>
            <button class="rlc-action" onclick="calculateStrideLength()">Calculate Stride Length</button>
            <div id="strideLengthError" class="error"></div><div id="strideLengthResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="strideLengthExportBtn" onclick="exportResults('strideLengthResults','Stride Length')">Export PNG</button>
        </div>

        <div id="training" class="tab-content" data-hash="training">
            <div class="info-note">Hitung training pace berdasarkan performa race dengan pendekatan VDOT.</div>
            <div class="form-row"><div class="form-group"><label>Base Pace Type</label><select id="basePaceType" onchange="setDefaultDistance()"><option value="5k">5K</option><option value="10k">10K</option><option value="half">Half Marathon</option><option value="marathon">Marathon</option><option value="threshold">Threshold</option></select></div><div class="form-group"><label id="trainingDistanceLabel">Best Race Distance (km)</label><input id="trainingDistance" type="number" step=".1" min=".1" value="5"></div></div>
            <div class="form-group"><label>Best Race Time</label><div class="time-inputs"><input id="bestTimeHours" type="number" placeholder="H"><input id="bestTimeMinutes" type="number" placeholder="M"><input id="bestTimeSeconds" type="number" placeholder="S"></div></div>
            <button class="rlc-action" onclick="calculateTrainingPaces()">Calculate Training Paces</button>
            <div id="trainingPaceError" class="error"></div><div id="trainingPaceResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="trainingPaceExportBtn" onclick="exportResults('trainingPaceResults','Training Paces')">Export PNG</button>
        </div>

        <div id="hydration" class="tab-content" data-hash="hydration">
            <div class="info-note">Estimasi kebutuhan cairan dan sodium untuk latihan atau race.</div>
            <div class="form-row"><div class="form-group"><label>Durasi (menit)</label><input id="hydDuration" type="number" min="10" max="600" value="60"></div><div class="form-group"><label>Target Replacement</label><select id="hydReplacePct"><option value=".5">50%</option><option value=".6" selected>60%</option><option value=".7">70%</option><option value=".8">80%</option></select></div></div>
            <div class="form-row"><div class="form-group"><label>Suhu (°C)</label><input id="hydTemp" type="number" min="0" max="50" value="28"></div><div class="form-group"><label>Kelembaban (%)</label><input id="hydHumidity" type="number" min="0" max="100" value="70"></div></div>
            <div class="form-row"><div class="form-group"><label>Sweat Rate (ml/jam)</label><input id="hydSweatRate" type="number" min="200" max="2500"></div><div class="form-group"><label>Saltiness</label><select id="hydSaltiness"><option value="low">Low</option><option value="normal" selected>Normal</option><option value="high">High</option></select></div></div>
            <button class="rlc-action" onclick="calculateHydration()">Calculate Hydration</button>
            <div id="hydrationError" class="error"></div><div id="hydrationResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="hydrationExportBtn" onclick="exportResults('hydrationResults','Hydration')">Export PNG</button>
        </div>

        <div id="fueling" class="tab-content" data-hash="fueling">
            <div class="info-note">Rencanakan asupan karbohidrat per jam dan interval konsumsi.</div>
            <div class="form-group"><label>Target Durasi</label><div class="time-inputs"><input id="fuelHours" type="number" value="2"><input id="fuelMinutes" type="number" value="0"><input id="fuelSeconds" type="number" value="0"></div></div>
            <div class="form-row"><div class="form-group"><label>Intensitas</label><select id="fuelIntensity"><option value="easy">Easy / Long Run</option><option value="tempo">Tempo</option><option value="race" selected>Race</option></select></div><div class="form-group"><label>GI Tolerance</label><select id="fuelTolerance"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></div></div>
            <div class="form-row"><div class="form-group"><label>Fuel Type</label><select id="fuelType"><option value="gel" selected>Gel</option><option value="drink">Sports Drink</option><option value="mix">Mix</option></select></div><div class="form-group"><label>Interval</label><select id="fuelInterval"><option value="15">15 min</option><option value="20" selected>20 min</option><option value="30">30 min</option></select></div></div>
            <button class="rlc-action" onclick="calculateFuelingPlan()">Calculate Fueling</button>
            <div id="fuelingError" class="error"></div><div id="fuelingResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="fuelingExportBtn" onclick="exportResults('fuelingResults','Fueling Plan')">Export PNG</button>
        </div>

        <div id="vo2max" class="tab-content" data-hash="vo2max">
            <div class="info-note">Estimasi VO2 Max dari performa race.</div>
            <div class="form-group"><label id="vo2DistanceLabel">Race Distance (km)</label><input id="vo2Distance" type="number" step=".1" min=".1"></div>
            <div class="form-group"><label>Race Time</label><div class="time-inputs"><input id="vo2Hours" type="number" placeholder="H"><input id="vo2Minutes" type="number" placeholder="M"><input id="vo2Seconds" type="number" placeholder="S"></div></div>
            <button class="rlc-action" onclick="calculateVO2Max()">Calculate VO2 Max</button>
            <div id="vo2Error" class="error"></div><div id="vo2Results" class="results"></div>
            <button type="button" class="rlc-export-btn" id="vo2ExportBtn" onclick="exportResults('vo2Results','VO2 Max')">Export PNG</button>
        </div>

        <div id="heartrate" class="tab-content" data-hash="heartrate">
            <div class="info-note">Estimasi heart-rate zones menggunakan HR reserve.</div>
            <div class="form-row"><div class="form-group"><label>Usia</label><input id="hrAge" type="number" min="10" max="100"></div><div class="form-group"><label>Resting HR</label><input id="hrRest" type="number" min="30" max="120"></div></div>
            <div class="form-row"><div class="form-group"><label>Gender</label><select id="hrGender"><option value="male">Laki-laki</option><option value="female">Perempuan</option></select></div><div class="form-group"><label>Fitness</label><select id="hrFitness"><option value="beginner">Pemula</option><option value="intermediate">Menengah</option><option value="advanced">Lanjutan</option><option value="athlete">Atlet</option></select></div></div>
            <div class="form-row"><div class="form-group"><label>Intensity</label><select id="hrIntensity"><option value="recovery">Recovery</option><option value="easy">Easy</option><option value="moderate">Moderate</option><option value="tempo">Tempo</option><option value="interval">Interval</option><option value="repetition">Repetition</option></select></div><div class="form-group"><label>Duration</label><input id="hrDuration" type="number" value="30"></div></div>
            <div class="form-row"><div class="form-group"><label>Temperature</label><input id="hrTemperature" type="number" value="25"></div><div class="form-group"><label>Humidity</label><input id="hrHumidity" type="number" value="60"></div></div>
            <button class="rlc-action" onclick="calculateHeartRateZones()">Calculate HR Zones</button>
            <div id="heartRateError" class="error"></div><div id="heartRateResults" class="results"></div>
            <button type="button" class="rlc-export-btn" id="heartRateExportBtn" onclick="exportResults('heartRateResults','Heart Rate Zones')">Export PNG</button>
        </div>

        <div id="smartMileage" class="tab-content" data-hash="smartmileage">
            <div class="info-note">Bangun progres mileage dengan build week dan cutback otomatis.</div>
            <div class="subpanel">
                <div class="subpanel-head"><div class="subpanel-title">Smart Recommendation</div><span class="eyebrow">Optional</span></div>
                <p class="subpanel-copy">Masukkan PB terbaru untuk estimasi volume awal mingguan.</p>
                <div class="form-row mt-4"><div class="form-group"><label>Recent Distance</label><select id="smPbDist"><option value="5">5K</option><option value="10">10K</option><option value="21.1">Half Marathon</option><option value="42.2">Marathon</option></select></div><div class="form-group"><label>Time</label><div class="time-inputs"><input id="smPbH" type="number" placeholder="H"><input id="smPbM" type="number" placeholder="M"><input id="smPbS" type="number" placeholder="S"></div></div></div>
                <button class="secondary-action" onclick="calculateSmartSuggestion()">Calculate Recommended Volume</button>
            </div>
            <div class="form-row"><div class="form-group"><label>Calculation Mode</label><select id="smMode" onchange="updateSmartMileageInputs()"><option value="distance">Distance</option><option value="time">Time</option></select></div><div class="form-group"><label id="smCurrentLabel">Current Weekly Volume</label><input id="smCurrent" type="number" min="0"></div></div>
            <div class="form-row"><div class="form-group"><label>Start Date</label><input id="smStartDate" type="date"></div><div class="form-group"><label>Program Duration (weeks)</label><input id="smDuration" type="number" min="4" max="52" value="16"></div></div>
            <div class="form-group"><label>Target Goal</label><select id="smGoal"><option>5K</option><option>10K</option><option>Half Marathon</option><option>Marathon</option><option>Ultra Marathon</option></select></div>
            <div class="subpanel">
                <div class="risk-row"><label class="mb-0">Progression Aggressiveness</label><span id="smAggressivenessLabel" class="risk-badge">Standard Progressive</span></div>
                <div class="mt-3 flex items-center gap-4"><input id="smSlider" class="flex-1" type="range" min="1" max="15" value="10" oninput="updateSmartMileageSlider()"><span id="smSliderVal" class="font-mono font-black text-[#B8FF00]">10%</span></div>
                <p class="subpanel-copy mt-3">&lt;8% conservative · 8–12% standard · &gt;13% aggressive.</p>
            </div>
            <div class="form-group"><label class="checkbox-line"><input id="smInjury" type="checkbox" onchange="updateSmartMileageSlider()"><span>Recovery / rehab mode</span></label><p class="subpanel-copy ml-6 mt-1">Membatasi progres ke 7% dan meningkatkan frekuensi cutback.</p></div>
            <button class="rlc-action" onclick="calculateSmartMileage()">Generate Smart Plan</button>
            <div id="smartMileageError" class="error"></div>
            <div id="smartMileageResults" class="results"><canvas id="smChart"></canvas><div id="smTable"></div></div>
            <div class="flex gap-2 mt-3"><button class="rlc-export-btn" id="smartMileageExportBtn" onclick="exportResults('smartMileageResults','Smart Mileage Plan')">Export PNG</button><button class="rlc-export-btn" id="smartMileageIcsBtn" onclick="exportSmartMileageICS()">Export Calendar</button></div>
        </div>
    </section>
</div>
</div>

<script>
window.globalUnit = 'metric';
window.smChartInstance = null;

window.el = function(id) { return document.getElementById(id); };
window.n = function(id) { return parseFloat(window.el(id)?.value) || 0; };
window.i = function(id) { return parseInt(window.el(id)?.value) || 0; };
window.clamp = function(v, min, max) { return Math.min(max, Math.max(min, v)); };
window.tsec = function(h, m, s) { return (h || 0) * 3600 + (m || 0) * 60 + (s || 0); };

window.formatTime = function(total, includeHours = true) {
    total = Math.max(0, Math.round(total || 0));
    const h = Math.floor(total / 3600), m = Math.floor((total % 3600) / 60), s = total % 60;
    return includeHours && h > 0 ? `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}` : `${m}:${String(s).padStart(2, '0')}`;
};

window.metricDistance = function(d) { return window.globalUnit === 'imperial' ? d * 1.60934 : d; };
window.displayDistance = function(d) { return window.globalUnit === 'imperial' ? d / 1.60934 : d; };

window.showError = function(id, msg) {
    const e = window.el(id); if (!e) return; e.textContent = msg; e.style.display = 'block';
    const r = window.el(id.replace('Error', 'Results')); if (r) r.classList.remove('show');
    const b = window.el(id.replace('Error', 'ExportBtn')); if (b) b.style.display = 'none';
};

window.showResults = function(id, html) {
    const err = window.el(id.replace('Results', 'Error')); if (err) err.style.display = 'none';
    const r = window.el(id); if (!r) return; r.innerHTML = html; r.classList.add('show');
    const b = window.el(id.replace('Results', 'ExportBtn')); if (b) b.style.display = 'inline-flex';
    setTimeout(() => r.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
};

window.rows = function(items) {
    return `<div class="result-grid">${items.map(([a, b]) => `<div class="result-item"><div class="result-label">${a}</div><div class="result-value">${b}</div></div>`).join('')}</div>`;
};

window.updateGlobalUnit = function() {
    window.globalUnit = window.el('globalUnit').value;
    window.updateAllLabels();
    window.updatePacePerUnitLabel();
    window.updateSmartMileageInputs();
};

window.updateAllLabels = function() {
    const unit = window.globalUnit === 'metric' ? 'km' : 'miles';
    ['recentRaceDistanceLabel', 'targetRaceDistanceLabel', 'improvementDistanceLabel', 'splitDistanceLabel', 'splitIntervalLabel', 'strideDistanceLabel', 'vo2DistanceLabel', 'trainingDistanceLabel'].forEach(id => {
        if (window.el(id)) window.el(id).textContent = window.el(id).textContent.replace(/(km|miles)/, unit);
    });
    if (window.el('strideLengthLabel')) window.el('strideLengthLabel').textContent = window.globalUnit === 'metric' ? 'Stride Length (cm)' : 'Stride Length (inches)';
};

window.openTab = function(evt, name) {
    document.querySelectorAll('#rl-calculator .tab-content').forEach(x => x.classList.remove('active'));
    document.querySelectorAll('#rl-calculator .tab-btn').forEach(x => x.classList.remove('active'));
    const target = window.el(name);
    if (target) {
        target.classList.add('active');
        const hash = target.dataset.hash;
        if (hash) history.replaceState(null, '', '#' + hash);
        setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
    }
    if (evt?.currentTarget) {
        evt.currentTarget.classList.add('active');
    } else {
        [...document.querySelectorAll('#rl-calculator .tab-btn')].find(b => (b.getAttribute('onclick') || '').includes(`'${name}'`))?.classList.add('active');
    }
};

window.openTabByHash = function(hash) {
    const target = [...document.querySelectorAll('#rl-calculator .tab-content')].find(x => x.dataset.hash === hash);
    if (!target) return;
    document.querySelectorAll('#rl-calculator .tab-content').forEach(x => x.classList.remove('active'));
    document.querySelectorAll('#rl-calculator .tab-btn').forEach(x => x.classList.remove('active'));
    target.classList.add('active');
    [...document.querySelectorAll('#rl-calculator .tab-btn')].find(b => (b.getAttribute('onclick') || '').includes(`'${target.id}'`))?.classList.add('active');
};

window.updatePaceDistanceLabel = function() {
    const u = window.el('paceUnit').value;
    window.el('paceDistanceLabel').textContent = u === 'meter' ? 'Distance (m)' : 'Distance (km)';
};

window.updatePacePerUnitLabel = function() {
    window.el('pacePerUnitLabel').textContent = window.globalUnit === 'metric' ? 'min/km' : 'min/mile';
};

window.calculatePace = function() {
    const d = window.n('paceDistance'), u = window.el('paceUnit').value, total = window.tsec(window.i('paceHours'), window.i('paceMinutes'), window.i('paceSeconds'));
    if (d <= 0) return window.showError('paceError', 'Please enter a valid distance.');
    if (total <= 0) return window.showError('paceError', 'Please enter a valid time.');
    const km = u === 'meter' ? d / 1000 : d, secKm = total / km, secUnit = window.globalUnit === 'metric' ? secKm : secKm * 1.60934;
    const speed = (km / (total / 3600) * (window.globalUnit === 'metric' ? 1 : 0.621371)).toFixed(2);
    const pu = window.globalUnit === 'metric' ? 'min/km' : 'min/mile', su = window.globalUnit === 'metric' ? 'km/h' : 'mph';
    window.el('pacePerUnitMinutes').value = Math.floor(secUnit / 60);
    window.el('pacePerUnitSeconds').value = Math.round(secUnit % 60);
    window.showResults('paceResults', window.rows([['Pace', `${window.formatTime(secUnit, false)} ${pu}`], ['Speed', `${speed} ${su}`]]));
};

window.calculateMagicMile = function() {
    const s = window.tsec(window.i('magicHours'), window.i('magicMinutes'), window.i('magicSeconds'));
    if (!s) return window.showError('magicMileError', 'Please enter a valid magic mile time.');
    const base = s / (window.globalUnit === 'metric' ? 1.60934 : 1);
    const dist = window.globalUnit === 'metric' ? { '5K': 5, '10K': 10, 'Half Marathon': 21.1, 'Marathon': 42.2 } : { '5K': 3.1, '10K': 6.2, 'Half Marathon': 13.1, 'Marathon': 26.2 };
    const f = { '5K': 1.05, '10K': 1.08, 'Half Marathon': 1.15, 'Marathon': 1.2 };
    const out = [['Magic Mile', window.formatTime(s, true)], ...Object.keys(dist).map(k => [`${k} Prediction`, window.formatTime(base * dist[k] * f[k], true)])];
    window.showResults('magicMileResults', window.rows(out));
};

window.calculateMarathonPace = function() {
    const s = window.tsec(window.i('marathonHours'), window.i('marathonMinutes'), window.i('marathonSeconds'));
    if (!s) return window.showError('marathonError', 'Please enter a valid marathon time.');
    const d = window.globalUnit === 'metric' ? 42.195 : 26.2, p = s / d, sp = (d / (s / 3600)).toFixed(2);
    window.showResults('marathonResults', window.rows([['Required Pace', `${window.formatTime(p, false)} ${window.globalUnit === 'metric' ? 'min/km' : 'min/mile'}`], ['Average Speed', `${sp} ${window.globalUnit === 'metric' ? 'km/h' : 'mph'}`]]));
};

window.updateSelectInput = function(selectId, inputId) {
    const s = window.el(selectId), x = window.el(inputId);
    if (s.value === 'custom') { x.disabled = false; x.value = ''; } else { x.disabled = true; x.value = s.value; }
};

window.updateRecentRaceDistance = function() { window.updateSelectInput('recentRaceDistanceSelect', 'recentRaceDistance'); };
window.updateTargetRaceDistance = function() { window.updateSelectInput('targetRaceDistanceSelect', 'targetRaceDistance'); };
window.updateSplitDistance = function() { window.updateSelectInput('splitDistanceSelect', 'splitDistance'); };

window.calculateRacePredictor = function() {
    const d1 = window.n('recentRaceDistance'), d2 = window.n('targetRaceDistance'), s = window.tsec(window.i('recentRaceHours'), window.i('recentRaceMinutes'), window.i('recentRaceSeconds'));
    if (!d1 || !d2) return window.showError('racePredictorError', 'Please enter valid distances.');
    if (!s) return window.showError('racePredictorError', 'Please enter a valid race time.');
    window.showResults('racePredictorResults', window.rows([['Predicted Time', window.formatTime(s * Math.pow(window.metricDistance(d2) / window.metricDistance(d1), 1.06), true)]]));
};

window.calculateImprovement = function() {
    const cur = window.tsec(window.i('currentBestHours'), window.i('currentBestMinutes'), window.i('currentBestSeconds')), tar = window.tsec(window.i('targetTimeHours'), window.i('targetTimeMinutes'), window.i('targetTimeSeconds'));
    if (!cur || !tar) return window.showError('improvementError', 'Please enter valid times.');
    const diff = cur - tar, pct = (diff / cur * 100).toFixed(2);
    window.showResults('improvementResults', window.rows([['Time Improvement', `${window.formatTime(Math.abs(diff), true)} (${pct}%)`], ['Target Status', diff >= 0 ? 'Faster than current PB' : 'Target slower than current PB']]));
};

window.updateSplitStrategy = function() {
    window.el('splitPercentageGroup').style.display = window.el('splitStrategy').value === 'even' ? 'none' : 'block';
};

window.updateSplitPercentageValue = function() {
    window.el('splitPercentageValue').textContent = parseFloat(window.el('splitPercentage').value).toFixed(1) + '%';
};

window.calculateSplits = function() {
    const d = window.n('splitDistance'), intv = window.n('splitInterval'), total = window.tsec(window.i('splitHours'), window.i('splitMinutes'), window.i('splitSeconds'));
    if (!d || !intv) return window.showError('splitError', 'Please enter valid distance and interval.');
    if (!total) return window.showError('splitError', 'Please enter valid total time.');
    const dm = window.metricDistance(d), im = window.metricDistance(intv), count = Math.floor(dm / im), base = total / dm, strat = window.el('splitStrategy').value, pct = window.n('splitPercentage') / 100;
    let cumulative = 0, out = [];
    for (let k = 1; k <= count; k++) {
        let adj = 1;
        if (strat === 'negative') adj = 1 + pct * ((count + 1) / 2 - k) / Math.max(1, count);
        if (strat === 'positive') adj = 1 - pct * ((count + 1) / 2 - k) / Math.max(1, count);
        const split = base * im * adj;
        cumulative += split;
        out.push([`${(intv * k).toFixed(intv % 1 ? 1 : 0)} ${window.globalUnit === 'metric' ? 'km' : 'miles'}`, `${window.formatTime(cumulative, true)} · ${window.formatTime((split / im) * (window.globalUnit === 'metric' ? 1 : 1.60934), false)}`]);
    }
    window.showResults('splitResults', window.rows(out));
};

window.calculateStepsToDistance = function() {
    const st = window.i('stepsCount'), sl = window.n('strideLength');
    if (!st || !sl) return window.showError('stepsError', 'Please enter valid steps and stride length.');
    const m = window.globalUnit === 'metric' ? (st * sl) / 100 : (st * sl * 2.54) / 100, km = m / 1000;
    window.showResults('stepsResults', window.rows([['Distance', `${(window.globalUnit === 'metric' ? km : km * 0.621371).toFixed(2)} ${window.globalUnit === 'metric' ? 'km' : 'miles'}`]]));
};

window.calculateStrideLength = function() {
    const d = window.n('strideDistance'), st = window.i('strideStepsCount');
    if (!d || !st) return window.showError('strideLengthError', 'Please enter valid distance and steps.');
    const cm = (window.metricDistance(d) * 1000 / st) * 100;
    window.showResults('strideLengthResults', window.rows([['Stride Length', `${(window.globalUnit === 'metric' ? cm : cm / 2.54).toFixed(1)} ${window.globalUnit === 'metric' ? 'cm' : 'inches'}`]]));
};

window.calculateVDOT = function(km, s) {
    const min = s / 60, v = (km * 1000 / s) * 60, vo2 = -4.6 + 0.182258 * v + 0.000104 * v * v, p = 0.8 + 0.1894393 * Math.exp(-0.012778 * min) + 0.2989558 * Math.exp(-0.1932605 * min);
    return vo2 / p;
};

window.paceFromVDOT = function(vdot, pct) {
    const a = 0.000104, b = 0.182258, c = -(vdot * pct + 4.6), disc = b * b - 4 * a * c;
    if (disc < 0) return null;
    const v = (-b + Math.sqrt(disc)) / (2 * a), sec = (1000 / v) * 60;
    return window.globalUnit === 'metric' ? sec : sec * 1.60934;
};

window.setDefaultDistance = function() {
    const map = { '5k': 5, '10k': 10, 'half': 21.1, 'marathon': 42.2, 'threshold': 10 };
    window.el('trainingDistance').value = map[window.el('basePaceType').value] || 5;
};

window.calculateTrainingPaces = function() {
    const d = window.n('trainingDistance'), s = window.tsec(window.i('bestTimeHours'), window.i('bestTimeMinutes'), window.i('bestTimeSeconds'));
    if (!d || !s) return window.showError('trainingPaceError', 'Please enter best race distance and time.');
    const v = window.calculateVDOT(window.metricDistance(d), s), pu = window.globalUnit === 'metric' ? 'min/km' : 'min/mile';
    const zones = [
        ['Estimated VDOT', v.toFixed(1)],
        ['Easy', `${window.formatTime(window.paceFromVDOT(v, 0.65), false)} ${pu}`],
        ['Moderate', `${window.formatTime(window.paceFromVDOT(v, 0.75), false)} ${pu}`],
        ['Tempo', `${window.formatTime(window.paceFromVDOT(v, 0.85), false)} ${pu}`],
        ['Interval', `${window.formatTime(window.paceFromVDOT(v, 0.95), false)} ${pu}`],
        ['Repetition', `${window.formatTime(window.paceFromVDOT(v, 1), false)} ${pu}`]
    ];
    window.showResults('trainingPaceResults', window.rows(zones));
};

window.calculateVO2Max = function() {
    const d = window.n('vo2Distance'), s = window.tsec(window.i('vo2Hours'), window.i('vo2Minutes'), window.i('vo2Seconds'));
    if (!d) return window.showError('vo2Error', 'Please enter a valid distance.');
    if (!s) return window.showError('vo2Error', 'Please enter a valid time.');
    const km = window.metricDistance(d), v = (km * 1000) / (s / 60), vo2 = -4.6 + 0.182258 * v + 0.000104 * v * v, sec = s / km * (window.globalUnit === 'metric' ? 1 : 1.60934);
    window.showResults('vo2Results', window.rows([['Race Pace', `${window.formatTime(sec, false)} ${window.globalUnit === 'metric' ? 'min/km' : 'min/mile'}`], ['Estimated VO2 Max', `${vo2.toFixed(1)} ml/kg/min`]]));
};

window.calculateHeartRateZones = function() {
    const age = window.i('hrAge'), rest = window.i('hrRest') || 60;
    if (!age) return window.showError('heartRateError', 'Usia wajib diisi.');
    const max = 220 - age, hrr = max - rest, z = [['Zone 1 · Recovery', 0.5, 0.6], ['Zone 2 · Easy', 0.6, 0.7], ['Zone 3 · Moderate', 0.7, 0.8], ['Zone 4 · Tempo', 0.8, 0.9], ['Zone 5 · Interval', 0.9, 1]];
    window.showResults('heartRateResults', window.rows([['Estimated HR Max', `${max} bpm`], ...z.map(([name, a, b]) => [name, `${Math.round(rest + a * hrr)}–${Math.round(rest + b * hrr)} bpm`])]));
};

window.calculateHydration = function() {
    const dur = window.i('hydDuration'), rep = window.n('hydReplacePct') || 0.6, temp = window.clamp(window.n('hydTemp') || 28, 0, 50), hum = window.clamp(window.n('hydHumidity') || 70, 0, 100), manual = window.n('hydSweatRate'), salt = window.el('hydSaltiness').value;
    if (dur < 10) return window.showError('hydrationError', 'Durasi lari minimal 10 menit.');
    let sweat = manual ? window.clamp(manual, 200, 2500) : window.clamp(600 + (temp - 20) * 25 + (hum - 50) * 5 + (dur >= 90 ? 50 : 0), 350, 1800);
    const sodium = { low: 500, normal: 700, high: 900 }[salt] || 700, intake = window.clamp(sweat * rep, 200, 1200), total = intake * dur / 60;
    window.showResults('hydrationResults', window.rows([['Sweat Rate', `${Math.round(sweat)} ml/h`], ['Target Intake', `${Math.round(intake)} ml/h`], ['Total Intake', `${Math.round(total)} ml`], ['Sodium', `${Math.round((sweat / 1000) * sodium)} mg/h`]]));
};

window.calculateFuelingPlan = function() {
    const total = window.tsec(window.i('fuelHours'), window.i('fuelMinutes'), window.i('fuelSeconds')), intensity = window.el('fuelIntensity').value, tolerance = window.el('fuelTolerance').value, type = window.el('fuelType').value, interval = window.i('fuelInterval') || 20;
    if (total < 1200) return window.showError('fuelingError', 'Masukkan durasi minimal 20 menit.');
    const h = total / 3600;
    let carbs = h < 1 ? 25 : h < 2 ? 50 : h < 3 ? 70 : 85;
    carbs += intensity === 'easy' ? -10 : intensity === 'race' ? 5 : 0;
    carbs += tolerance === 'low' ? -15 : tolerance === 'high' ? 10 : 0;
    carbs = window.clamp(carbs, 20, 95);
    const each = carbs * interval / 60;
    const guide = type === 'gel' ? `${(carbs / 25).toFixed(1)} gel/h` : type === 'drink' ? `${Math.round(carbs / 30 * 500)} ml sports drink/h` : `${(carbs * 0.5 / 25).toFixed(1)} gel/h + ${Math.round(carbs * 0.5 / 30 * 500)} ml drink/h`;
    window.showResults('fuelingResults', window.rows([['Duration', window.formatTime(total, true)], ['Carbohydrate', `${Math.round(carbs)} g/h`], [`Every ${interval} min`, `~${Math.round(each)} g`], ['Practical Guide', guide]]));
};

window.updateSmartMileageInputs = function() {
    window.el('smCurrentLabel').textContent = window.el('smMode').value === 'time' ? 'Current Weekly Duration (minutes)' : `Current Weekly Volume (${window.globalUnit === 'metric' ? 'km' : 'miles'})`;
};

window.calculateSmartSuggestion = function() {
    const d = window.n('smPbDist'), min = window.i('smPbH') * 60 + window.i('smPbM') + window.i('smPbS') / 60;
    if (min <= 0) return alert('Masukkan waktu PB yang valid.');
    let v = 25;
    if (d === 5) v = min < 20 ? 50 : min < 25 ? 40 : min < 30 ? 30 : 25;
    if (d === 10) v = min < 40 ? 65 : min < 50 ? 50 : min < 60 ? 40 : 30;
    if (d === 21.1) v = min < 90 ? 80 : min < 105 ? 65 : min < 120 ? 50 : 40;
    if (d === 42.2) v = min < 180 ? 100 : min < 210 ? 80 : min < 240 ? 65 : 50;
    if (window.globalUnit === 'imperial') v = Math.round(v * 0.621371);
    window.el('smMode').value = 'distance';
    window.updateSmartMileageInputs();
    window.el('smCurrent').value = v;
};

window.updateSmartMileageSlider = function() {
    let v = window.i('smSlider');
    if (window.el('smInjury').checked && v > 7) { v = 7; window.el('smSlider').value = 7; }
    window.el('smSliderVal').textContent = v + '%';
    const b = window.el('smAggressivenessLabel');
    b.textContent = v < 8 ? 'Conservative / Rehab' : v <= 12 ? 'Standard Progressive' : 'Aggressive / High Risk';
    b.style.background = v < 8 ? '#10b981' : v <= 12 ? '#3b82f6' : '#ef4444';
};

window.buildMileagePlan = function() {
    const cur = window.n('smCurrent'), weeks = window.i('smDuration'), pct = window.i('smSlider') / 100, inj = window.el('smInjury').checked, max = window.globalUnit === 'metric' ? 200 : 125, cut = inj ? 3 : 4;
    let peak = cur, prev = cur, out = [];
    for (let w = 1; w <= weeks; w++) {
        let vol, isCut = w % cut === 0;
        if (isCut) vol = peak * 0.75;
        else {
            vol = (w > 1 && (w - 1) % cut === 0 ? peak : prev) * (1 + pct);
            vol = Math.min(max, vol);
            peak = vol;
        }
        out.push({ week: w, volume: Math.min(max, vol), isCutback: isCut });
        prev = vol;
    }
    return out;
};

window.calculateSmartMileage = function() {
    if (window.n('smCurrent') <= 0) return window.showError('smartMileageError', 'Please enter a valid current volume.');
    const p = window.buildMileagePlan();
    window.renderSmartMileageChart(p);
    window.renderSmartMileageTable(p);
    window.el('smartMileageError').style.display = 'none';
    window.el('smartMileageResults').classList.add('show');
    window.el('smartMileageExportBtn').style.display = 'inline-flex';
    window.el('smartMileageIcsBtn').style.display = 'inline-flex';
};

window.renderSmartMileageChart = function(plan) {
    const ctx = window.el('smChart').getContext('2d');
    if (window.smChartInstance) window.smChartInstance.destroy();
    window.smChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: plan.map(x => `W${x.week}`),
            datasets: [{
                data: plan.map(x => x.volume.toFixed(1)),
                backgroundColor: plan.map(x => x.isCutback ? '#34d399' : '#60a5fa'),
                borderRadius: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: 'rgba(255,255,255,.06)' }, ticks: { color: '#cbd5e1' } },
                x: { grid: { display: false }, ticks: { color: '#cbd5e1' } }
            }
        }
    });
};

window.renderSmartMileageTable = function(plan) {
    const unit = window.el('smMode').value === 'time' ? 'min' : window.globalUnit === 'metric' ? 'km' : 'mi';
    window.el('smTable').innerHTML = window.rows(plan.map(x => [`Week ${x.week} · ${x.isCutback ? 'Cutback' : 'Build'}`, `${Math.round(x.volume)} ${unit}`]));
};

window.exportSmartMileageICS = function() {
    const date = window.el('smStartDate').value;
    if (!date) return alert('Pilih Start Date terlebih dahulu.');
    const plan = window.buildMileagePlan(), unit = window.el('smMode').value === 'time' ? 'min' : window.globalUnit === 'metric' ? 'km' : 'mi', goal = window.el('smGoal').value;
    let d = new Date(date), ics = 'BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//RuangLari//Mileage//EN\n';
    plan.forEach(x => {
        const ds = d.toISOString().slice(0, 10).replaceAll('-', '');
        const de = new Date(d);
        de.setDate(de.getDate() + 1);
        ics += `BEGIN:VEVENT\nDTSTART;VALUE=DATE:${ds}\nDTEND;VALUE=DATE:${de.toISOString().slice(0, 10).replaceAll('-', '')}\nSUMMARY:Week ${x.week} Run Volume: ${Math.round(x.volume)} ${unit}\nDESCRIPTION:${x.isCutback ? 'Recovery' : 'Build'} week · Goal: ${goal}\nEND:VEVENT\n`;
        d.setDate(d.getDate() + 7);
    });
    ics += 'END:VCALENDAR';
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([ics], { type: 'text/calendar' }));
    a.download = 'smart-mileage-plan.ics';
    a.click();
};

window.exportResults = function(resultsId, title) {
    const node = window.el(resultsId);
    if (!node?.classList.contains('show')) return alert('Hitung hasil terlebih dahulu.');
    if (typeof html2canvas === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
        script.onload = () => window.exportResults(resultsId, title);
        document.head.appendChild(script);
        return;
    }
    const box = document.createElement('div');
    box.style.cssText = 'position:fixed;left:-10000px;top:0;width:900px;background:#fff;color:#0f172a;padding:28px;font-family:Arial,sans-serif';
    box.innerHTML = `<div style="display:flex;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding-bottom:16px;margin-bottom:16px"><strong>RUANG LARI / PERFORMANCE LAB</strong><strong>${title}</strong></div>`;
    const clone = node.cloneNode(true);
    clone.style.cssText = 'display:block;border:0;padding:0;margin:0;background:#fff';
    clone.querySelectorAll('.result-item').forEach(x => x.style.cssText = 'display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e2e8f0');
    clone.querySelectorAll('.result-label').forEach(x => x.style.cssText = 'color:#64748b;font-size:12px;font-weight:700');
    clone.querySelectorAll('.result-value').forEach(x => x.style.cssText = 'color:#0f172a;font-size:14px;font-weight:900');
    box.appendChild(clone);
    document.body.appendChild(box);
    html2canvas(box, { backgroundColor: '#fff', scale: 2, useCORS: true }).then(c => {
        const a = document.createElement('a');
        a.download = title.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '.png';
        a.href = c.toDataURL('image/png');
        a.click();
        box.remove();
    });
};

document.addEventListener('DOMContentLoaded', () => {
    if (window.el('smStartDate')) window.el('smStartDate').valueAsDate = new Date();
    window.setDefaultDistance();
    window.updateAllLabels();
    window.updatePacePerUnitLabel();
    window.updateSplitPercentageValue();
    window.updateSplitDistance();
    window.updateRecentRaceDistance();
    window.updateTargetRaceDistance();
    window.updatePaceDistanceLabel();
    window.updateSmartMileageInputs();
    window.updateSmartMileageSlider();
    const hash = location.hash.slice(1);
    if (hash) window.openTabByHash(hash);
    else history.replaceState(null, '', '#pacecalculator');
});
window.addEventListener('hashchange', () => window.openTabByHash(location.hash.slice(1)));
</script>
@endsection
