<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EoEventAuthorizationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_eo_owner_can_access_edit_participants_duplicate_and_delete(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);

        $event = Event::factory()->create([
            'user_id' => $eo->id,
        ]);

        $this->actingAs($eo)->get(route('eo.events.edit', $event))->assertOk();
        $this->actingAs($eo)->get(route('eo.events.participants', $event))->assertOk();
        $this->actingAs($eo)->post(route('eo.events.duplicate', $event))->assertRedirect();
        $this->actingAs($eo)->delete(route('eo.events.destroy', $event))->assertRedirect(route('eo.events.index'));
    }
}
