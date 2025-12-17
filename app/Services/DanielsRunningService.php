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
        
        // Simplified VDOT calculation
        // This is an approximation - full VDOT calculation is more complex
        // Formula: VDOT ≈ 0.2989558 * (distance/time) + (-0.193260626) * (time) + 7.000388531
        
        $velocity = $distanceInMeters / $totalSeconds; // meters per second
        
        // Approximate VDOT calculation
        $vdot = (-4.6 + 0.182258 * $velocity * 1000) + (0.000104 * pow($velocity * 1000, 2));
        
        // Adjust based on distance (different distances have different VDOT equivalencies)
        $adjustment = $this->getVDOTAdjustment($distance);
        $vdot = $vdot * $adjustment;
        
        // Round to 2 decimal places and ensure reasonable range (30-85)
        return max(30, min(85, round($vdot, 2)));
    }
    
    /**
     * Get VDOT adjustment factor based on distance
     */
    private function getVDOTAdjustment(string $distance): float
    {
        $adjustments = [
            '5k' => 1.0,
            '10k' => 0.98,
            '21k' => 0.96,
            '42k' => 0.94,
        ];
        
        return $adjustments[$distance] ?? 1.0;
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
        // Simplified pace calculations based on VDOT
        // E pace (Easy): ~65-78% of VO2max
        // T pace (Threshold): ~83-88% of VO2max  
        // I pace (Interval): ~95-100% of VO2max
        // R pace (Repetition): Faster than I pace
        
        // Base pace calculation (minutes per km)
        $basePace = 0.2 * pow($vdot, -1.1); // Simplified formula
        
        $paces = [
            'E' => round($basePace * 1.35, 2), // Easy pace
            'M' => round($basePace * 1.15, 2), // Marathon pace
            'T' => round($basePace * 1.05, 2), // Threshold pace
            'I' => round($basePace * 0.98, 2), // Interval pace
            'R' => round($basePace * 0.95, 2), // Repetition pace
        ];
        
        return $paces;
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










