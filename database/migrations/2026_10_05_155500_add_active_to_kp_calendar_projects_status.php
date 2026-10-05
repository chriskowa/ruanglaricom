<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `kp_calendar_projects` MODIFY COLUMN `status` ENUM('DRAFT_TRIAL','DRAFT_SAVED','ACTIVE','PENDING_CHECKOUT','PRODUCTION_ACTIVE','ARCHIVED','EXPIRED_TRIAL') NOT NULL DEFAULT 'DRAFT_TRIAL'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `kp_calendar_projects` MODIFY COLUMN `status` ENUM('DRAFT_TRIAL','DRAFT_SAVED','PENDING_CHECKOUT','PRODUCTION_ACTIVE','ARCHIVED','EXPIRED_TRIAL') NOT NULL DEFAULT 'DRAFT_TRIAL'");
    }
};
