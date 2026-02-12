<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventDirectoryIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_event_lari_listing_excludes_managed_events(): void
    {
        User::factory()->create(['id' => 1, 'role' => 'admin']);
        $eo = User::factory()->create(['role' => 'eo']);

        $directoryEvent = Event::factory()->create([
            'user_id' => 1,
            'event_kind' => 'directory',
            'status' => 'published',
            'start_at' => now()->addDays(7),
        ]);

        $managedEvent = Event::factory()->create([
            'user_id' => $eo->id,
            'event_kind' => 'managed',
            'status' => 'published',
            'start_at' => now()->addDays(7),
        ]);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee($directoryEvent->name)
            ->assertDontSee($managedEvent->name);
    }

    public function test_admin_master_gpx_dropdown_excludes_managed_events(): void
    {
        $admin = User::factory()->create(['id' => 1, 'role' => 'admin']);
        $eo = User::factory()->create(['role' => 'eo']);

        $directoryEvent = Event::factory()->create([
            'user_id' => 1,
            'event_kind' => 'directory',
            'status' => 'published',
            'start_at' => now()->addDays(7),
        ]);

        $managedEvent = Event::factory()->create([
            'user_id' => $eo->id,
            'event_kind' => 'managed',
            'status' => 'published',
            'start_at' => now()->addDays(7),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.master-gpx.create'))
            ->assertOk()
            ->assertSee($directoryEvent->name)
            ->assertDontSee($managedEvent->name);
    }
}
