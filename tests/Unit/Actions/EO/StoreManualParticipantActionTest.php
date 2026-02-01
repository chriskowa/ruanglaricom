<?php

namespace Tests\Unit\Actions\EO;

use App\Actions\EO\StoreManualParticipantAction;
use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StoreManualParticipantActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_paid_manual_transaction_and_participant(): void
    {
        $operator = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $operator->id]);

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

        $action = app(StoreManualParticipantAction::class);
        $tx = $action->execute($event, [
            'name' => 'Budi',
            'gender' => 'male',
            'email' => 'BUDI@EXAMPLE.COM',
            'phone' => '081234567890',
            'id_card' => '1234567890',
            'category_id' => $category->id,
            'date_of_birth' => '1990-01-01',
            'target_time' => '01:30:00',
            'jersey_size' => 'M',
            'emergency_contact_name' => 'Siti',
            'emergency_contact_number' => '081234567891',
        ], $operator);

        $this->assertInstanceOf(Transaction::class, $tx);
        $this->assertEquals('manual', $tx->payment_gateway);
        $this->assertEquals('paid', $tx->payment_status);
        $this->assertEquals(0, (int) $tx->admin_fee);
        $this->assertEquals(100000, (int) $tx->final_amount);

        $participant = Participant::where('transaction_id', $tx->id)->first();
        $this->assertNotNull($participant);
        $this->assertEquals('budi@example.com', $participant->email);
        $this->assertEquals($category->id, $participant->race_category_id);
    }

    public function test_it_rejects_duplicate_email_within_event(): void
    {
        $operator = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $operator->id]);

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

        $action = app(StoreManualParticipantAction::class);
        $action->execute($event, [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'id_card' => '1234567890',
            'category_id' => $category->id,
        ], $operator);

        $this->expectException(ValidationException::class);
        $action->execute($event, [
            'name' => 'Budi 2',
            'email' => 'BUDI@EXAMPLE.COM',
            'phone' => '081234567890',
            'id_card' => '2234567890',
            'category_id' => $category->id,
        ], $operator);
    }

    public function test_it_rejects_when_quota_is_full(): void
    {
        $operator = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $operator->id]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'distance_km' => 10,
            'code' => '10K',
            'quota' => 1,
            'min_age' => 12,
            'max_age' => 99,
            'cutoff_minutes' => 120,
            'price_regular' => 100000,
            'reg_start_at' => now()->subDay(),
            'reg_end_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $tx = Transaction::create([
            'event_id' => $event->id,
            'user_id' => $operator->id,
            'pic_data' => ['email' => 'x@example.com'],
            'total_original' => 100000,
            'discount_amount' => 0,
            'admin_fee' => 0,
            'final_amount' => 100000,
            'payment_status' => 'paid',
            'payment_gateway' => 'manual',
            'unique_code' => 0,
            'paid_at' => now(),
        ]);

        Participant::create([
            'transaction_id' => $tx->id,
            'race_category_id' => $category->id,
            'name' => 'Existing',
            'email' => 'existing@example.com',
            'phone' => '081234567890',
            'id_card' => '999',
            'status' => 'pending',
            'is_picked_up' => false,
        ]);

        $action = app(StoreManualParticipantAction::class);
        $this->expectException(ValidationException::class);
        $action->execute($event, [
            'name' => 'New',
            'email' => 'new@example.com',
            'phone' => '081234567890',
            'id_card' => '1234567890',
            'category_id' => $category->id,
        ], $operator);
    }
}

