<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('participant_supports', function (Blueprint $table) {
            $table->string('payment_method')->default('midtrans')->after('status'); // midtrans, moota, cod
            $table->string('payment_channel')->nullable()->after('payment_method');
            $table->integer('unique_code')->nullable()->after('nominal');
            $table->string('moota_transaction_id')->nullable()->after('midtrans_order_id');
            $table->timestamp('expires_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_supports', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_channel', 'unique_code', 'moota_transaction_id', 'expires_at']);
        });
    }
};
