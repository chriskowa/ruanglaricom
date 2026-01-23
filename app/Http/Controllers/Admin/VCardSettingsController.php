<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use Illuminate\Http\Request;

class VCardSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'vcard_title' => AppSettings::get('vcard_title', 'Ruang Lari - Komunitas Lari & Challenge 40 Hari Indonesia'),
            'vcard_description' => AppSettings::get('vcard_description', 'Gabung Ruang Lari, komunitas lari terbesar di Indonesia. Ikuti 40 Days Challenge, program latihan gratis, kalkulator pace, dan temukan jadwal event lari terbaru disini.'),
            'vcard_logo_url' => AppSettings::get('vcard_logo_url', 'https://res.cloudinary.com/dslfarxct/images/c_scale,w_248,h_66/f_auto,q_auto/v1765865897/logo-ruang-lari_57925c8f9/logo-ruang-lari_57925c8f9.webp?_i=AA'),
            'vcard_bg_image_url' => AppSettings::get('vcard_bg_image_url', 'https://res.cloudinary.com/dslfarxct/images/v1760944069/pelari-kece/pelari-kece.webp'),
            'vcard_og_image_url' => AppSettings::get('vcard_og_image_url', AppSettings::get('vcard_bg_image_url', '')),
            'vcard_ads_url' => AppSettings::get('vcard_ads_url', 'https://wa.me/6285524807623?text=hai%20saya%20tertarik%20untuk%20memasang%20iklan'),
            'vcard_ads_title' => AppSettings::get('vcard_ads_title', 'Space Iklan Tersedia'),
            'vcard_ads_description' => AppSettings::get('vcard_ads_description', 'Klik untuk pasang iklan brand kamu disini'),
            'vcard_featured_links' => AppSettings::get('vcard_featured_links', ''),
            'vcard_links' => AppSettings::get('vcard_links', ''),
            'vcard_social_links' => AppSettings::get('vcard_social_links', ''),
        ];

        return view('admin.settings.vcard', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'vcard_title' => ['nullable', 'string', 'max:255'],
            'vcard_description' => ['nullable', 'string', 'max:500'],
            'vcard_logo_url' => ['nullable', 'url', 'max:1000'],
            'vcard_bg_image_url' => ['nullable', 'url', 'max:1000'],
            'vcard_og_image_url' => ['nullable', 'url', 'max:1000'],
            'vcard_ads_url' => ['nullable', 'url', 'max:1000'],
            'vcard_ads_title' => ['nullable', 'string', 'max:120'],
            'vcard_ads_description' => ['nullable', 'string', 'max:200'],
            'vcard_featured_links' => ['nullable', 'string'],
            'vcard_links' => ['nullable', 'string'],
            'vcard_social_links' => ['nullable', 'string'],
        ]);

        foreach (['vcard_featured_links', 'vcard_links', 'vcard_social_links'] as $jsonKey) {
            if (!empty($data[$jsonKey])) {
                $decoded = json_decode($data[$jsonKey], true);
                if (!is_array($decoded)) {
                    return back()->with('error', $jsonKey.' harus berupa JSON array.');
                }
            }
        }

        foreach ($data as $key => $value) {
            AppSettings::set($key, $value);
        }

        return back()->with('success', 'V-Card settings updated.');
    }
}

