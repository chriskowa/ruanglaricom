<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kp_uploaded_assets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('storage_key', 255)->nullable()->unique();
            $t->string('mime_type', 60);
            $t->unsignedInteger('width_px')->nullable();
            $t->unsignedInteger('height_px')->nullable();
            $t->unsignedBigInteger('file_size_bytes')->nullable();
            $t->enum('status', [
                'LOCAL_ONLY','TEMP_UPLOAD','SAVED_PROJECT','PENDING_CHECKOUT',
                'PAID_ACTIVE','EXPIRED','DELETED'
            ])->default('LOCAL_ONLY')->index();
            $t->string('original_filename', 200)->nullable();
            $t->string('checksum_sha256', 64)->nullable()->index();
            $t->unsignedTinyInteger('resolution_quality_score')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['status','expires_at']);
        });

        Schema::create('kp_calendar_projects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('cover_asset_id')->nullable()
                ->constrained('kp_uploaded_assets')->nullOnDelete();
            $t->uuid('anonymous_uuid')->nullable()->unique();
            $t->string('name', 120)->default('Kalender Lariku');
            $t->smallInteger('year')->unsigned();
            $t->enum('format_type', [
                'A3_LANDSCAPE','A4_LANDSCAPE','DESK_CALENDAR','SQUARE','CUSTOM'
            ])->default('A4_LANDSCAPE');
            $t->boolean('is_anonymous')->default(true);
            $t->enum('start_week_on', ['MONDAY','SUNDAY'])->default('MONDAY');
            $t->enum('status', [
                'DRAFT_TRIAL','DRAFT_SAVED','PENDING_CHECKOUT',
                'PRODUCTION_ACTIVE','ARCHIVED','EXPIRED_TRIAL'
            ])->default('DRAFT_TRIAL');
            $t->longText('state_draft')->nullable();
            $t->unsignedInteger('version_counter')->default(1);
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['user_id','status']);
            $t->index(['anonymous_uuid','expires_at']);
            $t->index(['status','expires_at']);
        });

        Schema::create('kp_calendar_pages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')
                ->constrained('kp_calendar_projects')->cascadeOnDelete();
            $t->tinyInteger('month_number')->unsigned();
            $t->enum('page_type', ['COVER','MONTH','YEAR_REVIEW','CUSTOM'])->default('MONTH');
            $t->unsignedSmallInteger('canvas_width_mm')->default(297);
            $t->unsignedSmallInteger('canvas_height_mm')->default(210);
            $t->json('layout_config')->nullable();
            $t->timestamps();
            $t->unique(['project_id','month_number']);
        });

        Schema::create('kp_calendar_elements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('page_id')
                ->constrained('kp_calendar_pages')->cascadeOnDelete();
            $t->foreignId('asset_id')->nullable()
                ->constrained('kp_uploaded_assets')->nullOnDelete();
            $t->enum('element_type', [
                'PHOTO','TEXT','CALENDAR_GRID','MONTHLY_GOAL','TRAINING_PLAN',
                'WORKOUT','RACE','RACE_COUNTDOWN','MONTHLY_STATS','PACE_CHART',
                'HABIT_TRACKER','PERSONAL_BEST','SHOE_MILEAGE','NOTES','QUOTE',
                'ACTIVITY_CARD','PROGRESS_BAR','RUN_CLUB_EVENT','SPONSOR_LOGO',
                'DECORATIVE_SHAPE','PAGE_NUMBER','MONTH_TITLE'
            ])->index();
            $t->unsignedMediumInteger('x_mm')->default(0);
            $t->unsignedMediumInteger('y_mm')->default(0);
            $t->unsignedMediumInteger('width_mm')->default(50);
            $t->unsignedMediumInteger('height_mm')->default(50);
            $t->decimal('rotation_deg', 5, 2)->default(0);
            $t->smallInteger('z_index')->default(0);
            $t->boolean('locked')->default(false);
            $t->boolean('visible')->default(true);
            $t->json('style_config')->nullable();
            $t->longText('content_json')->nullable();
            $t->json('data_binding')->nullable();
            $t->timestamps();
            $t->index(['page_id','z_index']);
            $t->index(['element_type','page_id']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('kp_calendar_elements');
        Schema::dropIfExists('kp_calendar_pages');
        Schema::dropIfExists('kp_calendar_projects');
        Schema::dropIfExists('kp_uploaded_assets');
        Schema::enableForeignKeyConstraints();
    }
};
