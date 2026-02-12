<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventEmailMinuteCounter;
use App\Models\Participant;
use App\Models\Transaction;
use App\Services\EventRegistrationEmailDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NonInstantTicketRateLimitCounterTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_instant_ticket_rate_limit_creates_counters_and_spills_to_next_minute(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-31 10:00:10'));

        $event = Event::factory()->create([
            'is_instant_notification' => false,
            'ticket_email_rate_limit_per_minute' => 2,
        ]);

        $t1 = $this->makeTransactionWithParticipant($event, 'pic1@example.com', 'p1@example.com');
        $t2 = $this->makeTransactionWithParticipant($event, 'pic2@example.com', 'p2@example.com');

        $dispatcher = app(EventRegistrationEmailDispatcher::class);
        $dispatcher->dispatch($t1);
        $dispatcher->dispatch($t2);

        $this->assertSame(2, (int) EventEmailMinuteCounter::query()
            ->where('event_id', $event->id)
            ->where('minute_at', Carbon::parse('2026-01-31 10:00:00'))
            ->value('reserved_emails'));

        $this->assertSame(2, (int) EventEmailMinuteCounter::query()
            ->where('event_id', $event->id)
            ->where('minute_at', Carbon::parse('2026-01-31 10:01:00'))
            ->value('reserved_emails'));
    }

    private function makeTransactionWithParticipant(Event $event, string $picEmail, string $participantEmail): Transaction
    {
        $txn = Transaction::create([
            'event_id' => $event->id,
            'pic_data' => ['email' => $picEmail, 'name' => 'PIC', 'phone' => '08123456789'],
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
            'event_package_id' => null,
            'race_category_id' => null,
            'name' => 'Peserta',
            'gender' => 'male',
            'phone' => '0812',
            'email' => $participantEmail,
            'id_card' => '1',
            'emergency_contact_name' => 'X',
            'emergency_contact_number' => '1',
            'status' => 'confirmed',
            'price_type' => 'regular',
        ]);

        return $txn;
    }
}
