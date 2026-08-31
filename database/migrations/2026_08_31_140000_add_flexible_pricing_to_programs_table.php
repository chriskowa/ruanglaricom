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
        Schema::table('programs', function (Blueprint $table) {
            $table->string('pricing_model', 30)->default('one_time')->after('price'); // one_time, hourly, daily, weekly, monthly, custom_package
            $table->decimal('price_hourly', 12, 2)->nullable()->after('pricing_model');
            $table->decimal('price_daily', 12, 2)->nullable()->after('price_hourly');
            $table->decimal('price_weekly', 12, 2)->nullable()->after('price_daily');
            $table->decimal('price_monthly', 12, 2)->nullable()->after('price_weekly');
            $table->unsignedInteger('session_quota')->default(1)->after('price_monthly');
            $table->boolean('allow_manual_payment')->default(true)->after('session_quota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'pricing_model',
                'price_hourly',
                'price_daily',
                'price_weekly',
                'price_monthly',
                'session_quota',
                'allow_manual_payment',
            ]);
        });
    }
};
