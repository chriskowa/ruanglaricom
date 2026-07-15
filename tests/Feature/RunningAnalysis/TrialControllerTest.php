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
        \Illuminate\Support\Facades\Queue::fake();

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

    public function test_publishing_trial_creates_notification_and_lets_runner_review_and_stream()
    {
        $admin = User::factory()->admin()->create();
        $runner = User::factory()->runner()->create();
        $otherRunner = User::factory()->runner()->create();
        $session = Session::factory()->create();

        $trial = Trial::factory()->create([
            'session_id' => $session->id,
            'runner_id'  => $runner->id,
            'status'     => Trial::STATUS_REVIEW_REQUIRED,
        ]);

        // 1. Approve & Publish
        $response = $this->actingAs($admin)->post(route('admin.running-analysis.trials.approve', $trial));
        $response->assertRedirect(route('admin.running-analysis.trials.review', $trial));

        $this->assertDatabaseHas('running_analysis_trials', [
            'id'     => $trial->id,
            'status' => Trial::STATUS_PUBLISHED,
        ]);

        // Verify Notification is created in database
        $this->assertDatabaseHas('notifications', [
            'user_id'        => $runner->id,
            'type'           => 'running_analysis',
            'reference_type' => Trial::class,
            'reference_id'   => $trial->id,
        ]);

        // 2. Runner Views Review Page
        $response = $this->actingAs($runner)->get(route('runner.running-analysis.trials.review', $trial));
        $response->assertStatus(200);

        // 3. Other Runner Is Denied Access
        $response = $this->actingAs($otherRunner)->get(route('runner.running-analysis.trials.review', $trial));
        $response->assertStatus(403);

        // 4. Trial is unpublished - Denied access
        $unpublishedTrial = Trial::factory()->create([
            'session_id' => $session->id,
            'runner_id'  => $runner->id,
            'status'     => Trial::STATUS_REVIEW_REQUIRED,
        ]);
        $response = $this->actingAs($runner)->get(route('runner.running-analysis.trials.review', $unpublishedTrial));
        $response->assertStatus(403);

        // 5. Runner serves artifact
        Storage::fake('local');
        $artifact = $trial->artifacts()->create([
            'type'       => 'video_clip',
            'disk'       => 'local',
            'path'       => 'dummy.mp4',
            'mime_type'  => 'video/mp4',
            'sha256'     => 'hash',
            'size_bytes' => 100,
        ]);
        Storage::disk('local')->put('dummy.mp4', 'dummy data');

        $response = $this->actingAs($runner)->get(route('runner.running-analysis.trials.artifact', [$trial, $artifact]));
        $response->assertStatus(200);

        // Other runner is denied artifact
        $response = $this->actingAs($otherRunner)->get(route('runner.running-analysis.trials.artifact', [$trial, $artifact]));
        $response->assertStatus(403);
    }


    public function test_runner_dashboard_compiles_successfully()
    {
        $runner = User::factory()->runner()->create();
        $response = $this->actingAs($runner)->get(route('runner.dashboard'));
        $response->assertStatus(200);
    }

    public function test_admin_can_download_pdf_for_trial()
    {
        $admin  = User::factory()->admin()->create();
        $runner = User::factory()->runner()->create();
        $session = Session::factory()->create();
        $session->runners()->attach($runner->id, ['sequence_no' => 1, 'status' => 'pending']);

        $trial = Trial::factory()->create([
            'session_id' => $session->id,
            'runner_id'  => $runner->id,
            'operator_id'=> $admin->id,
            'status'     => Trial::STATUS_PUBLISHED,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.running-analysis.trials.pdf', $trial));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        $disposition = $response->headers->get('Content-Disposition') ?? '';
        $this->assertStringContainsString('.pdf', $disposition,
            'Content-Disposition header should contain .pdf filename');
    }

    public function test_runner_can_download_own_pdf_report()
    {
        $admin  = User::factory()->admin()->create();
        $runner = User::factory()->runner()->create();
        $session = Session::factory()->create();
        $session->runners()->attach($runner->id, ['sequence_no' => 1, 'status' => 'pending']);

        $trial = Trial::factory()->create([
            'session_id' => $session->id,
            'runner_id'  => $runner->id,
            'operator_id'=> $admin->id,
            'status'     => Trial::STATUS_PUBLISHED,
        ]);

        $response = $this->actingAs($runner)
            ->get(route('runner.running-analysis.trials.pdf', $trial));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_other_runner_cannot_download_pdf_report()
    {
        $admin  = User::factory()->admin()->create();
        $runner = User::factory()->runner()->create();
        $otherRunner = User::factory()->runner()->create();
        $session = Session::factory()->create();
        $session->runners()->attach($runner->id, ['sequence_no' => 1, 'status' => 'pending']);

        $trial = Trial::factory()->create([
            'session_id' => $session->id,
            'runner_id'  => $runner->id,
            'operator_id'=> $admin->id,
            'status'     => Trial::STATUS_PUBLISHED,
        ]);

        $response = $this->actingAs($otherRunner)
            ->get(route('runner.running-analysis.trials.pdf', $trial));

        $response->assertStatus(403);
    }
}
