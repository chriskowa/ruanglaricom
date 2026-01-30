<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Transaction;
use Tests\TestCase;

class EmailTicketQrOptionTest extends TestCase
{
    public function test_email_ticket_renders_qr_when_enabled(): void
    {
        $event = new Event([
            'name' => 'Test Event',
            'slug' => 'test-event',
            'location_name' => 'Test Location',
            'start_at' => now(),
            'ticket_email_use_qr' => true,
        ]);

        $participant = new Participant([
            'id' => 1,
            'name' => 'Runner',
            'email' => 'runner@example.com',
            'phone' => '081234',
            'transaction_id' => 10,
            'bib_number' => '1001',
        ]);
        $participant->setRelation('category', new RaceCategory(['name' => '5K']));

        $transaction = new Transaction([
            'id' => 10,
            'final_amount' => 150000,
            'payment_status' => 'paid',
        ]);

        $view = $this->view('emails.events.registration-success', [
            'event' => $event,
            'participants' => collect([$participant]),
            'transaction' => $transaction,
            'notifiableName' => 'Runner',
        ]);

        $view->assertSee('api.qrserver.com', false);
    }

    public function test_email_ticket_hides_qr_when_disabled(): void
    {
        $event = new Event([
            'name' => 'Test Event',
            'slug' => 'test-event',
            'location_name' => 'Test Location',
            'start_at' => now(),
            'ticket_email_use_qr' => false,
        ]);

        $participant = new Participant([
            'id' => 1,
            'name' => 'Runner',
            'email' => 'runner@example.com',
            'phone' => '081234',
            'transaction_id' => 10,
            'bib_number' => '1001',
        ]);
        $participant->setRelation('category', new RaceCategory(['name' => '5K']));

        $transaction = new Transaction([
            'id' => 10,
            'final_amount' => 150000,
            'payment_status' => 'paid',
        ]);

        $view = $this->view('emails.events.registration-success', [
            'event' => $event,
            'participants' => collect([$participant]),
            'transaction' => $transaction,
            'notifiableName' => 'Runner',
        ]);

        $view->assertDontSee('api.qrserver.com', false);
        $view->assertSee('Nomor Tiket', false);
    }
}

