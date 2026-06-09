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
}
