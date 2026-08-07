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
}
