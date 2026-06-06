<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelfGeneratedProgramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock OpenAI API calls to make tests fast, deterministic, and independent of external API keys
        $this->mock(OpenAiService::class, function ($mock) {
            $mock->shouldReceive('getAiResponseOrThrow')
                ->zeroOrMoreTimes()
                ->andReturn(json_encode([
                    'templates' => [
                        'Base_easy_run' => 'Lari santai {distance_km} km @ {target_pace} menit/km. Jaga napas ritmis.',
                        'Base_long_run' => 'Lari panjang {distance_km} km @ {target_pace} menit/km. Fokus ketahanan dasar.',
                        'Base_rest' => 'Istirahat total dan pemulihan aktif.',
                        'Strength_easy_run' => 'Lari pemulihan {distance_km} km @ {target_pace} menit/km.',
                        'Strength_long_run' => 'Lari panjang {distance_km} km @ {target_pace} menit/km.',
                        'Strength_repetition' => 'Drill kecepatan {distance_km} km @ {target_pace} menit/km.',
                        'Strength_threshold' => 'Tempo run {distance_km} km @ {target_pace} menit/km.',
                        'Strength_rest' => 'Istirahat total.',
                        'Speed_easy_run' => 'Lari santai pemulihan {distance_km} km @ {target_pace} menit/km.',
                        'Speed_long_run' => 'Lari panjang progresif {distance_km} km @ {target_pace} menit/km.',
                        'Speed_interval' => 'Interval intensitas tinggi {distance_km} km @ {target_pace} menit/km.',
                        'Speed_rest' => 'Istirahat total.',
                        'Taper_easy_run' => 'Lari santai menjelang lomba {distance_km} km @ {target_pace} menit/km.',
                        'Taper_long_run' => 'Lari simulasi lomba {distance_km} km @ {target_pace} menit/km.',
                        'Taper_threshold' => 'Tempo ringan {distance_km} km @ {target_pace} menit/km.',
                        'Taper_rest' => 'Istirahat total bersiap untuk hari lomba.'
                    ]
                ]));
        });
    }

    public function test_generator_validates_required_parameters(): void
    {
        $response = $this->postJson(route('generator.generate'), []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['pb_distance', 'pb_time', 'target_distance', 'target_date', 'goal_time', 'weekly_mileage', 'frequency', 'runner_level', 'long_run_day', 'gender', 'age']);
    }

    public function test_generator_with_beginner_level_caps_vdot_improvement_and_frequency(): void
    {
        $user = User::factory()->create();

        $postData = [
            'pb_distance' => '5k',
            'pb_time' => '25:00', // VDOT ~38.3
            'target_distance' => '10k',
            'target_date' => now()->addWeeks(12)->toDateString(),
            'goal_time' => '45:00', // VDOT ~43.4 (wants +5.1 VDOT points)
            'weekly_mileage' => 40,
            'frequency' => 6, // Requesting 6 days/week, which is too high for beginner
            'runner_level' => 'beginner',
            'long_run_day' => 'sunday',
            'gender' => 'male',
            'age' => 25,
            'is_tropical' => true,
            'use_ai' => true,
        ];

        $response = $this->actingAs($user)->postJson(route('generator.generate'), $postData);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        $data = $response->json('data');
        $this->assertNotNull($data);
        
        // Target VDOT should be capped at currentVdot + 2.0 (e.g. 38.3 + 2.0 = 40.3), not 43.4
        $this->assertEquals(40.3, $data['summary']['target_vdot']);

        // Check if frequency is capped at max 4 days/week for beginners
        $sessions = $data['sessions'];
        $week1Sessions = array_filter($sessions, fn($s) => $s['week'] === 1);
        $activeDaysCount = count(array_filter($week1Sessions, fn($s) => $s['type'] !== 'rest'));
        
        $this->assertLessThanOrEqual(4, $activeDaysCount);
    }

    public function test_generator_with_advanced_level_allows_higher_vdot_improvement_and_frequency(): void
    {
        $user = User::factory()->create();

        $postData = [
            'pb_distance' => '10k',
            'pb_time' => '40:00', // VDOT ~52.9
            'target_distance' => '21k',
            'target_date' => now()->addWeeks(12)->toDateString(),
            'goal_time' => '1:15:00', // VDOT ~62.3 (wants +9.4 VDOT points)
            'weekly_mileage' => 60,
            'frequency' => 6, // Requesting 6 days/week
            'runner_level' => 'advanced',
            'long_run_day' => 'sunday',
            'gender' => 'male',
            'age' => 25,
            'is_tropical' => false,
            'use_ai' => true,
        ];

        $response = $this->actingAs($user)->postJson(route('generator.generate'), $postData);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $data = $response->json('data');
        
        // Advanced allows up to +4.0 VDOT points (52.9 + 4.0 = 56.9)
        $this->assertEquals(56.9, $data['summary']['target_vdot']);

        // Advanced allows up to 6 days/week frequency if requested
        $sessions = $data['sessions'];
        $week1Sessions = array_filter($sessions, fn($s) => $s['week'] === 1);
        $activeDaysCount = count(array_filter($week1Sessions, fn($s) => $s['type'] !== 'rest'));
        
        $this->assertEquals(6, $activeDaysCount);
    }

    public function test_generator_with_tropical_adjustment_and_heart_rate_zones(): void
    {
        $user = User::factory()->create();

        $postData = [
            'pb_distance' => '5k',
            'pb_time' => '25:00', // VDOT ~38.3
            'target_distance' => '10k',
            'target_date' => now()->addWeeks(12)->toDateString(),
            'goal_time' => '50:00', // VDOT ~38.3
            'weekly_mileage' => 30,
            'frequency' => 4,
            'runner_level' => 'intermediate',
            'long_run_day' => 'sunday',
            'gender' => 'male',
            'age' => 30,
            'is_tropical' => true,
            'use_ai' => false,
        ];

        // 1. Check with tropical = true
        $response = $this->actingAs($user)->postJson(route('generator.generate'), $postData);
        $response->assertStatus(200);
        $dataTropical = $response->json('data');

        $this->assertArrayHasKey('hr_zones', $dataTropical);
        $this->assertEquals(114, $dataTropical['hr_zones']['E']['min']); // (220 - 30) * 0.60 = 114
        $this->assertEquals(150, $dataTropical['hr_zones']['E']['max']); // (220 - 30) * 0.79 = 150

        // 2. Check with tropical = false
        $postData['is_tropical'] = false;
        $response = $this->actingAs($user)->postJson(route('generator.generate'), $postData);
        $response->assertStatus(200);
        $dataStandard = $response->json('data');

        // Tropical Easy pace should be slower (greater float value) than Standard Easy pace by 0.25 min/km (15s)
        $this->assertEquals($dataStandard['paces']['E'] + 0.25, $dataTropical['paces']['E']);
    }
}
