<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kp_athlete_templates')) {
            Schema::create('kp_athlete_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('slug', 120)->unique();
                $table->string('category', 60)->default('Nasional')->nullable();
                $table->text('bio')->nullable();
                $table->string('cover_image', 255)->nullable();
                $table->json('photos')->nullable(); // Array of strings (0 = cover, 1..12 = Jan..Dec)
                $table->string('accent_color', 20)->default('#E63946');
                $table->string('paper_color', 20)->default('white');
                $table->string('default_font', 50)->default('Inter');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_active', 'sort_order']);
            });
        }

        if (Schema::hasTable('kp_calendar_projects')) {
            Schema::table('kp_calendar_projects', function (Blueprint $table) {
                if (!Schema::hasColumn('kp_calendar_projects', 'admin_notes')) {
                    $table->text('admin_notes')->nullable()->after('version_counter');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kp_calendar_projects')) {
            Schema::table('kp_calendar_projects', function (Blueprint $table) {
                if (Schema::hasColumn('kp_calendar_projects', 'admin_notes')) {
                    $table->dropColumn('admin_notes');
                }
            });
        }

        Schema::dropIfExists('kp_athlete_templates');
    }
};
