<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EoEventDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_eo_can_duplicate_own_event_with_unique_slug_and_categories(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);

        $event = Event::factory()->create([
            'user_id' => $eo->id,
            'name' => 'My Event',
            'slug' => 'my-event',
        ]);

        $event->categories()->create([
            'name' => '5K',
            'distance_km' => 5.0,
            'price_regular' => 100000,
            'is_active' => true,
        ]);
        $event->categories()->create([
            'name' => '10K',
            'distance_km' => 10.0,
            'price_regular' => 150000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($eo)->post(route('eo.events.duplicate', $event));
        $response->assertRedirect();

        $this->assertDatabaseCount('events', 2);
        $this->assertDatabaseCount('race_categories', 4);

        $newEvent = Event::query()->where('id', '!=', $event->id)->firstOrFail();
        $this->assertSame($eo->id, $newEvent->user_id);
        $this->assertSame('My Event (Copy)', $newEvent->name);
        $this->assertNotSame($event->slug, $newEvent->slug);
        $this->assertTrue(str_starts_with($newEvent->slug, 'my-event-copy'));

        $this->assertSame(2, $newEvent->categories()->count());
        $this->assertDatabaseHas('race_categories', ['event_id' => $newEvent->id, 'name' => '5K']);
        $this->assertDatabaseHas('race_categories', ['event_id' => $newEvent->id, 'name' => '10K']);

        $response->assertRedirect(route('eo.events.edit', $newEvent));
    }

    public function test_eo_cannot_duplicate_other_eo_event(): void
    {
        $owner = User::factory()->create(['role' => 'eo']);
        $other = User::factory()->create(['role' => 'eo']);

        $event = Event::factory()->create([
            'user_id' => $owner->id,
            'slug' => 'owner-event',
        ]);

        $this->actingAs($other)
            ->post(route('eo.events.duplicate', $event))
            ->assertForbidden();
    }
}
