<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            if (! Schema::hasColumn('marketplace_products', 'is_sold')) {
                $table->boolean('is_sold')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('marketplace_products', 'sold_channel')) {
                $table->string('sold_channel')->nullable()->after('is_sold');
            }
            if (! Schema::hasColumn('marketplace_products', 'sold_channel_note')) {
                $table->string('sold_channel_note')->nullable()->after('sold_channel');
            }
            if (! Schema::hasColumn('marketplace_products', 'sold_to_buyer_name')) {
                $table->string('sold_to_buyer_name')->nullable()->after('sold_channel_note');
            }
            if (! Schema::hasColumn('marketplace_products', 'sold_to_user_id')) {
                $table->foreignId('sold_to_user_id')->nullable()->constrained('users')->nullOnDelete()->after('sold_to_buyer_name');
            }
            if (! Schema::hasColumn('marketplace_products', 'sold_at')) {
                $table->timestamp('sold_at')->nullable()->after('sold_to_user_id');
            }
            if (! Schema::hasColumn('marketplace_products', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('sold_at');
            }
            if (! Schema::hasColumn('marketplace_products', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('is_archived');
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            if (Schema::hasColumn('marketplace_products', 'sold_to_user_id')) {
                $table->dropForeign(['sold_to_user_id']);
            }
            $table->dropColumn([
                'is_sold',
                'sold_channel',
                'sold_channel_note',
                'sold_to_buyer_name',
                'sold_to_user_id',
                'sold_at',
                'is_archived',
                'archived_at',
            ]);
        });
    }
};
