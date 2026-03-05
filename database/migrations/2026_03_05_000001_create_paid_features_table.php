<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paid_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('feature_slug');
            $table->decimal('price', 12, 2);
            $table->string('status')->default('pending');
            $table->string('midtrans_order_id')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('midtrans_transaction_status')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'feature_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paid_features');
    }
};
