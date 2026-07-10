<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\RaceDistance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_event_with_custom_distances(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Admin Test Run',
            'event_date' => now()->addDays(14)->toDateString(),
            'start_time' => '05:30',
            'location_name' => 'Senayan',
            'event_kind' => 'directory',
            'status' => 'published',
            'custom_distances' => '25K, 75K',
        ];

        $res = $this->actingAs($admin)
            ->post(route('admin.events.store'), $payload);

        $res->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'name' => 'Admin Test Run',
        ]);

        $this->assertDatabaseHas('race_distances', [
            'slug' => '25k',
            'distance_meter' => 25000,
        ]);

        $this->assertDatabaseHas('race_distances', [
            'slug' => '75k',
            'distance_meter' => 75000,
        ]);

        $event = Event::query()->where('name', 'Admin Test Run')->first();
        $this->assertNotNull($event);
        $this->assertCount(2, $event->raceDistances);
    }

    public function test_admin_can_update_event_with_custom_distances(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $event = Event::create([
            'user_id' => $admin->id,
            'name' => 'Existing Run',
            'slug' => 'existing-run',
            'start_at' => now()->addDays(5),
            'location_name' => 'Senayan',
            'event_kind' => 'directory',
            'status' => 'draft',
        ]);

        $payload = [
            'name' => 'Updated Event Name',
            'event_date' => now()->addDays(5)->toDateString(),
            'start_time' => '05:30',
            'location_name' => 'Senayan',
            'event_kind' => 'directory',
            'status' => 'published',
            'custom_distances' => '12K, 35K',
        ];

        $res = $this->actingAs($admin)
            ->put(route('admin.events.update', $event), $payload);

        $res->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Updated Event Name',
        ]);

        $this->assertDatabaseHas('race_distances', [
            'slug' => '12k',
            'distance_meter' => 12000,
        ]);

        $this->assertDatabaseHas('race_distances', [
            'slug' => '35k',
            'distance_meter' => 35000,
        ]);

        $event->refresh();
        $this->assertCount(2, $event->raceDistances);
    }
}
