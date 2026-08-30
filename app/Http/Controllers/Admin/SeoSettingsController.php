<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use Illuminate\Http\Request;

class SeoSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'seo_meta_title_default' => AppSettings::get(
                'seo_meta_title_default',
                'Ruang Lari | Media Lari Indonesia, Race, Training & Running Tools'
            ),
            'seo_meta_description_default' => AppSettings::get(
                'seo_meta_description_default',
                'Ruang Lari adalah media dan platform lari Indonesia. Temukan berita running, program latihan gratis, kalender race, rute lari, komunitas, coach, dan running tools.'
            ),
            'seo_meta_keywords_default' => AppSettings::get(
                'seo_meta_keywords_default',
                'ruang lari, media lari indonesia, info lari, tips latihan lari, program latihan 5k, program marathon, event running indonesia, kalender lari, running tools'
            ),
            'seo_og_image_default' => AppSettings::get('seo_og_image_default', 'https://ruanglari.id/assets/images/ruanglari-cover.jpg'),
            'seo_twitter_card_default' => AppSettings::get('seo_twitter_card_default', 'summary_large_image'),
            'seo_json_ld_schema_default' => AppSettings::get('seo_json_ld_schema_default', '{}'),
        ];

        return view('admin.settings.seo', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'seo_meta_title_default' => 'nullable|string|max:255',
            'seo_meta_description_default' => 'nullable|string|max:500',
            'seo_meta_keywords_default' => 'nullable|string|max:500',
            'seo_og_image_default' => 'nullable|url|max:1000',
            'seo_twitter_card_default' => 'nullable|string|in:summary,summary_large_image',
            'seo_json_ld_schema_default' => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            AppSettings::set($key, $value);
        }

        return back()->with('success', 'SEO settings updated successfully.');
    }
}
