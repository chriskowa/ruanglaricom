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

class TargetTimeRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock ReCaptcha
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_participant_registration_stores_valid_target_time()
    {
        // Mock MidtransService
        $this->mock(MidtransService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createEventTransaction')
                ->andReturn([
                    'success' => true, 
                    'snap_token' => 'dummy-snap-token', 
                    'order_id' => 'dummy-order-id'
                ]);
        });

        // Create User (Organizer)
        $user = User::factory()->create();

        // Create Event
        $event = Event::create([
            'name' => 'Target Time Event',
            'slug' => 'target-time-event',
            'location' => 'Jakarta',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDays(1),
            'registration_close_at' => now()->addDays(5),
            'description' => 'Test Description',
            'is_published' => true,
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'Monas',
        ]);

        // Create Category
        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'price_regular' => 150000,
            'quota' => 100,
            'start_time' => '06:00',
        ]);

        // Prepare Data with Valid Target Time
        $data = [
            'pic_name' => 'Runner One',
            'pic_email' => 'runner@example.com',
            'pic_phone' => '081234567890',
            'participants' => [
                [
                    'name' => 'Runner One',
                    'gender' => 'male',
                    'email' => 'runner@example.com',
                    'phone' => '081234567890',
                    'id_card' => '1234567890123456',
                    'address' => 'Jl. Test No. 1, Jakarta',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Mom',
                    'emergency_contact_number' => '081111111111',
                    'date_of_birth' => '1990-01-01',
                    'jersey_size' => 'M',
                    'target_time' => '01:45:30', // Valid: 1h 45m 30s
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token',
        ];

        // Submit
        $response = $this->postJson(route('events.register.store', $event->slug), $data);

        // Assert Success
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert Database
        $this->assertDatabaseHas('participants', [
            'email' => 'runner@example.com',
            'target_time' => '01:45:30',
            'address' => 'Jl. Test No. 1, Jakarta',
        ]);
    }

    public function test_participant_registration_rejects_invalid_target_time_format()
    {
        // Mock MidtransService (should not be called, but safe to mock)
        $this->mock(MidtransService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createEventTransaction');
        });

        $user = User::factory()->create();
        $event = Event::create([
            'name' => 'Target Time Event Invalid',
            'slug' => 'target-time-event-invalid',
            'start_at' => now()->addDays(10),
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'Test Location',
        ]);
        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'price_regular' => 150000,
        ]);

        $data = [
            'pic_name' => 'Runner Invalid',
            'pic_email' => 'invalid@example.com',
            'pic_phone' => '081234567890',
            'participants' => [
                [
                    'name' => 'Runner Invalid',
                    'gender' => 'male',
                    'email' => 'invalid@example.com',
                    'phone' => '081234567890',
                    'id_card' => '1234567890123456',
                    'address' => 'Jl. Test No. 2, Jakarta',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Mom',
                    'emergency_contact_number' => '081111111111',
                    'date_of_birth' => '1990-01-01',
                    'jersey_size' => 'M',
                    'target_time' => '25:00:00', // Invalid Hour (25)
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token',
        ];

        $response = $this->postJson(route('events.register.store', $event->slug), $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['participants.0.target_time']);
    }

    public function test_participant_registration_rejects_invalid_minute_second()
    {
        $user = User::factory()->create();
        $event = Event::create([
            'name' => 'Target Time Event Invalid 2',
            'slug' => 'target-time-event-invalid-2',
            'start_at' => now()->addDays(10),
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'Test Location',
        ]);
        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'price_regular' => 150000,
        ]);

        $data = [
            'pic_name' => 'Runner Invalid',
            'pic_email' => 'invalid@example.com',
            'pic_phone' => '081234567890',
            'participants' => [
                [
                    'name' => 'Runner Invalid',
                    'gender' => 'male',
                    'email' => 'invalid@example.com',
                    'phone' => '081234567890',
                    'id_card' => '1234567890123456',
                    'address' => 'Jl. Test No. 3, Jakarta',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Mom',
                    'emergency_contact_number' => '081111111111',
                    'date_of_birth' => '1990-01-01',
                    'jersey_size' => 'M',
                    'target_time' => '01:60:00', // Invalid Minute (60)
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token',
        ];

        $response = $this->postJson(route('events.register.store', $event->slug), $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['participants.0.target_time']);
    }

    public function test_participant_registration_rejects_zero_duration()
    {
        $user = User::factory()->create();
        $event = Event::create([
            'name' => 'Target Time Event Zero',
            'slug' => 'target-time-event-zero',
            'start_at' => now()->addDays(10),
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'Test Location',
        ]);
        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'price_regular' => 150000,
        ]);

        $data = [
            'pic_name' => 'Runner Zero',
            'pic_email' => 'zero@example.com',
            'pic_phone' => '081234567890',
            'participants' => [
                [
                    'name' => 'Runner Zero',
                    'gender' => 'male',
                    'email' => 'zero@example.com',
                    'phone' => '081234567890',
                    'id_card' => '1234567890123456',
                    'address' => 'Jl. Test No. 4, Jakarta',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Mom',
                    'emergency_contact_number' => '081111111111',
                    'date_of_birth' => '1990-01-01',
                    'jersey_size' => 'M',
                    'target_time' => '00:00:00', // Invalid Zero Duration
                ],
            ],
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token',
        ];

        $response = $this->postJson(route('events.register.store', $event->slug), $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['participants.0.target_time']);
    }
}
