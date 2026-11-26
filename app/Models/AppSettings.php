<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSettings extends Model
{
    protected $guarded = [];

    public static function get($key, $default = null)
    {
        return \Illuminate\Support\Facades\Cache::remember("app_settings.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set($key, $value)
    {
        \Illuminate\Support\Facades\Cache::forget("app_settings.{$key}");
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Resolve active map provider configuration managed from Admin Settings.
     *
     * @return array{provider: string, osm_style: string, mapbox_token: string, raw_mapbox_token: string}
     */
    public static function mapConfig(): array
    {
        $provider = strtolower(trim((string) self::get('map_provider', 'openstreetmap')));
        if (!in_array($provider, ['openstreetmap', 'mapbox'], true)) {
            $provider = 'openstreetmap';
        }

        $osmStyle = strtolower(trim((string) self::get('map_osm_style', 'osm')));
        if (!in_array($osmStyle, ['osm', 'carto_voyager', 'carto_dark'], true)) {
            $osmStyle = 'osm';
        }

        $rawToken = trim((string) self::get('mapbox_token', ''));
        if ($rawToken === '') {
            $rawToken = trim((string) config('services.mapbox.token', ''));
        }

        return [
            'provider' => $provider,
            'osm_style' => $osmStyle,
            'mapbox_token' => ($provider === 'mapbox' && $rawToken !== '') ? $rawToken : '',
            'raw_mapbox_token' => $rawToken,
        ];
    }
}
