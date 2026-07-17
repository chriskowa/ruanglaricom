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

        // 4b. Trial is approved - Allowed access
        $approvedTrial = Trial::factory()->create([
            'session_id' => $session->id,
            'runner_id'  => $runner->id,
            'status'     => Trial::STATUS_APPROVED,
        ]);
        $response = $this->actingAs($runner)->get(route('runner.running-analysis.trials.review', $approvedTrial));
        $response->assertStatus(200);

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

    public function test_serve_s3_artifact_redirects_to_temporary_url()
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

        $artifact = $trial->artifacts()->create([
            'type'       => 'video_clip',
            'disk'       => 's3',
            'path'       => 'trials/video.mp4',
            'sha256'     => 'dummy',
            'mime_type'  => 'video/mp4',
            'size_bytes' => 1024,
            'created_at' => now(),
        ]);

        $mockDisk = \Mockery::mock(\Illuminate\Contracts\Filesystem\Filesystem::class);
        $mockDisk->shouldReceive('exists')->with('trials/video.mp4')->andReturn(true);
        $mockDisk->shouldReceive('temporaryUrl')
            ->with('trials/video.mp4', \Mockery::type(\DateTimeInterface::class))
            ->andReturn('https://s3.amazonaws.com/ruanglari/trials/video.mp4?signature=dummy');

        Storage::shouldReceive('disk')->with('s3')->andReturn($mockDisk);

        $response = $this->actingAs($admin)
            ->get(route('admin.running-analysis.trials.artifact', [$trial, $artifact]));

        $response->assertRedirect('https://s3.amazonaws.com/ruanglari/trials/video.mp4?signature=dummy');
    }

    public function test_deleting_runner_deletes_associated_trials_and_physical_files()
    {
        Storage::fake('local');

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

        Storage::disk('local')->put('running-analysis/' . $trial->id . '/video.webm', 'dummy contents');

        $artifact = $trial->artifacts()->create([
            'type'       => 'video_clip',
            'disk'       => 'local',
            'path'       => 'running-analysis/' . $trial->id . '/video.webm',
            'sha256'     => hash('sha256', 'dummy contents'),
            'mime_type'  => 'video/webm',
            'size_bytes' => 14,
            'created_at' => now(),
        ]);

        Storage::disk('local')->assertExists('running-analysis/' . $trial->id . '/video.webm');

        // Delete the runner user
        $runner->delete();

        // Assert that the trial database record is gone
        $this->assertDatabaseMissing('running_analysis_trials', ['id' => $trial->id]);
        $this->assertDatabaseMissing('running_analysis_artifacts', ['id' => $artifact->id]);

        // Assert that the physical file is deleted from local storage
        Storage::disk('local')->assertMissing('running-analysis/' . $trial->id . '/video.webm');
    }

    public function test_can_finalize_trial_synchronously()
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
        
        // Save dummy landmark JSON to disk so the ReportBuilder can read it
        Storage::fake('local');
        Storage::disk('local')->put('dummy/landmarks.json', json_encode([
            'metadata' => [
                'video_duration_sec' => 3.5,
                'direction' => 'right',
                'original_fps' => 30,
            ],
            'landmarks' => [
                // minimum dummy frame
                [
                    'time_ms' => 0,
                    'keypoints' => [
                        'left_hip' => ['x' => 0.5, 'y' => 0.5, 'z' => 0.0, 'visibility' => 0.99],
                        'left_knee' => ['x' => 0.5, 'y' => 0.7, 'z' => 0.0, 'visibility' => 0.99],
                        'left_ankle' => ['x' => 0.5, 'y' => 0.9, 'z' => 0.0, 'visibility' => 0.99],
                        'left_shoulder' => ['x' => 0.55, 'y' => 0.3, 'z' => 0.0, 'visibility' => 0.99],
                        'left_ear' => ['x' => 0.55, 'y' => 0.2, 'z' => 0.0, 'visibility' => 0.99],
                        'left_heel' => ['x' => 0.5, 'y' => 0.92, 'z' => 0.0, 'visibility' => 0.99],
                        'left_foot_index' => ['x' => 0.48, 'y' => 0.95, 'z' => 0.0, 'visibility' => 0.99],
                        'right_hip' => ['x' => 0.5, 'y' => 0.5, 'z' => 0.0, 'visibility' => 0.99],
                        'right_knee' => ['x' => 0.5, 'y' => 0.7, 'z' => 0.0, 'visibility' => 0.99],
                        'right_ankle' => ['x' => 0.5, 'y' => 0.9, 'z' => 0.0, 'visibility' => 0.99],
                        'right_shoulder' => ['x' => 0.55, 'y' => 0.3, 'z' => 0.0, 'visibility' => 0.99],
                        'right_ear' => ['x' => 0.55, 'y' => 0.2, 'z' => 0.0, 'visibility' => 0.99],
                        'right_heel' => ['x' => 0.5, 'y' => 0.92, 'z' => 0.0, 'visibility' => 0.99],
                        'right_foot_index' => ['x' => 0.48, 'y' => 0.95, 'z' => 0.0, 'visibility' => 0.99],
                    ]
                ]
            ],
            'summary' => [
                'cadence_spm' => ['value' => 170.0],
                'torso_angle_deg' => ['mean' => 5.2],
                'knee_flexion_deg' => ['mean' => 35.0],
                'overstride_landing_m' => ['mean' => 0.02],
                'overall_score' => 88.0,
                'positive_points' => ['Good knee pull', 'Consistent stride'],
                'recommendations' => [
                    ['category' => 'strength', 'title' => 'Plank', 'description' => 'Planks hold for core strength'],
                    ['category' => 'drills', 'title' => 'A-skips', 'description' => 'A-skips for knee drive'],
                    ['category' => 'cues', 'title' => 'Lean forward', 'description' => 'Lean slightly forward from ankles']
                ]
            ]
        ]));

        $trial->artifacts()->create([
            'type' => 'pose_landmarks',
            'disk' => 'local',
            'path' => 'dummy/landmarks.json',
            'mime_type' => 'application/json',
            'sha256' => hash('sha256', 'dummy'),
            'size_bytes' => 100,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.running-analysis.trials.finalize', $trial) . '?sync=true');
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('running_analysis_trials', [
            'id' => $trial->id,
            'status' => Trial::STATUS_REVIEW_REQUIRED, // should bypass queued/analyzing
        ]);

        $this->assertDatabaseHas('running_analysis_session_runner', [
            'session_id' => $session->id,
            'runner_id' => $runner->id,
            'status' => 'captured',
        ]);
    }
}
