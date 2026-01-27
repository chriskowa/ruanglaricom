<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use Illuminate\Http\Request;

class IntegrationSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            // Integrations
            'google_search_console' => AppSettings::get('google_search_console'),
            'google_analytics' => AppSettings::get('google_analytics'),
            'bing_search_console' => AppSettings::get('bing_search_console'),
            'google_ads_tag' => AppSettings::get('google_ads_tag'),
            
            // General
            'site_title' => AppSettings::get('site_title', 'Ruang Lari'),
            'site_tagline' => AppSettings::get('site_tagline', 'Platform komunitas lari terbesar di Indonesia'),
            'contact_email' => AppSettings::get('contact_email'),
            'contact_whatsapp' => AppSettings::get('contact_whatsapp'),
            
            // Social Media
            'social_instagram' => AppSettings::get('social_instagram'),
            'social_tiktok' => AppSettings::get('social_tiktok'),
            'social_facebook' => AppSettings::get('social_facebook'),
            'social_youtube' => AppSettings::get('social_youtube'),

            // Financial
            'platform_fee_percent' => AppSettings::get('platform_fee_percent', 5),

            // Moota Payment
            'moota_api_token' => AppSettings::get('moota_api_token'),
            'moota_webhook_secret' => AppSettings::get('moota_webhook_secret'),
            'moota_bank_id' => AppSettings::get('moota_bank_id'),
            'moota_is_active' => AppSettings::get('moota_is_active', false),
            'moota_instructions' => AppSettings::get('moota_instructions'),
        ];

        return view('admin.settings.integrations', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            // Integrations
            'google_search_console' => 'nullable|string',
            'google_analytics' => 'nullable|string',
            'bing_search_console' => 'nullable|string',
            'google_ads_tag' => 'nullable|string',

            // General
            'site_title' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_whatsapp' => 'nullable|string|max:20',

            // Social Media
            'social_instagram' => 'nullable|url',
            'social_tiktok' => 'nullable|url',
            'social_facebook' => 'nullable|url',
            'social_youtube' => 'nullable|url',

            // Financial
            'platform_fee_percent' => 'nullable|numeric|min:0|max:100',

            // Moota Payment
            'moota_api_token' => 'nullable|string',
            'moota_webhook_secret' => 'nullable|string',
            'moota_bank_id' => 'nullable|string',
            'moota_is_active' => 'nullable|boolean',
            'moota_instructions' => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            AppSettings::set($key, $value);
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
