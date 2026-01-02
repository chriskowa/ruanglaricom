<?php

namespace App\Services;

use Carbon\Carbon;

class DanielsRunningService
{
    /**
     * Calculate VDOT from race time and distance
     * Based on Jack Daniels' Running Formula
     */
    public function calculateVDOT(string $raceTime, string $distance): float
    {
        // Parse time (HH:MM:SS or MM:SS)
        $timeParts = explode(':', $raceTime);
        $totalSeconds = 0;
        
        if (count($timeParts) === 3) {
            // HH:MM:SS
            $totalSeconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2];
        } elseif (count($timeParts) === 2) {
            // MM:SS
            $totalSeconds = ($timeParts[0] * 60) + $timeParts[1];
        }
        
        // Convert distance to meters
        $distanceInMeters = $this->distanceToMeters($distance);
        
        // Calculate pace per kilometer
        $pacePerKm = $totalSeconds / ($distanceInMeters / 1000); // seconds per km
        
        $velocity = $distanceInMeters / $totalSeconds; // meters per second
        
        // Approximate VDOT calculation
        // Formula often uses velocity in meters per minute
        $velocityMin = $velocity * 60;
        
        // Iterative calculation to match equivalent race times logic
        // Initial VDOT guess
        $vdot = 50.0;
        
        for ($i = 0; $i < 5; $i++) {
            $ratio = $this->getRatioForDistance($distance, $vdot);
            if ($ratio <= 0) $ratio = 0.01;
            
            // Calculate implied vVO2max from this race performance
            $vVO2max = $velocityMin / $ratio;
            
            // Calculate new VDOT from vVO2max
            // VDOT = -4.6 + 0.182258 * v + 0.000104 * v^2
            $newVdot = -4.6 + 0.182258 * $vVO2max + 0.000104 * pow($vVO2max, 2);
            
            if (abs($newVdot - $vdot) < 0.01) {
                $vdot = $newVdot;
                break;
            }
            $vdot = $newVdot;
        }
        
        // Round to 2 decimal places and ensure reasonable range (30-85)
        return max(30, min(85, round($vdot, 2)));
    }
    
    /**
     * Get ratio of vVO2max sustainable for a given distance and VDOT
     */
    private function getRatioForDistance(string $distance, float $vdot): float
    {
        $ratios = [
            '5k' => 0.957,    // ~95.7% of vVO2max
            '10k' => 0.915,   // ~91.5% of vVO2max
            '21k' => 0.865,   // ~86.5% of vVO2max
            '42k' => 0.815,   // ~81.5% of vVO2max
        ];
        
        // Normalize key
        $key = '5k';
        if (strpos($distance, '5k') !== false) $key = '5k';
        elseif (strpos($distance, '10k') !== false) $key = '10k';
        elseif (strpos($distance, 'hm') !== false || strpos($distance, '21k') !== false) $key = '21k';
        elseif (strpos($distance, 'fm') !== false || strpos($distance, '42k') !== false) $key = '42k';

        $ratio = $ratios[$key] ?? 0.957;
        
        // Minor adjustment for VDOT range: higher VDOT = slightly better endurance
        $ratio += ($vdot - 50) * 0.0005; 
        
        return $ratio;
    }
    
    /**
     * Convert distance string to meters
     */
    private function distanceToMeters(string $distance): float
    {
        $distances = [
            '5k' => 5000,
            '10k' => 10000,
            '21k' => 21097.5,
            '42k' => 42195,
        ];
        
        return $distances[$distance] ?? 5000;
    }
    
    /**
     * Calculate training paces based on VDOT
     * Returns paces in minutes per kilometer
     */
    public function calculateTrainingPaces(float $vdot): array
    {
        // 1. Calculate Velocity at VO2max (vVO2max) in meters/minute
        // Using the inverse of the VDOT formula: 
        // 0.000104 * v^2 + 0.182258 * v + (-4.6 - VDOT) = 0
        
        $a = 0.000104;
        $b = 0.182258;
        $c = -4.6 - $vdot;
        
        // Quadratic formula: v = (-b + sqrt(b^2 - 4ac)) / 2a
        $vVO2max = (-$b + sqrt(pow($b, 2) - 4 * $a * $c)) / (2 * $a);
        
        // 2. Calculate Paces based on % of vVO2max
        // Intensities (approximate Daniels' percentages)
        $ratios = [
            'E' => 0.70,  // Easy: ~70% vVO2max
            'M' => 0.82,  // Marathon: ~82% vVO2max
            'T' => 0.88,  // Threshold: ~88% vVO2max
            'I' => 0.97,  // Interval: ~97% vVO2max
            'R' => 1.05,  // Repetition: ~105% vVO2max
        ];
        
        $paces = [];
        foreach ($ratios as $type => $ratio) {
            $velocity = $vVO2max * $ratio; // m/min
            $paceMinPerKm = 1000 / $velocity; // min/km
            $paces[$type] = round($paceMinPerKm, 2);
        }
        
        return $paces;
    }

    /**
     * Calculate equivalent race times for standard distances
     */
    public function calculateEquivalentRaceTimes(float $vdot): array
    {
        // Standard distances in meters
        $distances = [
            '5k' => 5000,
            '10k' => 10000,
            '21k' => 21097.5,
            '42k' => 42195,
        ];

        // 1. Calculate Velocity at VO2max (vVO2max)
        $a = 0.000104;
        $b = 0.182258;
        $c = -4.6 - $vdot;
        $vVO2max = (-$b + sqrt(pow($b, 2) - 4 * $a * $c)) / (2 * $a); // m/min
        
        $results = [];
        foreach ($distances as $name => $distMeters) {
            // Velocity for this distance
            $ratio = $this->getRatioForDistance($name, $vdot);
            
            $velocity = $vVO2max * $ratio; // m/min
            
            // Calculate Time
            $totalMinutes = $distMeters / $velocity;
            
            // Format Time
            $hours = floor($totalMinutes / 60);
            $minutes = floor($totalMinutes % 60);
            $seconds = round(($totalMinutes - floor($totalMinutes)) * 60);
            $timeStr = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            
            // Calculate Pace (min/km)
            $paceMinPerKm = 1000 / $velocity;
            $pm = floor($paceMinPerKm);
            $ps = round(($paceMinPerKm - $pm) * 60);
            $paceStr = sprintf('%d:%02d', $pm, $ps);
            
            $results[$name] = [
                'time' => $timeStr,
                'pace' => $paceStr . '/km'
            ];
        }

        return $results;
    }

    /**
     * Calculate track split times for specific distances
     * Returns times for Repetition, Interval, and Threshold paces
     */
    public function calculateTrackTimes(float $vdot): array
    {
        $paces = $this->calculateTrainingPaces($vdot); // min/km
        
        $distances = [
            '100m' => 0.1,
            '200m' => 0.2,
            '300m' => 0.3,
            '400m' => 0.4,
            '600m' => 0.6,
            '800m' => 0.8,
            '1000m' => 1.0,
            '1200m' => 1.2,
            '1600m' => 1.6,
            '2000m' => 2.0,
        ];

        $trackTimes = [];

        foreach ($distances as $label => $distKm) {
            $trackTimes[$label] = [
                'R' => $this->formatSplitTime($distKm * $paces['R']),
                'I' => $this->formatSplitTime($distKm * $paces['I']),
                'T' => $this->formatSplitTime($distKm * $paces['T']),
                'pace_R' => $this->formatPace($paces['R']),
                'pace_I' => $this->formatPace($paces['I']),
                'pace_T' => $this->formatPace($paces['T']),
            ];
        }

        return $trackTimes;
    }

    /**
     * Format pace (minutes/km) to MM:SS
     */
    private function formatPace(float $minutes): string
    {
        $m = floor($minutes);
        $s = round(($minutes - $m) * 60);
        return sprintf('%d:%02d', $m, $s);
    }

    /**
     * Format split time (minutes) to MM:SS or SS if < 1 min
     */
    private function formatSplitTime(float $minutes): string
    {
        $totalSeconds = round($minutes * 60);
        $m = floor($totalSeconds / 60);
        $s = $totalSeconds % 60;

        if ($m > 0) {
            return sprintf('%d:%02d', $m, $s);
        } else {
            return sprintf('%d', $s);
        }
    }
    
    /**
     * Generate training program based on Daniels' principles
     */
    public function generateProgram(array $params): array
    {
        $vdot = $this->calculateVDOT($params['race_time'], $params['race_distance']);
        $paces = $this->calculateTrainingPaces($vdot);
        
        $goalDistance = $params['goal_distance'];
        $goalRaceDate = Carbon::parse($params['goal_race_date']);
        $weeklyMileage = (float) $params['weekly_mileage'];
        $frequency = (int) $params['training_frequency'];
        
        // Calculate training duration (18-24 weeks)
        $weeksUntilRace = max(18, min(24, (int) ceil($goalRaceDate->diffInWeeks(Carbon::now()))));
        
        // Determine training phase distribution
        $phase1Weeks = max(4, (int) ($weeksUntilRace * 0.25)); // Foundation (25%)
        $phase2Weeks = max(4, (int) ($weeksUntilRace * 0.25)); // Early Quality (25%)
        $phase3Weeks = max(6, (int) ($weeksUntilRace * 0.40)); // Quality (40%)
        $phase4Weeks = max(2, (int) ($weeksUntilRace * 0.10)); // Tapering (10%)
        
        $sessions = [];
        $day = 1;
        
        // Phase 1: Foundation (Easy runs only)
        for ($week = 1; $week <= $phase1Weeks; $week++) {
            $weeklyKm = $weeklyMileage + ($week * ($weeklyMileage * 0.05)); // Gradual increase
            
            $daysPerWeek = min($frequency, 5);
            $kmPerDay = $weeklyKm / $daysPerWeek;
            
            for ($w = 1; $w <= 7; $w++) {
                if ($w <= $daysPerWeek) {
                    $sessions[] = [
                        'day' => $day++,
                        'type' => 'easy_run',
                        'distance' => round($kmPerDay, 1),
                        'duration' => $this->timeFromPace($kmPerDay, $paces['E']),
                        'description' => 'Easy run - Build aerobic base',
                    ];
                } else {
                    $sessions[] = [
                        'day' => $day++,
                        'type' => 'rest',
                        'description' => 'Rest day',
                    ];
                }
            }
        }
        
        // Phase 2: Early Quality (Introduce speed work)
        for ($week = 1; $week <= $phase2Weeks; $week++) {
            $weeklyKm = $weeklyMileage * 1.1;
            
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.2, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.2, $paces['E']), 'description' => 'Easy run'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            
            if ($goalDistance == '5k' || $goalDistance == '10k') {
                // More R pace for shorter distances
                $sessions[] = ['day' => $day++, 'type' => 'repetition', 'distance' => 5, 'duration' => $this->timeFromPace(5, $paces['R']), 'description' => 'Repetition: 8x 200m with recovery'];
            } else {
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.15, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.15, $paces['E']), 'description' => 'Easy run'];
            }
            
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.2, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.2, $paces['E']), 'description' => 'Easy run'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.45, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.45, $paces['E']), 'description' => 'Long easy run'];
        }
        
        // Phase 3: Quality (Specific training)
        for ($week = 1; $week <= $phase3Weeks; $week++) {
            $weeklyKm = $weeklyMileage * 1.15;
            
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.15, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.15, $paces['E']), 'description' => 'Easy run'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            
            if ($goalDistance == '5k' || $goalDistance == '10k') {
                // Interval focus for short distances
                $sessions[] = ['day' => $day++, 'type' => 'interval', 'distance' => 8, 'duration' => $this->timeFromPace(8, $paces['I']), 'description' => 'Interval: 5x 1000m at I pace'];
            } else {
                // Threshold focus for long distances
                $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => 10, 'duration' => $this->timeFromPace(10, $paces['T']), 'description' => 'Tempo run: 20 min at T pace'];
            }
            
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => round($weeklyKm * 0.15, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.15, $paces['T']), 'description' => 'Tempo run'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.5, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.5, $paces['E']), 'description' => 'Long run'];
        }
        
        // Phase 4: Tapering
        for ($week = 1; $week <= $phase4Weeks; $week++) {
            $weeklyKm = $weeklyMileage * (1 - ($week * 0.2)); // Reduce volume
            
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.2, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.2, $paces['E']), 'description' => 'Easy run'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => round($weeklyKm * 0.3, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.3, $paces['T']), 'description' => 'Tempo run'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.2, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.2, $paces['E']), 'description' => 'Easy run'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest day'];
            
            if ($week < $phase4Weeks) {
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.3, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.3, $paces['E']), 'description' => 'Long run practice'];
            } else {
                $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Race day - Good luck!'];
            }
        }
        
        return [
            'sessions' => $sessions,
            'duration_weeks' => $weeksUntilRace,
            'vdot' => $vdot,
            'training_paces' => $paces,
        ];
    }
    
    /**
     * Generate training program using provided VDOT and duration
     */
    public function generateProgramFromVDOT(float $vdot, array $params): array
    {
        $paces = $this->calculateTrainingPaces($vdot);
        
        $goalDistance = $params['goal_distance'] ?? '10k';
        $weeklyMileage = (float) ($params['weekly_mileage'] ?? 20);
        $frequency = (int) ($params['training_frequency'] ?? 4);
        $durationWeeks = (int) max(6, min(12, (int) ($params['duration_weeks'] ?? 8)));
        
        $phase1Weeks = max(2, (int) ($durationWeeks * 0.25));
        $phase2Weeks = max(2, (int) ($durationWeeks * 0.25));
        $phase3Weeks = max(2, (int) ($durationWeeks * 0.35));
        $phase4Weeks = max(1, $durationWeeks - ($phase1Weeks + $phase2Weeks + $phase3Weeks));
        
        $sessions = [];
        $day = 1;
        
        for ($week = 1; $week <= $phase1Weeks; $week++) {
            $weeklyKm = $weeklyMileage + ($week * ($weeklyMileage * 0.03));
            $daysPerWeek = min($frequency, 5);
            $kmPerDay = $weeklyKm / $daysPerWeek;
            for ($w = 1; $w <= 7; $w++) {
                if ($w <= $daysPerWeek) {
                    $sessions[] = [
                        'day' => $day++,
                        'type' => 'easy_run',
                        'distance' => round($kmPerDay, 1),
                        'duration' => $this->timeFromPace($kmPerDay, $paces['E']),
                        'description' => 'Easy run - Aerobic base',
                    ];
                } else {
                    $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest'];
                }
            }
        }
        
        for ($week = 1; $week <= $phase2Weeks; $week++) {
            $weeklyKm = $weeklyMileage * 1.05;
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.2, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.2, $paces['E']), 'description' => 'Easy'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest'];
            if ($goalDistance == '5k' || $goalDistance == '10k') {
                $sessions[] = ['day' => $day++, 'type' => 'repetition', 'distance' => 4, 'duration' => $this->timeFromPace(4, $paces['R']), 'description' => 'R pace reps'];
            } else {
                $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.15, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.15, $paces['E']), 'description' => 'Easy'];
            }
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.2, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.2, $paces['E']), 'description' => 'Easy'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.4, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.4, $paces['E']), 'description' => 'Long easy run'];
        }
        
        for ($week = 1; $week <= $phase3Weeks; $week++) {
            $weeklyKm = $weeklyMileage * 1.1;
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.15, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.15, $paces['E']), 'description' => 'Easy'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest'];
            if ($goalDistance == '5k' || $goalDistance == '10k') {
                $sessions[] = ['day' => $day++, 'type' => 'interval', 'distance' => 6, 'duration' => $this->timeFromPace(6, $paces['I']), 'description' => 'Intervals'];
            } else {
                $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => 8, 'duration' => $this->timeFromPace(8, $paces['T']), 'description' => 'Tempo run'];
            }
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest'];
            $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => round($weeklyKm * 0.15, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.15, $paces['T']), 'description' => 'Tempo'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.45, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.45, $paces['E']), 'description' => 'Long run'];
        }
        
        for ($week = 1; $week <= $phase4Weeks; $week++) {
            $weeklyKm = $weeklyMileage * (1 - ($week * 0.25));
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.2, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.2, $paces['E']), 'description' => 'Easy'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest'];
            $sessions[] = ['day' => $day++, 'type' => 'tempo', 'distance' => round($weeklyKm * 0.25, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.25, $paces['T']), 'description' => 'Light tempo'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest'];
            $sessions[] = ['day' => $day++, 'type' => 'easy_run', 'distance' => round($weeklyKm * 0.2, 1), 'duration' => $this->timeFromPace($weeklyKm * 0.2, $paces['E']), 'description' => 'Easy'];
            $sessions[] = ['day' => $day++, 'type' => 'rest', 'description' => 'Rest'];
        }
        
        return [
            'sessions' => $sessions,
            'duration_weeks' => $durationWeeks,
            'vdot' => $vdot,
            'training_paces' => $paces,
        ];
    }
    
    /**
     * Calculate time duration from distance and pace
     */
    private function timeFromPace(float $distanceKm, float $paceMinPerKm): string
    {
        $totalMinutes = $distanceKm * $paceMinPerKm;
        $hours = floor($totalMinutes / 60);
        $minutes = floor($totalMinutes % 60);
        $seconds = round(($totalMinutes - floor($totalMinutes)) * 60);
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}










