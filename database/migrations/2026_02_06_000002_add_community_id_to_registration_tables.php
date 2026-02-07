<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_registrations', function (Blueprint $table) {
            $table->foreignId('community_id')->nullable()->after('event_id')->constrained('communities')->nullOnDelete();
        });

        Schema::table('community_participants', function (Blueprint $table) {
            $table->foreignId('community_member_id')->nullable()->after('community_registration_id')->constrained('community_members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('community_participants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('community_member_id');
        });

        Schema::table('community_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('community_id');
        });
    }
};
