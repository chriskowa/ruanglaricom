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
        Schema::table('articles', function (Blueprint $table) {
            $table->string('focus_keyword')->nullable()->after('meta_keywords');
            $table->string('focus_keyword_en')->nullable()->after('focus_keyword');
            $table->text('secondary_keywords')->nullable()->after('focus_keyword_en');
            $table->text('secondary_keywords_en')->nullable()->after('secondary_keywords');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'focus_keyword',
                'focus_keyword_en',
                'secondary_keywords',
                'secondary_keywords_en',
            ]);
        });
    }
};
