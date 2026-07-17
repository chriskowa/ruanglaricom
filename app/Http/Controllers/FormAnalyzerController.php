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
        $user = auth()->user();
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

            if (! $isAdmin && ! $hasPaidFeature) {
                $ip = $request->ip();
                $sessionId = $request->session()->getId();
                $usageKey = 'form_analyzer:usage:'.$ip.':'.$sessionId;
                $usage = (int) Cache::get($usageKey, 0);
                if ($usage >= self::MAX_TRIES) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'Batas percobaan tercapai.',
                        'code' => 'limit_reached',
                        'message' => 'Kamu sudah mencoba Form Analyzer '.self::MAX_TRIES.'x di perangkat ini. Dukung pengembangan RuangLari untuk akses tanpa batas.',
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
            $analysisService = app(BiomechanicsAnalysisService::class);
            $biomech = $analysisService->normalizeBiomechMetrics($metrics);
            
            // Enforce Expert Mode limit
            if (isset($metrics['samples']) && $metrics['samples'] > 100) {
                if (! $isAdmin && ! $hasPaidFeature) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'Akses ditolak.',
                        'message' => 'Video terlalu detail (Expert Mode). Silakan upgrade untuk analisis ini.',
                    ], 403);
                }
            }

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
