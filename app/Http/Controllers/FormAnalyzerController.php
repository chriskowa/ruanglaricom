<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class FormAnalyzerController extends Controller
{
    private const MAX_CONCURRENT = 5;

    public function index()
    {
        return view('tools.form-analyzer');
    }

    public function analyze(Request $request)
    {
        $dir = null;
        $originalPath = null;
        $slot = null;
        $slotLock = null;
        try {
            $data = $request->validate([
                'upload_video' => ['nullable', 'boolean'],
                'video' => [
                    'nullable',
                    'file',
                    'max:153600',
                    'mimetypes:video/mp4,video/quicktime,video/webm,video/x-matroska',
                    'mimes:mp4,mov,webm,mkv',
                ],
                'metrics' => ['nullable', 'string', 'max:20000'],
                'client_duration' => ['nullable', 'numeric', 'min:0', 'max:3600'],
                'client_width' => ['nullable', 'integer', 'min:0', 'max:20000'],
                'client_height' => ['nullable', 'integer', 'min:0', 'max:20000'],
            ], [
                'metrics.max' => 'Data analisis (metrics) terlalu besar. Maksimal 20000 karakter. Coba ulang tanpa mengirim visualisasi atau gunakan resolusi lebih kecil.',
            ]);

            $user = $request->user();
            $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
            if (! $isAdmin) {
                $ip = $request->ip();
                $sessionId = $request->session()->getId();
                $usageKey = 'form_analyzer:usage:'.$ip.':'.$sessionId;
                $usage = (int) Cache::get($usageKey, 0);
                if ($usage >= 2) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'Batas percobaan tercapai.',
                        'code' => 'limit_reached',
                        'message' => 'Kamu sudah mencoba Form Analyzer 2x di perangkat ini. Dukung pengembangan RuangLari untuk akses tanpa batas.',
                    ], 429);
                }
                Cache::put($usageKey, $usage + 1, now()->addDay());
            }

            $uuid = (string) Str::uuid();
            $uploadVideo = filter_var($data['upload_video'] ?? false, FILTER_VALIDATE_BOOL);
            $hasVideo = $request->hasFile('video');

            if ($uploadVideo && ! $hasVideo) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Video wajib diupload.',
                    'message' => 'Aktifkan upload video hanya jika Anda benar-benar ingin mengirim file ke server.',
                ], 422);
            }
            if (! $hasVideo && empty($data['metrics'])) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Tidak ada data analisis.',
                    'message' => 'Pilih video untuk dianalisis.',
                ], 422);
            }

            if ($hasVideo) {
                [$slot, $slotLock] = $this->acquireSlot();
                if (! $slotLock) {
                    return response()->json([
                        'ok' => false,
                        'queued' => true,
                        'retry_after' => 5,
                        'message' => 'Server sedang penuh. Anda masuk antrian, coba lagi beberapa detik.',
                        'max_concurrent' => self::MAX_CONCURRENT,
                    ], 429);
                }
                $dir = "tmp/form-analysis/{$uuid}";
            }

            $metrics = $this->parseMetrics($data['metrics'] ?? null);
            $biomech = $this->normalizeBiomechMetrics($metrics);
            $formReport = $this->buildFormReport($biomech, $metrics);

            $originalMeta = $this->buildMeta(null, $data, 0);
            $optimizedMeta = null;
            $compression = [
                'used' => false,
                'original_bytes' => null,
                'optimized_bytes' => null,
                'saved_bytes' => null,
                'saved_percent' => null,
            ];
            $compressionWarnings = [];

            if ($hasVideo) {
                $file = $request->file('video');
                $ext = strtolower($file->getClientOriginalExtension() ?: 'mp4');
                $originalPath = $file->storeAs($dir, "original.{$ext}");

                $originalAbs = storage_path('app/'.$originalPath);
                $originalSize = @filesize($originalAbs) ?: 0;

                $probeOriginal = $this->probeVideo($originalAbs);
                $originalMeta = $this->buildMeta($probeOriginal, $data, $originalSize);

                if ($originalMeta['duration_seconds'] && ($originalMeta['duration_seconds'] < 2 || $originalMeta['duration_seconds'] > 60)) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'Durasi video tidak sesuai.',
                        'message' => 'Gunakan video 2–60 detik. Rekomendasi terbaik: 5–10 detik.',
                    ], 422);
                }
                if ($originalMeta['width'] && $originalMeta['height'] && min($originalMeta['width'], $originalMeta['height']) < 240) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'Resolusi video terlalu rendah.',
                        'message' => 'Minimal 240p. Rekomendasi terbaik: 720p agar lutut & ankle terbaca jelas.',
                    ], 422);
                }

                if ($uploadVideo) {
                    $compressionResult = $this->compressVideoIfPossible($originalAbs, $dir, $probeOriginal);
                    $compressedAbs = $compressionResult['compressed_abs'] ?? null;
                    $compressedSize = $compressionResult['compressed_size'] ?? null;
                    $compressedUsed = $compressionResult['used'] ?? false;
                    $compressionWarnings = $compressionResult['warnings'] ?? [];

                    if ($compressedUsed && $compressedAbs && $compressedSize !== null) {
                        $probeOptimized = $this->probeVideo($compressedAbs) ?? null;
                        $optimizedMeta = $this->buildMeta($probeOptimized, $data, (int) $compressedSize);
                        $compression = [
                            'used' => (bool) $optimizedMeta,
                            'original_bytes' => $originalSize,
                            'optimized_bytes' => $optimizedMeta ? ($optimizedMeta['size_bytes'] ?? null) : null,
                            'saved_bytes' => ($optimizedMeta && $originalSize > 0) ? max(0, $originalSize - ($optimizedMeta['size_bytes'] ?? 0)) : null,
                            'saved_percent' => ($optimizedMeta && $originalSize > 0) ? round((($originalSize - ($optimizedMeta['size_bytes'] ?? 0)) / $originalSize) * 100, 1) : null,
                        ];
                    } else {
                        $compression = [
                            'used' => false,
                            'original_bytes' => $originalSize,
                            'optimized_bytes' => null,
                            'saved_bytes' => null,
                            'saved_percent' => null,
                        ];
                    }
                } else {
                    $compressionWarnings[] = [
                        'code' => 'no_server_upload',
                        'title' => 'Mode hemat aktif',
                        'message' => 'Video tidak dikirim untuk optimasi. Analisis form dilakukan di perangkat Anda.',
                        'severity' => 'info',
                    ];
                }
            }

            $meta = [
                'original' => $originalMeta,
                'optimized' => $optimizedMeta,
                'display' => $optimizedMeta ?: $originalMeta,
                'compression' => $compression,
            ];

            [$score, $issues, $suggestions, $coachMessage, $positives, $formIssues, $strengthPlan, $recoveryPlan, $videoScore, $formScore] =
                $this->makeFeedback($originalMeta, $meta['compression'], $compressionWarnings, $biomech);

            return response()->json([
                'ok' => true,
                'score' => $score,
                'video_score' => $videoScore,
                'form_score' => $formScore,
                'meta' => $meta,
                'issues' => $issues,
                'suggestions' => $suggestions,
                'positives' => $positives,
                'form_issues' => $formIssues,
                'form_report' => $formReport,
                'strength_plan' => $strengthPlan,
                'recovery_plan' => $recoveryPlan,
                'coach_message' => $coachMessage,
                'slot' => $slot,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Video gagal diproses.',
                'message' => $e->getMessage(),
            ], 422);
        } finally {
            if ($dir) {
                Storage::deleteDirectory($dir);
            }
            try {
                if ($slotLock) {
                    $slotLock->release();
                }
            } catch (\Throwable $e) {
            }
        }
    }

    public function report(Request $request)
    {
        $data = $request->validate([
            'report' => ['required', 'array'],
        ]);

        $report = $data['report'];
        $score = $report['score'] ?? null;
        $videoScore = $report['video_score'] ?? null;
        $meta = $report['meta'] ?? [];
        $display = $meta['display'] ?? [];
        $compression = $meta['compression'] ?? [];

        $positives = $report['positives'] ?? [];
        $issues = $report['issues'] ?? [];
        $suggestions = $report['suggestions'] ?? [];
        $formIssues = $report['form_issues'] ?? [];
        $formReport = $report['form_report'] ?? [];
        $strengthPlan = $report['strength_plan'] ?? [];
        $recoveryPlan = $report['recovery_plan'] ?? [];
        $coachMessage = $report['coach_message'] ?? null;

        $html = view('tools.form-analyzer-report', [
            'score' => $score,
            'videoScore' => $videoScore,
            'meta' => $meta,
            'display' => $display,
            'compression' => $compression,
            'positives' => $positives,
            'issues' => $issues,
            'suggestions' => $suggestions,
            'formIssues' => $formIssues,
            'formReport' => $formReport,
            'strengthPlan' => $strengthPlan,
            'recoveryPlan' => $recoveryPlan,
            'coachMessage' => $coachMessage,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="form-analyzer-report.pdf"',
        ]);
    }

    public function support(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:190'],
            'email' => ['nullable', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $ip = $request->ip();
        $sessionId = $request->session()->getId();
        $usageKey = 'form_analyzer:usage:'.$ip.':'.$sessionId;
        Cache::forget($usageKey);

        Log::info('form_analyzer_support', [
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'message' => $data['message'] ?? null,
            'ip' => $ip,
            'user_id' => $user ? $user->id : null,
            'user_agent' => $request->userAgent(),
            'at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'ok' => true,
        ]);
    }


    private function acquireSlot(): array
    {
        for ($i = 1; $i <= self::MAX_CONCURRENT; $i++) {
            $lock = Cache::lock("form_analyzer:slot:{$i}", 120);
            if ($lock->get()) {
                return [$i, $lock];
            }
        }

        return [null, null];
    }

    private function parseMetrics(?string $metrics): ?array
    {
        if (! is_string($metrics) || trim($metrics) === '') {
            return null;
        }
        $decoded = json_decode($metrics, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function normalizeBiomechMetrics(?array $metrics): ?array
    {
        if (! is_array($metrics)) {
            return null;
        }

        $num = function ($key) use ($metrics) {
            $v = $metrics[$key] ?? null;
            if ($v === null || $v === '') {
                return null;
            }
            if (is_numeric($v)) {
                return (float) $v;
            }

            return null;
        };

        $int = function ($key) use ($metrics) {
            $v = $metrics[$key] ?? null;
            if ($v === null || $v === '') {
                return null;
            }
            if (is_numeric($v)) {
                return (int) $v;
            }

            return null;
        };

        return [
            'confidence' => $num('confidence'),
            'samples' => $int('samples'),
            'heel_strike_pct' => $num('heel_strike_pct'),
            'overstride_pct' => $num('overstride_pct'),
            'shin_angle_deg' => $num('shin_angle_deg'),
            'knee_flex_deg' => $num('knee_flex_deg'),
            'trunk_lean_deg' => $num('trunk_lean_deg'),
            'arm_cross_pct' => $num('arm_cross_pct'),
            'cadence_spm' => $num('cadence_spm'),
            'vertical_oscillation' => $num('vertical_oscillation'),
            'asymmetry' => $num('asymmetry'),
        ];
    }

    private function buildMeta(?array $probe, array $clientData, int $sizeBytes): array
    {
        $duration = $probe['duration'] ?? null;
        $width = $probe['width'] ?? null;
        $height = $probe['height'] ?? null;
        $fps = $probe['fps'] ?? null;
        $bitrate = $probe['bitrate'] ?? null;

        if (! $duration && ! empty($clientData['client_duration'])) {
            $duration = (float) $clientData['client_duration'];
        }
        if (! $width && ! empty($clientData['client_width'])) {
            $width = (int) $clientData['client_width'];
        }
        if (! $height && ! empty($clientData['client_height'])) {
            $height = (int) $clientData['client_height'];
        }

        $resolution = ($width && $height) ? ($width.'x'.$height) : null;
        $isPortrait = ($width && $height) ? ($height > $width) : null;

        return [
            'duration_seconds' => $duration,
            'duration_human' => $duration ? $this->formatDuration($duration) : '--',
            'width' => $width,
            'height' => $height,
            'resolution' => $resolution ?: '--',
            'is_portrait' => $isPortrait,
            'fps' => $fps,
            'fps_human' => $fps ? (round($fps, 2).' fps') : '--',
            'bitrate_bps' => $bitrate,
            'size_bytes' => $sizeBytes,
            'size_human' => $this->formatBytes($sizeBytes),
        ];
    }

    private function probeVideo(string $absPath): ?array
    {
        if (! $this->canRunBinary('ffprobe')) {
            return null;
        }

        $process = new Process([
            'ffprobe',
            '-v', 'error',
            '-show_entries', 'format=duration,bit_rate',
            '-show_entries', 'stream=width,height,avg_frame_rate,r_frame_rate',
            '-of', 'json',
            $absPath,
        ]);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $json = json_decode($process->getOutput(), true);
        if (! is_array($json)) {
            return null;
        }

        $duration = null;
        $bitrate = null;
        if (isset($json['format']['duration'])) {
            $duration = (float) $json['format']['duration'];
        }
        if (isset($json['format']['bit_rate'])) {
            $bitrate = (int) $json['format']['bit_rate'];
        }

        $width = null;
        $height = null;
        $fps = null;

        $streams = $json['streams'] ?? [];
        if (is_array($streams)) {
            foreach ($streams as $s) {
                if (isset($s['width']) && isset($s['height'])) {
                    $width = (int) $s['width'];
                    $height = (int) $s['height'];
                }
                $rate = $s['avg_frame_rate'] ?? ($s['r_frame_rate'] ?? null);
                if (is_string($rate) && str_contains($rate, '/')) {
                    [$n, $d] = array_pad(explode('/', $rate, 2), 2, null);
                    $n = (float) $n;
                    $d = (float) $d;
                    if ($d > 0) {
                        $fps = $n / $d;
                    }
                } elseif (is_numeric($rate)) {
                    $fps = (float) $rate;
                }
            }
        }

        return [
            'duration' => $duration,
            'width' => $width,
            'height' => $height,
            'fps' => $fps,
            'bitrate' => $bitrate,
        ];
    }

    private function compressVideoIfPossible(string $originalAbs, string $dir, ?array $probe): array
    {
        $warnings = [];
        if (! $this->canRunBinary('ffmpeg')) {
            $warnings[] = [
                'code' => 'ffmpeg_missing',
                'title' => 'Optimasi ukuran file tidak aktif',
                'message' => 'Server belum menyediakan ffmpeg. Video tetap dianalisis dari metadata yang ada.',
                'severity' => 'info',
            ];

            return ['used' => false, 'warnings' => $warnings];
        }

        $width = $probe['width'] ?? null;
        $height = $probe['height'] ?? null;
        $fps = $probe['fps'] ?? null;

        $targetWidth = ($width && $width > 720) ? 720 : null;
        $vf = $targetWidth ? "scale='min({$targetWidth},iw)':-2" : 'scale=iw:ih';

        $outRel = $dir.'/optimized.mp4';
        $outAbs = storage_path('app/'.$outRel);

        $args = [
            'ffmpeg',
            '-y',
            '-i', $originalAbs,
            '-vf', $vf,
            '-pix_fmt', 'yuv420p',
            '-c:v', 'libx264',
            '-preset', 'veryfast',
            '-crf', '26',
            '-movflags', '+faststart',
            '-an',
        ];
        if ($fps && $fps > 0 && $fps < 60) {
            $args[] = '-r';
            $args[] = (string) min(30, (int) round($fps));
        }
        $args[] = $outAbs;

        $process = new Process($args);
        $process->setTimeout(120);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $warnings[] = [
                'code' => 'compress_failed',
                'title' => 'Kompresi gagal',
                'message' => 'Video tetap diproses tanpa optimasi. Pastikan ffmpeg mendukung H.264 (libx264) di server.',
                'severity' => 'info',
            ];

            return ['used' => false, 'warnings' => $warnings];
        }

        $compressedSize = @filesize($outAbs);
        if (! is_int($compressedSize) || $compressedSize <= 0) {
            $warnings[] = [
                'code' => 'compress_empty',
                'title' => 'Hasil kompresi tidak valid',
                'message' => 'Video tetap diproses tanpa optimasi.',
                'severity' => 'info',
            ];

            return ['used' => false, 'warnings' => $warnings];
        }

        return [
            'used' => true,
            'warnings' => $warnings,
            'compressed_abs' => $outAbs,
            'compressed_rel' => $outRel,
            'compressed_size' => $compressedSize,
        ];
    }

    private function makeFeedback(array $meta, array $compression, array $compressionWarnings, ?array $biomech): array
    {
        $issues = [];
        $suggestions = [];
        $positives = [];
        $formIssues = [];
        $strengthPlan = [];
        $recoveryPlan = [];

        $duration = $meta['duration_seconds'] ?? null;
        $width = $meta['width'] ?? null;
        $height = $meta['height'] ?? null;
        $fps = $meta['fps'] ?? null;
        $size = $meta['size_bytes'] ?? null;
        $isPortrait = $meta['is_portrait'] ?? null;

        $videoScore = 100;

        if ($duration) {
            if ($duration < 4) {
                $videoScore -= 25;
                $issues[] = [
                    'code' => 'duration_too_short',
                    'title' => 'Durasi terlalu pendek',
                    'message' => 'Usahakan 5–10 detik lari stabil agar ada cukup langkah untuk dianalisis.',
                    'severity' => 'high',
                ];
                $suggestions[] = [
                    'code' => 'duration_fix',
                    'title' => 'Rekam ulang 5–10 detik',
                    'message' => 'Mulai rekam 1–2 detik sebelum lari stabil, lalu ambil 8–10 detik.',
                    'severity' => 'info',
                ];
            } elseif ($duration > 20) {
                $videoScore -= 15;
                $issues[] = [
                    'code' => 'duration_too_long',
                    'title' => 'Durasi terlalu panjang',
                    'message' => 'Video panjang membuat upload lambat dan tidak menambah akurasi. Idealnya 5–10 detik.',
                    'severity' => 'medium',
                ];
                $suggestions[] = [
                    'code' => 'duration_trim',
                    'title' => 'Trim bagian paling stabil',
                    'message' => 'Ambil segmen 6–12 detik saat pace sudah stabil dan tubuh full terlihat.',
                    'severity' => 'info',
                ];
            } elseif ($duration >= 5 && $duration <= 12) {
                $positives[] = [
                    'code' => 'duration_good',
                    'title' => 'Durasi ideal',
                    'message' => 'Rentang 5–12 detik biasanya cukup untuk menangkap beberapa langkah stabil.',
                    'severity' => 'good',
                ];
            } else {
                $videoScore -= 5;
            }
        } else {
            $videoScore -= 10;
            $issues[] = [
                'code' => 'duration_unknown',
                'title' => 'Durasi tidak terbaca',
                'message' => 'Metadata durasi tidak terbaca dari video. Analisis tetap jalan tapi feedback kualitas jadi terbatas.',
                'severity' => 'medium',
            ];
        }

        if ($width && $height) {
            $minSide = min($width, $height);
            if ($minSide < 480) {
                $videoScore -= 25;
                $issues[] = [
                    'code' => 'resolution_low',
                    'title' => 'Resolusi terlalu rendah',
                    'message' => 'Detail lutut & ankle berisiko tidak terbaca jelas.',
                    'severity' => 'high',
                ];
                $suggestions[] = [
                    'code' => 'resolution_fix',
                    'title' => 'Naikkan resolusi',
                    'message' => 'Gunakan minimal 720p dan hindari zoom digital.',
                    'severity' => 'info',
                ];
            } elseif ($minSide < 720) {
                $videoScore -= 10;
                $issues[] = [
                    'code' => 'resolution_mid',
                    'title' => 'Resolusi cukup, tapi bisa lebih tajam',
                    'message' => 'Untuk hasil paling rapi, usahakan 720p atau lebih.',
                    'severity' => 'medium',
                ];
            } else {
                $positives[] = [
                    'code' => 'resolution_good',
                    'title' => 'Resolusi bagus',
                    'message' => 'Detail lutut dan ankle lebih mudah terbaca di resolusi ini.',
                    'severity' => 'good',
                ];
            }

            if ($isPortrait === true) {
                $videoScore -= 10;
                $issues[] = [
                    'code' => 'portrait',
                    'title' => 'Orientasi portrait',
                    'message' => 'Landscape biasanya lebih stabil untuk menangkap seluruh tubuh dengan ruang langkah yang cukup.',
                    'severity' => 'medium',
                ];
                $suggestions[] = [
                    'code' => 'landscape',
                    'title' => 'Gunakan landscape',
                    'message' => 'Putar HP horizontal dan posisikan pelari full-body di frame.',
                    'severity' => 'info',
                ];
            } else {
                $positives[] = [
                    'code' => 'orientation_ok',
                    'title' => 'Orientasi mendukung analisis',
                    'message' => 'Frame memberi ruang langkah dan tubuh full lebih mudah masuk.',
                    'severity' => 'good',
                ];
            }
        } else {
            $videoScore -= 10;
            $issues[] = [
                'code' => 'resolution_unknown',
                'title' => 'Resolusi tidak terbaca',
                'message' => 'Metadata resolusi tidak terbaca. Pastikan video jelas dan tubuh full terlihat.',
                'severity' => 'medium',
            ];
        }

        if ($fps) {
            if ($fps < 24) {
                $videoScore -= 20;
                $issues[] = [
                    'code' => 'fps_low',
                    'title' => 'FPS rendah',
                    'message' => 'Gerakan cepat (kaki/ankle) rentan blur dan sulit dianalisis.',
                    'severity' => 'high',
                ];
                $suggestions[] = [
                    'code' => 'fps_fix',
                    'title' => 'Rekam 30fps atau 60fps',
                    'message' => 'Aktifkan mode 30fps/60fps, pencahayaan cukup, dan hindari panning kamera.',
                    'severity' => 'info',
                ];
            } elseif ($fps < 30) {
                $videoScore -= 8;
                $issues[] = [
                    'code' => 'fps_mid',
                    'title' => 'FPS cukup, tapi ideal 30fps',
                    'message' => 'Kalau memungkinkan, gunakan 30fps untuk detail langkah yang lebih jelas.',
                    'severity' => 'medium',
                ];
            } else {
                $positives[] = [
                    'code' => 'fps_good',
                    'title' => 'FPS ideal',
                    'message' => 'Gerakan kaki/ankle lebih tajam dan minim blur.',
                    'severity' => 'good',
                ];
            }
        } else {
            $videoScore -= 6;
            $issues[] = [
                'code' => 'fps_unknown',
                'title' => 'FPS tidak terbaca',
                'message' => 'Metadata fps tidak terbaca. Pastikan video tidak patah-patah.',
                'severity' => 'low',
            ];
        }

        if ($size && $size > 70 * 1024 * 1024) {
            $videoScore -= 8;
            $issues[] = [
                'code' => 'size_large',
                'title' => 'Ukuran file besar',
                'message' => 'Upload akan lebih lama dan rawan gagal jika koneksi tidak stabil.',
                'severity' => 'medium',
            ];
            $suggestions[] = [
                'code' => 'size_fix',
                'title' => 'Gunakan durasi pendek & 720p',
                'message' => 'Video 5–10 detik 720p biasanya cukup dan jauh lebih ringan.',
                'severity' => 'info',
            ];
        }

        foreach ($compressionWarnings as $w) {
            $issues[] = $w;
        }
        if (! empty($compression['used'])) {
            $saved = $compression['saved_percent'] ?? null;
            $positives[] = [
                'code' => 'optimized_upload',
                'title' => 'Upload sudah dioptimalkan',
                'message' => is_numeric($saved) ? "Ukuran file berhasil diperkecil (hemat {$saved}%)." : 'Ukuran file berhasil dioptimalkan.',
                'severity' => 'good',
            ];
        }

        $videoScore = (int) max(0, min(100, round($videoScore)));

        $formScore = null;

        [$formIssues, $strengthPlan, $recoveryPlan, $formCoachLines, $formPositives] = $this->buildBiomechFeedback($biomech);
        foreach ($formPositives as $p) {
            $positives[] = $p;
        }

        if (is_array($biomech) && (($biomech['samples'] ?? null) || ($biomech['source'] ?? null))) {
            $formScoreVal = 100.0;

            $over = $biomech['overstride_pct'] ?? null;
            if (is_numeric($over)) {
                if ($over >= 60) {
                    $formScoreVal -= 35;
                } elseif ($over >= 35) {
                    $formScoreVal -= 20;
                } elseif ($over >= 20) {
                    $formScoreVal -= 10;
                }
            }

            $arm = $biomech['arm_cross_pct'] ?? null;
            if (is_numeric($arm)) {
                if ($arm >= 60) {
                    $formScoreVal -= 25;
                } elseif ($arm >= 40) {
                    $formScoreVal -= 15;
                } elseif ($arm >= 25) {
                    $formScoreVal -= 8;
                }
            }

            $shin = $biomech['shin_angle_deg'] ?? null;
            if (is_numeric($shin) && $shin >= 18) {
                $formScoreVal -= 8;
            }

            $knee = $biomech['knee_flex_deg'] ?? null;
            if (is_numeric($knee) && $knee < 20) {
                $formScoreVal -= 10;
            }

            $trunk = $biomech['trunk_lean_deg'] ?? null;
            if (is_numeric($trunk) && ($trunk > 18 || $trunk < -5)) {
                $formScoreVal -= 6;
            }

            $vo = $biomech['vertical_oscillation'] ?? null;
            if (is_numeric($vo) && $vo >= 0.012) {
                $formScoreVal -= 6;
            }

            $conf = $biomech['confidence'] ?? null;
            if (is_numeric($conf) && $conf < 0.5) {
                $formScoreVal -= 8;
            }

            $formScoreVal = max(0, min(100, $formScoreVal));
            $formScore = (int) round($formScoreVal);
        }

        $score = $formScore ?? $videoScore;

        $coachLines = [];
        $coachLines[] = 'Saya sudah analisis form lari kamu (landing, lever, push, pull, ayunan tangan, postur). Ini ringkasannya:';
        $coachLines[] = '';
        $coachLines[] = '1) Fokus perbaiki 1–2 poin terbesar dulu (lihat “Laporan Form”).';
        $coachLines[] = '2) Latih 2–3x/minggu sesuai rencana penguatan, lalu rekam ulang untuk cek progres.';
        $coachLines[] = '3) Kalau banyak status “missing”, rekam tampak samping, tubuh full terlihat, pencahayaan cukup (5–10 detik).';
        $coachLines[] = '';
        if ($videoScore >= 85) {
            $coachLines[] = '';
            $coachLines[] = 'Kualitas data sudah sangat layak. Hasil feedback akan lebih konsisten.';
        } elseif ($videoScore >= 70) {
            $coachLines[] = '';
            $coachLines[] = 'Kualitas data cukup oke. Kalau mau hasil lebih presisi, perbaiki pencahayaan dan pastikan tubuh full terlihat.';
        } elseif ($videoScore >= 55) {
            $coachLines[] = '';
            $coachLines[] = 'Ada beberapa hal yang perlu dibenahi supaya analisis tidak “melenceng”. Fokus perbaiki yang statusnya paling berat dulu.';
        } else {
            $coachLines[] = '';
            $coachLines[] = 'Data pose berisiko menghasilkan feedback kurang akurat. Rekam ulang dengan tampak samping, tubuh full, dan cahaya cukup.';
        }

        if (! empty($formCoachLines)) {
            $coachLines[] = '';
            foreach ($formCoachLines as $l) {
                $coachLines[] = $l;
            }
        }

        $suggestions = array_values($suggestions);
        $issues = array_values($issues);
        $positives = array_values($positives);

        return [$score, $issues, $suggestions, implode("\n", $coachLines), $positives, $formIssues, $strengthPlan, $recoveryPlan, $videoScore, $formScore];
    }

    private function buildBiomechFeedback(?array $biomech): array
    {
        $formIssues = [];
        $strengthPlan = [];
        $recoveryPlan = [];
        $coachLines = [];
        $positives = [];

        if (! $biomech) {
            $formIssues[] = [
                'code' => 'biomech_missing',
                'title' => 'Analisis form belum tersedia',
                'message' => 'Tidak ada data pose yang terbaca dari video. Pastikan tubuh full terlihat, terang, dan tampak samping.',
                'severity' => 'medium',
            ];
            $coachLines[] = 'Analisis form (heel strike, overstride, dll) butuh deteksi pose. Rekam lebih terang dan pastikan tubuh full di frame.';

            return [$formIssues, $strengthPlan, $recoveryPlan, $coachLines, $positives];
        }

        $confidence = $biomech['confidence'] ?? null;
        $samples = $biomech['samples'] ?? null;
        if (is_numeric($confidence) && $confidence < 0.45) {
            $formIssues[] = [
                'code' => 'biomech_low_conf',
                'title' => 'Kepercayaan analisis rendah',
                'message' => 'Sendi tidak terbaca stabil (pencahayaan, blur, atau tubuh tidak full). Anggap hasil ini sebagai indikasi, bukan kepastian.',
                'severity' => 'high',
            ];
        } elseif (is_numeric($confidence) && $confidence >= 0.7) {
            $positives[] = [
                'code' => 'biomech_conf_good',
                'title' => 'Kualitas deteksi pose bagus',
                'message' => 'Sendi terbaca cukup stabil sehingga indikasi form lebih bisa dipercaya.',
                'severity' => 'good',
            ];
        }

        if (is_numeric($samples) && $samples > 0) {
            $positives[] = [
                'code' => 'biomech_samples',
                'title' => 'Sampel cukup',
                'message' => "Dianalisis dari {$samples} potongan frame untuk melihat pola, bukan 1 frame saja.",
                'severity' => 'good',
            ];
        }

        $heel = $biomech['heel_strike_pct'] ?? null;
        if (is_numeric($heel)) {
            if ($heel >= 70) {
                $formIssues[] = [
                    'code' => 'heel_strike_high',
                    'title' => 'Heel strike dominan',
                    'message' => 'Indikasi tumit mendarat lebih dulu di mayoritas langkah. Ini sering berpasangan dengan overstriding dan beban ke lutut/tibia.',
                    'severity' => 'high',
                ];
                $strengthPlan[] = [
                    'code' => 'hs_strength',
                    'title' => 'Penguatan (2–3x/minggu)',
                    'message' => 'Calf raise eksentrik 3x12, tibialis raise 3x15, glute bridge 3x12, dead bug 3x10/side.',
                    'severity' => 'info',
                ];
                $strengthPlan[] = [
                    'code' => 'hs_drill',
                    'title' => 'Drill teknik (2–3 set)',
                    'message' => 'Wall lean drill 3x20 detik, “quick feet” 3x20 langkah, stride pendek fokus mendarat di bawah pinggul.',
                    'severity' => 'info',
                ];
                $recoveryPlan[] = [
                    'code' => 'hs_recovery',
                    'title' => 'Pemulihan & penanganan awal',
                    'message' => 'Kurangi intensitas 7–14 hari jika muncul nyeri tulang kering/lutut. Prioritaskan easy run, tidur cukup, dan evaluasi sepatu/permukaan.',
                    'severity' => 'info',
                ];
            } elseif ($heel >= 40) {
                $formIssues[] = [
                    'code' => 'heel_strike_mid',
                    'title' => 'Heel strike cukup sering',
                    'message' => 'Masih muncul cukup sering. Fokus ke langkah lebih pendek dan mendarat lebih dekat ke pinggul.',
                    'severity' => 'medium',
                ];
                $strengthPlan[] = [
                    'code' => 'hs_mid_strength',
                    'title' => 'Penguatan',
                    'message' => 'Calf raise 3x12 + glute med (side plank clamshell) 3x10/side.',
                    'severity' => 'info',
                ];
            } else {
                $positives[] = [
                    'code' => 'heel_strike_low',
                    'title' => 'Footstrike relatif aman',
                    'message' => 'Indikasi heel strike tidak dominan. Pertahankan dan fokus ke konsistensi.',
                    'severity' => 'good',
                ];
            }
        }

        $over = $biomech['overstride_pct'] ?? null;
        if (is_numeric($over)) {
            if ($over >= 60) {
                $formIssues[] = [
                    'code' => 'overstride_high',
                    'title' => 'Overstriding',
                    'message' => 'Kaki sering mendarat terlalu jauh di depan pinggul. Ini meningkatkan braking force dan risiko shin splints / knee pain.',
                    'severity' => 'high',
                ];
                $strengthPlan[] = [
                    'code' => 'over_strength',
                    'title' => 'Penguatan posterior chain',
                    'message' => 'Single-leg RDL 3x8/side, split squat 3x8/side, hamstring bridge 3x10.',
                    'severity' => 'info',
                ];
                $strengthPlan[] = [
                    'code' => 'over_drill',
                    'title' => 'Drill cadence',
                    'message' => 'Naikkan cadence +3–7% pada easy run (tanpa menambah kecepatan), fokus kaki “cepat” dan langkah pendek.',
                    'severity' => 'info',
                ];
                $recoveryPlan[] = [
                    'code' => 'over_recovery',
                    'title' => 'Jika ada shin splints / nyeri tulang kering',
                    'message' => 'Kurangi volume sementara, hindari speedwork, lakukan calf stretch ringan setelah latihan, dan progreskan beban bertahap. Jika nyeri terlokalisasi tajam atau semakin sakit saat ditekan, pertimbangkan evaluasi profesional.',
                    'severity' => 'info',
                ];
            } elseif ($over >= 35) {
                $formIssues[] = [
                    'code' => 'overstride_mid',
                    'title' => 'Overstriding ringan',
                    'message' => 'Sesekali mendarat terlalu jauh. Biasanya membaik dengan cadence naik sedikit dan kontrol trunk.',
                    'severity' => 'medium',
                ];
            } else {
                $positives[] = [
                    'code' => 'overstride_low',
                    'title' => 'Step placement cukup rapi',
                    'message' => 'Indikasi mendarat dekat pinggul cukup sering. Ini bagus untuk efisiensi.',
                    'severity' => 'good',
                ];
            }
        }

        $shin = $biomech['shin_angle_deg'] ?? null;
        if (is_numeric($shin)) {
            if ($shin >= 18) {
                $formIssues[] = [
                    'code' => 'shin_angle_high',
                    'title' => 'Shin angle cenderung “mengerem”',
                    'message' => 'Sudut betis cukup besar saat fase bawah, indikasi braking force lebih tinggi. Ini sering terkait overstriding dan heel strike.',
                    'severity' => 'medium',
                ];
                $strengthPlan[] = [
                    'code' => 'shin_drill',
                    'title' => 'Koreksi step placement',
                    'message' => 'Fokus mendarat “di bawah pinggul”, rasakan dorongan ke belakang (push) bukan mengerem di depan (brake).',
                    'severity' => 'info',
                ];
            } elseif ($shin <= 10) {
                $positives[] = [
                    'code' => 'shin_angle_ok',
                    'title' => 'Shin angle cukup efisien',
                    'message' => 'Indikasi braking force lebih rendah pada beberapa frame yang terbaca.',
                    'severity' => 'good',
                ];
            }
        }

        $knee = $biomech['knee_flex_deg'] ?? null;
        if (is_numeric($knee)) {
            if ($knee < 20) {
                $formIssues[] = [
                    'code' => 'knee_stiff',
                    'title' => 'Knee flexion rendah (landing kaku)',
                    'message' => 'Indikasi lutut terlalu “lurus” saat mendarat, shock absorption kurang, beban naik ke lutut/hip.',
                    'severity' => 'high',
                ];
                $strengthPlan[] = [
                    'code' => 'knee_strength',
                    'title' => 'Penguatan & kontrol',
                    'message' => 'Step-down 3x8/side, squat tempo 3x6, hip hinge (RDL) 3x8.',
                    'severity' => 'info',
                ];
                $recoveryPlan[] = [
                    'code' => 'knee_recovery',
                    'title' => 'Jika ada nyeri lutut',
                    'message' => 'Batasi downhill/interval sementara, perbanyak easy run. Jika nyeri tajam/berulang >2 minggu, pertimbangkan konsultasi fisioterapis.',
                    'severity' => 'info',
                ];
            } elseif ($knee >= 30 && $knee <= 55) {
                $positives[] = [
                    'code' => 'knee_ok',
                    'title' => 'Knee flexion cukup',
                    'message' => 'Landing cenderung lebih “empuk” dan menyerap beban lebih baik.',
                    'severity' => 'good',
                ];
            }
        }

        $trunk = $biomech['trunk_lean_deg'] ?? null;
        if (is_numeric($trunk)) {
            if ($trunk > 18) {
                $formIssues[] = [
                    'code' => 'trunk_lean_high',
                    'title' => 'Trunk lean berlebihan',
                    'message' => 'Cenderung membungkuk dari pinggang. Idealnya “lean dari pergelangan kaki” dengan core tetap stabil.',
                    'severity' => 'medium',
                ];
                $strengthPlan[] = [
                    'code' => 'trunk_core',
                    'title' => 'Core stability',
                    'message' => 'Plank 3x30–45s, side plank 3x20–30s/side, dead bug 3x10/side.',
                    'severity' => 'info',
                ];
            } elseif ($trunk < -5) {
                $formIssues[] = [
                    'code' => 'trunk_lean_back',
                    'title' => 'Terlalu tegak / condong ke belakang',
                    'message' => 'Ini bisa meningkatkan braking. Coba sedikit lean ke depan dari pergelangan kaki.',
                    'severity' => 'medium',
                ];
            } else {
                $positives[] = [
                    'code' => 'trunk_ok',
                    'title' => 'Postur tubuh atas relatif baik',
                    'message' => 'Lean terlihat wajar untuk lari stabil.',
                    'severity' => 'good',
                ];
            }
        }

        $vo = $biomech['vertical_oscillation'] ?? null;
        if (is_numeric($vo)) {
            if ($vo >= 0.012) {
                $formIssues[] = [
                    'code' => 'vertical_osc_high',
                    'title' => 'Vertical oscillation tinggi (bounce)',
                    'message' => 'Tubuh tampak “naik-turun” cukup besar. Ini bisa boros energi dan membebani betis/hamstring.',
                    'severity' => 'medium',
                ];
                $strengthPlan[] = [
                    'code' => 'vertical_osc_fix',
                    'title' => 'Efisiensi langkah',
                    'message' => 'Coba langkah sedikit lebih pendek + cadence naik tipis. Fokus “meluncur ke depan”, bukan memantul ke atas.',
                    'severity' => 'info',
                ];
            } else {
                $positives[] = [
                    'code' => 'vertical_osc_ok',
                    'title' => 'Bounce cukup terkendali',
                    'message' => 'Indikasi gerak naik-turun relatif kecil sehingga efisiensi biasanya lebih baik.',
                    'severity' => 'good',
                ];
            }
        }

        $arm = $biomech['arm_cross_pct'] ?? null;
        if (is_numeric($arm)) {
            if ($arm >= 45) {
                $formIssues[] = [
                    'code' => 'arm_cross',
                    'title' => 'Ayunan tangan menyilang',
                    'message' => 'Indikasi crossing midline cukup sering. Ini bisa memutar torso dan boros energi.',
                    'severity' => 'medium',
                ];
                $strengthPlan[] = [
                    'code' => 'arm_drill',
                    'title' => 'Drill ayunan tangan',
                    'message' => 'Bayangkan “siku ke belakang”, tangan tetap di “rel” sejajar arah lari, bahu rileks.',
                    'severity' => 'info',
                ];
            } else {
                $positives[] = [
                    'code' => 'arm_ok',
                    'title' => 'Ayunan tangan cukup rapi',
                    'message' => 'Tidak banyak crossing midline.',
                    'severity' => 'good',
                ];
            }
        }

        $cadence = $biomech['cadence_spm'] ?? null;
        if (is_numeric($cadence)) {
            if ($cadence < 155) {
                $formIssues[] = [
                    'code' => 'cadence_low',
                    'title' => 'Cadence rendah',
                    'message' => 'Cadence rendah sering terkait overstriding. Naikkan pelan-pelan, fokus langkah pendek.',
                    'severity' => 'medium',
                ];
            } elseif ($cadence >= 165 && $cadence <= 190) {
                $positives[] = [
                    'code' => 'cadence_ok',
                    'title' => 'Cadence sehat',
                    'message' => 'Rentang cadence ini sering lebih efisien untuk banyak pelari (tergantung tinggi & pace).',
                    'severity' => 'good',
                ];
            }
        }

        $asym = $biomech['asymmetry'] ?? null;
        if (is_numeric($asym) && $asym >= 0.25) {
            $formIssues[] = [
                'code' => 'asymmetry',
                'title' => 'Asimetri langkah terdeteksi',
                'message' => 'Indikasi kanan/kiri tidak simetris. Bisa dari kelemahan unilateral, riwayat cedera, atau kamera tidak stabil.',
                'severity' => 'medium',
            ];
            $strengthPlan[] = [
                'code' => 'asym_strength',
                'title' => 'Penguatan unilateral',
                'message' => 'Split squat 3x8/side, single-leg calf raise 3x10/side, single-leg RDL 3x8/side.',
                'severity' => 'info',
            ];
            $recoveryPlan[] = [
                'code' => 'asym_recovery',
                'title' => 'Jika asimetri disertai nyeri',
                'message' => 'Kurangi beban sementara dan prioritaskan pemulihan. Jika nyeri satu sisi menetap, pertimbangkan evaluasi profesional.',
                'severity' => 'info',
            ];
        }

        $formIssues[] = [
            'code' => 'limits',
            'title' => 'Batasan analisis',
            'message' => 'Dari video tampak samping, sistem sulit memastikan pronasi/valgus lutut (butuh tampak depan/belakang). Gunakan hasil ini sebagai indikasi awal.',
            'severity' => 'info',
        ];

        $coachLines[] = 'Analisis form (beta):';
        $coachLines[] = 'Jika ada nyeri, utamakan pemulihan dan perbaiki 1 perubahan kecil dulu (cadence/step length).';
        $coachLines[] = 'Catatan: ini bukan diagnosis medis. Jika nyeri tajam, bengkak, kebas, atau makin parah, pertimbangkan pemeriksaan profesional.';

        return [$formIssues, $strengthPlan, $recoveryPlan, $coachLines, $positives];
    }

    private function buildFormReport(?array $biomech, ?array $metrics): array
    {
        $coverage = (is_array($metrics) && isset($metrics['coverage']) && is_array($metrics['coverage'])) ? $metrics['coverage'] : null;
        $missing = [];
        if (is_array($metrics) && isset($metrics['coverage_missing']) && is_array($metrics['coverage_missing'])) {
            $missing = array_values(array_filter($metrics['coverage_missing'], fn ($x) => is_string($x) && $x !== ''));
        }

        $num = function ($key) use ($biomech) {
            if (! is_array($biomech)) {
                return null;
            }
            $v = $biomech[$key] ?? null;

            return is_numeric($v) ? (float) $v : null;
        };

        $section = function (string $code, string $title) {
            return [
                'code' => $code,
                'title' => $title,
                'status' => 'ok',
                'summary' => null,
                'findings' => [],
                'actions' => [],
                'strength' => [],
            ];
        };

        $setStatus = function (array &$s, string $status, ?string $summary = null) {
            $priority = ['missing' => 3, 'issue' => 2, 'warn' => 1, 'ok' => 0];
            $cur = $s['status'] ?? 'ok';
            if (($priority[$status] ?? 0) >= ($priority[$cur] ?? 0)) {
                $s['status'] = $status;
                if ($summary) {
                    $s['summary'] = $summary;
                }
            }
        };

        $landing = $section('landing', 'Landing');
        $lever = $section('lever', 'Lever (mid-stance)');
        $push = $section('push', 'Push (toe-off)');
        $pull = $section('pull', 'Pull (swing)');
        $arm = $section('arm_swing', 'Ayunan Tangan');
        $posture = $section('posture', 'Postur & Stabilitas');

        if (! is_array($biomech)) {
            foreach ([$landing, $lever, $push, $pull, $arm, $posture] as &$s) {
                $setStatus($s, 'missing', 'Data pose belum cukup untuk menilai form.');
                $s['actions'][] = 'Rekam tampak samping, tubuh full terlihat, cahaya cukup, dan pace stabil 5–10 detik.';
            }
            unset($s);

            return [$landing, $lever, $push, $pull, $arm, $posture];
        }

        if (! empty($missing)) {
            foreach ([$landing, $lever, $push, $pull, $arm, $posture] as &$s) {
                $s['findings'][] = 'Cakupan frame belum lengkap untuk semua kategori gerak.';
            }
            unset($s);
        }

        $heel = $num('heel_strike_pct');
        $over = $num('overstride_pct');
        $shin = $num('shin_angle_deg');
        $knee = $num('knee_flex_deg');
        $trunk = $num('trunk_lean_deg');
        $armCross = $num('arm_cross_pct');
        $vo = $num('vertical_oscillation');
        $conf = $num('confidence');

        if (is_numeric($heel)) {
            $landing['findings'][] = "Heel strike: {$heel}%";
            if ($heel >= 70) {
                $setStatus($landing, 'issue', 'Heel strike dominan');
                $landing['actions'][] = 'Kurangi langkah panjang; fokus mendarat lebih dekat ke pinggul.';
                $landing['actions'][] = 'Naikkan cadence +3–7% pada easy run tanpa menambah pace.';
                $landing['strength'][] = 'Calf raise eksentrik 3x12 + tibialis raise 3x15 (2–3x/minggu).';
            } elseif ($heel >= 40) {
                $setStatus($landing, 'warn', 'Heel strike cukup sering');
                $landing['actions'][] = 'Fokus langkah lebih pendek dan “quick feet”.';
                $landing['strength'][] = 'Calf raise 3x12 + glute bridge 3x12 (2–3x/minggu).';
            } else {
                $landing['actions'][] = 'Pertahankan footstrike yang sudah relatif aman.';
            }
        }

        if (is_numeric($over)) {
            $landing['findings'][] = "Overstride: {$over}%";
            if ($over >= 60) {
                $setStatus($landing, 'issue', 'Overstriding');
                $landing['actions'][] = 'Jaga kaki mendarat “di bawah pinggul” untuk mengurangi braking force.';
                $landing['strength'][] = 'Single-leg RDL 3x8/side + split squat 3x8/side (2x/minggu).';
            } elseif ($over >= 35) {
                $setStatus($landing, 'warn', 'Overstriding ringan');
                $landing['actions'][] = 'Naikkan cadence sedikit dan kontrol trunk.';
            }
        }

        if (is_numeric($shin)) {
            $lever['findings'][] = "Shin angle: {$shin}°";
            if ($shin >= 18) {
                $setStatus($lever, 'warn', 'Shin angle cenderung “mengerem”');
                $lever['actions'][] = 'Fokus “push ke belakang” (dorong) dibanding “brake di depan”.';
                $lever['strength'][] = 'Hamstring bridge 3x10 + hip hinge (RDL) 3x8 (2x/minggu).';
            } else {
                $lever['actions'][] = 'Pertahankan shin angle agar braking force tetap rendah.';
            }
        }

        if (is_numeric($knee)) {
            $lever['findings'][] = "Knee flex: {$knee}°";
            if ($knee < 20) {
                $setStatus($lever, 'issue', 'Landing cenderung kaku (shock absorption rendah)');
                $lever['actions'][] = 'Latih landing lebih “empuk” dengan kontrol lutut dan pinggul.';
                $lever['strength'][] = 'Step-down 3x8/side + squat tempo 3x6 (2x/minggu).';
            } elseif ($knee >= 30 && $knee <= 55) {
                $lever['actions'][] = 'Knee flexion terlihat cukup untuk menyerap beban.';
            }
        }

        if (is_numeric($vo)) {
            $push['findings'][] = "Vertical oscillation: {$vo}";
            if ($vo >= 0.012) {
                $setStatus($push, 'warn', 'Bounce cukup tinggi');
                $push['actions'][] = 'Coba langkah lebih pendek + cadence naik tipis; fokus “meluncur ke depan”.';
                $push['strength'][] = 'Calf/ankle stiffness: pogo hops ringan 3x20 (2x/minggu) jika tanpa nyeri.';
            } else {
                $push['actions'][] = 'Gerak vertikal relatif efisien.';
            }
        }

        if (is_numeric($trunk)) {
            $posture['findings'][] = "Trunk lean: {$trunk}°";
            if ($trunk > 18) {
                $setStatus($posture, 'warn', 'Condong berlebihan dari pinggang');
                $posture['actions'][] = 'Coba lean dari pergelangan kaki, bukan membungkuk dari pinggang.';
                $posture['strength'][] = 'Plank 3x30–45s + dead bug 3x10/side (2–3x/minggu).';
            } else {
                $posture['actions'][] = 'Postur tubuh atas relatif stabil.';
            }
        }

        if (is_numeric($armCross)) {
            $arm['findings'][] = "Arm cross: {$armCross}%";
            if ($armCross >= 55) {
                $setStatus($arm, 'warn', 'Ayunan tangan cenderung menyilang');
                $arm['actions'][] = 'Ayun tangan maju-mundur sejajar arah lari; hindari menyilang garis tengah tubuh.';
                $arm['strength'][] = 'Scapular retraction row band 3x12 + wall slide 3x10 (2x/minggu).';
            } else {
                $arm['actions'][] = 'Ayunan tangan relatif rapi.';
            }
        }

        if (is_numeric($conf)) {
            if ($conf < 0.45) {
                foreach ([$landing, $lever, $push, $pull, $arm, $posture] as &$s) {
                    $s['findings'][] = 'Kepercayaan deteksi pose rendah; anggap hasil sebagai indikasi.';
                    $setStatus($s, 'warn');
                }
                unset($s);
            }
        }

        if ($coverage) {
            foreach ([$landing, $lever, $push, $pull, $arm, $posture] as &$s) {
                $code = $s['code'];
                if (isset($coverage[$code]['count'], $coverage[$code]['min'])) {
                    $c = (int) $coverage[$code]['count'];
                    $m = (int) $coverage[$code]['min'];
                    $s['findings'][] = "Cakupan frame: {$c}/{$m}";
                    if ($c < $m) {
                        $setStatus($s, 'missing', 'Frame belum cukup untuk kategori ini');
                    }
                }
            }
            unset($s);
        }

        return [$landing, $lever, $push, $pull, $arm, $posture];
    }

    private function canRunBinary(string $bin): bool
    {
        try {
            $p = new Process([$bin, '-version']);
            $p->setTimeout(5);
            $p->run();

            return $p->isSuccessful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '--';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = max(0, min($i, count($units) - 1));
        $val = $bytes / (1024 ** $i);
        $dec = $val >= 100 ? 0 : ($val >= 10 ? 1 : 2);

        return number_format($val, $dec).' '.$units[$i];
    }

    private function formatDuration(float $seconds): string
    {
        if ($seconds <= 0) {
            return '--';
        }
        $m = (int) floor($seconds / 60);
        $s = (int) round($seconds % 60);

        return ($m > 0 ? ($m.'m ') : '').$s.'s';
    }
}
