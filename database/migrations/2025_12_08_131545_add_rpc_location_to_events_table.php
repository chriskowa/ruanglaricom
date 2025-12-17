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
        Schema::table('events', function (Blueprint $table) {
            $table->string('rpc_location_name')->nullable()->after('location_name');
            $table->text('rpc_location_address')->nullable()->after('rpc_location_name');
            $table->decimal('rpc_latitude', 10, 8)->nullable()->after('rpc_location_address');
            $table->decimal('rpc_longitude', 11, 8)->nullable()->after('rpc_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['rpc_location_name', 'rpc_location_address', 'rpc_latitude', 'rpc_longitude']);
        });
    }
};
