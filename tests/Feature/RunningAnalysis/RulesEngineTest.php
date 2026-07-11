<?php

namespace Tests\Feature\RunningAnalysis;

use App\Models\RunningAnalysis\Session;
use App\Models\RunningAnalysis\Trial;
use App\Models\User;
use App\Services\RunningAnalysis\ReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RulesEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_builder_orchestrates_analysis_correctly()
    {
        Storage::fake('local');

        $runner = User::factory()->runner()->create();
        $session = Session::factory()->create();
        $trial = Trial::factory()->create([
            'session_id' => $session->id,
            'runner_id' => $runner->id,
            'status' => Trial::STATUS_QUEUED,
        ]);

        // Generate synthetic MediaPipe frames (approx. 2 seconds at 30fps)
        $frames = [];
        for ($i = 0; $i < 60; $i++) {
            // Simulate a simple walking/running wave for the Y axis
            // Heel strike (Y goes down to ~1.0)
            $leftY = 0.5 + 0.4 * sin($i * 0.5);
            $rightY = 0.5 + 0.4 * sin(($i * 0.5) + M_PI); // Out of phase
            
            $frames[] = [
                // Left heel
                29 => ['x' => 0.5, 'y' => $leftY, 'z' => 0, 'visibility' => 0.9],
                // Right heel
                30 => ['x' => 0.5, 'y' => $rightY, 'z' => 0, 'visibility' => 0.9],
                // Left toe
                31 => ['x' => 0.5, 'y' => $leftY + 0.1, 'z' => 0, 'visibility' => 0.9],
                // Right toe
                32 => ['x' => 0.5, 'y' => $rightY + 0.1, 'z' => 0, 'visibility' => 0.9],
            ];
        }

        $poseJson = json_encode(['landmarks' => $frames]);
        
        $artifact = $trial->artifacts()->create([
            'type' => 'pose_landmarks',
            'disk' => 'local',
            'path' => 'running-analysis/' . $trial->id . '/pose.json',
            'mime_type' => 'application/json',
            'sha256' => hash('sha256', $poseJson),
            'size_bytes' => strlen($poseJson),
        ]);

        Storage::disk('local')->put($artifact->path, $poseJson);

        // Run the orchestrator
        $builder = app(ReportBuilder::class);
        $builder->process($trial);

        $trial->refresh();
        
        // Assertions
        $this->assertEquals(Trial::STATUS_REVIEW_REQUIRED, $trial->status);
        $this->assertTrue($trial->gaitEvents()->count() > 0);
        
        $this->assertDatabaseHas('running_analysis_metrics', [
            'trial_id' => $trial->id,
        ]);
        
        $this->assertTrue($trial->findings()->count() > 0);
        $this->assertTrue($trial->recommendations()->count() > 0);
    }

    public function test_report_builder_orchestrates_v2_analysis_correctly()
    {
        Storage::fake('local');

        $runner = User::factory()->runner()->create();
        $session = Session::factory()->create();
        $trial = Trial::factory()->create([
            'session_id' => $session->id,
            'runner_id' => $runner->id,
            'status' => Trial::STATUS_QUEUED,
        ]);

        $poseJson = json_encode([
            'summary' => [
                'confidence' => 0.95,
                'samples' => 120,
                'heel_strike_pct' => 15.5,
                'overstride_pct' => 8.2,
                'shin_angle_deg' => 5.4,
                'knee_flex_deg' => 22.1,
                'trunk_lean_deg' => 6.2,
                'arm_cross_pct' => 1.2,
                'cadence_spm' => 172.5,
                'elbow_angle_deg' => 95.0,
                'vertical_oscillation' => 0.08,
                'asymmetry' => 0.02,
            ],
            'landmarks' => []
        ]);
        
        $artifact = $trial->artifacts()->create([
            'type' => 'pose_landmarks',
            'disk' => 'local',
            'path' => 'running-analysis/' . $trial->id . '/pose.json',
            'mime_type' => 'application/json',
            'sha256' => hash('sha256', $poseJson),
            'size_bytes' => strlen($poseJson),
        ]);

        Storage::disk('local')->put($artifact->path, $poseJson);

        // Run the orchestrator
        $builder = app(ReportBuilder::class);
        $builder->process($trial);

        $trial->refresh();
        
        // Assertions
        $this->assertEquals(Trial::STATUS_REVIEW_REQUIRED, $trial->status);
        
        // Check database metrics
        $this->assertDatabaseHas('running_analysis_metrics', [
            'trial_id' => $trial->id,
            'metric_code' => 'HEEL_STRIKE_PCT',
            'value_decimal' => 15.5,
        ]);
        
        $this->assertDatabaseHas('running_analysis_metrics', [
            'trial_id' => $trial->id,
            'metric_code' => 'OVERSTRIDE_PCT',
            'value_decimal' => 8.2,
        ]);
        
        $this->assertDatabaseHas('running_analysis_metrics', [
            'trial_id' => $trial->id,
            'metric_code' => 'CADENCE_SPM',
            'value_decimal' => 172.5,
        ]);

        $this->assertTrue($trial->findings()->count() > 0);
        $this->assertTrue($trial->recommendations()->count() > 0);
    }
}
