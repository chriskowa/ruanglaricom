<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityRegistration;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityMasterRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_using_master_community_id()
    {
        $owner = User::factory()->create();

        $event = Event::create([
            'name' => 'Managed Event',
            'slug' => 'managed-event',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDay(),
            'registration_close_at' => now()->addDays(5),
            'status' => 'published',
            'is_active' => true,
            'event_kind' => 'managed',
            'user_id' => $owner->id,
            'location_name' => 'GBK',
        ]);

        $community = Community::create([
            'name' => 'Indo Runners',
            'slug' => 'indo-runners',
            'pic_name' => 'Budi PIC',
            'pic_email' => 'budi@example.com',
            'pic_phone' => '08123456789',
        ]);

        $response = $this->post(route('community.register.start'), [
            'event_id' => $event->id,
            'community_id' => $community->id,
            // Manual fields omitted, should rely on master data
        ]);

        $response->assertRedirect();

        $registration = CommunityRegistration::latest()->first();

        $this->assertNotNull($registration);
        $this->assertEquals($community->id, $registration->community_id);
        $this->assertEquals('Indo Runners', $registration->community_name);
        $this->assertEquals('Budi PIC', $registration->pic_name);
        $this->assertEquals('budi@example.com', $registration->pic_email);
        $this->assertEquals('08123456789', $registration->pic_phone);
    }

    public function test_manual_registration_still_works()
    {
        $owner = User::factory()->create();

        $event = Event::create([
            'name' => 'Managed Event',
            'slug' => 'managed-event',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDay(),
            'registration_close_at' => now()->addDays(5),
            'status' => 'published',
            'is_active' => true,
            'event_kind' => 'managed',
            'user_id' => $owner->id,
            'location_name' => 'GBK',
        ]);

        $response = $this->post(route('community.register.start'), [
            'event_id' => $event->id,
            'community_name' => 'Manual Community',
            'pic_name' => 'Manual PIC',
            'pic_email' => 'manual@example.com',
            'pic_phone' => '08987654321',
        ]);

        $response->assertRedirect();

        $registration = CommunityRegistration::latest()->first();

        $this->assertNotNull($registration);
        $this->assertNull($registration->community_id);
        $this->assertEquals('Manual Community', $registration->community_name);
        $this->assertEquals('Manual PIC', $registration->pic_name);
    }
}
