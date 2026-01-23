<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SeoSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'seo_meta_title_default' => \App\Models\AppSettings::get('seo_meta_title_default', 'Ruang Lari | Komunitas Lari Indonesia, Event, Pacer & Training Plans'),
            'seo_meta_description_default' => \App\Models\AppSettings::get('seo_meta_description_default', 'Ruang Lari adalah platform komunitas lari terbesar di Indonesia. Temukan pacer, ikuti event, pantau progres, dan raih personal best Anda.'),
            'seo_meta_keywords_default' => \App\Models\AppSettings::get('seo_meta_keywords_default', 'ruang lari, komunitas lari, event lari, pacer indonesia, training plan'),
            'seo_og_image_default' => \App\Models\AppSettings::get('seo_og_image_default', 'https://ruanglari.id/assets/images/ruanglari-cover.jpg'),
            'seo_twitter_card_default' => \App\Models\AppSettings::get('seo_twitter_card_default', 'summary_large_image'),
            'seo_json_ld_schema_default' => \App\Models\AppSettings::get('seo_json_ld_schema_default', '{}'),
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
            \App\Models\AppSettings::set($key, $value);
        }

        return back()->with('success', 'SEO settings updated successfully.');
    }
}
