<?php

namespace Tests\Feature\EO;

use App\Mail\EventRegistrationSuccess;
use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ManualParticipantStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $event = Event::factory()->create();
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

        $response = $this->post(route('eo.events.participants.store', $event), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'id_card' => '123',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_non_eo_user_gets_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'runner']);
        $event = Event::factory()->create(['user_id' => $user->id]);
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

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $response = $this->actingAs($user)->post(route('eo.events.participants.store', $event), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'id_card' => '123',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_other_eo_cannot_add_participant_to_event(): void
    {
        $owner = User::factory()->create(['role' => 'eo']);
        $otherEo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $owner->id]);
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

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $response = $this->actingAs($otherEo)->post(route('eo.events.participants.store', $event), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'id_card' => '123',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_eo_can_add_participant_and_email_is_sent_synchronously(): void
    {
        Mail::fake();
        
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
        
        $data = [
            'name' => 'John Doe',
            'gender' => 'male',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'id_card' => '1234567890123456',
            'category_id' => $category->id,
            'date_of_birth' => '1990-01-01',
            'target_time' => '01:00:00',
            'jersey_size' => 'M',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_number' => '081234567891',
        ];
        
        $response = $this->actingAs($eo)->postJson(route('eo.events.participants.store', $event), $data);
        
        $response->assertStatus(201);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('participants', 1);
        
        Mail::assertSent(EventRegistrationSuccess::class, function ($mail) {
            return $mail->hasTo('john@example.com');
        });
    }

    public function test_duplicate_email_is_rejected(): void
    {
        Mail::fake();
        
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
        
        // First participant
        $this->actingAs($eo)->postJson(route('eo.events.participants.store', $event), [
            'name' => 'John Doe',
            'gender' => 'male',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'id_card' => '1234567890123456',
            'category_id' => $category->id,
        ]);
        
        // Second participant (duplicate email)
        $response = $this->actingAs($eo)->postJson(route('eo.events.participants.store', $event), [
            'name' => 'John Doe 2',
            'gender' => 'male',
            'email' => 'john@example.com',
            'phone' => '081234567891',
            'id_card' => '1234567890123457',
            'category_id' => $category->id,
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_eo_can_add_participant_with_queue_option(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

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

        $data = [
            'name' => 'Queue Runner',
            'email' => 'queue@example.com',
            'phone' => '081234567899',
            'id_card' => '1234567890123456',
            'category_id' => $category->id,
            'use_queue' => true,
        ];

        $response = $this->actingAs($eo)->postJson(route('eo.events.participants.store', $event), $data);

        $response->assertStatus(201);
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendEventRegistrationNotification::class);
    }

    public function test_eo_can_disable_whatsapp_notification(): void
    {
        Mail::fake();

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

        $data = [
            'name' => 'No WA Runner',
            'email' => 'nowa@example.com',
            'phone' => '081234567898',
            'id_card' => '1234567890123456',
            'category_id' => $category->id,
            'send_whatsapp' => false,
        ];

        $response = $this->actingAs($eo)->postJson(route('eo.events.participants.store', $event), $data);

        $response->assertStatus(201);
        
        $transaction = Transaction::whereHas('participants', function($q) {
            $q->where('email', 'nowa@example.com');
        })->first();

        $this->assertFalse($transaction->pic_data['send_whatsapp'] ?? true);
    }

    public function test_eo_manual_entry_uses_early_bird_price_when_eligible(): void
    {
        Mail::fake();
        
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);
        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => 'Early Bird 10K',
            'distance_km' => 10,
            'code' => '10K',
            'quota' => 100,
            'min_age' => 12,
            'max_age' => 99,
            'cutoff_minutes' => 120,
            'price_regular' => 100000,
            'price_early' => 80000,
            'early_bird_quota' => 50,
            'early_bird_end_at' => now()->addDays(2),
            'reg_start_at' => now()->subDay(),
            'reg_end_at' => now()->addDay(),
            'is_active' => true,
        ]);
        
        $data = [
            'name' => 'Early Bird Runner',
            'email' => 'early@example.com',
            'phone' => '081234567800',
            'id_card' => '1234567890123456',
            'category_id' => $category->id,
        ];
        
        $response = $this->actingAs($eo)->postJson(route('eo.events.participants.store', $event), $data);
        
        $response->assertStatus(201);
        
        $participant = Participant::where('email', 'early@example.com')->first();
        $this->assertEquals('early', $participant->price_type);
        
        $transaction = $participant->transaction;
        $this->assertEquals(80000, $transaction->total_original);
    }

    public function test_eo_manual_entry_uses_regular_price_when_early_bird_expired(): void
    {
        Mail::fake();
        
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);
        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => 'Expired 10K',
            'distance_km' => 10,
            'code' => '10K',
            'quota' => 100,
            'min_age' => 12,
            'max_age' => 99,
            'cutoff_minutes' => 120,
            'price_regular' => 100000,
            'price_early' => 80000,
            'early_bird_quota' => 50,
            'early_bird_end_at' => now()->subDays(1), // Expired
            'reg_start_at' => now()->subDay(),
            'reg_end_at' => now()->addDay(),
            'is_active' => true,
        ]);
        
        $data = [
            'name' => 'Regular Runner',
            'email' => 'regular@example.com',
            'phone' => '081234567801',
            'id_card' => '1234567890123456',
            'category_id' => $category->id,
        ];
        
        $response = $this->actingAs($eo)->postJson(route('eo.events.participants.store', $event), $data);
        
        $response->assertStatus(201);
        
        $participant = Participant::where('email', 'regular@example.com')->first();
        $this->assertEquals('regular', $participant->price_type);
        
        $transaction = $participant->transaction;
        $this->assertEquals(100000, $transaction->total_original);
    }

    public function test_eo_manual_entry_uses_regular_price_when_early_bird_quota_full(): void
    {
        Mail::fake();
        
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);
        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => 'Full 10K',
            'distance_km' => 10,
            'code' => '10K',
            'quota' => 100,
            'min_age' => 12,
            'max_age' => 99,
            'cutoff_minutes' => 120,
            'price_regular' => 100000,
            'price_early' => 80000,
            'early_bird_quota' => 1, // Only 1 spot
            'early_bird_end_at' => now()->addDays(2),
            'reg_start_at' => now()->subDay(),
            'reg_end_at' => now()->addDay(),
            'is_active' => true,
        ]);
        
        // Fill the quota
        $t1 = Transaction::create([
            'event_id' => $event->id,
            'user_id' => $eo->id,
            'pic_data' => [],
            'total_original' => 80000,
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_gateway' => 'manual',
            'final_amount' => 80000,
        ]);
        
        Participant::create([
            'transaction_id' => $t1->id,
            'race_category_id' => $category->id,
            'name' => 'Early Runner',
            'email' => 'early@test.com',
            'phone' => '000',
            'id_card' => '000',
            'status' => 'pending',
            'price_type' => 'early', // Mark as early
        ]);
        
        // Now try to add another one
        $data = [
            'name' => 'Late Runner',
            'email' => 'late@example.com',
            'phone' => '081234567802',
            'id_card' => '1234567890123456',
            'category_id' => $category->id,
            'use_queue' => true,
        ];
        
        $response = $this->actingAs($eo)->postJson(route('eo.events.participants.store', $event), $data);
        
        $response->assertStatus(201);
        
        $participant = Participant::where('email', 'late@example.com')->first();
        $this->assertEquals('regular', $participant->price_type);
        
        $transaction = $participant->transaction;
        $this->assertEquals(100000, $transaction->total_original);
    }
}
