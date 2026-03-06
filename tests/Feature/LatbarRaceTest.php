<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LatbarRaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_participant_target_and_result_time()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'slug' => 'latbar-race-test',
        ]);

        $transaction = Transaction::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'payment_status' => 'paid',
        ]);

        $participant = Participant::factory()->create([
            'transaction_id' => $transaction->id,
            'name' => 'Runner Test',
            'target_time' => '00:00:00',
        ]);

        $this->assertNull($participant->result_time_ms);

        $response = $this->actingAs($user)
            ->postJson(route('events.latbar-race.target-time', $event->slug), [
                'participant_id' => $participant->id,
                'target_time' => '00:10:00',
                'result_time_ms' => 600000,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $participant->refresh();
        $this->assertEquals('00:10:00', $participant->target_time);
        $this->assertEquals(600000, $participant->result_time_ms);
    }
}
