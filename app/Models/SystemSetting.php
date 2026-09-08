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
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting !== null ? $setting->value : $default;
        });
    }

    /**
     * Store or update a setting value.
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

        Cache::forget("system_setting_{$key}");

        return $setting;
    }

    /**
     * Remove a setting from storage and cache.
     */
    public static function forget(string $key): bool
    {
        Cache::forget("system_setting_{$key}");

        return (bool) static::where('key', $key)->delete();
    }
}
