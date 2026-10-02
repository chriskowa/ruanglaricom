<?php

namespace App\Http\Controllers\KalenderPelari;

use App\Http\Controllers\Controller;
class LandingController extends Controller
{
    public function index()
    {
        $nextYear = (int) now()->format('Y') + 1;

        return view('kalender-pelari.landing', [
            'nextYear' => $nextYear,
            'wizardUrl' => route('kalender-pelari.wizard'),
            'pageMetaTitle' => "Kalender Pelari {$nextYear} | Buat Kalender Lari Pribadi",
            'pageMetaDescription' => 'Pilih tahun, ukuran, dan tampilan kalender lari. Halaman bulanan sudah berisi tanggal dan bisa disesuaikan di editor tanpa login.',
        ]);
    }
}
