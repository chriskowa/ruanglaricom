<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityRegistration;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_show_view_renders_correctly()
    {
        $owner = User::factory()->create();

        $event = Event::create([
            'name' => 'Test Event',
            'slug' => 'test-event',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDay(),
            'registration_close_at' => now()->addDays(5),
            'status' => 'published',
            'is_active' => true,
            'event_kind' => 'managed',
            'user_id' => $owner->id,
            'location_name' => 'GBK',
        ]);

        $registration = CommunityRegistration::create([
            'event_id' => $event->id,
            'community_id' => Community::create([
                'name' => 'Test Community',
                'slug' => 'test-community',
                'pic_name' => 'PIC Test',
                'pic_email' => 'pic@test.com',
                'pic_phone' => '08123456789',
            ])->id,
            'community_name' => 'Test Community',
            'pic_name' => 'PIC Test',
            'pic_email' => 'pic@test.com',
            'pic_phone' => '08123456789',
            'status' => 'draft',
        ]);

        $response = $this->get(route('community.register.show', [
            'event' => $event->slug,
            'community' => 'test-community',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Test Community');
    }
}
