<?php

namespace Tests\Feature\EO;

use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantsExportAndApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_eo_participants_api_returns_address(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'distance_km' => 10,
            'code' => '10K',
            'price_regular' => 100000,
            'reg_start_at' => now()->subDay(),
            'reg_end_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $tx = Transaction::create([
            'event_id' => $event->id,
            'user_id' => $eo->id,
            'pic_data' => [],
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
            'name' => 'Runner',
            'gender' => 'male',
            'email' => 'runner@example.com',
            'phone' => '081234567890',
            'id_card' => '123',
            'address' => 'Jl. API Test No. 1, Jakarta',
            'status' => 'pending',
            'is_picked_up' => false,
        ]);

        $response = $this->actingAs($eo)->getJson(route('api.eo.events.participants', $event).'?per_page=50');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.0.address', 'Jl. API Test No. 1, Jakarta');
    }

    public function test_eo_participants_export_csv_contains_alamat_column(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'distance_km' => 10,
            'code' => '10K',
            'price_regular' => 100000,
            'reg_start_at' => now()->subDay(),
            'reg_end_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $tx = Transaction::create([
            'event_id' => $event->id,
            'user_id' => $eo->id,
            'pic_data' => [],
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
            'name' => 'Runner',
            'gender' => 'male',
            'email' => 'runner@example.com',
            'phone' => '081234567890',
            'id_card' => '123',
            'address' => 'Jl. CSV Test No. 1, Jakarta',
            'status' => 'pending',
            'is_picked_up' => false,
        ]);

        $response = $this->actingAs($eo)->get(route('eo.events.participants.export', $event));
        $response->assertOk();

        $content = $response->streamedContent();
        $this->assertStringContainsString('Alamat', $content);
        $this->assertStringContainsString('Jl. CSV Test No. 1, Jakarta', $content);
    }

    public function test_eo_participants_export_xlsx_is_zip_stream(): void
    {
        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        $response = $this->actingAs($eo)->get(route('eo.events.participants.export-xlsx', $event));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $content = $response->streamedContent();
        $this->assertStringStartsWith('PK', $content);
    }
}
