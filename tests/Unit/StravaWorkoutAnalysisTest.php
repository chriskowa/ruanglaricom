<?php

namespace Tests\Unit;

use App\Http\Controllers\Runner\StravaController;
use App\Models\User;
use App\Services\StravaApiService;
use ReflectionClass;
use Tests\TestCase;

class StravaWorkoutAnalysisTest extends TestCase
{
    private StravaController $controller;
    private StravaApiService $api;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new StravaController();
        $this->api = app(StravaApiService::class);
    }

    public function test_calculate_heart_rate_zones_identifies_high_intensity_zone4_and_zone5(): void
    {
        $user = new User(['date_of_birth' => '1996-01-01']);
        $details = ['max_heartrate' => 168];

        // 22s Z1, 57s Z2, 155s Z3, 1088s Z4, 1408s Z5
        $hrData = array_merge(
            array_fill(0, 22, 90),
            array_fill(0, 57, 110),
            array_fill(0, 155, 125),
            array_fill(0, 1088, 145),
            array_fill(0, 1408, 158)
        );
        $timeData = range(1, count($hrData));

        $streams = [
            'heartrate' => ['data' => $hrData],
            'time' => ['data' => $timeData],
        ];

        $res = $this->controller->calculateHeartRateZones($user, $details, $streams);

        $this->assertTrue($res['has_hr_data']);
        $this->assertEquals(168, $res['max_hr']);
        $this->assertGreaterThan(90.0, $res['summary']['high_intensity_pct']); // 39.9% + 51.6% = 91.5%
        $this->assertTrue($res['summary']['is_high_intensity']);
        $this->assertStringContainsString('Zone 5', $res['summary']['dominant_zone']);
    }

    public function test_analyze_splits_for_intervals_detects_middle_fast_splits(): void
    {
        $splits = [
            ['split' => 1, 'distance' => 1000, 'moving_time' => 430, 'average_speed' => 1000/430, 'average_heartrate' => 128], // 7:10 /km
            ['split' => 2, 'distance' => 1000, 'moving_time' => 415, 'average_speed' => 1000/415, 'average_heartrate' => 138], // 6:55 /km
            ['split' => 3, 'distance' => 1000, 'moving_time' => 320, 'average_speed' => 1000/320, 'average_heartrate' => 162], // 5:20 /km FAST REP 1
            ['split' => 4, 'distance' => 1000, 'moving_time' => 420, 'average_speed' => 1000/420, 'average_heartrate' => 148], // 7:00 /km recovery
            ['split' => 5, 'distance' => 1000, 'moving_time' => 315, 'average_speed' => 1000/315, 'average_heartrate' => 166], // 5:15 /km FAST REP 2
            ['split' => 6, 'distance' => 1000, 'moving_time' => 410, 'average_speed' => 1000/410, 'average_heartrate' => 152], // 6:50 /km recovery
            ['split' => 7, 'distance' => 630,  'moving_time' => 320, 'average_speed' => 630/320,  'average_heartrate' => 144], // cooldown
        ];

        $res = $this->controller->analyzeSplitsForIntervals($splits, [], $this->api);

        $this->assertTrue($res['is_interval_detected']);
        $this->assertContains(3, $res['fast_splits']);
        $this->assertContains(5, $res['fast_splits']);
        $this->assertGreaterThan(30, $res['spread_seconds']);
    }

    public function test_infer_workout_type_hint_classifies_high_hr_interval_correctly(): void
    {
        $ref = new ReflectionClass($this->controller);
        $method = $ref->getMethod('inferWorkoutTypeHint');
        $method->setAccessible(true);

        $summarizeMethod = $ref->getMethod('summarizeSeconds');
        $summarizeMethod->setAccessible(true);
        $splitStats = $summarizeMethod->invoke($this->controller, [430, 415, 320, 420, 315, 410, 508]);

        $hrZonesSummary = [
            'zone_1_pct' => 0.8,
            'zone_2_pct' => 2.1,
            'zone_3_pct' => 5.7,
            'zone_4_pct' => 39.9,
            'zone_5_pct' => 51.6,
            'high_intensity_pct' => 91.5,
            'aerobic_pct' => 2.9,
            'tempo_pct' => 5.7,
            'dominant_zone' => 'Zone 5 (Anaerobic / VO2max)',
            'is_high_intensity' => true,
        ];

        $splitsAnalysis = [
            'is_interval_detected' => true,
            'pattern' => 'middle_interval_splits',
            'spread_seconds' => 193.0,
            'fast_splits' => [3, 5],
            'evidence' => ['Split KM di tengah sesi merupakan repetisi interval kencang.'],
        ];

        $hint = $method->invoke(
            $this->controller,
            6.63,
            $splitStats,
            [],
            ['laps_list' => [], 'interval_structure' => null, 'is_structured_interval' => false],
            ['detected_surge_count' => 0, 'surges' => []],
            $hrZonesSummary,
            $splitsAnalysis
        );

        $this->assertEquals('interval', $hint['type']);
        $this->assertNotEquals('easy_run', $hint['type']);
    }

    public function test_infer_workout_type_hint_does_not_blindly_classify_as_easy_run_when_hr_is_high(): void
    {
        $ref = new ReflectionClass($this->controller);
        $method = $ref->getMethod('inferWorkoutTypeHint');
        $method->setAccessible(true);

        $summarizeMethod = $ref->getMethod('summarizeSeconds');
        $summarizeMethod->setAccessible(true);
        $splitStats = $summarizeMethod->invoke($this->controller, [380, 385, 390, 385, 380]);

        $hrZonesSummary = [
            'zone_1_pct' => 2.0,
            'zone_2_pct' => 5.0,
            'zone_3_pct' => 15.0,
            'zone_4_pct' => 50.0,
            'zone_5_pct' => 28.0,
            'high_intensity_pct' => 78.0,
            'aerobic_pct' => 7.0,
            'tempo_pct' => 15.0,
            'dominant_zone' => 'Zone 4 (Threshold)',
            'is_high_intensity' => true,
        ];

        $hint = $method->invoke(
            $this->controller,
            5.0,
            $splitStats,
            [],
            ['laps_list' => [], 'interval_structure' => null, 'is_structured_interval' => false],
            ['detected_surge_count' => 0, 'surges' => []],
            $hrZonesSummary,
            ['is_interval_detected' => false, 'evidence' => []]
        );

        // When 78% is in Zone 4+5 with steady pace, it MUST be threshold or interval, NEVER easy_run!
        $this->assertNotEquals('easy_run', $hint['type']);
        $this->assertContains($hint['type'], ['threshold', 'interval']);
    }
}

