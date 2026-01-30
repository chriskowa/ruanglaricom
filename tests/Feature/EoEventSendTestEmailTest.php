<?php

namespace Tests\Feature;

use App\Mail\EventRegistrationSuccess;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EoEventSendTestEmailTest extends TestCase
{
    private function resetSchema(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('phone')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('location_name')->nullable();
            $table->string('status')->default('published');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('custom_email_message')->nullable();
            $table->boolean('ticket_email_use_qr')->default(true);
            $table->timestamps();
        });
    }

    public function test_send_test_email_sends_mailable_and_returns_remaining_quota(): void
    {
        $this->resetSchema();
        Mail::fake();

        $eo = User::factory()->create(['role' => 'eo', 'phone' => '08123456789']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        $payload = [
            'test_email' => 'test@example.com',
            'custom_email_message' => '<p>Halo <strong>Tester</strong></p>',
            'ticket_email_use_qr' => '1',
            'name' => 'Event Baru',
        ];

        $resp = $this->actingAs($eo)->postJson(route('eo.events.send-test-email', $event), $payload);
        $resp->assertOk();
        $resp->assertJsonPath('success', true);
        $resp->assertJsonPath('remaining', 2);

        Mail::assertSent(EventRegistrationSuccess::class, function (EventRegistrationSuccess $mailable) use ($payload) {
            return $mailable->hasTo('test@example.com')
                && $mailable->event->name === $payload['name']
                && $mailable->event->custom_email_message === $payload['custom_email_message']
                && (bool) ($mailable->event->ticket_email_use_qr ?? false) === true;
        });
    }

    public function test_send_test_email_validates_email_format(): void
    {
        $this->resetSchema();
        Mail::fake();

        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        $resp = $this->actingAs($eo)->postJson(route('eo.events.send-test-email', $event), [
            'test_email' => 'not-an-email',
            'custom_email_message' => '<p>x</p>',
            'ticket_email_use_qr' => '0',
        ]);

        $resp->assertStatus(422);
        Mail::assertNothingSent();
    }

    public function test_send_test_email_is_rate_limited_to_three_per_session(): void
    {
        $this->resetSchema();
        Mail::fake();

        $eo = User::factory()->create(['role' => 'eo']);
        $event = Event::factory()->create(['user_id' => $eo->id]);

        $this->actingAs($eo);

        for ($i = 0; $i < 3; $i++) {
            $resp = $this->postJson(route('eo.events.send-test-email', $event), [
                'test_email' => 'test@example.com',
                'custom_email_message' => '<p>ok</p>',
                'ticket_email_use_qr' => '1',
            ]);
            $resp->assertOk();
        }

        $resp4 = $this->postJson(route('eo.events.send-test-email', $event), [
            'test_email' => 'test@example.com',
            'custom_email_message' => '<p>ok</p>',
            'ticket_email_use_qr' => '1',
        ]);

        $resp4->assertStatus(429);
        $resp4->assertJsonPath('remaining', 0);
    }
}
