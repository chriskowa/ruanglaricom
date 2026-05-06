<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->text('excerpt_en')->nullable()->after('excerpt');
            $table->longText('content_en')->nullable()->after('content');

            $table->string('meta_title_en')->nullable()->after('meta_title');
            $table->text('meta_description_en')->nullable()->after('meta_description');
            $table->string('meta_keywords_en')->nullable()->after('meta_keywords');
            $table->string('canonical_url_en')->nullable()->after('canonical_url');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'title_en',
                'excerpt_en',
                'content_en',
                'meta_title_en',
                'meta_description_en',
                'meta_keywords_en',
                'canonical_url_en',
            ]);
        });
    }
};

