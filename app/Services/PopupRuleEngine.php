<?php

namespace App\Services;

use App\Models\Popup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PopupRuleEngine
{
    public function isEligible(Popup $popup, Request $request, ?User $user): bool
    {
        $rules = $popup->rules ?? [];
        if (! $this->matchStatus($popup, $rules)) {
            return false;
        }
        if (! $this->matchSchedule($popup, $rules)) {
            return false;
        }
        if (! $this->matchPage($rules, $request)) {
            return false;
        }
        if (! $this->matchDevice($rules, $request)) {
            return false;
        }
        if (! $this->matchSegment($rules, $user)) {
            return false;
        }
        if (! $this->matchGeo($rules, $request, $user)) {
            return false;
        }
        return true;
    }

    private function matchStatus(Popup $popup, array $rules): bool
    {
        if ($popup->status === 'draft') {
            return false;
        }
        if ($popup->status === 'expired') {
            return false;
        }
        return true;
    }

    private function matchSchedule(Popup $popup, array $rules): bool
    {
        $timezone = $popup->timezone ?: config('app.timezone');
        $now = Carbon::now($timezone);
        if ($popup->starts_at && $now->lt($popup->starts_at->copy()->timezone($timezone))) {
            return false;
        }
        if ($popup->ends_at && $now->gt($popup->ends_at->copy()->timezone($timezone))) {
            return false;
        }
        if (! empty($rules['time_windows']) && is_array($rules['time_windows'])) {
            $current = $now->format('H:i');
            $matched = false;
            foreach ($rules['time_windows'] as $window) {
                $start = $window['start'] ?? null;
                $end = $window['end'] ?? null;
                if (! $start || ! $end) {
                    continue;
                }
                if ($current >= $start && $current <= $end) {
                    $matched = true;
                    break;
                }
            }
            if (! $matched) {
                return false;
            }
        }
        return true;
    }

    private function matchPage(array $rules, Request $request): bool
    {
        $path = '/' . ltrim($request->input('path', $request->path()), '/');
        $include = $rules['include_paths'] ?? [];
        $exclude = $rules['exclude_paths'] ?? [];
        if (is_array($exclude) && $exclude) {
            foreach ($exclude as $pattern) {
                if ($pattern && Str::is($pattern, $path)) {
                    return false;
                }
            }
        }
        if (is_array($include) && $include) {
            $matched = false;
            foreach ($include as $pattern) {
                if ($pattern && Str::is($pattern, $path)) {
                    $matched = true;
                    break;
                }
            }
            if (! $matched) {
                return false;
            }
        }
        return true;
    }

    private function matchDevice(array $rules, Request $request): bool
    {
        $devices = $rules['devices'] ?? [];
        if (! is_array($devices) || ! $devices) {
            return true;
        }
        $ua = strtolower($request->userAgent() ?? '');
        $device = str_contains($ua, 'ipad') || str_contains($ua, 'tablet') ? 'tablet' : (str_contains($ua, 'mobi') || str_contains($ua, 'android') ? 'mobile' : 'desktop');
        return in_array($device, $devices, true);
    }

    private function matchSegment(array $rules, ?User $user): bool
    {
        $segments = $rules['segments'] ?? [];
        if (! is_array($segments) || ! $segments) {
            return true;
        }
        if (! $user) {
            return in_array('guest', $segments, true);
        }
        $allowed = [];
        $createdAt = $user->created_at;
        if ($createdAt && $createdAt->gt(now()->subDays(7))) {
            $allowed[] = 'new';
        } else {
            $allowed[] = 'returning';
        }
        if ($user->membership_status === 'active' || $user->package_tier === 'premium') {
            $allowed[] = 'premium';
        }
        $allowed[] = 'member';
        foreach ($allowed as $flag) {
            if (in_array($flag, $segments, true)) {
                return true;
            }
        }
        return false;
    }

    private function matchGeo(array $rules, Request $request, ?User $user): bool
    {
        $cities = $rules['city_ids'] ?? [];
        if (! is_array($cities) || ! $cities) {
            return true;
        }
        $cityId = $request->input('city_id');
        if (! $cityId && $user) {
            $cityId = $user->city_id;
        }
        if (! $cityId) {
            return false;
        }
        return in_array((int) $cityId, array_map('intval', $cities), true);
    }
}
