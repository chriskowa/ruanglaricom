<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Models\RaceCertificate;
use App\Models\RaceSession;
use App\Models\RaceSessionLap;
use App\Models\RaceSessionParticipant;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class RaceMasterApiController extends Controller
{
    public function docs()
    {
        return response()->json([
            'success' => true,
            'endpoints' => [
                ['method' => 'POST', 'path' => '/api/tools/race-master/races', 'desc' => 'Create race config (name, optional logo)'],
                ['method' => 'PUT', 'path' => '/api/tools/race-master/races/{race}', 'desc' => 'Update race config'],
                ['method' => 'POST', 'path' => '/api/tools/race-master/races/{race}/participants/bulk', 'desc' => 'Upsert participants by BIB'],
                ['method' => 'POST', 'path' => '/api/tools/race-master/races/{race}/sessions', 'desc' => 'Start a race session'],
                ['method' => 'POST', 'path' => '/api/tools/race-master/sessions/{session}/laps', 'desc' => 'Record lap by bib_number'],
                ['method' => 'POST', 'path' => '/api/tools/race-master/sessions/{session}/finish', 'desc' => 'Finish session and compute final standings'],
                ['method' => 'POST', 'path' => '/api/tools/race-master/sessions/{session}/certificates', 'desc' => 'Generate certificates (requires PDF library)'],
                ['method' => 'POST', 'path' => '/api/tools/race-master/sessions/{session}/poster', 'desc' => 'Generate IG story poster (1080x1920)'],
                ['method' => 'GET', 'path' => '/tools/race-master/certificates/{certificate}', 'desc' => 'Download certificate PDF (auth)'],
            ],
        ]);
    }

    public function index()
    {
        $races = Race::where('created_by', Auth::id())
            ->with(['sessions' => function ($q) {
                $q->latest();
            }])
            ->latest()
            ->get()
            ->map(function ($race) {
                $latestSession = $race->sessions->first();

                return [
                    'id' => $race->id,
                    'name' => $race->name,
                    'logo_url' => $race->logo_path ? Storage::disk('public')->url($race->logo_path) : null,
                    'created_at' => $race->created_at->format('Y-m-d H:i'),
                    'latest_category' => $latestSession ? $latestSession->category : null,
                    'latest_distance' => $latestSession ? $latestSession->distance_km : null,
                ];
            });

        return response()->json([
            'success' => true,
            'races' => $races,
        ]);
    }

    public function show(Race $race)
    {
        if ($race->created_by !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $participants = $race->participants()
            ->select('id', 'bib_number', 'name', 'predicted_time_ms')
            ->orderByRaw('CAST(bib_number AS UNSIGNED) ASC')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'bib' => $p->bib_number,
                    'name' => $p->name,
                    'predictedTimeMs' => $p->predicted_time_ms,
                ];
            });

        $latestSession = $race->sessions()->latest()->first();

        return response()->json([
            'success' => true,
            'race' => [
                'id' => $race->id,
                'name' => $race->name,
                'logo_url' => $race->logo_path ? Storage::disk('public')->url($race->logo_path) : null,
                'category' => $latestSession ? $latestSession->category : null,
                'distance_km' => $latestSession ? $latestSession->distance_km : null,
            ],
            'participants' => $participants,
        ]);
    }

    public function storeRace(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        $race = Race::create([
            'name' => trim($validated['name']),
            'logo_path' => null,
            'created_by' => Auth::id(),
        ]);

        if ($request->hasFile('logo')) {
            $race->logo_path = $this->storeLogo($request->file('logo'));
            $race->save();
        }

        return response()->json([
            'success' => true,
            'race' => [
                'id' => $race->id,
                'name' => $race->name,
                'logo_url' => $race->logo_path ? Storage::disk('public')->url($race->logo_path) : null,
            ],
        ]);
    }

    public function updateRace(Request $request, Race $race)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        $race->name = trim($validated['name']);

        if ($request->hasFile('logo')) {
            if ($race->logo_path) {
                Storage::disk('public')->delete($race->logo_path);
            }
            $race->logo_path = $this->storeLogo($request->file('logo'));
        }

        $race->save();

        return response()->json([
            'success' => true,
            'race' => [
                'id' => $race->id,
                'name' => $race->name,
                'logo_url' => $race->logo_path ? Storage::disk('public')->url($race->logo_path) : null,
            ],
        ]);
    }

    public function upsertParticipants(Request $request, Race $race)
    {
        $validated = $request->validate([
            'participants' => 'required|array|min:1|max:2000',
            'participants.*.bib_number' => 'required|string|max:32',
            'participants.*.name' => 'required|string|max:255',
            'participants.*.predicted_time_ms' => 'nullable|integer|min:0|max:86400000',
            'participants.*.participant_id' => 'nullable|integer|exists:participants,id',
        ]);

        $items = collect($validated['participants'])
            ->map(function ($p) {
                return [
                    'participant_id' => $p['participant_id'] ?? null,
                    'bib_number' => trim((string) $p['bib_number']),
                    'name' => trim((string) $p['name']),
                    'predicted_time_ms' => array_key_exists('predicted_time_ms', $p) ? $p['predicted_time_ms'] : null,
                ];
            })
            ->filter(fn ($p) => $p['bib_number'] !== '' && $p['name'] !== '')
            ->values();

        $createdOrUpdated = [];

        DB::transaction(function () use ($race, $items, &$createdOrUpdated) {
            foreach ($items as $p) {
                $row = RaceSessionParticipant::updateOrCreate(
                    ['race_id' => $race->id, 'bib_number' => $p['bib_number']],
                    [
                        'participant_id' => $p['participant_id'],
                        'name' => $p['name'],
                        'predicted_time_ms' => $p['predicted_time_ms'],
                    ]
                );

                $createdOrUpdated[] = [
                    'id' => $row->id,
                    'bib_number' => $row->bib_number,
                    'name' => $row->name,
                    'predicted_time_ms' => $row->predicted_time_ms,
                ];
            }
        }, 3);

        return response()->json([
            'success' => true,
            'participants' => $createdOrUpdated,
        ]);
    }

    public function startSession(Request $request, Race $race)
    {
        $validated = $request->validate([
            'category' => 'nullable|string|max:100',
            'distance_km' => 'nullable|numeric|min:0.1|max:999.999',
        ]);

        $slug = null;
        if (Schema::hasColumn('race_sessions', 'slug')) {
            for ($i = 0; $i < 5; $i++) {
                $candidate = Str::lower(Str::random(10));
                if (! RaceSession::query()->where('slug', $candidate)->exists()) {
                    $slug = $candidate;
                    break;
                }
            }
        }

        $session = RaceSession::create([
            'race_id' => $race->id,
            'slug' => $slug,
            'category' => $validated['category'] ?? null,
            'distance_km' => isset($validated['distance_km']) ? (float) $validated['distance_km'] : null,
            'started_at' => now(),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'race_id' => $session->race_id,
                'slug' => $session->slug,
                'started_at' => $session->started_at?->toISOString(),
                'public_results_url' => $session->slug ? route('tools.race-master.results', ['slug' => $session->slug]) : null,
            ],
        ]);
    }

    public function storeLap(Request $request, RaceSession $session)
    {
        $validated = $request->validate([
            'bib_number' => 'required|string|max:32',
            'total_time_ms' => 'required|integer|min:0|max:86400000',
            'recorded_at' => 'nullable|date',
        ]);

        $bib = trim((string) $validated['bib_number']);
        $rsp = RaceSessionParticipant::query()
            ->where('race_id', $session->race_id)
            ->where('bib_number', $bib)
            ->first();

        if (! $rsp) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown BIB',
            ], 404);
        }

        $lastLap = RaceSessionLap::query()
            ->where('race_session_id', $session->id)
            ->where('race_session_participant_id', $rsp->id)
            ->orderByDesc('lap_number')
            ->first();

        $lapNumber = $lastLap ? ($lastLap->lap_number + 1) : 1;
        $total = (int) $validated['total_time_ms'];
        $prevTotal = $lastLap ? (int) $lastLap->total_time_ms : 0;
        $lapTime = max(0, $total - $prevTotal);
        $prevLapTime = $lastLap ? (int) $lastLap->lap_time_ms : null;
        $delta = $prevLapTime === null ? null : ($lapTime - $prevLapTime);

        $recordedAt = isset($validated['recorded_at']) ? Carbon::parse($validated['recorded_at']) : now();

        $lap = DB::transaction(function () use ($session, $rsp, $lapNumber, $lapTime, $total, $delta, $recordedAt) {
            $lap = RaceSessionLap::create([
                'race_id' => $session->race_id,
                'race_session_id' => $session->id,
                'race_session_participant_id' => $rsp->id,
                'participant_id' => $rsp->participant_id,
                'lap_number' => $lapNumber,
                'lap_time_ms' => $lapTime,
                'total_time_ms' => $total,
                'delta_ms' => $delta,
                'position' => null,
                'recorded_at' => $recordedAt,
            ]);

            $position = RaceSessionLap::query()
                ->where('race_session_id', $session->id)
                ->where('lap_number', $lapNumber)
                ->where(function ($q) use ($lap) {
                    $q->where('recorded_at', '<', $lap->recorded_at)
                        ->orWhere(function ($q2) use ($lap) {
                            $q2->where('recorded_at', '=', $lap->recorded_at)->where('id', '<', $lap->id);
                        });
                })
                ->count() + 1;

            $lap->position = $position;
            $lap->save();

            return $lap;
        }, 3);

        return response()->json([
            'success' => true,
            'lap' => [
                'id' => $lap->id,
                'bib_number' => $rsp->bib_number,
                'name' => $rsp->name,
                'lap_number' => $lap->lap_number,
                'lap_time_ms' => $lap->lap_time_ms,
                'total_time_ms' => $lap->total_time_ms,
                'delta_ms' => $lap->delta_ms,
                'position' => $lap->position,
                'recorded_at' => $lap->recorded_at?->toISOString(),
            ],
        ]);
    }

    public function finishSession(Request $request, RaceSession $session)
    {
        if (! $session->ended_at) {
            $session->ended_at = now();
            if (Schema::hasColumn('race_sessions', 'slug') && ! $session->slug) {
                for ($i = 0; $i < 5; $i++) {
                    $candidate = Str::lower(Str::random(10));
                    if (! RaceSession::query()->where('slug', $candidate)->exists()) {
                        $session->slug = $candidate;
                        break;
                    }
                }
            }
            $session->save();
        }

        $final = $this->computeStandings($session);
        $generated = $this->generateCertificatesInternal($session, $final);
        $certificates = RaceCertificate::query()
            ->where('race_session_id', $session->id)
            ->with(['raceSessionParticipant'])
            ->get()
            ->map(function (RaceCertificate $c) {
                return [
                    'id' => $c->id,
                    'bib_number' => $c->raceSessionParticipant?->bib_number,
                    'download_url' => route('tools.race-master.certificates.download', ['certificate' => $c->id]),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'race_id' => $session->race_id,
                'slug' => $session->slug,
                'ended_at' => $session->ended_at?->toISOString(),
                'public_results_url' => $session->slug ? route('tools.race-master.results', ['slug' => $session->slug]) : null,
            ],
            'standings' => $final,
            'certificates_generated' => $generated,
            'certificates' => $certificates,
        ]);
    }

    public function generateCertificates(Request $request, RaceSession $session)
    {
        if (! $session->ended_at) {
            return response()->json([
                'success' => false,
                'message' => 'Session belum selesai.',
            ], 409);
        }

        $standings = $this->computeStandings($session);
        $generated = $this->generateCertificatesInternal($session, $standings);
        $certificates = RaceCertificate::query()
            ->where('race_session_id', $session->id)
            ->with(['raceSessionParticipant'])
            ->get()
            ->map(function (RaceCertificate $c) {
                return [
                    'id' => $c->id,
                    'bib_number' => $c->raceSessionParticipant?->bib_number,
                    'download_url' => route('tools.race-master.certificates.download', ['certificate' => $c->id]),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'certificates_generated' => $generated,
            'certificates' => $certificates,
        ]);
    }

    public function generatePoster(Request $request, RaceSession $session)
    {
        // 1. Validasi
        $request->validate([
            'background' => 'nullable|file|mimes:png,jpg,jpeg|max:8192', // Naikkan limit jika perlu
        ]);

        $race = $session->race()->firstOrFail();
        $standings = $this->computeStandings($session);

        // 2. Setup Canvas (1080x1920 - Story Size)
        $w = 1080;
        $h = 1920;
        $img = imagecreatetruecolor($w, $h);

        // 3. Warna
        $white = imagecolorallocate($img, 255, 255, 255);
        $gold = imagecolorallocate($img, 255, 215, 0); // Untuk Juara 1
        $grey = imagecolorallocate($img, 200, 200, 200);
        $blackOptions = imagecolorallocate($img, 15, 23, 42); // Fallback bg

        // Isi background default
        imagefilledrectangle($img, 0, 0, $w, $h, $blackOptions);

        // 4. Proses Background Image (Aspect Fill / Object-fit: Cover)
        if ($request->hasFile('background')) {
            try {
                $bgFile = $request->file('background');
                $ext = strtolower($bgFile->getClientOriginalExtension());

                $src = match ($ext) {
                    'jpg', 'jpeg' => @imagecreatefromjpeg($bgFile->getRealPath()),
                    'png' => @imagecreatefrompng($bgFile->getRealPath()),
                    default => null,
                };

                if ($src) {
                    $srcW = imagesx($src);
                    $srcH = imagesy($src);

                    // Hitung rasio untuk "Cover" area
                    $ratioSrc = $srcW / $srcH;
                    $ratioDst = $w / $h;

                    if ($ratioSrc > $ratioDst) {
                        // Gambar lebih lebar dari canvas: Crop kiri-kanan
                        $newH = $h;
                        $newW = $h * $ratioSrc;
                    } else {
                        // Gambar lebih tinggi dari canvas: Crop atas-bawah
                        $newW = $w;
                        $newH = $w / $ratioSrc;
                    }

                    // Copy dan resize ke canvas
                    // Posisi X dan Y diatur minus agar gambar terpusat (Center Crop)
                    $x = ($w - $newW) / 2;
                    $y = ($h - $newH) / 2;

                    imagecopyresampled($img, $src, (int) $x, (int) $y, 0, 0, (int) $newW, (int) $newH, $srcW, $srcH);
                    imagedestroy($src);
                }
            } catch (\Exception $e) {
                // Jika gagal load gambar, biarkan background default
            }
        }

        // 5. Tambahkan Overlay Gelap (Agar teks terbaca)
        // Alpha: 0 (Opaque) - 127 (Transparent). 40 adalah sekitar 30% gelap.
        $overlay = imagecolorallocatealpha($img, 0, 0, 0, 40);
        imagefilledrectangle($img, 0, 0, $w, $h, $overlay);

        // 6. Setup Font
        // PASTIKAN FILE INI ADA. Jika tidak, ganti ke path font yang valid di server Anda.
        $fontBold = public_path('fonts/Inter-Bold.ttf');
        $fontReg = public_path('fonts/Inter-Regular.ttf');

        // Fallback jika font tidak ada (kembali ke imagestring)
        $hasFont = file_exists($fontBold) && file_exists($fontReg);

        // --- Header Section ---
        $cursorY = 80;

        // Logo Race
        if ($race->logo_path && Storage::disk('public')->exists($race->logo_path)) {
            $logoPath = Storage::disk('public')->path($race->logo_path);
            $ext = pathinfo($logoPath, PATHINFO_EXTENSION);

            $logo = match (strtolower($ext)) {
                'png' => @imagecreatefrompng($logoPath),
                'jpg', 'jpeg' => @imagecreatefromjpeg($logoPath),
                default => null
            };

            if ($logo) {
                $lw = imagesx($logo);
                $lh = imagesy($logo);

                // Resize logo max width 250px
                $targetLW = 250;
                $scale = $targetLW / $lw;
                $targetLH = $lh * $scale;

                imagecopyresampled($img, $logo, 50, 50, 0, 0, (int) $targetLW, (int) $targetLH, $lw, $lh);
                imagedestroy($logo);

                $cursorY = 50 + $targetLH + 40; // Update cursor ke bawah logo
            }
        }

        // Judul Race
        if ($hasFont) {
            imagettftext($img, 32, 0, 50, $cursorY, $white, $fontBold, strtoupper($race->name));
            imagettftext($img, 18, 0, 50, $cursorY + 40, $grey, $fontReg, 'OFFICIAL RESULTS • TOP FINISHERS');
        } else {
            // Fallback font bawaan
            imagestring($img, 5, 50, $cursorY, strtoupper(substr($race->name, 0, 20)), $white);
        }

        // --- Leaderboard Section ---
        $cursorY += 100;

        // Background kotak transparan untuk list
        $boxColor = imagecolorallocatealpha($img, 255, 255, 255, 110); // Putih transparan (glass effect)
        // Atau Hitam transparan: imagecolorallocatealpha($img, 0, 0, 0, 80);

        $boxH = 1000;
        // imagefilledrectangle($img, 40, $cursorY, $w - 40, $cursorY + $boxH, $boxColor); // Opsional: Kotak pembungkus

        $top = array_slice($standings, 0, 5);
        $rank = 1;

        foreach ($top as $row) {
            $rsp = RaceSessionParticipant::find($row['race_session_participant_id']);
            if (! $rsp) {
                continue;
            }

            // Warna Rank: Emas untuk #1, Putih sisanya
            $rankColor = ($rank === 1) ? $gold : $white;
            $name = mb_strimwidth($rsp->name, 0, 25, '...'); // Potong nama jika kepanjangan

            if ($hasFont) {
                // RANK (Besar di kiri)
                imagettftext($img, 40, 0, 60, $cursorY + 50, $rankColor, $fontBold, '#'.$rank);

                // NAMA
                imagettftext($img, 28, 0, 180, $cursorY + 45, $white, $fontBold, $name);

                // BIB & DETAILS
                imagettftext($img, 16, 0, 180, $cursorY + 80, $grey, $fontReg, 'BIB '.$rsp->bib_number);

                // WAKTU (Kanan)
                $timeText = $this->formatMs((int) $row['total_time_ms']);
                // Hitung lebar teks waktu agar rata kanan
                $bbox = imagettfbbox(24, 0, $fontBold, $timeText);
                $textW = abs($bbox[4] - $bbox[0]);
                imagettftext($img, 24, 0, $w - 60 - $textW, $cursorY + 55, $white, $fontBold, $timeText);

                // Garis pemisah tipis
                if ($rank < 5) {
                    $lineColor = imagecolorallocatealpha($img, 255, 255, 255, 100);
                    imageline($img, 60, $cursorY + 110, $w - 60, $cursorY + 110, $lineColor);
                }

            } else {
                // Fallback
                imagestring($img, 5, 50, $cursorY, "#$rank ".$name, $white);
                imagestring($img, 4, $w - 150, $cursorY, $this->formatMs((int) $row['total_time_ms']), $white);
            }

            $cursorY += 130; // Jarak antar baris
            $rank++;
        }

        // --- Footer ---
        $footerText = 'Generated by RuangLari.com';
        if ($hasFont) {
            $bbox = imagettfbbox(14, 0, $fontReg, $footerText);
            $fw = abs($bbox[4] - $bbox[0]);
            imagettftext($img, 14, 0, ($w - $fw) / 2, $h - 40, $grey, $fontReg, $footerText);
        } else {
            imagestring($img, 3, ($w / 2) - 50, $h - 40, $footerText, $grey);
        }

        // Output
        ob_start();
        imagepng($img, null, 6); // Compression 6 (balance speed/size)
        $content = ob_get_clean();
        imagedestroy($img);

        return response($content, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="result-poster.png"',
        ]);
    }

    public function downloadCertificate(RaceCertificate $certificate)
    {
        $path = $certificate->pdf_path;
        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path, 'certificate-'.$certificate->id.'.pdf');
    }

    public function downloadCertificatePublic(RaceCertificate $certificate)
    {
        $path = $certificate->pdf_path;
        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path, 'certificate-'.$certificate->id.'.pdf');
    }

    public function publicResultsPage(string $slug)
    {
        $session = RaceSession::query()
            ->where('slug', $slug)
            ->with(['race'])
            ->firstOrFail();

        return view('tools.race-master-results', [
            'slug' => $slug,
            'raceName' => $session->race?->name,
        ]);
    }

    public function publicResultsJson(string $slug)
    {
        $session = RaceSession::query()
            ->where('slug', $slug)
            ->with(['race'])
            ->firstOrFail();

        $standings = $this->computeStandings($session);
        $participants = RaceSessionParticipant::query()
            ->where('race_id', $session->race_id)
            ->get()
            ->keyBy('id');

        $rows = [];
        $rankById = [];
        foreach ($standings as $idx => $row) {
            $rankById[$row['race_session_participant_id']] = $idx + 1;
        }

        foreach ($participants as $id => $p) {
            $stat = null;
            foreach ($standings as $s) {
                if ((int) $s['race_session_participant_id'] === (int) $id) {
                    $stat = $s;
                    break;
                }
            }

            $rank = $rankById[$id] ?? null;
            $totalTimeMs = $stat ? (int) $stat['total_time_ms'] : null;
            $laps = $stat ? (int) $stat['laps'] : 0;

            $certificate = RaceCertificate::query()
                ->where('race_session_id', $session->id)
                ->where('race_session_participant_id', $id)
                ->first();

            $certificateUrl = $certificate
                ? URL::temporarySignedRoute('tools.race-master.certificates.public', now()->addDays(30), ['certificate' => $certificate->id])
                : null;

            $rows[] = [
                'participant_id' => (int) $id,
                'bib' => $p->bib_number,
                'name' => $p->name,
                'rank' => $rank,
                'laps' => $laps,
                'total_time_ms' => $totalTimeMs,
                'total_time' => $totalTimeMs !== null ? $this->formatMs($totalTimeMs) : null,
                'status' => $totalTimeMs !== null ? 'finished' : ($session->ended_at ? 'dnf' : 'running'),
                'certificate_url' => $certificateUrl,
            ];
        }

        usort($rows, function ($a, $b) {
            $ra = $a['rank'] ?? PHP_INT_MAX;
            $rb = $b['rank'] ?? PHP_INT_MAX;
            if ($ra === $rb) {
                return strcmp((string) $a['bib'], (string) $b['bib']);
            }

            return $ra <=> $rb;
        });

        return response()->json([
            'success' => true,
            'race' => [
                'id' => $session->race_id,
                'name' => $session->race?->name,
                'logo_url' => ($session->race && $session->race->logo_path) ? Storage::disk('public')->url($session->race->logo_path) : null,
            ],
            'session' => [
                'id' => $session->id,
                'slug' => $session->slug,
                'category' => $session->category,
                'distance_km' => $session->distance_km !== null ? (float) $session->distance_km : null,
                'started_at' => $session->started_at?->toISOString(),
                'ended_at' => $session->ended_at?->toISOString(),
                'public_results_url' => route('tools.race-master.results', ['slug' => $session->slug]),
            ],
            'results' => $rows,
        ]);
    }

    public function publicParticipantPoster(Request $request, string $slug, string $bib)
    {
        $request->validate([
            'background' => 'nullable|file|mimes:png,jpg,jpeg|max:4096',
        ]);

        $session = RaceSession::query()
            ->where('slug', $slug)
            ->with(['race'])
            ->firstOrFail();

        if (! $session->ended_at) {
            return response()->json(['message' => 'Session belum selesai.'], 409);
        }

        $rsp = RaceSessionParticipant::query()
            ->where('race_id', $session->race_id)
            ->where('bib_number', $bib)
            ->first();

        if (! $rsp) {
            return response()->json(['message' => 'Unknown BIB'], 404);
        }

        $standings = $this->computeStandings($session);
        $rank = null;
        $stat = null;
        foreach ($standings as $idx => $row) {
            if ((int) $row['race_session_participant_id'] === (int) $rsp->id) {
                $rank = $idx + 1;
                $stat = $row;
                break;
            }
        }
        if (! $stat) {
            return response()->json(['message' => 'Participant has no results'], 404);
        }

        $png = $this->renderParticipantPosterPng($session, $rsp, (int) $rank, $stat, $request->file('background'));

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function publicParticipantCertificate(Request $request, string $slug, string $bib)
    {
        $session = RaceSession::query()
            ->where('slug', $slug)
            ->with(['race'])
            ->firstOrFail();

        if (! $session->ended_at) {
            return response()->json(['message' => 'Session belum selesai.'], 409);
        }

        $rsp = RaceSessionParticipant::query()
            ->where('race_id', $session->race_id)
            ->where('bib_number', $bib)
            ->first();

        if (! $rsp) {
            return response()->json(['message' => 'Unknown BIB'], 404);
        }

        $standings = $this->computeStandings($session);
        $rank = null;
        $stat = null;
        foreach ($standings as $idx => $row) {
            if ((int) $row['race_session_participant_id'] === (int) $rsp->id) {
                $rank = $idx + 1;
                $stat = $row;
                break;
            }
        }
        if (! $stat) {
            return response()->json(['message' => 'Participant has no results'], 404);
        }

        $certificate = $this->generateCertificateForParticipant($session, $rsp, (int) $rank, (int) $stat['total_time_ms']);
        $downloadUrl = URL::temporarySignedRoute('tools.race-master.certificates.public', now()->addDays(30), ['certificate' => $certificate->id]);

        return response()->json([
            'success' => true,
            'certificate' => [
                'id' => $certificate->id,
                'download_url' => $downloadUrl,
            ],
        ]);
    }

    private function computeStandings(RaceSession $session): array
    {
        return RaceSessionLap::query()
            ->select('race_session_participant_id', DB::raw('MAX(lap_number) as laps'), DB::raw('MAX(total_time_ms) as total_time_ms'))
            ->where('race_session_id', $session->id)
            ->groupBy('race_session_participant_id')
            ->get()
            ->map(function ($row) {
                return [
                    'race_session_participant_id' => (int) $row->race_session_participant_id,
                    'laps' => (int) $row->laps,
                    'total_time_ms' => (int) $row->total_time_ms,
                ];
            })
            ->sortBy([
                ['laps', 'desc'],
                ['total_time_ms', 'asc'],
            ])
            ->values()
            ->all();
    }

    private function generateCertificatesInternal(RaceSession $session, array $standings): int
    {
        $race = $session->race()->first();
        if (! $race) {
            return 0;
        }

        $logoDataUri = $this->raceLogoDataUri($race);
        $generated = 0;

        $options = new Options;
        $options->set('dpi', 300);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $rank = 1;
        foreach ($standings as $row) {
            $rsp = RaceSessionParticipant::query()->whereKey((int) $row['race_session_participant_id'])->first();
            if (! $rsp) {
                $rank++;

                continue;
            }

            $html = view('tools.race-certificate', [
                'certificateId' => 'RM-'.$session->id.'-'.$rsp->id,
                'raceName' => $race->name,
                'logoDataUri' => $logoDataUri,
                'participantName' => $rsp->name,
                'bibNumber' => $rsp->bib_number,
                'finalPosition' => $rank,
                'totalTime' => $this->formatMs((int) $row['total_time_ms']),
                'issuedAt' => now()->format('d M Y'),
            ])->render();

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('a4', 'landscape');
            $dompdf->render();
            $output = $dompdf->output();

            $dir = 'race-certificates/'.$session->id;
            $path = $dir.'/certificate-'.$rsp->id.'.pdf';
            Storage::disk('local')->put($path, $output);

            RaceCertificate::updateOrCreate(
                [
                    'race_id' => $race->id,
                    'race_session_id' => $session->id,
                    'race_session_participant_id' => $rsp->id,
                ],
                [
                    'participant_id' => $rsp->participant_id,
                    'final_position' => $rank,
                    'total_time_ms' => (int) $row['total_time_ms'],
                    'pdf_path' => $path,
                    'created_by' => Auth::id(),
                ]
            );

            $generated++;
            $rank++;
        }

        return $generated;
    }

    private function raceLogoDataUri(Race $race): ?string
    {
        if (! $race->logo_path) {
            return null;
        }
        if (! Storage::disk('public')->exists($race->logo_path)) {
            return null;
        }
        $full = Storage::disk('public')->path($race->logo_path);
        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mime = $ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : 'image/png';
        $bin = @file_get_contents($full);
        if ($bin === false) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($bin);
    }

    private function formatMs(int $ms): string
    {
        $ms = max(0, $ms);
        $cs = (int) floor(($ms % 1000) / 10);
        $totalSeconds = (int) floor($ms / 1000);
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%d:%02d.%02d', $minutes, $seconds, $cs);
    }

    private function storeLogo($file): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file);

        if ($image->width() < 200 || $image->height() < 200) {
            throw ValidationException::withMessages([
                'logo' => 'Resolusi logo minimal 200x200 pixel.',
            ]);
        }

        $folder = 'race-logos';
        if (! Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $filename = uniqid().'_'.time().'.png';
        $path = $folder.'/'.$filename;
        $fullPath = Storage::disk('public')->path($path);

        $image->toPng()->save($fullPath);

        return $path;
    }

    public function generateParticipantPoster(Request $request, RaceSession $session, $identifier)
    {
        $request->validate([
            'background' => 'nullable|file|mimes:png,jpg,jpeg|max:4096',
        ]);

        $race = $session->race()->firstOrFail();

        $rsp = RaceSessionParticipant::query()
            ->where('race_id', $session->race_id)
            ->where(function ($q) use ($identifier) {
                $q->where('id', $identifier)->orWhere('bib_number', $identifier);
            })
            ->first();

        if (! $rsp) {
            return response()->json(['message' => 'Participant not found'], 404);
        }

        $standings = $this->computeStandings($session);
        $rank = null;
        $stat = null;

        foreach ($standings as $index => $row) {
            if ($row['race_session_participant_id'] == $rsp->id) {
                $rank = $index + 1;
                $stat = $row;
                break;
            }
        }

        if (! $stat) {
            return response()->json(['message' => 'Participant has no results yet'], 404);
        }

        $png = $this->renderParticipantPosterPng($session, $rsp, (int) $rank, $stat, $request->file('background'));

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function renderParticipantPosterPng(RaceSession $session, RaceSessionParticipant $rsp, int $rank, array $stat, $backgroundFile): string
    {
        $race = $session->race()->first();

        $w = 1080;
        $h = 1920;

        $img = imagecreatetruecolor($w, $h);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // 1. Background
        $bg = null;
        if ($backgroundFile) {
            $ext = strtolower($backgroundFile->getClientOriginalExtension());
            if (in_array($ext, ['jpg', 'jpeg'])) {
                $bg = @imagecreatefromjpeg($backgroundFile->getRealPath());
            } elseif ($ext === 'png') {
                $bg = @imagecreatefrompng($backgroundFile->getRealPath());
            }
        }

        if ($bg) {
            $srcW = imagesx($bg);
            $srcH = imagesy($bg);
            $scale = max($w / max($srcW, 1), $h / max($srcH, 1));
            $newW = (int) ceil($srcW * $scale);
            $newH = (int) ceil($srcH * $scale);
            $tmp = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($tmp, $bg, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
            $x = (int) floor(($newW - $w) / 2);
            $y = (int) floor(($newH - $h) / 2);
            imagecopy($img, $tmp, 0, 0, $x, $y, $w, $h);
            imagedestroy($tmp);
            imagedestroy($bg);
        } else {
            // Default elegant dark gradient background if no image
            $topColor = imagecolorallocate($img, 15, 23, 42); // Dark slate
            $bottomColor = imagecolorallocate($img, 30, 41, 59); // Slightly lighter
            imagefilledrectangle($img, 0, 0, $w, $h, $topColor);
            // Simple gradient effect for default bg
            for ($i = 0; $i < $h; $i += 5) {
                $alpha = (int) (127 * ($i / $h)); // Fade out
                $c = imagecolorallocatealpha($img, 0, 0, 0, $alpha); // Darken bottom
                imagefilledrectangle($img, 0, $i, $w, $i + 5, $c);
            }
        }

        // 2. Gradients / Overlays (Top and Bottom)
        // Top Gradient (Dark at top -> Transparent)
        $gradientHeight = 500;
        for ($y = 0; $y < $gradientHeight; $y++) {
            // Alpha: 40 (darkish) -> 127 (transparent)
            $alpha = 40 + (int) (87 * ($y / $gradientHeight));
            $color = imagecolorallocatealpha($img, 0, 0, 0, $alpha);
            imageline($img, 0, $y, $w, $y, $color);
        }

        // Bottom Gradient (Transparent -> Dark at bottom)
        $bottomGradientHeight = 600;
        $startY = $h - $bottomGradientHeight;
        for ($y = 0; $y < $bottomGradientHeight; $y++) {
            // Alpha: 127 (transparent) -> 10 (very dark)
            $alpha = 127 - (int) (117 * ($y / $bottomGradientHeight));
            $color = imagecolorallocatealpha($img, 0, 0, 0, $alpha);
            imageline($img, 0, $startY + $y, $w, $startY + $y, $color);
        }

        // Colors
        $white = imagecolorallocate($img, 255, 255, 255);
        $offWhite = imagecolorallocate($img, 241, 245, 249);
        $gray = imagecolorallocate($img, 148, 163, 184); // Slate 400
        $accent = imagecolorallocate($img, 250, 204, 21); // Yellow 400
        $brand = imagecolorallocate($img, 129, 140, 248); // Indigo 400

        $font = $this->posterFontPath();

        // 3. Top Section: Logo & Race Name
        $logoY = 80;
        $contentX = 80;

        if ($race && $race->logo_path && Storage::disk('public')->exists($race->logo_path)) {
            $logoPath = Storage::disk('public')->path($race->logo_path);
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $logo = null;
            if ($ext === 'png') {
                $logo = @imagecreatefrompng($logoPath);
            }
            if ($ext === 'jpg' || $ext === 'jpeg') {
                $logo = @imagecreatefromjpeg($logoPath);
            }

            if ($logo) {
                $lw = imagesx($logo);
                $lh = imagesy($logo);
                $maxH = 120; // Max height for logo
                $scale = min(300 / $lw, $maxH / $lh); // Constraint width too

                $tw = (int) ($lw * $scale);
                $th = (int) ($lh * $scale);

                $dst = imagecreatetruecolor($tw, $th);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $t = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $tw, $th, $t);
                imagecopyresampled($dst, $logo, 0, 0, 0, 0, $tw, $th, $lw, $lh);

                imagecopy($img, $dst, $contentX, $logoY, 0, 0, $tw, $th);
                imagedestroy($dst);
                imagedestroy($logo);

                $logoY += $th + 30; // Push text down
            }
        } else {
            $logoY += 20;
        }

        $raceName = $race ? strtoupper(mb_substr($race->name, 0, 35)) : 'RACE EVENT';
        $this->gdText($img, $raceName, $contentX, $logoY + 40, 32, $white, $font, 'left');
        $this->gdText($img, 'OFFICIAL RESULT', $contentX, $logoY + 90, 22, $brand, $font, 'left');

        // 4. Middle Section: Rank & Participant
        // Rank Badge (Right aligned)
        $rankText = 'RANK #'.$rank;
        $this->gdText($img, $rankText, $w - 80, 200, 48, $accent, $font, 'right');

        // Participant Info (Bottom Left, above stats)
        $nameY = $h - 550;
        $this->gdText($img, $rsp->name, 80, $nameY, 55, $white, $font, 'left');

        // BIB Pill
        $bibText = 'BIB '.$rsp->bib_number;
        // Draw a pill background for BIB
        // (Simplified as text for now, maybe add a rectangle behind it if needed, but text with accent color is clean)
        $this->gdText($img, $bibText, 80, $nameY + 80, 32, $brand, $font, 'left');

        // 5. Bottom Section: Stats Grid
        // Calculate Data
        $distanceKm = $session->distance_km !== null ? (float) $session->distance_km : null;
        if ($distanceKm === null && $session->category) {
            $distanceKm = $this->guessDistanceKm($session->category);
        }

        $totalMs = (int) ($stat['total_time_ms'] ?? 0);
        $totalSec = max(1, (int) floor($totalMs / 1000));
        $timeStr = $this->formatMs($totalMs);

        $distStr = $session->category ?: ($distanceKm ? (rtrim(rtrim(number_format($distanceKm, 2, '.', ''), '0'), '.').' KM') : '-');

        $paceStr = '-';
        $speedStr = '-';
        if ($distanceKm && $distanceKm > 0) {
            $paceSec = (int) round($totalSec / $distanceKm);
            $paceStr = sprintf('%d:%02d', intdiv($paceSec, 60), $paceSec % 60);
            $speed = ($distanceKm / ($totalSec / 3600));
            $speedStr = number_format($speed, 1);
        }

        // Draw Stats
        $statsY = $h - 250;
        $colWidth = $w / 3;

        // Col 1: Distance
        $c1 = $colWidth * 0.5;
        $this->gdText($img, 'DISTANCE', $c1, $statsY, 20, $gray, $font, 'center');
        $this->gdText($img, $distStr, $c1, $statsY + 50, 40, $white, $font, 'center');

        // Col 2: Time
        $c2 = $colWidth * 1.5;
        $this->gdText($img, 'FINISH TIME', $c2, $statsY, 20, $gray, $font, 'center');
        $this->gdText($img, $timeStr, $c2, $statsY + 50, 40, $white, $font, 'center');

        // Col 3: Pace
        $c3 = $colWidth * 2.5;
        $this->gdText($img, 'AVG PACE', $c3, $statsY, 20, $gray, $font, 'center');
        $this->gdText($img, $paceStr.' /km', $c3, $statsY + 50, 40, $white, $font, 'center');

        // Footer Line
        $lineY = $h - 120;
        imageline($img, 100, $lineY, $w - 100, $lineY, $gray);
        $this->gdText($img, 'Powered by RuangLari.com', $w / 2, $h - 60, 18, $gray, $font, 'center');

        ob_start();
        imagepng($img, null, 7);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    private function posterFontPath(): ?string
    {
        $candidates = [
            'C:\Windows\Fonts\arialbd.ttf',
            'C:\Windows\Fonts\arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

        foreach ($candidates as $p) {
            if (is_string($p) && file_exists($p)) {
                return $p;
            }
        }

        return null;
    }

    private function gdText($img, string $text, int $x, int $y, int $size, $color, ?string $font, string $align): void
    {
        if (! $font) {
            imagestring($img, 5, $x, $y, $text, $color);

            return;
        }

        $bbox = imagettfbbox($size, 0, $font, $text);
        $textWidth = abs($bbox[2] - $bbox[0]);

        $drawX = $x;
        if ($align === 'center') {
            $drawX = (int) round($x - ($textWidth / 2));
        } elseif ($align === 'right') {
            $drawX = (int) round($x - $textWidth);
        }

        imagettftext($img, $size, 0, $drawX, $y, $color, $font, $text);
    }

    private function guessDistanceKm(string $category): ?float
    {
        $c = strtolower(trim($category));
        if ($c === '') {
            return null;
        }

        $map = [
            '5k' => 5.0,
            '10k' => 10.0,
            'hm' => 21.1,
            'fm' => 42.195,
            'half marathon' => 21.1,
            'full marathon' => 42.195,
        ];
        if (isset($map[$c])) {
            return $map[$c];
        }

        if (str_contains($c, 'marathon') && ! str_contains($c, 'half')) {
            return 42.195;
        }
        if (str_contains($c, 'half')) {
            return 21.1;
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*k/', $c, $m)) {
            return (float) $m[1];
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*m/', $c, $m)) {
            return ((float) $m[1]) / 1000.0;
        }

        return null;
    }

    private function generateCertificateForParticipant(RaceSession $session, RaceSessionParticipant $rsp, int $rank, int $totalTimeMs): RaceCertificate
    {
        $race = $session->race()->firstOrFail();

        $logoDataUri = $this->raceLogoDataUri($race);

        $options = new Options;
        $options->set('dpi', 300);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $html = view('tools.race-certificate', [
            'certificateId' => 'RM-'.$session->id.'-'.$rsp->id,
            'raceName' => $race->name,
            'logoDataUri' => $logoDataUri,
            'participantName' => $rsp->name,
            'bibNumber' => $rsp->bib_number,
            'finalPosition' => $rank,
            'totalTime' => $this->formatMs($totalTimeMs),
            'issuedAt' => now()->format('d M Y'),
        ])->render();

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();
        $output = $dompdf->output();

        $dir = 'race-certificates/'.$session->id;
        $path = $dir.'/certificate-'.$rsp->id.'.pdf';
        Storage::disk('local')->put($path, $output);

        return RaceCertificate::updateOrCreate(
            [
                'race_id' => $race->id,
                'race_session_id' => $session->id,
                'race_session_participant_id' => $rsp->id,
            ],
            [
                'participant_id' => $rsp->participant_id,
                'final_position' => $rank,
                'total_time_ms' => $totalTimeMs,
                'pdf_path' => $path,
                'created_by' => Auth::id(),
            ]
        );
    }
}
