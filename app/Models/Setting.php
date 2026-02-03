<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    const CACHE_KEY = 'app_settings';

    protected $fillable = ['key', 'value'];

    protected static function booted()
    {
        static::saved(function () {
            static::refreshCache();
        });

        static::deleted(function () {
            static::refreshCache();
        });
    }

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Refresh the settings cache
     */
    public static function refreshCache()
    {
        Cache::forget(static::CACHE_KEY);
        Cache::rememberForever(static::CACHE_KEY, function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get all settings from cache
     */
    protected static function getCachedSettings(): array
    {
        return Cache::rememberForever(static::CACHE_KEY, function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $settings = static::getCachedSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value = null): void
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Remove a setting
     */
    public static function remove(string $key): void
    {
        self::where('key', $key)->delete();
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAll(): array
    {
        return static::getCachedSettings();
    }
}
