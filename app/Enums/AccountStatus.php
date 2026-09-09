<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';

    /**
     * Get the translated label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Suspended => __('Suspended'),
        };
    }

    /**
     * Get the semantic color token for the status.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'emerald',
            self::Suspended => 'rose',
        };
    }

    /**
     * Get the badge CSS classes for the status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400',
            self::Suspended => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400',
        };
    }

    /**
     * Check if the account is currently active.
     */
    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /**
     * Check if the account is currently suspended.
     */
    public function isSuspended(): bool
    {
        return $this === self::Suspended;
    }
}
