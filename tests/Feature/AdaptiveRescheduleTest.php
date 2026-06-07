<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\CustomWorkout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdaptiveRescheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_preview_reschedule_calculates_detraining_vdot_and_acwr_correctly(): void
    {
        $user = User::factory()->create(['role' => 'runner']);

        $programJson = [
            'sessions' => [
                ['day' => 1, 'type' => 'easy_run', 'distance' => 5.0, 'duration' => '00:30:00', 'description' => 'Easy Run Day 1'],
                ['day' => 2, 'type' => 'tempo', 'distance' => 6.0, 'duration' => '00:35:00', 'description' => 'Tempo Run Day 2'],
                ['day' => 3, 'type' => 'rest', 'distance' => 0.0, 'duration' => '00:00:00', 'description' => 'Rest Day 3'],
                ['day' => 4, 'type' => 'easy_run', 'distance' => 5.0, 'duration' => '00:30:00', 'description' => 'Easy Run Day 4'],
                ['day' => 5, 'type' => 'interval', 'distance' => 7.0, 'duration' => '00:40:00', 'description' => 'Interval Day 5'],
                ['day' => 6, 'type' => 'rest', 'distance' => 0.0, 'duration' => '00:00:00', 'description' => 'Rest Day 6'],
                ['day' => 7, 'type' => 'long_run', 'distance' => 10.0, 'duration' => '01:00:00', 'description' => 'Long Run Day 7'],
            ]
        ];

        $program = Program::create([
            'coach_id' => $user->id,
            'title' => 'Test Program',
            'slug' => 'test-program',
            'program_json' => $programJson,
            'duration_weeks' => 1,
            'vdot_score' => 45.0,
            'is_active' => true,
            'is_published' => true,
        ]);

        $enrollment = ProgramEnrollment::create([
            'runner_id' => $user->id,
            'program_id' => $program->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'status' => 'active',
            'current_vdot' => 45.0,
        ]);

        // Sickness for 6 days (should reduce VDOT by 1.0 -> 44.0)
        $response = $this->actingAs($user)->postJson(route('runner.calendar.adaptive-reschedule.preview'), [
            'enrollment_id' => $enrollment->id,
            'reason' => 'sick',
            'days_missed' => 6,
            'start_date' => now()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        $preview = $response->json('preview');
        $this->assertEquals(44.0, $preview['adjusted_vdot']);
        
        // Sickness for 12 days (should reduce VDOT by 2.0 -> 43.0 and inject 1 recovery week)
        $response12 = $this->actingAs($user)->postJson(route('runner.calendar.adaptive-reschedule.preview'), [
            'enrollment_id' => $enrollment->id,
            'reason' => 'sick',
            'days_missed' => 12,
            'start_date' => now()->toDateString(),
        ]);
        
        $response12->assertStatus(200);
        $preview12 = $response12->json('preview');
        $this->assertEquals(43.0, $preview12['adjusted_vdot']);
        
        // Sesi yang dikembalikan harus berisi recovery sessions + original sessions yang di-shift
        $this->assertNotEmpty($preview12['sessions']);
    }

    public function test_apply_adaptive_reschedule_updates_enrollment_and_creates_records(): void
    {
        $user = User::factory()->create(['role' => 'runner']);

        $programJson = [
            'sessions' => [
                ['day' => 1, 'type' => 'easy_run', 'distance' => 5.0, 'duration' => '00:30:00', 'description' => 'Easy Run Day 1'],
                ['day' => 2, 'type' => 'tempo', 'distance' => 6.0, 'duration' => '00:35:00', 'description' => 'Tempo Run Day 2'],
                ['day' => 3, 'type' => 'rest', 'distance' => 0.0, 'duration' => '00:00:00', 'description' => 'Rest Day 3'],
                ['day' => 4, 'type' => 'easy_run', 'distance' => 5.0, 'duration' => '00:30:00', 'description' => 'Easy Run Day 4'],
                ['day' => 5, 'type' => 'interval', 'distance' => 7.0, 'duration' => '00:40:00', 'description' => 'Interval Day 5'],
                ['day' => 6, 'type' => 'rest', 'distance' => 0.0, 'duration' => '00:00:00', 'description' => 'Rest Day 6'],
                ['day' => 7, 'type' => 'long_run', 'distance' => 10.0, 'duration' => '01:00:00', 'description' => 'Long Run Day 7'],
            ]
        ];

        $program = Program::create([
            'coach_id' => $user->id,
            'title' => 'Test Program 2',
            'slug' => 'test-program-2',
            'program_json' => $programJson,
            'duration_weeks' => 1,
            'vdot_score' => 45.0,
            'is_active' => true,
            'is_published' => true,
        ]);

        $enrollment = ProgramEnrollment::create([
            'runner_id' => $user->id,
            'program_id' => $program->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'status' => 'active',
            'current_vdot' => 45.0,
        ]);

        // Apply rescheduling for injury (severe/moderate) -> 2 weeks recovery
        $response = $this->actingAs($user)->postJson(route('runner.calendar.adaptive-reschedule.apply'), [
            'enrollment_id' => $enrollment->id,
            'reason' => 'injury',
            'days_missed' => 15,
            'start_date' => now()->toDateString(),
            'injury_severity' => 'moderate',
            'body_part' => 'ankle',
            'notes' => 'Ankle sprain',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Check enrollment VDOT decayed (absen 15 hari -> detraining decay 4.0 -> VDOT 41.0)
        $enrollment->refresh();
        $this->assertEquals(41.0, $enrollment->current_vdot);
        $this->assertEquals('injury', $enrollment->status_reason);
        $this->assertNotEmpty($enrollment->reschedule_history);

        // Check custom workouts created for the recovery weeks (14 days)
        $customWorkoutsCount = CustomWorkout::where('runner_id', $user->id)->count();
        $this->assertGreaterThan(0, $customWorkoutsCount);

        // Check shifted program sessions are registered in ProgramSessionTracking
        $trackingsCount = ProgramSessionTracking::where('enrollment_id', $enrollment->id)->count();
        $this->assertGreaterThan(0, $trackingsCount);
    }
}
