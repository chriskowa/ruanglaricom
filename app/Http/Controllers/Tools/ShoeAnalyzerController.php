<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShoeAnalyzerController extends Controller
{
    public function analyze(Request $request)
    {
        $data = $request->validate([
            'shoe_image' => ['required', 'file', 'image', 'max:10240'],
            'estimated_mileage' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'wear_pattern' => ['nullable', 'string', 'in:heel_lateral,heel_medial,forefoot_lateral,forefoot_medial,midfoot,even'],
            'wear_symmetry' => ['nullable', 'string', 'in:symmetrical,asymmetrical'],
            'pain_zones' => ['nullable', 'string', 'max:255'],
        ]);

        $targetLifeKm = 700;

        $mileage = isset($data['estimated_mileage']) ? (float) $data['estimated_mileage'] : null;
        $wearPct = null;
        $remainingKm = null;
        $healthStatus = null;

        if ($mileage !== null) {
            $wearPct = ($mileage / $targetLifeKm) * 100;
            if ($wearPct < 0) {
                $wearPct = 0;
            }
            if ($wearPct > 140) {
                $wearPct = 140;
            }
            $remainingKm = $targetLifeKm - $mileage;
            if ($remainingKm < 0) {
                $remainingKm = 0;
            }
        }

        $wearPctInt = $wearPct !== null ? (int) round(max(0, min(100, $wearPct))) : null;

        if ($wearPctInt === null) {
            $healthStatus = null;
        } elseif ($wearPctInt <= 50) {
            $healthStatus = 'Healthy';
        } elseif ($wearPctInt <= 80) {
            $healthStatus = 'Warning';
        } else {
            $healthStatus = 'Critical';
        }

        $wearPattern = $data['wear_pattern'] ?? null;
        $wearSymmetry = $data['wear_symmetry'] ?? null;
        $painZonesRaw = $data['pain_zones'] ?? '';

        $painZones = collect(explode(',', $painZonesRaw))
            ->map(function ($v) {
                return strtolower(trim($v));
            })
            ->filter()
            ->values();

        $biomechType = 'Neutral';

        if ($wearPattern === 'heel_lateral' || $wearPattern === 'forefoot_lateral') {
            $biomechType = 'Supination / Underpronation';
        } elseif ($wearPattern === 'heel_medial' || $wearPattern === 'forefoot_medial') {
            $biomechType = 'Overpronation';
        } elseif ($wearPattern === 'midfoot') {
            $biomechType = 'Midfoot striker (cenderung netral)';
        } elseif ($wearPattern === 'even') {
            $biomechType = 'Neutral';
        }

        if ($wearSymmetry === 'asymmetrical' && $biomechType === 'Neutral') {
            $biomechType = 'Mild asymmetry (mendekati overpronation/supination)';
        }

        $injuryLevel = 'Low';
        if ($wearPctInt !== null) {
            if ($wearPctInt > 80) {
                $injuryLevel = 'High';
            } elseif ($wearPctInt > 60) {
                $injuryLevel = 'Medium';
            }
        }

        if ($biomechType !== 'Neutral' && $wearPctInt !== null && $wearPctInt >= 50 && $injuryLevel === 'Low') {
            $injuryLevel = 'Medium';
        }

        if ($painZones->count() >= 2 && $injuryLevel !== 'High') {
            $injuryLevel = 'High';
        } elseif ($painZones->count() === 1 && $injuryLevel === 'Low') {
            $injuryLevel = 'Medium';
        }

        $injuries = [];
        $warning = null;

        if (str_contains(strtolower($biomechType), 'overpronation')) {
            $injuries[] = 'Nyeri lutut bagian dalam (medial knee pain)';
            $injuries[] = 'Plantar fasciitis atau nyeri telapak kaki';
            $injuries[] = 'Shin splints bagian dalam';
            if ($injuryLevel === 'High') {
                $warning = 'Pola outsole menunjukkan beban berat di sisi dalam kaki. Dikombinasikan dengan usia sepatu, ini meningkatkan risiko IT Band Syndrome, nyeri lutut kronis, dan plantar fasciitis.';
            } elseif ($injuryLevel === 'Medium') {
                $warning = 'Ada kecenderungan overpronation. Jika intensitas latihan naik tanpa penguatan pinggul dan kaki, risiko nyeri lutut dan telapak kaki akan meningkat.';
            } else {
                $warning = 'Ada sedikit kecenderungan overpronation, tetapi masih dalam batas aman. Tetap perhatikan penguatan pinggul dan kaki agar tidak makin berat.';
            }
        } elseif (str_contains(strtolower($biomechType), 'supination')) {
            $injuries[] = 'Shin splints bagian luar';
            $injuries[] = 'Nyeri pergelangan atau mudah keseleo';
            $injuries[] = 'Stress fracture tulang kering pada volume tinggi';
            if ($injuryLevel === 'High') {
                $warning = 'Beban banyak jatuh di sisi luar kaki dan outsole sudah sangat aus. Ini kombinasi berisiko tinggi untuk shin splints dan stress fracture, terutama jika volume lari tinggi.';
            } elseif ($injuryLevel === 'Medium') {
                $warning = 'Ada kecenderungan supination. Jika dipaksa lari jauh dengan sepatu yang mulai aus, betis dan tulang kering akan bekerja ekstra keras.';
            } else {
                $warning = 'Supination ringan terdeteksi. Pastikan sepatu cukup empuk dan mobilitas pergelangan kaki terjaga.';
            }
        } else {
            if ($injuryLevel === 'High') {
                $injuries[] = 'General overuse injuries';
                $injuries[] = 'Nyeri lutut setelah lari jauh';
                $injuries[] = 'Kekakuan pergelangan dan betis setelah tempo/interval';
                $warning = 'Pola keausan relatif netral, tetapi usia sepatu mendekati atau melewati batas aman. Foam yang “mati” membuat benturan langsung naik ke sendi.';
            } elseif ($injuryLevel === 'Medium') {
                $injuries[] = 'Nyeri lutut ringan';
                $injuries[] = 'Fatigue di betis dan pergelangan kaki';
                $warning = 'Outsole menunjukkan keausan moderat. Masih aman untuk easy run, tetapi kurang ideal untuk long run dan latihan cepat.';
            } else {
                $warning = 'Pola outsole terlihat cukup seimbang. Risiko cedera lebih banyak ditentukan oleh progres beban latihan dan kebiasaan strength training.';
            }
        }

        if ($painZones->contains('knee') && !in_array('Nyeri lutut setelah lari jauh', $injuries, true)) {
            $injuries[] = 'Nyeri lutut setelah lari jauh';
        }
        if ($painZones->contains('shin') && !in_array('Shin splints (nyeri tulang kering)', $injuries, true)) {
            $injuries[] = 'Shin splints (nyeri tulang kering)';
        }
        if ($painZones->contains('it_band') && !in_array('IT Band Syndrome (nyeri sisi luar lutut/paha)', $injuries, true)) {
            $injuries[] = 'IT Band Syndrome (nyeri sisi luar lutut/paha)';
        }
        if ($painZones->contains('achilles') && !in_array('Tendinopati Achilles', $injuries, true)) {
            $injuries[] = 'Tendinopati Achilles';
        }
        if ($painZones->contains('plantar') && !in_array('Plantar fasciitis', $injuries, true)) {
            $injuries[] = 'Plantar fasciitis';
        }
        if ($painZones->contains('hip') && !in_array('Nyeri pinggul terkait kelemahan gluteus medius', $injuries, true)) {
            $injuries[] = 'Nyeri pinggul terkait kelemahan gluteus medius';
        }

        $gearType = 'Neutral / Daily Trainer';
        $gearReason = 'Pola pendaratan kamu masih cocok dengan sepatu netral serbaguna dengan cushioning moderat.';
        $gearExamples = [
            'Nike Pegasus / Structure',
            'Adidas SL / Solar Control',
            'ASICS Cumulus / GT Series',
        ];

        if (str_contains(strtolower($biomechType), 'overpronation')) {
            $gearType = 'Stability / Support';
            $gearReason = 'Kecenderungan overpronation butuh sepatu dengan dukungan sisi dalam (medial posting) agar lutut tidak terus kolaps ke dalam di akhir long run.';
            $gearExamples = [
                'ASICS GT Series',
                'Brooks Adrenaline',
                'Nike Structure',
            ];
        } elseif (str_contains(strtolower($biomechType), 'supination')) {
            $gearType = 'Neutral Max Cushion';
            $gearReason = 'Supination cenderung membuat kaki kaku dan bertumpu di sisi luar, sehingga perlu sepatu netral dengan cushioning tebal dan midsole yang lembut.';
            $gearExamples = [
                'Hoka Clifton / Bondi',
                'New Balance 1080',
                'ASICS Nimbus',
            ];
        } elseif (str_contains(strtolower($biomechType), 'midfoot')) {
            $gearType = 'Lightweight Neutral / Performance Trainer';
            $gearReason = 'Pendaratan midfoot yang stabil cocok dengan sepatu netral yang responsif, tidak terlalu tinggi, dan cukup stabil untuk tempo run.';
            $gearExamples = [
                'ASICS Novablast',
                'Nike Tempo Next% (latihan cepat)',
                'Adidas Boston',
            ];
        }

        if ($wearPctInt !== null && $wearPctInt >= 90) {
            $gearReason .= ' Outsole dan midsole sepatu ini sudah mendekati akhir usia pakai, sebaiknya mulai transisi ke pasangan baru untuk sesi kunci.';
        }

        $formAdvice = null;

        if (str_contains(strtolower($biomechType), 'overpronation')) {
            $formAdvice = 'Fokus pada penguatan gluteus medius, otot pinggul, dan otot penyangga arch kaki. Latihan seperti single-leg squat ke box, monster walk dengan resistance band, dan calf raise satu kaki 2–3x per minggu sangat membantu menahan pronasi berlebih.';
        } elseif (str_contains(strtolower($biomechType), 'supination')) {
            $formAdvice = 'Prioritaskan mobilitas pergelangan kaki (ankle dorsiflexion) dan penguatan otot betis bagian luar serta peroneal. Kombinasikan ankle mobility drill, calf stretch dinamis, dan latihan balance satu kaki di permukaan tidak rata.';
        } else {
            $formAdvice = 'Pertahankan pola netral dengan kombinasi penguatan glutes, hamstring, dan core. Deadlift ringan, hip thrust, plank variasi, dan calf raise bisa dijadikan menu dasar strength 2x seminggu.';
        }

        if ($painZones->contains('knee')) {
            $formAdvice .= ' Tambahkan fokus pada penguatan quadriceps dan kontrol lutut, misalnya wall sit, step down pelan dari box rendah, dan lunges terkontrol.';
        }
        if ($painZones->contains('shin')) {
            $formAdvice .= ' Untuk shin splints, turunkan sementara intensitas lari cepat, tambah latihan eksentrik untuk tibialis anterior, dan perbanyak easy run di permukaan lebih empuk.';
        }
        if ($painZones->contains('achilles')) {
            $formAdvice .= ' Untuk Achilles, lakukan calf raise eksentrik di tangga (3 set x 15 repetisi) 3–4x per minggu dan hindari lonjakan tiba-tiba volume hill sprint.';
        }
        if ($painZones->contains('plantar')) {
            $formAdvice .= ' Untuk plantar fasciitis, lakukan rolling telapak kaki dengan bola kecil, stretching betis, dan latihan towel curl untuk menguatkan otot intrinsik kaki.';
        }

        return response()->json([
            'biomechanics_type' => $biomechType,
            'wear_percentage' => $wearPctInt,
            'estimated_remaining_km' => $remainingKm !== null ? (int) round($remainingKm) : null,
            'health_status' => $healthStatus,
            'injury_risks' => [
                'level' => $injuryLevel,
                'potential_injuries' => $injuries,
                'biomechanical_warning' => $warning,
            ],
            'form_advice' => $formAdvice,
            'gear_recommendation' => [
                'type' => $gearType,
                'reason' => $gearReason,
                'examples' => $gearExamples,
            ],
        ]);
    }
}

