<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\MasterGpx;
use App\Models\RaceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPredictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_prediction_endpoint_returns_prediction(): void
    {
        $event = Event::factory()->create(['slug' => 'test-event']);

        $gpx = MasterGpx::create([
            'event_id' => $event->id,
            'title' => 'Route 5K',
            'gpx_path' => 'master-gpx/test.gpx',
            'distance_km' => 5.0,
            'elevation_gain_m' => 60,
            'elevation_loss_m' => 60,
            'is_published' => true,
        ]);

        $cat = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '5K',
            'code' => '5K',
            'distance_km' => 5,
            'quota' => 100,
            'price_regular' => 100000,
            'min_age' => 12,
            'max_age' => 99,
            'reg_start_at' => now()->subDay(),
            'reg_end_at' => now()->addDay(),
            'is_active' => true,
            'master_gpx_id' => $gpx->id,
        ]);

        $resp = $this->post(route('events.prediction.predict', $event->slug), [
            'category_id' => $cat->id,
            'weather' => 'panas',
            'pb_h' => 0,
            'pb_m' => 25,
            'pb_s' => 0,
            'pb_date' => now()->subDays(20)->toDateString(),
        ], [
            'Accept' => 'application/json',
        ]);

        $resp->assertOk();
        $resp->assertJsonPath('ok', true);
        $resp->assertJsonPath('category.id', $cat->id);
        $resp->assertJsonStructure([
            'result' => [
                'vdot',
                'prediction' => ['optimistic', 'realistic', 'pessimistic'],
                'confidence',
            ],
        ]);
    }
}

