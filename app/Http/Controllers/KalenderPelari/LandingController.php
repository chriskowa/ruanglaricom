<?php

namespace App\Http\Controllers\KalenderPelari;

use App\Http\Controllers\Controller;

class LandingController extends Controller
{
    public function index()
    {
        $currentYear = (int) now()->format('Y');
        $nextYear = $currentYear + 1;

        $templateHighlights = [
            ['id' => 'poster12m', 'name' => 'Poster 12 Bulan (A3+)', 'desc' => '1 lembar A3+ (4 baris x 3 kolom) & hero atlet'],
            ['id' => 'classic', 'name' => 'Running Classic', 'desc' => 'Tata letak klasik seimbang dengan ruang foto luas'],
            ['id' => 'bold', 'name' => 'Sport Bold', 'desc' => 'Header atletik masif & kontras tinggi kompetisi'],
            ['id' => 'strava', 'name' => 'Strava Heatmap', 'desc' => 'Dark feed Strava, garis rute GPS menyala & stats'],
            ['id' => 'night', 'name' => 'Midnight Runner', 'desc' => 'Dark mode kontras neon untuk lari malam & subuh'],
            ['id' => 'magazine', 'name' => 'Magazine Cover', 'desc' => 'Judul tipografi besar menimpa foto utama'],
            ['id' => 'polaroid', 'name' => 'Polaroid Gallery', 'desc' => 'Foto frame polaroid artistik dengan ruang catatan'],
            ['id' => 'badge', 'name' => 'Marathon Bib', 'desc' => 'Nomor dada lomba maraton & chip timing'],
            ['id' => 'topography', 'name' => 'Trail Topo Peak', 'desc' => 'Garis kontur elevasi gunung & profil tanjakan'],
            ['id' => 'modernist', 'name' => 'Swiss Modernist', 'desc' => 'Blok warna geometris Bauhaus asimetris'],
            ['id' => 'neon', 'name' => 'Neon Track', 'desc' => 'Garis lintasan lari neon elektrik kontras gelap'],
            ['id' => 'infographic', 'name' => 'Year in Sport 365', 'desc' => 'Poster rekap tahunan Strava, metrik KM & jam'],
        ];

        return view('kalender-pelari.landing', [
            'currentYear' => $currentYear,
            'nextYear' => $nextYear,
            'studioUrl' => route('kalender-pelari.studio'),
            'wizardUrl' => route('kalender-pelari.wizard'),
            'templateHighlights' => $templateHighlights,
            'totalTemplatesCount' => 37,
            'pageMetaTitle' => "Buat Kalender Lari {$nextYear} Custom | Studio Cetak PDF Kalender Pelari",
            'pageMetaDescription' => "Rancang kalender pelari {$nextYear} dengan 37 template atletik, foto maraton, jadwal lomba lari nasional, dan QR code profil. Ekspor high-definition 300 DPI siap cetak.",
        ]);
    }
}
