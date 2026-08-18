<?php

namespace Tests\Feature;

use App\Models\MasterGpx;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GpxFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_gpx_database_page_is_accessible()
    {
        $response = $this->get(route('gpx.index'));
        $response->assertStatus(200);
        $response->assertSee('DATABASE');
        $response->assertSee('RUTE GPX');
    }

    public function test_guest_gpx_submission_returns_requires_login()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('test-route.gpx', 10, 'application/gpx+xml');

        $response = $this->postJson(route('tools.buat-rute-lari.submit-gpx'), [
            'title' => 'Test Route',
            'city' => 'Jakarta Pusat',
            'gpx_file' => $file,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'requires_login' => true,
        ]);
    }

    public function test_authenticated_runner_can_submit_gpx_and_receives_points()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'runner',
            'run_points' => 0,
        ]);

        $gpxContent = '<?xml version="1.0" encoding="UTF-8"?>
        <gpx version="1.1" creator="RuangLari">
            <trk>
                <name>Monas Loop</name>
                <trkseg>
                    <trkpt lat="-6.175392" lon="106.827153"><ele>10</ele></trkpt>
                    <trkpt lat="-6.176000" lon="106.828000"><ele>12</ele></trkpt>
                    <trkpt lat="-6.177000" lon="106.829000"><ele>15</ele></trkpt>
                </trkseg>
            </trk>
        </gpx>';

        $file = UploadedFile::fake()->createWithContent('monas-loop.gpx', $gpxContent);

        $response = $this->actingAs($user)->postJson(route('tools.buat-rute-lari.submit-gpx'), [
            'title' => 'Monas Loop 5K',
            'city' => 'Jakarta Pusat',
            'gpx_file' => $file,
            'notes' => 'Rute lari sekeliling Monas',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'points_earned' => 10,
            'total_points' => 10,
        ]);

        $this->assertDatabaseHas('master_gpxes', [
            'user_id' => $user->id,
            'title' => 'Monas Loop 5K',
            'city' => 'Jakarta Pusat',
            'is_published' => false,
        ]);

        $this->assertEquals(10, $user->fresh()->run_points);
    }

    public function test_honeypot_rejects_spam_submission()
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'runner']);
        $file = UploadedFile::fake()->create('test.gpx', 10, 'application/gpx+xml');

        $response = $this->actingAs($user)->postJson(route('tools.buat-rute-lari.submit-gpx'), [
            'title' => 'Bot Route',
            'city' => 'Jakarta',
            'gpx_file' => $file,
            'website_url' => 'http://spam-link.com', // Honeypot filled by bot
        ]);

        $response->assertStatus(422);
    }

    public function test_daily_point_cap_limits_points_to_max_3_submissions_per_day()
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'runner', 'run_points' => 0]);

        $gpxContent = '<?xml version="1.0" encoding="UTF-8"?>
        <gpx version="1.1" creator="RuangLari">
            <trk><trkseg>
                <trkpt lat="-6.175392" lon="106.827153"><ele>10</ele></trkpt>
                <trkpt lat="-6.176000" lon="106.828000"><ele>12</ele></trkpt>
            </trkseg></trk>
        </gpx>';

        // Submit 3 times (earned 10 pts each = 30 pts)
        for ($i = 1; $i <= 3; $i++) {
            $file = UploadedFile::fake()->createWithContent("route-{$i}.gpx", $gpxContent);
            $res = $this->actingAs($user)->postJson(route('tools.buat-rute-lari.submit-gpx'), [
                'title' => "Route {$i}",
                'city' => 'Jakarta',
                'gpx_file' => $file,
            ]);
            $res->assertStatus(200);
            $res->assertJson(['points_earned' => 10]);
        }

        $this->assertEquals(30, $user->fresh()->run_points);

        // 4th submission: Route is still accepted for moderation, but 0 points awarded
        $file4 = UploadedFile::fake()->createWithContent('route-4.gpx', $gpxContent);
        $res4 = $this->actingAs($user)->postJson(route('tools.buat-rute-lari.submit-gpx'), [
            'title' => 'Route 4',
            'city' => 'Jakarta',
            'gpx_file' => $file4,
        ]);
        $res4->assertStatus(200);
        $res4->assertJson(['points_earned' => 0]);

        $this->assertEquals(30, $user->fresh()->run_points);
    }

    public function test_gps_radius_and_nearest_sorting()
    {
        $user = User::factory()->create();

        // Monas Jakarta: lat -6.1754, lng 106.8272
        $monas = MasterGpx::create([
            'user_id' => $user->id,
            'title' => 'Monas Loop',
            'city' => 'Jakarta Pusat',
            'start_latitude' => -6.175400,
            'start_longitude' => 106.827200,
            'coordinates_json' => [[-6.175400, 106.827200], [-6.176000, 106.828000]],
            'distance_km' => 5.0,
            'is_published' => true,
        ]);

        // Bandung: lat -6.9175, lng 107.6191 (~120 km away from Jakarta)
        $bandung = MasterGpx::create([
            'user_id' => $user->id,
            'title' => 'Gasibu Loop',
            'city' => 'Bandung',
            'start_latitude' => -6.917500,
            'start_longitude' => 107.619100,
            'coordinates_json' => [[-6.917500, 107.619100], [-6.918000, 107.620000]],
            'distance_km' => 3.0,
            'is_published' => true,
        ]);

        // User is at Thamrin Jakarta (-6.1900, 106.8230) ~ 1.7 km from Monas, ~120 km from Bandung
        $response = $this->getJson(route('gpx.index', [
            'lat' => -6.1900,
            'lng' => 106.8230,
            'radius' => 25, // 25 km radius should only include Monas
            'sort' => 'nearest',
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertSee('Monas Loop');
        $response->assertDontSee('Gasibu Loop');
    }
}
