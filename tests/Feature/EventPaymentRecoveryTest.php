<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class EventPaymentRecoveryTest extends TestCase
{
    private function resetSchema(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('events');

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('slug')->unique();
            $table->string('name');
            $table->dateTime('start_at');
            $table->string('location_name');
            $table->json('payment_config')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('public_ref', 20)->nullable();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('pic_data')->nullable();
            $table->decimal('total_original', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('payment_gateway')->default('midtrans');
            $table->string('midtrans_mode', 16)->default('production');
            $table->string('snap_token')->nullable();
            $table->string('midtrans_order_id')->nullable();
            $table->string('midtrans_transaction_status')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_pending_lookup_requires_phone_and_returns_matching_transactions(): void
    {
        $this->resetSchema();

        $event = Event::query()->create([
            'slug' => 'e',
            'name' => 'E',
            'start_at' => now(),
            'location_name' => 'L',
        ]);

        $tx = Transaction::query()->create([
            'event_id' => $event->id,
            'payment_gateway' => 'midtrans',
            'payment_status' => 'pending',
            'snap_token' => 'tok',
            'midtrans_order_id' => 'EVENT-PRD-1-1',
            'pic_data' => [
                'name' => 'PIC',
                'phone' => '08123456789',
            ],
        ]);

        $resp = $this->postJson(route('api.events.payments.pending', ['slug' => $event->slug]), [
            'phone' => '08123456789',
        ]);

        $resp->assertOk();
        $resp->assertJsonPath('success', true);
        $resp->assertJsonPath('transactions.0.id', $tx->id);
    }

    public function test_resume_returns_snap_token_only_for_pending_and_matching_phone(): void
    {
        $this->resetSchema();

        $event = Event::query()->create([
            'slug' => 'e',
            'name' => 'E',
            'start_at' => now(),
            'location_name' => 'L',
        ]);

        $tx = Transaction::query()->create([
            'event_id' => $event->id,
            'payment_gateway' => 'midtrans',
            'payment_status' => 'pending',
            'snap_token' => 'tok',
            'pic_data' => [
                'phone' => '08123456789',
            ],
        ]);

        $resp = $this->postJson(route('api.events.payments.resume', ['slug' => $event->slug, 'transaction' => $tx->id]), [
            'phone' => '08123456789',
        ]);

        $resp->assertOk();
        $resp->assertJsonPath('success', true);
        $resp->assertJsonPath('snap_token', 'tok');

        $resp2 = $this->postJson(route('api.events.payments.resume', ['slug' => $event->slug, 'transaction' => $tx->id]), [
            'phone' => '089999',
        ]);

        $resp2->assertStatus(403);
    }

    public function test_status_endpoint_updates_internal_status_from_midtrans_response(): void
    {
        $this->resetSchema();

        $event = Event::query()->create([
            'slug' => 'e',
            'name' => 'E',
            'start_at' => now(),
            'location_name' => 'L',
        ]);

        $tx = Transaction::query()->create([
            'event_id' => $event->id,
            'payment_gateway' => 'midtrans',
            'payment_status' => 'pending',
            'midtrans_mode' => 'sandbox',
            'midtrans_order_id' => 'EVENT-SBX-1-1',
            'pic_data' => [
                'phone' => '08123456789',
            ],
        ]);

        $statusObj = new \stdClass();
        $statusObj->transaction_status = 'settlement';

        $mock = Mockery::mock(MidtransService::class);
        $mock->shouldReceive('checkTransactionStatus')
            ->once()
            ->with('EVENT-SBX-1-1', 'sandbox')
            ->andReturn(['success' => true, 'status' => $statusObj]);

        $this->app->instance(MidtransService::class, $mock);

        $resp = $this->getJson(route('api.events.payments.status', ['slug' => $event->slug, 'transaction' => $tx->id]).'?phone=08123456789');

        $resp->assertOk();
        $resp->assertJsonPath('success', true);

        $tx->refresh();
        $this->assertSame('paid', $tx->payment_status);
        $this->assertSame('settlement', $tx->midtrans_transaction_status);
        $this->assertNotNull($tx->paid_at);
    }
}

