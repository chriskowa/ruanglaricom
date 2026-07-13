<?php

namespace Tests\Feature\Coach;

use App\Models\User;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualAthleteEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_manually_enroll_runner_to_program_bag()
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $program = Program::create([
            'title' => 'Half Marathon Prep',
            'slug' => 'half-marathon-prep',
            'description' => 'Preparation for half marathon',
            'duration_weeks' => 12,
            'coach_id' => $coach->id,
            'is_active' => true,
            'program_json' => ['sessions' => []],
        ]);

        $response = $this->actingAs($coach)->post(route('coach.athletes.enroll'), [
            'program_id' => $program->id,
            'name' => 'Runner Test',
            'email' => 'runnertest@example.com',
            'phone' => '08123456789',
            'start_date' => '2026-08-01',
            'vdot_mode' => 'direct',
            'vdot' => '45',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Runner user record must exist
        $runner = User::where('email', 'runnertest@example.com')->first();
        $this->assertNotNull($runner);

        // Enrollment should be in Program Bag (status = purchased, no start/end date)
        $enrollment = ProgramEnrollment::where('program_id', $program->id)
            ->where('runner_id', $runner->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertEquals('purchased', $enrollment->status);
        $this->assertNull($enrollment->start_date);
        $this->assertNull($enrollment->end_date);
        $this->assertEquals('paid', $enrollment->payment_status);

        // VDOT score should be stored on enrollment
        $this->assertNotNull($enrollment->current_vdot);
        $this->assertGreaterThan(0, $enrollment->current_vdot);
    }

    public function test_coach_cannot_enroll_runner_to_other_coach_program()
    {
        $coachA = User::factory()->create(['role' => 'coach']);
        $coachB = User::factory()->create(['role' => 'coach']);
        $programB = Program::create([
            'title' => 'Coach B Program',
            'slug' => 'coach-b-program',
            'description' => 'Program by Coach B',
            'duration_weeks' => 12,
            'coach_id' => $coachB->id,
            'is_active' => true,
            'program_json' => ['sessions' => []],
        ]);

        $response = $this->actingAs($coachA)->post(route('coach.athletes.enroll'), [
            'program_id' => $programB->id,
            'name' => 'Runner Test B',
            'email' => 'runnertestb@example.com',
            'phone' => '08123456789',
            'start_date' => '2026-08-01',
            'vdot_mode' => 'direct',
            'vdot' => '40',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Runner should NOT have been created
        $runner = User::where('email', 'runnertestb@example.com')->first();
        $this->assertNull($runner);
    }
}
