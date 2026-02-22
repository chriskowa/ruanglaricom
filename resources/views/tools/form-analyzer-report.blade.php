<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Form Analyzer</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #0f172a;
            background: #f8fafc;
        }
        .page {
            padding: 24px 28px;
        }
        .hero {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 16px;
            background: #ffffff;
        }
        .hero-table {
            width: 100%;
            border-collapse: collapse;
        }
        .hero-left {
            width: 60%;
            vertical-align: top;
        }
        .hero-right {
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        .hero-title {
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 4px 0;
            color: #0f172a;
        }
        .hero-subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 0 0 10px 0;
        }
        .hero-badges {
            margin-top: 6px;
        }
        .pill {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 999px;
            margin-right: 4px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .pill-primary {
            background: #e0f2fe;
            color: #0c4a6e;
            border: 1px solid #bae6fd;
        }
        .pill-secondary {
            background: #eef2ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }
        .score-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            background: #f8fafc;
        }
        .score-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .score-value {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin: 2px 0 4px 0;
        }
        .score-bar {
            height: 6px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
            margin: 6px 0 6px 0;
        }
        .score-bar-fill {
            height: 100%;
            background: #0ea5e9;
        }
        .score-meta {
            font-size: 10px;
            color: #475569;
            margin-top: 4px;
        }
        .score-date {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 6px;
        }
        .row {
            display: flex;
            width: 100%;
        }
        .col-6 {
            width: 50%;
            box-sizing: border-box;
        }
        .section {
            margin-bottom: 14px;
        }
        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #334155;
            margin-bottom: 8px;
            padding: 6px 8px;
            background: #eef2ff;
            border-radius: 6px;
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            box-sizing: border-box;
            background: #ffffff;
        }
        .metric-grid {
            display: flex;
            flex-wrap: wrap;
        }
        .metric {
            width: 50%;
            padding: 4px 4px 4px 0;
            box-sizing: border-box;
        }
        .metric-label {
            font-size: 10px;
            color: #64748b;
        }
        .metric-value {
            font-size: 12px;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 6px;
            border-radius: 999px;
            border: 1px solid #cbd5f5;
            background-color: #e0f2fe;
            color: #075985;
        }
        .list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .list li {
            margin-bottom: 6px;
            padding: 6px 8px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .small {
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>
<div class="page">
    @php
        $stringify = function ($value) {
            if ($value === null) {
                return '';
            }
            if (is_array($value)) {
                $flat = [];
                array_walk_recursive($value, function ($v) use (&$flat) {
                    $flat[] = $v;
                });
                return trim(implode(' ', array_map('strval', $flat)));
            }
            if (is_bool($value)) {
                return $value ? 'Ya' : 'Tidak';
            }
            return (string) $value;
        };
    @endphp
    @php
        $scoreValue = is_numeric($score) ? max(0, min(100, (int) $score)) : null;
    @endphp
    <div class="hero">
        <table class="hero-table">
            <tr>
                <td class="hero-left">
                    <div class="hero-title">Form Analyzer Report</div>
                    <div class="hero-subtitle">Ringkasan analisis form lari berbasis video dan AI biomekanik</div>
                    <div class="hero-badges">
                        <span class="pill pill-primary">AI Powered</span>
                        <span class="pill pill-secondary">Beta</span>
                    </div>
                </td>
                <td class="hero-right">
                    <div class="score-card">
                        <div class="score-label">Form Score</div>
                        <div class="score-value">{{ $score ?? '--' }}</div>
                        @if($scoreValue !== null)
                            <div class="score-bar">
                                <div class="score-bar-fill" style="width: {{ $scoreValue }}%;"></div>
                            </div>
                        @endif
                        @if(!empty($videoScore))
                            <div class="score-meta">Video Score: <strong>{{ $videoScore }}</strong></div>
                        @endif
                        <div class="score-date">Dibuat pada {{ now()->format('d M Y H:i') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Video</div>
        <div class="card">
            @php
                $duration = $display['duration_human'] ?? '--';
                $resolution = $display['resolution'] ?? '--';
                $fps = $display['fps_human'] ?? '--';
                $sizeHuman = $display['size_human'] ?? ($meta['original']['size_human'] ?? null);
                $saved = $compression['saved_percent'] ?? null;
            @endphp
            <div class="metric-grid">
                <div class="metric">
                    <div class="metric-label">Durasi</div>
                    <div class="metric-value">{{ $duration }}</div>
                </div>
                <div class="metric">
                    <div class="metric-label">Resolusi</div>
                    <div class="metric-value">{{ $resolution }}</div>
                </div>
                <div class="metric">
                    <div class="metric-label">Frame rate</div>
                    <div class="metric-value">{{ $fps }}</div>
                </div>
                <div class="metric">
                    <div class="metric-label">Ukuran file</div>
                    <div class="metric-value">
                        @if($sizeHuman)
                            {{ $sizeHuman }}
                            @if($saved !== null)
                                (hemat {{ $saved }}%)
                            @endif
                        @else
                            --
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($positives))
        <div class="section">
            <div class="section-title">Hal yang sudah bagus</div>
            <div class="card">
                <ul class="list">
                    @foreach($positives as $item)
                        <li>{{ $stringify($item) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if(!empty($issues))
        <div class="section">
            <div class="section-title">Catatan penting</div>
            <div class="card">
                <ul class="list">
                    @foreach($issues as $item)
                        <li>{{ $stringify($item) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if(!empty($suggestions))
        <div class="section">
            <div class="section-title">Saran perbaikan</div>
            <div class="card">
                <ul class="list">
                    @foreach($suggestions as $item)
                        <li>{{ $stringify($item) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if(!empty($formIssues))
        <div class="section">
            <div class="section-title">Analisis form (beta)</div>
            <div class="card">
                <ul class="list">
                    @foreach($formIssues as $item)
                        <li>{{ $stringify($item) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if(!empty($formReport))
        <div class="section">
            <div class="section-title">Laporan form lengkap</div>
            @foreach($formReport as $section)
                @php
                    $title = is_array($section) ? $stringify($section['title'] ?? 'Bagian Form') : $stringify($section);
                    $status = is_array($section) ? strtolower($stringify($section['status'] ?? 'ok')) : 'ok';
                    $summary = is_array($section) ? $stringify($section['summary'] ?? null) : null;
                    $findings = is_array($section) && isset($section['findings']) && is_array($section['findings']) ? $section['findings'] : [];
                    $actions = is_array($section) && isset($section['actions']) && is_array($section['actions']) ? $section['actions'] : [];
                    $strength = is_array($section) && isset($section['strength']) && is_array($section['strength']) ? $section['strength'] : [];
                @endphp
                <div class="card" style="margin-bottom: 6px;">
                    <div class="row" style="margin-bottom: 4px;">
                        <div class="col-6">
                            <div class="metric-value">{{ $title }}</div>
                            @if($summary)
                                <div class="small">{{ $summary }}</div>
                            @endif
                        </div>
                        <div class="col-6" style="text-align: right;">
                            <span class="badge">{{ strtoupper($status) }}</span>
                        </div>
                    </div>
                    @if(!empty($findings))
                        <div class="small" style="margin-bottom: 2px;">Temuan:</div>
                        <ul class="list">
                            @foreach($findings as $item)
                                <li>{{ $stringify($item) }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($actions))
                        <div class="small" style="margin-top: 4px; margin-bottom: 2px;">Tindakan disarankan:</div>
                        <ul class="list">
                            @foreach($actions as $item)
                                <li>{{ $stringify($item) }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($strength))
                        <div class="small" style="margin-top: 4px; margin-bottom: 2px;">Latihan penguatan:</div>
                        <ul class="list">
                            @foreach($strength as $item)
                                <li>{{ $stringify($item) }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($strengthPlan))
        <div class="section">
            <div class="section-title">Solusi penguatan</div>
            <div class="card">
                <ul class="list">
                    @foreach($strengthPlan as $item)
                        <li>{{ $stringify($item) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if(!empty($recoveryPlan))
        <div class="section">
            <div class="section-title">Pemulihan dan pengobatan awal</div>
            <div class="card">
                <ul class="list">
                    @foreach($recoveryPlan as $item)
                        <li>{{ $stringify($item) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if(!empty($coachMessage))
        <div class="section">
            <div class="section-title">Catatan pelatih</div>
            <div class="card">
                <p style="margin: 0;">{{ $stringify($coachMessage) }}</p>
            </div>
        </div>
    @endif
</div>
</body>
</html>
