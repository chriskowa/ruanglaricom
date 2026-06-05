<?php

namespace Tests\Feature\EO;

use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateParticipantFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_eo_can_update_participant_fields(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);
        $category = RaceCategory::create([
            'event_id' => $event->id,
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

        $transaction = Transaction::create([
            'event_id' => $event->id,
            'user_id' => $eo->id,
            'pic_data' => [
                'name' => 'PIC Name',
                'email' => 'pic@example.com',
                'phone' => '081234567890',
            ],
            'total_original' => 100000,
            'final_amount' => 100000,
            'payment_status' => 'paid',
            'payment_gateway' => 'manual',
            'unique_code' => 0,
        ]);

        $participant = Participant::create([
            'transaction_id' => $transaction->id,
            'race_category_id' => $category->id,
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'phone' => '081234567890',
            'id_card' => 'ORIGINAL-ID-CARD',
            'gender' => 'male',
            'emergency_contact_name' => 'Original Emergency',
            'emergency_contact_number' => '081234567899',
            'status' => 'success',
        ]);

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '081234567888',
            'gender' => 'female',
            'id_card' => 'UPDATED-ID-CARD',
            'emergency_contact_name' => 'Updated Emergency',
            'emergency_contact_number' => '081234567777',
            'race_category_id' => $category->id,
        ];

        $response = $this->actingAs($eo)->putJson(
            route('eo.events.participants.update', [$event, $participant]),
            $updateData
        );

        $response->assertStatus(200);

        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.name', 'Updated Name');
        $response->assertJsonPath('data.email', 'updated@example.com');
        $response->assertJsonPath('data.phone', '081234567888');
        $response->assertJsonPath('data.gender', 'female');
        $response->assertJsonPath('data.id_card', 'UPDATED-ID-CARD');
        $response->assertJsonPath('data.emergency_contact_name', 'Updated Emergency');
        $response->assertJsonPath('data.emergency_contact_number', '081234567777');

        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'id_card' => 'UPDATED-ID-CARD',
            'emergency_contact_name' => 'Updated Emergency',
            'emergency_contact_number' => '081234567777',
        ]);
    }

    public function test_eo_update_requires_id_card(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);
        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'distance_km' => 10,
            'code' => '10K',
            'quota' => 100,
            'price_regular' => 100000,
            'reg_start_at' => now()->subDay(),
            'reg_end_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'event_id' => $event->id,
            'user_id' => $eo->id,
            'pic_data' => [
                'name' => 'PIC Name',
                'email' => 'pic@example.com',
                'phone' => '081234567890',
            ],
            'total_original' => 100000,
            'final_amount' => 100000,
            'payment_status' => 'paid',
            'payment_gateway' => 'manual',
            'unique_code' => 0,
        ]);

        $participant = Participant::create([
            'transaction_id' => $transaction->id,
            'race_category_id' => $category->id,
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'phone' => '081234567890',
            'id_card' => 'ORIGINAL-ID-CARD',
            'gender' => 'male',
            'status' => 'success',
        ]);

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '081234567888',
            'gender' => 'female',
            'id_card' => '', // Empty ID Card
            'race_category_id' => $category->id,
        ];

        $response = $this->actingAs($eo)->putJson(
            route('eo.events.participants.update', [$event, $participant]),
            $updateData
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_card']);
    }

    public function test_eo_can_update_participant_with_coupon(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);
        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'distance_km' => 10,
            'code' => '10K',
            'quota' => 100,
            'price_regular' => 100000,
            'reg_start_at' => now()->subDay(),
            'reg_end_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $coupon = \App\Models\Coupon::create([
            'event_id' => $event->id,
            'code' => 'PROMO50',
            'type' => 'percent',
            'value' => 50.00,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'event_id' => $event->id,
            'user_id' => $eo->id,
            'pic_data' => [
                'name' => 'PIC Name',
                'email' => 'pic@example.com',
                'phone' => '081234567890',
            ],
            'total_original' => 100000,
            'final_amount' => 100000,
            'payment_status' => 'paid',
            'payment_gateway' => 'manual',
            'unique_code' => 0,
        ]);

        $participant = Participant::create([
            'transaction_id' => $transaction->id,
            'race_category_id' => $category->id,
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'phone' => '081234567890',
            'id_card' => 'ORIGINAL-ID-CARD',
            'gender' => 'male',
            'status' => 'success',
        ]);

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '081234567888',
            'gender' => 'female',
            'id_card' => 'UPDATED-ID-CARD',
            'race_category_id' => $category->id,
            'coupon_id' => $coupon->id,
        ];

        $response = $this->actingAs($eo)->putJson(
            route('eo.events.participants.update', [$event, $participant]),
            $updateData
        );

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.coupon_id', $coupon->id);
        $response->assertJsonPath('data.coupon_code', 'PROMO50');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'coupon_id' => $coupon->id,
            'discount_amount' => 50000.00, // 50% of 100,000
            'final_amount' => 50000.00,
        ]);
    }
}
