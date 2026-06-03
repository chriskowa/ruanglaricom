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

class BloodTypeRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_registration_with_blood_type_enabled_and_valid_value()
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
            'name' => 'Blood Type Test Event',
            'slug' => 'blood-type-test-event',
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
                    'blood_type' => '1',
                ]
            ],
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '5K',
            'price_regular' => 100000,
            'quota' => 50,
        ]);

        $data = [
            'pic_name' => 'Runner Blood',
            'pic_email' => 'blood@example.com',
            'pic_phone' => '081299999999',
            'participants' => [
                [
                    'name' => 'Runner Blood',
                    'gender' => 'female',
                    'email' => 'blood@example.com',
                    'phone' => '081299999999',
                    'id_card' => '3201234567890123',
                    'address' => 'Jl. Merdeka No. 10, Bandung',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Dad',
                    'emergency_contact_number' => '081288888888',
                    'date_of_birth' => '1995-05-15',
                    'jersey_size' => 'S',
                    'blood_type' => 'AB',
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token',
        ];

        $response = $this->postJson(route('events.register.store', $event->slug), $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('participants', [
            'email' => 'blood@example.com',
            'blood_type' => 'AB',
        ]);
    }

    public function test_registration_rejects_invalid_blood_type()
    {
        $user = User::factory()->create();

        $event = Event::create([
            'name' => 'Blood Type Test Event 2',
            'slug' => 'blood-type-test-event-2',
            'location' => 'Bandung',
            'start_at' => now()->addDays(10),
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'Gedung Sate',
            'premium_amenities' => [
                'form_fields' => [
                    'blood_type' => '1',
                ]
            ],
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '5K',
            'price_regular' => 100000,
        ]);

        $data = [
            'pic_name' => 'Runner Blood Invalid',
            'pic_email' => 'blood_inv@example.com',
            'pic_phone' => '081299999999',
            'participants' => [
                [
                    'name' => 'Runner Blood Invalid',
                    'gender' => 'female',
                    'email' => 'blood_inv@example.com',
                    'phone' => '081299999999',
                    'id_card' => '3201234567890123',
                    'address' => 'Jl. Merdeka No. 10, Bandung',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Dad',
                    'emergency_contact_number' => '081288888888',
                    'date_of_birth' => '1995-05-15',
                    'jersey_size' => 'S',
                    'blood_type' => 'Z', // Invalid blood type
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token',
        ];

        $response = $this->postJson(route('events.register.store', $event->slug), $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['participants.0.blood_type']);
    }

    public function test_registration_with_empty_blood_type_sanitized_to_null()
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
            'name' => 'Blood Type Test Event 3',
            'slug' => 'blood-type-test-event-3',
            'location' => 'Bandung',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDays(1),
            'registration_close_at' => now()->addDays(5),
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'Gedung Sate',
            'premium_amenities' => [
                'form_fields' => [
                    'blood_type' => '0', // Disabled
                ]
            ],
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '5K',
            'price_regular' => 100000,
        ]);

        $data = [
            'pic_name' => 'Runner No Blood',
            'pic_email' => 'noblood@example.com',
            'pic_phone' => '081299999999',
            'participants' => [
                [
                    'name' => 'Runner No Blood',
                    'gender' => 'female',
                    'email' => 'noblood@example.com',
                    'phone' => '081299999999',
                    'id_card' => '3201234567890123',
                    'address' => 'Jl. Merdeka No. 10, Bandung',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Dad',
                    'emergency_contact_number' => '081288888888',
                    'date_of_birth' => '1995-05-15',
                    'jersey_size' => 'S',
                    'blood_type' => '', // Empty string should be sanitized to null
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token',
        ];

        $response = $this->postJson(route('events.register.store', $event->slug), $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('participants', [
            'email' => 'noblood@example.com',
            'blood_type' => null,
        ]);
    }
}
