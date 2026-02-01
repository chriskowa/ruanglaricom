<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EoEventRpcFieldTest extends TestCase
{
    use RefreshDatabase;

    public function test_eo_can_create_event_with_rpc_fields(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);

        $payload = [
            'name' => 'Test RPC Event',
            'start_at' => now()->addDays(1)->toDateTimeString(),
            'location_name' => 'Main Venue',
            'location_address' => 'Main Address',
            'rpc_location_name' => 'RPC Venue',
            'rpc_location_address' => 'RPC Address',
            'rpc_latitude' => -6.200000,
            'rpc_longitude' => 106.816666,
            'categories' => [
                [
                    'name' => '5K',
                    'distance_km' => 5,
                    'price_regular' => 100000,
                ]
            ]
        ];

        $response = $this->actingAs($eo)->post(route('eo.events.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'name' => 'Test RPC Event',
            'rpc_location_name' => 'RPC Venue',
            'rpc_location_address' => 'RPC Address',
            'rpc_latitude' => -6.200000,
            'rpc_longitude' => 106.816666,
        ]);
    }

    public function test_eo_can_update_event_with_rpc_fields(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create([
            'user_id' => $eo->id,
            'rpc_location_name' => 'Old RPC Venue',
        ]);

        $payload = [
            'name' => $event->name,
            'start_at' => $event->start_at->toDateTimeString(),
            'location_name' => $event->location_name,
            'rpc_location_name' => 'Updated RPC Venue',
            'rpc_location_address' => 'Updated RPC Address',
            'rpc_latitude' => -7.200000,
            'rpc_longitude' => 107.816666,
        ];

        $response = $this->actingAs($eo)->put(route('eo.events.update', $event), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'rpc_location_name' => 'Updated RPC Venue',
            'rpc_location_address' => 'Updated RPC Address',
            'rpc_latitude' => -7.200000,
            'rpc_longitude' => 107.816666,
        ]);
    }
}
