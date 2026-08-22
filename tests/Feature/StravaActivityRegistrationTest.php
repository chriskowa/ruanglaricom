<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class StravaActivityRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_participant_registration_stores_strava_url_when_enabled()
    {
        $this->mock(MidtransService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createEventTransaction')
                ->andReturn([
                    'success' => true,
                    'snap_token' => 'dummy-snap-token',
                    'order_id' => 'dummy-order-id',
                ]);
        });

        $user = User::factory()->create();

        $event = Event::create([
            'name' => 'Strava Qualifying Race',
            'slug' => 'strava-qualifying-race',
            'location' => 'Bandung',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDays(1),
            'registration_close_at' => now()->addDays(5),
            'description' => 'Test description',
            'is_published' => true,
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'Gedung Sate',
            'premium_amenities' => [
                'form_fields' => [
                    'strava_activity' => '1',
                ],
            ],
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => 'Half Marathon',
            'price_regular' => 250000,
            'quota' => 50,
        ]);

        $stravaLink = 'https://www.strava.com/activities/12345678901';

        $data = [
            'pic_name' => 'Strava Runner',
            'pic_email' => 'strava.runner@example.com',
            'pic_phone' => '081299999999',
            'participants' => [
                [
                    'name' => 'Strava Runner',
                    'gender' => 'male',
                    'email' => 'strava.runner@example.com',
                    'phone' => '081299999999',
                    'id_card' => '3201234567890123',
                    'address' => 'Jl. Asia Afrika No. 1, Bandung',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Coach',
                    'emergency_contact_number' => '081288888888',
                    'date_of_birth' => '1995-05-15',
                    'jersey_size' => 'M',
                    'strava_url' => $stravaLink,
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token',
        ];

        $response = $this->postJson(route('events.register.store', $event->slug), $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('participants', [
            'email' => 'strava.runner@example.com',
            'strava_url' => $stravaLink,
        ]);
    }
}
