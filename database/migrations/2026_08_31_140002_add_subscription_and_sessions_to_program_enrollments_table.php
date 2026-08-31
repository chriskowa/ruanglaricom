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
        Schema::table('program_enrollments', function (Blueprint $table) {
            $table->string('pricing_type', 30)->default('one_time')->after('payment_transaction_id'); // one_time, hourly, daily, weekly, monthly
            $table->string('subscription_status', 30)->default('active')->after('pricing_type'); // active, pending_payment, overdue, grace_period, expired, cancelled
            $table->date('current_period_start')->nullable()->after('subscription_status');
            $table->date('current_period_end')->nullable()->after('current_period_start');
            $table->date('next_billing_date')->nullable()->after('current_period_end');
            $table->unsignedInteger('total_sessions_quota')->default(0)->after('next_billing_date');
            $table->unsignedInteger('sessions_used')->default(0)->after('total_sessions_quota');
            $table->unsignedInteger('sessions_remaining')->default(0)->after('sessions_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'pricing_type',
                'subscription_status',
                'current_period_start',
                'current_period_end',
                'next_billing_date',
                'total_sessions_quota',
                'sessions_used',
                'sessions_remaining',
            ]);
        });
    }
};
