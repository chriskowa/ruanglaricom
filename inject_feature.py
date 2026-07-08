import re

file_path = r'c:\laragon\www\ruanglari\resources\views\tools\calculator.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

html_target = r"""                        <div class="form-group">
                            <label>Best Race Time (optional)</label>
                            <div class="time-inputs">
                                <input type="number" id="bestTimeHours" placeholder="Hours" min="0">
                                <input type="number" id="bestTimeMinutes" placeholder="Min" min="0">
                                <input type="number" id="bestTimeSeconds" placeholder="Sec" min="0">
                            </div>
                        </div>"""

new_html_target = """                        <div class="form-group">
                            <label>Best Race Time (optional)</label>
                            <div class="time-inputs">
                                <input type="number" id="bestTimeHours" placeholder="Hours" min="0">
                                <input type="number" id="bestTimeMinutes" placeholder="Min" min="0">
                                <input type="number" id="bestTimeSeconds" placeholder="Sec" min="0">
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                            <label style="color:var(--primary); font-size:1rem;">Session Target / Menu Latihan (Opsional)</label>
                            <p style="font-size:0.8rem; color:var(--muted); margin-bottom:1rem;">Hitung estimasi waktu/jarak untuk menu spesifik berdasarkan Pace (misal: Interval 400m, atau Easy Run 45m)</p>
                            <div class="form-row">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label>Tipe Target</label>
                                    <select id="trainingTargetMode" onchange="toggleTrainingTarget()">
                                        <option value="none">Tidak Ada Target</option>
                                        <option value="distance">Berdasarkan Jarak (Meter)</option>
                                        <option value="duration">Berdasarkan Durasi (Menit)</option>
                                    </select>
                                </div>
                                <div class="form-group" id="trainingTargetValueGroup" style="display: none; margin-bottom:0;">
                                    <label id="trainingTargetValueLabel">Nilai Target</label>
                                    <input type="number" id="trainingTargetValue" min="1" placeholder="">
                                </div>
                            </div>
                        </div>"""

content = re.sub(html_target, new_html_target, content)

js_target = r"""        function calculateTrainingPaces\(\) \{
            const distance = parseFloat\(document.getElementById\('trainingDistance'\).value\) \|\| 0;
            const bestHours = parseInt\(document.getElementById\('bestTimeHours'\).value\) \|\| 0;
            const bestMinutes = parseInt\(document.getElementById\('bestTimeMinutes'\).value\) \|\| 0;
            const bestSeconds = parseInt\(document.getElementById\('bestTimeSeconds'\).value\) \|\| 0;
            
            const bestTimeSec = timeToSeconds\(bestHours, bestMinutes, bestSeconds\);
            if \(distance <= 0 \|\| bestTimeSec <= 0\) \{
                showError\('trainingPaceError', 'Please enter best race distance and time.'\);
                return;
            \}
            
            const distMetric = convertDistanceToMetric\(distance\);
            const vdot = calculateVDOT\(distMetric, bestTimeSec\);
            const paceUnit = globalUnit === 'metric' \? 'min/km' : 'min/mile';
            
            const trainingPercents = \{ easy: 0.65, moderate: 0.75, tempo: 0.85, interval: 0.95, repetition: 1.00 \};
            
            let results = '<div class="result-grid">';
            results \+= `<div class="result-item"><div class="result-label">Estimated VDOT</div><div class="result-value">$\{vdot.toFixed\(1\)\}</div></div>`;
            Object.entries\(trainingPercents\).forEach\(\(\[name, pct\]\) => \{
                const sec = getPaceFromVDOT\(vdot, pct, globalUnit\);
                if \(!sec\) return;
                results \+= `<div class="result-item"><div class="result-label">$\{name.toUpperCase\(\)\}</div><div class="result-value">$\{formatTime\(sec, false\)\} $\{paceUnit\}</div></div>`;
            \}\);
            results \+= '</div>';
            showResults\('trainingPaceResults', results\);
        \}"""

new_js = """        function toggleTrainingTarget() {
            const mode = document.getElementById('trainingTargetMode').value;
            const group = document.getElementById('trainingTargetValueGroup');
            const label = document.getElementById('trainingTargetValueLabel');
            const input = document.getElementById('trainingTargetValue');
            
            if (mode === 'none') {
                group.style.display = 'none';
                input.value = '';
            } else {
                group.style.display = 'block';
                if (mode === 'distance') {
                    label.textContent = 'Jarak Latihan (Meter)';
                    input.placeholder = 'e.g., 400';
                } else {
                    label.textContent = 'Durasi Latihan (Menit)';
                    input.placeholder = 'e.g., 45';
                }
            }
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
            
            const targetMode = document.getElementById('trainingTargetMode') ? document.getElementById('trainingTargetMode').value : 'none';
            const targetValue = document.getElementById('trainingTargetValue') ? parseFloat(document.getElementById('trainingTargetValue').value) : 0;
            
            let results = '<div class="result-grid">';
            results += `<div class="result-item"><div class="result-label">Estimated VDOT</div><div class="result-value">${vdot.toFixed(1)}</div></div>`;
            
            Object.entries(trainingPercents).forEach(([name, pct]) => {
                const sec = getPaceFromVDOT(vdot, pct, globalUnit);
                if (!sec) return;
                
                let targetHtml = '';
                if (targetMode === 'distance' && targetValue > 0) {
                    const baseDist = globalUnit === 'metric' ? 1000 : 1609.34;
                    const timeInSeconds = (targetValue / baseDist) * sec;
                    targetHtml = `<div style="font-size:0.75rem; color:var(--muted); margin-top:0.25rem;">Waktu utk ${targetValue}m: <span style="color:var(--text); font-weight:700;">${formatTime(timeInSeconds, timeInSeconds >= 3600)}</span></div>`;
                } else if (targetMode === 'duration' && targetValue > 0) {
                    const distUnits = (targetValue * 60) / sec;
                    const unitLabel = globalUnit === 'metric' ? 'km' : 'mi';
                    targetHtml = `<div style="font-size:0.75rem; color:var(--muted); margin-top:0.25rem;">Jarak dlm ${targetValue}m: <span style="color:var(--text); font-weight:700;">${distUnits.toFixed(2)} ${unitLabel}</span></div>`;
                }
                
                results += `<div class="result-item" style="flex-direction:column; align-items:flex-start;">
                    <div style="display:flex; justify-content:space-between; width:100%;">
                        <div class="result-label">${name.toUpperCase()}</div>
                        <div class="result-value">${formatTime(sec, false)} ${paceUnit}</div>
                    </div>
                    ${targetHtml}
                </div>`;
            });
            results += '</div>';
            showResults('trainingPaceResults', results);
        }"""

content = re.sub(js_target, new_js, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("HTML and JS injected successfully.")
