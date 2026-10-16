<?php

namespace Tests\Unit;

use App\Http\Controllers\CalendarController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;
use ReflectionClass;

class StravaGpxTelemetryTest extends TestCase
{
    private CalendarController $controller;
    private $buildGpxMethod;
    private $parsePayloadMethod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CalendarController();
        $ref = new ReflectionClass($this->controller);

        $this->buildGpxMethod = $ref->getMethod('buildGpxFromPoints');
        $this->buildGpxMethod->setAccessible(true);

        $this->parsePayloadMethod = $ref->getMethod('parseRoutePostPayload');
        $this->parsePayloadMethod->setAccessible(true);
    }

    public function test_gpx_generation_includes_garmin_trackpoint_extension_for_hr_and_cadence(): void
    {
        $points = [
            ['lat' => -6.200000, 'lng' => 106.816666],
            ['lat' => -6.201000, 'lng' => 106.817500],
            ['lat' => -6.202000, 'lng' => 106.818200],
            ['lat' => -6.203000, 'lng' => 106.819000],
            ['lat' => -6.204000, 'lng' => 106.820000],
        ];

        $telemetry = [
            'hr' => 155,
            'cadence' => 174,
            'power' => 210,
        ];

        $gpx = $this->buildGpxMethod->invoke(
            $this->controller,
            'Morning Run Test',
            $points,
            330,
            Carbon::now()->subHour(),
            $telemetry
        );

        $this->assertNotEmpty($gpx);
        $this->assertStringContainsString('xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1"', $gpx);
        $this->assertStringContainsString('<gpxtpx:TrackPointExtension>', $gpx);
        $this->assertStringContainsString('<gpxtpx:hr>', $gpx);
        $this->assertStringContainsString('<gpxtpx:cad>', $gpx);
        $this->assertStringContainsString('<gpxtpx:watts>', $gpx);
        $this->assertStringContainsString('<power>', $gpx);

        // Verify valid XML structure
        $xml = simplexml_load_string($gpx);
        $this->assertNotFalse($xml, 'GPX must be valid XML');
        $this->assertEquals('gpx', $xml->getName());

        $trkpts = $xml->trk->trkseg->trkpt;
        $this->assertCount(5, $trkpts);

        $hrs = [];
        $cads = [];
        foreach ($trkpts as $pt) {
            $namespaces = $pt->getNamespaces(true);
            $ext = $pt->children()->extensions;
            $gpxtpx = $ext->children($namespaces['gpxtpx'] ?? 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1');
            $tpx = $gpxtpx->TrackPointExtension;

            $this->assertNotEmpty((string) $tpx->hr);
            $this->assertNotEmpty((string) $tpx->cad);

            $hrs[] = (int) $tpx->hr;
            $cads[] = (int) $tpx->cad;
        }

        $avgHr = array_sum($hrs) / count($hrs);
        $avgCad = array_sum($cads) / count($cads);

        // HR average should match requested target
        $this->assertEqualsWithDelta(155, $avgHr, 1.0);
        // In GPX, cadence is single-leg RPM (174 / 2 = 87) so Strava doubling yields 174 SPM
        $this->assertEqualsWithDelta(87, $avgCad, 1.0);
        $this->assertEqualsWithDelta(174, $avgCad * 2, 2.0);
    }

    public function test_gpx_generation_without_telemetry_remains_clean_and_valid(): void
    {
        $points = [
            ['lat' => -6.200000, 'lng' => 106.816666],
            ['lat' => -6.201000, 'lng' => 106.817500],
        ];

        $gpx = $this->buildGpxMethod->invoke(
            $this->controller,
            'Clean Route',
            $points,
            360,
            Carbon::now()->subMinutes(30),
            []
        );

        $this->assertNotEmpty($gpx);
        $this->assertStringNotContainsString('TrackPointExtension', $gpx);
        $this->assertStringNotContainsString('<extensions>', $gpx);

        $xml = simplexml_load_string($gpx);
        $this->assertNotFalse($xml, 'GPX without telemetry must be valid XML');
    }

    public function test_parse_route_post_payload_retains_hr_cadence_and_power(): void
    {
        $request = Request::create('/tools/buat-rute-lari/strava-upload', 'POST', [
            'name' => 'Tempo Run',
            'pace_sec_per_km' => 300,
            'hr' => 165,
            'cadence' => 180,
            'power' => 250,
            'points' => [
                ['lat' => -6.2, 'lng' => 106.8],
                ['lat' => -6.21, 'lng' => 106.81],
            ],
        ]);

        $payload = $this->parsePayloadMethod->invoke($this->controller, $request);

        $this->assertEquals('Tempo Run', $payload['name']);
        $this->assertEquals(300, $payload['pace_sec_per_km']);
        $this->assertEquals(165, $payload['hr']);
        $this->assertEquals(180, $payload['cadence']);
        $this->assertEquals(250, $payload['power']);
        $this->assertCount(2, $payload['points']);
    }

    public function test_gpx_generation_uses_custom_device_as_creator(): void
    {
        $points = [
            ['lat' => -6.200000, 'lng' => 106.816666],
            ['lat' => -6.201000, 'lng' => 106.817500],
        ];

        // Test with "Garmin Forerunner 165"
        $gpx1 = $this->buildGpxMethod->invoke(
            $this->controller,
            'Easy Morning Run',
            $points,
            360,
            Carbon::now()->subMinutes(30),
            ['hr' => 150, 'cadence' => 170],
            'Garmin Forerunner 165'
        );

        $xml1 = simplexml_load_string($gpx1);
        $this->assertNotFalse($xml1);
        $this->assertEquals('Garmin Forerunner 165', (string) $xml1['creator']);

        // Test with "Forerunner 265"
        $gpx2 = $this->buildGpxMethod->invoke(
            $this->controller,
            'Interval Session',
            $points,
            300,
            Carbon::now()->subMinutes(30),
            ['hr' => 165, 'cadence' => 180],
            'Forerunner 265'
        );

        $xml2 = simplexml_load_string($gpx2);
        $this->assertNotFalse($xml2);
        $this->assertEquals('Forerunner 265', (string) $xml2['creator']);
    }

    public function test_parse_route_post_payload_retains_dynamic_device(): void
    {
        $request = Request::create('/tools/buat-rute-lari/strava-upload', 'POST', [
            'name' => 'Threshold Run',
            'device' => 'Garmin Forerunner 165',
            'points' => [
                ['lat' => -6.2, 'lng' => 106.8],
                ['lat' => -6.21, 'lng' => 106.81],
            ],
        ]);

        $payload = $this->parsePayloadMethod->invoke($this->controller, $request);
        $this->assertEquals('Garmin Forerunner 165', $payload['device']);
        $this->assertStringContainsString('Device: Garmin Forerunner 165', $payload['description']);
    }

    public function test_cadence_spm_is_halved_for_strava_so_165_is_not_doubled_to_330(): void
    {
        $points = [
            ['lat' => -6.200000, 'lng' => 106.816666],
            ['lat' => -6.201000, 'lng' => 106.817500],
            ['lat' => -6.202000, 'lng' => 106.818200],
            ['lat' => -6.203000, 'lng' => 106.819000],
            ['lat' => -6.204000, 'lng' => 106.820000],
        ];

        // User enters 165 SPM
        $gpx = $this->buildGpxMethod->invoke(
            $this->controller,
            'Morning Easy Run',
            $points,
            360,
            Carbon::now()->subMinutes(30),
            ['cadence' => 165],
            'Garmin Forerunner 165',
            'run'
        );

        $xml = simplexml_load_string($gpx);
        $this->assertNotFalse($xml);

        $cads = [];
        foreach ($xml->trk->trkseg->trkpt as $pt) {
            $namespaces = $pt->getNamespaces(true);
            $ext = $pt->children()->extensions;
            $gpxtpx = $ext->children($namespaces['gpxtpx'] ?? 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1');
            $cads[] = (int) $gpxtpx->TrackPointExtension->cad;
        }

        $avgGpxRpm = array_sum($cads) / count($cads);
        // The GPX values should be around 82-83 (single-leg RPM), NOT 165 (which would cause Strava to double to 330)
        $this->assertLessThan(100, $avgGpxRpm);
        $this->assertEqualsWithDelta(82.5, $avgGpxRpm, 1.0);

        // Strava reads <gpxtpx:cad> and multiplies by 2 for running activities:
        $stravaDisplayedSpm = $avgGpxRpm * 2;
        $this->assertEqualsWithDelta(165, $stravaDisplayedSpm, 1.0);
        $this->assertNotEquals(330, (int) round($stravaDisplayedSpm));
    }

    public function test_negative_split_produces_faster_late_segments_with_correlated_hr_and_cadence(): void
    {
        // 5 km route with 20 points
        $points = [];
        for ($i = 0; $i < 20; $i++) {
            $points[] = ['lat' => -6.200000 - ($i * 0.0022), 'lng' => 106.816666 + ($i * 0.0022)];
        }

        $targetPace = 300; // 5:00/km
        $targetHr = 150;
        $targetCad = 166;
        $splitPct = 3.0; // 3% faster per km

        $gpx = $this->buildGpxMethod->invoke(
            $this->controller,
            'Negative Split 5K',
            $points,
            $targetPace,
            Carbon::parse('2026-10-16 06:00:00 UTC'),
            ['hr' => $targetHr, 'cadence' => $targetCad],
            'Garmin Forerunner 265',
            'run',
            'negative',
            $splitPct
        );

        $xml = simplexml_load_string($gpx);
        $this->assertNotFalse($xml);

        $trkpts = $xml->trk->trkseg->trkpt;
        $times = [];
        $hrs = [];
        $cads = [];

        foreach ($trkpts as $pt) {
            $times[] = Carbon::parse((string) $pt->time)->getTimestamp();
            $namespaces = $pt->getNamespaces(true);
            $ext = $pt->children()->extensions;
            $gpxtpx = $ext->children($namespaces['gpxtpx'] ?? 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1');
            $hrs[] = (int) $gpxtpx->TrackPointExtension->hr;
            $cads[] = (int) $gpxtpx->TrackPointExtension->cad;
        }

        // 1. Time intervals: In negative split, earlier segments should take LONGER per meter than later segments
        $firstSegmentDeltaT = $times[1] - $times[0];
        $lastSegmentDeltaT = $times[count($times) - 1] - $times[count($times) - 2];
        $this->assertGreaterThan($lastSegmentDeltaT, $firstSegmentDeltaT, 'Negative split must have faster later segments (smaller dt)');

        // 2. Heart rate: Later points should have higher HR than earlier points
        $earlyAvgHr = array_sum(array_slice($hrs, 0, 4)) / 4.0;
        $lateAvgHr = array_sum(array_slice($hrs, -4)) / 4.0;
        $this->assertGreaterThan($earlyAvgHr, $lateAvgHr, 'Heart rate must be higher in late segments when running faster');

        // 3. Cadence: Later points should have higher cadence than earlier points
        $earlyAvgCad = array_sum(array_slice($cads, 0, 4)) / 4.0;
        $lateAvgCad = array_sum(array_slice($cads, -4)) / 4.0;
        $this->assertGreaterThan($earlyAvgCad, $lateAvgCad, 'Cadence must be higher in late segments when running faster');

        // 4. Overall averages MUST strictly match the targets
        $overallAvgHr = array_sum($hrs) / count($hrs);
        $this->assertEqualsWithDelta($targetHr, $overallAvgHr, 1.0, 'Average HR must strictly match target');

        $overallAvgCadSpm = (array_sum($cads) / count($cads)) * 2;
        $this->assertEqualsWithDelta($targetCad, $overallAvgCadSpm, 1.0, 'Average Cadence must strictly match target');
    }

    public function test_positive_split_produces_slower_late_segments_with_correlated_hr_and_cadence(): void
    {
        $points = [];
        for ($i = 0; $i < 20; $i++) {
            $points[] = ['lat' => -6.200000 - ($i * 0.0022), 'lng' => 106.816666 + ($i * 0.0022)];
        }

        $targetPace = 300;
        $targetHr = 150;
        $targetCad = 166;
        $splitPct = 3.0; // 3% slower per km

        $gpx = $this->buildGpxMethod->invoke(
            $this->controller,
            'Positive Split 5K',
            $points,
            $targetPace,
            Carbon::parse('2026-10-16 06:00:00 UTC'),
            ['hr' => $targetHr, 'cadence' => $targetCad],
            'Garmin Forerunner 265',
            'run',
            'positive',
            $splitPct
        );

        $xml = simplexml_load_string($gpx);
        $this->assertNotFalse($xml);

        $trkpts = $xml->trk->trkseg->trkpt;
        $times = [];
        $hrs = [];
        $cads = [];

        foreach ($trkpts as $pt) {
            $times[] = Carbon::parse((string) $pt->time)->getTimestamp();
            $namespaces = $pt->getNamespaces(true);
            $ext = $pt->children()->extensions;
            $gpxtpx = $ext->children($namespaces['gpxtpx'] ?? 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1');
            $hrs[] = (int) $gpxtpx->TrackPointExtension->hr;
            $cads[] = (int) $gpxtpx->TrackPointExtension->cad;
        }

        // 1. Time intervals: In positive split, earlier segments are faster (smaller dt) than later segments
        $firstSegmentDeltaT = $times[1] - $times[0];
        $lastSegmentDeltaT = $times[count($times) - 1] - $times[count($times) - 2];
        $this->assertLessThan($lastSegmentDeltaT, $firstSegmentDeltaT, 'Positive split must have slower later segments (larger dt)');

        // 2. Cadence: Later points should have lower cadence than earlier points
        $earlyAvgCad = array_sum(array_slice($cads, 0, 4)) / 4.0;
        $lateAvgCad = array_sum(array_slice($cads, -4)) / 4.0;
        $this->assertLessThan($earlyAvgCad, $lateAvgCad, 'Cadence must decrease in late segments when slowing down');

        // 3. Overall averages MUST strictly match the targets
        $overallAvgHr = array_sum($hrs) / count($hrs);
        $this->assertEqualsWithDelta($targetHr, $overallAvgHr, 1.0, 'Average HR must strictly match target');

        $overallAvgCadSpm = (array_sum($cads) / count($cads)) * 2;
        $this->assertEqualsWithDelta($targetCad, $overallAvgCadSpm, 1.0, 'Average Cadence must strictly match target');
    }

    public function test_parse_route_post_payload_retains_split_strategy_and_pct(): void
    {
        $request = Request::create('/tools/buat-rute-lari/strava-upload', 'POST', [
            'name' => 'Marathon Pace Simulation',
            'split_type' => 'negative',
            'split_pct' => 2.5,
            'pace_sec_per_km' => 315,
            'points' => [
                ['lat' => -6.2, 'lng' => 106.8],
                ['lat' => -6.21, 'lng' => 106.81],
            ],
        ]);

        $payload = $this->parsePayloadMethod->invoke($this->controller, $request);
        $this->assertEquals('negative', $payload['split_type']);
        $this->assertEquals(2.5, $payload['split_pct']);
        $this->assertStringContainsString('Split: Negative Split (+2.5 %/km)', $payload['description']);
    }
}

