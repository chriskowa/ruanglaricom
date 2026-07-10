<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Jobs\SendProgramReminderJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WhatsAppNotificationToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_toggle_wa_notification()
    {
        $response = $this->postJson(route('runner.profile.toggle-wa'), [
            'is_receive_wa' => false,
        ]);

        $response->assertStatus(401);
    }

    public function test_runner_can_toggle_wa_notification()
    {
        $user = User::factory()->create([
            'role' => 'runner',
            'is_receive_wa' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('runner.profile.toggle-wa'), [
            'is_receive_wa' => false,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_receive_wa' => false,
        ]);

        $this->assertFalse($user->fresh()->is_receive_wa);

        $response = $this->actingAs($user)->postJson(route('runner.profile.toggle-wa'), [
            'is_receive_wa' => true,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_receive_wa' => true,
        ]);

        $this->assertTrue($user->fresh()->is_receive_wa);
    }

    public function test_toggle_wa_requires_boolean_value()
    {
        $user = User::factory()->create([
            'role' => 'runner',
        ]);

        $response = $this->actingAs($user)->postJson(route('runner.profile.toggle-wa'), [
            'is_receive_wa' => 'not-a-boolean',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['is_receive_wa']);
    }

    public function test_cronjob_filters_reminders_by_is_receive_wa()
    {
        Queue::fake();

        // 1. Create a coach
        $coach = User::factory()->create([
            'role' => 'coach',
        ]);

        // 2. Create a program
        $program = Program::create([
            'title' => 'Test Program',
            'slug' => 'test-program',
            'description' => 'Test Description',
            'duration_weeks' => 4,
            'coach_id' => $coach->id,
            'program_json' => [
                'sessions' => [
                    [
                        'day' => 1,
                        'type' => 'Interval',
                        'distance' => 5,
                    ]
                ]
            ],
            'is_active' => true,
        ]);

        // 3. Create runner A (wa enabled) with active enrollment starting tomorrow
        $runnerA = User::factory()->create([
            'role' => 'runner',
            'phone' => '6281234567890',
            'is_receive_wa' => true,
        ]);
        ProgramEnrollment::create([
            'runner_id' => $runnerA->id,
            'program_id' => $program->id,
            'status' => 'active',
            'start_date' => Carbon::tomorrow(),
        ]);

        // 4. Create runner B (wa disabled) with active enrollment starting tomorrow
        $runnerB = User::factory()->create([
            'role' => 'runner',
            'phone' => '6289999999999',
            'is_receive_wa' => false,
        ]);
        ProgramEnrollment::create([
            'runner_id' => $runnerB->id,
            'program_id' => $program->id,
            'status' => 'active',
            'start_date' => Carbon::tomorrow(),
        ]);

        // 5. Run the scheduled reminders command
        $this->artisan('programs:schedule-reminders')
            ->assertExitCode(0);

        // 6. Assert only Runner A's reminder job was dispatched
        Queue::assertPushed(SendProgramReminderJob::class, function ($job) use ($runnerA) {
            return $job->user->id === $runnerA->id;
        });

        Queue::assertNotPushed(SendProgramReminderJob::class, function ($job) use ($runnerB) {
            return $job->user->id === $runnerB->id;
        });
    }
}
