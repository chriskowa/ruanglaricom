<?php

namespace Tests\Feature;

use App\Jobs\ProcessPaidEventTransaction;
use App\Models\CommunityInvoice;
use App\Models\CommunityParticipant;
use App\Models\CommunityRegistration;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EoCommunityImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_eo_can_import_paid_community_registration_and_dispatch_email_job(): void
    {
        Queue::fake();

        $eo = User::factory()->create([
            'role' => 'eo',
            'is_active' => true,
        ]);

        $event = Event::create([
            'name' => 'Managed Event',
            'slug' => 'managed-event',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDay(),
            'registration_close_at' => now()->addDays(5),
            'status' => 'published',
            'is_active' => true,
            'event_kind' => 'managed',
            'user_id' => $eo->id,
            'location_name' => 'GBK',
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '5K',
            'price_regular' => 150000,
            'quota' => 100,
            'is_active' => true,
        ]);

        $registration = CommunityRegistration::create([
            'event_id' => $event->id,
            'community_name' => 'Komunitas Test',
            'pic_name' => 'PIC',
            'pic_email' => 'pic@example.com',
            'pic_phone' => '081234567890',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        CommunityParticipant::create([
            'community_registration_id' => $registration->id,
            'event_id' => $event->id,
            'race_category_id' => $category->id,
            'name' => 'Peserta 1',
            'gender' => 'male',
            'email' => 'p1@example.com',
            'phone' => '081111111111',
            'id_card' => '1234567890123456',
            'address' => 'Alamat',
        ]);

        $paidTx = Transaction::create([
            'event_id' => $event->id,
            'user_id' => null,
            'pic_data' => [
                'name' => $registration->pic_name,
                'email' => $registration->pic_email,
                'phone' => $registration->pic_phone,
                'community_registration_id' => $registration->id,
                'community_name' => $registration->community_name,
            ],
            'total_original' => 150000,
            'coupon_id' => null,
            'discount_amount' => 0,
            'admin_fee' => 0,
            'final_amount' => 150000,
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_gateway' => 'moota',
            'payment_channel' => 'bank_transfer',
            'unique_code' => 0,
        ]);

        CommunityInvoice::create([
            'community_registration_id' => $registration->id,
            'transaction_id' => $paidTx->id,
            'payment_method' => 'moota',
            'status' => 'paid',
            'total_original' => 150000,
            'discount_amount' => 0,
            'admin_fee' => 0,
            'unique_code' => 0,
            'final_amount' => 150000,
        ]);

        $res = $this->actingAs($eo)->post(route('eo.events.community.import', [
            'event' => $event->id,
            'registration' => $registration->id,
        ]));

        $res->assertRedirect();
        $this->assertDatabaseHas('participants', [
            'race_category_id' => $category->id,
            'email' => 'p1@example.com',
            'id_card' => '1234567890123456',
        ]);

        $this->assertDatabaseHas('transactions', [
            'event_id' => $event->id,
            'payment_gateway' => 'community_import',
            'payment_status' => 'paid',
        ]);

        $registration->refresh();
        $this->assertNotNull($registration->imported_at);

        Queue::assertPushed(ProcessPaidEventTransaction::class);
    }
}

