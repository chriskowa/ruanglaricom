<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\PaidFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\RunningAnalysis\BiomechanicsAnalysisService;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class FormAnalyzerController extends Controller
{
    private const MAX_CONCURRENT = 5;
    public const MAX_TRIES = 2;

    public function index()
    {
        $hasPaidFeature = true;
        return view('tools.form-analyzer', compact('hasPaidFeature'));
    }

    public function analyze(Request $request)
    {
        $dir = null;
        $originalPath = null;
        $slot = null;
        $slotLock = null;
        try {
            $data = $request->validate([
                'upload_video' => ['nullable'],
                'video' => ['nullable', 'file', 'max:204800'],
                'metrics' => ['nullable', 'string'],
                'client_duration' => ['nullable'],
                'client_width' => ['nullable'],
                'client_height' => ['nullable'],
            ]);

            $user = $request->user();
            $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
            
            $hasPaidFeature = false;
            if ($user) {
                $hasPaidFeature = PaidFeature::query()
                    ->where('user_id', $user->id)
                    ->where('feature_slug', 'motion-capture-expert')
                    ->where('status', 'paid')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->exists();
            }

            $uuid = (string) Str::uuid();
            $uploadVideo = filter_var($data['upload_video'] ?? false, FILTER_VALIDATE_BOOL);
            $hasVideo = $request->hasFile('video');
            $metrics = $this->parseMetrics($data['metrics'] ?? null);

            if (! $hasVideo && empty($metrics)) {
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
            $analysisService = app(BiomechanicsAnalysisService::class);
            $biomech = $analysisService->normalizeBiomechMetrics($metrics);
            
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

                if ($originalMeta['duration_seconds'] && $originalMeta['duration_seconds'] > 60) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'Durasi video terlalu panjang.',
                        'message' => 'Gunakan video maksimal 60 detik.',
                    ], 422);
                }
                if ($originalMeta['width'] && $originalMeta['height'] && min($originalMeta['width'], $originalMeta['height']) < 120) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'Resolusi video terlalu rendah.',
                        'message' => 'Video terlalu kecil untuk diproses.',
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
                'runner_name' => auth()->user()?->name ?? 'Pelari',
            ];

            $analysisResult = $analysisService->analyze($metrics, $meta, $compressionWarnings);

            $score = $analysisResult['score'];
            $videoScore = $analysisResult['video_score'];
            $formScore = $analysisResult['form_score'];
            $positives = $analysisResult['positives'];
            $issues = $analysisResult['issues'];
            $suggestions = $analysisResult['suggestions'];
            $formIssues = $analysisResult['form_issues'];
            $formReport = $analysisResult['form_report'];
            $strengthPlan = $analysisResult['strength_plan'];
            $recoveryPlan = $analysisResult['recovery_plan'];
            $coachMessage = $analysisResult['coach_message'];


            // Save result to Cache for PDF generation
            $analysisId = (string) Str::uuid();
            $reportData = [
                'score' => $score,
                'video_score' => $videoScore,
                'meta' => $meta,
                'positives' => $positives,
                'issues' => $issues,
                'suggestions' => $suggestions,
                'form_issues' => $formIssues,
                'form_report' => $formReport,
                'strength_plan' => $strengthPlan,
                'recovery_plan' => $recoveryPlan,
                'coach_message' => $coachMessage,
            ];
            Cache::put('form_analyzer_result:'.$analysisId, $reportData, now()->addHours(24));

            return response()->json([
                'ok' => true,
                'analysis_id' => $analysisId,
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
            'analysis_id' => ['required', 'string'],
        ]);

        $report = Cache::get('form_analyzer_result:'.$data['analysis_id']);
        if (!$report) {
            return response()->json(['error' => 'Laporan tidak ditemukan atau sudah kedaluwarsa.'], 404);
        }

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
        
        // Remove automatic trial reset
        // $usageKey = 'form_analyzer:usage:'.$ip.':'.$sessionId;
        // Cache::forget($usageKey);

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


    private function buildMeta(?array $probe, array $data, int $sizeBytes): array
    {
        $duration = (float) ($probe['duration'] ?? $data['client_duration'] ?? 0);
        $width = (int) ($probe['width'] ?? $data['client_width'] ?? 0);
        $height = (int) ($probe['height'] ?? $data['client_height'] ?? 0);
        $fps = (float) ($probe['fps'] ?? 30);

        return [
            'duration_seconds' => $duration,
            'width' => $width,
            'height' => $height,
            'fps' => $fps,
            'size_bytes' => $sizeBytes,
            'formatted_duration' => $duration > 0 ? gmdate('i:s', (int) $duration) : '--',
            'formatted_resolution' => ($width && $height) ? "{$width}x{$height}" : '--',
            'formatted_size' => $sizeBytes > 0 ? round($sizeBytes / 1048576, 2).' MB' : '--',
        ];
    }

    private function probeVideo(string $path): ?array
    {
        if (! file_exists($path)) {
            return null;
        }

        try {
            $cmd = [
                'ffprobe',
                '-v', 'error',
                '-select_streams', 'v:0',
                '-show_entries', 'stream=width,height,r_frame_rate,duration',
                '-show_entries', 'format=duration',
                '-of', 'json',
                $path,
            ];
            $process = new Process($cmd);
            $process->setTimeout(10);
            $process->run();

            if (! $process->isSuccessful()) {
                return null;
            }

            $info = json_decode($process->getOutput(), true);
            $stream = $info['streams'][0] ?? [];
            $format = $info['format'] ?? [];

            $duration = (float) ($stream['duration'] ?? $format['duration'] ?? 0);
            $width = (int) ($stream['width'] ?? 0);
            $height = (int) ($stream['height'] ?? 0);

            $fps = 30;
            if (! empty($stream['r_frame_rate'])) {
                $parts = explode('/', $stream['r_frame_rate']);
                if (count($parts) === 2 && (float) $parts[1] > 0) {
                    $fps = round((float) $parts[0] / (float) $parts[1], 2);
                }
            }

            return [
                'duration' => $duration,
                'width' => $width,
                'height' => $height,
                'fps' => $fps,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function compressVideoIfPossible(string $originalAbs, string $dir, ?array $probe): array
    {
        $compressedPath = "{$dir}/optimized.mp4";
        $compressedAbs = storage_path('app/'.$compressedPath);

        try {
            $cmd = [
                'ffmpeg',
                '-y',
                '-i', $originalAbs,
                '-c:v', 'libx264',
                '-preset', 'fast',
                '-crf', '28',
                '-vf', 'scale=trunc(iw*min(720/iw\,1280/ih)/2)*2:trunc(ih*min(720/iw\,1280/ih)/2)*2',
                '-an',
                $compressedAbs,
            ];
            $process = new Process($cmd);
            $process->setTimeout(60);
            $process->run();

            if ($process->isSuccessful() && file_exists($compressedAbs)) {
                $size = filesize($compressedAbs);
                return [
                    'used' => true,
                    'compressed_abs' => $compressedAbs,
                    'compressed_size' => $size,
                    'warnings' => [],
                ];
            }
        } catch (\Throwable $e) {
        }

        return [
            'used' => false,
            'compressed_abs' => null,
            'compressed_size' => null,
            'warnings' => [],
        ];
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

}
