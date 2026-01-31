<?php

namespace Tests\Feature;

use App\Jobs\SendEventRegistrationNotification;
use App\Models\Event;
use App\Models\EventEmailDeliveryLog;
use App\Models\Participant;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventTicketEmailLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_delivery_logs_for_each_recipient(): void
    {
        Mail::fake();

        $event = Event::factory()->create();
        $txn = Transaction::create([
            'event_id' => $event->id,
            'pic_data' => ['email' => 'pic@example.com', 'name' => 'PIC', 'phone' => '08123456789'],
            'total_original' => 100000,
            'discount_amount' => 0,
            'admin_fee' => 0,
            'final_amount' => 100000,
            'payment_status' => 'paid',
            'payment_gateway' => 'midtrans',
            'unique_code' => 0,
        ]);

        Participant::create([
            'transaction_id' => $txn->id,
            'race_category_id' => null,
            'name' => 'A',
            'gender' => 'male',
            'phone' => '0812',
            'email' => 'a@example.com',
            'id_card' => '1',
            'emergency_contact_name' => 'X',
            'emergency_contact_number' => '1',
            'status' => 'confirmed',
            'price_type' => 'regular',
        ]);

        Participant::create([
            'transaction_id' => $txn->id,
            'race_category_id' => null,
            'name' => 'B',
            'gender' => 'male',
            'phone' => '0813',
            'email' => 'pic@example.com',
            'id_card' => '2',
            'emergency_contact_name' => 'Y',
            'emergency_contact_number' => '2',
            'status' => 'confirmed',
            'price_type' => 'regular',
        ]);

        (new SendEventRegistrationNotification($txn))->handle();

        $this->assertSame(2, EventEmailDeliveryLog::query()->where('event_id', $event->id)->where('channel', 'email')->where('status', 'sent')->count());
    }
}

