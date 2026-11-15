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
            ['id' => 'bold', 'name' => 'Sport Bold', 'desc' => 'Header atletik masif, kontras tinggi dan energi kompetisi race day.'],
            ['id' => 'strava', 'name' => 'Strava Heatmap', 'desc' => 'Dark feed Strava dengan garis lintasan rute GPS dan metrik kilometer.'],
            ['id' => 'classic', 'name' => 'Running Classic', 'desc' => 'Tata letak klasik seimbang dengan ruang foto luas dan kalender bersih.'],
            ['id' => 'night', 'name' => 'Midnight Runner', 'desc' => 'Dark mode elegan dengan palet biru es, pas untuk lari malam dan subuh.'],
            ['id' => 'badge', 'name' => 'Marathon Bib', 'desc' => 'Nuansa nomor dada maraton resmi lengkap dengan chip timing dan split pace.'],
            ['id' => 'topography', 'name' => 'Trail Topo Peak', 'desc' => 'Garis kontur elevasi pegunungan dan profil tanjakan untuk pelari trail.'],
        ];

        return view('kalender-pelari.landing', [
            'currentYear' => $currentYear,
            'nextYear' => $nextYear,
            'studioUrl' => route('kalender-pelari.studio'),
            'wizardUrl' => route('kalender-pelari.wizard'),
            'templateHighlights' => $templateHighlights,
            'pageMetaTitle' => "Buat Kalender Lari {$nextYear} Custom | Studio Cetak PDF Kalender Pelari",
            'pageMetaDescription' => "Rancang kalender pelari {$nextYear} dengan 36 template atletik, foto maraton, jadwal lomba lari nasional, dan QR code profil. Ekspor high-definition 300 DPI siap cetak.",
        ]);
    }
}
