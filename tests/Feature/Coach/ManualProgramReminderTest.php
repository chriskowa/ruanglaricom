<?php

namespace Tests\Feature\Coach;

use App\Models\User;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\CustomWorkout;
use App\Mail\ProgramReminderMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ManualProgramReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_coach_can_send_program_reminder_via_email()
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $runner = User::factory()->create(['role' => 'runner', 'email' => 'runner@example.com', 'phone' => '08123456789']);
        
        $program = Program::create([
            'title' => 'Half Marathon Prep',
            'slug' => 'half-marathon-prep',
            'description' => 'Preparation for half marathon',
            'duration_weeks' => 12,
            'coach_id' => $coach->id,
            'is_active' => true,
            'program_json' => [
                'sessions' => [
                    [
                        'day' => 1,
                        'type' => 'easy_run',
                        'distance' => 5,
                        'description' => 'Easy run 5km'
                    ]
                ]
            ],
        ]);

        $enrollment = ProgramEnrollment::create([
            'program_id' => $program->id,
            'runner_id' => $runner->id,
            'status' => 'active',
            'start_date' => '2026-08-01',
            'end_date' => '2026-10-24',
        ]);

        $response = $this->actingAs($coach)->post(route('coach.athletes.send-reminder', $enrollment->id), [
            'session_day' => 1,
            'channel' => 'email',
            'custom_message' => 'Semangat latihan besok ya!',
        ]);

        $response->assertJson(['success' => true]);
        
        Mail::assertQueued(ProgramReminderMail::class, function ($mail) use ($runner) {
            return $mail->hasTo($runner->email) && $mail->customMessage === 'Semangat latihan besok ya!';
        });
    }

    public function test_coach_cannot_send_reminder_for_other_coach_athlete()
    {
        $coachA = User::factory()->create(['role' => 'coach']);
        $coachB = User::factory()->create(['role' => 'coach']);
        $runner = User::factory()->create(['role' => 'runner', 'email' => 'runner@example.com', 'phone' => '08123456789']);
        
        $program = Program::create([
            'title' => 'Half Marathon Prep',
            'slug' => 'half-marathon-prep',
            'description' => 'Preparation for half marathon',
            'duration_weeks' => 12,
            'coach_id' => $coachB->id,
            'is_active' => true,
            'program_json' => [
                'sessions' => [
                    [
                        'day' => 1,
                        'type' => 'easy_run',
                        'distance' => 5,
                        'description' => 'Easy run 5km'
                    ]
                ]
            ],
        ]);

        $enrollment = ProgramEnrollment::create([
            'program_id' => $program->id,
            'runner_id' => $runner->id,
            'status' => 'active',
            'start_date' => '2026-08-01',
            'end_date' => '2026-10-24',
        ]);

        $response = $this->actingAs($coachA)->post(route('coach.athletes.send-reminder', $enrollment->id), [
            'session_day' => 1,
            'channel' => 'email',
            'custom_message' => 'Semangat latihan besok ya!',
        ]);

        $response->assertStatus(403);
        Mail::assertNotSent(ProgramReminderMail::class);
    }

    public function test_coach_can_send_program_reminder_with_auto_resolved_session()
    {
        \Illuminate\Support\Carbon::setTestNow('2026-08-02');

        $coach = User::factory()->create(['role' => 'coach']);
        $runner = User::factory()->create(['role' => 'runner', 'email' => 'runner@example.com', 'phone' => '08123456789']);
        
        $program = Program::create([
            'title' => 'Half Marathon Prep',
            'slug' => 'half-marathon-prep',
            'description' => 'Preparation for half marathon',
            'duration_weeks' => 12,
            'coach_id' => $coach->id,
            'is_active' => true,
            'program_json' => [
                'sessions' => [
                    [
                        'day' => 1,
                        'type' => 'easy_run',
                        'distance' => 5,
                        'description' => 'Easy run 5km'
                    ],
                    [
                        'day' => 3,
                        'type' => 'tempo_run',
                        'distance' => 8,
                        'description' => 'Tempo run 8km'
                    ]
                ]
            ],
        ]);

        $enrollment = ProgramEnrollment::create([
            'program_id' => $program->id,
            'runner_id' => $runner->id,
            'status' => 'active',
            'start_date' => '2026-08-01', // Day 1 is Aug 1, Day 2 is Aug 2, Day 3 is Aug 3 (tomorrow)
            'end_date' => '2026-10-24',
        ]);

        $response = $this->actingAs($coach)->post(route('coach.athletes.send-reminder', $enrollment->id), [
            'channel' => 'email',
        ]);

        $response->assertJson(['success' => true]);
        
        Mail::assertQueued(ProgramReminderMail::class, function ($mail) use ($runner) {
            return $mail->hasTo($runner->email) && $mail->sessionData['type'] === 'tempo_run' && $mail->sessionData['distance'] == 8;
        });

        \Illuminate\Support\Carbon::setTestNow(); // Reset time
    }
}
