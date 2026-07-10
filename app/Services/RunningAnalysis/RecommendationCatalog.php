<?php

namespace App\Services\RunningAnalysis;

class RecommendationCatalog
{
    /**
     * Map findings to actionable recommendations.
     */
    public function generate(array $findings): array
    {
        $recommendations = [];

        foreach ($findings as $finding) {
            switch ($finding['type']) {
                case 'low_cadence':
                    $recommendations[] = [
                        'recommendation_code' => 'REC_INCREASE_CADENCE',
                        'type' => 'cue',
                        'title' => 'Tingkatkan Irama Langkah (Cadence)',
                        'description' => 'Gunakan metronome pada 165 spm selama pemanasan lari, lalu ikuti ritmenya. Jangan memaksa langkah lebih panjang, tapi percepat putaran kaki.',
                        'priority' => 1,
                    ];
                    break;

                case 'asymmetrical_gct':
                    $recommendations[] = [
                        'recommendation_code' => 'REC_STRENGTHEN_WEAK_LEG',
                        'type' => 'strength',
                        'title' => 'Latih Keseimbangan Otot Kaki',
                        'description' => 'Lakukan latihan Single Leg Deadlift dan Pistol Squat (assisted) 3 set x 10 repetisi pada kaki yang lebih lemah untuk memperbaiki ketidakseimbangan.',
                        'priority' => 1,
                    ];
                    break;

                case 'low_flight_time':
                    $recommendations[] = [
                        'recommendation_code' => 'REC_PLYOMETRICS',
                        'type' => 'drill',
                        'title' => 'Latih Daya Ledak (Power)',
                        'description' => 'Tambahkan sesi Plyometrics (Box Jumps, A-Skips, B-Skips) seminggu sekali untuk melatih gaya pegas tendon.',
                        'priority' => 2,
                    ];
                    break;

                case 'optimal_cadence':
                    $recommendations[] = [
                        'recommendation_code' => 'REC_MAINTAIN_CADENCE',
                        'type' => 'cue',
                        'title' => 'Pertahankan Konsistensi',
                        'description' => 'Pertahankan cadence Anda! Form lari Anda sudah efisien di bagian irama.',
                        'priority' => 3,
                    ];
                    break;
            }
        }

        return $recommendations;
    }
}
