<?php

namespace App\Http\Controllers;

use App\Models\AppSettings;
use Illuminate\Http\Request;

class VCardController extends Controller
{
    public function index(Request $request)
    {
        $title = AppSettings::get('vcard_title', 'Ruang Lari - Komunitas Lari & Challenge 40 Hari Indonesia');
        $description = AppSettings::get('vcard_description', 'Gabung Ruang Lari, komunitas lari terbesar di Indonesia. Ikuti 40 Days Challenge, program latihan gratis, kalkulator pace, dan temukan jadwal event lari terbaru disini.');
        $logoUrl = AppSettings::get('vcard_logo_url', 'https://res.cloudinary.com/dslfarxct/images/c_scale,w_248,h_66/f_auto,q_auto/v1765865897/logo-ruang-lari_57925c8f9/logo-ruang-lari_57925c8f9.webp?_i=AA');
        $bgImageUrl = AppSettings::get('vcard_bg_image_url', 'https://res.cloudinary.com/dslfarxct/images/v1760944069/pelari-kece/pelari-kece.webp');
        $ogImageUrl = AppSettings::get('vcard_og_image_url', $bgImageUrl);

        $featuredLinks = json_decode((string) AppSettings::get('vcard_featured_links', ''), true);
        if (!is_array($featuredLinks) || count($featuredLinks) === 0) {
            $featuredLinks = [
                [
                    'title' => 'Latihan Bersama Robi Syianturi',
                    'url' => 'https://app.ruanglari.com/events/latihan-bersama-jumat',
                    'badge' => 'Join Latihan',
                    'icon' => 'users',
                ],
            ];
        }

        $links = json_decode((string) AppSettings::get('vcard_links', ''), true);
        if (!is_array($links) || count($links) === 0) {
            $links = [
                ['title' => '40 Days Challenge', 'url' => 'https://app.ruanglari.com/challenge/40-days-challenge', 'icon' => 'fire'],
                ['title' => 'Official Website', 'url' => 'https://ruanglari.com', 'icon' => 'globe'],
                ['title' => 'Free Program', 'url' => 'https://ruanglari.com/freeprogram/', 'icon' => 'gift'],
                ['title' => 'Kalkulator Lari', 'url' => 'https://ruanglari.com/running-calculator/', 'icon' => 'calculator'],
                ['title' => 'Pace Pro Tools', 'url' => 'https://ruanglari.com/ruang-lari-pace-pro/', 'icon' => 'gauge'],
                ['title' => 'Kalender Event', 'url' => 'https://ruanglari.com/kalender-lari-nasional/', 'icon' => 'calendar'],
                ['title' => 'Baca Blog', 'url' => 'https://ruanglari.com/blog', 'icon' => 'book-open'],
                ['title' => 'Kontak Admin', 'url' => 'https://wa.me/6287866950667', 'icon' => 'whatsapp', 'external' => true],
            ];
        }

        $socialLinks = json_decode((string) AppSettings::get('vcard_social_links', ''), true);
        if (!is_array($socialLinks) || count($socialLinks) === 0) {
            $socialLinks = [
                ['title' => 'Instagram', 'url' => 'https://instagram.com/ruanglaricom', 'icon' => 'instagram', 'external' => true],
                ['title' => 'WhatsApp', 'url' => 'https://wa.me/6287866950667', 'icon' => 'whatsapp', 'external' => true],
            ];
        }

        $adsUrl = AppSettings::get('vcard_ads_url', 'https://wa.me/6285524807623?text=hai%20saya%20tertarik%20untuk%20memasang%20iklan');
        $adsTitle = AppSettings::get('vcard_ads_title', 'Space Iklan Tersedia');
        $adsDescription = AppSettings::get('vcard_ads_description', 'Klik untuk pasang iklan brand kamu disini');

        return view('vcard.index', compact(
            'title',
            'description',
            'logoUrl',
            'bgImageUrl',
            'ogImageUrl',
            'featuredLinks',
            'links',
            'socialLinks',
            'adsUrl',
            'adsTitle',
            'adsDescription'
        ));
    }
}

