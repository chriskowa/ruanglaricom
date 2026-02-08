<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 32px; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; }
        .wrap { border: 4px solid #0f172a; padding: 28px; height: 100%; }
        .row { display: flex; justify-content: space-between; align-items: center; }
        .title { font-size: 34px; font-weight: 700; letter-spacing: 1px; }
        .subtitle { font-size: 14px; color: #334155; margin-top: 6px; }
        .name { font-size: 44px; font-weight: 800; margin-top: 24px; text-transform: uppercase; }
        .meta { margin-top: 16px; font-size: 16px; }
        .meta strong { display: inline-block; width: 140px; }
        .footer { position: absolute; bottom: 40px; left: 40px; right: 40px; font-size: 12px; color: #475569; }
        .badge { display: inline-block; padding: 8px 12px; border: 2px solid #0f172a; font-weight: 700; }
        .logo { width: 140px; height: 140px; object-fit: contain; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="row">
        <div>
            <div class="title">E-CERTIFICATE</div>
            <div class="subtitle">{{ $raceName }}</div>
        </div>
        @if(!empty($logoDataUri))
            <img class="logo" src="{{ $logoDataUri }}" alt="Logo">
        @else
            <div class="badge">RACE</div>
        @endif
    </div>

    <div class="subtitle" style="margin-top: 18px;">Sertifikat ini diberikan kepada:</div>
    <div class="name">{{ $participantName }}</div>

    <div class="meta">
        <div><strong>BIB</strong> {{ $bibNumber }}</div>
        <div><strong>Posisi</strong> {{ $finalPosition ?? '-' }}</div>
        <div><strong>Waktu Total</strong> {{ $totalTime }}</div>
        <div><strong>Tanggal</strong> {{ $issuedAt }}</div>
    </div>

    <div class="footer">
        Sertifikat ini dihasilkan otomatis oleh sistem. Nomor sertifikat: {{ $certificateId }}.
    </div>
</div>
</body>
</html>

