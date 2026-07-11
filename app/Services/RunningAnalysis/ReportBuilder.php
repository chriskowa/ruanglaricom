<?php

namespace App\Services\RunningAnalysis;

use App\Models\RunningAnalysis\Trial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportBuilder
{
    protected $eventDetector;
    protected $metricCalculator;
    protected $ruleEngine;
    protected $catalog;

    public function __construct(
        GaitEventDetector $eventDetector,
        MetricCalculator $metricCalculator,
        FindingRuleEngine $ruleEngine,
        RecommendationCatalog $catalog
    ) {
        $this->eventDetector = $eventDetector;
        $this->metricCalculator = $metricCalculator;
        $this->ruleEngine = $ruleEngine;
        $this->catalog = $catalog;
    }

    /**
     * Process a Trial: parse JSON, run deterministic rules, save Report models.
     */
    public function process(Trial $trial)
    {
        // 1. Fetch Pose Data
        $poseArtifact = $trial->artifacts()->where('type', 'pose_landmarks')->first();
        if (!$poseArtifact) {
            throw new \Exception("Trial {$trial->id} is missing pose data artifact.");
        }

        $jsonStr = Storage::disk($poseArtifact->disk)->get($poseArtifact->path);
        $data = json_decode($jsonStr, true);
        
        $frames = $data['landmarks'] ?? [];

        return DB::transaction(function () use ($trial, $frames) {
            // 2. Detect Events
            $events = $this->eventDetector->detect($frames, $trial->inference_fps ?: 30.0);
            
            // Save Gait Events
            $strideIndex = 1;
            foreach ($events as $event) {
                $trial->gaitEvents()->create([
                    'stride_index' => $strideIndex++,
                    'event_type' => $event['event_type'],
                    'side' => $event['side'],
                    'frame_index' => $event['frame'],
                    'timestamp_ms' => $event['timestamp_ms'],
                    'confidence' => 1.0, // Assuming high confidence for now
                    'source' => \App\Models\RunningAnalysis\GaitEvent::SOURCE_AUTOMATIC,
                ]);
            }

            // 3. Calculate Biomechanical metrics and Shoulder-Hip Skeletal Ratio
            $metricsData = $this->metricCalculator->calculate($events);
            
            // Calculate skeletal proportions (Shoulder-to-Hip ratio)
            $totalRatio = 0;
            $ratioCount = 0;
            foreach ($frames as $frame) {
                // Landmarks can be nested in 'landmarks' index or flat in the array element
                $landmarks = $frame['landmarks'] ?? $frame; 
                if (isset($landmarks[11], $landmarks[12], $landmarks[23], $landmarks[24])) {
                    $lS = $landmarks[11];
                    $rS = $landmarks[12];
                    $lH = $landmarks[23];
                    $rH = $landmarks[24];
                    
                    if (($lS['visibility'] ?? 0) > 0.5 && ($rS['visibility'] ?? 0) > 0.5 &&
                        ($lH['visibility'] ?? 0) > 0.5 && ($rH['visibility'] ?? 0) > 0.5) {
                        
                        $shoulderWidth = sqrt(pow($lS['x'] - $rS['x'], 2) + pow($lS['y'] - $rS['y'], 2));
                        $hipWidth = sqrt(pow($lH['x'] - $rH['x'], 2) + pow($lH['y'] - $rH['y'], 2));
                        
                        if ($hipWidth > 0) {
                            $totalRatio += ($shoulderWidth / $hipWidth);
                            $ratioCount++;
                        }
                    }
                }
            }
            $avgRatio = $ratioCount > 0 ? $totalRatio / $ratioCount : 1.0;
            $predictedGender = ($avgRatio < 1.22) ? 'female' : 'male';
            
            // Save Metrics (EAV structure)
            $metricsToSave = [
                ['code' => 'CADENCE_SPM', 'val' => $metricsData['cadence'], 'unit' => 'spm'],
                ['code' => 'GCT_LEFT_MS', 'val' => $metricsData['contact_time_ms_left'], 'unit' => 'ms'],
                ['code' => 'GCT_RIGHT_MS', 'val' => $metricsData['contact_time_ms_right'], 'unit' => 'ms'],
                ['code' => 'FLIGHT_TIME_MS', 'val' => $metricsData['flight_time_ms'], 'unit' => 'ms'],
                ['code' => 'SKELETAL_GENDER_RATIO', 'val' => $avgRatio, 'unit' => 'ratio'],
            ];

            foreach ($metricsToSave as $m) {
                $trial->metrics()->create([
                    'metric_code' => $m['code'],
                    'category' => \App\Models\RunningAnalysis\Metric::CATEGORY_GENERAL,
                    'value_decimal' => $m['val'],
                    'unit' => $m['unit'],
                    'confidence' => 1.0,
                    'calculation_version' => '1.0',
                ]);
            }

            // 4. Evaluate Findings
            $findingsData = $this->ruleEngine->evaluate($metricsData);
            
            // Check for profile gender vs skeletal predicted gender mismatch
            $profileGender = $trial->runner->gender ?? null;
            if ($profileGender && $profileGender !== $predictedGender) {
                $findingsData[] = [
                    'type' => 'gender_mismatch',
                    'severity' => 'moderate',
                    'description' => 'Ketidakcocokan gender skeletal: Struktur tulang terdeteksi sebagai ' . $predictedGender . ' (Shoulder-to-Hip ratio: ' . round($avgRatio, 2) . '), sedangkan profile pelari adalah ' . $profileGender . '.',
                ];
            }
            
            // Save Findings
            $savedFindings = [];
            foreach ($findingsData as $f) {
                $savedFindings[] = $trial->findings()->create([
                    'finding_code' => strtoupper($f['type']), // low_cadence -> LOW_CADENCE
                    'category' => 'general',
                    'severity' => $f['severity'],
                    'confidence' => 1.0,
                    'evidence_json' => ['metric_value' => $f['description']],
                    'explanation_key' => $f['type'],
                    'ruleset_version' => '1.0',
                    'review_status' => \App\Models\RunningAnalysis\Finding::REVIEW_GENERATED,
                ]);
            }

            // 5. Generate Recommendations
            $recsData = $this->catalog->generate($findingsData);
            
            foreach ($recsData as $i => $r) {
                // Attach to the first finding just for relationship testing, usually it maps 1:1
                $findingId = $savedFindings[$i]->id ?? null;
                
                $trial->recommendations()->create([
                    'finding_id' => $findingId,
                    'recommendation_code' => $r['recommendation_code'],
                    'type' => $r['type'],
                    'title' => $r['title'],
                    'description' => $r['description'],
                    'priority' => $r['priority'],
                    'source' => \App\Models\RunningAnalysis\Recommendation::SOURCE_DETERMINISTIC,
                    'catalog_version' => '1.0',
                ]);
            }

            // 6. Update Trial Status
            $trial->update(['status' => Trial::STATUS_REVIEW_REQUIRED]);

            return true;
        });
    }
}
