<?php

namespace Tests\Feature\RunningAnalysis;

use App\Models\RunningAnalysis\Session;
use App\Models\RunningAnalysis\Trial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Str;

class TrialControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_idempotent_trial()
    {
        $admin = User::factory()->admin()->create();
        $runner = User::factory()->runner()->create();
        $session = Session::factory()->create();
        
        $session->runners()->attach($runner->id, ['sequence_no' => 1, 'status' => 'pending']);

        $trialId = Str::uuid()->toString();

        $payload = [
            'id' => $trialId,
            'runner_id' => $runner->id,
            'camera_device_label' => 'Logitech Brio',
            'camera_width' => 1920,
            'camera_height' => 1080,
            'camera_fps' => 60,
            'pose_model' => 'pose_landmarker',
        ];

        // First call
        $response1 = $this->actingAs($admin)->postJson(route('admin.running-analysis.trials.store', $session), $payload);
        $response1->assertStatus(200);
        $response1->assertJson(['trial_id' => $trialId, 'status' => Trial::STATUS_CAPTURING]);

        $this->assertDatabaseHas('running_analysis_trials', [
            'id' => $trialId,
            'attempt_no' => 1,
        ]);

        // Second call (idempotent retry)
        $response2 = $this->actingAs($admin)->postJson(route('admin.running-analysis.trials.store', $session), $payload);
        $response2->assertStatus(200);
        $response2->assertJson(['trial_id' => $trialId]);

        // Still only 1 trial in DB
        $this->assertDatabaseCount('running_analysis_trials', 1);
    }

    public function test_can_upload_artifact_and_verify_checksum()
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $trial = Trial::factory()->create();
        
        $fileContent = '{"landmarks": []}';
        $file = UploadedFile::fake()->createWithContent('pose.json', $fileContent);
        $hash = hash('sha256', $fileContent);

        $response = $this->actingAs($admin)->postJson(route('admin.running-analysis.trials.artifacts.upload', $trial), [
            'type' => 'pose_landmarks',
            'file' => $file,
            'sha256' => $hash,
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('running_analysis_artifacts', [
            'trial_id' => $trial->id,
            'type' => 'pose_landmarks',
            'sha256' => $hash,
        ]);
    }

    public function test_rejects_artifact_with_invalid_checksum()
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $trial = Trial::factory()->create();
        
        $fileContent = '{"landmarks": []}';
        $file = UploadedFile::fake()->createWithContent('pose.json', $fileContent);

        $response = $this->actingAs($admin)->postJson(route('admin.running-analysis.trials.artifacts.upload', $trial), [
            'type' => 'pose_landmarks',
            'file' => $file,
            'sha256' => 'invalid-hash-string',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('running_analysis_artifacts', 0);
    }

    public function test_can_finalize_trial()
    {
        $admin = User::factory()->admin()->create();
        $session = Session::factory()->create();
        $runner = User::factory()->runner()->create();
        $session->runners()->attach($runner->id, ['sequence_no' => 1, 'status' => 'pending']);

        $trial = Trial::factory()->create([
            'session_id' => $session->id,
            'runner_id' => $runner->id,
            'status' => Trial::STATUS_CAPTURING
        ]);
        
        // Add required artifact
        $trial->artifacts()->create([
            'type' => 'pose_landmarks',
            'disk' => 'local',
            'path' => 'dummy/path',
            'mime_type' => 'application/json',
            'sha256' => 'hash',
            'size_bytes' => 100,
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.running-analysis.trials.finalize', $trial));
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('running_analysis_trials', [
            'id' => $trial->id,
            'status' => Trial::STATUS_QUEUED,
        ]);

        $this->assertDatabaseHas('running_analysis_session_runner', [
            'session_id' => $session->id,
            'runner_id' => $runner->id,
            'status' => 'captured',
        ]);
    }
}
