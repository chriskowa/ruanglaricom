<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StravaDisconnectTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_disconnect_strava()
    {
        $response = $this->postJson(route('calendar.strava.disconnect'));

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_disconnect_strava()
    {
        $user = User::factory()->create([
            'strava_id' => '12345678',
            'strava_access_token' => 'old-access-token',
            'strava_refresh_token' => 'old-refresh-token',
            'strava_expires_at' => now()->addHours(2),
        ]);

        $response = $this->actingAs($user)->postJson(route('calendar.strava.disconnect'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $user->refresh();
        $this->assertNull($user->strava_id);
        $this->assertNull($user->strava_access_token);
        $this->assertNull($user->strava_refresh_token);
        $this->assertNull($user->strava_expires_at);
    }
}
