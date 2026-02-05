<?php

namespace Tests\Feature;

use App\Models\CommunityInvoice;
use App\Models\CommunityParticipant;
use App\Models\CommunityRegistration;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_create_community_registration_add_participant_and_generate_moota_invoice(): void
    {
        $owner = User::factory()->create();

        $event = Event::create([
            'name' => 'Managed Event',
            'slug' => 'managed-event',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDay(),
            'registration_close_at' => now()->addDays(5),
            'status' => 'published',
            'is_active' => true,
            'event_kind' => 'managed',
            'user_id' => $owner->id,
            'location_name' => 'GBK',
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '5K',
            'price_regular' => 150000,
            'quota' => 100,
            'is_active' => true,
        ]);

        $startRes = $this->post(route('community.register.start'), [
            'event_id' => $event->id,
            'community_name' => 'Komunitas Test',
            'pic_name' => 'PIC',
            'pic_email' => 'pic@example.com',
            'pic_phone' => '081234567890',
        ]);

        $startRes->assertRedirect();
        $registration = CommunityRegistration::query()->latest()->first();
        $this->assertNotNull($registration);

        $addRes = $this->postJson(route('community.register.participants.store', $registration), [
            'name' => 'Peserta 1',
            'gender' => 'male',
            'email' => 'p1@example.com',
            'phone' => '081111111111',
            'id_card' => '1234567890123456',
            'address' => 'Alamat',
            'race_category_id' => $category->id,
        ]);

        $addRes->assertOk()->assertJson(['success' => true]);

        $invoiceRes = $this->postJson(route('community.register.invoice', [
            'event' => $event->slug,
            'registration' => $registration->id,
        ]), [
            'payment_method' => 'moota',
        ]);

        $invoiceRes->assertOk();
        $invoiceRes->assertJson([
            'success' => true,
            'payment_gateway' => 'moota',
            'payment_channel' => 'bank_transfer',
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseHas('community_invoices', [
            'community_registration_id' => $registration->id,
            'payment_method' => 'moota',
            'status' => 'pending',
        ]);
    }

    public function test_qris_invoice_returns_qris_payload(): void
    {
        config()->set('qris.static', '0002010102115802ID53033605802ID6304ABCD');
        config()->set('qris.nmid', 'ID10200300400');

        $owner = User::factory()->create();

        $event = Event::create([
            'name' => 'Managed Event',
            'slug' => 'managed-event',
            'start_at' => now()->addDays(10),
            'registration_open_at' => now()->subDay(),
            'registration_close_at' => now()->addDays(5),
            'status' => 'published',
            'is_active' => true,
            'event_kind' => 'managed',
            'user_id' => $owner->id,
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
            'status' => 'draft',
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

        $invoiceRes = $this->postJson(route('community.register.invoice', [
            'event' => $event->slug,
            'registration' => $registration->id,
        ]), [
            'payment_method' => 'qris',
        ]);

        $invoiceRes->assertOk();
        $this->assertNotEmpty($invoiceRes->json('qris_payload'));

        $invoice = CommunityInvoice::query()->where('community_registration_id', $registration->id)->latest()->first();
        $this->assertNotNull($invoice);
        $this->assertNotEmpty($invoice->qris_payload);
    }
}
