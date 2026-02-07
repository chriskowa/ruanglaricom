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
        if (! Schema::hasTable('communities')) {
            Schema::create('communities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('pic_name');
                $table->string('pic_email');
                $table->string('pic_phone');
                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->text('description')->nullable();
                $table->string('logo')->nullable();
                $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('hero_image')->nullable();
                $table->string('theme_color')->default('neon');
                $table->string('wa_group_link')->nullable();
                $table->string('instagram_link')->nullable();
                $table->string('tiktok_link')->nullable();
                $table->json('schedules')->nullable();
                $table->json('captains')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('communities', function (Blueprint $table) {
                if (! Schema::hasColumn('communities', 'hero_image')) {
                    $table->string('hero_image')->nullable()->after('logo');
                }
                if (! Schema::hasColumn('communities', 'theme_color')) {
                    $table->string('theme_color')->default('neon')->after('hero_image');
                }
                if (! Schema::hasColumn('communities', 'wa_group_link')) {
                    $table->string('wa_group_link')->nullable()->after('theme_color');
                }
                if (! Schema::hasColumn('communities', 'instagram_link')) {
                    $table->string('instagram_link')->nullable()->after('wa_group_link');
                }
                if (! Schema::hasColumn('communities', 'tiktok_link')) {
                    $table->string('tiktok_link')->nullable()->after('instagram_link');
                }
                if (! Schema::hasColumn('communities', 'schedules')) {
                    $table->json('schedules')->nullable()->after('tiktok_link');
                }
                if (! Schema::hasColumn('communities', 'captains')) {
                    $table->json('captains')->nullable()->after('schedules');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('communities')) {
            Schema::table('communities', function (Blueprint $table) {
                foreach ([
                    'hero_image',
                    'theme_color',
                    'wa_group_link',
                    'instagram_link',
                    'tiktok_link',
                    'schedules',
                    'captains',
                ] as $col) {
                    if (Schema::hasColumn('communities', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
