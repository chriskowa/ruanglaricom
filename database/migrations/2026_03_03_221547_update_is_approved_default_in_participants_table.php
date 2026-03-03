<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing records to 1 if null
        DB::table('participants')->whereNull('isApproved')->update(['isApproved' => 1]);

        Schema::table('participants', function (Blueprint $table) {
            $table->boolean('isApproved')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We cannot easily revert default value if we don't know previous state
        // But we can revert to nullable if needed, but user didn't ask for nullable.
        // Assuming default was 0 or null.
    }
};
