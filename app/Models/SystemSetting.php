<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value', 'group', 'description'])]
class SystemSetting extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * Retrieve a setting by its key, with optional default fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::remember("system_setting_{$key}", 86400, function () use ($key, $default) {
                try {
                    $setting = static::where('key', $key)->first();

                    return $setting !== null ? $setting->value : $default;
                } catch (\Throwable) {
                    return $default;
                }
            });
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Retrieve a boolean setting by its key.
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $val = static::get($key, $default);

        if (is_bool($val)) {
            return $val;
        }

        if (is_string($val)) {
            return filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) $val;
    }

    /**
     * Retrieve an integer setting by its key.
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $val = static::get($key, $default);

        return is_numeric($val) ? (int) $val : $default;
    }

    /**
     * Retrieve a string setting by its key.
     */
    public static function getString(string $key, string $default = ''): string
    {
        $val = static::get($key, $default);

        return is_string($val) ? $val : (string) ($val ?? $default);
    }

    /**
     * Check if a setting exists in storage.
     */
    public static function has(string $key): bool
    {
        try {
            return static::where('key', $key)->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Store or update a setting value and immediately refresh the cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?string $description = null): self
    {
        /** @var self $setting */
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::put("system_setting_{$key}", $setting->value, 86400);

        return $setting;
    }

    /**
     * Remove a setting from storage and cache.
     */
    public static function forget(string $key): bool
    {
        Cache::forget("system_setting_{$key}");

        try {
            return (bool) static::where('key', $key)->delete();
        } catch (\Throwable) {
            return false;
        }
    }
}
