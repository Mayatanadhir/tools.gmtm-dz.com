<?php

declare(strict_types=1);

use App\Models\SystemSetting;

if (! function_exists('system_setting')) {
    /**
     * Retrieve a system setting by key with optional fallback.
     */
    function system_setting(string $key, mixed $default = null): mixed
    {
        return SystemSetting::get($key, $default);
    }
}

if (! function_exists('is_registration_open')) {
    /**
     * Determine whether new user registrations are currently open.
     */
    function is_registration_open(): bool
    {
        return SystemSetting::getBool('allow_registration', true);
    }
}
