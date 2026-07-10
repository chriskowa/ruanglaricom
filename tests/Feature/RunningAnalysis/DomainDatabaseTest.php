<?php

namespace Tests\Feature\RunningAnalysis;

use App\Models\RunningAnalysis\Session;
use App\Models\RunningAnalysis\Trial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_session_and_trial_with_relationships()
    {
        // Setup users
        $admin = User::factory()->admin()->create();
        $runner = User::factory()->runner()->create();
        
        // Create session
        $session = Session::factory()->create([
            'created_by' => $admin->id,
            'name'       => 'Test Capture Session',
        ]);

        $this->assertDatabaseHas('running_analysis_sessions', [
            'id' => $session->id,
            'name' => 'Test Capture Session',
        ]);

        // Attach runner to session
        $session->runners()->attach($runner->id, [
            'status' => 'pending',
            'sequence_no' => 1
        ]);

        $this->assertDatabaseHas('running_analysis_session_runner', [
            'session_id' => $session->id,
            'runner_id' => $runner->id,
            'status' => 'pending',
        ]);

        // Create trial
        $trialId = \Illuminate\Support\Str::uuid()->toString();
        $trial = Trial::factory()->create([
            'id' => $trialId,
            'session_id' => $session->id,
            'runner_id' => $runner->id,
            'operator_id' => $admin->id,
            'direction' => Trial::DIRECTION_LEFT_TO_RIGHT,
            'status' => Trial::STATUS_CREATED,
        ]);

        $this->assertDatabaseHas('running_analysis_trials', [
            'id' => $trialId,
            'runner_id' => $runner->id,
            'status' => Trial::STATUS_CREATED,
        ]);

        $this->assertTrue($trial->session->is($session));
        $this->assertTrue($trial->runner->is($runner));
        $this->assertTrue($trial->operator->is($admin));
    }
}
