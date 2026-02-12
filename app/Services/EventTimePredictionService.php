<?php

namespace App\Services;

use App\Models\MasterGpx;
use App\Models\RaceCategory;

class EventTimePredictionService
{
    public function __construct(
        private readonly DanielsRunningService $daniels
    ) {}

    public function predict(RaceCategory $category, string $weather, int $pbSeconds, \DateTimeInterface $pbDate): array
    {
        $distanceKey = $this->distanceKeyFromKm((float) ($category->distance_km ?? 0));
        $pbTime = $this->secondsToTimeString($pbSeconds);

        $vdot = $this->daniels->calculateVDOT($pbTime, $distanceKey);

        $gpx = $category->masterGpx;
        $gain = $gpx ? (int) ($gpx->elevation_gain_m ?? 0) : 0;
        $distanceKm = $gpx ? (float) ($gpx->distance_km ?? 0) : (float) ($category->distance_km ?? 0);
        $gainPerKm = ($distanceKm > 0) ? ($gain / $distanceKm) : 0.0;

        $weatherPenalty = $this->weatherPenalty($weather);
        $elevationPenalty = min(0.12, max(0.0, $gainPerKm * 0.0015));
        $terrainPenalty = $gainPerKm > 35 ? 0.02 : ($gainPerKm > 20 ? 0.01 : 0.0);

        $totalPenalty = $weatherPenalty + $elevationPenalty + $terrainPenalty;

        $realistic = (int) round($pbSeconds * (1 + $totalPenalty));
        $optimistic = (int) round($pbSeconds * (1 + ($totalPenalty * 0.5)));
        $pessimistic = (int) round($pbSeconds * (1 + ($totalPenalty * 1.2)));

        $confidence = $this->confidenceScore($category, $pbDate, $gpx);
        $strategy = $this->strategyText($gainPerKm, $weather);

        return [
            'vdot' => $vdot,
            'distance_key' => $distanceKey,
            'pb_time' => $pbTime,
            'penalties' => [
                'weather' => $weatherPenalty,
                'elevation' => $elevationPenalty,
                'terrain' => $terrainPenalty,
                'total' => $totalPenalty,
            ],
            'prediction' => [
                'optimistic' => $this->secondsToTimeString($optimistic),
                'realistic' => $this->secondsToTimeString($realistic),
                'pessimistic' => $this->secondsToTimeString($pessimistic),
            ],
            'confidence' => $confidence,
            'route' => [
                'distance_km' => $distanceKm ?: null,
                'elevation_gain_m' => $gpx ? ($gpx->elevation_gain_m ?? null) : null,
                'elevation_loss_m' => $gpx ? ($gpx->elevation_loss_m ?? null) : null,
                'gain_per_km' => $distanceKm > 0 ? round($gainPerKm, 2) : null,
                'master_gpx_id' => $gpx?->id,
            ],
            'strategy' => $strategy,
        ];
    }

    private function distanceKeyFromKm(float $km): string
    {
        if (abs($km - 5.0) < 0.6) {
            return '5k';
        }
        if (abs($km - 10.0) < 1.0) {
            return '10k';
        }
        if (abs($km - 21.1) < 1.0 || abs($km - 21.0) < 1.0) {
            return '21k';
        }
        if (abs($km - 42.2) < 2.0 || abs($km - 42.0) < 2.0) {
            return '42k';
        }

        return '10k';
    }

    private function secondsToTimeString(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    private function weatherPenalty(string $weather): float
    {
        return match ($weather) {
            'panas' => 0.03,
            'dingin' => -0.01,
            'hujan' => 0.02,
            'gerimis' => 0.01,
            default => 0.0,
        };
    }

    private function confidenceScore(RaceCategory $category, \DateTimeInterface $pbDate, ?MasterGpx $gpx): float
    {
        $days = now()->diffInDays($pbDate);
        $score = 0.7;
        if ($days <= 30) {
            $score += 0.1;
        }
        if ($days >= 61) {
            $score -= 0.1;
        }
        if (! $gpx) {
            $score -= 0.2;
        }

        return max(0.2, min(0.9, round($score, 2)));
    }

    private function strategyText(float $gainPerKm, string $weather): string
    {
        $base = 'Mulai sedikit konservatif, jaga effort stabil, dan hindari sprint di awal.';
        if ($gainPerKm > 35) {
            $base = 'Fokus pada effort di tanjakan, pendekkan langkah, dan manfaatkan turunan untuk recovery tanpa overstride.';
        } elseif ($gainPerKm > 20) {
            $base = 'Jaga effort di tanjakan ringan, dan pertahankan ritme di segmen datar.';
        }

        $wx = match ($weather) {
            'panas' => ' Prioritaskan hidrasi, kurangi agresivitas pace di awal, dan gunakan strategi cooling.',
            'hujan' => ' Waspadai licin, pilih sepatu yang grip, dan stabilkan cadence.',
            'gerimis' => ' Jaga footing dan tetap konsisten, jangan terlalu agresif di tikungan/turunan.',
            'dingin' => ' Warm-up lebih lama, dan mulai bertahap sampai tubuh panas.',
            default => '',
        };

        return trim($base.$wx);
    }
}
