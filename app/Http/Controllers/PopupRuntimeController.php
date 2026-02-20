<?php

namespace App\Http\Controllers;

use App\Models\AppSettings;
use App\Models\Popup;
use App\Models\PopupStat;
use App\Services\PopupRuleEngine;
use Illuminate\Http\Request;

class PopupRuntimeController extends Controller
{
    public function active(Request $request, PopupRuleEngine $engine)
    {
        $settings = AppSettings::get('popup.settings', []);
        if (is_string($settings)) {
            $decoded = json_decode($settings, true);
            $settings = is_array($decoded) ? $decoded : [];
        }
        if (isset($settings['enabled']) && ! $settings['enabled']) {
            return response()->json(['popups' => []]);
        }
        $user = $request->user();
        $popups = Popup::query()
            ->whereIn('status', ['active', 'scheduled'])
            ->get()
            ->filter(fn (Popup $popup) => $engine->isEligible($popup, $request, $user))
            ->values()
            ->map(function (Popup $popup) use ($settings) {
                $popupSettings = array_merge([
                    'overlay' => $settings['global_overlay'] ?? 'rgba(15, 23, 42, 0.7)',
                    'z_index' => $settings['z_index'] ?? 1000,
                ], $popup->settings ?? []);
                $rules = $popup->rules ?? [];
                if (empty($rules['frequency']) && ! empty($settings['default_frequency'])) {
                    $rules['frequency'] = [
                        'mode' => $settings['default_frequency'],
                        'interval_hours' => $settings['default_interval_hours'] ?? 24,
                    ];
                }
                return [
                    'id' => $popup->id,
                    'name' => $popup->name,
                    'slug' => $popup->slug,
                    'content' => $popup->content ?? [],
                    'settings' => $popupSettings,
                    'rules' => $rules,
                ];
            });
        return response()->json(['popups' => $popups]);
    }

    public function track(Request $request, Popup $popup)
    {
        $data = $request->validate([
            'event' => 'required|in:view,click,conversion',
        ]);
        $stat = PopupStat::firstOrCreate([
            'popup_id' => $popup->id,
            'stat_date' => now()->toDateString(),
        ]);
        if ($data['event'] === 'view') {
            $stat->increment('views');
        } elseif ($data['event'] === 'click') {
            $stat->increment('clicks');
        } else {
            $stat->increment('conversions');
        }
        return response()->json(['success' => true]);
    }
}
