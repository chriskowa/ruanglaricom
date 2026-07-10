<?php

namespace App\Services\RunningAnalysis;

class FindingRuleEngine
{
    /**
     * Evaluate metrics and generate diagnostic findings.
     */
    public function evaluate(array $metrics): array
    {
        $findings = [];

        // 1. Cadence Rule
        if ($metrics['cadence'] > 0 && $metrics['cadence'] < 160) {
            $findings[] = [
                'type' => 'low_cadence',
                'severity' => 'warning',
                'description' => 'Irama langkah (cadence) rendah (' . $metrics['cadence'] . ' spm). Berpotensi meningkatkan beban pada sendi lutut.',
            ];
        } elseif ($metrics['cadence'] >= 160 && $metrics['cadence'] <= 185) {
            $findings[] = [
                'type' => 'optimal_cadence',
                'severity' => 'good',
                'description' => 'Irama langkah optimal (' . $metrics['cadence'] . ' spm).',
            ];
        }

        // 2. Ground Contact Time Symmetry Rule
        if ($metrics['contact_time_ms_left'] > 0 && $metrics['contact_time_ms_right'] > 0) {
            $diff = abs($metrics['contact_time_ms_left'] - $metrics['contact_time_ms_right']);
            $avg = ($metrics['contact_time_ms_left'] + $metrics['contact_time_ms_right']) / 2;
            
            $asymmetryPercentage = ($diff / $avg) * 100;

            if ($asymmetryPercentage > 5.0) {
                $worseLeg = $metrics['contact_time_ms_left'] > $metrics['contact_time_ms_right'] ? 'kiri' : 'kanan';
                $findings[] = [
                    'type' => 'asymmetrical_gct',
                    'severity' => 'danger',
                    'description' => 'Ketidakseimbangan waktu kontak tanah (' . round($asymmetryPercentage, 1) . '%). Kaki ' . $worseLeg . ' menapak lebih lama.',
                ];
            }
        }

        // 3. Flight Time Rule
        if ($metrics['flight_time_ms'] < 100) {
             $findings[] = [
                'type' => 'low_flight_time',
                'severity' => 'warning',
                'description' => 'Fase melayang (flight time) sangat rendah (' . round($metrics['flight_time_ms']) . ' ms), gaya lari menyerupai jalan cepat (shuffling).',
            ];
        }

        return $findings;
    }
}
