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
        
        // Target VDOT should be capped at currentVdot * 1.06 (38.32 * 1.06 = 40.6)
        $this->assertEquals(40.6, $data['summary']['target_vdot']);

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
        
        // Advanced allows up to +10% improvement (52.88 * 1.10 = 58.1)
        $this->assertEquals(58.1, $data['summary']['target_vdot']);

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
        $this->assertEquals(112, $dataTropical['hr_zones']['E']['min']); // (208 - 0.7*30) * 0.60 = 112
        $this->assertEquals(148, $dataTropical['hr_zones']['E']['max']); // (208 - 0.7*30) * 0.79 = 148

        // 2. Check with tropical = false
        $postData['is_tropical'] = false;
        $response = $this->actingAs($user)->postJson(route('generator.generate'), $postData);
        $response->assertStatus(200);
        $dataStandard = $response->json('data');

        // Tropical Easy pace should be slower (greater float value) than Standard Easy pace by 0.25 min/km (15s)
        $this->assertEquals(round($dataStandard['paces']['E'] * 1.05, 4), round($dataTropical['paces']['E'], 4));
    }

    public function test_generator_with_cooper_and_balke_parameter_tests(): void
    {
        $user = User::factory()->create();

        // 1. Test Cooper 12-minute test (2800 meters)
        $postData = [
            'pb_distance' => 'cooper12',
            'pb_time' => '2800', // 2800 meters
            'target_distance' => '10k',
            'target_date' => now()->addWeeks(12)->toDateString(),
            'goal_time' => '50:00',
            'weekly_mileage' => 40,
            'frequency' => 4,
            'runner_level' => 'intermediate',
            'long_run_day' => 'sunday',
            'gender' => 'male',
            'age' => 25,
            'is_tropical' => false,
            'use_ai' => false,
        ];

        $response = $this->actingAs($user)->postJson(route('generator.generate'), $postData);
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $dataCooper = $response->json('data');
        $this->assertNotNull($dataCooper['summary']['vdot']);

        // 2. Test Balke 15-minute test (3200 meters)
        $postData['pb_distance'] = 'balke15';
        $postData['pb_time'] = '3200'; // 3200 meters

        $response = $this->actingAs($user)->postJson(route('generator.generate'), $postData);
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $dataBalke = $response->json('data');
        $this->assertNotNull($dataBalke['summary']['vdot']);
    }
}
