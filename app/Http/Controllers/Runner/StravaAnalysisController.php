<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\StravaActivity;
use App\Services\DanielsRunningService;
use App\Services\OpenAiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StravaAnalysisController extends Controller
{
    protected $danielsService;
    protected $openAiService;

    public function __construct(DanielsRunningService $danielsService, OpenAiService $openAiService)
    {
        $this->danielsService = $danielsService;
        $this->openAiService = $openAiService;
    }

    /**
     * Check Strava connection status and data availability
     */
    public function status()
    {
        $user = auth()->user();

        $isConnected = !empty($user->strava_access_token) && !empty($user->strava_refresh_token);
        $lastActivity = StravaActivity::where('user_id', $user->id)
            ->whereNotNull('start_date')
            ->orderByDesc('start_date')
            ->first();

        $totalActivities = StravaActivity::where('user_id', $user->id)->count();

        return response()->json([
            'strava_connected' => $isConnected,
            'last_sync' => $lastActivity?->start_date?->toDateTimeString(),
            'total_activities' => $totalActivities,
            'connect_url' => route('runner.strava.connect'),
            'sync_url' => route('runner.strava.sync'),
        ]);
    }

    /**
     * Analyze user's Strava training data
     */
    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'range' => 'required|in:7,14,30,60,90,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = auth()->user();

        // Check Strava connection
        if (empty($user->strava_access_token)) {
            return response()->json([
                'success' => false,
                'needs_connect' => true,
                'message' => 'Akun Strava belum tersambung. Silakan hubungkan akun Strava Anda terlebih dahulu.',
                'connect_url' => route('runner.strava.connect'),
            ]);
        }

        // Determine date range
        $endDate = Carbon::now();
        $days = (int) $validated['range'];

        if ($validated['range'] === 'custom' && !empty($validated['start_date']) && !empty($validated['end_date'])) {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date'])->endOfDay();
        } else {
            $startDate = Carbon::now()->subDays($days ?: 14);
        }

        // Fetch user's running activities in date range
        $runTypes = ['run', 'virtualrun', 'trailrun', 'treadmill'];
        $activities = StravaActivity::query()
            ->where('user_id', $user->id)
            ->whereNotNull('start_date')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get();

        $runs = [];
        $totalDistanceM = 0;
        $totalMovingTimeS = 0;
        $totalElevationGain = 0;
        $hrReadings = [];

        foreach ($activities as $act) {
            $type = strtolower((string) ($act->type ?? ''));
            if (!in_array($type, $runTypes, true)) {
                continue;
            }

            $distKm = round(((float) $act->distance_m) / 1000, 2);
            $movingTimeMin = round(((float) $act->moving_time_s) / 60, 1);
            $paceMinPerKm = $distKm > 0 ? ($movingTimeMin / $distKm) : 0;

            $totalDistanceM += $act->distance_m;
            $totalMovingTimeS += $act->moving_time_s;
            $totalElevationGain += $act->total_elevation_gain ?: 0;

            // Extract heart rate from raw if available
            $raw = is_array($act->raw) ? $act->raw : [];
            $avgHr = data_get($raw, 'details.average_heartrate') ?? data_get($raw, 'average_heartrate');
            $maxHr = data_get($raw, 'details.max_heartrate') ?? data_get($raw, 'max_heartrate');
            $cadence = data_get($raw, 'details.average_cadence') ?? data_get($raw, 'average_cadence');
            $suffer = data_get($raw, 'details.suffer_score') ?? data_get($raw, 'suffer_score');

            if ($avgHr) $hrReadings[] = (float) $avgHr;

            $runs[] = [
                'id' => $act->id,
                'name' => $act->name,
                'distance_km' => $distKm,
                'moving_time_s' => $act->moving_time_s,
                'moving_time_min' => $movingTimeMin,
                'pace_min_km' => $paceMinPerKm,
                'pace_str' => $this->formatPace($paceMinPerKm),
                'date' => $act->local_start_date?->toDateString() ?: $act->start_date?->toDateString(),
                'day_of_week' => $act->local_start_date?->format('l') ?: $act->start_date?->format('l'),
                'avg_hr' => $avgHr,
                'max_hr' => $maxHr,
                'cadence' => $cadence,
                'suffer_score' => $suffer,
                'elevation_gain' => $act->total_elevation_gain,
            ];
        }

        $totalRunsCount = count($runs);
        $totalDistanceKm = round($totalDistanceM / 1000, 1);
        $totalMovingTimeMin = round($totalMovingTimeS / 60, 1);
        $overallAvgPaceMinKm = $totalDistanceKm > 0 ? ($totalMovingTimeMin / $totalDistanceKm) : 0;
        $avgHrOverall = !empty($hrReadings) ? round(array_sum($hrReadings) / count($hrReadings)) : null;

        if ($totalRunsCount === 0) {
            // Check if there's data but no runs
            $anyData = StravaActivity::where('user_id', $user->id)->exists();
            return response()->json([
                'success' => false,
                'needs_sync' => !$anyData,
                'message' => $anyData
                    ? 'Tidak ditemukan data lari Strava dalam rentang yang dipilih. Coba perluas rentang tanggal atau sync ulang data Strava.'
                    : 'Belum ada data lari tersimpan. Silakan klik "Sync Strava" terlebih dahulu untuk mengambil data aktivitas Anda.',
            ]);
        }

        // 1. Estimate VDOT from fastest runs
        $estimatedVdot = $this->estimateVdotFromRuns($runs);
        if ($estimatedVdot <= 0) {
            $estimatedVdot = $user->vdot ?: 35.0; // Fallback
        }

        // Calculate paces for classifications
        $vdotPaces = $this->danielsService->calculateTrainingPaces($estimatedVdot);

        // 2. Classify workouts based on scientific pace zones & duration
        $classified = [
            'easy_run' => [],
            'tempo' => [],
            'interval' => [],
            'long_run' => [],
        ];

        foreach ($runs as $run) {
            $dist = $run['distance_km'];
            $timeMin = $run['moving_time_min'];
            $pace = $run['pace_min_km'];

            // 1. Long Run check (either >= 12km or >= 80 min at easy pace)
            if ($dist >= 12.0 || $timeMin >= 80) {
                $classified['long_run'][] = $run;
            }
            // 2. Interval/Speed check (near or faster than interval pace)
            elseif ($pace > 0 && $pace <= ($vdotPaces['I'] + 0.15)) {
                $classified['interval'][] = $run;
            }
            // 3. Tempo check (near threshold pace)
            elseif ($pace > 0 && $pace <= ($vdotPaces['T'] + 0.15)) {
                $classified['tempo'][] = $run;
            }
            // 4. Easy Run check
            else {
                $classified['easy_run'][] = $run;
            }
        }

        $easyCount = count($classified['easy_run']);
        $tempoCount = count($classified['tempo']);
        $intervalCount = count($classified['interval']);
        $longRunCount = count($classified['long_run']);

        // 3. Calculate advanced metrics
        $qualityRuns = $tempoCount + $intervalCount;
        $easyPct = $totalRunsCount > 0 ? round(($easyCount + $longRunCount) / $totalRunsCount * 100) : 0;
        $hardPct = $totalRunsCount > 0 ? round($qualityRuns / $totalRunsCount * 100) : 0;
        $weeksDuration = max(1, $startDate->diffInWeeks($endDate));
        $weeklyMileageEst = round($totalDistanceKm / $weeksDuration, 1);
        $trainingFreqEst = round($totalRunsCount / $weeksDuration);
        $trainingFreqEst = max(2, min(7, $trainingFreqEst));

        // Consistency check: active days per week
        $activeDays = collect($runs)->pluck('date')->unique()->count();
        $consistencyPct = round($activeDays / max(1, $startDate->diffInDays($endDate)) * 100);

        // Monotony check: variation in daily distances
        $distances = collect($runs)->pluck('distance_km')->toArray();
        $avgDist = count($distances) > 0 ? array_sum($distances) / count($distances) : 0;
        $variance = count($distances) > 1
            ? array_sum(array_map(fn($d) => pow($d - $avgDist, 2), $distances)) / (count($distances) - 1)
            : 0;
        $stdDev = sqrt($variance);
        $monotony = $avgDist > 0 ? round($stdDev / $avgDist, 2) : 0;

        // Build enriched metrics for AI
        $enrichedMetrics = [
            'period' => $startDate->toDateString() . ' → ' . $endDate->toDateString(),
            'days_analyzed' => $startDate->diffInDays($endDate),
            'total_runs' => $totalRunsCount,
            'total_km' => $totalDistanceKm,
            'total_time_min' => $totalMovingTimeMin,
            'avg_pace' => $this->formatPace($overallAvgPaceMinKm) . '/km',
            'total_elevation_gain_m' => round($totalElevationGain),
            'avg_hr' => $avgHrOverall,
            'distribution' => [
                'easy_run' => $easyCount,
                'tempo' => $tempoCount,
                'interval' => $intervalCount,
                'long_run' => $longRunCount,
            ],
            'polarized_ratio' => [
                'easy_pct' => $easyPct,
                'hard_pct' => $hardPct,
                'target' => '80/20',
            ],
            'weekly_avg' => [
                'km' => $weeklyMileageEst,
                'sessions' => $trainingFreqEst,
            ],
            'consistency_pct' => $consistencyPct,
            'distance_variation_coeff' => $monotony,
            'estimated_vdot' => round($estimatedVdot, 1),
            'vdot_training_paces' => [
                'Easy' => $this->formatPace($vdotPaces['E']) . '/km',
                'Tempo' => $this->formatPace($vdotPaces['T']) . '/km',
                'Interval' => $this->formatPace($vdotPaces['I']) . '/km',
            ],
        ];

        // 4. AI Coach Analysis
        $aiAnalysis = $this->getAiAnalysis($enrichedMetrics);

        return response()->json([
            'success' => true,
            'range_details' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'days' => $startDate->diffInDays($endDate),
            ],
            'statistics' => [
                'total_runs' => $totalRunsCount,
                'total_distance_km' => $totalDistanceKm,
                'total_time_min' => $totalMovingTimeMin,
                'avg_pace_str' => $this->formatPace($overallAvgPaceMinKm),
                'total_elevation_m' => round($totalElevationGain),
                'avg_hr' => $avgHrOverall,
            ],
            'estimated_vdot' => round($estimatedVdot, 1),
            'vdot_paces' => [
                'E' => $this->formatPace($vdotPaces['E']) . '/km',
                'T' => $this->formatPace($vdotPaces['T']) . '/km',
                'I' => $this->formatPace($vdotPaces['I']) . '/km',
            ],
            'classification' => [
                'easy_run_count' => $easyCount,
                'tempo_count' => $tempoCount,
                'interval_count' => $intervalCount,
                'long_run_count' => $longRunCount,
            ],
            'polarized_ratio' => [
                'easy_pct' => $easyPct,
                'hard_pct' => $hardPct,
            ],
            'ai_insights' => $aiAnalysis,
            'autofill_params' => [
                'vdot' => round($estimatedVdot, 1),
                'weekly_mileage' => $weeklyMileageEst,
                'training_frequency' => $trainingFreqEst,
            ],
        ]);
    }

    /**
     * Estimate VDOT based on closest standard distances
     */
    private function estimateVdotFromRuns(array $runs): float
    {
        $maxVdot = 0.0;
        foreach ($runs as $run) {
            $dist = $run['distance_km'];
            $time = $run['moving_time_s'];

            if ($dist >= 4.5 && $dist < 7.5) {
                $scaledTime = $time * (5.0 / $dist);
                $timeStr = $this->formatSecondsToTime($scaledTime);
                $v = $this->danielsService->calculateVDOT($timeStr, '5k');
                if ($v > $maxVdot) $maxVdot = $v;
            } elseif ($dist >= 9.0 && $dist < 13.0) {
                $scaledTime = $time * (10.0 / $dist);
                $timeStr = $this->formatSecondsToTime($scaledTime);
                $v = $this->danielsService->calculateVDOT($timeStr, '10k');
                if ($v > $maxVdot) $maxVdot = $v;
            } elseif ($dist >= 19.0 && $dist < 24.0) {
                $scaledTime = $time * (21.0975 / $dist);
                $timeStr = $this->formatSecondsToTime($scaledTime);
                $v = $this->danielsService->calculateVDOT($timeStr, '21k');
                if ($v > $maxVdot) $maxVdot = $v;
            } elseif ($dist >= 38.0) {
                $scaledTime = $time * (42.195 / $dist);
                $timeStr = $this->formatSecondsToTime($scaledTime);
                $v = $this->danielsService->calculateVDOT($timeStr, '42k');
                if ($v > $maxVdot) $maxVdot = $v;
            }
        }
        return $maxVdot;
    }

    private function formatSecondsToTime(float $seconds): string
    {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = round($seconds % 60);
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    private function formatPace(float $minutesPerKm): string
    {
        if ($minutesPerKm <= 0) return '0:00';
        $minutes = floor($minutesPerKm);
        $seconds = round(($minutesPerKm - $minutes) * 60);
        return sprintf('%d:%02d', $minutes, $seconds);
    }    private function getAiAnalysis(array $metrics): string
    {
        $hasKey = (bool) (config('services.openai.api_key') ?: env('OPENAI_API_KEY'));
        if (!$hasKey) {
            return $this->getFallbackAnalysis($metrics);
        }

        $system = <<<SYSTEM
Anda adalah Coach AI Ruang Lari — pakar sport science kelas dunia yang bertugas menganalisis data latihan secara kritis, objektif, dan berbasis data. Pengguna adalah pelari yang membutuhkan evaluasi jujur, koreksi teknis, dan arahan latihan yang jelas demi peningkatan performa, bukan sekadar pujian kosong.

Pegang teguh prinsip sport science berikut:
1. **Polarized Training (80/20 Rule)** — ~80% volume latihan wajib berada di Zona Easy (aerobic base), ~20% di Zona Quality (Tempo/Interval). Sorot jika pelari terlalu sering berlari di "grey zone/junk miles" (pace nanggung yang melelahkan namun kurang memberikan adaptasi fisiologis optimal).
2. **Jack Daniels' VDOT System** — Evaluasi kesesuaian pace latihan dengan target zona pace VDOT pelari.
3. **Progressive Overload & 10% Rule** — Kenaikan volume mingguan tidak boleh melebihi 10%.
4. **Variasi & Spesifikasi Sesi** — Keseimbangan antara Easy Run, Tempo, Interval, dan Long Run.
5. **Faktor Suhu Tropis** — Sadari penyesuaian pace di Indonesia (panas/lembap) rata-rata 10-25 detik/km lebih lambat.

Gaya komunikasi Anda:
- Objektif, lugas, profesional, dan berbasis data ilmiah.
- Hindari nada memuji berlebihan. Sampaikan kekuatan seperlunya, namun prioritaskan ulasan celah latihan dan koreksi konkret.
- JANGAN gunakan banyak emoji. Gunakan maksimal satu emoji per judul bagian saja, dan JANGAN gunakan emoji sama sekali di dalam paragraf isi agar ulasan terlihat bersih dan profesional.
SYSTEM;

        $metricsJson = json_encode($metrics, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
Analisis data latihan lari pelari berikut dan berikan ulasan yang terstruktur menggunakan format Markdown lengkap (heading, paragraf, bullet points, dan teks tebal).

DATA LATIHAN:
{$metricsJson}

FORMAT ULASAN YANG HARUS DIBERIKAN:

### 📊 Ringkasan Performa
Tulis paragraf singkat (2-3 kalimat) mengenai status latihan pelari berdasarkan data secara objektif. Sebutkan nilai **VDOT** pelari serta tingkat kemampuannya saat ini.

### ⚖️ Analisis Distribusi Intensitas (80/20)
Tulis paragraf evaluasi yang membandingkan persentase **Easy Run** vs **Quality Run** dari volume latihan aktual pelari. Analisis apakah distribusi tersebut sudah optimal. Sebutkan secara spesifik jika ada indikasi "junk miles" (berlari di intensitas tanggung yang kurang bermanfaat).

### 🎯 Kekuatan Latihan
Sajikan dalam bentuk bullet points (-):
- Poin kekuatan pertama (misal: konsistensi jadwal mingguan atau porsi long run yang stabil).
- Poin kekuatan kedua (jika ada).
*Gunakan cetak tebal pada metrik kunci.*

### ⚠️ Celah & Risiko Latihan
Sajikan dalam bentuk bullet points (-):
- Risiko pertama yang terdeteksi dari data (misal: peningkatan volume lari terlalu mendadak atau kurangnya variasi sesi latihan).
- Risiko kedua yang perlu diwaspadai pelari.
*Gunakan cetak tebal pada indikator risiko.*

### 🏃‍♂️ Rekomendasi Latihan Kuantitatif
Berikan 3 langkah taktis spesifik untuk perbaikan program latihan ke depan dengan bullet points (-):
- Rekomendasi 1: Langkah perbaikan teknis dengan target kuantitatif (contoh: **Lakukan 1 sesi Tempo 20-30 menit di target pace {T_pace}/km** pada hari Rabu).
- Rekomendasi 2: Penyesuaian volume atau intensitas mingguan dengan angka konkret.
- Rekomendasi 3: Pengaturan hari istirahat atau recovery run.

### 🚀 Langkah Berikutnya di Ruang Lari
Tulis paragraf penutup singkat yang mengarahkan pelari untuk mengambil tindakan berikut:
- Menggunakan fitur **Generate Program Lari AI** dengan target jarak lomba yang paling realistis.
- Melakukan **Konsultasi Coach Personal** jika membutuhkan program latihan yang disesuaikan secara personal oleh pelatih.

Aturan Tambahan:
- Gunakan format heading Markdown (`###`) untuk setiap judul bagian.
- Gunakan bullet points (`-`) untuk bagian Kekuatan, Celah/Risiko, dan Rekomendasi agar mudah dibaca oleh pelari.
- Tebalkan kata-kata kunci penting seperti angka jarak, pace, VDOT, atau nama hari agar visualisasinya menarik dan interaktif bagi pelari.
- Gunakan Bahasa Indonesia yang lugas, profesional, informatif, dan tidak bertele-tele. Batasi panjang total ulasan maksimal 450 kata.
PROMPT;

        try {
            return $this->openAiService->getAiResponseOrThrow($prompt, $system);
        } catch (\Throwable $e) {
            Log::error('AI Strava analysis error: ' . $e->getMessage());
            return $this->getFallbackAnalysis($metrics);
        }
    }

    /**
     * Generate fallback analysis without AI
     */
    private function getFallbackAnalysis(array $metrics): string
    {
        $totalRuns = $metrics['total_runs'] ?? 0;
        $totalKm = $metrics['total_km'] ?? 0;
        $easyPct = $metrics['polarized_ratio']['easy_pct'] ?? 0;
        $hardPct = $metrics['polarized_ratio']['hard_pct'] ?? 0;
        $vdot = $metrics['estimated_vdot'] ?? 0;
        $weeklyKm = $metrics['weekly_avg']['km'] ?? 0;

        $lines = [];
        $lines[] = "**Ringkasan Performa**";
        $lines[] = "Total {$totalRuns} sesi lari dengan jarak {$totalKm} km. Estimasi VDOT Anda: {$vdot}.";
        $lines[] = "";
        $lines[] = "**Distribusi Intensitas**";
        $lines[] = "Easy/Long: {$easyPct}% | Quality (Tempo+Interval): {$hardPct}%";

        if ($easyPct >= 70 && $easyPct <= 85) {
            $lines[] = "Distribusi intensitas latihan Anda sudah ideal dan mendekati polarized model 80/20.";
        } elseif ($hardPct > 30) {
            $lines[] = "Porsi Quality Run terlalu tinggi ({$hardPct}%). Sebaiknya tingkatkan porsi lari lambat (Easy Run) untuk meminimalkan risiko cedera dan overtraining.";
        } else {
            $lines[] = "Disarankan untuk menjadwalkan setidaknya 1 sesi Quality Run (Tempo/Interval) per minggu guna merangsang peningkatan kapasitas VDOT Anda.";
        }

        $lines[] = "";
        $lines[] = "**Rekomendasi**";
        $lines[] = "Gunakan fitur **Generate Program Lari AI** di Ruang Lari untuk membuat program latihan terstruktur berdasarkan nilai VDOT {$vdot} Anda, atau ajukan **Konsultasi Coach Personal** untuk evaluasi mendalam.";

        return implode("\n", $lines);
    }
}
