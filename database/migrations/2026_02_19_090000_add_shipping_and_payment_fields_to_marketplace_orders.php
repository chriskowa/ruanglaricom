<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->string('payment_method')->default('midtrans')->after('seller_id');
            $table->string('shipping_name')->nullable()->after('payment_method');
            $table->string('shipping_phone')->nullable()->after('shipping_name');
            $table->text('shipping_address')->nullable()->after('shipping_phone');
            $table->string('shipping_city')->nullable()->after('shipping_address');
            $table->string('shipping_postal_code', 20)->nullable()->after('shipping_city');
            $table->string('shipping_courier')->nullable()->after('shipping_postal_code');
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('shipping_courier');
            $table->text('shipping_note')->nullable()->after('shipping_cost');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'shipping_name',
                'shipping_phone',
                'shipping_address',
                'shipping_city',
                'shipping_postal_code',
                'shipping_courier',
                'shipping_cost',
                'shipping_note',
            ]);
        });
    }
};

