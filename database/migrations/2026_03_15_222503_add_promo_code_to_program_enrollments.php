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
            if (!Schema::hasColumn('program_enrollments', 'promo_code_used')) {
                $table->string('promo_code_used', 50)->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('program_enrollments', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('promo_code_used');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_enrollments', function (Blueprint $table) {
            $table->dropColumn(['promo_code_used', 'payment_method']);
        });
    }
};
