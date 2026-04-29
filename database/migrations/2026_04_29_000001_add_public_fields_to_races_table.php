<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            if (! Schema::hasColumn('races', 'slug')) {
                $table->string('slug', 120)->nullable()->after('name');
                $table->unique('slug');
            }

            if (! Schema::hasColumn('races', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('created_by');
                $table->timestamp('published_at')->nullable()->after('is_published');
            }

            if (! Schema::hasColumn('races', 'description')) {
                $table->text('description')->nullable()->after('published_at');
            }

            if (! Schema::hasColumn('races', 'location_name')) {
                $table->string('location_name', 255)->nullable()->after('description');
            }

            if (! Schema::hasColumn('races', 'start_at')) {
                $table->timestamp('start_at')->nullable()->after('location_name');
            }

            if (! Schema::hasColumn('races', 'end_at')) {
                $table->timestamp('end_at')->nullable()->after('start_at');
            }

            if (! Schema::hasColumn('races', 'banner_path')) {
                $table->string('banner_path')->nullable()->after('end_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            if (Schema::hasColumn('races', 'banner_path')) {
                $table->dropColumn('banner_path');
            }
            if (Schema::hasColumn('races', 'end_at')) {
                $table->dropColumn('end_at');
            }
            if (Schema::hasColumn('races', 'start_at')) {
                $table->dropColumn('start_at');
            }
            if (Schema::hasColumn('races', 'location_name')) {
                $table->dropColumn('location_name');
            }
            if (Schema::hasColumn('races', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('races', 'is_published')) {
                $table->dropColumn(['is_published', 'published_at']);
            }
            if (Schema::hasColumn('races', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        });
    }
};

