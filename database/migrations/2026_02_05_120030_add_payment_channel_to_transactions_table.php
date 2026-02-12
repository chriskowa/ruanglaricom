<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'payment_channel')) {
                $table->string('payment_channel')->nullable()->after('payment_gateway');
                $table->index('payment_channel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'payment_channel')) {
                $table->dropIndex(['payment_channel']);
                $table->dropColumn('payment_channel');
            }
        });
    }
};
