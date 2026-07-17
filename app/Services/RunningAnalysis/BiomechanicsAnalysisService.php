<?php

namespace App\Services\RunningAnalysis;

use App\Services\OpenAiService;
use Illuminate\Support\Facades\Log;

class BiomechanicsAnalysisService
{
    private const FORM_PHASES = [
        'landing' => 'Landing',
        'lever' => 'Lever (mid-stance)',
        'push' => 'Push (toe-off)',
        'pull' => 'Pull (swing)',
        'arm_swing' => 'Ayunan Tangan',
        'posture' => 'Postur & Stabilitas',
    ];

    /**
     * Jalankan analisis deterministik, lalu gunakan AI hanya untuk memperbaiki
     * bahasa dan penyusunan rekomendasi. Nilai skor tetap dihitung oleh rules.
     */
    public function analyze(array $metrics, array $meta = [], array $compressionWarnings = []): array
    {
        $biomech = $this->normalizeBiomechMetrics($metrics);
        $symptoms = $this->normalizeSymptoms($meta);
        $compression = $this->normalizeCompression($meta['compression'] ?? null);

        $capture = $this->buildCaptureFeedback(
            $meta,
            $compression,
            $compressionWarnings
        );

        $biomechanics = $this->buildBiomechFeedback($biomech, $symptoms);
        $formReport = $this->buildFormReport($biomech, $metrics);
        $formScore = $this->calculateFormScore($biomech);
        $videoScore = $capture['video_score'];
        $score = $formScore ?? $videoScore;

        $positives = $this->uniqueByCode(array_merge(
            $capture['positives'],
            $biomechanics['positives']
        ));

        $captureSuggestions = $capture['suggestions'];
        $techniqueSuggestions = $biomechanics['technique_suggestions'];
        $formIssues = $biomechanics['form_issues'];
        $strengthPlan = $biomechanics['strength_plan'];
        $recoveryPlan = $biomechanics['recovery_plan'];

        $coachMessage = $this->buildCoachMessage(
            $meta,
            $videoScore,
            $formScore,
            $formIssues,
            $capture['issues'],
            $symptoms
        );

        // AI tidak boleh mengganti metrik, threshold, atau skor deterministik.
        // AI hanya boleh memperbaiki narasi berdasarkan data yang sudah tersedia.
        if (is_array($biomech) && ! empty(config('services.openai.api_key'))) {
            try {
                $aiFeedback = $this->getAiFeedback(
                    $meta,
                    $biomech,
                    $metrics,
                    $symptoms,
                    [
                        'form_score' => $formScore,
                        'form_issues' => $formIssues,
                        'technique_suggestions' => $techniqueSuggestions,
                        'strength_plan' => $strengthPlan,
                        'recovery_plan' => $recoveryPlan,
                        'form_report' => $formReport,
                    ]
                );

                if (is_array($aiFeedback)) {
                    $aiPositives = $this->normalizePositiveItems($aiFeedback['positives'] ?? []);
                    if ($aiPositives !== []) {
                        $positives = $this->uniqueByCode(array_merge(
                            $capture['positives'],
                            $aiPositives
                        ));
                    }

                    $aiFormIssues = $this->normalizeAiFormIssues(
                        $aiFeedback['form_issues'] ?? [],
                        $formIssues
                    );
                    if ($aiFormIssues !== []) {
                        $formIssues = $aiFormIssues;
                    }

                    $aiTechniqueSuggestions = $this->normalizeTechniqueSuggestions(
                        $aiFeedback['technique_suggestions']
                            ?? $aiFeedback['suggestions']
                            ?? [],
                        $formIssues
                    );
                    if ($aiTechniqueSuggestions !== []) {
                        $techniqueSuggestions = $aiTechniqueSuggestions;
                    }

                    $aiStrengthPlan = $this->normalizeStrengthPlan(
                        $aiFeedback['strength_plan'] ?? [],
                        $formIssues
                    );
                    if ($aiStrengthPlan !== []) {
                        $strengthPlan = $aiStrengthPlan;
                    }

                    $aiRecoveryPlan = $this->normalizeRecoveryPlan(
                        $aiFeedback['recovery_plan'] ?? [],
                        $formIssues,
                        $symptoms
                    );
                    if ($aiRecoveryPlan !== []) {
                        $recoveryPlan = $aiRecoveryPlan;
                    }

                    $aiFormReport = $this->normalizeFormReport(
                        $aiFeedback['form_report'] ?? [],
                        $formReport
                    );
                    if ($aiFormReport !== []) {
                        $formReport = $aiFormReport;
                    }

                    if (isset($aiFeedback['coach_message']) && is_string($aiFeedback['coach_message'])) {
                        $candidateMessage = trim($aiFeedback['coach_message']);
                        if ($candidateMessage !== '') {
                            $coachMessage = $candidateMessage;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning(
                    'BiomechanicsAnalysisService AI feedback failed; deterministic fallback retained: '
                    . $e->getMessage()
                );
            }
        }

        $techniqueSuggestions = $this->sortByPriority($techniqueSuggestions);
        $formIssues = $this->sortByPriority($formIssues);
        $strengthPlan = $this->sortByPriority($strengthPlan);
        $recoveryPlan = $this->sortByPriority($recoveryPlan);

        // Backward compatibility: `suggestions` tetap tersedia, tetapi setiap item
        // sekarang memiliki category `capture` atau `technique`.
        $suggestions = $this->uniqueByCode(array_merge(
            $captureSuggestions,
            $techniqueSuggestions
        ));

        return [
            'score' => $score,
            'video_score' => $videoScore,
            'form_score' => $formScore,
            'positives' => array_slice($positives, 0, 5),

            // Kualitas input/video.
            'issues' => $capture['issues'],
            'capture_suggestions' => $captureSuggestions,

            // Backward-compatible combined suggestions.
            'suggestions' => $suggestions,

            // Analisis biomekanika.
            'form_issues' => $formIssues,
            'technique_suggestions' => $techniqueSuggestions,
            'form_report' => $formReport,

            // Program tindak lanjut.
            'strength_plan' => $strengthPlan,
            'recovery_plan' => $recoveryPlan,

            'coach_message' => $coachMessage,
            'analysis_meta' => [
                'confidence' => $biomech['confidence'] ?? null,
                'samples' => $biomech['samples'] ?? null,
                'symptom_data_available' => $symptoms['available'],
                'engine' => ! empty(config('services.openai.api_key'))
                    ? 'rules_with_ai_narrative'
                    : 'rules_only',
            ],
        ];
    }

    public function normalizeBiomechMetrics(?array $metrics): ?array
    {
        if (! is_array($metrics) || $metrics === []) {
            return null;
        }

        $num = static function (string $key) use ($metrics): ?float {
            $value = $metrics[$key] ?? null;

            return is_numeric($value) ? (float) $value : null;
        };

        $int = static function (string $key) use ($metrics): ?int {
            $value = $metrics[$key] ?? null;

            return is_numeric($value) ? (int) $value : null;
        };

        $string = static function (string $key) use ($metrics): ?string {
            $value = $metrics[$key] ?? null;

            return is_string($value) && trim($value) !== '' ? trim($value) : null;
        };

        return [
            'confidence' => $num('confidence'),
            'samples' => $int('samples'),
            'source' => $string('source'),
            'heel_strike_pct' => $num('heel_strike_pct'),
            'overstride_pct' => $num('overstride_pct'),
            'shin_angle_deg' => $num('shin_angle_deg'),
            'knee_flex_deg' => $num('knee_flex_deg'),
            'trunk_lean_deg' => $num('trunk_lean_deg'),
            'arm_cross_pct' => $num('arm_cross_pct'),
            'cadence_spm' => $num('cadence_spm'),
            'elbow_angle_deg' => $num('elbow_angle_deg'),
            'vertical_oscillation' => $num('vertical_oscillation'),
            'asymmetry' => $num('asymmetry'),
        ];
    }

    private function normalizeCompression(mixed $compression): array
    {
        $compression = is_array($compression) ? $compression : [];

        return [
            'used' => (bool) ($compression['used'] ?? false),
            'original_bytes' => is_numeric($compression['original_bytes'] ?? null)
                ? (int) $compression['original_bytes']
                : null,
            'optimized_bytes' => is_numeric($compression['optimized_bytes'] ?? null)
                ? (int) $compression['optimized_bytes']
                : null,
            'saved_bytes' => is_numeric($compression['saved_bytes'] ?? null)
                ? (int) $compression['saved_bytes']
                : null,
            'saved_percent' => is_numeric($compression['saved_percent'] ?? null)
                ? round((float) $compression['saved_percent'], 1)
                : null,
        ];
    }

    private function normalizeSymptoms(array $meta): array
    {
        $input = is_array($meta['symptoms'] ?? null) ? $meta['symptoms'] : [];

        $read = static function (string $key) use ($input, $meta): mixed {
            return $input[$key] ?? $meta[$key] ?? null;
        };

        $painPresentRaw = $read('pain_present');
        $painPresent = null;
        if (is_bool($painPresentRaw)) {
            $painPresent = $painPresentRaw;
        } elseif (is_numeric($painPresentRaw)) {
            $painPresent = (bool) $painPresentRaw;
        } elseif (is_string($painPresentRaw)) {
            $normalized = strtolower(trim($painPresentRaw));
            if (in_array($normalized, ['yes', 'true', '1', 'ya', 'iya'], true)) {
                $painPresent = true;
            } elseif (in_array($normalized, ['no', 'false', '0', 'tidak'], true)) {
                $painPresent = false;
            }
        }

        $painScore = is_numeric($read('pain_score'))
            ? max(0, min(10, (float) $read('pain_score')))
            : null;

        $painLocation = is_string($read('pain_location'))
            ? trim((string) $read('pain_location'))
            : null;

        $painDurationDays = is_numeric($read('pain_duration_days'))
            ? max(0, (int) $read('pain_duration_days'))
            : null;

        $redFlags = array_values(array_filter([
            $this->toBool($read('swelling')) ? 'Pembengkakan' : null,
            $this->toBool($read('unable_to_bear_weight')) ? 'Sulit menumpu berat badan' : null,
            $this->toBool($read('altered_gait')) ? 'Pola berjalan atau berlari berubah karena nyeri' : null,
            $this->toBool($read('night_pain')) ? 'Nyeri mengganggu tidur' : null,
        ]));

        return [
            'available' => $painPresent !== null
                || $painScore !== null
                || $painLocation !== null
                || $painDurationDays !== null
                || $redFlags !== [],
            'pain_present' => $painPresent,
            'pain_score' => $painScore,
            'pain_location' => $painLocation,
            'pain_duration_days' => $painDurationDays,
            'pain_during_running' => $this->nullableBool($read('pain_during_running')),
            'pain_after_running' => $this->nullableBool($read('pain_after_running')),
            'recent_injury' => $this->nullableBool($read('recent_injury')),
            'weekly_distance_km' => is_numeric($read('weekly_distance_km'))
                ? max(0, (float) $read('weekly_distance_km'))
                : null,
            'red_flags' => $redFlags,
        ];
    }

    private function buildCaptureFeedback(
        array $meta,
        array $compression,
        array $compressionWarnings
    ): array {
        $issues = [];
        $suggestions = [];
        $positives = [];
        $videoScore = 100;

        $duration = is_numeric($meta['duration_seconds'] ?? null)
            ? (float) $meta['duration_seconds']
            : null;
        $width = is_numeric($meta['width'] ?? null) ? (int) $meta['width'] : null;
        $height = is_numeric($meta['height'] ?? null) ? (int) $meta['height'] : null;
        $fps = is_numeric($meta['fps'] ?? null) ? (float) $meta['fps'] : null;
        $size = is_numeric($meta['size_bytes'] ?? null) ? (int) $meta['size_bytes'] : null;
        $isPortrait = array_key_exists('is_portrait', $meta)
            ? (bool) $meta['is_portrait']
            : null;

        if ($duration === null) {
            $videoScore -= 10;
            $issues[] = $this->captureIssue(
                'duration_unknown',
                'Durasi video tidak terbaca',
                'Sistem tidak memperoleh metadata durasi sehingga penilaian kualitas rekaman menjadi terbatas.',
                'medium'
            );
        } elseif ($duration < 4) {
            $videoScore -= 25;
            $issues[] = $this->captureIssue(
                'duration_too_short',
                'Durasi terlalu pendek',
                'Rekaman belum memberikan cukup langkah stabil untuk menilai pola gerak secara konsisten.',
                'high',
                $duration,
                'detik',
                'Ideal 5–12 detik'
            );
            $suggestions[] = $this->captureSuggestion(
                'duration_fix',
                ['duration_too_short'],
                'high',
                'Rekam 5–12 detik saat pace stabil',
                'Mulai merekam sebelum pelari memasuki frame, pertahankan kamera diam, lalu ambil beberapa langkah dengan kecepatan yang konsisten.'
            );
        } elseif ($duration > 20) {
            $videoScore -= 15;
            $issues[] = $this->captureIssue(
                'duration_too_long',
                'Durasi terlalu panjang',
                'Segmen yang terlalu panjang memperbesar ukuran berkas tanpa meningkatkan kualitas analisis secara berarti.',
                'medium',
                $duration,
                'detik',
                'Ideal 5–12 detik'
            );
            $suggestions[] = $this->captureSuggestion(
                'duration_trim',
                ['duration_too_long'],
                'medium',
                'Gunakan segmen paling stabil',
                'Potong video pada bagian ketika seluruh tubuh terlihat dan kecepatan lari sudah stabil.'
            );
        } elseif ($duration >= 5 && $duration <= 12) {
            $positives[] = $this->positive(
                'duration_good',
                'Durasi rekaman memadai',
                'Rekaman menyediakan cukup langkah untuk membaca pola tanpa menambah data yang tidak diperlukan.'
            );
        } else {
            $videoScore -= 5;
        }

        if ($width === null || $height === null) {
            $videoScore -= 10;
            $issues[] = $this->captureIssue(
                'resolution_unknown',
                'Resolusi tidak terbaca',
                'Pastikan video dapat diproses dan tubuh pelari tetap terlihat dengan jelas.',
                'medium'
            );
        } else {
            $minSide = min($width, $height);
            if ($minSide < 480) {
                $videoScore -= 25;
                $issues[] = $this->captureIssue(
                    'resolution_low',
                    'Resolusi terlalu rendah',
                    'Detail pada lutut, pergelangan kaki, dan kontak kaki berisiko tidak terbaca stabil.',
                    'high',
                    "{$width}×{$height}",
                    'px',
                    'Minimal 720p disarankan'
                );
                $suggestions[] = $this->captureSuggestion(
                    'resolution_fix',
                    ['resolution_low'],
                    'high',
                    'Gunakan resolusi minimal 720p',
                    'Hindari zoom digital dan posisikan kamera cukup dekat agar tubuh memenuhi frame tanpa terpotong.'
                );
            } elseif ($minSide < 720) {
                $videoScore -= 10;
                $issues[] = $this->captureIssue(
                    'resolution_mid',
                    'Resolusi cukup tetapi belum optimal',
                    'Analisis tetap dapat dijalankan, namun detail sendi akan lebih konsisten pada resolusi 720p atau lebih tinggi.',
                    'medium',
                    "{$width}×{$height}",
                    'px',
                    '720p atau lebih'
                );
            } else {
                $positives[] = $this->positive(
                    'resolution_good',
                    'Resolusi rekaman baik',
                    'Detail tubuh dan sendi memiliki ruang yang memadai untuk dianalisis.'
                );
            }

            if ($isPortrait === true) {
                $videoScore -= 10;
                $issues[] = $this->captureIssue(
                    'portrait_orientation',
                    'Orientasi portrait membatasi ruang gerak',
                    'Ruang horizontal yang sempit meningkatkan risiko tubuh atau langkah keluar dari frame.',
                    'medium'
                );
                $suggestions[] = $this->captureSuggestion(
                    'landscape_orientation',
                    ['portrait_orientation'],
                    'medium',
                    'Gunakan orientasi landscape',
                    'Letakkan kamera horizontal dan sisakan ruang di depan serta belakang arah lari.'
                );
            } elseif ($isPortrait === false) {
                $positives[] = $this->positive(
                    'orientation_good',
                    'Orientasi rekaman sesuai',
                    'Frame horizontal memberi ruang yang lebih baik untuk menangkap beberapa fase langkah.'
                );
            }
        }

        if ($fps === null) {
            $videoScore -= 6;
            $issues[] = $this->captureIssue(
                'fps_unknown',
                'Frame rate tidak terbaca',
                'Ketajaman gerak cepat tidak dapat dinilai dari metadata video.',
                'low'
            );
        } elseif ($fps < 24) {
            $videoScore -= 20;
            $issues[] = $this->captureIssue(
                'fps_low',
                'Frame rate terlalu rendah',
                'Gerakan cepat pada kaki dan pergelangan kaki berisiko terlewat atau tampak kabur.',
                'high',
                round($fps, 1),
                'fps',
                '30–60 fps disarankan'
            );
            $suggestions[] = $this->captureSuggestion(
                'fps_fix',
                ['fps_low'],
                'high',
                'Rekam pada 30 atau 60 fps',
                'Gunakan pencahayaan yang cukup dan hindari menggerakkan kamera selama pelari melewati frame.'
            );
        } elseif ($fps < 30) {
            $videoScore -= 8;
            $issues[] = $this->captureIssue(
                'fps_mid',
                'Frame rate cukup tetapi belum optimal',
                'Detail fase kontak dan toe-off akan lebih jelas pada 30 fps atau lebih.',
                'medium',
                round($fps, 1),
                'fps',
                'Minimal 30 fps'
            );
        } else {
            $positives[] = $this->positive(
                'fps_good',
                'Frame rate memadai',
                'Gerakan cepat memiliki jumlah frame yang lebih cukup untuk dibaca.'
            );
        }

        if ($size !== null && $size > 70 * 1024 * 1024) {
            $videoScore -= 8;
            $issues[] = $this->captureIssue(
                'file_size_large',
                'Ukuran berkas terlalu besar',
                'Berkas besar memperpanjang waktu unggah dan meningkatkan risiko kegagalan koneksi.',
                'medium',
                round($size / 1024 / 1024, 1),
                'MB',
                'Gunakan segmen pendek 720p–1080p'
            );
            $suggestions[] = $this->captureSuggestion(
                'file_size_fix',
                ['file_size_large'],
                'medium',
                'Kurangi durasi, bukan kualitas gerak',
                'Gunakan segmen 5–12 detik dan kompresi moderat agar detail sendi tetap terjaga.'
            );
        }

        foreach ($compressionWarnings as $index => $warning) {
            if (! is_array($warning)) {
                continue;
            }

            $issues[] = [
                'code' => (string) ($warning['code'] ?? "compression_warning_{$index}"),
                'category' => 'capture',
                'title' => (string) ($warning['title'] ?? 'Peringatan kompresi'),
                'message' => (string) ($warning['message'] ?? 'Kompresi video dapat memengaruhi kualitas analisis.'),
                'severity' => $this->normalizeSeverity($warning['severity'] ?? 'medium', false),
            ];
        }

        if ($compression['used']) {
            $saved = $compression['saved_percent'];
            $positives[] = $this->positive(
                'optimized_upload',
                'Berkas berhasil dioptimalkan',
                $saved !== null
                    ? "Ukuran berkas berkurang sekitar {$saved}% tanpa mengubah data analisis yang tersedia."
                    : 'Ukuran berkas berhasil diperkecil untuk memperlancar proses unggah.'
            );
        }

        return [
            'video_score' => (int) max(0, min(100, round($videoScore))),
            'issues' => $this->sortByPriority($this->uniqueByCode($issues)),
            'suggestions' => $this->sortByPriority($this->uniqueByCode($suggestions)),
            'positives' => $this->uniqueByCode($positives),
        ];
    }

    private function buildBiomechFeedback(?array $biomech, array $symptoms): array
    {
        if (! is_array($biomech)) {
            $issue = $this->formIssue(
                code: 'biomech_missing',
                phase: null,
                metricCode: null,
                title: 'Data biomekanika belum tersedia',
                message: 'Pose tubuh tidak terbaca cukup baik untuk menghasilkan evaluasi teknik lari.',
                severity: 'high',
                value: null,
                unit: null,
                reference: 'Rekam tampak samping, tubuh penuh terlihat, dan pencahayaan cukup.',
                confidence: null,
                samples: null,
                priorityScore: 100
            );

            return [
                'positives' => [],
                'form_issues' => [$issue],
                'technique_suggestions' => [],
                'strength_plan' => [],
                'recovery_plan' => [],
                'coach_lines' => [
                    'Data pose belum cukup untuk memberikan koreksi teknik yang dapat dipertanggungjawabkan.',
                ],
            ];
        }

        $formIssues = [];
        $techniqueSuggestions = [];
        $strengthPlan = [];
        $positives = [];
        $coachLines = [];

        $confidence = $biomech['confidence'] ?? null;
        $samples = $biomech['samples'] ?? null;

        if (is_numeric($confidence) && $confidence < 0.45) {
            $formIssues[] = $this->formIssue(
                'biomech_low_confidence',
                null,
                'confidence',
                'Kepercayaan deteksi pose rendah',
                'Sendi tidak terbaca stabil. Temuan biomekanika berikut harus diperlakukan sebagai indikasi, bukan kesimpulan pasti.',
                'high',
                round($confidence * 100, 1),
                '%',
                'Confidence ≥70% lebih layak untuk interpretasi utama.',
                $confidence,
                $samples,
                96
            );
            $coachLines[] = 'Prioritaskan rekaman ulang sebelum melakukan perubahan teknik yang besar.';
        } elseif (is_numeric($confidence) && $confidence >= 0.7) {
            $positives[] = $this->positive(
                'biomech_confidence_good',
                'Deteksi pose cukup stabil',
                'Kepercayaan pembacaan sendi memadai untuk melihat pola gerak utama.'
            );
        }

        if (is_numeric($samples) && $samples >= 5) {
            $positives[] = $this->positive(
                'biomech_samples_good',
                'Analisis menggunakan beberapa frame',
                "Evaluasi disusun dari {$samples} sampel sehingga tidak bergantung pada satu posisi sesaat."
            );
        }

        $heel = $biomech['heel_strike_pct'] ?? null;
        $overstride = $biomech['overstride_pct'] ?? null;
        $shin = $biomech['shin_angle_deg'] ?? null;
        $knee = $biomech['knee_flex_deg'] ?? null;
        $trunk = $biomech['trunk_lean_deg'] ?? null;
        $armCross = $biomech['arm_cross_pct'] ?? null;
        $cadence = $biomech['cadence_spm'] ?? null;
        $elbow = $biomech['elbow_angle_deg'] ?? null;
        $verticalOscillation = $biomech['vertical_oscillation'] ?? null;
        $asymmetry = $biomech['asymmetry'] ?? null;

        if (is_numeric($heel)) {
            if ($heel >= 70) {
                $formIssues[] = $this->formIssue(
                    'heel_strike_high',
                    'landing',
                    'heel_strike_pct',
                    'Kontak tumit dominan',
                    'Tumit terdeteksi menyentuh permukaan lebih dahulu pada mayoritas langkah. Nilai ini perlu dibaca bersama overstride dan shin angle, bukan sebagai masalah tunggal.',
                    'medium',
                    round($heel, 1),
                    '%',
                    'Dominan ≥70%; evaluasi gabungan dengan posisi kaki dan tibia.',
                    $confidence,
                    $samples,
                    62
                );
            } elseif ($heel < 40) {
                $positives[] = $this->positive(
                    'heel_strike_not_dominant',
                    'Kontak tumit tidak dominan',
                    'Pola kontak awal tidak menunjukkan dominasi heel-first pada sebagian besar sampel.'
                );
            }
        }

        if (is_numeric($overstride)) {
            if ($overstride >= 60) {
                $formIssues[] = $this->formIssue(
                    'overstride_high',
                    'landing',
                    'overstride_pct',
                    'Overstriding dominan',
                    'Kaki cukup sering mendarat jauh di depan proyeksi pinggul sehingga berpotensi meningkatkan gaya pengereman.',
                    'high',
                    round($overstride, 1),
                    '%',
                    'Prioritas tinggi ≥60%; target awal <35%.',
                    $confidence,
                    $samples,
                    92
                );
                $techniqueSuggestions[] = $this->techniqueSuggestion(
                    'overstride_step_length',
                    ['overstride_high'],
                    'landing',
                    'high',
                    'Kurangi jangkauan langkah secara bertahap',
                    'Fokuskan kaki mendarat lebih dekat ke bawah pinggul tanpa memaksa perubahan footstrike secara mendadak.',
                    'Langkah cepat, ringan, dan mendarat dekat tubuh.',
                    'Easy run atau drill teknik selama 5–10 menit.',
                    'Mulai dari perubahan kecil; pertahankan pace tetap ringan.'
                );
                $strengthPlan[] = $this->strengthExercise(
                    'overstride_single_leg_rdl',
                    ['overstride_high'],
                    'high',
                    'posterior_chain',
                    'Single-Leg Romanian Deadlift',
                    'Meningkatkan kontrol pinggul dan rantai belakang agar posisi kaki lebih terkendali saat landing.',
                    ['Gluteus', 'Hamstring', 'Hip stabilizer'],
                    'Bodyweight atau dumbbell ringan',
                    'intermediate',
                    3,
                    '8 repetisi per sisi',
                    '3-1-1',
                    75,
                    '2 kali per minggu',
                    [
                        'Jaga pinggul tetap sejajar.',
                        'Dorong pinggul ke belakang.',
                        'Pertahankan punggung netral.',
                    ],
                    [
                        'Gunakan kickstand RDL atau berpegangan pada dinding.',
                    ],
                    [
                        'Tambah beban ringan setelah dua sesi berturut-turut dapat diselesaikan dengan teknik stabil.',
                    ]
                );
            } elseif ($overstride >= 35) {
                $formIssues[] = $this->formIssue(
                    'overstride_moderate',
                    'landing',
                    'overstride_pct',
                    'Kecenderungan overstriding',
                    'Sebagian langkah masih mendarat agak jauh di depan pinggul dan dapat dikoreksi melalui perubahan kecil pada ritme serta panjang langkah.',
                    'medium',
                    round($overstride, 1),
                    '%',
                    'Perhatian 35–59%; target awal <35%.',
                    $confidence,
                    $samples,
                    70
                );
                $techniqueSuggestions[] = $this->techniqueSuggestion(
                    'overstride_quick_steps',
                    ['overstride_moderate'],
                    'landing',
                    'medium',
                    'Gunakan langkah sedikit lebih cepat',
                    'Naikkan ritme secara kecil sambil mempertahankan pace dan relaksasi tubuh.',
                    'Quick, quiet steps.',
                    'Easy run.',
                    'Mulai sekitar 3% di atas cadence saat ini jika cadence tersedia.'
                );
            } else {
                $positives[] = $this->positive(
                    'overstride_low',
                    'Posisi landing relatif efisien',
                    'Kaki umumnya mendarat dekat dengan proyeksi pusat tubuh.'
                );
            }
        }

        if (is_numeric($shin) && $shin >= 18) {
            $formIssues[] = $this->formIssue(
                'shin_angle_high',
                'lever',
                'shin_angle_deg',
                'Sudut tibia menunjukkan kecenderungan braking',
                'Tibia cukup miring saat kontak sehingga posisi kaki berpotensi menahan gerak maju.',
                'medium',
                round($shin, 1),
                '°',
                'Perhatian ≥18°; nilai yang lebih dekat vertikal umumnya mengurangi braking.',
                $confidence,
                $samples,
                68
            );
            $techniqueSuggestions[] = $this->techniqueSuggestion(
                'shin_angle_push_backward',
                ['shin_angle_high'],
                'lever',
                'medium',
                'Arahkan dorongan ke belakang',
                'Pertahankan kaki lebih dekat ke tubuh dan pikirkan dorongan permukaan ke belakang, bukan menjangkau ke depan.',
                'Dorong tanah ke belakang.',
                'Strides ringan setelah easy run.',
                '4–6 repetisi pendek dengan pemulihan penuh.'
            );
        }

        if (is_numeric($knee)) {
            if ($knee < 20) {
                $formIssues[] = $this->formIssue(
                    'knee_flexion_low',
                    'lever',
                    'knee_flex_deg',
                    'Fleksi lutut saat landing rendah',
                    'Lutut relatif kaku pada fase penerimaan beban sehingga kemampuan menyerap benturan dapat berkurang.',
                    'high',
                    round($knee, 1),
                    '°',
                    'Prioritas tinggi <20°; rentang referensi analisis 30–55°.',
                    $confidence,
                    $samples,
                    88
                );
                $techniqueSuggestions[] = $this->techniqueSuggestion(
                    'soft_landing_control',
                    ['knee_flexion_low'],
                    'lever',
                    'high',
                    'Latih penerimaan beban yang lebih terkontrol',
                    'Hindari mengunci lutut saat kaki menyentuh permukaan dan pertahankan pinggul tetap stabil.',
                    'Mendarat tenang dengan lutut tidak terkunci.',
                    'Drill low pogo atau step landing ringan jika tidak ada nyeri.',
                    'Kualitas gerak lebih penting daripada tinggi atau kecepatan.'
                );
                $strengthPlan[] = $this->strengthExercise(
                    'knee_flexion_step_down',
                    ['knee_flexion_low'],
                    'high',
                    'landing_control',
                    'Controlled Step-Down',
                    'Meningkatkan kontrol eksentrik lutut dan pinggul saat menerima beban satu kaki.',
                    ['Quadriceps', 'Gluteus medius', 'Hip stabilizer'],
                    'Step rendah',
                    'beginner',
                    3,
                    '8 repetisi per sisi',
                    '3-1-1',
                    60,
                    '2 kali per minggu',
                    [
                        'Turunkan tumit secara perlahan.',
                        'Jaga lutut mengikuti arah jari kaki.',
                        'Pertahankan panggul tetap sejajar.',
                    ],
                    [
                        'Gunakan step lebih rendah atau berpegangan.',
                    ],
                    [
                        'Naikkan tinggi step setelah kontrol tetap baik pada seluruh repetisi.',
                    ]
                );
            } elseif ($knee >= 30 && $knee <= 55) {
                $positives[] = $this->positive(
                    'knee_flexion_good',
                    'Fleksi lutut berada pada rentang referensi',
                    'Tekukan lutut saat landing cukup mendukung penerimaan beban.'
                );
            }
        }

        if (is_numeric($trunk)) {
            if ($trunk > 18) {
                $formIssues[] = $this->formIssue(
                    'trunk_lean_excessive',
                    'posture',
                    'trunk_lean_deg',
                    'Kemiringan badan ke depan berlebihan',
                    'Kemiringan yang besar dapat menunjukkan badan membungkuk dari pinggang, bukan lean ringan dari pergelangan kaki.',
                    'medium',
                    round($trunk, 1),
                    '°',
                    'Perhatian >18°; referensi umum 5–15°.',
                    $confidence,
                    $samples,
                    66
                );
                $techniqueSuggestions[] = $this->techniqueSuggestion(
                    'trunk_lean_from_ankles',
                    ['trunk_lean_excessive'],
                    'posture',
                    'medium',
                    'Pertahankan garis tubuh yang panjang',
                    'Kurangi lipatan pada pinggang dan gunakan lean ringan dari pergelangan kaki.',
                    'Tinggi dari kepala ke pinggul; lean dari ankle.',
                    'Easy run dan wall lean drill.',
                    'Jaga bahu rileks dan pandangan ke depan.'
                );
                $strengthPlan[] = $this->strengthExercise(
                    'trunk_dead_bug',
                    ['trunk_lean_excessive'],
                    'medium',
                    'trunk_control',
                    'Dead Bug',
                    'Meningkatkan kontrol batang tubuh tanpa menambah gerakan pinggang yang berlebihan.',
                    ['Deep core', 'Hip flexor control'],
                    'Matras',
                    'beginner',
                    3,
                    '8–10 repetisi per sisi',
                    '2-1-2',
                    45,
                    '2–3 kali per minggu',
                    [
                        'Pertahankan punggung bawah netral.',
                        'Buang napas saat lengan dan kaki menjauh.',
                    ],
                    [
                        'Gerakkan satu kaki saja.',
                    ],
                    [
                        'Perpanjang tuas secara bertahap setelah posisi torso stabil.',
                    ]
                );
            } elseif ($trunk < -5) {
                $formIssues[] = $this->formIssue(
                    'trunk_lean_backward',
                    'posture',
                    'trunk_lean_deg',
                    'Badan cenderung tegak atau condong ke belakang',
                    'Posisi ini dapat menyulitkan perpindahan pusat massa ke depan dan sering muncul bersama langkah yang terlalu menjangkau.',
                    'medium',
                    round($trunk, 1),
                    '°',
                    'Perhatian <−5°; referensi umum 5–15° ke depan.',
                    $confidence,
                    $samples,
                    64
                );
            } elseif ($trunk >= 5 && $trunk <= 15) {
                $positives[] = $this->positive(
                    'trunk_lean_good',
                    'Kemiringan badan berada pada rentang referensi',
                    'Postur mendukung perpindahan tubuh ke depan tanpa lean berlebihan.'
                );
            }
        }

        if (is_numeric($armCross)) {
            if ($armCross >= 55) {
                $formIssues[] = $this->formIssue(
                    'arm_cross_high',
                    'arm_swing',
                    'arm_cross_pct',
                    'Ayunan tangan sering menyilang garis tengah',
                    'Pola ini meningkatkan rotasi torso yang tidak diperlukan dan dapat mengurangi efisiensi gerak maju.',
                    'medium',
                    round($armCross, 1),
                    '%',
                    'Perhatian ≥55%; target awal <45%.',
                    $confidence,
                    $samples,
                    65
                );
                $techniqueSuggestions[] = $this->techniqueSuggestion(
                    'arm_swing_front_back',
                    ['arm_cross_high'],
                    'arm_swing',
                    'medium',
                    'Arahkan ayunan tangan ke depan dan belakang',
                    'Pertahankan siku dekat tubuh dan hindari tangan melewati garis tengah dada.',
                    'Siku ke belakang, tangan tidak menyilang.',
                    'Easy run dan arm-swing drill di tempat.',
                    'Lakukan 2–3 set selama 20–30 detik.'
                );
                $strengthPlan[] = $this->strengthExercise(
                    'arm_cross_band_row',
                    ['arm_cross_high'],
                    'supporting',
                    'scapular_control',
                    'Resistance-Band Row',
                    'Mendukung kontrol skapula dan posisi bahu agar ayunan lengan lebih stabil.',
                    ['Mid-back', 'Rear shoulder', 'Scapular stabilizer'],
                    'Resistance band',
                    'beginner',
                    3,
                    '12 repetisi',
                    '2-1-2',
                    45,
                    '2 kali per minggu',
                    [
                        'Tarik siku ke belakang tanpa mengangkat bahu.',
                        'Jaga rusuk tidak mengembang berlebihan.',
                    ],
                    [
                        'Gunakan resistance band yang lebih ringan.',
                    ],
                    [
                        'Tambah tahanan setelah 12 repetisi tetap stabil.',
                    ]
                );
            } elseif ($armCross < 45) {
                $positives[] = $this->positive(
                    'arm_cross_good',
                    'Arah ayunan tangan relatif efisien',
                    'Lengan umumnya bergerak searah dengan gerak maju tubuh.'
                );
            }
        }

        if (is_numeric($cadence)) {
            if ($cadence < 155) {
                $formIssues[] = $this->formIssue(
                    'cadence_low',
                    'landing',
                    'cadence_spm',
                    'Cadence berada di bawah rentang analisis',
                    'Ritme langkah yang rendah dapat berkaitan dengan langkah lebih panjang, tetapi harus disesuaikan dengan pace, tinggi badan, dan konteks lari.',
                    'medium',
                    round($cadence, 1),
                    'spm',
                    'Perhatian <155 spm; perubahan harus bertahap dan kontekstual.',
                    $confidence,
                    $samples,
                    69
                );
                $techniqueSuggestions[] = $this->techniqueSuggestion(
                    'cadence_gradual_increase',
                    ['cadence_low'],
                    'landing',
                    'medium',
                    'Uji peningkatan cadence secara kecil',
                    'Pertahankan pace ringan lalu naikkan cadence sekitar 3% untuk melihat apakah landing menjadi lebih dekat ke tubuh.',
                    'Lebih cepat sedikit, bukan berlari lebih kencang.',
                    'Blok 2–3 menit dalam easy run.',
                    'Kembali ke cadence normal jika gerakan menjadi tegang.'
                );
            } elseif ($cadence >= 165 && $cadence <= 190) {
                $positives[] = $this->positive(
                    'cadence_reference_range',
                    'Cadence berada pada rentang referensi',
                    'Ritme langkah berada dalam rentang yang lazim digunakan sebagai acuan efisiensi.'
                );
            }
        }

        if (is_numeric($verticalOscillation)) {
            if ($verticalOscillation >= 0.012) {
                $formIssues[] = $this->formIssue(
                    'vertical_oscillation_high',
                    'push',
                    'vertical_oscillation',
                    'Gerak vertikal relatif tinggi',
                    'Sebagian energi gerak tampak lebih banyak diarahkan ke atas daripada ke depan.',
                    'medium',
                    round($verticalOscillation, 4),
                    'unit deteksi',
                    'Perhatian ≥0,012 pada sistem pengukuran ini.',
                    $confidence,
                    $samples,
                    67
                );
                $techniqueSuggestions[] = $this->techniqueSuggestion(
                    'vertical_oscillation_forward_flow',
                    ['vertical_oscillation_high'],
                    'push',
                    'medium',
                    'Fokuskan gerak ke depan',
                    'Gunakan langkah sedikit lebih pendek dan hindari mendorong tubuh terlalu tinggi pada toe-off.',
                    'Meluncur ke depan, bukan memantul ke atas.',
                    'Easy run dan strides ringan.',
                    'Jaga effort tetap rendah.'
                );
                $strengthPlan[] = $this->strengthExercise(
                    'vertical_oscillation_calf_raise',
                    ['vertical_oscillation_high'],
                    'supporting',
                    'ankle_stiffness',
                    'Controlled Calf Raise',
                    'Membangun kapasitas betis dan kontrol pergelangan kaki sebelum menggunakan drill plyometric.',
                    ['Gastrocnemius', 'Soleus'],
                    'Step atau lantai datar',
                    'beginner',
                    3,
                    '12–15 repetisi',
                    '2-1-3',
                    45,
                    '2–3 kali per minggu',
                    [
                        'Naik dan turun dengan kontrol.',
                        'Jaga beban pada pangkal ibu jari dan jari kedua.',
                    ],
                    [
                        'Lakukan dengan dua kaki dan berpegangan.',
                    ],
                    [
                        'Lanjutkan ke single-leg calf raise setelah repetisi stabil dan tanpa nyeri.',
                    ]
                );
            } else {
                $positives[] = $this->positive(
                    'vertical_oscillation_good',
                    'Gerak vertikal relatif terkendali',
                    'Tubuh tidak menunjukkan pantulan vertikal yang berlebihan pada sistem pengukuran ini.'
                );
            }
        }

        if (is_numeric($asymmetry)) {
            if ($asymmetry >= 0.25) {
                $formIssues[] = $this->formIssue(
                    'asymmetry_high',
                    'posture',
                    'asymmetry',
                    'Asimetri kanan-kiri terdeteksi',
                    'Perbedaan pola kanan dan kiri cukup besar pada sampel yang dianalisis. Temuan perlu dikonfirmasi dengan rekaman tambahan dan informasi gejala.',
                    'high',
                    round($asymmetry, 3),
                    'rasio',
                    'Prioritas konfirmasi ≥0,25.',
                    $confidence,
                    $samples,
                    90
                );
                $techniqueSuggestions[] = $this->techniqueSuggestion(
                    'asymmetry_recheck',
                    ['asymmetry_high'],
                    'posture',
                    'high',
                    'Konfirmasi perbedaan kanan dan kiri',
                    'Ulangi rekaman pada kondisi yang sama dan hindari memaksakan koreksi satu sisi sebelum temuan konsisten.',
                    'Gerak simetris dan rileks.',
                    'Rekaman ulang serta easy run terkontrol.',
                    'Bandingkan minimal dua percobaan.'
                );
                $strengthPlan[] = $this->strengthExercise(
                    'asymmetry_split_squat',
                    ['asymmetry_high'],
                    'high',
                    'unilateral_control',
                    'Supported Split Squat',
                    'Meningkatkan kontrol satu sisi dan membantu membandingkan kualitas gerak kanan-kiri.',
                    ['Quadriceps', 'Gluteus', 'Hip stabilizer'],
                    'Bodyweight dan penyangga',
                    'beginner',
                    3,
                    '8 repetisi per sisi',
                    '3-1-1',
                    75,
                    '2 kali per minggu',
                    [
                        'Gunakan rentang gerak yang sama pada kedua sisi.',
                        'Jaga lutut mengikuti arah jari kaki.',
                        'Catat jika satu sisi jauh lebih sulit.',
                    ],
                    [
                        'Kurangi kedalaman dan gunakan pegangan.',
                    ],
                    [
                        'Tambah beban hanya jika kedua sisi stabil.',
                    ]
                );
            } elseif ($asymmetry < 0.15) {
                $positives[] = $this->positive(
                    'asymmetry_low',
                    'Perbedaan kanan-kiri relatif kecil',
                    'Tidak terlihat asimetri besar pada indikator yang tersedia.'
                );
            }
        }

        // Elbow angle hanya dilaporkan sebagai konteks karena rentang ideal sangat
        // bergantung pada pace dan tidak cukup kuat sebagai issue tunggal.
        if (is_numeric($elbow) && ($elbow < 55 || $elbow > 125)) {
            $formIssues[] = $this->formIssue(
                'elbow_angle_outlier',
                'arm_swing',
                'elbow_angle_deg',
                'Sudut siku berada di luar rentang observasi umum',
                'Sudut siku terlihat sangat tertutup atau sangat terbuka. Evaluasi bersama arah ayunan tangan dan tingkat relaksasi bahu.',
                'low',
                round($elbow, 1),
                '°',
                'Kontekstual; jangan digunakan sebagai diagnosis atau target tunggal.',
                $confidence,
                $samples,
                42
            );
        }

        // Temuan gabungan lebih kuat daripada menyimpulkan risiko hanya dari heel strike.
        if (
            is_numeric($heel)
            && is_numeric($overstride)
            && is_numeric($shin)
            && $heel >= 70
            && $overstride >= 60
            && $shin >= 18
        ) {
            $formIssues[] = [
                'code' => 'landing_braking_pattern',
                'phase' => 'landing',
                'metric_code' => null,
                'title' => 'Pola landing dengan indikasi braking',
                'message' => 'Kontak tumit dominan muncul bersama overstride dan shin angle tinggi. Kombinasi ini lebih relevan untuk prioritas koreksi dibanding heel strike secara terpisah.',
                'observed_value' => null,
                'unit' => null,
                'reference' => 'Temuan gabungan dari tiga indikator landing.',
                'confidence' => $confidence,
                'sample_count' => $samples,
                'priority_score' => 98,
                'severity' => 'high',
                'evidence' => [
                    'heel_strike_pct' => round($heel, 1),
                    'overstride_pct' => round($overstride, 1),
                    'shin_angle_deg' => round($shin, 1),
                ],
            ];
        }

        $formIssues = $this->sortByPriority($this->uniqueByCode($formIssues));
        $techniqueSuggestions = $this->filterSuggestionsByIssues(
            $this->uniqueByCode($techniqueSuggestions),
            $formIssues
        );
        $strengthPlan = $this->filterSuggestionsByIssues(
            $this->uniqueByCode($strengthPlan),
            $formIssues
        );

        $recoveryPlan = $this->buildRecoveryPlan($formIssues, $symptoms);

        return [
            'positives' => $this->uniqueByCode($positives),
            'form_issues' => array_slice($formIssues, 0, 8),
            'technique_suggestions' => array_slice($this->sortByPriority($techniqueSuggestions), 0, 5),
            'strength_plan' => array_slice($this->sortByPriority($strengthPlan), 0, 5),
            'recovery_plan' => $recoveryPlan,
            'coach_lines' => $coachLines,
        ];
    }

    public function buildFormReport(?array $biomech, ?array $metrics): array
    {
        $sections = [];
        foreach (self::FORM_PHASES as $code => $title) {
            $sections[$code] = [
                'code' => $code,
                'title' => $title,
                'status' => 'ok',
                'summary' => null,
                'findings' => [],
                'impact' => [],
                'actions' => [],
                'strength' => [],
            ];
        }

        if (! is_array($biomech)) {
            foreach ($sections as &$section) {
                $section['status'] = 'missing';
                $section['summary'] = 'Data pose belum cukup untuk menilai fase ini.';
                $section['actions'][] = 'Rekam tampak samping, tubuh penuh terlihat, cahaya cukup, dan pace stabil selama 5–12 detik.';
            }
            unset($section);

            return array_values($sections);
        }

        $coverage = is_array($metrics['coverage'] ?? null) ? $metrics['coverage'] : [];
        $coverageMissing = is_array($metrics['coverage_missing'] ?? null)
            ? array_values(array_filter($metrics['coverage_missing'], 'is_string'))
            : [];

        $formFeedback = $this->buildBiomechFeedback(
            $biomech,
            [
                'available' => false,
                'pain_present' => null,
                'pain_score' => null,
                'pain_location' => null,
                'pain_duration_days' => null,
                'pain_during_running' => null,
                'pain_after_running' => null,
                'recent_injury' => null,
                'weekly_distance_km' => null,
                'red_flags' => [],
            ]
        );

        foreach ($formFeedback['form_issues'] as $issue) {
            $phase = $issue['phase'] ?? null;
            if (! isset($sections[$phase])) {
                continue;
            }

            $sections[$phase]['findings'][] = $this->formatIssueEvidence($issue);
            $sections[$phase]['impact'][] = $issue['message'];
            $this->raiseSectionStatus($sections[$phase], $issue['severity'] ?? 'low');
            if ($sections[$phase]['summary'] === null) {
                $sections[$phase]['summary'] = $issue['title'];
            }
        }

        foreach ($formFeedback['technique_suggestions'] as $suggestion) {
            $phase = $suggestion['phase'] ?? null;
            if (isset($sections[$phase])) {
                $sections[$phase]['actions'][] = $suggestion['message'];
            }
        }

        foreach ($formFeedback['strength_plan'] as $exercise) {
            $phase = $this->phaseFromIssueCodes(
                $exercise['issue_codes'] ?? [],
                $formFeedback['form_issues']
            );

            if (isset($sections[$phase])) {
                $dosage = $exercise['dosage'] ?? [];
                $sets = $dosage['sets'] ?? null;
                $reps = $dosage['reps'] ?? null;
                $label = $exercise['title'];
                if ($sets !== null || $reps !== null) {
                    $label .= ' — ' . trim(implode(' × ', array_filter([
                        $sets !== null ? (string) $sets : null,
                        is_string($reps) ? $reps : null,
                    ])));
                }
                $sections[$phase]['strength'][] = $label;
            }
        }

        // Pull/swing belum memiliki metrik khusus dalam input saat ini.
        if ($sections['pull']['findings'] === []) {
            $sections['pull']['status'] = 'missing';
            $sections['pull']['summary'] = 'Belum ada metrik swing yang tervalidasi.';
            $sections['pull']['findings'][] = 'Fase pull belum dinilai karena input belum menyediakan metrik swing yang spesifik.';
            $sections['pull']['actions'][] = 'Gunakan rekaman dengan visibilitas lutut dan pergelangan kaki yang jelas untuk pengembangan metrik swing berikutnya.';
        }

        foreach ($coverage as $phase => $coverageData) {
            if (! isset($sections[$phase]) || ! is_array($coverageData)) {
                continue;
            }

            $count = is_numeric($coverageData['count'] ?? null)
                ? (int) $coverageData['count']
                : null;
            $minimum = is_numeric($coverageData['min'] ?? null)
                ? (int) $coverageData['min']
                : null;

            if ($count !== null && $minimum !== null) {
                $sections[$phase]['findings'][] = "Cakupan frame: {$count}/{$minimum}";
                if ($count < $minimum) {
                    $sections[$phase]['status'] = 'missing';
                    $sections[$phase]['summary'] = 'Frame belum cukup untuk fase ini.';
                }
            }
        }

        foreach ($coverageMissing as $phase) {
            if (isset($sections[$phase])) {
                $sections[$phase]['status'] = 'missing';
                $sections[$phase]['summary'] = 'Cakupan frame belum memenuhi minimum.';
            }
        }

        foreach ($sections as &$section) {
            $section['findings'] = array_values(array_unique(array_filter($section['findings'])));
            $section['impact'] = array_values(array_unique(array_filter($section['impact'])));
            $section['actions'] = array_values(array_unique(array_filter($section['actions'])));
            $section['strength'] = array_values(array_unique(array_filter($section['strength'])));

            if ($section['summary'] === null) {
                $section['summary'] = $section['status'] === 'ok'
                    ? 'Tidak ada temuan prioritas pada data yang tersedia.'
                    : 'Data belum cukup untuk kesimpulan.';
            }
        }
        unset($section);

        return array_values($sections);
    }

    private function buildRecoveryPlan(array $formIssues, array $symptoms): array
    {
        $highIssueCodes = array_values(array_map(
            static fn (array $item): string => (string) $item['code'],
            array_filter(
                $formIssues,
                static fn (array $item): bool => ($item['severity'] ?? null) === 'high'
            )
        ));

        $hasBiomechPriority = $highIssueCodes !== [];
        $painPresent = $symptoms['pain_present'];
        $painScore = $symptoms['pain_score'];
        $redFlags = $symptoms['red_flags'];

        if ($redFlags !== [] || ($painPresent === true && is_numeric($painScore) && $painScore >= 7)) {
            return [[
                'code' => 'recovery_seek_assessment',
                'related_issue_codes' => $highIssueCodes,
                'status' => 'seek_assessment',
                'priority' => 'high',
                'priority_score' => 100,
                'title' => 'Tunda latihan yang memicu gejala dan lakukan evaluasi profesional',
                'message' => 'Data gejala menunjukkan kondisi yang tidak tepat ditangani hanya melalui rekomendasi video. Analisis biomekanika ini bukan diagnosis medis.',
                'load_guidance' => [
                    'Hentikan sesi yang memperburuk nyeri.',
                    'Jangan memaksakan perubahan teknik atau latihan plyometric.',
                ],
                'monitoring' => array_values(array_filter([
                    $symptoms['pain_location'] ? 'Lokasi nyeri: ' . $symptoms['pain_location'] : null,
                    is_numeric($painScore) ? 'Skala nyeri: ' . $painScore . '/10' : null,
                    ...$redFlags,
                ])),
                'stop_conditions' => [
                    'Nyeri meningkat atau tetap berat.',
                    'Sulit menumpu berat badan.',
                    'Pola berjalan berubah karena nyeri.',
                ],
                'reassess_after' => 'Setelah memperoleh evaluasi tenaga kesehatan atau profesional olahraga yang kompeten.',
                'severity' => 'high',
            ]];
        }

        if ($painPresent === true && is_numeric($painScore) && $painScore >= 4) {
            return [[
                'code' => 'recovery_modify_training',
                'related_issue_codes' => $highIssueCodes,
                'status' => 'modify_training',
                'priority' => 'high',
                'priority_score' => 92,
                'title' => 'Sesuaikan beban latihan berdasarkan respons nyeri',
                'message' => 'Kurangi atau ganti aktivitas yang meningkatkan gejala. Jangan menggunakan hasil video sebagai dasar tunggal untuk menentukan cedera.',
                'load_guidance' => [
                    'Pertahankan aktivitas hanya pada intensitas yang tidak memperburuk gejala.',
                    'Hindari peningkatan volume dan intensitas pada minggu yang sama.',
                    'Tunda drill eksplosif sampai aktivitas dasar dapat dilakukan tanpa peningkatan nyeri.',
                ],
                'monitoring' => [
                    'Catat nyeri saat latihan dan 24 jam setelahnya.',
                    'Pantau apakah langkah berubah untuk menghindari nyeri.',
                ],
                'stop_conditions' => [
                    'Nyeri tajam atau terus meningkat.',
                    'Muncul pembengkakan atau keterbatasan gerak.',
                    'Nyeri mengganggu aktivitas sehari-hari.',
                ],
                'reassess_after' => 'Evaluasi kembali setelah gejala stabil; cari bantuan profesional jika tidak membaik.',
                'severity' => 'high',
            ]];
        }

        if ($painPresent === true) {
            return [[
                'code' => 'recovery_monitor_symptoms',
                'related_issue_codes' => $highIssueCodes,
                'status' => 'monitor_symptoms',
                'priority' => 'medium',
                'priority_score' => 75,
                'title' => 'Lanjutkan secara konservatif sambil memantau gejala',
                'message' => 'Keluhan ringan tetap perlu dipantau. Hindari memaksakan perubahan teknik besar dalam satu sesi.',
                'load_guidance' => [
                    'Gunakan easy run atau aktivitas alternatif yang tidak meningkatkan gejala.',
                    'Pertahankan perubahan teknik dalam skala kecil.',
                ],
                'monitoring' => [
                    'Catat perubahan nyeri selama dan setelah latihan.',
                    'Bandingkan respons kanan dan kiri.',
                ],
                'stop_conditions' => [
                    'Nyeri meningkat dari sesi ke sesi.',
                    'Gerak menjadi kompensatif.',
                ],
                'reassess_after' => 'Setelah 2–4 sesi ringan atau lebih cepat jika gejala memburuk.',
                'severity' => 'medium',
            ]];
        }

        if ($painPresent === false) {
            return [[
                'code' => 'recovery_no_specific_restriction',
                'related_issue_codes' => $highIssueCodes,
                'status' => 'no_specific_recovery',
                'priority' => $hasBiomechPriority ? 'medium' : 'supporting',
                'priority_score' => $hasBiomechPriority ? 55 : 25,
                'title' => 'Tidak ada pembatasan pemulihan khusus berdasarkan data gejala',
                'message' => $hasBiomechPriority
                    ? 'Terdapat temuan teknik prioritas, tetapi pengguna melaporkan tidak ada nyeri. Fokuskan perubahan secara bertahap dan pantau respons tubuh.'
                    : 'Tidak ada gejala yang dilaporkan dan tidak ada temuan biomekanika prioritas tinggi pada data yang tersedia.',
                'load_guidance' => [
                    'Jangan menaikkan volume dan intensitas secara bersamaan.',
                    'Berikan minimal 48 jam antar-sesi penguatan utama.',
                ],
                'monitoring' => [
                    'Pantau nyeri baru, kekakuan, atau perubahan pola langkah.',
                ],
                'stop_conditions' => [
                    'Muncul nyeri tajam atau progresif.',
                    'Teknik memburuk karena kelelahan.',
                ],
                'reassess_after' => $hasBiomechPriority
                    ? 'Setelah 4–6 sesi easy run dan latihan koreksi.'
                    : 'Pada analisis berikutnya atau ketika pola latihan berubah.',
                'severity' => 'info',
            ]];
        }

        // Tidak ada data gejala: jangan meresepkan pengurangan latihan.
        return [[
            'code' => 'recovery_symptom_data_missing',
            'related_issue_codes' => $highIssueCodes,
            'status' => 'monitor',
            'priority' => $hasBiomechPriority ? 'medium' : 'supporting',
            'priority_score' => $hasBiomechPriority ? 60 : 30,
            'title' => 'Data gejala belum tersedia',
            'message' => 'Video biomekanika tidak cukup untuk menentukan kebutuhan istirahat atau pemulihan. Tambahkan informasi nyeri sebelum memberi rekomendasi beban yang spesifik.',
            'load_guidance' => [
                'Terapkan koreksi secara bertahap pada intensitas ringan.',
                'Hindari perubahan footstrike atau volume latihan secara mendadak.',
            ],
            'monitoring' => [
                'Catat apakah ada nyeri saat atau setelah berlari.',
                'Pantau kekakuan, pembengkakan, dan perubahan pola gerak.',
            ],
            'stop_conditions' => [
                'Nyeri tajam atau meningkat.',
                'Pola berjalan atau berlari berubah karena ketidaknyamanan.',
            ],
            'reassess_after' => $hasBiomechPriority
                ? 'Setelah 4–6 sesi koreksi atau setelah data gejala dilengkapi.'
                : 'Setelah data gejala tersedia.',
            'severity' => 'info',
        ]];
    }

    private function calculateFormScore(?array $biomech): ?int
    {
        if (! is_array($biomech)) {
            return null;
        }

        $hasUsableData = is_numeric($biomech['samples'] ?? null)
            || is_string($biomech['source'] ?? null)
            || collect($biomech)->except(['confidence', 'samples', 'source'])->filter(
                static fn (mixed $value): bool => is_numeric($value)
            )->isNotEmpty();

        if (! $hasUsableData) {
            return null;
        }

        $score = 100.0;

        // Landing-related penalties are capped to avoid double-counting highly
        // correlated indicators such as heel strike, overstride, and shin angle.
        $landingPenalty = 0.0;

        $overstride = $biomech['overstride_pct'] ?? null;
        if (is_numeric($overstride)) {
            $landingPenalty += match (true) {
                $overstride >= 60 => 25,
                $overstride >= 35 => 15,
                $overstride >= 20 => 7,
                default => 0,
            };
        }

        $heel = $biomech['heel_strike_pct'] ?? null;
        if (is_numeric($heel)) {
            $landingPenalty += match (true) {
                $heel >= 70 => 6,
                $heel >= 40 => 3,
                default => 0,
            };
        }

        $shin = $biomech['shin_angle_deg'] ?? null;
        if (is_numeric($shin)) {
            $landingPenalty += match (true) {
                $shin >= 25 => 8,
                $shin >= 18 => 5,
                default => 0,
            };
        }

        $score -= min(35, $landingPenalty);

        $knee = $biomech['knee_flex_deg'] ?? null;
        if (is_numeric($knee)) {
            $score -= match (true) {
                $knee < 15 => 12,
                $knee < 20 => 9,
                $knee < 30 => 4,
                default => 0,
            };
        }

        $trunk = $biomech['trunk_lean_deg'] ?? null;
        if (is_numeric($trunk)) {
            $score -= match (true) {
                $trunk > 25 || $trunk < -10 => 8,
                $trunk > 18 || $trunk < -5 => 5,
                default => 0,
            };
        }

        $armCross = $biomech['arm_cross_pct'] ?? null;
        if (is_numeric($armCross)) {
            $score -= match (true) {
                $armCross >= 70 => 10,
                $armCross >= 55 => 7,
                $armCross >= 45 => 3,
                default => 0,
            };
        }

        $cadence = $biomech['cadence_spm'] ?? null;
        if (is_numeric($cadence)) {
            $score -= match (true) {
                $cadence < 145 => 8,
                $cadence < 155 => 5,
                default => 0,
            };
        }

        $verticalOscillation = $biomech['vertical_oscillation'] ?? null;
        if (is_numeric($verticalOscillation)) {
            $score -= match (true) {
                $verticalOscillation >= 0.018 => 8,
                $verticalOscillation >= 0.012 => 5,
                default => 0,
            };
        }

        $asymmetry = $biomech['asymmetry'] ?? null;
        if (is_numeric($asymmetry)) {
            $score -= match (true) {
                $asymmetry >= 0.35 => 12,
                $asymmetry >= 0.25 => 8,
                $asymmetry >= 0.15 => 3,
                default => 0,
            };
        }

        $confidence = $biomech['confidence'] ?? null;
        if (is_numeric($confidence)) {
            $score -= match (true) {
                $confidence < 0.35 => 10,
                $confidence < 0.5 => 6,
                $confidence < 0.7 => 2,
                default => 0,
            };
        }

        return (int) round(max(0, min(100, $score)));
    }

    private function buildCoachMessage(
        array $meta,
        int $videoScore,
        ?int $formScore,
        array $formIssues,
        array $captureIssues,
        array $symptoms
    ): string {
        $runnerName = trim((string) (
            $meta['runner_name']
            ?? $meta['display']['runner_name']
            ?? 'Pelari'
        ));

        $priorityIssues = array_slice(
            array_values(array_filter(
                $formIssues,
                static fn (array $item): bool => in_array(
                    $item['severity'] ?? null,
                    ['high', 'medium'],
                    true
                )
            )),
            0,
            2
        );

        $parts = [];
        $parts[] = "{$runnerName}, analisis ini memisahkan kualitas rekaman dari kualitas teknik lari agar hasil lebih mudah ditindaklanjuti.";

        if ($formScore !== null) {
            $parts[] = "Skor form deterministik adalah {$formScore}/100, sedangkan kualitas data video {$videoScore}/100.";
        } else {
            $parts[] = "Kualitas data video adalah {$videoScore}/100, tetapi skor form belum dapat dihitung karena data biomekanika belum cukup.";
        }

        if ($priorityIssues !== []) {
            $titles = implode(' dan ', array_map(
                static fn (array $item): string => strtolower((string) $item['title']),
                $priorityIssues
            ));
            $parts[] = "Fokuskan evaluasi pada {$titles}. Terapkan satu atau dua perubahan terlebih dahulu agar respons tubuh dapat dinilai dengan jelas.";
        } else {
            $parts[] = 'Tidak ada masalah biomekanika prioritas tinggi pada data yang tersedia. Pertahankan pola yang stabil dan evaluasi ulang pada kondisi rekaman yang sama.';
        }

        if ($captureIssues !== []) {
            $parts[] = 'Perbaiki masalah kualitas rekaman sebelum membandingkan progres antar-trial.';
        }

        if (! $symptoms['available']) {
            $parts[] = 'Data nyeri belum tersedia, sehingga sistem tidak menetapkan pengurangan beban atau masa pemulihan tertentu.';
        } elseif ($symptoms['pain_present'] === true) {
            $parts[] = 'Karena terdapat keluhan, gunakan recovery plan sebagai panduan konservatif dan bukan diagnosis medis.';
        }

        return implode(' ', $parts);
    }

    private function getAiFeedback(
        array $meta,
        array $biomech,
        array $metrics,
        array $symptoms,
        array $deterministic
    ): ?array {
        $openAiService = app(OpenAiService::class);
        $runnerName = trim((string) (
            $meta['runner_name']
            ?? $meta['display']['runner_name']
            ?? 'Pelari'
        ));

        $payload = [
            'runner_name' => $runnerName,
            'biomechanics_metrics' => $biomech,
            'coverage' => $metrics['coverage'] ?? null,
            'coverage_missing' => $metrics['coverage_missing'] ?? null,
            'symptoms' => $symptoms,
            'deterministic_result' => $deterministic,
        ];

        $systemMessage = <<<TEXT
Anda adalah editor laporan biomekanika lari profesional untuk Ruang Lari.
Tugas Anda hanya memperjelas bahasa, menyusun prioritas, dan membuat rekomendasi lebih actionable berdasarkan data yang diberikan.

Aturan wajib:
1. Jangan mengubah observed_value, unit, reference, confidence, sample_count, metric_code, phase, severity, priority_score, atau code dari form_issues deterministik.
2. Jangan membuat metrik, diagnosis, gejala, risiko cedera, normal range, atau hubungan sebab-akibat yang tidak tersedia.
3. Jangan menyatakan heel strike sebagai masalah tunggal. Baca bersama overstride dan shin angle.
4. Jangan menyarankan pengurangan latihan, istirahat beberapa hari, atau evaluasi medis jika data gejala tidak mendukung.
5. Jika data gejala tidak tersedia, recovery_plan harus bersifat monitoring dan konservatif.
6. Maksimal 3 positives, 5 form_issues, 4 technique_suggestions, 4 strength_plan, dan 2 recovery_plan.
7. Gunakan Bahasa Indonesia profesional, lugas, tidak berlebihan, dan tidak menggunakan pujian kosong.
8. Sapa pelari dengan nama "{$runnerName}" di coach_message.
9. Jawab JSON murni tanpa markdown atau teks tambahan.
TEXT;

        $prompt = <<<'TEXT'
Perbaiki dan ringkas hasil deterministik berikut tanpa mengubah fakta kuantitatifnya.

Skema output wajib:
{
  "positives": [
    {
      "code": "string",
      "title": "string",
      "message": "string",
      "severity": "good"
    }
  ],
  "form_issues": [
    {
      "code": "harus sama dengan deterministic_result.form_issues",
      "phase": "landing|lever|push|pull|arm_swing|posture|null",
      "metric_code": "string|null",
      "title": "string",
      "message": "string",
      "observed_value": "harus dipertahankan",
      "unit": "harus dipertahankan",
      "reference": "harus dipertahankan",
      "confidence": "harus dipertahankan",
      "sample_count": "harus dipertahankan",
      "priority_score": "harus dipertahankan",
      "severity": "high|medium|low",
      "evidence": "harus dipertahankan jika tersedia"
    }
  ],
  "technique_suggestions": [
    {
      "code": "string",
      "category": "technique",
      "issue_codes": ["code form_issue terkait"],
      "phase": "landing|lever|push|pull|arm_swing|posture|null",
      "priority": "high|medium|supporting",
      "priority_score": 0,
      "title": "string",
      "message": "string",
      "cue": "string",
      "when_to_apply": "string",
      "dosage": "string|null",
      "severity": "high|medium|info"
    }
  ],
  "strength_plan": [
    {
      "code": "string",
      "issue_codes": ["code form_issue terkait"],
      "priority": "high|medium|supporting",
      "priority_score": 0,
      "category": "string",
      "title": "string",
      "goal": "string",
      "message": "string",
      "target_muscles": ["string"],
      "equipment": "string",
      "level": "beginner|intermediate|advanced",
      "dosage": {
        "sets": 3,
        "reps": "string",
        "tempo": "string|null",
        "rest_seconds": 60,
        "frequency": "string"
      },
      "coaching_cues": ["maksimal 4 string"],
      "common_mistakes": ["maksimal 3 string"],
      "regression": ["maksimal 2 string"],
      "progression": ["maksimal 2 string"],
      "stop_conditions": ["maksimal 3 string"],
      "reassess_after": "string",
      "severity": "info"
    }
  ],
  "recovery_plan": [
    {
      "code": "string",
      "related_issue_codes": ["code form_issue terkait"],
      "status": "no_specific_recovery|monitor|monitor_symptoms|modify_training|seek_assessment",
      "priority": "high|medium|supporting",
      "priority_score": 0,
      "title": "string",
      "message": "string",
      "load_guidance": ["string"],
      "monitoring": ["string"],
      "stop_conditions": ["string"],
      "reassess_after": "string",
      "severity": "high|medium|info"
    }
  ],
  "coach_message": "string",
  "form_report": [
    {
      "code": "landing|lever|push|pull|arm_swing|posture",
      "title": "string",
      "status": "ok|warn|issue|missing",
      "summary": "string",
      "findings": ["string"],
      "impact": ["string"],
      "actions": ["string"],
      "strength": ["string"]
    }
  ]
}

Data input:
TEXT;

        $prompt .= "\n" . json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        $responseContent = $openAiService->getAiResponse($prompt, $systemMessage);
        if (! is_string($responseContent) || trim($responseContent) === '') {
            return null;
        }

        $cleaned = trim($responseContent);
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $cleaned, $matches)) {
            $cleaned = trim($matches[1]);
        }

        $decoded = json_decode($cleaned, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function normalizePositiveItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = $this->cleanString($item['title'] ?? null);
            $message = $this->cleanString($item['message'] ?? null);
            if ($title === null || $message === null) {
                continue;
            }

            $normalized[] = [
                'code' => $this->cleanCode($item['code'] ?? "positive_{$index}"),
                'title' => $title,
                'message' => $message,
                'severity' => 'good',
            ];
        }

        return array_slice($this->uniqueByCode($normalized), 0, 3);
    }

    private function normalizeAiFormIssues(mixed $items, array $fallback): array
    {
        if (! is_array($items) || $items === []) {
            return [];
        }

        $fallbackByCode = [];
        foreach ($fallback as $item) {
            if (isset($item['code'])) {
                $fallbackByCode[(string) $item['code']] = $item;
            }
        }

        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $code = $this->cleanCode($item['code'] ?? null);
            if ($code === null || ! isset($fallbackByCode[$code])) {
                continue;
            }

            $base = $fallbackByCode[$code];
            $title = $this->cleanString($item['title'] ?? null) ?? $base['title'];
            $message = $this->cleanString($item['message'] ?? null) ?? $base['message'];

            // Preserve every quantitative and classification field from rules.
            $normalized[] = array_merge($base, [
                'title' => $title,
                'message' => $message,
            ]);
        }

        return array_slice($this->sortByPriority($normalized), 0, 5);
    }

    private function normalizeTechniqueSuggestions(mixed $items, array $formIssues): array
    {
        if (! is_array($items)) {
            return [];
        }

        $issueCodes = $this->issueCodeSet($formIssues);
        $normalized = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = $this->cleanString($item['title'] ?? null);
            $message = $this->cleanString($item['message'] ?? null);
            if ($title === null || $message === null) {
                continue;
            }

            $related = $this->validIssueCodes($item['issue_codes'] ?? [], $issueCodes);
            if ($related === []) {
                continue;
            }

            $phase = $this->normalizePhase($item['phase'] ?? null);
            $priority = $this->normalizePriority($item['priority'] ?? 'medium');

            $normalized[] = [
                'code' => $this->cleanCode($item['code'] ?? "technique_{$index}"),
                'category' => 'technique',
                'issue_codes' => $related,
                'phase' => $phase,
                'priority' => $priority,
                'priority_score' => $this->normalizePriorityScore(
                    $item['priority_score'] ?? null,
                    $priority
                ),
                'title' => $title,
                'message' => $message,
                'cue' => $this->cleanString($item['cue'] ?? null),
                'when_to_apply' => $this->cleanString($item['when_to_apply'] ?? null),
                'dosage' => $this->cleanString($item['dosage'] ?? null),
                'severity' => $this->priorityToSeverity($priority),
            ];
        }

        return array_slice($this->sortByPriority($this->uniqueByCode($normalized)), 0, 4);
    }

    private function normalizeStrengthPlan(mixed $items, array $formIssues): array
    {
        if (! is_array($items)) {
            return [];
        }

        $issueCodes = $this->issueCodeSet($formIssues);
        $normalized = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = $this->cleanString($item['title'] ?? null);
            $goal = $this->cleanString($item['goal'] ?? $item['message'] ?? null);
            if ($title === null || $goal === null) {
                continue;
            }

            $related = $this->validIssueCodes(
                $item['issue_codes'] ?? $item['related_issue_codes'] ?? [],
                $issueCodes
            );
            if ($related === []) {
                continue;
            }

            $priority = $this->normalizePriority($item['priority'] ?? 'medium');
            $dosageInput = is_array($item['dosage'] ?? null) ? $item['dosage'] : [];

            $sets = is_numeric($dosageInput['sets'] ?? null)
                ? max(1, min(6, (int) $dosageInput['sets']))
                : 3;
            $reps = $this->cleanString($dosageInput['reps'] ?? null) ?? '8–12 repetisi';
            $rest = is_numeric($dosageInput['rest_seconds'] ?? null)
                ? max(15, min(180, (int) $dosageInput['rest_seconds']))
                : 60;

            $normalized[] = [
                'code' => $this->cleanCode($item['code'] ?? "strength_{$index}"),
                'issue_codes' => $related,
                'priority' => $priority,
                'priority_score' => $this->normalizePriorityScore(
                    $item['priority_score'] ?? null,
                    $priority
                ),
                'category' => $this->cleanString($item['category'] ?? null) ?? 'general_strength',
                'title' => $title,
                'goal' => $goal,
                'message' => $this->cleanString($item['message'] ?? null) ?? $goal,
                'target_muscles' => $this->cleanStringArray($item['target_muscles'] ?? [], 5),
                'equipment' => $this->cleanString($item['equipment'] ?? null) ?? 'Bodyweight',
                'level' => in_array($item['level'] ?? null, ['beginner', 'intermediate', 'advanced'], true)
                    ? $item['level']
                    : 'beginner',
                'dosage' => [
                    'sets' => $sets,
                    'reps' => $reps,
                    'tempo' => $this->cleanString($dosageInput['tempo'] ?? null),
                    'rest_seconds' => $rest,
                    'frequency' => $this->cleanString($dosageInput['frequency'] ?? null)
                        ?? '2 kali per minggu',
                ],
                'coaching_cues' => $this->cleanStringArray($item['coaching_cues'] ?? [], 4),
                'common_mistakes' => $this->cleanStringArray($item['common_mistakes'] ?? [], 3),
                'regression' => $this->cleanStringArray($item['regression'] ?? [], 2),
                'progression' => $this->cleanStringArray($item['progression'] ?? [], 2),
                'stop_conditions' => $this->cleanStringArray(
                    $item['stop_conditions'] ?? [
                        'Hentikan bila muncul nyeri tajam atau teknik tidak dapat dipertahankan.',
                    ],
                    3
                ),
                'reassess_after' => $this->cleanString($item['reassess_after'] ?? null)
                    ?? 'Setelah 6–8 sesi latihan.',
                'severity' => 'info',
            ];
        }

        return array_slice($this->sortByPriority($this->uniqueByCode($normalized)), 0, 4);
    }

    private function normalizeRecoveryPlan(
        mixed $items,
        array $formIssues,
        array $symptoms
    ): array {
        if (! is_array($items)) {
            return [];
        }

        $issueCodes = $this->issueCodeSet($formIssues);
        $allowedStatuses = [
            'no_specific_recovery',
            'monitor',
            'monitor_symptoms',
            'modify_training',
            'seek_assessment',
        ];

        $normalized = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $status = in_array($item['status'] ?? null, $allowedStatuses, true)
                ? $item['status']
                : 'monitor';

            // Prevent AI from escalating recovery without symptom evidence.
            if (! $symptoms['available'] && in_array($status, ['modify_training', 'seek_assessment'], true)) {
                $status = 'monitor';
            }
            if ($symptoms['pain_present'] === false && $status === 'seek_assessment') {
                $status = 'no_specific_recovery';
            }

            $title = $this->cleanString($item['title'] ?? null);
            $message = $this->cleanString($item['message'] ?? null);
            if ($title === null || $message === null) {
                continue;
            }

            $priority = $this->normalizePriority($item['priority'] ?? 'medium');

            $normalized[] = [
                'code' => $this->cleanCode($item['code'] ?? "recovery_{$index}"),
                'related_issue_codes' => $this->validIssueCodes(
                    $item['related_issue_codes'] ?? [],
                    $issueCodes
                ),
                'status' => $status,
                'priority' => $priority,
                'priority_score' => $this->normalizePriorityScore(
                    $item['priority_score'] ?? null,
                    $priority
                ),
                'title' => $title,
                'message' => $message,
                'load_guidance' => $this->cleanStringArray($item['load_guidance'] ?? [], 5),
                'monitoring' => $this->cleanStringArray($item['monitoring'] ?? [], 5),
                'stop_conditions' => $this->cleanStringArray($item['stop_conditions'] ?? [], 4),
                'reassess_after' => $this->cleanString($item['reassess_after'] ?? null)
                    ?? 'Evaluasi kembali berdasarkan respons latihan.',
                'severity' => $status === 'seek_assessment'
                    ? 'high'
                    : ($status === 'modify_training' ? 'medium' : 'info'),
            ];
        }

        return array_slice($this->sortByPriority($this->uniqueByCode($normalized)), 0, 2);
    }

    private function normalizeFormReport(mixed $items, array $fallback): array
    {
        if (! is_array($items)) {
            return [];
        }

        $fallbackByCode = [];
        foreach ($fallback as $item) {
            if (is_array($item) && isset($item['code'])) {
                $fallbackByCode[(string) $item['code']] = $item;
            }
        }

        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $code = $this->normalizePhase($item['code'] ?? null);
            if ($code === null || ! isset($fallbackByCode[$code])) {
                continue;
            }

            $base = $fallbackByCode[$code];
            $status = in_array($item['status'] ?? null, ['ok', 'warn', 'issue', 'missing'], true)
                ? $item['status']
                : $base['status'];

            $normalized[] = [
                'code' => $code,
                'title' => self::FORM_PHASES[$code],
                'status' => $status,
                'summary' => $this->cleanString($item['summary'] ?? null) ?? $base['summary'],
                'findings' => $this->cleanStringArray($item['findings'] ?? $base['findings'], 6),
                'impact' => $this->cleanStringArray($item['impact'] ?? $base['impact'], 4),
                'actions' => $this->cleanStringArray($item['actions'] ?? $base['actions'], 4),
                'strength' => $this->cleanStringArray($item['strength'] ?? $base['strength'], 4),
            ];
        }

        if (count($normalized) !== count(self::FORM_PHASES)) {
            return [];
        }

        $indexed = [];
        foreach ($normalized as $item) {
            $indexed[$item['code']] = $item;
        }

        $ordered = [];
        foreach (array_keys(self::FORM_PHASES) as $code) {
            if (! isset($indexed[$code])) {
                return [];
            }
            $ordered[] = $indexed[$code];
        }

        return $ordered;
    }

    private function captureIssue(
        string $code,
        string $title,
        string $message,
        string $severity,
        mixed $observedValue = null,
        ?string $unit = null,
        ?string $reference = null
    ): array {
        return [
            'code' => $code,
            'category' => 'capture',
            'title' => $title,
            'message' => $message,
            'observed_value' => $observedValue,
            'unit' => $unit,
            'reference' => $reference,
            'priority_score' => $this->severityPriorityScore($severity),
            'severity' => $this->normalizeSeverity($severity, false),
        ];
    }

    private function captureSuggestion(
        string $code,
        array $issueCodes,
        string $priority,
        string $title,
        string $message
    ): array {
        $priority = $this->normalizePriority($priority);

        return [
            'code' => $code,
            'category' => 'capture',
            'issue_codes' => $issueCodes,
            'priority' => $priority,
            'priority_score' => $this->normalizePriorityScore(null, $priority),
            'title' => $title,
            'message' => $message,
            'severity' => $this->priorityToSeverity($priority),
        ];
    }

    private function positive(string $code, string $title, string $message): array
    {
        return [
            'code' => $code,
            'title' => $title,
            'message' => $message,
            'severity' => 'good',
        ];
    }

    private function formIssue(
        string $code,
        ?string $phase,
        ?string $metricCode,
        string $title,
        string $message,
        string $severity,
        mixed $value,
        ?string $unit,
        ?string $reference,
        ?float $confidence,
        ?int $samples,
        int $priorityScore
    ): array {
        return [
            'code' => $code,
            'phase' => $phase,
            'metric_code' => $metricCode,
            'title' => $title,
            'message' => $message,
            'observed_value' => $value,
            'unit' => $unit,
            'reference' => $reference,
            'confidence' => $confidence,
            'sample_count' => $samples,
            'priority_score' => max(0, min(100, $priorityScore)),
            'severity' => $this->normalizeSeverity($severity, false),
            'evidence' => $metricCode !== null && $value !== null
                ? [$metricCode => $value]
                : [],
        ];
    }

    private function techniqueSuggestion(
        string $code,
        array $issueCodes,
        ?string $phase,
        string $priority,
        string $title,
        string $message,
        ?string $cue,
        ?string $whenToApply,
        ?string $dosage
    ): array {
        $priority = $this->normalizePriority($priority);

        return [
            'code' => $code,
            'category' => 'technique',
            'issue_codes' => $issueCodes,
            'phase' => $phase,
            'priority' => $priority,
            'priority_score' => $this->normalizePriorityScore(null, $priority),
            'title' => $title,
            'message' => $message,
            'cue' => $cue,
            'when_to_apply' => $whenToApply,
            'dosage' => $dosage,
            'severity' => $this->priorityToSeverity($priority),
        ];
    }

    private function strengthExercise(
        string $code,
        array $issueCodes,
        string $priority,
        string $category,
        string $title,
        string $goal,
        array $targetMuscles,
        string $equipment,
        string $level,
        int $sets,
        string $reps,
        ?string $tempo,
        int $restSeconds,
        string $frequency,
        array $coachingCues,
        array $regression,
        array $progression
    ): array {
        $priority = $this->normalizePriority($priority);

        return [
            'code' => $code,
            'issue_codes' => $issueCodes,
            'priority' => $priority,
            'priority_score' => $this->normalizePriorityScore(null, $priority),
            'category' => $category,
            'title' => $title,
            'goal' => $goal,
            'message' => $goal,
            'target_muscles' => $targetMuscles,
            'equipment' => $equipment,
            'level' => $level,
            'dosage' => [
                'sets' => $sets,
                'reps' => $reps,
                'tempo' => $tempo,
                'rest_seconds' => $restSeconds,
                'frequency' => $frequency,
            ],
            'coaching_cues' => $coachingCues,
            'common_mistakes' => [],
            'regression' => $regression,
            'progression' => $progression,
            'stop_conditions' => [
                'Hentikan bila muncul nyeri tajam.',
                'Kurangi beban jika teknik tidak dapat dipertahankan.',
            ],
            'reassess_after' => 'Setelah 6–8 sesi latihan.',
            'severity' => 'info',
        ];
    }

    private function filterSuggestionsByIssues(array $items, array $formIssues): array
    {
        $issueCodes = $this->issueCodeSet($formIssues);

        return array_values(array_filter(
            $items,
            fn (array $item): bool => $this->validIssueCodes(
                $item['issue_codes'] ?? $item['related_issue_codes'] ?? [],
                $issueCodes
            ) !== []
        ));
    }

    private function issueCodeSet(array $formIssues): array
    {
        $set = [];
        foreach ($formIssues as $item) {
            if (is_array($item) && isset($item['code'])) {
                $set[(string) $item['code']] = true;
            }
        }

        return $set;
    }

    private function validIssueCodes(mixed $codes, array $issueCodeSet): array
    {
        if (! is_array($codes)) {
            return [];
        }

        $valid = [];
        foreach ($codes as $code) {
            if (! is_string($code)) {
                continue;
            }
            $code = trim($code);
            if ($code !== '' && isset($issueCodeSet[$code])) {
                $valid[] = $code;
            }
        }

        return array_values(array_unique($valid));
    }

    private function phaseFromIssueCodes(array $issueCodes, array $formIssues): ?string
    {
        foreach ($issueCodes as $issueCode) {
            foreach ($formIssues as $issue) {
                if (($issue['code'] ?? null) === $issueCode) {
                    return $issue['phase'] ?? null;
                }
            }
        }

        return null;
    }

    private function formatIssueEvidence(array $issue): string
    {
        $label = (string) ($issue['title'] ?? 'Temuan');
        $value = $issue['observed_value'] ?? null;
        $unit = $issue['unit'] ?? null;

        if ($value === null) {
            return $label;
        }

        return trim($label . ': ' . $value . ($unit ? " {$unit}" : ''));
    }

    private function raiseSectionStatus(array &$section, string $severity): void
    {
        $target = match ($severity) {
            'high' => 'issue',
            'medium' => 'warn',
            default => 'ok',
        };

        $rank = ['ok' => 0, 'warn' => 1, 'issue' => 2, 'missing' => 3];
        if (($rank[$target] ?? 0) > ($rank[$section['status']] ?? 0)) {
            $section['status'] = $target;
        }
    }

    private function sortByPriority(array $items): array
    {
        usort($items, static function (array $a, array $b): int {
            $aScore = is_numeric($a['priority_score'] ?? null)
                ? (float) $a['priority_score']
                : 0;
            $bScore = is_numeric($b['priority_score'] ?? null)
                ? (float) $b['priority_score']
                : 0;

            return $bScore <=> $aScore;
        });

        return array_values($items);
    }

    private function uniqueByCode(array $items): array
    {
        $unique = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $code = isset($item['code']) && is_string($item['code']) && trim($item['code']) !== ''
                ? trim($item['code'])
                : "item_{$index}";

            if (! isset($unique[$code])) {
                $item['code'] = $code;
                $unique[$code] = $item;
            }
        }

        return array_values($unique);
    }

    private function normalizeSeverity(mixed $severity, bool $allowGood): string
    {
        $allowed = $allowGood
            ? ['good', 'high', 'medium', 'low', 'info']
            : ['high', 'medium', 'low', 'info'];

        return is_string($severity) && in_array($severity, $allowed, true)
            ? $severity
            : 'info';
    }

    private function normalizePriority(mixed $priority): string
    {
        return is_string($priority) && in_array($priority, ['high', 'medium', 'supporting'], true)
            ? $priority
            : 'medium';
    }

    private function normalizePriorityScore(mixed $score, string $priority): int
    {
        if (is_numeric($score)) {
            return (int) max(0, min(100, round((float) $score)));
        }

        return match ($priority) {
            'high' => 90,
            'medium' => 65,
            default => 35,
        };
    }

    private function severityPriorityScore(string $severity): int
    {
        return match ($severity) {
            'high' => 90,
            'medium' => 65,
            'low' => 40,
            default => 20,
        };
    }

    private function priorityToSeverity(string $priority): string
    {
        return match ($priority) {
            'high' => 'high',
            'medium' => 'medium',
            default => 'info',
        };
    }

    private function normalizePhase(mixed $phase): ?string
    {
        return is_string($phase) && isset(self::FORM_PHASES[$phase])
            ? $phase
            : null;
    }

    private function cleanCode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_\-]+/', '_', $value) ?? '';
        $value = trim($value, '_-');

        return $value !== '' ? $value : null;
    }

    private function cleanString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        return $value !== '' ? $value : null;
    }

    private function cleanStringArray(mixed $items, int $limit): array
    {
        if (! is_array($items)) {
            return [];
        }

        $clean = [];
        foreach ($items as $item) {
            $value = $this->cleanString($item);
            if ($value !== null) {
                $clean[] = $value;
            }
        }

        return array_slice(array_values(array_unique($clean)), 0, max(0, $limit));
    }

    private function toBool(mixed $value): bool
    {
        return $this->nullableBool($value) === true;
    }

    private function nullableBool(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (bool) $value;
        }
        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['yes', 'true', '1', 'ya', 'iya'], true)) {
                return true;
            }
            if (in_array($normalized, ['no', 'false', '0', 'tidak'], true)) {
                return false;
            }
        }

        return null;
    }
}
