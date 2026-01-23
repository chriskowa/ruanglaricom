<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacer_bookings', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_number')->unique();
            $table->foreignId('runner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pacer_id')->constrained('pacers')->cascadeOnDelete();

            $table->string('event_name')->nullable();
            $table->date('race_date')->nullable();
            $table->string('distance')->nullable();
            $table->string('target_pace')->nullable();
            $table->string('meeting_point')->nullable();
            $table->text('notes')->nullable();

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('platform_fee_amount', 12, 2)->default(0);
            $table->decimal('pacer_amount', 12, 2)->default(0);

            $table->string('status')->default('pending')->index();
            $table->string('midtrans_order_id')->nullable()->index();
            $table->string('snap_token')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('disputed_at')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacer_bookings');
    }
};

