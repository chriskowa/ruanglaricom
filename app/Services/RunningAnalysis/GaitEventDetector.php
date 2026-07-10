<?php

namespace App\Services\RunningAnalysis;

use Illuminate\Support\Collection;

class GaitEventDetector
{
    // MediaPipe pose landmark indices
    const LEFT_HEEL = 29;
    const RIGHT_HEEL = 30;
    const LEFT_FOOT_INDEX = 31; // Toe
    const RIGHT_FOOT_INDEX = 32;

    /**
     * Detect gait events from a sequence of frames.
     * Returns a collection of events (heel_strike, toe_off).
     * 
     * Note: MediaPipe Y-axis goes from 0 (top) to 1 (bottom).
     * So a "max Y" value means the foot is lowest (striking ground).
     */
    public function detect(array $frames, float $fps = 30.0): Collection
    {
        $events = collect();
        
        // Ensure we have frames
        if (empty($frames)) {
            return $events;
        }

        // Smooth data to prevent false peaks
        $smoothedLeftHeel = $this->smoothY($frames, self::LEFT_HEEL);
        $smoothedRightHeel = $this->smoothY($frames, self::RIGHT_HEEL);
        $smoothedLeftToe = $this->smoothY($frames, self::LEFT_FOOT_INDEX);
        $smoothedRightToe = $this->smoothY($frames, self::RIGHT_FOOT_INDEX);

        // Find Heel Strikes (Local Maxima of Heel Y)
        $leftStrikes = $this->findLocalMaxima($smoothedLeftHeel);
        $rightStrikes = $this->findLocalMaxima($smoothedRightHeel);

        // Find Toe Offs (Local Minima following a Heel Strike, or inflection points)
        // A simple approximation for toe-off is when the toe reaches its highest point 
        // behind the runner (local minimum in Y, or max backward in Z).
        // For 2D/pseudo-3D video, finding the local minima of Toe Y isn't always reliable.
        // We'll use the peak of backward velocity in Z or just the lowest Y point before it goes up.
        $leftToeOffs = $this->findLocalMinima($smoothedLeftToe);
        $rightToeOffs = $this->findLocalMinima($smoothedRightToe);

        // Combine and label events
        foreach ($leftStrikes as $frameIdx) {
            $events->push(['event_type' => 'initial_contact', 'side' => 'left', 'frame' => $frameIdx, 'timestamp_ms' => ($frameIdx / $fps) * 1000]);
        }
        foreach ($rightStrikes as $frameIdx) {
            $events->push(['event_type' => 'initial_contact', 'side' => 'right', 'frame' => $frameIdx, 'timestamp_ms' => ($frameIdx / $fps) * 1000]);
        }
        foreach ($leftToeOffs as $frameIdx) {
            $events->push(['event_type' => 'toe_off', 'side' => 'left', 'frame' => $frameIdx, 'timestamp_ms' => ($frameIdx / $fps) * 1000]);
        }
        foreach ($rightToeOffs as $frameIdx) {
            $events->push(['event_type' => 'toe_off', 'side' => 'right', 'frame' => $frameIdx, 'timestamp_ms' => ($frameIdx / $fps) * 1000]);
        }

        // Sort by frame
        return $events->sortBy('frame')->values();
    }

    /**
     * Apply a simple moving average to smooth the Y coordinates.
     */
    private function smoothY(array $frames, int $landmarkIndex, int $window = 3): array
    {
        $smoothed = [];
        $count = count($frames);

        for ($i = 0; $i < $count; $i++) {
            $sum = 0;
            $samples = 0;

            for ($j = max(0, $i - $window); $j <= min($count - 1, $i + $window); $j++) {
                if (isset($frames[$j][$landmarkIndex]['y'])) {
                    $sum += $frames[$j][$landmarkIndex]['y'];
                    $samples++;
                }
            }
            
            $smoothed[$i] = $samples > 0 ? $sum / $samples : 0;
        }

        return $smoothed;
    }

    /**
     * Find local maxima in an array of numbers (Y-axis pointing down -> ground contact).
     */
    private function findLocalMaxima(array $data): array
    {
        $peaks = [];
        $count = count($data);

        for ($i = 1; $i < $count - 1; $i++) {
            if ($data[$i] > $data[$i - 1] && $data[$i] > $data[$i + 1]) {
                // Ensure it's a significant peak (not just noise)
                // In a real app we'd add prominence thresholding here.
                $peaks[] = $i;
            }
        }

        return $peaks;
    }

    /**
     * Find local minima.
     */
    private function findLocalMinima(array $data): array
    {
        $valleys = [];
        $count = count($data);

        for ($i = 1; $i < $count - 1; $i++) {
            if ($data[$i] < $data[$i - 1] && $data[$i] < $data[$i + 1]) {
                $valleys[] = $i;
            }
        }

        return $valleys;
    }
}
