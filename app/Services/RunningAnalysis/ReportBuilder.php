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
        
        $rawFrames = $data['landmarks'] ?? [];
        $frames = [];
        if (is_array($rawFrames)) {
            foreach ($rawFrames as $f) {
                if (isset($f[29]) || isset($f[0])) {
                    $frames[] = $f;
                } elseif (isset($f['landmarks'])) {
                    $frames[] = $f['landmarks'];
                }
            }
        }
        if (empty($frames) && isset($data['frames']) && is_array($data['frames'])) {
            foreach ($data['frames'] as $f) {
                if (isset($f['landmarks'])) {
                    $frames[] = $f['landmarks'];
                }
            }
        }

        return DB::transaction(function () use ($trial, $frames, $data) {
            $summary = $data['summary'] ?? null;

            // Normalize nested summary keys if present (for real client-side analysis files)
            if (is_array($summary)) {
                // If cadence_spm is nested object, extract 'median'
                if (isset($summary['cadence_spm']['median'])) {
                    $summary['cadence_spm'] = $summary['cadence_spm']['median'];
                }
                
                // If angles is present, extract sub-keys
                if (isset($summary['angles']['trunk_lean_at_contact_median'])) {
                    $summary['trunk_lean_deg'] = $summary['angles']['trunk_lean_at_contact_median'];
                }
                
                // Knee flexion (average of peak knee flexion or left knee flexion)
                if (isset($summary['angles']['peak_knee_flexion_left_median']) && isset($summary['angles']['peak_knee_flexion_right_median'])) {
                    $summary['knee_flex_deg'] = ($summary['angles']['peak_knee_flexion_left_median'] + $summary['angles']['peak_knee_flexion_right_median']) / 2;
                } elseif (isset($summary['angles']['left_knee_at_contact_median'])) {
                    $summary['knee_flex_deg'] = $summary['angles']['left_knee_at_contact_median'];
                }
                
                // Elbow angle (average of left/right elbow angle if present, otherwise default to 90)
                if (isset($summary['angles']['left_elbow_median']) && isset($summary['angles']['right_elbow_median'])) {
                    $summary['elbow_angle_deg'] = ($summary['angles']['left_elbow_median'] + $summary['angles']['right_elbow_median']) / 2;
                } else {
                    $summary['elbow_angle_deg'] = 90.0;
                }
                
                // Asymmetry
                if (isset($summary['symmetry']['gct_asymmetry_percent'])) {
                    $summary['asymmetry'] = $summary['symmetry']['gct_asymmetry_percent'];
                }
                
                // Confidence from quality overall score
                if (isset($data['quality']['overall_score'])) {
                    $summary['confidence'] = $data['quality']['overall_score'];
                }
                
                // Samples count from valid_frame_count
                if (isset($summary['valid_frame_count'])) {
                    $summary['samples'] = $summary['valid_frame_count'];
                }
                
                // Defaults for arm cross / heel strike / vertical oscillation / overstride if not directly calculated by heuristic
                if (!isset($summary['arm_cross_pct'])) {
                    $summary['arm_cross_pct'] = 0.0;
                }
                if (!isset($summary['heel_strike_pct'])) {
                    $summary['heel_strike_pct'] = 50.0;
                }
                if (!isset($summary['vertical_oscillation'])) {
                    $summary['vertical_oscillation'] = 0.08;
                }
                if (!isset($summary['overstride_pct'])) {
                    $hasOverstride = false;
                    if (isset($summary['flags']) && is_array($summary['flags'])) {
                        foreach ($summary['flags'] as $flag) {
                            if (($flag['code'] ?? '') === 'possible_overstride') {
                                $hasOverstride = true;
                            }
                        }
                    }
                    $summary['overstride_pct'] = $hasOverstride ? 100.0 : 0.0;
                }
                if (!isset($summary['shin_angle_deg'])) {
                    $summary['shin_angle_deg'] = 5.0;
                }
            }

            if ($summary) {
                // RUN BIOMECHANICS V2 PROCESS
                $analysisService = app(\App\Services\RunningAnalysis\BiomechanicsAnalysisService::class);
                
                $summaryMetricMap = [
                    'confidence' => ['code' => 'DETECTION_CONFIDENCE', 'unit' => 'ratio'],
                    'samples' => ['code' => 'SAMPLES_COUNT', 'unit' => 'frames'],
                    'heel_strike_pct' => ['code' => 'HEEL_STRIKE_PCT', 'unit' => '%'],
                    'overstride_pct' => ['code' => 'OVERSTRIDE_PCT', 'unit' => '%'],
                    'shin_angle_deg' => ['code' => 'SHIN_ANGLE_DEG', 'unit' => 'deg'],
                    'knee_flex_deg' => ['code' => 'KNEE_FLEXION_DEG', 'unit' => 'deg'],
                    'trunk_lean_deg' => ['code' => 'TRUNK_LEAN_DEG', 'unit' => 'deg'],
                    'arm_cross_pct' => ['code' => 'ARM_CROSS_PCT', 'unit' => '%'],
                    'cadence_spm' => ['code' => 'CADENCE_SPM', 'unit' => 'spm'],
                    'elbow_angle_deg' => ['code' => 'ELBOW_ANGLE_DEG', 'unit' => 'deg'],
                    'vertical_oscillation' => ['code' => 'VERTICAL_OSCILLATION', 'unit' => 'ratio'],
                    'asymmetry' => ['code' => 'GCT_ASYMMETRY', 'unit' => 'ratio'],
                ];

                // 1. Process and save metrics
                foreach ($summary as $key => $val) {
                    if ($val === null || !is_numeric($val)) continue;
                    
                    $metricMeta = $summaryMetricMap[$key] ?? null;
                    if ($metricMeta) {
                        $trial->metrics()->create([
                            'metric_code' => $metricMeta['code'],
                            'category' => \App\Models\RunningAnalysis\Metric::CATEGORY_GENERAL,
                            'value_decimal' => $val,
                            'unit' => $metricMeta['unit'],
                            'confidence' => 1.0,
                            'calculation_version' => '2.0',
                        ]);
                    }
                }
                
                // Let's also run standard gait event detector & calculate basic GCT left/right/flight time for timeline sync
                $events = $this->eventDetector->detect($frames, $trial->inference_fps ?: 30.0);
                $strideIndex = 1;
                foreach ($events as $event) {
                    $trial->gaitEvents()->create([
                        'stride_index' => $strideIndex++,
                        'event_type' => $event['event_type'],
                        'side' => $event['side'],
                        'frame_index' => $event['frame'],
                        'timestamp_ms' => $event['timestamp_ms'],
                        'confidence' => 1.0,
                        'source' => \App\Models\RunningAnalysis\GaitEvent::SOURCE_AUTOMATIC,
                    ]);
                }
                $metricsData = $this->metricCalculator->calculate($events);
                if ($metricsData['contact_time_ms_left'] > 0) {
                    $trial->metrics()->firstOrCreate(
                        ['metric_code' => 'GCT_LEFT_MS'],
                        [
                            'category' => \App\Models\RunningAnalysis\Metric::CATEGORY_GENERAL,
                            'value_decimal' => $metricsData['contact_time_ms_left'],
                            'unit' => 'ms',
                            'confidence' => 1.0,
                            'calculation_version' => '2.0',
                        ]
                    );
                }
                if ($metricsData['contact_time_ms_right'] > 0) {
                    $trial->metrics()->firstOrCreate(
                        ['metric_code' => 'GCT_RIGHT_MS'],
                        [
                            'category' => \App\Models\RunningAnalysis\Metric::CATEGORY_GENERAL,
                            'value_decimal' => $metricsData['contact_time_ms_right'],
                            'unit' => 'ms',
                            'confidence' => 1.0,
                            'calculation_version' => '2.0',
                        ]
                    );
                }
                if ($metricsData['flight_time_ms'] > 0) {
                    $trial->metrics()->firstOrCreate(
                        ['metric_code' => 'FLIGHT_TIME_MS'],
                        [
                            'category' => \App\Models\RunningAnalysis\Metric::CATEGORY_GENERAL,
                            'value_decimal' => $metricsData['flight_time_ms'],
                            'unit' => 'ms',
                            'confidence' => 1.0,
                            'calculation_version' => '2.0',
                        ]
                    );
                }

                // 2. Perform Biomechanics Form Analysis
                $meta = [
                    'duration_seconds' => null,
                    'width' => $trial->camera_width,
                    'height' => $trial->camera_height,
                    'fps' => $trial->camera_fps,
                    'size_bytes' => null,
                    'runner_name' => optional($trial->runner)->name ?? 'Pelari',
                ];
                $analysisResult = $analysisService->analyze($summary, $meta);
                
                // 3. Save findings
                $savedFindings = [];
                foreach ($analysisResult['form_issues'] as $issue) {
                    $savedFindings[] = $trial->findings()->create([
                        'finding_code' => strtoupper($issue['code']),
                        'category' => 'general',
                        'severity' => $issue['severity'],
                        'confidence' => 1.0,
                        'evidence_json' => ['metric_value' => $issue['message']],
                        'explanation_key' => $issue['code'],
                        'ruleset_version' => '2.0',
                        'review_status' => \App\Models\RunningAnalysis\Finding::REVIEW_GENERATED,
                    ]);
                }
                
                // Save plans as recommendations linked to the first finding
                $findingId = count($savedFindings) > 0 ? $savedFindings[0]->id : null;
                
                foreach ($analysisResult['strength_plan'] as $plan) {
                    $trial->recommendations()->create([
                        'finding_id' => $findingId,
                        'recommendation_code' => strtoupper($plan['code']),
                        'type' => \App\Models\RunningAnalysis\Recommendation::TYPE_STRENGTH,
                        'title' => $plan['title'],
                        'description' => $plan['message'],
                        'priority' => 1,
                        'source' => \App\Models\RunningAnalysis\Recommendation::SOURCE_DETERMINISTIC,
                        'catalog_version' => '2.0',
                    ]);
                }
                
                foreach ($analysisResult['recovery_plan'] as $plan) {
                    $trial->recommendations()->create([
                        'finding_id' => $findingId,
                        'recommendation_code' => strtoupper($plan['code']),
                        'type' => \App\Models\RunningAnalysis\Recommendation::TYPE_CUE,
                        'title' => $plan['title'],
                        'description' => $plan['message'],
                        'priority' => 1,
                        'source' => \App\Models\RunningAnalysis\Recommendation::SOURCE_DETERMINISTIC,
                        'catalog_version' => '2.0',
                    ]);
                }

                foreach ($analysisResult['suggestions'] as $suggestion) {
                    $trial->recommendations()->create([
                        'finding_id' => $findingId,
                        'recommendation_code' => strtoupper($suggestion['code']),
                        'type' => \App\Models\RunningAnalysis\Recommendation::TYPE_DRILL,
                        'title' => $suggestion['title'],
                        'description' => $suggestion['message'],
                        'priority' => 2,
                        'source' => \App\Models\RunningAnalysis\Recommendation::SOURCE_DETERMINISTIC,
                        'catalog_version' => '2.0',
                    ]);
                }

                // Save report narrative (coach message and positives)
                \App\Models\RunningAnalysis\Report::updateOrCreate(
                    ['trial_id' => $trial->id],
                    [
                        'runner_id' => $trial->runner_id,
                        'report_version' => 2,
                        'status' => \App\Models\RunningAnalysis\Report::STATUS_DRAFT,
                        'deterministic_summary_json' => $analysisResult,
                        'runner_narrative_json' => [
                            'coach_message' => $analysisResult['coach_message'] ?? null,
                            'positives' => $analysisResult['positives'] ?? [],
                        ],
                        'disclaimer_version' => '1.0',
                        'published_at' => null,
                    ]
                );

                $trial->update([
                    'quality_score' => isset($analysisResult['score']) ? ($analysisResult['score'] / 100.0) : null,
                ]);
            } else {
                // FALLBACK TO LEGACY PROCESS FOR V1
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
                        'confidence' => 1.0,
                        'source' => \App\Models\RunningAnalysis\GaitEvent::SOURCE_AUTOMATIC,
                    ]);
                }

                // 3. Calculate Biomechanical metrics and Shoulder-Hip Ratio
                $metricsData = $this->metricCalculator->calculate($events);
                
                $totalRatio = 0;
                $ratioCount = 0;
                foreach ($frames as $frame) {
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
                
                // Save Metrics
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
                
                // Save Findings
                $savedFindings = [];
                foreach ($findingsData as $f) {
                    $savedFindings[] = $trial->findings()->create([
                        'finding_code' => strtoupper($f['type']),
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
            }

            // 6. Update Trial Status
            $trial->update(['status' => Trial::STATUS_REVIEW_REQUIRED]);

            return true;
        });
    }
}
