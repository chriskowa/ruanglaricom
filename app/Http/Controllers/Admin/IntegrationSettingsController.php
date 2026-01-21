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
            'google_search_console' => AppSettings::get('google_search_console'),
            'google_analytics' => AppSettings::get('google_analytics'),
            'bing_search_console' => AppSettings::get('bing_search_console'),
            'google_ads_tag' => AppSettings::get('google_ads_tag'),
        ];

        return view('admin.settings.integrations', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'google_search_console' => 'nullable|string',
            'google_analytics' => 'nullable|string',
            'bing_search_console' => 'nullable|string',
            'google_ads_tag' => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            AppSettings::set($key, $value);
        }

        return back()->with('success', 'Integration settings updated successfully.');
    }
}
