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
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'payment_gateway')) {
                $table->string('payment_gateway')->default('midtrans')->after('payment_status');
            }
            if (!Schema::hasColumn('transactions', 'payment_gateway_reference')) {
                $table->string('payment_gateway_reference')->nullable()->after('payment_gateway');
            }
            if (!Schema::hasColumn('transactions', 'unique_code')) {
                $table->integer('unique_code')->default(0)->after('total_original');
            }
            if (!Schema::hasColumn('transactions', 'moota_transaction_id')) {
                $table->string('moota_transaction_id')->nullable()->after('payment_gateway_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_gateway', 'payment_gateway_reference', 'unique_code', 'moota_transaction_id']);
        });
    }
};
