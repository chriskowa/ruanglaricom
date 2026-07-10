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
}
