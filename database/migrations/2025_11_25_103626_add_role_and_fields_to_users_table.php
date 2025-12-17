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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'coach', 'runner', 'eo'])->default('runner')->after('email');
            $table->foreignId('city_id')->nullable()->after('role')->constrained('cities')->onDelete('set null');
            $table->enum('package_tier', ['basic', 'pro', 'business'])->default('basic')->after('city_id');
            $table->json('bank_account')->nullable()->after('package_tier');
            $table->timestamp('bank_verified_at')->nullable()->after('bank_account');
            $table->string('referral_code')->unique()->nullable()->after('bank_verified_at');
            $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('users')->onDelete('set null');
            $table->string('avatar')->nullable()->after('referred_by');
            $table->string('phone')->nullable()->after('avatar');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->text('address')->nullable()->after('date_of_birth');
            $table->text('strava_token')->nullable()->after('address');
            $table->text('google_calendar_token')->nullable()->after('strava_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['wallet_id']);
            $table->dropForeign(['referred_by']);
            $table->dropColumn([
                'role', 'city_id', 'package_tier', 'bank_account', 'bank_verified_at',
                'wallet_id', 'referral_code', 'referred_by', 'avatar', 'phone',
                'date_of_birth', 'address', 'strava_token', 'google_calendar_token'
            ]);
        });
    }
};
