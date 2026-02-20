<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use Illuminate\Http\Request;

class PopupSettingsController extends Controller
{
    public function index()
    {
        $settings = AppSettings::get('popup.settings', []);
        if (is_string($settings)) {
            $decoded = json_decode($settings, true);
            $settings = is_array($decoded) ? $decoded : [];
        }
        return view('admin.popups.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enabled' => 'nullable|boolean',
            'default_frequency' => 'required|string|in:session,day,interval',
            'default_interval_hours' => 'nullable|integer|min:1|max:720',
            'global_overlay' => 'nullable|string|max:30',
            'z_index' => 'nullable|integer|min:10|max:9999',
        ]);
        $payload = [
            'enabled' => (bool) ($data['enabled'] ?? false),
            'default_frequency' => $data['default_frequency'],
            'default_interval_hours' => (int) ($data['default_interval_hours'] ?? 24),
            'global_overlay' => $data['global_overlay'] ?? 'rgba(15, 23, 42, 0.7)',
            'z_index' => (int) ($data['z_index'] ?? 1000),
        ];
        AppSettings::set('popup.settings', json_encode($payload));
        return redirect()->back()->with('success', 'Popup settings updated.');
    }
}
