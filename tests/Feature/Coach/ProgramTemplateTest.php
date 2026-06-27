<?php

namespace Tests\Feature\Coach;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_generate_program_template_with_time_trials()
    {
        $coach = User::factory()->create(['role' => 'coach']);

        $response = $this->actingAs($coach)->postJson(route('coach.programs.generate-template'), [
            'duration_weeks' => 4,
        ]);

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertArrayHasKey('duration_weeks', $data);
        $this->assertArrayHasKey('sessions', $data);
        $this->assertEquals(4, $data['duration_weeks']);
        $this->assertCount(28, $data['sessions']);

        // Check a normal tempo session on Week 1 Day 4 (Day 4)
        $day4 = collect($data['sessions'])->firstWhere('day', 4);
        $this->assertNotNull($day4);
        $this->assertEquals('tempo', $day4['type']);
        $this->assertNotNull($day4['advanced_config']);
        
        $cfg4 = json_decode($day4['advanced_config'], true);
        $this->assertEquals('tempo', $cfg4['type']);
        $this->assertEquals(8, $cfg4['tempo']['distance']);

        // Check the time_trial session on Week 4 Day 4 (Day 25)
        $day25 = collect($data['sessions'])->firstWhere('day', 25);
        $this->assertNotNull($day25);
        $this->assertEquals('time_trial', $day25['type']);
        $this->assertEquals(5, $day25['distance']);
        $this->assertEquals('00:20:00', $day25['duration']);
        $this->assertStringContainsString('Time Trial 5K Max Effort', $day25['description']);
        $this->assertNotNull($day25['advanced_config']);

        $cfg25 = json_decode($day25['advanced_config'], true);
        $this->assertEquals('time_trial', $cfg25['type']);
        $this->assertEquals('Time Trial 5K', $cfg25['title']);
        $this->assertTrue($cfg25['warmup']['enabled']);
        $this->assertEquals('max_effort', $cfg25['timeTrial']['effort']);
        $this->assertEquals(5, $cfg25['timeTrial']['distance']);
        $this->assertEquals('km', $cfg25['timeTrial']['unit']);
    }
}
