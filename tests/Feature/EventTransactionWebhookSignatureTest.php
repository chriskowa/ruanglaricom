<?php

namespace Tests\Feature;

use App\Jobs\ProcessPaidEventTransaction;
use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventTransactionWebhookSignatureTest extends TestCase
{
    private function resetSchema(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('events');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });

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

    public function test_webhook_rejects_invalid_signature(): void
    {
        $this->resetSchema();

        config([
            'midtrans.server_key' => 'prd-key',
            'midtrans.server_key_sandbox' => 'sbx-key',
        ]);

        $event = Event::query()->create([
            'slug' => 'e',
            'name' => 'E',
            'start_at' => now(),
            'location_name' => 'L',
        ]);

        $orderId = 'EVENT-SBX-1-1700000000';
        $tx = Transaction::query()->create([
            'event_id' => $event->id,
            'payment_gateway' => 'midtrans',
            'midtrans_mode' => 'sandbox',
            'midtrans_order_id' => $orderId,
        ]);

        Queue::fake();

        $resp = $this->postJson(route('events.transactions.webhook'), [
            'order_id' => $orderId,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'signature_key' => 'invalid',
        ]);

        $resp->assertStatus(401);

        $tx->refresh();
        $this->assertSame('pending', $tx->payment_status);
        Queue::assertNotPushed(ProcessPaidEventTransaction::class);
    }

    public function test_webhook_accepts_valid_signature_for_sandbox_and_marks_paid(): void
    {
        $this->resetSchema();

        config([
            'midtrans.server_key' => 'prd-key',
            'midtrans.server_key_sandbox' => 'sbx-key',
        ]);

        $event = Event::query()->create([
            'slug' => 'e',
            'name' => 'E',
            'start_at' => now(),
            'location_name' => 'L',
        ]);

        $orderId = 'EVENT-SBX-1-1700000000';
        $statusCode = '200';
        $grossAmount = '10000.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.'sbx-key');

        $tx = Transaction::query()->create([
            'event_id' => $event->id,
            'payment_gateway' => 'midtrans',
            'midtrans_mode' => 'sandbox',
            'midtrans_order_id' => $orderId,
        ]);

        Queue::fake();

        $resp = $this->postJson(route('events.transactions.webhook'), [
            'order_id' => $orderId,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
        ]);

        $resp->assertOk();

        $tx->refresh();
        $this->assertSame('paid', $tx->payment_status);
        Queue::assertPushed(ProcessPaidEventTransaction::class);
    }

    public function test_webhook_rejects_mode_mismatch_between_order_id_and_transaction(): void
    {
        $this->resetSchema();

        config([
            'midtrans.server_key' => 'prd-key',
            'midtrans.server_key_sandbox' => 'sbx-key',
        ]);

        $event = Event::query()->create([
            'slug' => 'e',
            'name' => 'E',
            'start_at' => now(),
            'location_name' => 'L',
        ]);

        $orderId = 'EVENT-SBX-1-1700000000';
        $tx = Transaction::query()->create([
            'event_id' => $event->id,
            'payment_gateway' => 'midtrans',
            'midtrans_mode' => 'production',
            'midtrans_order_id' => $orderId,
        ]);

        Queue::fake();

        $resp = $this->postJson(route('events.transactions.webhook'), [
            'order_id' => $orderId,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'status_code' => '200',
            'gross_amount' => '10000.00',
            'signature_key' => 'whatever',
        ]);

        $resp->assertStatus(409);

        $tx->refresh();
        $this->assertSame('pending', $tx->payment_status);
        Queue::assertNotPushed(ProcessPaidEventTransaction::class);
    }
}
