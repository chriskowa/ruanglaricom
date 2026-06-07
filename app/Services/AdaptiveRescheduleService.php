<?php

namespace App\Services;

use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\RunnerInjuryLog;
use Carbon\Carbon;

class AdaptiveRescheduleService
{
    private DanielsRunningService $daniels;

    public function __construct(DanielsRunningService $daniels)
    {
        $this->daniels = $daniels;
    }

    /**
     * Utama: Memproses reschedule berdasarkan kondisi pelari
     */
    public function reschedule(ProgramEnrollment $enrollment, array $params): array
    {
        $reason = $params['reason'] ?? 'busy'; // 'sick', 'busy', 'injury'
        $daysMissed = (int) ($params['days_missed'] ?? 0);
        $startDate = isset($params['start_date']) ? Carbon::parse($params['start_date']) : Carbon::today(); // Tanggal pelari aktif kembali
        
        $user = $enrollment->runner;
        $originalVdot = $enrollment->current_vdot ?? $enrollment->program->vdot ?? $user->vdot ?? 40.0;
        
        // 1. Hitung Penurunan VDOT akibat Detraining
        $adjustedVdot = $this->calculateDetrainingVdot($originalVdot, $daysMissed);
        
        // 2. Tentukan Protokol Pemulihan Latihan
        $recoveryWeeks = 0;
        $recoveryProtocol = [];
        if ($reason === 'injury') {
            $injurySeverity = $params['injury_severity'] ?? 'minor'; // minor, moderate
            $recoveryProtocol = $this->getInjuryRecoveryProtocol($injurySeverity);
            $recoveryWeeks = count($recoveryProtocol);

            // Log injury
            RunnerInjuryLog::create([
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'injury_type' => $injurySeverity,
                'body_part' => $params['body_part'] ?? 'unknown',
                'injured_at' => Carbon::today()->subDays($daysMissed),
                'recovered_at' => $startDate,
                'notes' => $params['notes'] ?? 'Auto-logged during reschedule',
            ]);
        }

        // 3. Muat program asli
        $programJson = $enrollment->program->program_json;
        $sessions = $programJson['sessions'] ?? [];
        
        // 4. Hitung sisa sesi yang belum dikerjakan
        $completedSessionDays = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->pluck('session_day')
            ->toArray();
            
        $remainingSessions = array_filter($sessions, function($session) use ($completedSessionDays) {
            return !in_array((int)$session['day'], $completedSessionDays);
        });

        // 5. Susun Ulang Sesi berdasarkan Linimasa Baru & ACWR
        $newScheduledSessions = [];
        $currentDate = $startDate->copy();
        
        // Sisipkan sesi recovery terlebih dahulu jika ada
        if (!empty($recoveryProtocol)) {
            foreach ($recoveryProtocol as $weekIndex => $weekConfig) {
                $weekPaces = $this->daniels->calculateTrainingPaces($adjustedVdot);
                
                // Buat sesi Easy Recovery
                for ($d = 1; $d <= 7; $d++) {
                    if ($d === 1 || $d === 5) { // Rest days
                        $newScheduledSessions[] = $this->buildRestSession($currentDate->copy(), $weekIndex + 1);
                    } else {
                        // Easy Run dengan persentase volume dari target normal
                        $normalEasyDist = 6.0; // Default
                        $dist = $normalEasyDist * $weekConfig['volume_pct'];
                        $newScheduledSessions[] = $this->buildEasySession($currentDate->copy(), $dist, $weekPaces['E'], $weekIndex + 1);
                    }
                    $currentDate->addDay();
                }
            }
        }

        // Hitung target mingguan sebelum absen untuk patokan ACWR
        $historicWeeklyMileage = (float)($enrollment->program->weekly_mileage ?? 30.0);
        $chronicWorkload = $historicWeeklyMileage; // Inisialisasi awal kebiasaan volume pelari

        // Susun kembali sisa sesi latihan utama
        $weekCounter = $recoveryWeeks + 1;
        $weekSessions = [];
        
        foreach ($remainingSessions as $session) {
            $weekSessions[] = $session;
            
            // Ketika terkumpul 1 minggu latihan (atau jika ini sesi terakhir)
            if (count($weekSessions) >= 7 || next($remainingSessions) === false) {
                // Hitung total volume usulan minggu ini
                $proposedWeeklyVolume = array_reduce($weekSessions, function($carry, $s) {
                    return $carry + (float)($s['distance'] ?? 0);
                }, 0);

                // Cek batasan ACWR
                $acwr = $proposedWeeklyVolume / max(1, $chronicWorkload);
                
                // Jika melebihi batas 1.3, pangkas jarak sesi agar aman
                if ($acwr > 1.3) {
                    $scalingFactor = (1.3 * $chronicWorkload) / $proposedWeeklyVolume;
                    foreach ($weekSessions as &$ws) {
                        if (isset($ws['distance']) && $ws['distance'] > 0) {
                            $ws['distance'] = round($ws['distance'] * $scalingFactor, 1);
                            // Hitung ulang durasi sesi berdasarkan pace baru
                            $ws['duration'] = $this->recalculateDuration($ws['distance'], $ws['type'] ?? 'easy_run', $adjustedVdot);
                        }
                    }
                }

                // Tambahkan tanggal nyata ke sesi
                foreach ($weekSessions as $index => $ws) {
                    $sessionDate = $currentDate->copy()->addDays($index);
                    $newScheduledSessions[] = [
                        'date' => $sessionDate->format('Y-m-d'),
                        'session_day' => (int)$ws['day'],
                        'week' => $weekCounter,
                        'type' => $ws['type'] ?? 'easy_run',
                        'distance' => $ws['distance'] ?? 0,
                        'duration' => $ws['duration'] ?? '00:00:00',
                        'description' => ($ws['description'] ?? '') . " (Rescheduled - VDOT adjusted to {$adjustedVdot})",
                    ];
                }

                // Update chronic workload secara progresif
                $chronicWorkload = ($chronicWorkload * 3 + $proposedWeeklyVolume) / 4; // Rata-rata bergerak sederhana
                $currentDate->addDays(7);
                $weekSessions = [];
                $weekCounter++;
            }
        }

        return [
            'enrollment_id' => $enrollment->id,
            'adjusted_vdot' => $adjustedVdot,
            'sessions' => $newScheduledSessions
        ];
    }

    /**
     * Menghitung VDOT pelari setelah tidak aktif
     */
    public function calculateDetrainingVdot(float $originalVdot, int $daysMissed): float
    {
        if ($daysMissed <= 4) return $originalVdot;
        if ($daysMissed <= 7) return max(10.0, $originalVdot - 1.0);
        if ($daysMissed <= 14) return max(10.0, $originalVdot - 2.0);
        if ($daysMissed <= 30) return max(10.0, $originalVdot - 4.0);
        
        // Lebih dari 30 hari: kurangi 15% dari VDOT asli
        return max(10.0, round($originalVdot * 0.85, 2));
    }

    /**
     * Mendapatkan konfigurasi pemulihan bertahap pasca-cedera
     */
    private function getInjuryRecoveryProtocol(string $severity): array
    {
        if ($severity === 'minor') {
            return [
                ['week' => 1, 'volume_pct' => 0.50, 'description' => 'Recovery Week 1: 50% Volume, Easy Run Only'],
            ];
        }
        
        // Cedera sedang/berat membutuhkan pemulihan 2-3 minggu
        return [
            ['week' => 1, 'volume_pct' => 0.40, 'description' => 'Recovery Week 1: 40% Volume, E-Run Only'],
            ['week' => 2, 'volume_pct' => 0.65, 'description' => 'Recovery Week 2: 65% Volume, E-Run + Strides'],
        ];
    }

    private function buildRestSession(Carbon $date, int $week): array
    {
        return [
            'date' => $date->format('Y-m-d'),
            'type' => 'rest',
            'week' => $week,
            'distance' => 0,
            'duration' => '00:00:00',
            'description' => 'Rest day - Pemulihan otot pasca-absen/cedera.',
        ];
    }

    private function buildEasySession(Carbon $date, float $distance, float $paceMinPerKm, int $week): array
    {
        $totalSec = round($distance * $paceMinPerKm * 60);
        $duration = sprintf('%02d:%02d:%02d', floor($totalSec/3600), floor(($totalSec%3600)/60), $totalSec%60);

        return [
            'date' => $date->format('Y-m-d'),
            'type' => 'easy_run',
            'week' => $week,
            'distance' => round($distance, 1),
            'duration' => $duration,
            'description' => 'Easy recovery run. Jaga detak jantung di zona aerobik santai.',
        ];
    }

    private function recalculateDuration(float $distance, string $type, float $vdot): string
    {
        $paces = $this->daniels->calculateTrainingPaces($vdot);
        $paceType = 'E';
        if ($type === 'interval') $paceType = 'I';
        elseif ($type === 'tempo' || $type === 'threshold') $paceType = 'T';
        elseif ($type === 'repetition') $paceType = 'R';

        $pace = $paces[$paceType] ?? $paces['E'];
        $totalSec = round($distance * $pace * 60);
        return sprintf('%02d:%02d:%02d', floor($totalSec/3600), floor(($totalSec%3600)/60), $totalSec%60);
    }
}
