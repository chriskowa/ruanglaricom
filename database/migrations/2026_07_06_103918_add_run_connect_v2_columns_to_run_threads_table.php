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
        Schema::table('run_threads', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('visibility');
            $table->unsignedBigInteger('parent_thread_id')->nullable()->after('is_recurring');
            $table->text('recap_notes')->nullable()->after('notes');
            $table->string('recap_image_path')->nullable()->after('recap_notes');
            
            // Note: Since this is likely not using a foreign key constraint to avoid cascade issues 
            // if we delete the parent, we just use a regular column.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('run_threads', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'parent_thread_id', 'recap_notes', 'recap_image_path']);
        });
    }
};
