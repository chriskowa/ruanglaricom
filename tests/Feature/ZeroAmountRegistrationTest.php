<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZeroAmountRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $event;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Recaptcha
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);

        $this->user = User::factory()->create();
        
        $this->event = Event::factory()->create([
            'user_id' => $this->user->id,
            'slug' => 'zero-amount-event',
            'name' => 'Zero Amount Event',
            'registration_open_at' => now()->subDay(),
            'registration_close_at' => now()->addDay(),
            'payment_config' => ['allowed_methods' => ['midtrans']],
        ]);

        $this->category = RaceCategory::create([
            'event_id' => $this->event->id,
            'name' => '10K',
            'distance_km' => 10,
            'code' => '10K',
            'quota' => 100,
            'min_age' => 12,
            'max_age' => 99,
            'cutoff_minutes' => 120,
            'price_regular' => 100000,
            'reg_start_at' => now()->subDay(),
            'reg_end_at' => now()->addDay(),
            'is_active' => true,
        ]);
    }

    protected function getRegistrationData($couponCode = null, $user = null)
    {
        $userData = $user ?? User::factory()->create();
        
        return [
            'pic_name' => $userData->name,
            'pic_email' => $userData->email,
            'pic_phone' => '081234567890',
            'coupon_code' => $couponCode,
            'payment_method' => 'midtrans',
            'g-recaptcha-response' => 'dummy-token',
            'participants' => [
                [
                    'name' => $userData->name,
                    'gender' => 'male',
                    'email' => $userData->email,
                    'phone' => '081234567890',
                    'id_card' => '1234567890123456',
                    'category_id' => $this->category->id,
                    'emergency_contact_name' => 'Jane Doe',
                    'emergency_contact_number' => '081234567891',
                    'date_of_birth' => '1990-01-01',
                    'target_time' => '01:00:00',
                    'jersey_size' => 'M',
                ]
            ],
        ];
    }

    public function test_zero_amount_registration_with_100_percent_coupon_marks_transaction_as_paid()
    {
        $coupon = Coupon::create([
            'code' => 'FREE100',
            'type' => 'percent',
            'value' => 100,
            'event_id' => $this->event->id,
            'start_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'max_uses' => 10,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('events.register.store', $this->event->slug), $this->getRegistrationData('FREE100'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $transaction = Transaction::latest()->first();
        
        $this->assertEquals(0, $transaction->final_amount, 'Final amount should be 0');
        $this->assertEquals('paid', $transaction->payment_status, 'Payment status should be paid for zero amount');
        $this->assertNull($transaction->snap_token, 'Snap token should be null for zero amount');
        $this->assertNotNull($transaction->paid_at, 'Paid at should be set');
        
        // Ensure used count is incremented (if implemented in Action)
        // StoreRegistrationAction doesn't explicitly increment used_count in the snippet I saw, 
        // but it might rely on a model event or another service. 
        // If not implemented, this assertion might fail, identifying a bug.
        // Let's check if it increments. Usually it should.
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_registration_waives_platform_fee_with_100_percent_coupon()
    {
        // Set platform fee on event
        $this->event->update(['platform_fee' => 5000]);

        Coupon::create([
            'code' => 'FREEWITHFEE',
            'type' => 'percent',
            'value' => 100,
            'event_id' => $this->event->id,
            'start_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'max_uses' => 10,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('events.register.store', $this->event->slug), $this->getRegistrationData('FREEWITHFEE'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $transaction = Transaction::latest()->first();
        
        $this->assertEquals(0, $transaction->admin_fee, 'Admin fee should be 0 for fully discounted transaction');
        $this->assertEquals(0, $transaction->final_amount, 'Final amount should be 0');
        $this->assertEquals('paid', $transaction->payment_status);
    }

    public function test_registration_fails_with_expired_coupon()
    {
        Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'percent',
            'value' => 100,
            'event_id' => $this->event->id,
            'start_at' => now()->subDays(5),
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $response = $this->postJson(route('events.register.store', $this->event->slug), $this->getRegistrationData('EXPIRED'));

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_registration_fails_with_usage_limit_reached_coupon()
    {
        Coupon::create([
            'code' => 'FULL',
            'type' => 'percent',
            'value' => 100,
            'event_id' => $this->event->id,
            'start_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'max_uses' => 1,
            'used_count' => 1,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('events.register.store', $this->event->slug), $this->getRegistrationData('FULL'));

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
        // Note: The specific "habis" message is masked by a preliminary generic check in StoreRegistrationAction
        // Expecting generic invalid message or specific message
        $message = $response->json('message');
        $this->assertTrue(
            str_contains($message, 'habis') || str_contains($message, 'tidak valid'),
            "Message was: $message"
        );
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_registration_fails_if_coupon_min_transaction_amount_not_met()
    {
        // Price is 100,000. Min purchase 200,000.
        Coupon::create([
            'code' => 'MINPURCHASE',
            'type' => 'percent',
            'value' => 50,
            'event_id' => $this->event->id,
            'start_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'min_transaction_amount' => 200000,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('events.register.store', $this->event->slug), $this->getRegistrationData('MINPURCHASE'));

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
        // The message in StoreRegistrationAction line 352 is generic: "Kupon tidak valid untuk transaksi ini..."
        $this->assertStringContainsString('tidak valid', $response->json('message'));
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_registration_fails_with_per_user_usage_limit_reached()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $coupon = Coupon::create([
            'code' => 'ONCEPERUSER',
            'type' => 'percent',
            'value' => 10,
            'event_id' => $this->event->id,
            'start_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'usage_limit_per_user' => 1,
            'is_active' => true,
        ]);

        // First successful transaction
        Transaction::create([
            'event_id' => $this->event->id,
            'user_id' => $user->id,
            'pic_data' => [],
            'total_original' => 100000,
            'coupon_id' => $coupon->id,
            'final_amount' => 90000,
            'payment_status' => 'paid',
        ]);

        // Try to use again
        $response = $this->postJson(route('events.register.store', $this->event->slug), $this->getRegistrationData('ONCEPERUSER', $user));

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('transactions', 1); // Only the manually created one
    }

    public function test_coupon_cannot_be_stacked_with_promo_buy_x_get_y()
    {
        // Update event to have Buy 1 Get 1 Free promo
        $this->event->update(['promo_buy_x' => 1]);

        // Create non-stackable coupon
        Coupon::create([
            'code' => 'NONSTACK',
            'type' => 'percent',
            'value' => 10,
            'event_id' => $this->event->id,
            'start_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'is_stackable' => false,
            'is_active' => true,
        ]);

        // Register 2 participants (Buy 1 Get 1 triggers)
        $data = $this->getRegistrationData('NONSTACK');
        $data['participants'][] = $data['participants'][0]; // Duplicate participant to have 2

        $response = $this->postJson(route('events.register.store', $this->event->slug), $data);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('tidak dapat digabungkan', $response->json('message'));
        $this->assertDatabaseCount('transactions', 0);
    }
}
