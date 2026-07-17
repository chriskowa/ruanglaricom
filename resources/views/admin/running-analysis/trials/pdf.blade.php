@php
    /* ================================================================
       VIEW PREPARATION
       Semua data diturunkan dari variabel yang sudah tersedia.
       Tidak ada nilai biomekanik, diagnosis, atau dosis latihan buatan.
    ================================================================= */
    $runnerName     = optional($trial->runner)->name ?? 'Pelari';
    $attemptNumber  = $trial->attempt_no ?? '—';
    $analysisDate   = $trial->created_at
        ? $trial->created_at->timezone('Asia/Jakarta')->format('d M Y')
        : '—';
    $trialStatus    = strtoupper(str_replace('_', ' ', (string) ($trial->status ?? 'unknown')));
    $logoPath       = public_path('images/logo ruang lari putih.png');
    $hasLogo        = file_exists($logoPath);

    $scoreValue = is_numeric($score ?? null)
        ? max(0, min(100, (int) round((float) $score)))
        : null;

    if ($scoreValue === null) {
        $scoreCategory    = 'INSUFFICIENT DATA';
        $scoreDescription = 'Data belum cukup untuk menghasilkan skor teknik lari.';
        $scoreColor       = '#64748b';
        $scoreBackground  = '#f1f5f9';
        $scoreSymbol      = '–';
    } elseif ($scoreValue >= 85) {
        $scoreCategory    = 'EXCELLENT';
        $scoreDescription = 'Pola gerak sangat baik dengan koreksi minimal.';
        $scoreColor       = '#15803d';
        $scoreBackground  = '#dcfce7';
        $scoreSymbol      = '✓';
    } elseif ($scoreValue >= 70) {
        $scoreCategory    = 'GOOD';
        $scoreDescription = 'Teknik secara umum baik dengan beberapa koreksi ringan.';
        $scoreColor       = '#3f6212';
        $scoreBackground  = '#ecfccb';
        $scoreSymbol      = '✓';
    } elseif ($scoreValue >= 55) {
        $scoreCategory    = 'NEEDS IMPROVEMENT';
        $scoreDescription = 'Terdapat beberapa aspek yang perlu diperbaiki secara terarah.';
        $scoreColor       = '#a16207';
        $scoreBackground  = '#fef9c3';
        $scoreSymbol      = '!';
    } else {
        $scoreCategory    = 'PRIORITY CORRECTION';
        $scoreDescription = 'Diperlukan koreksi prioritas sebelum meningkatkan intensitas latihan.';
        $scoreColor       = '#b91c1c';
        $scoreBackground  = '#fee2e2';
        $scoreSymbol      = '!';
    }

    $phaseLabels = [
        'landing'   => 'Landing (Foot Strike)',
        'lever'     => 'Lever (Mid-Stance)',
        'push'      => 'Push (Toe-Off)',
        'pull'      => 'Pull (Swing Phase)',
        'arm_swing' => 'Arm Swing',
        'posture'   => 'Posture & Stability',
    ];

    $metricLabels = [
        'knee_flexion'        => 'Knee Flexion',
        'hip_flexion'         => 'Hip Flexion',
        'ankle_dorsiflexion'  => 'Ankle Dorsiflexion',
        'hip_drop'            => 'Hip Drop',
        'ground_contact_time' => 'Ground Contact Time',
        'flight_time'         => 'Flight Time',
        'cadence'             => 'Cadence',
        'stride_length'       => 'Stride Length',
        'step_length'         => 'Step Length',
        'vertical_oscillation'=> 'Vertical Oscillation',
        'trunk_lean'          => 'Trunk Lean',
        'foot_strike_angle'   => 'Foot Strike Angle',
        'overstride_distance' => 'Overstride Distance',
        'stance_time'         => 'Stance Time',
    ];

    $statusConfig = [
        'ok'      => ['label' => 'OPTIMAL', 'symbol' => '✓', 'class' => 'status-ok'],
        'warn'    => ['label' => 'PERHATIAN', 'symbol' => '!', 'class' => 'status-warn'],
        'issue'   => ['label' => 'PRIORITAS', 'symbol' => '↑', 'class' => 'status-issue'],
        'missing' => ['label' => 'DATA TIDAK TERSEDIA', 'symbol' => '–', 'class' => 'status-missing'],
    ];

    $severityConfig = [
        'significant' => ['order' => 1, 'label' => 'PRIORITAS', 'symbol' => '↑', 'class' => 'severity-significant'],
        'moderate'    => ['order' => 2, 'label' => 'PERHATIAN', 'symbol' => '!', 'class' => 'severity-moderate'],
        'minor'       => ['order' => 3, 'label' => 'MINOR', 'symbol' => '•', 'class' => 'severity-minor'],
    ];

    $formReportCollection = collect($formReport ?? [])->values();
    $positivesCollection  = collect($positives ?? [])->filter()->take(3)->values();
    $findingsCollection   = collect($findings ?? [])->sortBy(function ($finding) use ($severityConfig) {
        return $severityConfig[data_get($finding, 'severity', 'minor')]['order'] ?? 99;
    })->values();
    $priorityFindings = $findingsCollection->take(3);

    $metricsCollection   = collect($metrics ?? []);
    $metricGroups        = $metricsCollection->groupBy(function ($metric) {
        return data_get($metric, 'metric_code', 'unknown_metric');
    });
    $cuesCollection      = collect($cues ?? []);
    $drillsCollection    = collect($drills ?? []);
    $strengthCollection  = collect($strengths ?? []);
    $eventsCollection    = collect($sortedEvents ?? []);

    $availablePhaseCount = $formReportCollection->filter(function ($phase) {
        return data_get($phase, 'status', 'missing') !== 'missing';
    })->count();
    $totalPhaseCount = max(1, $formReportCollection->count());
    $coveragePercent = (int) round(($availablePhaseCount / $totalPhaseCount) * 100);

    if ($formReportCollection->isEmpty()) {
        $coverageLabel = 'Belum tersedia';
        $coverageClass = 'coverage-low';
    } elseif ($coveragePercent >= 85) {
        $coverageLabel = 'Lengkap';
        $coverageClass = 'coverage-high';
    } elseif ($coveragePercent >= 60) {
        $coverageLabel = 'Sebagian';
        $coverageClass = 'coverage-medium';
    } else {
        $coverageLabel = 'Terbatas';
        $coverageClass = 'coverage-low';
    }

    $readableCode = function ($code) {
        return ucwords(str_replace(['_', '-'], ' ', strtolower((string) $code)));
    };

    $findingTitle = function ($finding) use ($readableCode) {
        $title = trim((string) data_get($finding, 'title', ''));
        if ($title !== '') {
            return $title;
        }

        $codeSource = data_get($finding, 'explanation_key')
            ?? data_get($finding, 'finding_code')
            ?? 'Temuan';

        return $readableCode($codeSource);
    };

    $formatMetricValue = function ($metric) {
        $value = data_get($metric, 'value_decimal');
        return is_numeric($value)
            ? number_format((float) $value, 2, '.', '')
            : '—';
    };

    $flattenEvidence = function ($value, $prefix = '') use (&$flattenEvidence, $readableCode) {
        if (is_object($value)) {
            $value = (array) $value;
        }

        if (!is_array($value)) {
            return [[
                'label' => $prefix !== '' ? $prefix : 'Nilai',
                'value' => is_scalar($value) || is_null($value) ? ($value ?? '—') : 'Data tidak dapat ditampilkan',
            ]];
        }

        $rows = [];
        $isList = $value === [] || array_keys($value) === range(0, count($value) - 1);

        foreach ($value as $key => $item) {
            $labelPart = $isList
                ? 'Item ' . ((int) $key + 1)
                : $readableCode($key);
            $label = $prefix !== '' ? $prefix . ' — ' . $labelPart : $labelPart;

            if (is_array($item) || is_object($item)) {
                $rows = array_merge($rows, $flattenEvidence($item, $label));
            } else {
                $rows[] = [
                    'label' => $label,
                    'value' => is_scalar($item) || is_null($item) ? ($item ?? '—') : 'Data tidak dapat ditampilkan',
                ];
            }
        }

        return $rows;
    };

    $trainingFocus = null;
    foreach ($formReportCollection as $phase) {
        $firstAction = collect(data_get($phase, 'actions', []))->filter()->first();
        if ($firstAction) {
            $trainingFocus = $firstAction;
            break;
        }
    }
    if (!$trainingFocus && $cuesCollection->isNotEmpty()) {
        $firstCue = $cuesCollection->first();
        $trainingFocus = trim((string) data_get($firstCue, 'title'));
        if (!$trainingFocus) {
            $trainingFocus = trim((string) data_get($firstCue, 'description'));
        }
    }
    if (!$trainingFocus && $drillsCollection->isNotEmpty()) {
        $firstDrill = $drillsCollection->first();
        $trainingFocus = trim((string) data_get($firstDrill, 'title'));
    }
    if (!$trainingFocus && !empty($coachMessage)) {
        $trainingFocus = $coachMessage;
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Running Form Analysis — {{ $runnerName }}</title>
<style>
@page {
    margin: 24px 0 38px 0;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html,
body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 9.5pt;
    line-height: 1.48;
    color: #1e293b;
    background: #ffffff;
}

table {
    width: 100%;
    border-collapse: collapse;
}

img {
    border: 0;
}

.page-break {
    page-break-before: always;
}

.keep-together,
.summary-card,
.phase-card,
.finding-card,
.training-card,
.metric-row,
.coach-box {
    page-break-inside: avoid;
}

.page-content {
    padding: 0 22px 10px 22px;
}

/* HEADER */
.report-header {
    background: #0f172a;
    color: #ffffff;
    border-bottom: 4px solid #ccff00;
    padding: 18px 22px 16px 22px;
}

.report-logo {
    width: 112px;
    height: auto;
    display: block;
}

.logo-fallback {
    font-size: 16pt;
    font-weight: 900;
    font-style: italic;
    letter-spacing: -0.5px;
    color: #ccff00;
}

.report-kicker {
    margin-top: 4px;
    font-size: 7.8pt;
    font-weight: 700;
    letter-spacing: 0.9px;
    color: #94a3b8;
    text-transform: uppercase;
}

.report-title {
    font-size: 21pt;
    line-height: 1.08;
    font-weight: 900;
    letter-spacing: -0.6px;
    color: #ffffff;
}

.report-subtitle {
    margin-top: 4px;
    font-size: 8.5pt;
    color: #cbd5e1;
}

.runner-name {
    margin-top: 12px;
    font-size: 15pt;
    font-weight: 900;
    color: #ffffff;
}

.header-meta {
    margin-top: 4px;
    font-size: 8pt;
    line-height: 1.5;
    color: #94a3b8;
}

.header-meta strong {
    color: #ffffff;
}

/* FOOTER */
.page-footer {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    height: 27px;
    padding: 6px 22px 0 22px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #64748b;
    font-size: 7.2pt;
}

.page-number:before {
    content: counter(page);
}

.page-count:before {
    content: counter(pages);
}

/* COMMON */
.section {
    margin-top: 17px;
}

.section-heading {
    margin-bottom: 9px;
    padding-bottom: 5px;
    border-bottom: 1px solid #cbd5e1;
    font-size: 10.5pt;
    font-weight: 900;
    letter-spacing: 0.7px;
    text-transform: uppercase;
    color: #0f172a;
}

.section-heading .section-index {
    display: inline-block;
    min-width: 24px;
    margin-right: 6px;
    padding: 2px 4px;
    border-radius: 2px;
    background: #0f172a;
    color: #ccff00;
    text-align: center;
    font-size: 8pt;
}

.section-note {
    margin-top: -3px;
    margin-bottom: 9px;
    color: #64748b;
    font-size: 8.5pt;
}

.muted {
    color: #64748b;
}

.empty-state {
    padding: 12px;
    border: 1px dashed #cbd5e1;
    border-radius: 4px;
    background: #f8fafc;
    color: #64748b;
    font-size: 8.8pt;
    text-align: center;
}

.label {
    font-size: 7.5pt;
    font-weight: 800;
    letter-spacing: 0.55px;
    text-transform: uppercase;
    color: #64748b;
}

/* SCORE */
.score-panel {
    margin-top: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    overflow: hidden;
}

.score-main {
    width: 34%;
    padding: 15px 16px;
    vertical-align: middle;
    text-align: center;
}

.score-number {
    font-size: 29pt;
    line-height: 1;
    font-weight: 900;
    letter-spacing: -1px;
}

.score-denominator {
    margin-top: 1px;
    font-size: 8.5pt;
    font-weight: 700;
}

.score-category {
    margin-top: 7px;
    font-size: 9.5pt;
    font-weight: 900;
    letter-spacing: 0.6px;
}

.score-details {
    width: 66%;
    padding: 13px 16px;
    vertical-align: middle;
    background: #ffffff;
}

.score-description {
    margin-top: 3px;
    font-size: 9.2pt;
    color: #334155;
}

.coverage-table {
    margin-top: 10px;
}

.coverage-table td {
    padding: 5px 7px;
    border-top: 1px solid #f1f5f9;
    font-size: 8.3pt;
}

.coverage-value {
    font-weight: 800;
    text-align: right;
}

.coverage-high {
    color: #15803d;
}

.coverage-medium {
    color: #a16207;
}

.coverage-low {
    color: #b91c1c;
}

/* SUMMARY CARDS */
.summary-grid td {
    width: 33.333%;
    vertical-align: top;
}

.summary-grid td:nth-child(1) {
    padding-right: 5px;
}

.summary-grid td:nth-child(2) {
    padding-left: 5px;
    padding-right: 5px;
}

.summary-grid td:nth-child(3) {
    padding-left: 5px;
}

.summary-card {
    min-height: 132px;
    padding: 10px 11px;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    background: #ffffff;
}

.summary-card.strengths {
    border-top: 3px solid #16a34a;
}

.summary-card.priorities {
    border-top: 3px solid #dc2626;
}

.summary-card.focus {
    border-top: 3px solid #0f172a;
    background: #f8fafc;
}

.summary-card-title {
    margin-bottom: 7px;
    font-size: 8.3pt;
    font-weight: 900;
    letter-spacing: 0.55px;
    text-transform: uppercase;
    color: #0f172a;
}

.summary-item {
    margin-bottom: 6px;
    padding-left: 14px;
    position: relative;
    font-size: 8.4pt;
    line-height: 1.42;
    color: #334155;
}

.summary-item:last-child {
    margin-bottom: 0;
}

.summary-symbol {
    position: absolute;
    left: 0;
    top: 0;
    font-weight: 900;
}

.symbol-good {
    color: #15803d;
}

.symbol-priority {
    color: #b91c1c;
}

.focus-text {
    font-size: 9pt;
    line-height: 1.5;
    font-weight: 700;
    color: #0f172a;
}

/* COACH */
.coach-box {
    padding: 11px 13px;
    border-left: 4px solid #ccff00;
    border-radius: 4px;
    background: #f8fafc;
}

.coach-quote {
    font-size: 9.4pt;
    line-height: 1.55;
    color: #334155;
    font-style: italic;
}

/* STATUS */
.status-badge,
.severity-badge,
.metric-status {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 7.4pt;
    font-weight: 900;
    letter-spacing: 0.35px;
    text-transform: uppercase;
    white-space: nowrap;
}

.status-ok {
    color: #166534;
    background: #dcfce7;
}

.status-warn {
    color: #854d0e;
    background: #fef9c3;
}

.status-issue {
    color: #991b1b;
    background: #fee2e2;
}

.status-missing {
    color: #475569;
    background: #e2e8f0;
}

/* PHASE ANALYSIS */
.phase-card {
    margin-bottom: 10px;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #0f172a;
    border-radius: 5px;
    background: #ffffff;
}

.phase-header {
    padding: 9px 11px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.phase-number {
    width: 34px;
    font-size: 13pt;
    font-weight: 900;
    color: #cbd5e1;
    vertical-align: middle;
}

.phase-title {
    font-size: 10pt;
    font-weight: 900;
    color: #0f172a;
    vertical-align: middle;
}

.phase-status-cell {
    width: 118px;
    text-align: right;
    vertical-align: middle;
}

.phase-body {
    padding: 9px 11px 10px 11px;
}

.phase-summary {
    margin-bottom: 8px;
    color: #475569;
    font-size: 8.8pt;
    font-style: italic;
}

.phase-columns td {
    width: 50%;
    vertical-align: top;
}

.phase-columns td:first-child {
    padding-right: 8px;
}

.phase-columns td:last-child {
    padding-left: 8px;
    border-left: 1px solid #e2e8f0;
}

.phase-block-title {
    margin-bottom: 4px;
    font-size: 7.6pt;
    font-weight: 900;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #64748b;
}

.detail-item {
    position: relative;
    margin-bottom: 4px;
    padding-left: 12px;
    font-size: 8.6pt;
    line-height: 1.45;
    color: #334155;
}

.detail-item:last-child {
    margin-bottom: 0;
}

.detail-bullet {
    position: absolute;
    left: 0;
    top: 0;
    color: #94a3b8;
    font-weight: 900;
}

.correction-item {
    color: #1e3a8a;
}

/* FINDINGS */
.finding-card {
    margin-bottom: 8px;
    padding: 10px 11px;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
}

.severity-significant {
    border-left: 4px solid #dc2626;
    background: #fff7f7;
}

.severity-moderate {
    border-left: 4px solid #d97706;
    background: #fffbeb;
}

.severity-minor {
    border-left: 4px solid #2563eb;
    background: #f8fbff;
}

.severity-significant .severity-badge {
    color: #ffffff;
    background: #dc2626;
}

.severity-moderate .severity-badge {
    color: #ffffff;
    background: #d97706;
}

.severity-minor .severity-badge {
    color: #ffffff;
    background: #2563eb;
}

.finding-code {
    margin-left: 5px;
    font-family: 'DejaVu Sans Mono', monospace;
    font-size: 7.4pt;
    color: #64748b;
}

.finding-title {
    margin-top: 6px;
    font-size: 9.6pt;
    font-weight: 900;
    color: #0f172a;
}

.finding-description {
    margin-top: 3px;
    font-size: 8.8pt;
    line-height: 1.48;
    color: #334155;
}

.evidence-box {
    margin-top: 7px;
    padding: 7px 8px;
    border: 1px solid #e2e8f0;
    border-radius: 3px;
    background: #ffffff;
}

.evidence-label {
    margin-bottom: 4px;
    font-size: 7.4pt;
    font-weight: 900;
    letter-spacing: 0.45px;
    text-transform: uppercase;
    color: #64748b;
}

.evidence-table td {
    padding: 2px 4px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 8pt;
    vertical-align: top;
}

.evidence-table tr:last-child td {
    border-bottom: none;
}

.evidence-key {
    width: 42%;
    color: #64748b;
}

.evidence-value {
    font-weight: 700;
    color: #1e293b;
}

/* METRICS */
.metrics-table {
    border: 1px solid #e2e8f0;
}

.metrics-table th {
    padding: 7px 7px;
    border-bottom: 1px solid #cbd5e1;
    background: #0f172a;
    color: #ffffff;
    font-size: 7.5pt;
    font-weight: 900;
    letter-spacing: 0.35px;
    text-align: left;
    text-transform: uppercase;
}

.metrics-table td {
    padding: 6px 7px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
    font-size: 8.4pt;
    vertical-align: middle;
}

.metrics-table tr:nth-child(even) td {
    background: #f8fafc;
}

.metric-name {
    font-weight: 800;
    color: #0f172a;
}

.metric-value {
    font-family: 'DejaVu Sans Mono', monospace;
    font-weight: 800;
    text-align: right;
    color: #1e293b;
}

.metric-unit {
    color: #64748b;
    text-align: center;
}

.metric-neutral {
    color: #475569;
    background: #e2e8f0;
}

.metric-good {
    color: #166534;
    background: #dcfce7;
}

.metric-warning {
    color: #854d0e;
    background: #fef9c3;
}

.metric-priority {
    color: #991b1b;
    background: #fee2e2;
}

/* TRAINING */
.training-group {
    margin-bottom: 13px;
}

.training-group-title {
    margin-bottom: 7px;
    padding: 5px 8px;
    border-left: 4px solid #0f172a;
    background: #f1f5f9;
    color: #0f172a;
    font-size: 8pt;
    font-weight: 900;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.training-card {
    margin-bottom: 7px;
    padding: 9px 10px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    background: #ffffff;
}

.training-title {
    font-size: 9.4pt;
    font-weight: 900;
    color: #0f172a;
}

.training-description {
    margin-top: 3px;
    font-size: 8.7pt;
    line-height: 1.48;
    color: #475569;
}

.training-meta {
    margin-top: 7px;
    border-top: 1px solid #f1f5f9;
}

.training-meta td {
    padding: 4px 5px 0 0;
    font-size: 8pt;
    vertical-align: top;
}

.training-meta-key {
    width: 23%;
    color: #64748b;
}

.training-meta-value {
    font-weight: 700;
    color: #1e293b;
}

.retest-box {
    padding: 11px 12px;
    border: 1px solid #cbd5e1;
    border-left: 4px solid #ccff00;
    border-radius: 4px;
    background: #0f172a;
    color: #ffffff;
}

.retest-title {
    font-size: 8pt;
    font-weight: 900;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #ccff00;
}

.retest-text {
    margin-top: 4px;
    font-size: 9pt;
    line-height: 1.48;
    color: #e2e8f0;
}

/* APPENDIX */
.appendix-table th {
    padding: 6px 7px;
    border-bottom: 1px solid #cbd5e1;
    background: #f1f5f9;
    color: #475569;
    font-size: 7.5pt;
    font-weight: 900;
    letter-spacing: 0.35px;
    text-align: left;
    text-transform: uppercase;
}

.appendix-table td {
    padding: 5px 7px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 8.3pt;
    vertical-align: middle;
}

.appendix-index {
    width: 28px;
    color: #94a3b8;
}
</style>
</head>
<body>

<div class="page-footer">
    <table>
        <tr>
            <td style="text-align:left;">
                Ruang Lari &nbsp;|&nbsp; Running Form Analysis &nbsp;|&nbsp; Trial #{{ $attemptNumber }}
            </td>
            <td style="text-align:right;">
                Dibuat {{ $generatedAt ?? '—' }} &nbsp;|&nbsp; Hal. <span class="page-number"></span> / <span class="page-count"></span>
            </td>
        </tr>
    </table>
</div>

<!-- ================================================================
     PAGE 1 — EXECUTIVE SUMMARY
================================================================= -->
<div class="report-header">
    <table>
        <tr>
            <td style="width:29%; vertical-align:top;">
                @if($hasLogo)
                    <img src="{{ $logoPath }}" class="report-logo" alt="Ruang Lari">
                @else
                    <div class="logo-fallback">RUANG LARI</div>
                @endif
                <div class="report-kicker">Performance Lab</div>
            </td>
            <td style="width:71%; vertical-align:top; padding-left:18px;">
                <div class="report-title">Running Form Analysis</div>
                <div class="report-subtitle">Biomechanical Assessment &amp; Corrective Training Report</div>
                <div class="runner-name">{{ $runnerName }}</div>
                <div class="header-meta">
                    Trial #{{ $attemptNumber }}
                    &nbsp;&bull;&nbsp; {{ $analysisDate }}
                    @if(!empty($videoMeta))
                        &nbsp;&bull;&nbsp; {{ $videoMeta }}
                    @endif
                    <br>
                    Status analisis: <strong>{{ $trialStatus }}</strong>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="page-content">
    <div class="section">
        <div class="section-heading"><span class="section-index">01</span> Ringkasan Analisis</div>

        <div class="score-panel keep-together">
            <table>
                <tr>
                    <td class="score-main" style="background:{{ $scoreBackground }}; color:{{ $scoreColor }};">
                        @if($scoreValue !== null)
                            <div class="score-number">{{ $scoreValue }}</div>
                            <div class="score-denominator">/ 100</div>
                        @else
                            <div class="score-number">N/A</div>
                        @endif
                        <div class="score-category">{{ $scoreSymbol }} {{ $scoreCategory }}</div>
                    </td>
                    <td class="score-details">
                        <div class="label">Interpretasi skor</div>
                        <div class="score-description">{{ $scoreDescription }}</div>

                        <table class="coverage-table">
                            <tr>
                                <td>Kelengkapan analisis fase</td>
                                <td class="coverage-value {{ $coverageClass }}">
                                    {{ $coverageLabel }}
                                    @if($formReportCollection->isNotEmpty())
                                        ({{ $availablePhaseCount }}/{{ $formReportCollection->count() }})
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Metrik biomekanik tersedia</td>
                                <td class="coverage-value">{{ $metricsCollection->count() }}</td>
                            </tr>
                            <tr>
                                <td>Temuan teridentifikasi</td>
                                <td class="coverage-value">{{ $findingsCollection->count() }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="summary-card strengths">
                        <div class="summary-card-title">Hal yang Sudah Baik</div>
                        @forelse($positivesCollection as $positive)
                            @php
                                $positiveTitle = is_array($positive)
                                    ? (data_get($positive, 'title') ?? implode(' ', array_filter(array_values($positive), 'is_scalar')))
                                    : $positive;
                                $positiveDescription = is_array($positive) ? data_get($positive, 'description') : null;
                            @endphp
                            <div class="summary-item">
                                <span class="summary-symbol symbol-good">✓</span>
                                <strong>{{ $positiveTitle }}</strong>
                                @if($positiveDescription)
                                    <br><span class="muted">{{ $positiveDescription }}</span>
                                @endif
                            </div>
                        @empty
                            <div class="summary-item muted">
                                <span class="summary-symbol">–</span>
                                Belum ada poin positif yang tercatat.
                            </div>
                        @endforelse
                    </div>
                </td>
                <td>
                    <div class="summary-card priorities">
                        <div class="summary-card-title">Temuan Prioritas</div>
                        @forelse($priorityFindings as $finding)
                            @php
                                $severity = data_get($finding, 'severity', 'minor');
                                $severitySymbol = $severityConfig[$severity]['symbol'] ?? '!';
                            @endphp
                            <div class="summary-item">
                                <span class="summary-symbol symbol-priority">{{ $severitySymbol }}</span>
                                <strong>{{ $findingTitle($finding) }}</strong>
                            </div>
                        @empty
                            <div class="summary-item muted">
                                <span class="summary-symbol">✓</span>
                                Tidak ada koreksi prioritas yang tercatat.
                            </div>
                        @endforelse
                    </div>
                </td>
                <td>
                    <div class="summary-card focus">
                        <div class="summary-card-title">Fokus Latihan Utama</div>
                        @if($trainingFocus)
                            <div class="focus-text">{{ $trainingFocus }}</div>
                        @else
                            <div class="summary-item muted" style="padding-left:0;">
                                Fokus latihan belum tersedia dari hasil analisis.
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if(!empty($coachMessage))
        <div class="section">
            <div class="section-heading"><span class="section-index">02</span> Umpan Balik Pelatih</div>
            <div class="coach-box">
                <div class="coach-quote">&ldquo;{{ $coachMessage }}&rdquo;</div>
            </div>
        </div>
    @endif
</div>

<!-- ================================================================
     PAGE 2 — PHASE ANALYSIS
================================================================= -->
@if($formReportCollection->isNotEmpty())
<div class="page-break"></div>
<div class="page-content">
    <div class="section">
        <div class="section-heading"><span class="section-index">03</span> Analisis Fase Lari</div>
        <div class="section-note">Temuan dibatasi pada poin terpenting agar laporan mudah dipindai dan langsung dapat diterapkan.</div>

        @foreach($formReportCollection as $phaseIndex => $phase)
            @php
                $phaseCode    = data_get($phase, 'code', '');
                $phaseTitle   = $phaseLabels[$phaseCode] ?? data_get($phase, 'title') ?? $readableCode($phaseCode);
                $phaseStatus  = data_get($phase, 'status', 'missing');
                $phaseConfig  = $statusConfig[$phaseStatus] ?? $statusConfig['missing'];
                $phaseSummary = data_get($phase, 'summary');
                $phaseFinds   = collect(data_get($phase, 'findings', []))->filter()->take(2);
                $phaseActions = collect(data_get($phase, 'actions', []))->filter()->take(2);
            @endphp

            <div class="phase-card">
                <div class="phase-header">
                    <table>
                        <tr>
                            <td class="phase-number">{{ str_pad($phaseIndex + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="phase-title">{{ $phaseTitle }}</td>
                            <td class="phase-status-cell">
                                <span class="status-badge {{ $phaseConfig['class'] }}">
                                    {{ $phaseConfig['symbol'] }} {{ $phaseConfig['label'] }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="phase-body">
                    @if($phaseSummary)
                        <div class="phase-summary">{{ $phaseSummary }}</div>
                    @endif

                    @if($phaseFinds->isNotEmpty() || $phaseActions->isNotEmpty())
                        <table class="phase-columns">
                            <tr>
                                <td>
                                    <div class="phase-block-title">Observasi</div>
                                    @forelse($phaseFinds as $findingText)
                                        <div class="detail-item">
                                            <span class="detail-bullet">•</span>
                                            {{ $findingText }}
                                        </div>
                                    @empty
                                        <div class="detail-item muted" style="padding-left:0;">Tidak ada temuan khusus.</div>
                                    @endforelse
                                </td>
                                <td>
                                    <div class="phase-block-title">Koreksi</div>
                                    @forelse($phaseActions as $actionText)
                                        <div class="detail-item correction-item">
                                            <span class="detail-bullet">→</span>
                                            {{ $actionText }}
                                        </div>
                                    @empty
                                        <div class="detail-item muted" style="padding-left:0;">Tidak ada koreksi yang tercatat.</div>
                                    @endforelse
                                </td>
                            </tr>
                        </table>
                    @else
                        <div class="empty-state">Tidak ada detail observasi atau koreksi pada fase ini.</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- ================================================================
     PAGE 3 — PRIORITY FINDINGS & METRICS
================================================================= -->
@if($findingsCollection->isNotEmpty() || $metricGroups->isNotEmpty())
<div class="page-break"></div>
<div class="page-content">
    @if($findingsCollection->isNotEmpty())
        <div class="section">
            <div class="section-heading"><span class="section-index">04</span> Temuan Prioritas</div>
            <div class="section-note">Urutan ditentukan berdasarkan tingkat keparahan temuan yang tersedia pada data analisis.</div>

            @foreach($findingsCollection as $finding)
                @php
                    $severity       = data_get($finding, 'severity', 'minor');
                    $severityInfo   = $severityConfig[$severity] ?? $severityConfig['minor'];
                    $findingCode    = data_get($finding, 'finding_code', '—');
                    $findingDescription = data_get($finding, 'description')
                        ?? data_get($finding, 'explanation');
                    if (!$findingDescription && data_get($finding, 'explanation_key')) {
                        $findingDescription = $readableCode(data_get($finding, 'explanation_key'));
                    }
                    $evidence = data_get($finding, 'evidence_json');
                    $evidenceRows = is_array($evidence) || is_object($evidence)
                        ? $flattenEvidence($evidence)
                        : [];
                @endphp

                <div class="finding-card {{ $severityInfo['class'] }}">
                    <div>
                        <span class="severity-badge">{{ $severityInfo['symbol'] }} {{ $severityInfo['label'] }}</span>
                        <span class="finding-code">{{ $findingCode }}</span>
                    </div>
                    <div class="finding-title">{{ $findingTitle($finding) }}</div>

                    @if($findingDescription && $findingDescription !== $findingTitle($finding))
                        <div class="finding-description">{{ $findingDescription }}</div>
                    @endif

                    @if(!empty($evidence))
                        <div class="evidence-box">
                            <div class="evidence-label">Bukti Pendukung</div>

                            @if(!empty($evidenceRows))
                                <table class="evidence-table">
                                    @foreach($evidenceRows as $evidenceRow)
                                        <tr>
                                            <td class="evidence-key">{{ $evidenceRow['label'] }}</td>
                                            <td class="evidence-value">{{ $evidenceRow['value'] }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <div class="finding-description" style="margin-top:0;">{{ $evidence }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($metricGroups->isNotEmpty())
        <div class="section">
            <div class="section-heading"><span class="section-index">05</span> Metrik Biomekanik</div>
            <div class="section-note">Nilai ditampilkan secara netral. Status hanya digunakan apabila tersedia dari backend.</div>

            <table class="metrics-table">
                <thead>
                    <tr>
                        <th style="width:30%;">Metrik</th>
                        <th style="width:13%; text-align:right;">Kiri</th>
                        <th style="width:13%; text-align:right;">Kanan</th>
                        <th style="width:14%; text-align:right;">Umum</th>
                        <th style="width:10%; text-align:center;">Satuan</th>
                        <th style="width:20%;">Interpretasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($metricGroups as $metricCode => $metricItems)
                        @php
                            $leftMetric = $metricItems->first(function ($metric) {
                                return strtolower((string) data_get($metric, 'side')) === 'left';
                            });
                            $rightMetric = $metricItems->first(function ($metric) {
                                return strtolower((string) data_get($metric, 'side')) === 'right';
                            });
                            $generalMetric = $metricItems->first(function ($metric) {
                                $side = strtolower((string) data_get($metric, 'side'));
                                return $side === '' || $side === 'none' || $side === 'both' || $side === 'general';
                            });
                            if (!$generalMetric && !$leftMetric && !$rightMetric) {
                                $generalMetric = $metricItems->first();
                            }

                            $unit = data_get($metricItems->first(), 'unit', '—');
                            $interpretation = data_get($metricItems->first(), 'interpretation')
                                ?? data_get($metricItems->first(), 'status')
                                ?? data_get($metricItems->first(), 'assessment');

                            $interpretationKey = strtolower((string) $interpretation);
                            $metricStatusClass = 'metric-neutral';
                            if (in_array($interpretationKey, ['ok', 'normal', 'optimal', 'good'], true)) {
                                $metricStatusClass = 'metric-good';
                            } elseif (in_array($interpretationKey, ['warn', 'warning', 'attention', 'moderate'], true)) {
                                $metricStatusClass = 'metric-warning';
                            } elseif (in_array($interpretationKey, ['issue', 'significant', 'priority', 'abnormal'], true)) {
                                $metricStatusClass = 'metric-priority';
                            }

                            $metricName = $metricLabels[$metricCode] ?? $readableCode($metricCode);
                        @endphp
                        <tr class="metric-row">
                            <td class="metric-name">{{ $metricName }}</td>
                            <td class="metric-value">
                                {{ $leftMetric ? $formatMetricValue($leftMetric) : '—' }}
                            </td>
                            <td class="metric-value">
                                {{ $rightMetric ? $formatMetricValue($rightMetric) : '—' }}
                            </td>
                            <td class="metric-value">
                                {{ $generalMetric ? $formatMetricValue($generalMetric) : '—' }}
                            </td>
                            <td class="metric-unit">{{ $unit ?: '—' }}</td>
                            <td>
                                @if($interpretation)
                                    <span class="metric-status {{ $metricStatusClass }}">{{ $readableCode($interpretation) }}</span>
                                @else
                                    <span class="metric-status metric-neutral">Belum tersedia</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif

<!-- ================================================================
     PAGE 4 — TRAINING PROGRAM
================================================================= -->
@if($cuesCollection->isNotEmpty() || $drillsCollection->isNotEmpty() || $strengthCollection->isNotEmpty())
<div class="page-break"></div>
<div class="page-content">
    <div class="section">
        <div class="section-heading"><span class="section-index">06</span> Program Koreksi dan Latihan</div>
        <div class="section-note">Detail dosis hanya ditampilkan jika tersedia pada data. Report tidak menambahkan set, repetisi, atau frekuensi secara otomatis.</div>

        @if($cuesCollection->isNotEmpty())
            <div class="training-group">
                <div class="training-group-title">Petunjuk Teknik Lari (Running Cues)</div>
                @foreach($cuesCollection as $cue)
                    <div class="training-card">
                        <div class="training-title">{{ data_get($cue, 'title', 'Petunjuk Teknik') }}</div>
                        @if(data_get($cue, 'description'))
                            <div class="training-description">{{ data_get($cue, 'description') }}</div>
                        @endif

                        @php
                            $cuePurpose   = data_get($cue, 'purpose') ?? data_get($cue, 'goal');
                            $cueDose      = data_get($cue, 'dose');
                            $cueFrequency = data_get($cue, 'frequency');
                            $cuePriority  = data_get($cue, 'priority');
                        @endphp
                        @if($cuePurpose || $cueDose || $cueFrequency || $cuePriority)
                            <table class="training-meta">
                                @if($cuePurpose)
                                    <tr><td class="training-meta-key">Tujuan</td><td class="training-meta-value">{{ $cuePurpose }}</td></tr>
                                @endif
                                @if($cueDose)
                                    <tr><td class="training-meta-key">Dosis</td><td class="training-meta-value">{{ $cueDose }}</td></tr>
                                @endif
                                @if($cueFrequency)
                                    <tr><td class="training-meta-key">Frekuensi</td><td class="training-meta-value">{{ $cueFrequency }}</td></tr>
                                @endif
                                @if($cuePriority)
                                    <tr><td class="training-meta-key">Prioritas</td><td class="training-meta-value">{{ $readableCode($cuePriority) }}</td></tr>
                                @endif
                            </table>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($drillsCollection->isNotEmpty())
            <div class="training-group">
                <div class="training-group-title">Latihan Teknik (Technique Drills)</div>
                @foreach($drillsCollection as $drill)
                    <div class="training-card">
                        <div class="training-title">{{ data_get($drill, 'title', 'Latihan Teknik') }}</div>
                        @if(data_get($drill, 'description'))
                            <div class="training-description">{{ data_get($drill, 'description') }}</div>
                        @endif

                        @php
                            $drillPurpose   = data_get($drill, 'purpose') ?? data_get($drill, 'goal');
                            $drillSets      = data_get($drill, 'sets');
                            $drillReps      = data_get($drill, 'reps') ?? data_get($drill, 'repetitions');
                            $drillDuration  = data_get($drill, 'duration');
                            $drillFrequency = data_get($drill, 'frequency');
                            $drillPriority  = data_get($drill, 'priority');
                        @endphp
                        @if($drillPurpose || $drillSets || $drillReps || $drillDuration || $drillFrequency || $drillPriority)
                            <table class="training-meta">
                                @if($drillPurpose)
                                    <tr><td class="training-meta-key">Tujuan</td><td class="training-meta-value">{{ $drillPurpose }}</td></tr>
                                @endif
                                @if($drillSets)
                                    <tr><td class="training-meta-key">Set</td><td class="training-meta-value">{{ $drillSets }}</td></tr>
                                @endif
                                @if($drillReps)
                                    <tr><td class="training-meta-key">Repetisi</td><td class="training-meta-value">{{ $drillReps }}</td></tr>
                                @endif
                                @if($drillDuration)
                                    <tr><td class="training-meta-key">Durasi</td><td class="training-meta-value">{{ $drillDuration }}</td></tr>
                                @endif
                                @if($drillFrequency)
                                    <tr><td class="training-meta-key">Frekuensi</td><td class="training-meta-value">{{ $drillFrequency }}</td></tr>
                                @endif
                                @if($drillPriority)
                                    <tr><td class="training-meta-key">Prioritas</td><td class="training-meta-value">{{ $readableCode($drillPriority) }}</td></tr>
                                @endif
                            </table>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($strengthCollection->isNotEmpty())
            <div class="training-group">
                <div class="training-group-title">Latihan Kekuatan (Strength Exercises)</div>
                @foreach($strengthCollection as $strength)
                    <div class="training-card">
                        <div class="training-title">{{ data_get($strength, 'title', 'Latihan Kekuatan') }}</div>
                        @if(data_get($strength, 'description'))
                            <div class="training-description">{{ data_get($strength, 'description') }}</div>
                        @endif

                        @php
                            $strengthPurpose   = data_get($strength, 'purpose') ?? data_get($strength, 'goal');
                            $strengthSets      = data_get($strength, 'sets');
                            $strengthReps      = data_get($strength, 'reps') ?? data_get($strength, 'repetitions');
                            $strengthDuration  = data_get($strength, 'duration');
                            $strengthFrequency = data_get($strength, 'frequency');
                            $strengthPriority  = data_get($strength, 'priority');
                        @endphp
                        @if($strengthPurpose || $strengthSets || $strengthReps || $strengthDuration || $strengthFrequency || $strengthPriority)
                            <table class="training-meta">
                                @if($strengthPurpose)
                                    <tr><td class="training-meta-key">Tujuan</td><td class="training-meta-value">{{ $strengthPurpose }}</td></tr>
                                @endif
                                @if($strengthSets)
                                    <tr><td class="training-meta-key">Set</td><td class="training-meta-value">{{ $strengthSets }}</td></tr>
                                @endif
                                @if($strengthReps)
                                    <tr><td class="training-meta-key">Repetisi</td><td class="training-meta-value">{{ $strengthReps }}</td></tr>
                                @endif
                                @if($strengthDuration)
                                    <tr><td class="training-meta-key">Durasi</td><td class="training-meta-value">{{ $strengthDuration }}</td></tr>
                                @endif
                                @if($strengthFrequency)
                                    <tr><td class="training-meta-key">Frekuensi</td><td class="training-meta-value">{{ $strengthFrequency }}</td></tr>
                                @endif
                                @if($strengthPriority)
                                    <tr><td class="training-meta-key">Prioritas</td><td class="training-meta-value">{{ $readableCode($strengthPriority) }}</td></tr>
                                @endif
                            </table>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="retest-box keep-together">
            <div class="retest-title">Rekomendasi Evaluasi Berikutnya</div>
            <div class="retest-text">
                {{ $retestRecommendation ?? 'Lakukan evaluasi ulang setelah menjalankan program koreksi secara konsisten.' }}
            </div>
        </div>
    </div>
</div>
@endif

<!-- ================================================================
     APPENDIX — TECHNICAL TIMELINE
================================================================= -->
@if($eventsCollection->isNotEmpty())
<div class="page-break"></div>
<div class="page-content">
    <div class="section">
        <div class="section-heading"><span class="section-index">A</span> Lampiran Teknis — Gait Event Timeline</div>
        <div class="section-note">Data waktu berikut disediakan sebagai referensi teknis dan bukan bagian utama dari interpretasi coaching.</div>

        <table class="appendix-table">
            <thead>
                <tr>
                    <th style="width:7%;">#</th>
                    <th style="width:48%;">Event</th>
                    <th style="width:18%;">Sisi</th>
                    <th style="width:27%; text-align:right;">Waktu Relatif</th>
                </tr>
            </thead>
            <tbody>
                @foreach($eventsCollection as $eventIndex => $event)
                    @php
                        $eventType = data_get($event, 'event_type');
                        $eventLabel = match($eventType) {
                            'initial_contact'   => 'Landing (Foot Strike)',
                            'midstance'         => 'Mid-Stance',
                            'toe_off'           => 'Push-Off (Toe-Off)',
                            'max_swing_flexion' => 'Knee Pull (Swing)',
                            default             => $readableCode($eventType),
                        };
                        $eventTimestamp = (float) data_get($event, 'timestamp_ms', 0);
                        $baseTimestamp  = (float) ($eventBaseMs ?? 0);
                        $relativeSecond = number_format(($eventTimestamp - $baseTimestamp) / 1000, 3, '.', '');
                    @endphp
                    <tr>
                        <td class="appendix-index">{{ $eventIndex + 1 }}</td>
                        <td>{{ $eventLabel }}</td>
                        <td>{{ ucfirst((string) (data_get($event, 'side') ?: '—')) }}</td>
                        <td class="metric-value">+{{ $relativeSecond }}s</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</body>
</html>