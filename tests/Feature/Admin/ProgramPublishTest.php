<?php

namespace Tests\Feature\Admin;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramPublishTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $coach;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->coach = User::factory()->create([
            'role' => 'coach',
        ]);
    }

    public function test_admin_can_view_programs_and_see_publish_status()
    {
        $publishedProgram = Program::create([
            'title' => 'Program 10K Published',
            'slug' => 'program-10k-published',
            'duration_weeks' => 8,
            'price' => 50000,
            'coach_id' => $this->coach->id,
            'program_json' => '{}',
            'is_published' => true,
        ]);

        $draftProgram = Program::create([
            'title' => 'Program 5K Draft',
            'slug' => 'program-5k-draft',
            'duration_weeks' => 4,
            'price' => 0,
            'coach_id' => $this->coach->id,
            'program_json' => '{}',
            'is_published' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.programs.index'));

        $response->assertStatus(200);
        $response->assertSee('MANAJEMEN PROGRAM LARI');
        $response->assertSee($publishedProgram->title);
        $response->assertSee($draftProgram->title);
        $response->assertSee('Published');
        $response->assertSee('Draft');
    }

    public function test_admin_can_filter_programs_by_publish_status()
    {
        $publishedProgram = Program::create([
            'title' => 'Program Published Only',
            'slug' => 'program-published-only',
            'duration_weeks' => 8,
            'price' => 50000,
            'coach_id' => $this->coach->id,
            'program_json' => '{}',
            'is_published' => true,
        ]);

        $draftProgram = Program::create([
            'title' => 'Program Draft Only',
            'slug' => 'program-draft-only',
            'duration_weeks' => 4,
            'price' => 0,
            'coach_id' => $this->coach->id,
            'program_json' => '{}',
            'is_published' => false,
        ]);

        // Filter Published
        $responsePublished = $this->actingAs($this->admin)->get(route('admin.programs.index', ['published' => '1']));
        $responsePublished->assertStatus(200);
        $responsePublished->assertSee($publishedProgram->title);
        $responsePublished->assertDontSee($draftProgram->title);

        // Filter Draft
        $responseDraft = $this->actingAs($this->admin)->get(route('admin.programs.index', ['published' => '0']));
        $responseDraft->assertStatus(200);
        $responseDraft->assertSee($draftProgram->title);
        $responseDraft->assertDontSee($publishedProgram->title);
    }

    public function test_admin_can_toggle_publish_status()
    {
        $program = Program::create([
            'title' => 'Test Toggle Program',
            'slug' => 'test-toggle-program',
            'duration_weeks' => 6,
            'price' => 75000,
            'coach_id' => $this->coach->id,
            'program_json' => '{}',
            'is_published' => false,
        ]);

        // Toggle to publish
        $response = $this->actingAs($this->admin)->post(route('admin.programs.toggle-publish', $program));
        $response->assertRedirect();
        $this->assertTrue($program->fresh()->is_published);

        // Toggle to unpublish
        $response2 = $this->actingAs($this->admin)->post(route('admin.programs.toggle-publish', $program));
        $response2->assertRedirect();
        $this->assertFalse($program->fresh()->is_published);
    }

    public function test_admin_can_toggle_publish_via_json()
    {
        $program = Program::create([
            'title' => 'JSON Toggle Program',
            'slug' => 'json-toggle-program',
            'duration_weeks' => 6,
            'price' => 75000,
            'coach_id' => $this->coach->id,
            'program_json' => '{}',
            'is_published' => false,
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.programs.toggle-publish', $program));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_published' => true,
        ]);
        $this->assertTrue($program->fresh()->is_published);
    }

    public function test_non_admin_cannot_toggle_publish()
    {
        $runner = User::factory()->create(['role' => 'runner']);
        $program = Program::create([
            'title' => 'Restricted Program',
            'slug' => 'restricted-program',
            'duration_weeks' => 6,
            'price' => 75000,
            'coach_id' => $this->coach->id,
            'program_json' => '{}',
            'is_published' => false,
        ]);

        $response = $this->actingAs($runner)->post(route('admin.programs.toggle-publish', $program));
        $response->assertStatus(403);
        $this->assertFalse($program->fresh()->is_published);
    }
}
