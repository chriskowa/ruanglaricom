<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MootaPaymentModalFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_moota_registration_returns_amount_and_unique_code_and_status_can_be_polled(): void
    {
        $user = User::factory()->create();

        $event = Event::create([
            'name' => 'Test Event Moota',
            'slug' => 'test-event-moota',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDay(),
            'registration_close_at' => now()->addDays(5),
            'status' => 'published',
            'user_id' => $user->id,
            'location_name' => 'GBK',
            'payment_config' => [
                'allowed_methods' => ['moota'],
            ],
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '5K',
            'price_regular' => 150000,
            'quota' => 100,
            'is_active' => true,
        ]);

        $payload = [
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
                    'address' => 'Jl. Test No. 1, Jakarta',
                    'category_id' => $category->id,
                    'emergency_contact_name' => 'Emergency Contact',
                    'emergency_contact_number' => '081111111111',
                    'date_of_birth' => '1990-01-01',
                    'jersey_size' => 'M',
                ],
            ],
            'payment_method' => 'moota',
            'g-recaptcha-response' => 'dummy-token',
        ];

        $res = $this->postJson(route('events.register.store', $event->slug), $payload);

        $res->assertOk();
        $res->assertJson([
            'success' => true,
            'payment_gateway' => 'moota',
            'payment_status' => 'pending',
        ]);

        $txId = $res->json('transaction_id');
        $this->assertNotEmpty($txId);
        $this->assertGreaterThan(0, (float) $res->json('final_amount'));
        $this->assertGreaterThan(0, (int) $res->json('unique_code'));

        $statusRes = $this->getJson(route('api.events.payments.status', [
            'slug' => $event->slug,
            'transaction' => $txId,
        ]).'?phone='.urlencode('081234567890'));

        $statusRes->assertOk();
        $statusRes->assertJson([
            'success' => true,
            'transaction' => [
                'id' => (int) $txId,
                'payment_status' => 'pending',
            ],
        ]);
    }
}
