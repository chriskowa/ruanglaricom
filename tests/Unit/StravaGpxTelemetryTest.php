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

        // Average should be within 1 unit of requested target
        $this->assertEqualsWithDelta(155, $avgHr, 1.0);
        $this->assertEqualsWithDelta(174, $avgCad, 1.0);
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
}
