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
        // Optimasi untuk tabel articles
        Schema::table('articles', function (Blueprint $table) {
            // Index untuk filter artikel yang dipublish + urutan tanggal (paling sering dipakai)
            $table->index(['status', 'published_at']);
            
            // Index untuk pencarian artikel populer
            $table->index('views_count');
            
            // Index untuk filter kategori dan user (walaupun FK kadang sudah ada, kita pastikan eksplisit untuk performa)
            // Note: FK constraints biasanya sudah membuat index di beberapa DB engine, tapi index eksplisit lebih aman.
            // Cek dulu apakah sudah ada index FK. Jika belum, tambahkan.
            // Namun karena di migrasi awal sudah pakai foreignId()->constrained(), biasanya index sudah ada.
            // Kita fokus ke compound index yang belum ada.
            
            // Index untuk pencarian judul (jika pakai LIKE 'word%')
            $table->index('title');
        });

        // Optimasi untuk tabel blog_categories
        Schema::table('blog_categories', function (Blueprint $table) {
            // Index parent_id untuk query hierarki kategori
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
            $table->dropIndex(['views_count']);
            $table->dropIndex(['title']);
        });

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
        });
    }
};
