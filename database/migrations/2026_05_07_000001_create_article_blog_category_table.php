<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_blog_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('blog_category_id')->constrained('blog_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['article_id', 'blog_category_id']);
        });

        if (! Schema::hasTable('articles')) {
            return;
        }

        DB::table('articles')
            ->select(['id', 'category_id'])
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                $now = now();
                $payload = [];

                foreach ($rows as $r) {
                    $payload[] = [
                        'article_id' => $r->id,
                        'blog_category_id' => $r->category_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($payload) {
                    DB::table('article_blog_category')->insertOrIgnore($payload);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_blog_category');
    }
};

