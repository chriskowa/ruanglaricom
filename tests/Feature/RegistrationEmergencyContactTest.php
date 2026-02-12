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

class RegistrationEmergencyContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock ReCaptcha to always succeed
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_participant_registration_stores_emergency_contact()
    {
        // Mock MidtransService
        $this->mock(MidtransService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createEventTransaction')
                ->andReturn([
                    'success' => true,
                    'snap_token' => 'dummy-snap-token',
                    'order_id' => 'dummy-order-id',
                ]);
        });

        // 0. Create User (Organizer)
        $user = User::factory()->create();

        // 1. Setup Event and Category
        $event = Event::create([
            'name' => 'Test Event',
            'slug' => 'test-event',
            'location' => 'Jakarta',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDays(1),
            'registration_close_at' => now()->addDays(5),
            'description' => 'Test Description',
            'is_published' => true,
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'GBK',
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '5K',
            'price_regular' => 150000,
            'quota' => 100,
            'start_time' => '06:00',
        ]);

        // 2. Prepare Registration Data
        $data = [
            'pic_name' => 'John Doe',
            'pic_email' => 'john@example.com',
            'pic_phone' => '081234567890',
            'participants' => [
                [
                    'name' => 'Jane Doe',
                    'gender' => 'female',
                    'email' => 'jane@example.com',
                    'phone' => '089876543210',
                    'id_card' => '1234567890123456',
                    'address' => 'Jl. Emergency Test No. 1, Jakarta',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Emergency Contact',
                    'emergency_contact_number' => '081111111111',
                    'date_of_birth' => '1990-01-01',
                    'jersey_size' => 'M',
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token', // Add dummy token
        ];

        // 3. Submit Request
        $response = $this->postJson(route('events.register.store', $event->slug), $data);

        // 4. Assertions
        $response->assertStatus(200); // Or whatever success response is (JSON)
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('participants', [
            'name' => 'Jane Doe',
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_number' => '081111111111',
        ]);
    }

    public function test_registration_fails_without_emergency_contact()
    {
        // 0. Create User (Organizer)
        $user = User::factory()->create();

        // 1. Setup Event and Category
        $event = Event::create([
            'name' => 'Test Event 2',
            'slug' => 'test-event-2',
            'location' => 'Jakarta',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDays(1),
            'registration_close_at' => now()->addDays(5),
            'description' => 'Test Description',
            'is_published' => true,
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'GBK',
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'price_regular' => 200000,
            'quota' => 100,
            'start_time' => '06:00',
        ]);

        // 2. Prepare Registration Data (Missing Emergency Contact)
        $data = [
            'pic_name' => 'John Doe',
            'pic_email' => 'john@example.com',
            'pic_phone' => '081234567890',
            'participants' => [
                [
                    'name' => 'Jane Doe',
                    'gender' => 'female',
                    'email' => 'jane@example.com',
                    'phone' => '089876543210',
                    'id_card' => '1234567890123456',
                    'address' => 'Jl. Emergency Test No. 2, Jakarta',
                    'category_id' => $category->id,
                    // Missing emergency fields
                    'date_of_birth' => '1990-01-01',
                    'jersey_size' => 'M',
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token', // Add dummy token
        ];

        // 3. Submit Request
        $response = $this->postJson(route('events.register.store', $event->slug), $data);

        // 4. Assertions
        $response->assertInvalid([
            'participants.0.emergency_contact_name',
            'participants.0.emergency_contact_number',
        ]);
    }

    public function test_registration_fails_without_address(): void
    {
        $user = User::factory()->create();

        $event = Event::create([
            'name' => 'Test Event Address',
            'slug' => 'test-event-address',
            'location' => 'Jakarta',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDays(1),
            'registration_close_at' => now()->addDays(5),
            'description' => 'Test Description',
            'is_published' => true,
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'GBK',
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'price_regular' => 200000,
            'quota' => 100,
            'start_time' => '06:00',
        ]);

        $data = [
            'pic_name' => 'John Doe',
            'pic_email' => 'john-address@example.com',
            'pic_phone' => '081234567890',
            'participants' => [
                [
                    'name' => 'Jane Doe',
                    'gender' => 'female',
                    'email' => 'jane-address@example.com',
                    'phone' => '089876543210',
                    'id_card' => '1234567890123456',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Emergency Contact',
                    'emergency_contact_number' => '081111111111',
                    'date_of_birth' => '1990-01-01',
                    'jersey_size' => 'M',
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token',
        ];

        $response = $this->postJson(route('events.register.store', $event->slug), $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['participants.0.address']);
    }
}
