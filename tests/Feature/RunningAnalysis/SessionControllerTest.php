<?php

namespace Tests\Feature\RunningAnalysis;

use App\Models\RunningAnalysis\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_sessions_index()
    {
        $admin = User::factory()->admin()->create();
        Session::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.running-analysis.sessions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.running-analysis.sessions.index');
        $response->assertViewHas('sessions');
    }

    public function test_runner_cannot_view_sessions_index()
    {
        $runner = User::factory()->runner()->create();

        $response = $this->actingAs($runner)->get(route('admin.running-analysis.sessions.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_session()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.running-analysis.sessions.store'), [
            'name' => 'GBK Sunday Long Run',
            'location' => 'Gelora Bung Karno',
            'session_date' => now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('running_analysis_sessions', [
            'name' => 'GBK Sunday Long Run',
            'location' => 'Gelora Bung Karno',
            'status' => Session::STATUS_DRAFT,
        ]);

        $session = Session::first();
        $response->assertRedirect(route('admin.running-analysis.sessions.show', $session));
    }

    public function test_admin_can_add_runners_to_session()
    {
        $admin = User::factory()->admin()->create();
        $session = Session::factory()->create();
        
        $runner1 = User::factory()->runner()->create();
        $runner2 = User::factory()->runner()->create();

        $response = $this->actingAs($admin)->post(route('admin.running-analysis.sessions.runners.add', $session), [
            'runner_ids' => [$runner1->id, $runner2->id],
        ]);

        $response->assertRedirect(route('admin.running-analysis.sessions.show', $session));
        
        $this->assertDatabaseHas('running_analysis_session_runner', [
            'session_id' => $session->id,
            'runner_id' => $runner1->id,
            'sequence_no' => 1,
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('running_analysis_session_runner', [
            'session_id' => $session->id,
            'runner_id' => $runner2->id,
            'sequence_no' => 2,
            'status' => 'pending'
        ]);
    }

    public function test_admin_can_search_runners_via_ajax()
    {
        $admin = User::factory()->admin()->create();
        $session = Session::factory()->create();
        
        $runnerMatch = User::factory()->runner()->create(['name' => 'Budi Santoso', 'is_active' => true]);
        $runnerNoMatch = User::factory()->runner()->create(['name' => 'Charlie Chaplin', 'is_active' => true]);
        
        // Match search query
        $response = $this->actingAs($admin)->getJson(route('admin.running-analysis.sessions.runners.search', [
            'session' => $session->id,
            'q' => 'Budi'
        ]));
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Budi Santoso']);
        $response->assertJsonMissing(['name' => 'Charlie Chaplin']);
    }

    public function test_admin_can_remove_runner_from_session()
    {
        $admin = User::factory()->admin()->create();
        $session = Session::factory()->create();
        $runner = User::factory()->runner()->create();
        
        $session->runners()->attach($runner->id, ['sequence_no' => 1, 'status' => 'pending']);
        
        $response = $this->actingAs($admin)->delete(route('admin.running-analysis.sessions.runners.remove', [$session, $runner]));
        
        $response->assertRedirect(route('admin.running-analysis.sessions.show', $session));
        $this->assertDatabaseMissing('running_analysis_session_runner', [
            'session_id' => $session->id,
            'runner_id' => $runner->id,
        ]);
    }
}
