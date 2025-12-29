<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\MasterWorkout;
use App\Models\WorkoutVisibilityLog;
use Illuminate\Support\Facades\Route;

class CustomWorkoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_create_custom_workout()
    {
        $coach = User::factory()->create(['role' => 'coach']);

        $response = $this->actingAs($coach)->postJson(route('coach.custom-workouts.store'), [
            'title' => 'My Custom Interval',
            'type' => 'interval',
            'description' => 'Hard intervals',
            'default_distance' => 5,
            'is_public' => false
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('workout.title', 'My Custom Interval')
                 ->assertJsonPath('workout.coach_id', $coach->id);
        
        $this->assertDatabaseHas('master_workouts', [
            'title' => 'My Custom Interval',
            'coach_id' => $coach->id,
            'is_public' => false
        ]);
    }

    public function test_audit_log_created_when_public_workout_created()
    {
        $coach = User::factory()->create(['role' => 'coach']);

        $response = $this->actingAs($coach)->postJson(route('coach.custom-workouts.store'), [
            'title' => 'Public Workout',
            'type' => 'easy_run',
            'is_public' => true
        ]);

        $response->assertStatus(200);

        $workout = MasterWorkout::where('title', 'Public Workout')->first();

        $this->assertDatabaseHas('workout_visibility_logs', [
            'master_workout_id' => $workout->id,
            'user_id' => $coach->id,
            'old_visibility' => false,
            'new_visibility' => true
        ]);
    }

    public function test_access_control_private_workout()
    {
        $creator = User::factory()->create(['role' => 'coach']);
        $otherCoach = User::factory()->create(['role' => 'coach']);

        $workout = MasterWorkout::create([
            'title' => 'Private Workout',
            'type' => 'easy_run',
            'coach_id' => $creator->id,
            'is_public' => false
        ]);

        // Creator should see it
        $this->assertTrue(MasterWorkout::visibleFor($creator)->where('id', $workout->id)->exists());

        // Other coach should NOT see it
        $this->assertFalse(MasterWorkout::visibleFor($otherCoach)->where('id', $workout->id)->exists());
    }

    public function test_access_control_public_workout()
    {
        $creator = User::factory()->create(['role' => 'coach']);
        $otherCoach = User::factory()->create(['role' => 'coach']);

        $workout = MasterWorkout::create([
            'title' => 'Public Workout',
            'type' => 'easy_run',
            'coach_id' => $creator->id,
            'is_public' => true
        ]);

        // Creator should see it
        $this->assertTrue(MasterWorkout::visibleFor($creator)->where('id', $workout->id)->exists());

        // Other coach SHOULD see it
        $this->assertTrue(MasterWorkout::visibleFor($otherCoach)->where('id', $workout->id)->exists());
    }

    public function test_update_workout_visibility_logs()
    {
        $coach = User::factory()->create(['role' => 'coach']);

        // Create private workout
        $response = $this->actingAs($coach)->postJson(route('coach.custom-workouts.store'), [
            'title' => 'My Workout',
            'type' => 'easy_run',
            'is_public' => false
        ]);
        
        $workout = MasterWorkout::where('title', 'My Workout')->first();

        // Update to public
        $response = $this->actingAs($coach)->putJson(route('coach.custom-workouts.update', $workout), [
            'title' => 'My Workout',
            'type' => 'easy_run',
            'is_public' => true
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('workout_visibility_logs', [
            'master_workout_id' => $workout->id,
            'old_visibility' => false,
            'new_visibility' => true
        ]);
    }
}
