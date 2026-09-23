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
     * Get the semantic badge variant for the status.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Suspended => 'danger',
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
            self::Active => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20',
            self::Suspended => 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/20',
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
