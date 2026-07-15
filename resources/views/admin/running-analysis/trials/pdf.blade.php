<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Running Form Analysis — {{ $trial->runner->name }}</title>
<style>
/* =============================================
   RESET & BASE
============================================= */
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #1e293b; background: #fff; }
h1 { font-size: 18pt; }
h2 { font-size: 12pt; }
h3 { font-size: 10pt; }
p  { line-height: 1.5; }
table { width: 100%; border-collapse: collapse; }

/* =============================================
   HEADER
============================================= */
.page-header {
    background: #0f172a;
    color: #fff;
    padding: 14px 20px;
    border-bottom: 3px solid #ccff00;
}
.header-inner {
    width: 100%;
}
.header-brand {
    font-size: 16pt;
    font-weight: 900;
    font-style: italic;
    letter-spacing: -0.5px;
    color: #ccff00;
    display: inline;
}
.header-tagline {
    font-size: 7pt;
    color: #94a3b8;
    display: block;
    margin-top: 2px;
}
.header-runner-name {
    font-size: 14pt;
    font-weight: 900;
    color: #fff;
}
.header-meta {
    font-size: 7.5pt;
    color: #94a3b8;
    margin-top: 3px;
}
.header-score-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #ccff00;
    text-align: center;
    padding-top: 8px;
}
.header-score-number {
    font-size: 20pt;
    font-weight: 900;
    font-style: italic;
    color: #0f172a;
    line-height: 1;
    display: block;
}
.header-score-label {
    font-size: 6pt;
    color: #1e293b;
    font-weight: 700;
    display: block;
}
.score-no-data {
    font-size: 9pt;
    color: #94a3b8;
    font-style: italic;
}

/* =============================================
   LAYOUT HELPERS
============================================= */
.section {
    margin-top: 14px;
    page-break-inside: avoid;
}
.section-title {
    font-size: 8pt;
    font-weight: 900;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 4px;
    margin-bottom: 8px;
}
.section-title .accent { color: #65a30d; }

.two-col td { width: 50%; vertical-align: top; }
.two-col td:first-child { padding-right: 6px; }
.two-col td:last-child  { padding-left: 6px; }

/* =============================================
   COACH FEEDBACK
============================================= */
.quote-box {
    background: #f8fafc;
    border-left: 3px solid #ccff00;
    padding: 8px 10px;
    border-radius: 3px;
    font-size: 9pt;
    color: #334155;
    font-style: italic;
    margin-bottom: 8px;
}
.positives-item {
    font-size: 8.5pt;
    color: #1e293b;
    padding: 3px 0 3px 14px;
    border-bottom: 1px solid #f1f5f9;
    position: relative;
}
.positives-item .check {
    color: #16a34a;
    font-weight: 900;
    position: absolute;
    left: 0;
}
.positives-title { font-weight: 700; color: #0f172a; }
.positives-desc  { font-size: 8pt; color: #64748b; }

/* =============================================
   FORM PHASE CARDS
============================================= */
.phase-card {
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    padding: 7px 8px;
    margin-bottom: 6px;
    page-break-inside: avoid;
}
.phase-header {
    display: block;
    margin-bottom: 4px;
}
.phase-title {
    font-size: 9pt;
    font-weight: 900;
    color: #0f172a;
}
.phase-badge {
    font-size: 7pt;
    font-weight: 700;
    text-transform: uppercase;
    padding: 1px 5px;
    border-radius: 2px;
    letter-spacing: 0.5px;
}
.badge-ok      { background: #dcfce7; color: #166534; }
.badge-warn    { background: #fef9c3; color: #854d0e; }
.badge-issue   { background: #fee2e2; color: #991b1b; }
.badge-missing { background: #f1f5f9; color: #475569; }

.phase-summary {
    font-size: 8pt;
    color: #64748b;
    font-style: italic;
    margin-bottom: 5px;
}
.phase-findings-label, .phase-actions-label {
    font-size: 7.5pt;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
    margin-bottom: 2px;
}
.phase-finding-item {
    font-size: 8pt;
    color: #0f172a;
    padding-left: 10px;
    line-height: 1.4;
    margin-bottom: 1px;
}
.phase-action-item {
    font-size: 8pt;
    color: #1e40af;
    padding-left: 10px;
    line-height: 1.4;
    margin-bottom: 1px;
}
.bullet { color: #94a3b8; }
.bullet-blue { color: #3b82f6; }

/* =============================================
   METRICS TABLE
============================================= */
.metrics-table th {
    font-size: 7.5pt;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    background: #f8fafc;
    padding: 4px 6px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}
.metrics-table td {
    font-size: 8.5pt;
    padding: 4px 6px;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
    vertical-align: middle;
}
.metrics-table td.metric-value {
    font-family: 'Courier New', monospace;
    font-size: 8.5pt;
    font-weight: 700;
    color: #15803d;
    text-align: right;
}
.metrics-table td.metric-unit {
    font-size: 7.5pt;
    color: #94a3b8;
    text-align: right;
    width: 40px;
}
.metrics-table td.metric-side {
    font-size: 7.5pt;
    color: #64748b;
    width: 50px;
}
.metrics-table tr:last-child td { border-bottom: none; }

/* =============================================
   FINDINGS
============================================= */
.finding-row {
    padding: 5px 7px;
    border-radius: 3px;
    margin-bottom: 4px;
    page-break-inside: avoid;
}
.finding-sig  { background: #fff1f2; border-left: 3px solid #dc2626; }
.finding-mod  { background: #fffbeb; border-left: 3px solid #d97706; }
.finding-min  { background: #eff6ff; border-left: 3px solid #2563eb; }
.finding-sev-badge {
    font-size: 6.5pt;
    font-weight: 700;
    text-transform: uppercase;
    padding: 1px 4px;
    border-radius: 2px;
    letter-spacing: 0.4px;
}
.sev-sig { background: #dc2626; color: #fff; }
.sev-mod { background: #d97706; color: #fff; }
.sev-min { background: #2563eb; color: #fff; }
.finding-code {
    font-family: 'Courier New', monospace;
    font-size: 7pt;
    color: #64748b;
    margin-left: 4px;
}
.finding-desc { font-size: 8.5pt; color: #1e293b; margin-top: 2px; }
.finding-evidence { font-size: 7.5pt; color: #94a3b8; margin-top: 1px; }

/* =============================================
   TRAINING PROGRAM
============================================= */
.reco-group-label {
    font-size: 7.5pt;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    margin-top: 8px;
    padding: 2px 5px;
    background: #f8fafc;
    border-radius: 2px;
}
.reco-item {
    padding: 4px 8px 4px 12px;
    border-bottom: 1px solid #f1f5f9;
    page-break-inside: avoid;
}
.reco-item:last-child { border-bottom: none; }
.reco-title { font-size: 8.5pt; font-weight: 700; color: #0f172a; }
.reco-desc  { font-size: 8pt; color: #475569; line-height: 1.45; margin-top: 1px; }

/* =============================================
   FOOTER
============================================= */
.page-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: 7pt;
    color: #94a3b8;
    padding: 4px 20px;
}
.footer-inner {
    width: 100%;
}

/* =============================================
   PAGE COUNTER (Dompdf)
============================================= */
.page-number:before {
    content: counter(page);
}
.page-count:before {
    content: counter(pages);
}
</style>
</head>
<body>

<!-- ============ FIXED FOOTER ============ -->
<div class="page-footer">
    <table class="footer-inner">
        <tr>
            <td style="text-align:left;">
                RuangLari — Running Form Analysis Report &nbsp;|&nbsp; Trial #{{ $trial->attempt_no }}
            </td>
            <td style="text-align:right;">
                Dibuat: {{ $generatedAt }} &nbsp;|&nbsp;
                Hal <span class="page-number"></span> / <span class="page-count"></span>
            </td>
        </tr>
    </table>
</div>

<!-- ============ HEADER ============ -->
<div class="page-header">
    <table class="header-inner">
        <tr>
            <td style="width:auto; vertical-align:top;">
                <img src="{{ public_path('images/logo ruang lari putih.png') }}" class="h-6 mt-1 object-contain" alt="Ruang Lari">
                <span class="header-tagline">Running Form Analysis</span>
            </td>
            <td style="width:auto; text-align:center; vertical-align:top; padding: 0 16px;">
                <div class="header-runner-name">{{ $trial->runner->name }}</div>
                <div class="header-meta">
                    Trial #{{ $trial->attempt_no }}
                    &nbsp;&bull;&nbsp;
                    {{ $trial->created_at->timezone('Asia/Jakarta')->format('d M Y') }}
                    @if($videoMeta)
                        &nbsp;&bull;&nbsp; {{ $videoMeta }}
                    @endif
                    &nbsp;&bull;&nbsp;
                    Status: <strong>{{ strtoupper(str_replace('_', ' ', $trial->status)) }}</strong>
                </div>
            </td>
            <td style="width:70px; text-align:right; vertical-align:top;">
                @if($score !== null)
                <div style="text-align:center;">
                    <div class="header-score-circle">
                        <span class="header-score-number">{{ $score }}</span>
                        <span class="header-score-label">/ 100</span>
                    </div>
                    <div style="font-size:6.5pt; color:#94a3b8; margin-top:3px;">Form Score</div>
                </div>
                @else
                <div class="score-no-data">Score<br>N/A</div>
                @endif
            </td>
        </tr>
    </table>
</div>

<!-- ============ MAIN CONTENT ============ -->
<div style="padding: 4px 20px 30px 20px;">

    <!-- ---- SECTION 1: Overview / Coach Feedback ---- -->
    @if($coachMessage || count($positives) > 0)
    <div class="section">
        <div class="section-title"><span class="accent">&#9679;</span> Coach Feedback</div>

        @if($coachMessage)
        <div class="quote-box">&#8220;{{ $coachMessage }}&#8221;</div>
        @endif

        @if(count($positives) > 0)
        <div style="margin-top:4px; font-size:7.5pt; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">
            Yang Sudah Baik
        </div>
        @foreach($positives as $pos)
        <div class="positives-item">
            <span class="check">&#10003;</span>
            @if(is_array($pos))
                @if(isset($pos['title']))
                    <span class="positives-title">{{ $pos['title'] }}</span>
                    @if(isset($pos['description']))
                        <br><span class="positives-desc">{{ $pos['description'] }}</span>
                    @endif
                @else
                    {{ implode(' ', array_values($pos)) }}
                @endif
            @else
                {{ $pos }}
            @endif
        </div>
        @endforeach
        @endif
    </div>
    @endif

    <!-- ---- SECTION 2: Running Form Analysis (phase cards 2-column) ---- -->
    @if(count($formReport) > 0)
    <div class="section">
        <div class="section-title"><span class="accent">&#9679;</span> Running Form Analysis</div>

        @php
            $phaseLabels = [
                'landing'   => 'Landing (Foot Strike)',
                'lever'     => 'Lever (Mid-Stance)',
                'push'      => 'Push (Toe-Off)',
                'pull'      => 'Pull (Swing Phase)',
                'arm_swing' => 'Arm Swing',
                'posture'   => 'Posture & Stability',
            ];
            // Pair up phases for 2-column layout
            $phases   = array_values($formReport);
            $rows     = array_chunk($phases, 2);
        @endphp

        @foreach($rows as $row)
        <table class="two-col" style="margin-bottom:0;">
            <tr>
                @foreach($row as $phase)
                @php
                    $pCode    = $phase['code'] ?? '';
                    $pTitle   = $phaseLabels[$pCode] ?? ($phase['title'] ?? $pCode);
                    $pStatus  = $phase['status'] ?? 'ok';
                    $pSummary = $phase['summary'] ?? null;
                    $pFinds   = $phase['findings'] ?? [];
                    $pActions = $phase['actions'] ?? [];
                    $badgeClass = match($pStatus) {
                        'issue'   => 'badge-issue',
                        'warn'    => 'badge-warn',
                        'missing' => 'badge-missing',
                        default   => 'badge-ok',
                    };
                    $statusLabel = match($pStatus) {
                        'issue'   => 'Issue',
                        'warn'    => 'Perhatian',
                        'missing' => 'Tidak Ada Data',
                        default   => 'OK',
                    };
                @endphp
                <td>
                    <div class="phase-card">
                        <table style="width:100%; margin-bottom:4px;">
                            <tr>
                                <td style="vertical-align:middle;">
                                    <span class="phase-title">{{ $pTitle }}</span>
                                </td>
                                <td style="text-align:right; vertical-align:middle; width:70px;">
                                    <span class="phase-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        </table>

                        @if($pSummary)
                        <div class="phase-summary">{{ $pSummary }}</div>
                        @endif

                        @if(count($pFinds) > 0)
                        <div class="phase-findings-label">Temuan</div>
                        @foreach($pFinds as $f)
                        <div class="phase-finding-item"><span class="bullet">&#9632;</span> {{ $f }}</div>
                        @endforeach
                        @endif

                        @if(count($pActions) > 0)
                        <div class="phase-actions-label">Koreksi</div>
                        @foreach($pActions as $a)
                        <div class="phase-action-item"><span class="bullet-blue">&#8594;</span> {{ $a }}</div>
                        @endforeach
                        @endif
                    </div>
                </td>
                @endforeach
                {{-- Pad with empty cell if odd number --}}
                @if(count($row) === 1)
                <td></td>
                @endif
            </tr>
        </table>
        @endforeach
    </div>
    @endif

    <!-- ---- SECTION 3: Biomechanical Metrics ---- -->
    @if($metrics->count() > 0)
    <div class="section" style="page-break-before: auto;">
        <div class="section-title"><span class="accent">&#9679;</span> Biomechanical Metrics</div>
        <table class="metrics-table">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th style="width:60px;">Sisi</th>
                    <th style="text-align:right; width:70px;">Nilai</th>
                    <th style="text-align:right; width:40px;">Satuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($metrics as $m)
                <tr>
                    <td>{{ str_replace('_', ' ', $m->metric_code) }}</td>
                    <td class="metric-side">{{ $m->side ? ucfirst($m->side) : '—' }}</td>
                    <td class="metric-value">{{ round((float) $m->value_decimal, 2) }}</td>
                    <td class="metric-unit">{{ $m->unit }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- ---- SECTION 4: AI Findings ---- -->
    @if($findings->count() > 0)
    <div class="section">
        <div class="section-title"><span class="accent">&#9679;</span> AI Findings</div>

        @foreach($findings as $f)
        @php
            $sevClass  = match($f->severity) { 'significant' => 'finding-sig', 'moderate' => 'finding-mod', default => 'finding-min' };
            $sevBadge  = match($f->severity) { 'significant' => 'sev-sig', 'moderate' => 'sev-mod', default => 'sev-min' };
            $sevLabel  = ucfirst($f->severity);
            $descText  = str_replace('_', ' ', ucfirst(strtolower($f->explanation_key ?? $f->finding_code)));
            $evidence  = $f->evidence_json
                ? (is_array($f->evidence_json)
                    ? implode(', ', array_map(fn($k,$v) => "$k: $v", array_keys($f->evidence_json), $f->evidence_json))
                    : $f->evidence_json)
                : null;
        @endphp
        <div class="finding-row {{ $sevClass }}">
            <span class="finding-sev-badge {{ $sevBadge }}">{{ $sevLabel }}</span>
            <span class="finding-code">{{ $f->finding_code }}</span>
            <div class="finding-desc">{{ $descText }}</div>
            @if($evidence)
            <div class="finding-evidence">{{ $evidence }}</div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <!-- ---- SECTION 5: Training Program ---- -->
    @if($cues->count() > 0 || $drills->count() > 0 || $strengths->count() > 0)
    <div class="section" style="page-break-before: auto;">
        <div class="section-title"><span class="accent">&#9679;</span> Program Latihan & Koreksi</div>

        @if($cues->count() > 0)
        <div class="reco-group-label">&#128172; Posture & Gait Cues</div>
        @foreach($cues as $c)
        <div class="reco-item">
            <div class="reco-title">{{ $c->title }}</div>
            <div class="reco-desc">{{ $c->description }}</div>
        </div>
        @endforeach
        @endif

        @if($drills->count() > 0)
        <div class="reco-group-label">&#127939; Recommended Drills</div>
        @foreach($drills as $d)
        <div class="reco-item">
            <div class="reco-title">{{ $d->title }}</div>
            <div class="reco-desc">{{ $d->description }}</div>
        </div>
        @endforeach
        @endif

        @if($strengths->count() > 0)
        <div class="reco-group-label">&#128170; Strength Exercises</div>
        @foreach($strengths as $s)
        <div class="reco-item">
            <div class="reco-title">{{ $s->title }}</div>
            <div class="reco-desc">{{ $s->description }}</div>
        </div>
        @endforeach
        @endif
    </div>
    @endif

    <!-- ---- SECTION 6: Gait Events Timeline ---- -->
    @if($sortedEvents->count() > 0)
    <div class="section">
        <div class="section-title"><span class="accent">&#9679;</span> Gait Event Timeline</div>
        <table class="metrics-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Event</th>
                    <th>Sisi</th>
                    <th style="text-align:right; width:80px;">Waktu Relatif</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sortedEvents as $i => $ev)
                @php
                    $evLabel = match($ev->event_type) {
                        'initial_contact'   => 'Landing (Foot Strike)',
                        'midstance'         => 'Midstance',
                        'toe_off'           => 'Push Off (Toe-Off)',
                        'max_swing_flexion' => 'Knee Pull (Swing)',
                        default             => ucfirst(str_replace('_', ' ', $ev->event_type)),
                    };
                    $relSec = number_format(($ev->timestamp_ms - $eventBaseMs) / 1000, 3);
                @endphp
                <tr>
                    <td style="width:24px; color:#94a3b8;">{{ $i + 1 }}</td>
                    <td>{{ $evLabel }}</td>
                    <td class="metric-side">{{ ucfirst($ev->side ?? '—') }}</td>
                    <td class="metric-value">+{{ $relSec }}s</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div><!-- end main content -->

</body>
</html>
