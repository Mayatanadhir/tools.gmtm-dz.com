<?php

declare(strict_types=1);

namespace App\Enums;

enum EmployeeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case OnLeave = 'on_leave';

    /**
     * Get the translated label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
            self::OnLeave => __('On Leave'),
        };
    }

    /**
     * Get the semantic badge variant for the status.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'danger',
            self::OnLeave => 'warning',
        };
    }

    /**
     * Get the semantic color token for the status.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'emerald',
            self::Inactive => 'rose',
            self::OnLeave => 'amber',
        };
    }

    /**
     * Get the badge CSS classes for the status (legacy compatibility).
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20',
            self::Inactive => 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/20',
            self::OnLeave => 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/20',
        };
    }

    /**
     * Check if the employee is active.
     */
    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /**
     * Get all enum values as an array.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
